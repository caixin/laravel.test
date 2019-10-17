<?php

namespace App\Repositories\Admin;

use App\Repositories\AbstractRepository;
use Models\Admin\Admin;

class AdminRepository extends AbstractRepository
{
    public function __construct(Admin $entity)
    {
        parent::__construct($entity);
    }

    private function _preAction($row)
    {
        if (isset($row['password'])) {
            if ($row['password'] != '') {
                $row['password'] = bcrypt($row['password']);
            } else {
                unset($row['password']);
            }
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
        if (isset($this->_search['username'])) {
            $this->db = $this->db->where('username', 'like', '%'.$this->_search['username'].'%');
        }

        if (isset($this->_search['mobile'])) {
            $this->db = $this->db->where('mobile', '=', $this->_search['mobile']);
        }

        if (isset($this->_search['status'])) {
            $this->db = $this->db->where('status', '=', $this->_search['status']);
        }

        if (isset($this->_search['create_time1'])) {
            $this->db = $this->db->where('create_time', '>=', $this->_search['create_time1'] . ' 00:00:00');
        }
        if (isset($this->_search['create_time2'])) {
            $this->db = $this->db->where('create_time', '<=', $this->_search['create_time2'] . ' 23:59:59');
        }

        return $this;
    }
}
