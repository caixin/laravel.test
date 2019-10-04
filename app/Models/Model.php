<?php

namespace Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function insert($options=[])
    {
        $this->create_by = 'test1';
        $this->update_by = 'test2';
        parent::insert($options);
    }

    public function save(array $options = [])
    {
        $this->update_by = 'test';
        parent::save($options);
    }
}
