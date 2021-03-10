<?php
class ToolController extends BaseController
{

    protected $tm;

    function __construct()
    {
        parent::__construct();
        $this->tm = new ToolModel();
    }

    public function captchaDecodeV2Action()
    {
        $captcha = file_get_contents('php://input');

        // 安全校验 START
        $start = (int)(strlen($captcha) / 3);
        $end = (int)(strlen($captcha) / 2);
        while ($end - $start > 20)
            $end = (int)(($start + $end) / 2);
        $verify2 = bin2hex(substr($captcha, $start, $end - $start)) . "/@jysafe.cn";
        $rsa = $this->initRSA();
        $verify1 = $rsa->RSAPrivateDecrypt($_SERVER['HTTP_X_VERIFY']);
        if ($verify1 !== $verify2) throw new cuitException("验证失败");
        // 安全校验 END

        $this->loader->library('easyHttp');
        $dataName = "FormBoundary" . md5(uniqid(microtime(true), true));
        $http = new EasyHttp();
        $response = $http->request("{$GLOBALS['config']['internal']['host']}:{$GLOBALS['config']['internal']['py_port']}/vercode", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            //'user-agent' => 'USER-AGENT',		
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Content-Type" => "multipart/form-data; boundary=----{$dataName}"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            'body' => "------{$dataName}
Content-Disposition: form-data; name=\"captcha\"; filename=\"captcha.jpg\"
Content-Type: application/octet-stream

" . $captcha . "
------{$dataName}--",
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($response)) throw new cuitException("网络错误", 10511, null, $response);
        $ret = json_decode($response['body'], true);
        if (isset($ret['result'])) {
            $ret['errorCode'] = $ret['status'] = 2000;
            echo json_encode($ret);
        } else throw new cuitException("验证码识别出错", 10512);
    }

    public function updateUserIdAction()
    {
        $u = $this->tm->getUsers();
        $rsa = $this->initRSA();
        foreach ($u as $value) {
            $sid = $rsa->RSAPrivateDecrypt($value['sId']);
            $this->tm->updateSid($value['openid'], $sid);
        }
        $this->tm->closeDB();
    }
}
