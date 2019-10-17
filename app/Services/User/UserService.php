<?php

namespace App\Services\User;

use App\Repositories\User\UserRepository;
use App\Repositories\User\UserGroupRepository;
use App\Repositories\System\OperatorRepository;
use App\Repositories\Agent\AgentCodeRepository;

class UserService
{
    protected $userRepository;
    protected $userGroupRepository;
    protected $operatorRepository;
    protected $agentCodeRepository;

    public function __construct(
        UserRepository $userRepository,
        UserGroupRepository $userGroupRepository,
        OperatorRepository $operatorRepository,
        AgentCodeRepository $agentCodeRepository
    ) {
        $this->userRepository      = $userRepository;
        $this->userGroupRepository = $userGroupRepository;
        $this->operatorRepository  = $operatorRepository;
        $this->agentCodeRepository = $agentCodeRepository;
    }

    public function list($input)
    {
        $search_params = param_process($input, ['id', 'desc']);
        $order         = $search_params['order'];
        $search        = $search_params['search'];
        $params_uri    = $search_params['params_uri'];

        $operator = $this->operatorRepository->getList(0);
        if (!isset($search['operator_id'])) {
            foreach ($operator as $operator_id => $operator_name) {
                $search['operator_id'] = $operator_id;
                break;
            }
        }

        $result = $this->userRepository->search($search)
            ->order($order)->paginate(session('per_page'))
            ->result();

        return [
            'result'     => $result,
            'search'     => $search,
            'order'      => $order,
            'params_uri' => $params_uri,
            'operator'   => $operator,
            'user_group' => $this->userGroupRepository->getList($search['operator_id']),
        ];
    }

    public function create($input)
    {
        return [
            'row'        => $this->userRepository->getEntity(),
            'user_group' => $this->userGroupRepository->getList($input['operator_id']),
            'operator'   => $this->operatorRepository->getList(0),
        ];
    }

    public function store($row)
    {
        if ($row['super_user'] ?? '' == 1) {
            $row['type'] = 1;
        }

        $agent = $this->agentCodeRepository->search(['code'=>$row['agent_code']])->result_one();
        $row['operator_id'] = $agent->user->operator_id;
        $row['agent_id']    = $agent->user->agent_id;
        $row['agent_pid']   = $agent->user->id;
        $this->userRepository->create($row);
    }

    public function show($id)
    {
        $row = $this->userRepository->find($id);

        return [
            'row'        => $row,
            'user_group' => $this->userGroupRepository->getList($row['operator_id']),
            'agent'      => [],
        ];
    }

    public function update($row, $id)
    {
        if ($row['super_user'] ?? '' == 1) {
            $row['type'] = 1;
            $row['agent_code'] = '';
        }

        $agent = $this->agentCodeRepository->search(['code'=>$row['agent_code']])->result_one();
        $row['operator_id'] = $agent->user->operator_id;
        $row['agent_id']    = $agent->user->agent_id;
        $row['agent_pid']   = $agent->user->id;
        $this->userRepository->update($row, $id);
    }
}
