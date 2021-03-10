<?php
class Card
{
    private static $key = 12347890;
    private static $iv = 12347890;

    /**
     * 登录一卡通
     */
    public static function login()
    {
        $http = new EasyHttp();
        $login = $http->request('https://sso.cuit.edu.cn/authserver/login?service=http%3a%2f%2fykt.cuit.edu.cn%3a12491%2flogin.aspx', array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 2,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => "TGC=" . PARAM['cookie']
            ),    //	header信息
            'cookies' => array(
                // 'TGC' => PARAM['cookie']
            ),    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null,        //	如果stream = true，则必须设定一个临时文件名
            'delay' => 0, // 跳转延时TODO: 暂时只在CURL请求方式中添加该功能
        ));
        if (is_object($login)) throw new cuitException("服务器网络错误", 10511);
        if (200 === $login['response']['code']) throw new cuitException("SSO未登录", 12401);
        if (302 !== $login['response']['code']) throw new cuitException("异常");

        // http://ykt.cuit.edu.cn:12491/login.aspx?ticket=ST-18**********w-localhost
        
        // http://ykt.cuit.edu.cn:12491/login.aspx
        
        // http://ykt.cuit.edu.cn:12490/static/getUserInfoById.html?IDNo=*****&IDType=****&callbackUrl=http%3a%2f%2fykt.cuit.edu.cn%3a12490%2findex.html%23%2fhome%2findex%3fallShowHead%3d0
        $url = $login['headers']['location'];
        if(false === strpos($url, 'getUserInfoById'))throw new cuitException('失败', 1);
        
        $arr = parse_url($url);
        $query = $arr['query'];
        parse_str($query, $arr);
        $IDNo = base64_decode(base64_decode(base64_decode($arr['IDNo'])));
        // $IDType = base64_decode(base64_decode(base64_decode($arr['IDType'])));
        echo json_encode(
            array(
                'status' => 2000,
                'errorCode' => 2000,
                'data' => array(
                    'AccNum' => $IDNo
                )
            )
        );
    }

    /**
     * 获取钱包信息
     */
    public static function getAccWallet()
    {
        $data = self::genSign(PARAM, 'AccWallet');
        $data['ContentType'] = 'json';

        $http = new EasyHttp();
        $aw = $http->request('http://ykt.cuit.edu.cn:12490/QueryAccWallet.aspx', array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $data,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($aw)) throw new cuitException("服务器网络错误", 10511);
        if (false === strpos($aw['body'], "查询成功")) {
            // self::doLogInfo(__FUNCTION__, print_r($aw, true));
            throw new cuitException("查询失败");
        }
        $body = json_decode($aw['body'], true);
        $body['status'] = 2000;
        $body['errorCode'] = 2000;
        echo json_encode($body);
    }

    /**
     * 获取账户头像
     */
    public static function getAccPhoto()
    {
        if (!isset(PARAM['AccNum'])) throw new cuitException("参数缺失");
        $data = self::genSign(PARAM, 'AccPhoto');
        $data['ContentType'] = 'json';

        $http = new EasyHttp();
        $ap = $http->request('http://ykt.cuit.edu.cn:12490/QueryAccPhoto.aspx', array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $data,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($ap)) throw new cuitException("服务器网络错误", 10511);
        $body = json_decode($ap, true);
        if (1 === $body['Code']) {
            $body['errorCode'] = $body['status'] = 2000;
        }
        else {
            $body['errorCode'] = $body['status'] = $body['Code'];
        }
        echo json_encode($body);
    }

    /**
     * 获取账户信息
     */
    public static function getAccInfo()
    {
        if (!isset(PARAM['AccNum'])) throw new cuitException("参数缺失");
        $data = self::genSign(PARAM['AccNum'], 'AccInfo');
        $data['ContentType'] = 'json';

        $http = new EasyHttp();
        $ai = $http->request('http://ykt.cuit.edu.cn:12490/QueryAccInfo.aspx', array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $data,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($ai)) throw new cuitException("服务器网络错误", 10511);
        $body = json_decode($ai, true);
        if (1 === $body['Code']){
            $body['errorCode'] = $body['status'] = 2000;
        }else $body['errorCode'] = $body['status'] = $body['Code'];
        echo json_encode($body);
    }

    /**
     * 获取账户状态
     */
    public static function getAccAuth()
    {
        if (!isset(PARAM['AccNum'])) throw new cuitException("参数缺失");
        $data = self::genSign(PARAM['AccNum'], 'AccAuth');
        $data['ContentType'] = 'json';

        $http = new EasyHttp();
        $ai = $http->request('http://ykt.cuit.edu.cn:12490/QueryAccAuth.aspx', array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $data,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($ai)) throw new cuitException("服务器网络错误", 10511);
        $body = json_decode($ai, true);
        if (1 === $body['Code']) $body['errorCode'] = $body['status'] = 2000;
        else $body['errorCode'] = $body['status'] = $body['Code'];
        echo json_encode($body);
    }

    /**
     * 获取公告
     */
    public static function getAccBulletin()
    {
        if (!isset(PARAM['AccNum'])) throw new cuitException("参数缺失");
        $data = self::genSign(PARAM['AccNum'], 'AccBulletin');
        $data['ContentType'] = 'json';

        $http = new EasyHttp();
        $ai = $http->request('http://ykt.cuit.edu.cn:12490/QueryAccBulletin.aspx', array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $data,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($ai)) throw new cuitException("服务器网络错误", 10511);
        $body = json_decode($ai, true);
        if (1 === $body['Code']) $body['errorCode'] = $body['status'] = 2000;
        else $body['errorCode'] = $body['status'] = $body['Code'];
        echo json_encode($body);
    }

    /**
     * 查询流水
     */
    public static function getDealRec()
    {
        $data = self::genSign(PARAM, 'DealRec');
        $data['ContentType'] = 'json';

        $http = new EasyHttp();
        $ai = $http->request('http://ykt.cuit.edu.cn:12490/QueryDealRec.aspx', array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $data,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($ai)) throw new cuitException("服务器网络错误", 10511);
        $body = json_decode($ai['body'], true);
        if (1 === intval($body['Code'])) $body['errorCode'] = $body['status'] = 2000;
        else $body['errorCode'] = $body['status'] = $body['Code'];
        echo json_encode($body);
    }


    /**
     * 获取二维码
     */
    public static function getQRCode()
    {
        $data = self::genSign($_POST, 'QRCode');
        $data['ContentType'] = 'json';

        $http = new EasyHttp();
        $qr = $http->request('http://ykt.cuit.edu.cn:12490/GetQRCode.aspx', array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $data,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($qr)) throw new cuitException("服务器网络错误", 10511);
        $body = json_decode($qr['body'], true);
        if (1 === intval($body['Code'])) $body['errorCode'] = $body['status'] = 2000;
        else $body['errorCode'] = $body['status'] = $body['Code'];
        echo json_encode($body);
    }


    /**
     * 获取二维码状态
     */
    public static function getQRCodeInfo()
    {
        $data = self::genSign(PARAM, 'QRCodeInfo');
        $data['ContentType'] = 'json';

        $http = new EasyHttp();
        $qri = $http->request('http://ykt.cuit.edu.cn:12490/GetQRCodeInfo.aspx', array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $data,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($qri)) throw new cuitException("服务器网络错误", 10511);
        $body = json_decode($qri['body'], true);
        if ('1' === $body['Code']) $body['errorCode'] = $body['status'] = 2000;
        else{
            $body['errorCode'] = $body['status'] = $body['Code'];
        }
        // self::doLogInfo('payFail', $qri['body']);
        echo json_encode($body);
    }

    /**
     * decryptByDESModeCBC
     * 
     * @param msg hex
     */
    private static function decryptByDESModeCBC($msg)
    {
        if (function_exists('openssl_decrypt'))
            return openssl_decrypt(hex2bin($msg), 'DES-CBC', self::$key, OPENSSL_RAW_DATA, self::$iv);
        else throw new cuitException("不支持openssl!");
    }

    /**
     * encryptByDESModeCBCNew
     * 
     * @param msg String
     */
    private static function encryptByDESModeCBCNew($msg)
    {
        if (function_exists('openssl_decrypt'))
            return bin2hex(openssl_encrypt($msg, 'DES-CBC', self::$key, OPENSSL_RAW_DATA, self::$iv));
        else throw new cuitException("不支持openssl!");
    }

    private static function genSign($arr, $type)
    {
        $arr['Time'] = date("YmdHis");
        $key = "ok15we1@oid8x5afd@";
        $str = '';
        switch ($type) {
            case 'DealRec':
                $str = "{$arr['AccNum']}|{$arr['BeginDate']}|{$arr['Count']}|{$arr['EndDate']}|{$arr['RecNum']}|{$arr['Time']}|{$arr['Type']}|{$arr['ViceAccNum']}|{$arr['WalletNum']}|{$key}";
                break;
            case 'AccBulletin':
                $str = "{$arr['BeginDate']}|{$arr['EndDate']}|{$arr['Time']}|{$key}";
                break;
            case 'AccAuth':
            case 'AccWallet':
            case 'QRCode':
                $str = "{$arr['AccNum']}|{$arr['Time']}|{$key}";
                break;
            case 'QRCodeInfo':
                $str = "{$arr['QRCode']}|{$arr['Time']}|{$key}";
                break;
            default:
                header("X-errMsg: unknownType");
                break;
        }
        $arr['Sign'] = md5($str);
        return $arr;
    }
}
