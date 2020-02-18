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
        $wx_login = "?".$this->session_name;
        if (session($wx_login)) {
            halt($request);
            return $next($request);
        } else {
            $sdk = new Jssdk();
            $wechat = $sdk->getUserAll();
            if (!$wechat)
                abort(404);
            session($wx_login, $wechat);
            return $next($request);
        }
    }
}
