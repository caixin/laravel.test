<?php

namespace App\Repositories\User;

use App\Repositories\AbstractRepository;
use App\Repositories\System\Ip2locationRepository;
use Models\User\User;

class UserRepository extends AbstractRepository
{
    protected $ip2locationRepository;

    public function __construct(User $entity, Ip2locationRepository $ip2locationRepository)
    {
        parent::__construct($entity);
        $this->ip2locationRepository = $ip2locationRepository;
    }

    public function create($row)
    {
        if (isset($row['user_pwd'])) {
            if ($row['user_pwd'] != '') {
                $row['user_pwd'] = $this->userPwdEncode($row['user_pwd']);
            } else {
                unset($row['user_pwd']);
            }
        }
        if (isset($row['security_pwd'])) {
            if ($row['security_pwd'] != '') {
                $row['security_pwd'] = $this->userPwdEncode($row['security_pwd']);
            } else {
                unset($row['security_pwd']);
            }
        }

        $ip = request()->getClientIp();
        $ip_info = $this->ip2locationRepository->getIpData($ip);
        $ip_info = $ip_info ?? [];

        $row['create_ip']      = $ip;
        $row['create_ip_info'] = json_encode($ip_info);
        $row['create_ua']      = request()->server('HTTP_USER_AGENT');
        $row['create_domain']  = request()->getHttpHost();
        $row['session']        = $this->getSession(0, $ip);
        $row['referrer_code']  = $this->getInvitationCode();

        if (!isset($row['operator_id'])) {
            $row['operator_id'] = isset($this->operator_id) ? $this->operator_id : 1;
        }

        return parent::create($row);
    }

    public function update($row, $id=0)
    {
        $data = $this->row($id);
        if (isset($row['user_pwd'])) {
            if ($row['user_pwd'] != '') {
                $row['user_pwd'] = $this->userPwdEncode($row['user_pwd']);
            } else {
                unset($row['user_pwd']);
            }
        }
        if (isset($row['security_pwd'])) {
            if ($row['security_pwd'] != '') {
                $row['security_pwd'] = $this->userPwdEncode($row['security_pwd']);
            } else {
                unset($row['security_pwd']);
            }
        }

        $num = parent::update($row, $id);
        //如果邀請碼變更而更換代理 則下級也跟著變更代理ID
        if (isset($row['agent_id']) && $data['agent_id'] != $row['agent_id']) {
            $uids = $this->getAgentAllSubUID($row['id']);
            $this->search(['ids'=>$uids])->update([
                'agent_id' => $row['agent_id']
            ]);
        }

        return $num;
    }

    public function _do_search()
    {
        if (isset($this->_search['operator_id'])) {
            $this->db = $this->db->whereIn('operator_id', [0,$this->_search['operator_id']]);
        } elseif (session('is_login') && session('show_operator')) {
            $this->db = $this->db->whereIn("operator_id", session('show_operator'));
        }
        if (session('is_agent') == 1) {
            $this->db = $this->db->where('agent_id', session('id'));
        }

        if (isset($this->_search['ids'])) {
            $this->db = $this->db->whereIn('id', $this->_search['ids']);
        }

        if (isset($this->_search['user_name'])) {
            $this->db = $this->db->where('user_name', 'like', '%'.$this->_search['user_name'].'%');
        }

        if (isset($this->_search['type'])) {
            $this->db = $this->db->where('type', '=', $this->_search['type']);
        }

        if (isset($this->_search['status'])) {
            $this->db = $this->db->where('status', '=', $this->_search['status']);
        }

        if (isset($this->_search['mode'])) {
            $this->db = $this->db->where('mode', '&', $this->_search['mode']);
        }

        if (isset($this->_search['user_group_id'])) {
            $this->db = $this->db->where('user_group_id', '=', $this->_search['user_group_id']);
        }

        if (isset($this->_search['last_active_time1'])) {
            $this->db = $this->db->where('last_active_time', '>=', $this->_search['last_active_time1']);
        }
        if (isset($this->_search['last_active_time2'])) {
            $this->db = $this->db->where('last_active_time', '<=', $this->_search['last_active_time2']);
        }

        if (isset($this->_search['create_time1'])) {
            $this->db = $this->db->where('create_time', '>=', $this->_search['create_time1'] . ' 00:00:00');
        }
        if (isset($this->_search['create_time2'])) {
            $this->db = $this->db->where('create_time', '<=', $this->_search['create_time2'] . ' 23:59:59');
        }

        return $this;
    }

    /**
     * 取得Session
     */
    private function getSession($uid, $ip)
    {
        while (true) {
            $session = md5($uid . $ip . randPwd(6));
            $row = $this->where([['session','=',$session]])->result_one();
            if ($row === null) {
                break;
            }
        }
        return $session;
    }

    /**
     * 生成邀請碼
     */
    private function getInvitationCode()
    {
        while (true) {
            $code = randPwd(6);
            $row = $this->where([['referrer_code','=',$code]])->result_one();
            if ($row === null) {
                break;
            }
        }
        return $code;
    }

    /**
     * 生成用戶密碼
     *
     * @param integer $pwd 密碼
     * @return string 加密密碼
     */
    private function userPwdEncode($pwd)
    {
        return md5(md5($pwd) . 'sb');
    }

    /**
     * 取得下級所有UID
     */
    public function getAgentAllSubUID($uid, $uids = [], $starttime = '', $endtime = '')
    {
        $uids[] = $uid;
        $search['type'] = 0;
        $search['agent_pid'] = $uid;
        if ($starttime != '') {
            $search['create_time1'] = $starttime;
        }
        if ($endtime != '') {
            $search['create_time2'] = $endtime;
        }
        $result = $this->select(['id'])->search($search)->result();
        foreach ($result as $row) {
            $uids = $this->getAgentAllSubUID($row->id, $uids, $starttime, $endtime);
        }
        return $uids;
    }
}
