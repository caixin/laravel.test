<?php

namespace App\Repositories;

use Route;
use Models\Model;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

abstract class AbstractRepository
{
    const CHUNK = 1000;

    protected $entity; //預設Model
    protected $db;     //取資料時使用 用完還原成預設Model
    protected $_paginate = 0;
    protected $_select   = [];
    protected $_search   = [];
    protected $_where    = [];
    protected $_join     = [];
    protected $_order    = [];
    protected $_group    = [];
    protected $_having   = [];
    protected $_limit    = [];
    protected $is_action_log = true;

    public function __call($methods, $arguments)
    {
        return call_user_func_array([$this->entity, $methods], $arguments);
    }

    public function __construct(Model $entity)
    {
        $this->entity = $entity;
        $this->db     = $entity;
        DB::connection()->enableQueryLog();
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setActionLog($bool)
    {
        return $this->is_action_log = $bool;
    }

    public function paginate($data)
    {
        $this->_paginate = $data;
        return $this;
    }

    public function select($data)
    {
        $this->_select = $data;
        return $this;
    }

    public function search($data)
    {
        $this->_search = $data;
        return $this;
    }

    public function where($data)
    {
        $this->_where = $data;
        return $this;
    }

    public function join($data)
    {
        $this->_join = $data;
        return $this;
    }

    public function group($data)
    {
        $this->_group = $data;
        return $this;
    }

    public function having($data)
    {
        $this->_having = $data;
        return $this;
    }

    public function order($data)
    {
        $this->_order = $data;
        return $this;
    }

    public function limit($data)
    {
        $this->_limit = $data;
        return $this;
    }

    public function set($data)
    {
        $this->_set = $data;
        return $this;
    }

    public function reset()
    {
        $this->_paginate = 0;
        $this->_select   = [];
        $this->_search   = [];
        $this->_where    = [];
        $this->_join     = [];
        $this->_group    = [];
        $this->_having   = [];
        $this->_order    = [];
        $this->_limit    = [];
        $this->_set      = [];
        $this->db        = $this->entity;
        return $this;
    }

    public function _do_search()
    {
        return $this;
    }

    public function _do_action()
    {
        if (!empty($this->_select)) {
            $this->db = $this->db->select($this->_select);
        }

        if (!empty($this->_join)) {
            foreach ($this->_join as $join) {
                $this->db = $this->db->$join();
            }
        }

        $this->_do_search();
        if (!empty($this->_where)) {
            foreach ($this->_where as $where) {
                if (is_array($where[2])) {
                    $this->db = $this->db->whereIn($where[0], $where[2]);
                } else {
                    $this->db = $this->db->where($where[0], $where[1], $where[2]);
                }
            }
        }

        if (!empty($this->_group)) {
            $this->db = $this->db->groupBy($this->_group);
        }

        if (!empty($this->_having)) {
            $this->db = $this->db->having($this->_having);
        }

        if (!empty($this->_order)) {
            if (isset($this->_order[0])) {
                $this->db = $this->db->orderBy($this->_order[0], $this->_order[1]);
            } else {
                foreach ($this->_order as $key => $val) {
                    $this->db = $this->db->orderBy($key, $val);
                }
            }
        }

        if (!empty($this->_limit)) {
            $this->db = $this->db->offset($this->_limit[0])->limit($this->_limit[1]);
        }

        return $this;
    }

    public function row($id)
    {
        if ($row = Redis::get($this->entity->getTable().":$id")) {
            dd($row);
            return $row;
        }
        
        $row = $this->entity->find($id);

        Redis::setex($this->entity->getTable().":$id", 1800, $row);
        return $row;
    }

    public function result_one()
    {
        $this->_do_action();
        $row = $this->db->first();
        $this->reset();
        return $row;
    }

    public function result()
    {
        $this->_do_action();
        if ($this->_paginate > 0) {
            $result = $this->db->paginate($this->_paginate)->appends($this->_search);
        } else {
            $result = $this->db->get();
        }
        $this->reset();
        return $result;
    }

    /**
     * 取得最後執行的SQL語句
     *
     * @return string
     */
    public function last_query()
    {
        $querylog = DB::getQueryLog();
        $lastQuery = end($querylog);
        $stringSQL = str_replace('?', '%s', $lastQuery['query']);
        return sprintf($stringSQL, ...$lastQuery['bindings']);
    }

    public function get_compiled_select()
    {
        $this->_do_action();
        $stringSQL = str_replace('?', '%s', $this->db->toSql());
        $stringSQL = sprintf($stringSQL, ...$this->db->getBindings());
        $this->reset();
        return $stringSQL;
    }

    public function save($data, $id=0)
    {
        if ($id == 0) {
            $this->_do_action();
            $row = $this->db->first();
            $this->reset();
        } else {
            $row = $this->entity->find($id);
        }

        foreach ($data as $key => $val) {
            $row->$key = $val;
        }
        $row->save();
        $data[$this->entity->getKeyName()] = $row->id;
        $this->actionLog($data);
    }

    public function insert($data)
    {
        if (Schema::hasColumn($this->entity->getTable(), 'create_time')) {
            $data['create_time'] = date('Y-m-d H:i:s');
        }
        if (Schema::hasColumn($this->entity->getTable(), 'create_by')) {
            $data['create_by'] = session('username') ?: '';
        }
        if (Schema::hasColumn($this->entity->getTable(), 'update_time')) {
            $data['update_time'] = date('Y-m-d H:i:s');
        }
        if (Schema::hasColumn($this->entity->getTable(), 'update_by')) {
            $data['update_by'] = session('username') ?: '';
        }
        if (Schema::hasColumn($this->entity->getTable(), 'source')) {
            //$data['source'] = $this->source == '' ?: 'wap';
        }
        if (Schema::hasColumn($this->entity->getTable(), 'platform')) {
            //$data['platform'] = get_platform();
        }

        $id = $this->entity->insertGetId($data);
        $this->actionLog($data, $id > 0 ? 1 : 0);
        return $id;
    }

    public function create($data)
    {
        if (Schema::hasColumn($this->entity->getTable(), 'create_by')) {
            $data['create_by'] = session('username') ?: '';
        }
        if (Schema::hasColumn($this->entity->getTable(), 'update_by')) {
            $data['update_by'] = session('username') ?: '';
        }
        if (Schema::hasColumn($this->entity->getTable(), 'source')) {
            //$data['source'] = $this->source == '' ?: 'wap';
        }
        if (Schema::hasColumn($this->entity->getTable(), 'platform')) {
            //$data['platform'] = get_platform();
        }

        $create = $this->entity->create($data);
        $this->actionLog($data, $create->id > 0 ? 1 : 0);
        return $create->id;
    }

    public function insert_batch($data)
    {
        $this->entity->insert($data);
        //寫入操作日誌
        /*
        if ($this->is_action_log) {
            $sql_str = $this->db->last_query();
            $message = $this->title . '(' . $this->getActionString_batch($data) . ')';
            $this->admin_action_log_db->insert([
                'sql_str' => $sql_str,
                'message' => $message,
                'status'  => $this->trans_status() ? 1 : 0,
            ]);
        }
        */
    }

    public function update($data, $id=0)
    {
        if (Schema::hasColumn($this->entity->getTable(), 'update_by')) {
            $data['update_by'] = session('username');
        }

        if ($id == 0) {
            $this->_do_action();
            $update = $this->db->update($data);
            $this->reset();
        } else {
            $update = $this->entity->find($id)->update($data);
        }
        //清除快取
        Redis::del($this->entity->getTable().":$id");

        $this->actionLog($data, $update > 0 ? 1 : 0);
    }

    public function update_batch($data)
    {
        $this->entity->updateBatch($data);
        //清除快取
        foreach ($data as $row) {
            Redis::del($this->entity->getTable().":".$row[$this->entity->getKeyName()]);
        }

        //寫入操作日誌
        /*
        if ($this->is_action_log) {
            $sql_str = $this->db->last_query();
            $message = $this->title . '(' . $this->getActionString_batch($data) . ')';
            $this->admin_action_log_db->insert([
                'sql_str' => $sql_str,
                'message' => $message,
                'status'  => $this->trans_status() ? 1 : 0,
            ]);
        }
        */
    }

    public function delete($id=0)
    {
        if ($id == 0) {
            $this->_do_action();
            $this->db->delete();
            $this->reset();
        } else {
            $this->entity->find($id)->delete();
        }

        $this->actionLog([$this->entity->getKeyName()=>$id]);
    }

    public function increment($column, $amount)
    {
        $this->entity->increment($column, $amount);
    }

    public function decrement($column, $amount)
    {
        $this->entity->decrement($column, $amount);
    }

    public function truncate()
    {
        $this->entity->truncate();
    }

    /**
     * 寫入操作日誌
     *
     * @param array $data 欄位資料
     * @param integer $status 狀態
     * @return void
     */
    public function actionLog($data, $status=1)
    {
        if ($this->is_action_log) {
            $message = view()->shared('title') . '(' . $this->getActionString($data) . ')';
            DB::table('admin_action_log')->insert([
                'adminid'     => session('id'),
                'route'       => Route::currentRouteName(),
                'sql_str'     => $this->last_query(),
                'message'     => $message,
                'ip'          => Request()->getClientIp(),
                'status'      => $status,
                'create_time' => date('Y-m-d H:i:s'),
                'create_by'   => session('username'),
            ]);
        }
    }

    /**
     * 組成操作日誌字串
     */
    public function getActionString($data, $highlight = [])
    {
        $str = [];
        foreach ($data as $key => $val) {
            //判斷有無欄位
            if (isset(static::$columnList[$key])) {
                //判斷欄位有無靜態陣列
                if (isset($this->entity::$field->$val)) {
                    $val = $this->entity::$field->$val;
                }

                if (isset($highlight[$key])) {
                    $str[] = '<font color="blue">' . static::$columnList[$key] . "=$val</font>";
                } else {
                    $str[] = static::$columnList[$key] . "=$val";
                }
            }
        }

        return implode(';', $str);
    }

    /**
     * 組成操作日誌字串(多筆)
     */
    public function getActionString_batch($result)
    {
        $return = [];
        foreach ($result as $data) {
            $str = [];
            foreach ($data as $key => $val) {
                //判斷有無欄位
                if (isset(static::$columnList[$key])) {
                    //判斷欄位有無靜態陣列
                    if (isset(static::${"{$key}List"}[$val])) {
                        $val = static::${"{$key}List"}[$val];
                    }

                    $str[] = static::$columnList[$key] . "=$val";
                }
            }
            $return[] = implode(';', $str);
        }

        return implode('<br>', $return);
    }

    public static $columnList = [];
}
