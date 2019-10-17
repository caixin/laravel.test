<?php

namespace App\Http\Controllers\Auth;

use Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * 登入後導向的位置
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * 通過驗證後的動作
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //更新登入數次及時間
        $user->login_time = date('Y-m-d H:i:s');
        $user->login_count++;
        $user->token = session('_token');
        $user->save();
        //登入log
        Admin\AdminLoginLog::create([
            'adminid'     => $user->id,
            'ip'          => $request->getClientIp(),
            'status'      => 1,
            'create_time' => date('Y-m-d H:i:s'),
            'create_by'   => $user->username,
        ]);
        //重要資訊寫入Session
        session([
            'id'       => $user->id,
            'username' => $user->username,
            'roleid'   => $user->roleid,
            'per_page' => 20,
        ]);
        //轉跳
        if (session('refer')) {
            $refer = session('refer');
            session(['refer'=>null]);
            return redirect($refer);
        } else {
            return redirect('home');
        }
    }

    /**
     * 定義帳號欄位
     *
     * @return string
     */
    public function username()
    {
        return 'mobile';
    }

    /**
     * 登出後動作
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        return redirect('login');
    }
}
