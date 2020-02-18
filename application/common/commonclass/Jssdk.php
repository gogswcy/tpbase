<?php

namespace app\common\commonclass;

class Jssdk
{
    private $appid = '';
    private $appsecret = '';

    public function __construct()
    {
        $this->appid = config('wx_appid');
        $this->appsecret = config('wx_appsecret');
        $this->tokenPath = config('wx_token') . config('wx_appid') . 'access_token.json';
        $this->ticketPath = config('wx_ticket') . config('wx_appid') . 'jsapi_ticket.json';
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
        $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
        $wx['url'] = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
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

    private function actionAccessToken($flag = true)
    {
        $file = file_get_contents($this->tokenPath);
        $info = json_decode($file, 1);
        if ($flag) {
            if ($info && $info['expire_time'] > time())
                return $info['access_token'];
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->appsecret;
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

    /**
     * 获取微信用户信息，判断有没有code，有使用code换取access_token，没有去获取code。
     * @return array 微信用户信息数组
     */
    public function getUserAll()
    {
        //没有code，去微信接口获取code码
        if (!isset($_GET['code'])) {
            if (config('wx_unite')) {
                if (config('public')) {
                    $redirect_uri = request()->domain() . $_SERVER['PHP_SELF'];
                } else {
                    $redirect_uri = request()->domain() . $_SERVER['PATH_INFO'];
                }
                $callback = 'http://salt.s2.qyingyong.com/get-weixin-code.html?appid=' . $this->appid . '&scope=snsapi_userinfo&state=STATE&' . 'redirect_uri=' . $redirect_uri;
            } else {
                //微信服务器回调url，这里是本页url
                $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
                $callback = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            }
            $this->getCode($callback);
        } else {
            //获取code后跳转回来到这里了
            $code = $_GET['code'];
            //获取网页授权access_token和用户openid
            $data = $this->getAccessToken($code);
            if (isset($data['errcode'])) {
                if ($data['errcode'] == 40163) {
                    $redirect_url = request()->domain() . config('public') . $_SERVER["PHP_SELF"];
                    return redirect($redirect_url);
                } else {
                    echo 'errcode: ' . $data['errcode'] . '<br>' . 'errmsg:' . $data['errmsg'];
                    return;
                }
            }
            //获取微信用户信息      
            $data_all = $this->getUserInfo($data['access_token'], $data['openid']);
            return $data_all;
        }
    }

    /**
     * 获取code
     */
    private function getCode($callback)
    {
        $appid = $this->appid;
        $scope = 'snsapi_userinfo';
        //唯一ID标识符绝对不会重复
        $state = md5(uniqid(rand(), TRUE));
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . urlencode($callback) .  '&response_type=code&scope=' . $scope . '&state=' . $state . '#wechat_redirect';
        header("Location:$url");
        die;
    }

    /**
     * 3、使用code换取access_token
     * @param string 用于换取access_token的code，微信提供
     * @return array access_token和用户openid数组
     */
    private function getAccessToken($code)
    {
        $appid = $this->appid;
        $appsecret = $this->appsecret;
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $appsecret . '&code=' . $code . '&grant_type=authorization_code';
        $user = json_decode(file_get_contents($url));

        //返回的json数组转换成array数组
        $data = json_decode(json_encode($user), true);
        return $data;
    }

    /**
     * 4、使用access_token获取用户信息
     * @param string access_token
     * @param string 用户的openid
     * @return array 用户信息数组
     */
    private function getUserInfo($access_token, $openid)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
        $user = json_decode(file_get_contents($url));
        if (isset($user->errcode)) {
            echo 'error:' . $user->errcode . '<hr>msg  :' . $user->errmsg;
            exit;
        }
        //返回的json数组转换成array数组
        $data = json_decode(json_encode($user), true);
        return $data;
    }
}
