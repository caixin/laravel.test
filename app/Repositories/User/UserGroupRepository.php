<?php

namespace App\Repositories\User;

use App\Repositories\AbstractRepository;
use Models\User\UserGroup;

class UserGroupRepository extends AbstractRepository
{
    public function __construct(UserGroup $entity)
    {
        parent::__construct($entity);
    }

    public function _do_search()
    {
        if (isset($this->_search['operator_id'])) {
            $this->db = $this->db->where('operator_id', '=', $this->_search['operator_id']);
            unset($this->_search['operator_id']);
        }

        if (isset($this->_search['status'])) {
            $this->db = $this->db->where('status', '=', $this->_search['status']);
            unset($this->_search['status']);
        }

        if (isset($this->_search['create_time1'])) {
            $this->db = $this->db->where('create_time', '>=', $this->_search['create_time1'] . ' 00:00:00');
            unset($this->_search['create_time1']);
        }
        if (isset($this->_search['create_time2'])) {
            $this->db = $this->db->where('create_time', '<=', $this->_search['create_time2'] . ' 23:59:59');
            unset($this->_search['create_time2']);
        }

        return $this;
    }

    /**
     * 取得群組清單
     *
     * @param integer $operator_id 是否回傳全部 0:只回傳有權限的 1:全部
     */
    public function getList($operator_id=0)
    {
        $search['status'] = 1;
        if ($operator_id > 0) {
            $search['operator_id'] = $operator_id;
        }
        $result = $this->search($search)->result()->toArray();
        return array_column($result, 'name', 'id');
    }
}
