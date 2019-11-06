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

    /**
     * For 操作日誌用
     *
     * @var array
     */
    public static $columnList = [
        'id'             => '流水号',
        'name'           => '角色名称',
        'allow_operator' => '运营商权限',
        'allow_nav'      => '导航权限',
    ];
}
