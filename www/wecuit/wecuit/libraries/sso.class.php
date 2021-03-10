<?php
class SSO{

    private $sId;
    private $sPass;
    private $captcha;
    private $captchaCode;
    private $session;
    private $execution;
    private $try;
    private $try_timeout;
    private $http;
    private $tgc;

    function __construct($sId, $sPass)
    {
        $this->sId = $sId;
        $this->sPass = $sPass;
        $this->try = 0;
        $this->try_timeout = 1;
        $this->http = new EasyHttp();
    }

    public function prepareLogin(){
        echo "--->登录准备\r\n";
		$p = $this->http->request('https://sso.cuit.edu.cn/authserver/login', array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.1',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
                "referer" => "https://sso.cuit.edu.cn",
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
        if (is_object($p)) return $p;
        if(!isset($p['cookies'][1]->name))
        {
            file_put_contents(LOG_PATH . __FUNCTION__ . ".log", print_r($p, true));
            return false;
        }
        $this->session = "{$p['cookies'][1]->name}={$p['cookies'][1]->value}";
        preg_match("/\" name=\"execution\" value=\"(.*?)\" \/>/i", $p['body'], $m);
        $this->execution = $m[1];
        return true;
    }

    public function getCaptcha()
    {
        echo "--->取验证码\r\n";
        $c = $this->http->request('https://sso.cuit.edu.cn/authserver/captcha', array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.1',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				"referer" => "https://sso.cuit.edu.cn",
                'cookie' => $this->session
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
        if (is_object($c)) return $c;
        $this->captcha = $c['body'];
        if(isset($c['cookies'][1]))
        $this->session = "{$c['cookies'][1]->name}={$c['cookies'][1]->value};";
        return true;
    }

    public function deCaptcha()
    {
        echo "--->解验证码\r\n";
        $code = TOOL::captchaDecodeFunc($this->captcha);
        if(2000 === $code['status'])
        $this->captchaCode = $code['result'];
        else return false;
        return true;
    }

    public function login(){
        echo "--->开始登录\r\n";
        $l = $this->http->request('https://sso.cuit.edu.cn/authserver/login', array(
			'method' => 'POST',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.1',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				"referer" => "https://sso.cuit.edu.cn",
                'cookie' => $this->session
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => array(
                'execution' => $this->execution,
                '_eventId' =>  "submit",
                'lm' =>  "usernameLogin",
                'geolocation' =>  "",
                'username' => $this->sId,
                'password' => $this->sPass,
                'captcha' => $this->captchaCode,
            ),
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
        if (is_object($l)) return $l;
        // print_r($l);
        if(false !== strpos($l['body'], "用户名或密码错误"))
        {
            return false;
        }
        if(false !== strpos($l['body'], "验证码无效"))
        {
            if($this->try < $this->try_timeout)
            {
                preg_match("/\" name=\"execution\" value=\"(.*?)\" \/>/i", $l['body'], $m);
                $this->execution = $m[1];
                $this->try++;
                $this->getCaptcha();
                $this->deCaptcha();
                return $this->login();
            }else{
                return $l;
            }
        }

        if(false !== strpos($l['body'], '登录成功') && $l['cookies'][1]->name == 'TGC')
            $this->tgc = "{$l['cookies'][1]->name}={$l['cookies'][1]->value};";
        else return $l;
        return true;
    }

    public function getTGC()
    {
        return $this->tgc;
    }

}