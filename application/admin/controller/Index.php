<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Index extends Controller
{
    public function index()
    {
        $sessionName = config('admin_login');
        $session = session($sessionName);
        return view('index/index', ['session' => $session]);
    }

    public function welcome()
    {
        return view('index/welcome');
    }

    /**
     * 上传图片
     */
    public function uploadimg()
    {
        $base = input('post.base');
        $imageName = mt_rand(100000, 999999) . time() . mt_rand(100000, 999999) . '.jpg';
        if (strstr($base, ",")) {
            $image = explode(',', $base);
            $image = $image[1];
        } else {
            $image = $base;
        }
        $path = config('upload_dir');
        $path = $path . date("Ymd", time());

        //判断目录是否存在 不存在就创建
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        //图片名字
        $imageSrc = $path . "/" . $imageName;
        //返回的是字节数
        $r = file_put_contents($imageSrc, base64_decode($image));
        if (!$r) {
            return array('status' => 'error');
        } else {
            if (config('public'))
                $showurl = config('public') . '/' . $imageSrc;
            else
                $showurl = '/' . $imageSrc;

            $images = new Images();
            $images->url = '/' . $imageSrc;
            $images->status = 0;
            $images->create_time = time();
            $res = $images->save();
            if (!$res) {
                unlink($imageSrc);
                return json(['status' => 'error']);
            }
            return array('status' => 'success', 'url' => '/' . $imageSrc, 'showurl' => $showurl, 'id' => $images['id']);
        }
    }
}
