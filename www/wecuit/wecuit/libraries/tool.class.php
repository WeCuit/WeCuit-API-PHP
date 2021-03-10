<?php
class Tool
{

    public static function captchaDecodeFunc($captcha)
    {
        $dataName = "FormBoundary" . md5(uniqid(microtime(true),true));
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
        if (is_object($response)) throw new cuitException("服务器网络错误", 10511, null, $response);
        $ret = json_decode($response['body'], true);
        if (isset($ret['result'])) $ret['errorCode'] = $ret['status'] = 2000;
        else throw new cuitException("验证码识别出错", 10512);
        return $ret;
    }

    public function code2Session($code, $client)
    {
        // 尝试取得openid
        if ('wx' === $client) {
            // 微信
            require_once 'mp.class.php';
            $mp = new MPAPI($GLOBALS['config']['mini']['mp_appid'], $GLOBALS['config']['mini']['mp_secret'], $code);
            $data = $mp->code2Session();
            $data = json_decode($data, true);
        } else if ('qq' === $client) {
            // qq
            require_once 'q.class.php';
            $q = new QAPI($GLOBALS['config']['mini']['qq_appid'], $GLOBALS['config']['mini']['qq_secret'], $code);
            $data = $q->code2Session();
            $data = json_decode($data, true);
        }
        // print_r($data);
        /**
         * data
         * array(
         *  'session_key' => ???,
         *  'openid' => ???
         * )
         */
        if (!isset($data['openid'])) {
            // print_r($data);
            // exit;
            throw new cuitException("openid 获取失败");
        }
        return $data;
    }

    /**
     * 检查code可用性
     * 
     */
    public function checkCode($code, $client)
    {
        $data = $this->code2Session($code, $client);
        if (isset($data['openid'])) return true;
        return false;
    }

    /**
     * 图像交错处理
     * 
     */
    public function imageLace(&$path){
        $pic = new PIC($path);
        $pic->imageInterLace(1);
        $path = str_replace('.png', '.jpeg', $path);
        return $pic->outPic($path);
    }

    /**
     * 二维码生成
     * 
     */
    // public static function genQRCode($str = null)
    // {
    //     if(!extension_loaded('gd')) {
    //         echo "未启用GD库";
    //         exit;
    //     }
    //     //1.配置与说明
    //     $data = isset($_GET['str'])?urldecode($_GET['str']):'Hello World!'; //内容
    //     $level = 'M'; // 纠错级别：L、M、Q、H
    //     $size = 10; //元素尺寸
    //     $margin = 1; //边距
    //     $outfile = false;
    //     $saveandprint = true; // true直接输出屏幕  false 保存到文件中
    //     $back_color = 0xFFFFFF; //白色底色
    //     $fore_color = 0x000000; //黑色二维码色 若传参数要hexdec处理，如 $fore_color = str_replace('#','0x',$fore_color); $fore_color = hexdec('0xCCCCCC');

    //     // 可在 phpqrcode/phpqrcode.php 文件中修改以下配置
    //     // define('QR_FIND_BEST_MASK', true); // true 每次生成码都会变换掩码 ， false 时只要内容不变，生成图案不变
    //     // define('QR_PNG_MAXIMUM_SIZE', 1024);//生成最大图片尺寸，若要更大的尺寸，可以自己修改，根据自身需求和服务器性能决定

    //     //2.使用方法大全
    //     $QRcode = new \QRcode();
    //     // $QRcode = new QRcode();

    //     //生成png图片
    //     $QRcode->png($data, $outfile, $level, $size, $margin, $saveandprint, $back_color, $fore_color);

    //     //生成svg图片
    //     // $outfile = 'erweima.svg';
    //     // $QRcode->svg($data, $outfile, $level, $size, $margin, false, $back_color, $fore_color);
    //     //生成eps图片
    //     // $outfile = 'erweima.eps';
    //     // $QRcode->eps($data, $outfile, $level, $size, $margin, false, $back_color, $fore_color);

    //     //保存到文本 1表示黑色点  0表示白色点
    //     // $outfile = 'erweima.text';
    //     // $outfile = false;//不设置 outfile  返回数组
    //     // $text = $QRcode->text($data, $outfile, $level, $size, $margin);
    //     // print_R($text);

    //     echo $outfile;
    //     exit;
    // }

}
