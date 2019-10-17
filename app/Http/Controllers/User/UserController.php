<?php

namespace App\Http\Controllers\User;

use View;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserFormRequest;
use App\Services\User\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        return view('user.index', $this->userService->list($request->input()));
    }

    public function search(Request $request)
    {
        return redirect(get_search_uri($request->input(), 'user'));
    }

    public function create(Request $request)
    {
        View::share('sidebar', false);
        return view('user.create', $this->userService->create($request->input()));
    }

    public function store(UserCreateRequest $request)
    {
        $this->userService->store($request->post());

        session()->flash('message', '添加成功!');
        return "<script>parent.window.layer.close();parent.location.reload();</script>";
    }

    public function show($id)
    {
        View::share('sidebar', false);
        return view('user.show', $this->userService->show($id));
    }

    public function edit($id)
    {
        View::share('sidebar', false);
        return view('user.edit', $this->userService->show($id));
    }

    public function update(UserFormRequest $request, $id)
    {
        $this->userService->update($request->post(), $id);

        session()->flash('message', '编辑成功!');
        return "<script>parent.window.layer.close();parent.location.reload();</script>";
    }
}
