<?php
namespace app\common\commonclass;
class Jssdk
{
    private $appid = '';
    private $appsecret = '';

    public function __construct()
    {
        $this->appid = config('wx_appid');
        $this->appsecret = config('wx_secret');
        $this->tokenPath = config('wx_tokenPath');
        $this->ticketPath = config('wx_ticketPath');
    }

    /**
     * 获取参数
     */
    public function info()
    {
        //时间戳
        $wx['timestamp'] = time();
        //生成签名的随机串
        $wx['nonceStr'] = md5(time());
        //jsapi_ticket是公众号用于调用微信JS接口的临时票据。正常情况下，jsapi_ticket的有效期为7200秒，通过access_token来获取。
        $wx['jsapi_ticket'] = $this->actionTicket();
        //分享的地址，注意：这里是指当前网页的URL，不包含#及其后面部分
        $wx['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $string = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $wx['jsapi_ticket'], $wx['nonceStr'], $wx['timestamp'], $wx['url']);
        //生成签名
        $wx['signature'] = sha1($string);
        $wx['appId'] = $this->appid;
        return $wx;
    }

    private function actionTicket()
    {
        $file = file_get_contents($this->ticketPath);
        $info = json_decode($file, 1);

        if ($info && $info['expire_time'] > time()) return $info['jsapi_ticket'];

        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $this->actionAccessToken() . "&type=jsapi";
        $info = file_get_contents($url);
        $info = json_decode($info, 1);
        if ($info) {
            $info['expire_time'] = time() + $info['expires_in'];
            $info['jsapi_ticket'] = $info['ticket'];
            file_put_contents($this->ticketPath, json_encode($info));
            return $info['ticket'];
        } else {
            return '失败';
        }
    }

    private function actionAccessToken($flag=true)
    {
        $file = file_get_contents($this->tokenPath);
        $info = json_decode($file, 1);
        if ($flag) {
            if ($info && $info['expire_time'] > time())
                return $info['access_token'];
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->appsecret;
        $info = file_get_contents($url);
        $info = json_decode($info, 1);

        if ($info) {
            $info['expire_time'] = time() + $info['expires_in'];
            file_put_contents($this->tokenPath, json_encode($info));
            return $info['access_token'];
        } else {
            return '失败';
        }
    }
}