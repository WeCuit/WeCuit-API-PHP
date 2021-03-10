<?php
class JWC
{
    /**
     * （jwc控制器调用）
     */
    public static function downFile()
    {
        if (!(isset(PARAM['cookie']) && isset(PARAM['downUrl'])))
            throw new cuitException("参数缺失");
        if (0 !== strpos(PARAM['downUrl'], "http")) throw new cuitException("下载地址格式有误");
        $http = new EasyHttp();
        $down = $http->request(PARAM['downUrl'] . "&codeValue=" . PARAM['codeValue'], array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => PARAM['cookie']
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
        if (is_object($down)) throw new cuitException("服务器网络错误", 10511);
        if (302 == $down['response']['code']) {
            $link = '';
            if (false !== strpos($_SERVER['HTTP_REFERER'], 'appservice.qq.com')) {
                // qq兼容
                // 取文件后缀
                $h = get_headers("https://jwc.cuit.edu.cn{$down['headers']['location']}");
                preg_match("/\.([a-zA-Z]+);/i", $h[4], $m);
                $link = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/Api/file/redirect/type.{$m[1]}?link=https://jwc.cuit.edu.cn{$down['headers']['location']}";
            } else {
                $link = "https://jwc.cuit.edu.cn{$down['headers']['location']}";
            }
            // print_r($m);
            echo json_encode(
                array(
                    'status' => 2000,
                    'errorCode' => 2000,
                    'link' => $link
                )
            );
        } else {
            // print_r($down);
            if (isset($down['headers']['set-cookie'])) $cookie = "{$down['cookies'][0]->name}={$down['cookies'][0]->value}";
            $cap = $http->request('https://jwc.cuit.edu.cn/system/resource/js/filedownload/createimage.jsp?randnum=' . time(), array(
                'method' => 'GET',        //	GET/POST
                'timeout' => 5,            //	超时的秒数
                'redirection' => 0,        //	最大重定向次数
                'httpversion' => '1.1',    //	1.0/1.1
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
                'blocking' => true,        //	是否阻塞
                'headers' => array(
                    'cookie' => $cookie
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
            if (is_object($cap)) throw new cuitException("服务器网络错误", 10511);

            echo json_encode(
                array(
                    'status' => 2002,
                    'errorCode' => 2002,
                    'captcha' => base64_encode($cap['body']),
                    'cookie' => $cookie
                )
            );
        }
        exit;
    }
}
