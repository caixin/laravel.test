<?php

namespace App\Repositories;

use Models\Model;
use Illuminate\Support\Facades\Schema;

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

    public function __call($methods, $arguments)
    {
        return call_user_func_array([$this->entity, $methods], $arguments);
    }

    public function __construct(Model $entity)
    {
        $this->entity = $entity;
        $this->db     = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
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
                $this->db->join($join[0], $join[1], $join[2]);
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
        return $this->entity->find($id);
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

    public function last_query()
    {
        $queries = $this->entity->getQueryLog();
        return end($queries);
    }

    public function get_compiled_select()
    {
        $this->_do_action();
        $sql = $this->entity->tosql();
        $this->reset();
        return $sql;
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

        return $this->entity->insertGetId($data);
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
        //寫入操作日誌
        /*
        if ($this->is_action_log) {
            $sql_str = $this->db->insert_string($this->_table_name, $data);
            $message = $this->title . '(' . $this->getActionString($data) . ')';
            $this->admin_action_log_db->insert([
                'sql_str' => $sql_str,
                'message' => $message,
                'status'  => $id > 0 ? 1 : 0,
            ]);
        }
        */
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
            $this->db->update($data);
            $this->reset();
        } else {
            //$origin = $this->entity->find($id);
            $this->entity->find($id)->update($data);
        }
        //清除快取
        //$this->cache->redis->delete($this->entity->table()."-$id");

        //寫入操作日誌
        /*
        if ($this->is_action_log) {
            $sql_str = $this->db->update_string($this->_table_name, $row, [$this->_key => $row[$this->_key]]);
            $message = $this->title . '(' . $this->getActionString($row, $this->_array_diff($row, $origin)) . ')';
            $this->admin_action_log_db->insert([
                'sql_str' => $sql_str,
                'message' => $message,
                'status' => $num > 0 ? 1 : 0,
            ]);
        }
        */
    }

    public function update_batch($data)
    {
        $this->entity->updateBatch($data);
        //清除快取
        /*
        foreach ($data as $row) {
            $this->cache->redis->delete("$this->_table_name-".$row[$key]);
        }

        //寫入操作日誌
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
}
