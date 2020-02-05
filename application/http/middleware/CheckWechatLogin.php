<?php

namespace app\http\middleware;
use app\common\commonclass\Jssdk;

class CheckWechatLogin
{
    private $session_name;

    public function __construct()
    {
        $this->session_name = config('wx_login');
    }

    public function handle($request, \Closure $next)
    {
        $wx_login = $this->session_name;
        if (session('?{$wx_login}')) {
            // return $next($request);
        } else {
            // return $next($request);
        }
    }
}
