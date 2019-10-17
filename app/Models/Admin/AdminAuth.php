<?php

namespace Models\Admin;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminAuth extends Authenticatable
{
    const UPDATED_AT = 'update_time';
    
    protected $table = 'admin';
}
