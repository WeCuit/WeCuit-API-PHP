<?php
// @Deprecated
class TheolController extends BaseController
{
    public function loginAction()
    {
        $this->loader->library('easyHttp');
        // https://sso.cuit.edu.cn/authserver/login?service=http%3A%2F%2Fjxpt.cuit.edu.cn%2Fmeol%2Fhomepage%2Fcommon%2Fsso_login.jsp%3Bjsessionid%3D34885DF8A337807A7FBDD3B8DE6E92DD
        $SSO_TGC = "TGC=" . PARAM['SSO_TGC'] . ";";
        $theolCookie = PARAM['theolCookie'];

        $url = "https://sso.cuit.edu.cn/authserver/login?service=http://jxpt.cuit.edu.cn/meol/homepage/common/sso_login.jsp;{$theolCookie}";
        $http = new EasyHttp();
        $login = $http->request($url, array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 2,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => $SSO_TGC . $theolCookie,
                'Referer' => "http://jxpt.cuit.edu.cn/"
            ),    //	header信息
            'cookies' => array(),    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($login)) throw new Exception("服务器网络错误", 10511);
        $ret = array();
        if (302 === $login['response']['code']) {
            $ret['status'] = 2000;
            $ret['errorCode'] = 2000;
        } else {
            $ret['status'] = 12401;
            $ret['errorCode'] = 12401;
            $ret['errMsg'] = 'SSO未登录';
        }
        echo json_encode($ret);
    }

    public function loginDirectAction(){
        
        $sId = PARAM['sId'];
        $theolCookie = PARAM['theolCookie'] . ";";
        $rsa = $this->initRSA();
        $sPass = $rsa->RSAPrivateDecrypt(PARAM['sPass']);

        $this->loader->library('easyHttp');
        $http = new EasyHttp();
        $login = $http->request('http://jxpt.cuit.edu.cn/meol/loginCheck.do', array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.0',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => PARAM['cookie']
            ),    //	header信息
            'cookies' => array(),    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
    }

    public function courseListAction()
    {
        // throw new Exception("校方登录策略变动，暂停服务", 21503);
        $this->loader->library('easyHttp');
        $http = new EasyHttp();
        $list = $http->request('http://jxpt.cuit.edu.cn/meol/lesson/blen.student.lesson.list.jsp', array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.0',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => PARAM['cookie']
            ),    //	header信息
            'cookies' => array(),    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($list)) throw new Exception("服务器网络错误", 10511);
        // if(200 != $list['response']['code'])throw new Exception("教学平台异常{$list['response']['code']}", 21 . $list['response']['code']);
        $ret = array();
        if (isset($list['cookies'][0])) {
            // 未登录
            $ret['status'] = 21401;
            $ret['errorCode'] = 21401;
            $ret['theolCookie'] = "{$list['cookies'][0]->name}={$list['cookies'][0]->value}";
        }
        if (false !== strpos($list['body'], 'Permission Denied')) {
            // 未登录
            $ret['status'] = 21401;
            $ret['errorCode'] = 21401;
            $ret['errMsg'] = '教学平台未登录';
        } else {
            $ret['status'] = 2000;
            $ret['errorCode'] = 2000;
            $this->loader->helper('theol');
            $ret['list'] = courseListHandle($list['body']);
        }
        echo json_encode($ret);
    }

    public function dirTreeAction()
    {
        // http://jxpt.cuit.edu.cn/meol/common/script/xmltree.jsp?lid=25039&groupid=4&_=1716
        $lid = PARAM['lid'];
        $url = "http://jxpt.cuit.edu.cn/meol/common/script/xmltree.jsp?lid={$lid}&groupid=4&_=1716";
        $this->loader->library('easyHttp');
        $http = new EasyHttp();
        $dirXml = $http->request($url);
        if (is_object($dirXml)) throw new Exception("服务器网络错误", 10511);

        $this->loader->helper('theol');
        $json = json_decode(json_encode(simplexml_load_string($dirXml['body'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        // echo json_encode($json);
        // exit;
        $ret['dir'] = dirTreeHandle($json['item']);
        $ret['status'] = 2000;
        $ret['errorCode'] = 2000;
        echo json_encode($ret);
    }

    public function folderListAction()
    {
        $lid = PARAM['lid'];
        $folderId = PARAM['folderId'];
        $theolCookie = PARAM['theolCookie'];

        // http://jxpt.cuit.edu.cn/meol/common/script/listview.jsp?lid=25039&folderid=142416
        $url = "http://jxpt.cuit.edu.cn/meol/common/script/listview.jsp?lid={$lid}&folderid={$folderId}";
        $this->loader->library('easyHttp');
        $http = new EasyHttp();
        $folderList = $http->request($url, array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.0',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => $theolCookie
            ),    //	header信息
            'cookies' => array(),    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($folderList)) throw new Exception("服务器网络错误", 10511);

        $html = $folderList['body'];
        $this->loader->helper('theol');
        // print_r($folderList);
        $folderList = folderListHandle($html);
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'dir' => $folderList
        ));
    }

    public function fileSuffixAction()
    {
        $fileId = PARAM['fileId'];
        $resId = PARAM['resId'];
        $lid = PARAM['lid'];
        $theolCookie = PARAM['theolCookie'];

        $url = "http://jxpt.cuit.edu.cn/meol/common/script/download.jsp?fileid={$fileId}&resid={$resId}&lid={$lid}";
        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' =>
                "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
                    "Cookie:{$theolCookie} \r\n" .
                    "Pragma:no-cache\r\n",
            )
        );
        $context = stream_context_create($opts);
        if (!($h = get_headers($url, null, $context)))
            throw new cuitException("服务器网络异常");
        $filename = substr($h[3], strpos($h[3], "name=\"") + 6, -1);
        preg_match("/\.(\w+)/i", $filename, $type);
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'file' => array(
                'name' => $filename,
                'suffix' => $type[1],
            ),
            'suffix' => $type[1],
        ));
    }
    public function downloadFileAction()
    {
        $fileId = PARAM['fileId'];
        $resId = PARAM['resId'];
        $lid = PARAM['lid'];
        $theolCookie = PARAM['cookie'];
        // http://jxpt.cuit.edu.cn/meol/common/script/download.jsp?fileid=1319205&resid=142988&lid=25039
        $url = "http://jxpt.cuit.edu.cn/meol/common/script/download.jsp?fileid={$fileId}&resid={$resId}&lid={$lid}";
        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' =>
                "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
                    "Cookie:{$theolCookie} \r\n" .
                    "Pragma:no-cache\r\n",
            )
        );
        $context = stream_context_create($opts);
        // if (!($h = get_headers($url, null, $context)))
        //     throw new cuitException("服务器网络异常");
        // header($h[0]);
        
        // // 取文件名
        // $filename = substr($h[3], strpos($h[3], "name=\"") + 6, -1);
        
        $fileAddr = CACHE_PATH . "files/theol_{$fileId}_{$resId}_{$lid}";
        
        // 文件存在直接输出
        // if (file_exists($fileAddr)) {
        //     header("content-type:" . mime_content_type($fileAddr));
        //     echo file_get_contents($fileAddr);
        //     exit;
        // }

        // 文件不存在，正式处理
        $this->loader->library('easyHttp');
        $this->loader->helper('theol');

        // 获取content-type
        $http = new EasyHttp();
        // http://jxpt.cuit.edu.cn/meol/common/script/attribute_file.jsp?lid=25039&resid=144636
        $attribute_url = "http://jxpt.cuit.edu.cn/meol/common/script/attribute_file.jsp?lid={$lid}&resid={$resId}";
        $attribute = $http->request($attribute_url, array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => $theolCookie
            ),    //	header信息
            'cookies' => array(),    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if(is_object($attribute))
        throw new cuitException("服务器网络异常", 10511);
        $contentType = getFileType($attribute['body']);
        
        header("Content-Type:{$contentType}");
        
        // 中转文件
        if (!($fp = fopen($url, "rb", false, $context))) throw new cuitException("服务器网络异常");
        file_put_contents($fileAddr, '');
        $c = fread($fp, 4096);
        if(false !== strpos($c, '<script type="text/javascript">alert'))
        {
            // 频繁访问
            header('HTTP/1.1 403 Forbidden');
        }else{
            file_put_contents($fileAddr, $c, FILE_APPEND);
            echo $c;
            while (!feof($fp)) {
                $c = fread($fp, 4096);
                file_put_contents($fileAddr, $c, FILE_APPEND);
                echo $c;
            }
        }
        fclose($fp);
    }
}
