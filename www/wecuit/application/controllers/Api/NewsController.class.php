<?php
class NewsController extends BaseController{
    public function getListAction(){
        // 来源
        $source = PARAM['source'];
        // 标签
        $tag = PARAM['tag'];
        // 页面编号
        $page = PARAM['page'];
        $file = CACHE_PATH . "/news/{$source}/{$tag}_{$page}.json";
        
        if(file_exists($file))
        {
            echo file_get_contents($file);
        }else{
            echo json_encode(array(
                'status' => 20404,
                'errorCode' => 20404,
                'list' => array(
                    'title' => "页面不存在",
                    'date' => date("Y-m-d H:i:s"),
                    'link' => ""
                )
            ));
        }
    }
    // Deprecated 
    public function getTagsAction(){
        // 来源
        $source = PARAM['source'];
        
        $file = CACHE_PATH . "/news/{$source}/tags.json";
        
        if(file_exists($file))
        {
            echo file_get_contents($file);
        }else{
            echo json_encode(array(
                'status' => 20404,
                'errorCode' => 20404,
                'msg' => "标签数据丢失"
            ));
        }
    }
    public function getTagsV2Action(){
        // 来源
        $source = PARAM['source'];
        
        $file = CACHE_PATH . "/news/{$source}/tags.json";
        
        if(file_exists($file))
        {
            echo json_encode(array(
                'errorCode' => 2000,
                'tags' => json_decode(file_get_contents($file))
            ));
        }else{
            echo json_encode(array(
                'status' => 20404,
                'errorCode' => 20404,
                'msg' => "标签数据丢失"
            ));
        }
    }

    public function getContentAction(){
        $source = PARAM['source'];
        $link = PARAM['link'];
        $this->loader->library('easyHttp');
        if('home' == $source)
        {
            $this->getWwwNewsContent($link);
            exit;
        }
        $http = new EasyHttp();
        $con = $http->request($link, array('timeout' => 5));
        if(is_object($con)){
           echo "服务器网络异常";
           exit;
        }
        
        $body = $con['body'];

        preg_match("/<title>(.*?)<\/title>/i", $body, $title);
        if(!isset($title[0]))
        {
            echo "标题未找到!~";
            return;
        }

        // $start = strpos($body, "<form");
        // $end = strpos($body, "</form");
        // if(!$end)
        // {
        //     $end = strripos($body, "class=\"footer\"");
        // }
        // if(!$start || !$end)
        // {
        //     echo (!$start?"起始":'') . (!$end?"终止":'') . "位置未找到!~";
        //     return;
        // }
        // $start = strpos($body, ">", $start) + 1;
        // $content = substr($body, $start, $end - $start);

        $dom = new DOMDocument();
        @$dom->loadHTML($body);
        $dom->normalize();

        $xpath = new DOMXPath($dom);
        $ele = null;
        switch($source)
        {
            case 'gl':
                $ele = $xpath->query('/html/body/table[2]/tbody/tr/td/table[2]/tbody/tr[2]/td[4]/table/tbody/tr[2]/td/form/table');
                break;

            case 'tj':
                $ele = $xpath->query('/html/body/table[3]/tbody/tr[1]/td[3]/table[2]/tbody/tr/td/form/table');
                break;

            case 'whys':
                $ele = $xpath->query('/html/body/table[2]/tbody/tr/td/table[2]/tr[2]/td[4]/table/tr[2]/td/form/table');
                break;

            case 'wl':
                $ele = $xpath->query('/html/body/table[5]/tbody/tr[2]/td[4]/table/tbody/tr[2]/td/div/form/table');
                break;

            case 'dqkx':
                $ele = $xpath->query('/html/body/table[5]/tbody/tr/td[1]/table[3]/tbody/tr/td[2]/table/tbody/tr/td/form/table');
                break;

            case 'dzgc':
                $body = "<META content=\"text/html; charset=UTF-8\" http-equiv=\"Content-Type\">" . $body;
                @$dom->loadHTML($body);
                $dom->normalize();
        
                $xpath = new DOMXPath($dom);
                $ele = $xpath->query('/html/body/table/tbody/tr[4]/td/table/tbody/tr/td[4]/table/tbody/tr[3]/td/table/tbody/tr/td/form/table');
                break;

            case 'gdgc':
                $ele = $xpath->query('/html/body/div[5]/div[2]/form/div');
                break;
            
            case "compute":
                $ele = $xpath->query('/html/body/div[2]/div[2]/div/div/div/form/div/div/div/div');
                break;
            
            case "kzgc":
                $ele = $xpath->query('//*[@id="vsb_content"]');
                break;
            
            case "rjgc":
                $ele = $xpath->query('/html/body/table[4]/tbody/tr/td[2]/table[2]/tbody/tr/td/table/tbody/tr[2]/td/form/table');
                break;

            case "txgc":
                $ele = $xpath->query('/html/body/div[2]/div/div[2]/div[2]/form');
                break;
                
            case "wgy":
                $ele = $xpath->query('//*[@id="vsb_newscontent"]');
                break;

            case "wlaq":
                $ele = $xpath->query('/html/body/div[3]/div[2]/div[2]/form/div');
                break;

            case "yysx":
                $ele = $xpath->query('/html/body/div[4]/div/div[2]/div/div/div/div/table/tbody/tr/td');
                break;

            case "zyhj":
                $ele = $xpath->query('/html/body/table[3]/tbody/tr[1]/td[3]/table[2]/tbody/tr/td/form/table');
                break;

            case "qkl":
                $ele = $xpath->query('/html/body/div[4]/div/div[2]/div[2]/form/div');
                break;

            case "jwc":
                $ele = $xpath->query('/html/body/nav[3]/form/div');
                break;

            default:
                echo "无法识别的来源-->{$source}";
                exit;
                break;
        }
        if(0 == $ele->length)
        {
            echo "<br />\r\n{$source}->link=>{$link}<br />\r\n解析出错";
            exit;
        }
        $content = $dom->saveHTML($ele->item(0));

        $content = preg_replace_callback("/<img[\s\S]*?src=\"(.*?)\"/i", function($args){
            // "<img src=\"https://jwc.cuit.edu.cn\\1\""
            // echo strpos($args[1], "http") . "\r\n";
            // print_r($args);
            // exit;
            if(0 !== strpos($args[1], "http"))
                return str_replace('src="', 'src="https://jwc.cuit.edu.cn/', $args[0]);
            else return $args[0];
        }, $content);
        $content = preg_replace("/<span>.*?<span>关闭窗口<\/span>.*?<\/span>/i", '', $content);

        echo $title[0] . $content . "<br />";
    }

    public function downFileAction()
    {
        $cookie = PARAM['cookie'];
        $path = PARAM['downUrl'];
        $code = PARAM['codeValue'];
        $domain = PARAM['domain'];
        $this->loader->library('easyHttp');
        $uri = "http://{$domain}{$path}";
        $http = new EasyHttp();
        $down = $http->request($uri . "&codeValue=" . $code, array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36 Edg/86.0.622.43',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
                'cookie' => $cookie,
                'referer' => "http://{$domain}"
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
                $link = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/Api/File/redirect/type.{$m[1]}?link=https://jwc.cuit.edu.cn{$down['headers']['location']}";
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
            $cap = $http->request("http://{$domain}/system/resource/js/filedownload/createimage.jsp?randnum=" . time(), array(
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
    }

    
    protected function getWwwNewsContent($link)
    {
        $http = new EasyHttp();
        $news = $http->request($link, array(
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
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($news)){
            echo "服务器网络错误";
            exit;
        }
        
        $dom = new DOMDocument();
        
        //从一个字符串加载HTML
        @$dom->loadHTML($news['body']);
        
        //使该HTML规范化
        $dom->normalize();
        
        //用DOMXpath加载DOM，用于查询
        $xpath = new DOMXPath($dom);
        
        $iframe = $xpath->query("//*[@id=\"NewsContent\"]");
        $link = $iframe->item(0)->attributes->item(0)->value;
        
        $news = $http->request('https://www.cuit.edu.cn' . $link, array(
            'method' => 'GET',        //	GET/POST
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
        if (is_object($news)){
            echo "服务器网络错误";
            exit;
        }
        $news['body'] = str_replace(array("\r\n", "\r", "\n"), "", $news['body']);
        preg_match("/<title>(.*?)<\/title>/", $news['body'], $title);
        preg_match("/<body>(.*?)<\/body>/", $news['body'], $con);
        $con[0] = str_replace('href="/News/file/', 'href="https://www.cuit.edu.cn/News/file/', $con[0]);
        //     exit;
        echo $title[0] . $con[0];
    }
}