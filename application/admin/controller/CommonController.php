<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class CommonController extends Controller
{
    public function _empty()
    {
        abort(404);
    }

    public function getIndex()
    {
        return view('common/index');
    }
    public function postSave()
    {
        $data = input('post.data');
        halt($data);
    }
}
