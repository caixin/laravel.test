<?php

namespace App\Repositories\Agent;

use App\Repositories\AbstractRepository;
use Models\Agent\AgentCode;

class AgentCodeRepository extends AbstractRepository
{
    public function __construct(AgentCode $entity)
    {
        parent::__construct($entity);
    }

    public function _do_search()
    {
        if (isset($this->_search['uid'])) {
            $this->db = $this->db->where('uid', '=', $this->_search['uid']);
            unset($this->_search['uid']);
        }

        if (isset($this->_search['type'])) {
            $this->db = $this->db->where('type', '=', $this->_search['type']);
            unset($this->_search['type']);
        }

        if (isset($this->_search['level'])) {
            $this->db = $this->db->where('level', '=', $this->_search['level']);
            unset($this->_search['level']);
        }

        if (isset($this->_search['code'])) {
            $this->db = $this->db->where('code', '=', $this->_search['code']);
            unset($this->_search['code']);
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
}
