<?php

namespace App\Http\Controllers;

use Models\Admin;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $result = Admin\Admin::where('roleid', 1)->take(3)->get();
        print_r($result);
        return view('home');
    }
}
