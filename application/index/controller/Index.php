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

    /**
     * 判断是不是手机端
     */
    function is_mobile()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $is_pc = (strpos($agent, 'windows nt')) ? true : false;
        $is_mac = (strpos($agent, 'mac os')) ? true : false;
        $is_iphone = (strpos($agent, 'iphone')) ? true : false;
        $is_android = (strpos($agent, 'android')) ? true : false;
        $is_ipad = (strpos($agent, 'ipad')) ? true : false;

        if ($is_iphone) {
            return  true;
        }
        if ($is_android) {
            return  true;
        }
        if ($is_ipad) {
            return  true;
        }
        if ($is_pc) {
            return  false;
        }
        if ($is_mac) {
            return  false;
        }
    }

    /**
     * 微信图片上传
     */
    public function postWxupload()
    {
        $imagess_serverId = input('post.id');
        $sdk = new Jssdk();
        $access_token = $sdk->actionAccessToken();
        if(empty($imagess_serverId)){
            return json(['status' => 'error', 'msg' => '没有要上传的图片']);exit;
        }
        if(empty($access_token)){
            return json(['status' => 'error', 'msg' => '用户access_token不存在']);exit;
        }
        $save_path = 'uploads/images/'.date('Ymd').'/';
        if(!is_dir($save_path)){
            mkdir($save_path,0777,true);
        }
        $file_name = uniqid() . '.jpg';
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$imagess_serverId}";
        $raw = file_get_contents($url);
        file_put_contents($save_path.$file_name,$raw);
        if(!file_exists($save_path.$file_name))
        {
            return json(['status' => 'error', 'msg' => '上传图片失败']);
        }
        $showurl = config('public') . '/' . $save_path.$file_name;
        return ['status' => 'success', 'url' => '/'.$save_path.$file_name, 'showurl' => $showurl];
    }
}
