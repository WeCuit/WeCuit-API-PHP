<?php 
class JszxController extends BaseController
{
    
    /**
     * CUIT 登录操作RSAv1
     */
    public function loginRSAv1Action()
    {
        $rsa = $this->initRSA();
        if (!(isset(PARAM['userId']) && isset(PARAM['userPass'])))
            exit("参数缺失");
        
        if(strlen(PARAM['userId']) > 15)
        {
            $sId = $rsa->RSAPrivateDecrypt(PARAM['userId']);
        }
        
        $info = array(
            'id' => $sId,
            'pass' => $rsa->RSAPrivateDecrypt(PARAM['userPass'])
        );
        $this->loader->library('easyHttp','jszx');
        $info = JSZX::JSZX_doLogin($info);

        echo json_encode($info);
    }

    function checkLoginAction()
    {
        $this->loader->library('easyHttp','jszx');
        $ret = JSZX::checkLogin();

    }
    public function getCheckInListAction()
    {
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'errMsg' => '临近开学，暂停服务',
            'list' => array(
                'today' => [
                    array(
                        'link' => null,
                        'status' => 'X',
                        'title' => '版本变动较大，当前版本暂停服务'
                    ),
                    array(
                        'link' => null,
                        'status' => 'X',
                        'title' => '版本变动，暂停自动打卡服务'
                        ),
                    array(
                        'link' => null,
                        'status' => 'X',
                        'title' => '请等待新版发布'
                        )
                    ],
                'outDate' => [
                    ]
                )
            ));
        exit;
        $this->loader->library('easyHttp','jszx');
        $ret = JSZX::getCheckInList();
        echo json_encode($ret);
    }

    public function getCheckInListV2Action()
    {
        // echo json_encode(array(
        //     'status' => 2000,
        //     'errorCode' => 2000,
        //     'errMsg' => '临近开学，暂停服务',
        //     'list' => array(
        //         'today' => [
        //                 array(
        //                     'link' => null,
        //                     'status' => 'X',
        //                     'title' => '临近开学，暂停服务'
        //                     ),
        //                     array(
        //                         'link' => null,
        //                         'status' => 'X',
        //                         'title' => '临近开学，暂停自动打卡服务'
        //                         )
        //             ]
        //         )
        //     ));
        // exit;
        $this->loader->library('easyHttp');
        $ret = array();
        $http = new EasyHttp();
        $response = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Xs/netks/sj.asp?jkdk=Y", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => PARAM['cookie']
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
        if (is_object($response)) throw new cuitException("服务器网络错误" . __LINE__, 10511);
        if (200 != $response['response']['code']) throw new cuitException("阁下似乎还没有登录呢", 20401);
        $html = $response['body'];

        $html = preg_replace("/<script[\s\S]*?<\/script>/i", "", $html);
        $html = $this->str2UTF8($html);
        $html = str_replace("gb2312", "UTF-8", $html);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $dom->normalize();

        $xpath = new DOMXPath($dom);

        // 今天的
        $items = $xpath->query('/html/body/div[2]/table/tbody/tr[2]/td[2]/..');
        if(1 === $items->length){
            $tr = $items->item(0);
            $status = $tr->firstChild->nodeValue == '√' ? '√' : 'X';
            $a = $tr->childNodes->item(2)->firstChild;
            $text= $a->textContent;
            $link = substr($a->attributes->item(0)->value, 9);
            $list['today'][] = array(
                'title' => $text,
                'status' => $status,
                'link' => $link
            );
        }

        // 过期的
        $items = $xpath->query('/html/body/div[2]/table/tbody/tr[position()>2]/td[2]/..');
        for ($i=0; $i < $items->length; $i++) { 
            $tr = $items->item($i);
            $status = $tr->firstChild->nodeValue == '√' ? '√' : 'X';
            $a = $tr->childNodes->item(2)->firstChild;
            $text= $a->textContent;
            $link = $a->attributes->item(0)->value;
            $list['outDate'][] = array(
                'title' => $text,
                'status' => $status,
                'link' => $link
            );
        }
        $ret = array(
            'errorCode' => 2000,
            'list' => $list
        );
        echo json_encode($ret);
    }

    // Deprecated
    public function getCheckInEditAction()
    {
        $this->loader->library('easyHttp','jszx');
        $ret = JSZX::getCheckInEdit();
        echo json_encode($ret);
    }

    public function getCheckInEditV2Action()
    {
        $this->loader->library('easyHttp');
        $this->loader->helper('checkIn');

        if (!(isset(PARAM['cookie']) && isset(PARAM['link'])))
            throw new cuitException("参数缺失");

        $http = new EasyHttp();
        $edit = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Xs/netks/sjDb.asp?" . PARAM['link'], array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 1,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => PARAM['cookie']
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
        if (is_object($edit)) throw new cuitException("服务器网络错误", 10511);
        if(302 === $edit['response']['code']){
            echo json_encode(array(
                'errorCode' => 20401,
                'status' => 0,
            ));
            exit;
        }
        $html = $edit['body'];
        $form = checkIn_genFormData($html);
        echo json_encode(array(
            'errorCode' => 2000,
            'form' => $form
        ));
    }

    function doCheckInV2Action()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $this->loader->library('easyHttp');
        $this->loader->helper('checkIn');
        $body = checkIn_form2body($data['form'], $data['link']);
        
        $http = new EasyHttp();
        $submit = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Xs/netks/editSjRs.asp", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $data['JSZXCookie'],
                "Referer" => "http://jszx-jxpt.cuit.edu.cn/"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $body,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($submit)) throw new cuitException("服务器网络错误", 10511);
        if (200 != $submit['response']['code']) throw new cuitException("未登录");
        $html = $submit['body'];
        $html = $this->str2UTF8($html);

        preg_match("/>打卡时间：(.*?)<\//", $html, $time);
        $data = checkIn_genFormData($html);
        if (false != strpos($html, "提交打卡成功！")) {
            echo json_encode(
                array(
                    'errorCode' => 2000,
                    'status' => 2000,
                    'errMsg' => $time[1],
                    'form' => $data['data']
                )
            );
        } else {
            echo json_encode(
                array(
                    'status' => 2005,
                    'errMsg' => '失败了╮(╯▽╰)╭'
                )
            );
        }

    }
    function doCheckInV3Action()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $this->loader->library('easyHttp');
        $this->loader->helper('checkIn');
        $body = checkIn_form2body($data['form'], $data['link']);
        
        $http = new EasyHttp();
        $submit = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Xs/netks/editSjRs.asp", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $data['JSZXCookie'],
                "Referer" => "http://jszx-jxpt.cuit.edu.cn/"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $body,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($submit)) throw new cuitException("服务器网络错误", 10511);
        if (200 != $submit['response']['code']) throw new cuitException("未登录");
        $html = $submit['body'];
        $html = $this->str2UTF8($html);

        preg_match("/>打卡时间：(.*?)<\//", $html, $time);
        $data = checkIn_genFormData($html);
        if (false != strpos($html, "提交打卡成功！")) {
            echo json_encode(
                array(
                    'errorCode' => 2000,
                    'status' => 2000,
                    'errMsg' => $time[1],
                    'form' => $data
                )
            );
        } else {
            echo json_encode(
                array(
                    'status' => 2005,
                    'errMsg' => '失败了╮(╯▽╰)╭'
                )
            );
        }

    }
    function doCheckInAction()
    {
        $this->loader->library('easyHttp','jszx');
        $ret = JSZX::doCheckIn();
        // echo json_encode($ret);
    }

    // -------OFFICE
    function office_prepareAction(){
        $this->loader->library('easyHttp');
        $http = new EasyHttp();
        $prepare = $http->request("http://login.cuit.edu.cn:81/Login/xLogin/Login.asp", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://login.cuit.edu.cn:81/Login/xLogin/Login.asp"
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
        if(is_object($prepare))throw new Exception('服务器网络错误', 10511);
        $body = $prepare['body'];
        preg_match("/<input type=\"hidden\" name=\"codeKey\" value=\"(\d+)\"/", $body, $codeKey);
        $codeKey = $codeKey[1];
        preg_match("/<span style=\"color:#0000FF;\">(.*?)<\/span/", $body, $syncTime);
        $syncTime = $syncTime[1];
        $cookie = isset($prepare['cookies'][0])?"{$prepare['cookies'][0]->name}={$prepare['cookies'][0]->value}":'';
        
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'cookie' => $cookie,
            'codeKey' => $codeKey,
            'syncTime' => $syncTime
        ));
    }
    // 获取验证码
    function office_getCaptchaAction(){
        $this->loader->library('easyHttp', 'tool');
        $http = new EasyHttp();
        $captcha = $http->request("http://login.cuit.edu.cn:81/Login/xLogin/yzmDvCode.asp?k=" . PARAM['codeKey'] . "&t=" . time(), array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => PARAM['cookie'],
                "Referer" => "http://login.cuit.edu.cn:81/Login/xLogin/Login.asp"
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
        if(is_object($captcha))throw new Exception('服务器网络错误', 10511);
        $decode = TOOL::captchaDecodeFunc($captcha['body']);
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'base64img' => "data:image/png;base64, " . base64_encode($captcha['body']),
            'imgCode' => $decode['result'] ? $decode['result'] : '',
        ));
    }
    // 查询
    function office_queryAction(){
        $this->loader->library('easyHttp');
        $http = new EasyHttp();
        $query = $http->request("http://login.cuit.edu.cn:81/Login/xLogin/Login.asp", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => PARAM['cookie'],
                "Referer" => "http://login.cuit.edu.cn:81/Login/xLogin/Login.asp"
            ),    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => "WinW=1304&WinH=768&txtId=" . $this->str2GBK(PARAM['nickname']) . "&txtMM=" . PARAM['email'] . "&verifycode=" . PARAM['captcha'] . "&codeKey=" . PARAM['codeKey'] . "&Login=Check&IbtnEnter.x=8&IbtnEnter.y=26",
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if(is_object($query))throw new Exception('服务器网络错误', 10511);
        $query['body'] = $this->str2UTF8($query['body']);
        // echo $query['body'];
        preg_match("/class=user_main_z(.*?)<\/span/", $query['body'], $ret);
        $ret = $ret[1];
        $ret = substr($ret, strrpos($ret, '>') + 1);
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'result' => $ret
        ));
    }
    // -------OFFICE END
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