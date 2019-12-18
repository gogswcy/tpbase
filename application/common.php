<?php
// 应用公共文件

use app\common\model\Users;

function rememberUserSession($id)
{
    $adminSession = config('admin_login');
    $user = Users::field('id, name, account')->get($id);
    session("$adminSession", $user);
}
function rememberUserCookie($id)
{
    $adminCookie = config('admin_cookie');
    $user = Users::get($id);
    $time = time() + 7*24*60*60;
    $user->expire_time = $time;
    $token = md5($user['account'] . time());
    $user->token = $token;
    $user->save();
    cookie($adminCookie, $token, 24*60*60*7);
}
function refreshUserCookie($id)
{
    $adminCookie = config('admin_cookie');
    $user = Users::get($id);
    $time = $user['expire_time'] - time();
    $token = md5($user['account'] . time());
    $user->token = $token; 
    $user->save();
    cookie($adminCookie, $token, $time);
}