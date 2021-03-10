<?php

class WwwController extends BaseController
{
    // function __construct()
    // {
    //     // echo "WwwController Construct\r\n";
    //     // $wwwModel = new WwwModel("user");
    // }
    // @Deprecated
    function getNewsAction()
    {
        $wwwModel = new WwwModel();
        $id = PARAM['id'] ? PARAM['id'] : 1;
        $wwwModel->getNews($id);
    }

    // 获取新闻内容
    // @Deprecated
    public function getNewsContentAction()
    {
        $this->loader->library('easyHttp');
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $http = new EasyHttp();
        $news = $http->request('https://www.cuit.edu.cn/' . $_GET['link'], array(
            'method' => $method,        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 5,        //	最大重定向次数
            'httpversion' => '1.0',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                //  'cookie' => PARAM['cookie']
            ),    //	header信息
            'cookies' => array(),    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($news)) throw new cuitException("服务器网络错误", 10511);
        $news['body'] = str_replace(array("\r\n", "\r", "\n"), "", $news['body']);
        preg_match("/<iframe src='(.*?)'/", $news['body'], $link);
        if (!isset($link[1])) {
            // self::doLogInfo(__FUNCTION__, $_GET['link']);
            throw new cuitException("解析错误");
        }
        $news = $http->request('https://www.cuit.edu.cn' . $link[1], array(
            'method' => $method,        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 5,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                //  'cookie' => PARAM['cookie']
            ),    //	header信息
            'cookies' => array(),    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($news)) throw new cuitException("服务器网络错误", 10511);
        $news['body'] = str_replace(array("\r\n", "\r", "\n"), "", $news['body']);
        preg_match("/<body>(.*?)<\/body>/", $news['body'], $con);
        preg_match("/<title>(.*?)<\/title>/", $news['body'], $title);
        $con[0] = str_replace('href="/News/file/', 'href="https://www.cuit.edu.cn/News/file/', $con[0]);
        //     exit;
        echo json_encode(
            array(
                'status' => 2000,
                'errorCode' => 2000,
                'data' => $con[0],
                'title' => $title[1]
            )
        );
    }

    /**
     * 获取新闻列表V2（自动化）
     * 
     * @Deprecated
     */
    public function updateNewsListAction()
    {
        $this->loader->library('easyHttp');
        self::updateNewsListFunc(1);
        self::updateNewsListFunc(2);
        self::updateNewsListFunc(4);
        self::updateNewsListFunc(5);
        self::updateNewsListFunc(7);
        self::updateNewsListFunc(10);
    }

    private static function updateNewsListFunc($id)
    {
        $file = CACHE_PATH . "news/{$id}.json";
        $et = file_exists($file) ? filemtime($file) : 0;
        // 5分钟更新一次
        if (60 * 5 > time() - $et) {
            return file_get_contents($file);
        }
        $http = new EasyHttp();
        $news = $http->request('https://www.cuit.edu.cn/NewsList?id=' . $id, array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 5,        //	最大重定向次数
            'httpversion' => '1.0',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                //  'cookie' => PARAM['cookie']
            ),    //	header信息
            'cookies' => array(),    //	关联数组形式的cookie信息
            'body' => null,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => false,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($news)) return false;
        if (empty($news['body'])) return;
        $news['body'] = str_replace(array("\r\n", "\r", "\n"), "", $news['body']);

        $newsList = array(array());
        preg_match("/<!--中间列表简目 start-->(.*?)<!--中间列表简目 end-->/i", $news['body'], $newsList);
        if (!isset($newsList[1])) {
            // self::doLogInfo(__FUNCTION__, __LINE__ . "||||" . print_r($news, true));
            throw new cuitException("服务器数据异常");
        }
        // 	preg_match("/<span id=\"labCountOfPage\">(\d+)</i", $newsList[1], $page);
        preg_match_all("/<a href='(.*?)' target='_blank'>(.*?)<\/a> <font class='datetime'>(.*?)<\/font><\/li>/i", $newsList[1], $newsList);
        if (!isset($newsList[0])) {
            // self::doLogInfo(__FUNCTION__, __LINE__ . "||||" . $news['body']);
            throw new cuitException("服务器数据异常");
        }
        $list = array();
        foreach ($newsList[0] as $key => $value) {
            $list['list'][] = array(
                'title' => $newsList[2][$key],
                'link' => $newsList[1][$key],
                'date' => $newsList[3][$key]
            );
        }
        $ret = json_encode(
            array(
                'status' => 2000,
                'errorCode' => 2000,
                'data' => $list
            )
        );
        file_put_contents($file, $ret);
        return $ret;
    }
}
