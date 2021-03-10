<?php
// Base Controller
class BaseController
{
    // Base Controller has a property called $loader, it is an instance of Loader class(introduced later)
    protected $loader;

    public function __construct()
    {
        $this->loader = new Loader();
    }

    public function redirect($url, $message, $wait = 0)
    {
        if ($wait == 0) {
            header("Location:$url");
        } else {
            include CURR_VIEW_PATH . "message.html";
        }
        exit;
    }

    // TODO:
    public function initRSA()
    {
        $this->loader->library('rsa');
        return new RSA($GLOBALS['config']['rsa']['pi_key'], 
        $GLOBALS['config']['rsa']['pi_key']);
    }

    public function doLogInfo($name, $info = null)
    {
        file_put_contents(LOG_PATH . "/{$name}.log", date('[Y-m-d H:i:s]: ') . $info . PHP_EOL, FILE_APPEND);
    }

    public function getClient()
    {
        // 尝试取得openid
        $client = null;
        if ((isset($_SERVER['HTTP_REFERER']) && false !== strpos($_SERVER['HTTP_REFERER'], 'servicewechat.com')) || (isset(PARAM['client']) && PARAM['client'] == 'wx')) {
            // 微信
            $client = 'wx';
        } else if ((isset($_SERVER['HTTP_REFERER']) && false !== strpos($_SERVER['HTTP_REFERER'], 'appservice.qq.com')) || (isset(PARAM['client']) && PARAM['client'] == 'qq')) {
            // qq
            $client = 'qq';
        } else {
            throw new CuitException("不支持的客户端");
        }
        return $client;
    }

    public function getUniqid()
    {
        return md5(uniqid(microtime(true),true));
    }
    
    /**
     * 生成请求签名
     * 
     * @param path /???/????/
     */
    function genQuerySign($path, $openid, $data = ''){
        return md5(md5($path) . md5($openid) . md5($data) . $GLOBALS['config']['QUERY_SALT']);
    }
    
    function str2GBK($str)
    {
        $encoding = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GBK', 'GB2312', 'BIG5'));
        if ($encoding != 'GBK') {
            return mb_convert_encoding($str, 'GBK', $encoding);
        }
        return $str;
    }
    function str2UTF8($str)
    {
        // 编码处理
        $encoding = mb_detect_encoding($str, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
        // 如果字符串的编码格式不为UTF_8就转换编码格式
        if ($encoding != 'UTF-8') {
            return mb_convert_encoding($str, 'UTF-8', $encoding);
        }
        return $str;
    }
    
}
