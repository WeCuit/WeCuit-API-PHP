<?php
class QQ {
    
    public static function refreshAccessToken(){
        $ak_path = SESSION_PATH . "qq_access_token.json";
        // 最后修改时间
        $et = 0;
        $ak = array('expires_in' => 7200);
        if(file_exists($ak_path)){
            $ak = json_decode(file_get_contents($ak_path), true);
            $et = filemtime($ak_path);
        }
        
        // 2小时更新一次
        if($ak['expires_in'] - 60 > time() - $et)
        {
            // 2小时之内
            return;
        }
        
        $q = new QAPI($GLOBALS['config']['mini']['qq_appid'], $GLOBALS['config']['mini']['qq_secret']);
        $ak = $q->getAccessToken();
        file_put_contents($ak_path, json_encode($ak));
        print_r($ak);
    }
    
}

class QAPI {
    private $appid;
    private $secret;
    private $accessToken;
    private $code;

    public function __construct($appid, $secret, $code = null) {
        $this->appid = $appid;
        $this->secret = $secret;
        $this->code = $code;
        
        $ak_path = SESSION_PATH . "qq_access_token.json";
        // 最后修改时间
        $et = 0;
        $ak = array('expires_in' => 7200);
        if(file_exists($ak_path)){
            $ak = json_decode(file_get_contents($ak_path), true);
            $et = filemtime($ak_path);
        }
        
        // 2小时更新一次
        if($ak['expires_in'] - 60 > time() - $et)
        {
            $this->accessToken = json_decode(file_get_contents($ak_path), true)['access_token'];
        }else
            $this->getAccessToken();
    }

    public function sendSub($openid, $tmplid, $page, $data) {
        $url = "https://api.q.qq.com/api/json/subscribe/SendSubscriptionMessage?access_token=" . $this->accessToken;
        $param['access_token'] = $this->accessToken;
        $param['touser'] = $openid;
        $param['template_id'] = $tmplid;
        $param['page'] = $page;
        $param['data'] = $data;
        $param['form_id'] = 'test';
        // $param = http_build_query($param,'','&');
        $param = json_encode($param);
        $data = $this->postUrl($url, $param);
        return $data;
    }
    
    public function code2Session()
    {
        if(null === $this->code)return false;
        $url = "https://api.q.qq.com/sns/jscode2session";
        $param['grant_type'] = "authorization_code";
        $param['appid'] = $this->appid;
        $param['secret'] = $this->secret;
        $param['js_code'] = $this->code;
        $param = http_build_query($param,'','&');
        $url = $url."?".$param;
        $data = $this->getUrl($url);
        return $data;
    }
    
    public function getAccessToken() {
        $url = "https://api.q.qq.com/api/getToken";
        $param['grant_type'] = "client_credential";
        $param['appid'] = $this->appid;
        $param['secret'] = $this->secret;
        $param = http_build_query($param,'','&');
        $url = $url."?".$param;
        $data = $this->getUrl($url);
        $data = json_decode($data, true);
        if(isset($data['access_token']))$this->accessToken = $data['access_token'];
        return $data;
    }

    //CURL GET
    private function getUrl($url) {
        $ch = curl_init($url);
        $headers[] = 'Accept: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    //CURL POST
    private function postUrl($url, $data) {
        $ch = curl_init ();
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt ($ch, CURLOPT_POST, TRUE);
        $headers[] = 'content-type: application/json';
        $headers[] = 'Content-Length: ' . strlen($data);
        curl_setopt ($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        $ret = curl_exec($ch);
        curl_close ($ch);
        return $ret;
    }
}