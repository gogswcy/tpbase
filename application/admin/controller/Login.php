<?php

namespace app\admin\controller;

use app\common\model\Users;
use think\captcha\Captcha;
use think\Controller;
use think\Request;
use think\Validate;

class Login extends Controller
{
    protected $session_name;
    protected $cookie_name;

    public function __construct()
    {
        $this->session_name = config('admin_login');
        $this->cookie_name = config('admin_cookie');
    }

    public function index()
    {
        if (session("" . $this->session_name))
            return redirect(url('/admin/index/index'));
        if (cookie("?" . $this->cookie_name)) {
            $token = cookie($this->cookie_name);
            $user = Users::where('token', $token)->find();
            if ($user) {
                rememberUserSession($user['id']);
                refreshUserCookie($user['id']);
                return redirect(url('/admin/index/index'));
            }
        }
        return view('login/login');
    }

    public function login()
    {
        $account = trim(input('post.account'));
        $password = trim(input('post.password'));
        $captchaStr = trim(input('post.captcha'));
        $online = input('post.online');
        $rule = [
            'account' => 'require|max:15',
            'password' => 'require|length:6,12',
            'captcha' => 'require'
        ];
        $msg = [
            'account.require' => '请填写账号',
            'account.max' => '账号不大于15位',
            'password.require' => '请填写密码',
            'password.length' => '密码长度6-12位',
            'captcha.require' => '请填写验证码'
        ];
        $validata = Validate::make($rule, $msg);
        $res = $validata->check(['account' => $account, 'password' => $password, 'captcha' => $captchaStr]);
        if (!$res)
            return json(['status' => 'error', 'msg' => $validata->getError()]);

        $captcha = new Captcha();
        if (!$captcha->check($captchaStr))
            return json(['status' => 'error', 'msg' => '验证码错误']);
        $user = Users::where('account', $account)->find();
        if (!$user)
            return json(['status' => 'error', 'msg' => '账号或密码错误']);
        $passowrdStore = $user->password;
        if (md5($password) != $passowrdStore)
            return json(['status' => 'error', 'msg' => '账号或密码错误']);

        rememberUserSession($user['id']);
        if ($online === '1') {
            rememberUserCookie($user['id']);
        }
        $this->addLog('后台登录');
        return json(['status' => 'success']);
    }

    public function logout()
    {
        $this->addLog('退出登录');
        session(null);
        cookie($this->cookie_name, null);
        return redirect(url('/admin/login/index'));
    }

    public function create()
    { }

    public function save(Request $request)
    { }

    public function read($id)
    { }

    public function edit($id)
    { }

    public function update(Request $request, $id)
    { }

    public function delete($id)
    { }
}
