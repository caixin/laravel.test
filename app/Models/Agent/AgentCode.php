<?php

namespace Models\Agent;

use Models\Model;

class AgentCode extends Model
{
    protected $table = 'agent_code';

    const TYPE = [
        1  => '代理',
        2  => '玩家',
    ];

    public function user()
    {
        return $this->belongsTo('Models\User\User', 'uid');
    }
}
