<?php
class WebVpn{
    private $sId;
    private $sPass;
    private $encrypted_pwd;
    private $cookie;
    private $isNeedCaptcha;
    private $twfid;
    private $RSA_ENCRYPT_KEY;
    private $RSA_ENCRYPT_EXP;
    private $rc;

    function __construct($sId, $sPass, $twfid)
    {
        $this->sId = $sId;
        $this->sPass = $sPass;
        $this->twfid = $twfid;
    }

    public function login()
    {
        $http = new EasyHttp();
        $l = $http->request("https://webvpn.cuit.edu.cn/por/login_psw.csp?anti_replay=1&encrypt=1&apiversion=1", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => "language=zh_CN; privacy=1; ENABLE_RANDCODE=" . $this->isNeedCaptcha . "; TWFID=" .$this->twfid
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => array(
                'mitm_result' => "",
                'svpn_req_randcode' => $this->rc,
                'svpn_name' => $this->sId,
                'svpn_password' => $this->encrypted_pwd,
                'svpn_rand_code' => "",
            ),
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if(is_object($l))return [false];
        $r = self::xmlToArray($l['body']);
        if(1 == $r['ErrorCode'])
        {
            $this->twfid = $r['TwfID'];
            return [true, ''];
        }else{
            return [false, $r];
        }
    }
    public function getTWFID()
    {
        return $this->twfid;
    }
    public function encrypt()
    {
        $http = new EasyHttp();
        $e = $http->request("{$GLOBALS['config']['internal']['host']}:{$GLOBALS['config']['internal']['py_port']}/webvpn", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => null,    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => array(
                'str' => $this->sPass . "_" . $this->rc
            ),
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if(is_object($e))return false;
        $r = json_decode($e['body']);
        $this->encrypted_pwd = $r->result;
    }
    public function getCaptcha()
    {

    }
    public function loginAuth()
    {
        $this->cookie = "language=zh_CN; privacy=1; ENABLE_RANDCODE=" . $this->isNeedCaptcha . ";TWFID=" . $this->twfid;
        $http = new EasyHttp();
        $la = $http->request("https://webvpn.cuit.edu.cn/por/login_auth.csp?apiversion=1", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => $this->cookie,
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if(is_object($la))return false;
        $r = self::xmlToArray($la['body']);
        if("login auth success" === $r['Message'])
        {
            $this->rc = $r['CSRF_RAND_CODE'];
            $this->twfid = $r['TwfID'];
            $this->RSA_ENCRYPT_KEY = $r['RSA_ENCRYPT_KEY'];
            $this->RSA_ENCRYPT_EXP = $r['RSA_ENCRYPT_EXP'];
            
            return $this->encrypt();
        }
        return false;
    }
    public function checkLogin()
    {
        $this->cookie = "language=zh_CN; privacy=1; ENABLE_RANDCODE=" . $this->isNeedCaptcha . ";TWFID=" . $this->twfid;
        $http = new EasyHttp();
        $cl = $http->request("https://webvpn.cuit.edu.cn/por/svpnSetting.csp?apiversion=1", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => $this->cookie
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if(is_object($cl))return false;
        $r = self::xmlToArray($cl['body']);
        if('20026' === $r['ErrorCode'])return false;
        return true;
    }

    public static function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
}