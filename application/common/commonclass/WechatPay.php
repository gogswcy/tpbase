<?php
namespace app\common\commonclass;
class WechatPay
{
    protected $appid;
    protected $mch_id;
    protected $key;
    protected $certPath;
    protected $keyPath;
    public function __construct()
    {
        $this->appid = config('wx_appid');
        $this->mch_id = config('mchid');
        $this->key = config('key');
        $this->certPath = config('certPath');
        $this->keyPath = config('keyPath');
    }
    // curl
    function curl($method, $url, $data = '', $cert = '', $certPath = '', $keyPath = '')
    {
        $ch = curl_init();
        //设置要请求的地址
        curl_setopt($ch, CURLOPT_URL, $url);
        //不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //设置
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if ($method == 'post') {
            //设置请求头信息
            curl_setopt($ch, CURLOPT_HTTPHEADER, []);
            //设置请求体
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($cert) {
                curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
                curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
                curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
                curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        //发送请求
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
    // 预支付
    public function prepay($openid, $notify_url, $out_trade_no, $total_fee, $body)
    {
        $xml = '<xml>
                <appid>%s</appid>
                <body>%s</body>
                <mch_id>%s</mch_id>
                <nonce_str>%s</nonce_str>
                <notify_url>%s</notify_url>
                <openid>%s</openid>
                <out_trade_no>%s</out_trade_no>
                <spbill_create_ip>%s</spbill_create_ip>
                <total_fee>%s</total_fee>
                <trade_type>JSAPI</trade_type>
                <sign>%s</sign>
                </xml>';
        $nonce_str = md5(time());
        $spbill_create_ip = $_SERVER['REMOTE_ADDR'];
        $total_fee = $total_fee * 100;
        $config = [
            'appid' => $this->appid,
            'body' => $body,
            'mch_id' => $this->mch_id,
            'nonce_str' => $nonce_str,
            'notify_url' => $notify_url,
            'openid' => $openid,
            'out_trade_no' => $out_trade_no,
            'spbill_create_ip' => $spbill_create_ip,
            'total_fee' => $total_fee,
            'trade_type' => 'JSAPI'
        ];
        // 排序
        ksort($config);
        // 拼接
        $str = '';
        foreach ($config as $k => $v) {
            $str  = $str . $k . '=' . $v . '&';
        }
        // 拼接
        $str = $str . 'key=' . $this->key;
        // 生成签名
        $sign = strtoupper(MD5($str));
        // 格式化字符串
        $xml = sprintf($xml, $this->appid, $body, $this->mch_id, $nonce_str, $notify_url, $openid, $out_trade_no, $spbill_create_ip, $total_fee, $sign);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        // curl访问, 下单
        $res = $this->curl('post', $url, $xml);
        // 结果生成数组
        $res = json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), 1);
        // 判断是否成功
        if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
            $timeStamp = time();
            $nonceStr = MD5(time());
            $paySign = md5('appId=' . $this->appid . '&nonceStr=' . $nonceStr . '&package=prepay_id=' . $res['prepay_id'] . '&signType=MD5&timeStamp=' . $timeStamp . '&key=' . $this->key);
            $arr['timeStamp'] = $timeStamp;
            $arr['nonceStr'] = $nonceStr;
            $arr['paySign'] = $paySign;
            $arr['prepay_id'] = 'prepay_id='.$res['prepay_id'];
            return ['status' => 'success', 'data' => $arr];
        } else {
            return ['status' => 'error', 'data' => $res];
        }
    }
    // 查询订单
    public function query($out_trade_no)
    {
        $xml = '<xml>
        <appid>%s</appid>
        <mch_id>%s</mch_id>
        <nonce_str>%s</nonce_str>
        <out_trade_no>%s</out_trade_no>
        <sign>%s</sign>
        </xml> ';
        $nonce_str = md5(time());
        $str = 'appid=' . $this->appid . '&mch_id=' . $this->mch_id . '&nonce_str=' . $nonce_str . '&out_trade_no=' . $out_trade_no . '&key=' . $this->key;
        // 生成签名
        $sign = strtoupper(MD5($str));
        // 格式化字符串
        $xml = sprintf($xml, $this->appid, $this->mch_id, $nonce_str, $out_trade_no, $sign);
        $url = 'https://api.mch.weixin.qq.com/pay/orderquery';
        // curl访问, 下单
        $res = $this->curl('post', $url, $xml);
        // 如果没有返回报错
        if (!$res) {
            return ['status' => 'error', 'msg' => '未查询到订单信息'];
        }
        // 结果生成数组
        $res = json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), 1);
        if ((isset($res['return_code']) && $res['return_code'] == 'SUCCESS') && (isset($res['result_code']) && $res['result_code'] == 'SUCCESS')) {
            if ($res['trade_state'] == 'SUCCESS') {
                return ['status' => 'success', 'data' => $res];
            } else {
                return ['status' => 'error', 'msg' => $res['return_msg']];
            }
        }
    }
    // 企业提现到零钱
    public function cash($openid, $partner_trade_no, $amount, $description, $certPath, $keyPath)
    {
        // 访问地址
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $str_origion = '<xml>
                <mch_appid>%s</mch_appid>
                <mchid>%s</mchid>
                <nonce_str>%s</nonce_str>
                <partner_trade_no>%s</partner_trade_no>
                <openid>%s</openid>
                <check_name>%s</check_name>
                <amount>%s</amount>
                <desc>%s</desc>
                <spbill_create_ip>%s</spbill_create_ip>
                <sign>%s</sign>
                </xml>';
        $nonce_str = MD5(time());
        $partner_trade_no = md5(mt_rand(1000, 9999) . time() . mt_rand(10000, 99999));
        $ip = $_SERVER['REMOTE_ADDR'];
        $config = [
            'mch_appid' => $this->appid,
            'mchid' => $this->mch_id,
            'nonce_str' => $nonce_str,
            'partner_trade_no' => $partner_trade_no,
            'openid' => $openid,
            'check_name' => 'NO_CHECK',
            'amount' => $amount,
            'desc' => $description,
            'spbill_create_ip' => $ip,
        ];
        // 排序
        ksort($config);
        // 拼接
        $str = '';
        foreach ($config as $k => $v) {
            $str  = $str . $k . '=' . $v . '&';
        }
        // 拼接
        $str = $str . 'key=' . $this->key;
        // 生成签名
        $signature = strtoupper(MD5($str));
        $str = sprintf(
            $str_origion,
            $this->appid,
            $this->mch_id,
            $nonce_str,
            $partner_trade_no,
            $openid,
            'NO_CHECK',
            $amount,
            $description,
            $ip,
            $signature
        );
        $res = $this->curl('post', $url, $str, 1, $this->certPath, $this->keyPath);
        // 结果生成数组
        $res = json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), 1);
        if (!$res) {
            return ['status' => 'error', 'msg' => '提交失败'];
        }
        if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
            return ['status' => 'success', 'data' => $res];
        } else {
            return ['status' => 'error', 'msg' => $res];
        }
    }
    // 退款
    public function refund($notify_url, $out_refund_no, $out_trade_no, $refund_fee, $total_fee)
    {
        $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
        $xml = '<xml>
        <appid>%s</appid>
        <mch_id>%s</mch_id>
        <nonce_str>%s</nonce_str>
        <notify_url>%s</notify_url>
        <out_refund_no>%s</out_refund_no>
        <out_trade_no>%s</out_trade_no>
        <refund_fee>%s</refund_fee>
        <total_fee>%s</total_fee>
        <sign>%s</sign>
        </xml>';
        $nonce_str = MD5(time());
        $refund_fee = $refund_fee * 100;
        $str = 'appid=' . $this->appid . '&mch_id=' . $this->mch_id . '&nonce_str=' . $nonce_str . '&notify_url=' . $notify_url;
        $str .= '&out_refund_no=' . $out_refund_no . '&out_trade_no=' . $out_trade_no . '&refund_fee=' . $refund_fee . '&total_fee=' . $total_fee;
        $str .= '&key=' . $this->key;
        $signature = strtoupper(MD5($str));
        $xml = sprintf($xml, $this->appid, $this->mch_id, $nonce_str, $notify_url, $out_refund_no, $out_trade_no, $refund_fee, $total_fee, $signature);
        $res = $this->curl('post', $url, $xml, 1, $this->certPath, $this->keyPath);
        // 结果生成数组
        $res = json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), 1);
        if (!$res) {
            return ['status' => 'error', 'msg' => '提交失败'];
        }
        if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
            return ['status' => 'success', 'data' => $res];
        } else {
            return ['status' => 'error', 'msg' => $res];
        }
    }
    // 查询退款状态
    public function refundquery($out_trade_no)
    {
        $url = 'https://api.mch.weixin.qq.com/pay/refundquery';
        $xml = '<xml>
				<appid>%s</appid>
				<mch_id>%s</mch_id>
				<nonce_str>%s</nonce_str>
				<out_trade_no>%s</out_trade_no>
				<sign>%s</sign>
				</xml>';
        $nonce_str = MD5(time());
        $str = 'appid=' . $this->appid . '&mch_id=' . $this->mch_id . '&nonce_str=' . $nonce_str . '&out_trade_no=' . $out_trade_no . '&key=' . $this->key;
        $signature = strtoupper(MD5($str));
        $xml = sprintf($xml, $this->appid, $this->mch_id, $nonce_str, $out_trade_no, $signature);
        $res = $this->curl('post', $url, $xml);
        $res = json_decode(json_encode(simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA)), 1);
        if (!$res) {
            return ['status' => 'error', 'msg' => '查询失败'];
        }
        if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
            return ['status' => 'success', 'data' => $res];
        } else {
            return ['status' => 'error', 'msg' => $res];
        }
    }
    /**
     * 退款回调
     */
    public function refund_notify()
    {
        $data = file_get_contents('php://input');
        $data = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
    }
}
