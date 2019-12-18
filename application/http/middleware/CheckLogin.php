<?php

namespace app\http\middleware;

use app\common\model\Users;

class CheckLogin
{
    public function handle($request, \Closure $next)
    {
        $adminSession = config('admin_login');
        $adminCookie = config('admin_cookie');
        if (session("?{$adminSession}"))
            return $next($request);
        if (cookie("?{$adminCookie}")) {
            $token = cookie($adminCookie);
            $user = Users::where('token', $token)->find();
            if ($user) {
                rememberUserSession($user['id']);
                refreshUserCookie($user['id']);
                return $next($request);
            }
        }
        return redirect(url('/admin/login/index'));
    }
}
