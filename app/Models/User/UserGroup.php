<?php

namespace Models\User;

use Models\Model;

class UserGroup extends Model
{
    protected $table = 'user_group';

    const STATUS = [
        0 => '关闭',
        1 => '开启',
    ];

    public function operator()
    {
        return $this->belongsTo('Models\System\Operator');
    }
}
