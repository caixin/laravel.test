<?php

namespace Models\Admin;

use Models\Model;

class AdminLoginLog extends Model
{
    const UPDATED_AT = null;
    
    protected $table = 'admin_login_log';

    protected $fillable = [
        'adminid',
        'ip',
        'status',
    ];
}
