<?php

namespace App\Http\Middleware;

use View;
use Route;
use Closure;
use Models\Admin\AdminRole;
use App\Repositories\System\OperatorRepository;
use App\Repositories\Admin\AdminNavRepository;

class Share
{
    protected $operatorRepository;
    protected $adminNavRepository;

    public function __construct(
        OperatorRepository $operatorRepository,
        AdminNavRepository $adminNavRepository
    ) {
        $this->operatorRepository = $operatorRepository;
        $this->adminNavRepository = $adminNavRepository;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //讀取Version
        $json_string = file_get_contents("./version.json");
        $version = json_decode($json_string, true);
        $share['version'] = $version['version'];
        //側邊欄顯示與否
        $share['sidebar'] = $request->input('sidebar') !== null && $request->input('sidebar') == 0 ? false:true;
        //取得當前Route
        $route = Route::currentRouteName();
        $share['route'] = $route;
        $action = explode('.', $route);
        $share['controller'] = $action[0];

        //導航列表
        $where[] = ['status', '=', 1];
        $where[] = ['route', '<>', ''];
        $nav = $this->adminNavRepository->where($where)->order(['sort', 'asc'])->result()->toArray();
        $nav = array_column($nav, null, 'id');
        $share['allNav'] = $nav;
        //導航路徑
        $routes = [];
        foreach ($nav as $row) {
            if ($row['pid'] > 0) {
                $routes[$row['route']] = $row;
                if ($row['route1'] != '') {
                    $routes[$row['route1']] = $row;
                }
                if ($row['route2'] != '') {
                    $routes[$row['route2']] = $row;
                }
            }
        }
        $navid = isset($routes[$route]) ? $routes[$route]['id'] : 0;
        $share['breadcrumb'] = $this->getBreadcrumb($nav, $navid);
        //內頁Title
        $share['title'] = isset($routes[$route]) ? $routes[$route]['name'] : '首页';
        //導航樹狀
        $share['navList'] = $this->treeNav($nav);
        //導航權限
        $role = AdminRole::find(session('roleid'))->toArray();
        $permition = $role !== null && $role['allow_nav'] != '' ? json_decode($role['allow_nav'], true) : [];
        $permition = array_merge_recursive($permition, config('global.no_need_perm'));
        $share['allow_url'] = array_unique($permition);
        //運營商權限
        $operator = $this->operatorRepository->getList();
        if (session('roleid') == 1) {
            $share['allow_operator'] = $operator;
        } else {
            $allow_operator = $role !== null && $role['allow_operator'] != '' ? explode(',', $role['allow_operator']) : [];
            foreach ($allow_operator as $val) {
                $share['allow_operator'][$val] = $operator[$val];
            }
        }
        if (session('show_operator') === null) {
            session('show_operator', array_merge([0], array_keys($share['allow_operator'])));
        }
        //一頁顯示筆數
        $share['per_page'] = session('per_page') !== null ? session('per_page') : 20;

        View::share($share);
        return $next($request);
    }

    /**
     * 遞迴整理導航樹狀結構
     *
     * @param array $result 導航清單
     * @param integer $pid 上層導航ID
     * @return array
     */
    private function treeNav($result, $pid = 0)
    {
        $data = [];
        foreach ($result as $row) {
            if ($row['pid'] == $pid) {
                $row['sub'] = $this->treeNav($result, $row['id']);
                $row['subNavs'] = array_column($row['sub'], 'route');
                $data[] = $row;
            }
        }
        return $data;
    }

    /**
     * 遞迴取得導航路徑
     *
     * @param array $result 導航清單
     * @param integer $id 導航ID
     * @return array
     */
    private function getBreadcrumb($result, $id)
    {
        if ($id == 0) {
            return [];
        }

        $data = $this->getBreadcrumb($result, $result[$id]['pid']);
        $data[] = $result[$id];
        return $data;
    }
}
