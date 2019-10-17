<?php

namespace Models\System;

use Models\Model;

class Operator extends Model
{
    protected $table = 'operator';

    const STATUS = [
        0 => '关闭',
        1 => '开启',
    ];
}
