<?php

namespace App\Repositories\Admin;

use App\Repositories\AbstractRepository;
use Models\Admin\AdminRole;

class AdminRoleRepository extends AbstractRepository
{
    public function __construct(AdminRole $entity)
    {
        parent::__construct($entity);
    }

    private function _preAction($row)
    {
        $row['path'] = 0;
        if ($row['pid'] > 0) {
            $parent = $this->row($row['pid']);
            $row['path'] = $parent['path'] . '-' . $row['pid'];
        }
        return $row;
    }

    public function create($row)
    {
        $row = $this->_preAction($row);
        return parent::create($row);
    }

    public function update($row, $id=0)
    {
        $row = $this->_preAction($row);
        return parent::update($row, $id);
    }

    public function _do_search()
    {
        if (isset($this->_search['name'])) {
            $this->db = $this->db->where('name', 'like', '%'.$this->_search['name'].'%');
        }

        if (isset($this->_search['operator'])) {
            $this->db = $this->db->whereRaw('find_in_set(' . $this->_search['operator'] . ',allow_operator)');
        }

        if (isset($this->_search['create_time1'])) {
            $this->db = $this->db->where('create_time', '>=', $this->_search['create_time1'] . ' 00:00:00');
        }
        if (isset($this->_search['create_time2'])) {
            $this->db = $this->db->where('create_time', '<=', $this->_search['create_time2'] . ' 23:59:59');
        }

        return $this;
    }

    public function getRoleList()
    {
        $where[] = ['id','>',1];
        $result = $this->where($where)->result()->toArray();
        return array_column($result, 'name', 'id');
    }
}
