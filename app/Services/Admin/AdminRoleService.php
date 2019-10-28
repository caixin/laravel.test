<?php

namespace App\Services\Admin;

use App\Repositories\Admin\AdminRoleRepository;
use App\Repositories\Admin\AdminNavRepository;
use App\Repositories\System\OperatorRepository;

class AdminRoleService
{
    protected $adminRoleRepository;
    protected $adminNavRepository;
    protected $operatorRepository;

    public function __construct(
        AdminRoleRepository $adminRoleRepository,
        AdminNavRepository $adminNavRepository,
        OperatorRepository $operatorRepository
    ) {
        $this->adminRoleRepository = $adminRoleRepository;
        $this->adminNavRepository = $adminNavRepository;
        $this->operatorRepository = $operatorRepository;
    }

    public function list($input)
    {
        $search_params = param_process($input, ['id', 'asc']);
        $order         = $search_params['order'];
        $search        = $search_params['search'];
        $params_uri    = $search_params['params_uri'];

        $operator = $this->operatorRepository->getList();

        $result = $this->adminRoleRepository->search($search)
            ->order($order)->paginate(session('per_page'))->result();

        foreach ($result as $key => $row) {
            $allow_operator = [];
            if ($row['allow_operator'] != '') {
                foreach (explode(',', $row['allow_operator']) as $val) {
                    $allow_operator[] = $operator[$val];
                }
            }
            $row['allow_operator'] = implode(',', $allow_operator);
            $result[$key] = $row;
        }

        return [
            'result'     => $result,
            'search'     => $search,
            'order'      => $order,
            'params_uri' => $params_uri,
            'operator'   => $operator,
        ];
    }

    public function create($input)
    {
        $row = $this->adminRoleRepository->getEntity();
        if ($input['id'] > 0) {
            $row = $this->adminRoleRepository->row($input['id']);
        }
        $row['allow_operator'] = explode(',', $row['allow_operator']);
        $row['allow_nav'] = json_decode($row['allow_nav'], true);

        $navList = view()->shared('navList');
        return [
            'row'      => $row,
            'nav'      => session('roleid') == 1 ? $navList:$this->filterAllowNav($navList),
            'operator' => $this->operatorRepository->getList(),
        ];
    }

    public function store($row)
    {
        $row = array_map(function ($data) {
            return is_null($data) ? '':$data;
        }, $row);

        $row['allow_operator'] = implode(',', $row['allow_operator']);
        $row['allow_nav'] = json_encode($row['allow_nav']);
        
        $this->adminRoleRepository->create($row);
    }

    public function show($id)
    {
        $row = $this->adminRoleRepository->find($id);
        $row['allow_operator'] = explode(',', $row['allow_operator']);
        $row['allow_nav'] = json_decode($row['allow_nav'], true);

        $navList = view()->shared('navList');
        return [
            'row'      => $row,
            'nav'      => session('roleid') == 1 ? $navList:$this->filterAllowNav($navList),
            'operator' => $this->operatorRepository->getList(),
        ];
    }

    public function update($row, $id)
    {
        $row = array_map(function ($data) {
            return is_null($data) ? '':$data;
        }, $row);

        $row['allow_operator'] = implode(',', $row['allow_operator']);
        $row['allow_nav'] = json_encode($row['allow_nav']);

        $this->adminRoleRepository->update($row, $id);
    }

    public function save($row, $id)
    {
        $this->adminRoleRepository->save($row, $id);
    }

    public function destroy($id)
    {
        $this->adminRoleRepository->delete($id);
    }

    /**
     * 過濾掉沒權限的導航清單
     */
    public function filterAllowNav($navList)
    {
        foreach ($navList as $key => $row) {
            if (!in_array($row['url'], view()->shared('allow_url'))) {
                unset($navList[$key]);
                continue;
            }

            $row['sub'] = $this->filterAllowNav($row['sub']);
            $navList[$key] = $row;
        }

        return $navList;
    }
}
