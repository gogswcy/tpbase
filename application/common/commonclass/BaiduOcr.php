<?php

namespace app\common\commonclass;

class BaiduOcr
{
    protected $key;
    protected $secret;
    protected $path;

    public function __construct()
    {
        $this->key = config('bd_ocr_key');
        $this->secret = config('bd_ocr_secret');
        $this->path = config('bd_ocr_path') . '/' . config('bd_ocr_key') . '.json';
    }

    public function getToken()
    {
        if (file_exists($this->path)) {
            $tokenFile = json_decode(file_get_contents($this->path), 1);
            if ($tokenFile['expires_time'] > time())
                return $tokenFile['access_token'];
        }

        $url = 'https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id=%s&client_secret=%s';
        $url = sprintf($url, $this->key, $this->secret);
        $token = json_decode(file_get_contents($url), 1);
        $data = [
            'access_token' => $token['access_token'],
            'expires_time' => time() + $token['expires_in'],
        ];
        file_put_contents($this->path, json_encode($data));
        return $token['access_token'];
    }

    public function licenseOcr($data)
    {
        $token = $this->getToken();
        $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/business_license?access_token='.$token;
        $data = ['image' => $data, 'detect_direction' => 'true'];
        $res = $this->curlPost($url, $data);
        if (isset($res['error'])) {
            return json(['status' => 'error', 'msg' => '识别失败']);
        }
        $res['status'] = 'success';
        return $res;
    }

    public function basicOcr($data)
    {
        $token = $this->getToken();
        $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/general_basic';
        $url .= '?access_token='.$token;
        $data = ['image' => $data];
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, 1);
        if (isset($res['error'])) {
            return json(['status' => 'error', 'msg' => '识别失败']);
        }
        $res['status'] = 'success';
        return $res;
    }

    /**
     * 行驶证
     */
    public function vehicleLicense($data)
    {
        $token = $this->getToken();
        $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/vehicle_license?access_token='.$token;
        $data = ['image' => $data, 'detect_direction' => 'true'];
        $res = $this->curlPost($url, $data);
        $res = json_encode($res, 1);
        if (isset($res['error'])) {
            return json(['status' => 'error', 'msg' => '识别失败']);
        }
        $res['status'] = 'success';
        return $res;
    }
    /**
     * 身份证
     */
    public function idcardOcr($data)
    {
        $token = $this->getToken();
        $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/idcard?access_token='.$token;
        $data = [
            'image' => $data, 
            'id_card_side' => 'front',
            'detect_direction' => 'true'
        ];
        $res = $this->curlPost($url, $data);
        $res = json_encode($res, 1);
        if (isset($res['error'])) {
            return json(['status' => 'error', 'msg' => '识别失败']);
        }
        $res['status'] = 'success';
        return $res;
    }
    /**
     * 银行卡识别
     */
    public function bankCard($data)
    {
        $data = preg_replace('/^(.*?,)/', '', $data);
        $token = $this->getToken();
        $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/bankcard?access_token='.$token
        $data = [
            'image' => $data,
            'detect_directioni' => 'false',
        ];
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, 1);
        if (isset($res['error_code'])) {
            return ['status' => 'error', 'msg' => $res['error_msg']];
        }
        $res['status'] = 'success';
        return $res;
    }
    public function curlPost($url, $data)
    {
        $headerArray = array("Content-type:application/x-www-form-urlencoded;charset='utf-8'");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}