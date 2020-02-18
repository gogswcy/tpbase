<?php

namespace app\index\controller;

use app\common\commonclass\Jssdk;
use think\Controller;
class Index extends Controller
{
    protected $middleware = [
        'CheckWechatLogin' => ['except' => ['_empty']],
    ];

    public function _empty()
    {
        abort(404);
    }

    public function getIndex()
    {
        $sdk = new Jssdk();
        $wx = $sdk->info();
        return view('index/index', ['wx' => $wx]);
    }

    public function getPic()
    {
        return view('index/pictest');
    }
}
