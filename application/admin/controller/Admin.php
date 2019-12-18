<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\common\model\Users;
use think\Validate;

class Admin extends Controller
{
    public function index()
    { }

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

    public function changePwd()
    {
        $m = request()->method();
        if ($m === 'GET') {
            return view('user/changepwd');
        } else if ($m === 'POST') {
            $old = trim(input('post.old'));
            $new = trim(input('post.new'));
            $rule = [
                'new' => 'require|length:6,15',
                'old' => 'require|length:6,15'
            ];
            $msg = [
                'new' => '请输入6-15位的新密码',
                'old' => '密码请输入6-15位'
            ];
            $validate = Validate::make($rule, $msg);
            if (!$validate->check(['old' => $old, 'new' => $new]))
                return json(['status' => 'error', 'msg' => $validate->getError()]);

            $user = Users::get(session(config('admin_login'))['id']);
            $passwordStore = $user['password'];
            if (md5($old) != $passwordStore)
                return json(['status' => 'error', 'msg' => '原密码不正确']);

            $user->password = md5($new);
            $res = $user->save();
            if ($res)
                return json(['status' => 'success']);
            else
                return json(['status' => 'error', 'msg' => '保存失败']);
        }
    }
}
