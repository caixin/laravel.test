<?php

namespace App\Http\Controllers;

use Models\Admin;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $result = Admin\Admin::insertGetId([
            'username' => 'test',
            'password' => 'test',
            'mobile'   => '12121212122',
            'login_ip' => 'ips',
        ]);
        
        $value = session('key');

        return view('home');
    }
}
