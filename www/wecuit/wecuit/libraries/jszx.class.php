<?php
class JSZX
{
    private static $timeout = 3;
    private static $delay = 0.1;

    /**
     * CUIT 登录操作RSAv1(订阅控制器调用)
     */
    public function checkAcc($id, $pass)
    {
        $info = array(
            'id' => $id,
            'pass' => $pass
        );
        return self::JSZX_doLogin($info);
    }
    /**
     * 检测是否登录（Jszx控制器调用）
     */
    public static function checkLogin()
    {
        if (!isset(PARAM['cookie']))
            throw new cuitException("参数缺失");
        $http = new EasyHttp();
        $i = 0;
        $cl = null;
        while ($i < 3) {
            $cl = $http->request("http://login.cuit.edu.cn/Login/qqLogin.asp", array(
                'method' => 'GET',        //	GET/POST
                'timeout' => self::$timeout,            //	超时的秒数
                'redirection' => 0,        //	最大重定向次数
                'httpversion' => '1.1',    //	1.0/1.1
                'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
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
            if (!is_object($cl)) break;
            sleep(1);
            $i++;
        }
        if (is_object($cl)) throw new cuitException("服务器网络错误" . __LINE__, 10511);
        if (!isset($cl['headers']['location'])) {
            // self::doLogInfo(__FUNCTION__, print_r($cl, true));
            throw new CuitException("发生异常,已记录" . __LINE__);
        }
        if ("http://jxgl.cuit.edu.cn/jkdk" == $cl['headers']['location']) {
            $ret = array(
                'status' => 2000,
                'errorCode' => 2000,
                'errMsg' => '已登录',
            );
            echo json_encode($ret);
        } else {
            $ret = array(
                'status' => 2005,
                'errorCode' => 2005,
                'errMsg' => 'Not Login',
            );
            echo json_encode($ret);
        }
        exit;
    }

    /**
     * 模拟 CUIT 登录页面（Jszx,Task控制器、本类checkAcc方法 间接调用）
     *
     * @return array (
     *      "cookie",
     *      "codeKey"
     * )
     */
    public static function JSZX_loginPage(&$cookie)
    {
        $http = new EasyHttp();
        $lp = $http->request("http://login.cuit.edu.cn/Login/xLogin/Login.asp", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => self::$timeout,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
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
        if (is_object($lp)) throw new cuitException("服务器网络错误" . __LINE__, 10511);
        if(!$lp['body']) throw new cuitException(__FUNCTION__ . "响应体为空");
        if(isset($lp["headers"]["set-cookie"])){
            $cookie = $lp["headers"]["set-cookie"];
            $cookie = explode(";", $cookie)[0];
        }
        preg_match_all("/<input type=\"hidden\" name=\"codeKey\" value=\"(.*)\"><INPUT /U", $lp['body'], $yzm);

        if(!isset($yzm[1][0])){
            file_put_contents(LOG_PATH . __FUNCTION__ . ".log", print_r($lp));
        }
        return array(
            "cookie" => $cookie,
            // 为需要验证码登录时留后路
            "codeKey" => $yzm[1][0]
        );
    }

    /**
     * 模拟浏览器CUIT登录过程（Jszx,Task控制器、本类checkAcc方法调用）
     *
     * @param cookie 要登录的cookie
     * @param body 登录请求体
     *
     */
    public static function JSZX_doLogin($info, &$oldCookie = null)
    {

        // 模拟访问登录页面
        $loginPage = self::JSZX_loginPage($oldCookie);
        $cookie = $loginPage['cookie'];
        // 模拟提交的数据
        $body = "WinW=1304&WinH=768&txtId={$info['id']}&txtMM={$info['pass']}&verifycode=%B2%BB%B7%D6%B4%F3%D0%A1%D0%B4&codeKey={$loginPage['codeKey']}&Login=Check&IbtnEnter.x=31&IbtnEnter.y=28";


        $http = new EasyHttp();
        $response = $http->request("http://login.cuit.edu.cn/Login/xLogin/Login.asp", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => self::$timeout,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $cookie,
                "Referer" => "http://login.cuit.edu.cn/Login/xLogin/Login.asp"
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
        if (is_object($response)) throw new cuitException("服务器网络错误" . __LINE__, 10511);
        // 据分析，该请求成功后会有302跳转操作
        if ($response["response"]["code"] != 302) {
            $response['body'] = self::str2UTF8($response['body']);
            preg_match("/※ (.*?)<\/span><br>/", $response['body'], $notice);
            throw new cuitException($notice[1]);
        }
        // $response = $http->request("http://login.cuit.edu.cn/Login/qqLogin.asp", array(
        //     'method' => 'GET',        //	GET/POST
        //     'timeout' => 5,            //	超时的秒数
        //     'redirection' => 0,        //	最大重定向次数
        //     'httpversion' => '1.1',    //	1.0/1.1
        //     'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
        //     'blocking' => true,        //	是否阻塞
        //     'headers' => array(
        //         "cookie" => $verify['cookie'],
        //         "Referer" => "http://login.cuit.edu.cn/Login/xLogin/Login.asp"
        //         ),    //	header信息
        //     'cookies' => null,    //	关联数组形式的cookie信息
        //     // 'cookies' => $cookies,
        //     'body' => null,
        //     'compress' => false,    //	是否压缩
        //     'decompress' => true,    //	是否自动解压缩结果
        //     'sslverify' => true,
        //     'stream' => false,
        //     'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        // ));
        // $response = $http->request("http://jxgl.cuit.edu.cn/jkdk/", array(
        //     'method' => 'GET',        //	GET/POST
        //     'timeout' => 5,            //	超时的秒数
        //     'redirection' => 0,        //	最大重定向次数
        //     'httpversion' => '1.1',    //	1.0/1.1
        //     'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
        //     'blocking' => true,        //	是否阻塞
        //     'headers' => array(
        //         "cookie" => $verify['cookie'],
        //         "Referer" => "http://login.cuit.edu.cn/Login/xLogin/Login.asp"
        //         ),    //	header信息
        //     'cookies' => null,    //	关联数组形式的cookie信息
        //     // 'cookies' => $cookies,
        //     'body' => null,
        //     'compress' => false,    //	是否压缩
        //     'decompress' => true,    //	是否自动解压缩结果
        //     'sslverify' => true,
        //     'stream' => false,
        //     'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        // ));
        // 操作另一个域名
        $response = $http->request("http://jszx-jxpt.cuit.edu.cn/jxgl/xs/netks/sj.asp?jkdk=Y", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => self::$timeout,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "Referer" => "http://jxgl.cuit.edu.cn/"
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
        sleep(self::$delay);
        $cookie1 = $response["headers"]["set-cookie"];
        if (!$cookie1) throw new cuitException("登录失败204153");
        $cookie1 = explode(';', $cookie1)[0];
        // 这一步暂时不需要
        // $response = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/UserPub/Login.asp?UTp=Xs", array(
        //     'method' => 'GET',        //	GET/POST
        //     'timeout' => 5,            //	超时的秒数
        //     'redirection' => 0,        //	最大重定向次数
        //     'httpversion' => '1.1',    //	1.0/1.1
        //     'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
        //     'blocking' => true,        //	是否阻塞
        //     'headers' => array(
        //         "cookie" => $cookie1,
        //         "Referer" => "http://jxgl.cuit.edu.cn/"
        //         ),    //	header信息
        //     'cookies' => null,    //	关联数组形式的cookie信息
        //     // 'cookies' => $cookies,
        //     'body' => null,
        //     'compress' => false,    //	是否压缩
        //     'decompress' => true,    //	是否自动解压缩结果
        //     'sslverify' => true,
        //     'stream' => false,
        //     'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        // ));
        $response = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Login/tyLogin.asp", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => self::$timeout,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $cookie1,
                "Referer" => "http://jxgl.cuit.edu.cn/"
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
        sleep(self::$delay);
        preg_match_all("/;URL=(.*)\">/U", $response['body'], $ret);
        // 得到一个链接：http://login.cuit.edu.cn/Login/qqLogin.asp?Oid=jszx%2Djxpt%2Ecuit%2Eedu%2Ecn&OSid=*******
        // 这是绑定jszx-jxpt与login的关系吗？
        $response = $http->request($ret[1][0], array(
            'method' => 'GET',        //	GET/POST
            'timeout' => self::$timeout,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $cookie,
                "Referer" => "http://jszx-jxpt.cuit.edu.cn/"
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
        if (is_object($response)) throw new cuitException("服务器网络错误", 10511);
        // 从这里开始，似乎在激活cookie1
        $response = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Login/tyLogin.asp", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => self::$timeout,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $cookie1,
                "Referer" => "http://jszx-jxpt.cuit.edu.cn/"
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
        if (is_object($response)) throw new cuitException("服务器网络错误", 10511);
        sleep(self::$delay);
        $response = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Login/syLogin.asp", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => self::$timeout,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $cookie1,
                "Referer" => "http://jszx-jxpt.cuit.edu.cn/"
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
        if (is_object($response)) throw new cuitException("服务器网络错误", 10511);
        sleep(self::$delay);
        $response = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/UserPub/Login.asp?UTp=Xs&Func=Login", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => self::$timeout,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $cookie1,
                "Referer" => "http://jszx-jxpt.cuit.edu.cn/"
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
        if (is_object($response)) throw new cuitException("服务器网络错误", 10511);
        // 存储cookie
        /*
            $cookie----->login.cuit.edu.cn
            $cookie1---->jszx-jxpt.cuit.edu.cn
            */
        return array(
            'status' => 2000,
            'errorCode' => 2000,
            'cookie' => "{$cookie}; {$cookie1}"
        );
    }

    /**
     * 获取打卡列表（Task控制器调用）
     *
     * @return array
     */
    public static function getCheckInList()
    {
        if (!isset(PARAM['cookie']))
            throw new cuitException("参数缺失");
        $ret = self::getCheckInListFuncV2(PARAM['cookie']);
        echo json_encode($ret);
        exit;
    }

    /**
     * 获取打卡列表（Task控制器调用）
     *
     * @return array
     */
    public static function getCheckInListFuncV2($cookie)
    {
        $ret = array();
        $http = new EasyHttp();
        $response = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Xs/netks/sj.asp?jkdk=Y", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => self::$timeout,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $cookie
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
        if (200 != $response['response']['code']) throw new cuitException("阁下似乎还没有登录呢", 60401);
        $html = $response['body'];

        $html = preg_replace("/<script[\s\S]*?<\/script>/i", "", $html);
        $html = self::str2UTF8($html);
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
            'status' => 2000,
            'errorCode' => 2000,
            'list' => $list
        );
        return $ret;
    }

    /**
     * 获取打卡编辑页面内容（Jszx控制器调用）
     *
     * @return array
     */
    public static function getCheckInEdit()
    {
        if (!(isset(PARAM['cookie']) && isset(PARAM['link'])))
            throw new cuitException("参数缺失");
        $http = new EasyHttp();
        $edit = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Xs/netks/sjDb.asp?" . PARAM['link'], array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 2,        //	最大重定向次数
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
        file_put_contents('test/temp.html', $edit['body']);
        $edit['body'] = self::str2UTF8($edit['body']);
        $edit['body'] = str_replace(array("\n", "\r", "\r\n"), "", $edit['body']);
        if (false == strpos($edit['body'], "健康打卡-疫情防控-成都信息工程大学")) throw new cuitException("阁下似乎还没有登录呢");
        if (false !== strpos($edit['body'], "系统报告")) {
            $con = preg_replace("/<(.*?)>/i", '', $edit['body']);
            $notice = self::get_between($con, "相关原因：", "操作建议");
            throw new cuitException($notice);
        }
        preg_match("/18px\"><b>(.*?)——/i", $edit['body'], $title);
        // if (!isset($title[1])) self::doLogInfo(__FUNCTION__, __LINE__ . "----------" . $edit['body']);
        $title = $title[1];
        preg_match("/<BODY>(.*?)<\/BODY>/i", $edit['body'], $body);
        // unset($edit);
        $body = $body[1];

        // 匹配打卡时间
        preg_match("/>打卡时间：(.*?)<\//", $body, $time);
        $time = $time[1];
        // 出校审核情况
        // preg_match("/审核：<span style=\"color:#0000FF\">(.*?)<\//i", $body, $result);
        // if (!isset($result[1])) {
        //     // self::doLogInfo(__FUNCTION__, __LINE__ . print_r($result, true) . "---->" . $edit['body']);
        // }
        // $result = $result[1];

        // 匹配打卡内容
        preg_match_all("/type=hidden name=wtOR_(\d{1}) value=\"(.*?)\"/i", $body, $matches);
        $config = array();
        foreach ($matches[2] as $value) {
            $config[] = explode("\|/", $value);
        }
        if (!isset($config[0])) throw new cuitException('error');
        $data = '{"healthStatus":[{"title":"现居住地","key":"place","select":["——请选择——","航空港校内","龙泉校内","新气象小区","成信家园","成都（校外）","外地"],"index":"1","outLand":{}},{"title":"居住地状态","key":"placeStatus","select":["——请选择——","一般地区","疫情防控重点地区","所在小区被隔离管控"],"index":"1"},{"title":"工作状态","key":"workStatus","select":["——请选择——","航空港校内上班或学习","龙泉校内上班或学习","在校外完成实习任务","在校外","在家"],"index":"1"},{"title":"个人健康状况","key":"personHealthStatus","select":["——请选择——","正常","有新冠肺炎可疑症状","疑似感染新冠肺炎","确诊感染新冠肺炎","确诊感染新冠肺炎但已康复","有呕吐情况","有腹泻情况","有呕吐＋腹泻情况"],"index":"1"},{"title":"个人生活状态","key":"personLifeStatus","select":["——请选择——","正常","住院治疗","居家隔离观察","集中隔离观察","居家治疗"],"index":"1"},{"title":"家庭成员状况","key":"personStatus","select":["——请选择——","全部正常","有人有可疑症状","有人疑似感染","有人确诊感染","有人确诊感染但已康复"],"index":"1"}],"healthStatusOtherInfo":"","outIn":{"toPlace":"","toResult":"","out":[["??","今天","明天","后天"],["??","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22"]],"outIndex":[0,0],"in":[["??","当天","第2天","第3天"],["??","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23"]],"inIndex":[0,0]},"monthStatus":[{"type":"important","title":"(1)曾前往疫情防控重点地区？","isGo":false,"details":""},{"type":"danger","title":"(2)接触过疫情防控重点地区高危人员？","isGo":false,"details":""},{"type":"suspected","title":"(3)接触过感染者或疑似患者？","isGo":false,"details":""}],"transport":{"method":["—请选择—","飞机","火车","汽车","轮船","私家车或专车","其他"],"methodIndex":0,"toolId":"","backTime":{"month":"","day":""}}}';
        $data = json_decode($data, true);
        $data['checkInTime'] = $time;
        // $data['requestRet'] = $result;
        $data['JSZX_cookie'] = PARAM['cookie'];
        // $data['link'] = PARAM['link'];
        //  ------------个人健康现状-----------------------
        $data['healthStatus'][0]['index'] = intval($config[0][0]);
        $data['healthStatus'][0]['outLand']['province'] = $config[0][1];
        $data['healthStatus'][0]['outLand']['city'] = $config[0][2];
        $data['healthStatus'][0]['outLand']['area'] = $config[0][3];
        $data['healthStatus'][1]['index'] = intval($config[0][4]);
        $data['healthStatus'][2]['index'] = intval($config[0][5]);
        $data['healthStatus'][3]['index'] = intval(isset($config[0][6]) ? $config[0][6] : 0);
        $data['healthStatus'][4]['index'] = intval(isset($config[0][7]) ? $config[0][7] : 0);
        $data['healthStatus'][5]['index'] = intval(isset($config[0][8]) ? $config[0][8] : 0);
        $data['healthStatusOtherInfo'] = isset($config[0][9]) ? $config[0][9] : '';

        // -------------申请进出学校(无需求则不填)-----------------------
        // if (isset($config[1][0]))
        //     $data['outIn']['toPlace'] = $config[1][0];
        // if (isset($config[1][1]))
        //     $data['outIn']['toResult'] = $config[1][1];
        // if (isset($config[1][2]))
        //     $data['outIn']['outIndex'][0] = $config[1][2];

        // if (isset($config[1][3])) {
        //     if (0 == strlen($config[1][3])) $config[1][3] = 5;
        //     $data['outIn']['outIndex'][1] = intval($config[1][3]) - 5;
        // }
        // if (isset($config[1][4]))
        //     $data['outIn']['inIndex'][0] = $config[1][4];
        // if (isset($config[1][5])) {
        //     if (0 == strlen($config[1][5])) $config[1][5] = 6;
        //     $data['outIn']['inIndex'][1] = intval($config[1][5]) - 6;
        // }
        // -------------最近一个月以来的情况------------
        $checkbox = array(
            'Y' => true,
            'N' => false
        );
        // print_r($config);
        $data['monthStatus'][0]['isGo'] = $checkbox[isset($config[1][0]) ? $config[1][0] : 'N'];
        $data['monthStatus'][0]['details'] = isset($config[1][1]) ? $config[1][1] : '';
        $data['monthStatus'][1]['isGo'] = $checkbox[isset($config[1][2]) ? $config[1][2] : 'N'];
        $data['monthStatus'][1]['details'] = isset($config[1][3]) ? $config[1][3] : '';
        $data['monthStatus'][2]['isGo'] = $checkbox[isset($config[1][4]) ? $config[1][4] : 'N'];
        $data['monthStatus'][2]['details'] = isset($config[1][5]) ? $config[1][5] : '';

        // -------------从外地返校(预计，目前已在成都的不填)情况------------
        $data['transport']['methodIndex'] = intval($config[2][0]);
        $data['transport']['toolId'] = $config[2][1];
        $data['transport']['backTime']['month'] = $config[2][2];
        $data['transport']['backTime']['day'] = $config[2][3];

        return array(
            'status' => 2000,
            'errorCode' => 2000,
            'title' => $title,
            'edit' => $data
        );
    }
    public static function get_between($input, $start, $end) {
        $substr = substr($input, 
            strlen($start) + strpos($input, $start),
            (strlen($input) - strpos($input, $end)) * (-1));
        return $substr;
    }

    /**
     * 打卡操作（Jszx控制器调用）
     */
    public static function doCheckIn()
    {
        $post = self::initPostData();
        if (!isset($post['JSZX_cookie'])) throw new cuitException("参数缺失");
        if(isset($post['data']))
            $body = self::json2body($post['data']);
        else
        // TODO:后期去除本部分
            $body = self::json2body($post);
        $http = new EasyHttp();
        $edit = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Xs/netks/editSjRs.asp", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $post['JSZX_cookie'],
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
        if (is_object($edit)) throw new cuitException("服务器网络错误", 10511);
        if (200 != $edit['response']['code']) throw new cuitException("未登录");
        $edit['body'] = self::str2UTF8($edit['body']);

        preg_match("/>打卡时间：(.*?)<\//", $edit['body'], $time);
        if (false != strpos($edit['body'], "提交打卡成功！")) {
            echo json_encode(
                array(
                    'status' => 2000,
                    'errorCode' => 2000,
                    'errMsg' => $time[1]
                )
            );
        } else {
            echo json_encode(
                array(
                    'status' => 2005,
                    'errorCode' => 2005,
                    'errMsg' => '失败了╮(╯▽╰)╭'
                )
            );
        }
    }

    /**
     * 提交打卡数据（Task控制器调用）
     */
    public static function postCheckIn($cookie, $body)
    {
        $ret = array();
        $http = new EasyHttp();
        $edit = $http->request("http://jszx-jxpt.cuit.edu.cn/Jxgl/Xs/netks/editSjRs.asp", array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                "cookie" => $cookie,
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
        if (is_object($edit)) throw new cuitException("服务器网络错误", 10511);
        if (200 != $edit['response']['code']) throw new cuitException("阁下好像还未登录");
        $edit['body'] = self::str2UTF8($edit['body']);
        // print_r($edit);
        preg_match("/>打卡时间：(.*?)<\//", $edit['body'], $time);
        if (false != strpos($edit['body'], "提交打卡成功！")) {
            $ret = array(
                'status' => 2000,
                'errorCode' => 2000,
                'errMsg' => $time[1]
            );
        } else {
            $ret = array(
                'status' => 2005,
                'errorCode' => 2005,
                'errMsg' => '失败了╮(╯▽╰)╭'
            );
        }
        return $ret;
    }

    /**
     * 获取 post 参数; 在 content_type 为 application/json 时，自动解析 json（Jszx控制器间接调用）
     * @return array
     */
    public static function initPostData()
    {
        if (empty($_POST) && isset($_SERVER['CONTENT_TYPE']) && false !== strpos($_SERVER['CONTENT_TYPE'], 'application/json')) {
            $content = file_get_contents('php://input');
            $post = (array)json_decode($content, true);
        } else {
            $post = $_POST;
        }
        return $post;
    }

    /**
     * （Jszx控制器间接调用）
     */
    public static function str2UTF8($str)
    {
        // 编码处理
        $encoding = mb_detect_encoding($str, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
        // 如果字符串的编码格式不为UTF_8就转换编码格式
        if ($encoding != 'UTF-8') {
            return mb_convert_encoding($str, 'UTF-8', $encoding);
        }
        return $str;
    }

    /**
     * （Jszx控制器间接调用）
     */
    public static function str2GBK($str)
    {
        $encoding = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GBK', 'GB2312', 'BIG5'));
        if ($encoding != 'GBK') {
            return mb_convert_encoding($str, 'GBK', $encoding);
        }
        return $str;
    }

    /**
     * （Jszx控制器间接调用）
     */
    public static function json2body($post)
    {
        if (!(isset($post['link']))) throw new cuitException("参数缺失");
        parse_str($post['link'], $arr);
        if (!isset($arr['UTp'])) throw new cuitException('参数有误');
        $healthStatus = $post['healthStatus'];
        $outIn = $post['outIn'];
        $monthStatus = $post['monthStatus'];
        $transport = $post['transport'];
        $checkbox = array(
            true => 'Y',
            false => 'N'
        );
        $outTime = array('', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22');
        $inTime = array('', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23');
        $data = array(
            "RsNum" => "4",
            "Id" => $arr['Id'],
            "Tx" => "33_1",
            "canTj" => "1",
            "isNeedAns" => "0",
            "UTp" => $arr['UTp'],
            "ObjId" => $arr['ObjId'],
            // -------------个人健康现状-----------------------
            "th_1" => "21650",
            "wtOR_1" => "a\|/a\|/a\|/a\|/a\|/a\|/a\|/a\|/a\|/a",
            "sF21650_1" => $healthStatus[0]['index'],       // (1)现居住地点为
            "sF21650_2" => self::str2GBK($healthStatus[0]['outLand']['province']),        // 外地详址[省]
            "sF21650_3" => self::str2GBK($healthStatus[0]['outLand']['city']),        // 外地详址[市]
            "sF21650_4" => self::str2GBK($healthStatus[0]['outLand']['area']),        // 外地详址[区(县)]
            "sF21650_5" => $healthStatus[1]['index'],        // (2)现居住地状态
            "sF21650_6" => $healthStatus[2]['index'],        // (3)今天工作状态
            "sF21650_7" => $healthStatus[3]['index'],        // (4)个人健康状况
            "sF21650_8" => $healthStatus[4]['index'],        // (5)个人生活状态
            "sF21650_9" => $healthStatus[5]['index'],        // (6)家庭成员状况
            "sF21650_10" => self::str2GBK($post['healthStatusOtherInfo']),        // (7)其他需要说明的情况
            "sF21650_N" => "10",
            // -------------申请进出学校(无需求则不填)-----------------------
            "th_2" => "21912",
            "wtOR_2" => "a\|/a\|/a\|/a\|/a\|/a",
            "sF21912_1" => self::str2GBK($outIn['toPlace']),        // 目的地
            "sF21912_2" => self::str2GBK($outIn['toResult']),        // 事由
            "sF21912_3" => $outIn['outIndex'][0],        // 出校[今/明/后]
            "sF21912_4" => $outTime[$outIn['outIndex'][1]],        // 出校[几点]
            "sF21912_5" => $outIn['inIndex'][0],        // 回校[当天/第2天/第3天]
            "sF21912_6" => $inTime[$outIn['inIndex'][1]],        // 回校[几点]
            "sF21912_N" => "6",
            // -----------最近一个月以来的情况------------
            "th_3" => "21648",
            "wtOR_3" => "N\|/666\|/N\|/666\|/N\|/666",
            "sF21648_1" => $checkbox[$monthStatus[0]['isGo']],        // (1)曾前往疫情防控重点地区？
            "sF21648_2" => self::str2GBK($monthStatus[0]['details']),        // 若曾前往，请写明时间、地点及简要事由
            "sF21648_3" => $checkbox[$monthStatus[1]['isGo']],        // (2)接触过疫情防控重点地区高危人员
            "sF21648_4" => self::str2GBK($monthStatus[1]['details']),        // 若接触过，请写明时间、地点及简要事由
            "sF21648_5" => $checkbox[$monthStatus[2]['isGo']],        // (3)接触过感染者或疑似患者？
            "sF21648_6" => self::str2GBK($monthStatus[2]['details']),        // 若接触过，请写明时间、地点及简要事由
            "sF21648_N" => "6",
            // -----------从外地返校(预计，目前已在成都的不填)情况------------
            "th_4" => "21649",
            "wtOR_4" => "6\|/666\|/666\|/666",
            "sF21649_1" => $transport['methodIndex'],        // 主要交通方式
            "sF21649_2" => self::str2GBK($transport['toolId']),        // 公共交通的航班号、车次等
            "sF21649_3" => $transport['backTime']['month'],        // 返校（预计）时间[月]
            "sF21649_4" => $transport['backTime']['day'],        // 返校（预计）时间[日]
            "sF21649_N" => "4",
            "zw1" => "",
            "cxStYt" => "A",
            "zw2" => "",
            "B2" => self::str2GBK("提交打卡"),
        );
        return http_build_query($data);
    }
}
