<?php

namespace Models\Admin;

use Models\Model;

class AdminSession extends Model
{
    const UPDATED_AT = null;

    protected $table = 'admin_session';
    protected $primaryKey = 'adminid';

    protected $fillable = [
        'adminid',
        'username',
        'session_id',
    ];

    public function insert($options=[])
    {
        $this->create_by = session('username');
        parent::insert($options);
    }
}
