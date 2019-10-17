<?php

namespace App\Repositories\System;

use App\Repositories\AbstractRepository;
use Models\System\Operator;

class OperatorRepository extends AbstractRepository
{
    public function __construct(Operator $entity)
    {
        parent::__construct($entity);
    }

    public function _do_search()
    {
        if (isset($this->_search['ids'])) {
            $this->db = $this->db->whereIn('id', $this->_search['ids']);
            unset($this->_search['ids']);
        }

        if (isset($this->_search['domain_url'])) {
            $this->db = $this->db->whereRaw("FIND_IN_SET('".$this->_search['domain_url']."', domain_url)");
            unset($this->_search['domain_url']);
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
     * 取得營運商清單
     *
     * @param int $all 是否回傳全部 0:只回傳有權限的 1:全部
     */
    public function getList($all=1)
    {
        $search['status'] = 1;
        if ($all == 0) {
            $search['ids'] = session('show_operator');
        }
        $result = $this->search($search)->result()->toArray();
        $result = array_column($result, 'name', 'id');
        return $result;
    }
}
