<?php

namespace Models\User;

use Models\Model;

class User extends Model
{
    protected $table = 'user';

    protected $fillable = [
        'session',
        'user_name',
        'security_pwd',
        'user_pwd',
        'real_name',
        'mobile',
        'operator_id',
        'agent_id',
        'agent_pid',
        'agent_code',
        'type',
        'status',
        'user_group_id',
        'create_ip',
        'create_ip_info',
        'create_ua',
        'create_domain',
        'referrer_code',
    ];

    const MONEYTYPE = [
        0 => '主帐户',
        1 => '牛牛帐户'
    ];

    const TYPE = [
        0  => '会员用户',
        1  => '白名单用户',
        -1 => '牛牛Robot',
        -2 => '抢庄牛牛Robot',
    ];

    const STATUS = [
        0 => '正常',
        1 => '封号',
        2 => '冻结',
        3 => '标记',
    ];

    const STATUSCOLOR = [
        0 => '#000',
        1 => 'red',
        2 => 'blue',
        3 => 'goldenrod',
    ];

    const WHETHER = [
        1 => '是',
        0 => '否',
    ];

    const ACTIONTYPE = [
        0 => '人工加款',
        1 => '人工减款',
    ];

    const MODE = [
        1 => '是否首充',
        2 => '是否二充',
        4 => '是否提现',
        8 => '是否首次看投注熱度',
    ];

    public function operator()
    {
        return $this->belongsTo('Models\System\Operator');
    }

    public function userGroup()
    {
        return $this->belongsTo('Models\User\UserGroup');
    }

    public function agentCode()
    {
        return $this->belongsTo('Models\Agent\AgentGroup', 'agent_code', 'code');
    }

    public function agent()
    {
        return $this->belongsTo('Models\Admin\Admin', 'agent_id', 'id');
    }
}
