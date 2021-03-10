<?php
class NewsList
{
    private $dir;
    private $source;
    private $tags;
    private $pattern;
    private $uriExp;
    private $sort = false;

    public function __construct($dir, $source, $tags, $pattern, $uriExp, $sort = false)
    {
        $this->dir = $dir;
        $this->source = $source;
        $this->tags = $tags;
        $this->pattern = $pattern;
        $this->uriExp = $uriExp;
        $this->sort = $sort;
    }
    public static function pull($dir)
    {

        /**
         * {
         *      "domain": "",
         *      "list": {
         *          "title":"",
         *          "date":"",
         *          "link":"",
         *      }
         * }
         */

        // ================教务处================
        $tags = array(
            array(
                "name" => 1161,
                "text" => "通知公告"
            ),
            array(
                "name" => 1171,
                "text" => "考试安排"
            ),
            array(
                "name" => 1172,
                "text" => "课程安排"
            ),
            array(
                "name" => 1173,
                "text" => "选课通知"
            ),
            array(
                "name" => 1174,
                "text" => "国家级考试"
            ),
            array(
                "name" => 1182,
                "text" => "学科竞赛"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\">(.*?)<\/a>[\s\S]*?(\d+\/\d+)<\/h6>/i";
        // http://jwc.cuit.edu.cn/tymb.jsp?a126120p=2&wbtreeid=1171
        $uriExp = "http://jwc.cuit.edu.cn/tymb.jsp?wbtreeid=#tag#&a126120p=#page#";
        $pull = new NewsList($dir, 'jwc', $tags, $pattern, $uriExp);
        $pull->v1_pull();
        // exit;

        // ================管理学院================
        $tags = array(
            array(
                "name" => 1961,
                "text" => "学院公告"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+\/\d+\/\d+)&nbsp;<\/td>/i";
        $uriExp = "http://glxy.cuit.edu.cn/lby.jsp?wbtreeid=#tag#&a8p=#page#";
        $pull = new NewsList($dir, 'gl', $tags, $pattern, $uriExp, true);
        $pull->v1_pull();

        // ================统计学院================
        $tags = array(
            array(
                "name" => 1003,
                "text" => "通知公告"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+-\d+-\d+)&nbsp;<\/td>/i";
        $uriExp = "http://tjx.cuit.edu.cn/list.jsp?a6p=#page#&wbtreeid=#tag#";
        $pull = new NewsList($dir, 'tj', $tags, $pattern, $uriExp, true);
        $pull->v1_pull();

        // ================文化艺术学院================
        $tags = array(
            array(
                "name" => 1961,
                "text" => "学院公告"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+\/\d+\/\d+)&nbsp;<\/td>/i";
        $uriExp = "http://whys.cuit.edu.cn/lby.jsp?wbtreeid=#tag#&a8p=#page#";
        $pull = new NewsList($dir, 'whys', $tags, $pattern, $uriExp, true);
        $pull->v1_pull();

        // ================物流学院================
        $tags = array(
            array(
                "name" => 1970,
                "text" => "人才培养"
            ),
            array(
                "name" => 2034,
                "text" => "通知公告"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+\/\d+\/\d+)&nbsp;<\/td>/i";
        $uriExp = "http://wlxy.cuit.edu.cn/lby.jsp?wbtreeid=#tag#&a8p=#page#";
        $pull = new NewsList($dir, 'wl', $tags, $pattern, $uriExp, true);
        $pull->v1_pull();

        // ================大气科学学院================
        $tags = array(
            array(
                "name" => 1067,
                "text" => "通知公告"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+-\d+-\d+)&nbsp;<\/td>/i";
        // http://cas.cuit.edu.cn/list.jsp?a7t=9&a7p=2&a7c=30&urltype=tree.TreeTempUrl&wbtreeid=1068
        $uriExp = "http://cas.cuit.edu.cn/list.jsp?wbtreeid=#tag#&a7p=#page#";
        $pull = new NewsList($dir, 'dqkx', $tags, $pattern, $uriExp, true);
        $pull->v1_pull();

        // ================电子工程学院================
        $tags = array(
            array(
                "name" => 1515,
                "text" => "信息公告"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+\/\d+\/\d+)&nbsp;<\/td>/i";
        // http://dzgcxy.cuit.edu.cn/erji.jsp?a7t=8&a7p=2&a7c=25&urltype=tree.TreeTempUrl&wbtreeid=1515
        $uriExp = "http://dzgcxy.cuit.edu.cn/erji.jsp?wbtreeid=#tag#&a7p=#page#";
        $pull = new NewsList($dir, 'dzgc', $tags, $pattern, $uriExp, true);
        $pull->v1_pull();

        // ================光电工程学院================
        $tags = array(
            array(
                "name" => "xxgg",
                "text" => "通知公告"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\" title=\"(.*?)\"[\s\S]*?(\d+\/\d+\/\d+)&nbsp;<\/span>/i";
        // http://gdjsxy.cuit.edu.cn/xwgg/xxgg.htm
        $uriExp = "http://gdjsxy.cuit.edu.cn/xwgg/#tag#";
        $pull = new NewsList($dir, 'gdgc', $tags, $pattern, $uriExp);
        $pull->v2_pull();

        // ================计算机学院================
        $tags = array(
            array(
                'name' => "tzgg",
                "text" => "通知公告"
            ),
            array(
                'name' => "zhxw",
                "text" => "综合新闻"
            ),
            array(
                'name' => "jdxw",
                "text" => "焦点新闻"
            )
        );
        $pattern = "/id=\"line_u3_\d\"><A href=\"(.*?)\"[\s\S]*?<H3 class=\"h3\">(.*?)<\/[\s\S]*?date\">(.*?)<\//i";
        // http://gdjsxy.cuit.edu.cn/xwgg/xxgg.htm
        $uriExp = "http://jsjxy.cuit.edu.cn/xwgg/#tag#";
        $pull = new NewsList($dir, 'compute', $tags, $pattern, $uriExp);
        $pull->v2_pull();

        // ================控制工程学院================
        $tags = array(
            array(
                'name' => "tzgg",
                "text" => "通知公告"
            ),
        );
        $pattern = "/id=\"line_u6_\d\"[\s\S]*?<a href=\"(.*?)\" target=\"_blank\" title=\"(.*?)\"[\s\S]*?(\d+-\d+-\d+)<\/span>/i";
        // http://gdjsxy.cuit.edu.cn/xwgg/xxgg.htm
        $uriExp = "http://kzgcxy.cuit.edu.cn/gglm/#tag#";
        $pull = new NewsList($dir, 'kzgc', $tags, $pattern, $uriExp);
        $pull->v2_pull();
        
        // ================软件工程学院================
        $tags = array(
            array(
                "name" => 1064,
                "text" => "本科教学"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+\/\d+\/\d+)&nbsp;<\/td>/i";
        // http://rjgcxy.cuit.edu.cn/list.jsp?a3t=18&a3p=2&a3c=10&urltype=tree.TreeTempUrl&wbtreeid=1064
        $uriExp = "http://rjgcxy.cuit.edu.cn/list.jsp?wbtreeid=#tag#&a3p=#page#";
        $pull = new NewsList($dir, 'rjgc', $tags, $pattern, $uriExp, true);
        $pull->v1_pull();

        // ================通信工程学院================
        $tags = array(
            array(
                "name" => 1013,
                "text" => "通知公告"
            ),
        );
        $pattern = "/a href=\"(.*?)\" class=\"news-item\"[\s\S]*?<font color=\"black\">(.*?)<[\s\S]*?(\d+-\d+-\d+)</i";
        // http://txgcxy.cuit.edu.cn/txlist.jsp?a124815t=5&a124815p=2&a124815c=9&urltype=tree.TreeTempUrl&wbtreeid=1013
        $uriExp = "http://txgcxy.cuit.edu.cn/txlist.jsp?a124815c=10&wbtreeid=#tag#&a124815p=#page#";
        $pull = new NewsList($dir, 'txgc', $tags, $pattern, $uriExp, true);
        $pull->v1_pull();

        // ================外国语学院================
        $tags = array(
            array(
                "name" => 1293,
                "text" => "学院新闻"
            ),
            array(
                "name" => 1294,
                "text" => "通知公告"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+-\d+-\d+)]/i";
        // http://wgyxy.cuit.edu.cn/list.jsp?a3t=12&a3p=1&a3c=20&urltype=tree.TreeTempUrl&wbtreeid=1293
        $uriExp = "http://wgyxy.cuit.edu.cn/list.jsp?wbtreeid=#tag#&a3p=#page#";
        $pull = new NewsList($dir, 'wgy', $tags, $pattern, $uriExp);
        $pull->v1_pull();

        // ================网络空间安全学院================
        $tags = array(
            array(
                'name' => "tzgg",
                "text" => "通知公告"
            ),
        );
        $pattern = "/a><a href=\"(.*?)\">(.*?)<[\s\S]*?(\d+-\d+-\d+)</i";
        // http://cyber.cuit.edu.cn/tzgg/8.htm
        $uriExp = "http://cyber.cuit.edu.cn/#tag#";
        $pull = new NewsList($dir, 'wlaq', $tags, $pattern, $uriExp, true);
        $pull->v2_pull();

        // ================应用数学学院================
        $tags = array(
            array(
                'name' => "jxdt",
                "text" => "教学动态"
            ),
            array(
                'name' => "bkjy",
                "text" => "本科教育"
            ),
        );
        $pattern = "/<a href=\"(.*?)\" class=\"c124260\" title=\"(.*?)\"[\s\S]*?(\d+\/\d+\/\d+)<\/td/";
        // http://math.cuit.edu.cn/rcpy/bkjy.htm
        $uriExp = "http://math.cuit.edu.cn/rcpy/#tag#";
        $pull = new NewsList($dir, 'yysx', $tags, $pattern, $uriExp, true);
        $pull->v2_pull();

        // ================资源环境学院================
        $tags = array(
            array(
                "name" => 1003,
                "text" => "通知公告"
            ),
        );
        $pattern = "/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+-\d+-\d+)&nbsp;/i";
        // http://hjgcx.cuit.edu.cn/list.jsp?a6t=3&a6p=2&a6c=30&urltype=tree.TreeTempUrl&wbtreeid=1003
        $uriExp = "http://hjgcx.cuit.edu.cn/list.jsp?wbtreeid=#tag#&a6p=#page#";
        $pull = new NewsList($dir, 'zyhj', $tags, $pattern, $uriExp, true);
        $pull->v1_pull();

        // ================区块链产业学院================
        $tags = array(
            array(
                'name' => "tzgg",
                "text" => "通知公告"
            ),
            array(
                'name' => "xyxw",
                "text" => "学院新闻"
            ),
        );
        $pattern = "/<a href=\"(.*?)\"><FONT [\S]*?<\/FONT>&nbsp;(.*?)<\/a>[\s\S]*?(\d+-\d+-\d+)</";
        // http://qkl.cuit.edu.cn/xwzx/tzgg.htm
        $uriExp = "http://qkl.cuit.edu.cn/xwzx/#tag#";
        $pull = new NewsList($dir, 'qkl', $tags, $pattern, $uriExp, true);
        $pull->v2_pull();

        // ==================================================================
        // self::tj_pull($dir);
        // self::gl_pull($dir);
        self::home_pull($dir);
        // self::compute_pull($dir);
    }

    // 通用拉取模板1
    public function v1_pull()
    {
        $path = "{$this->dir}/{$this->source}";
        if (!file_exists($path)) mkdir($path);
        
        foreach($this->tags as $key => $value){
            $page = 1;
            $i = 1;
            while(($ret = $this->v1_list($value['name'], $page)) && ($page = $ret['next']))
            {
                unset($ret['next']);
                $ret['errorCode'] = $ret['status'] = 2000;
                file_put_contents("{$path}/{$value['name']}_{$i}.json", json_encode($ret));
                $i++;
            }
            if ($ret) {
                unset($ret['next']);
                $ret['errorCode'] = $ret['status'] = 2000;
                file_put_contents("{$path}/{$value['name']}_{$i}.json", json_encode($ret));
            }
            $this->tags[$key]['total'] = $i;
        }
        file_put_contents("{$path}/tags.json", json_encode($this->tags));
    }
    private function v1_list($tag, $page = 1)
    {
        $uri = str_replace(
            array("#tag#", "#page#"),
            array($tag, $page),
            $this->uriExp
        );
        $http = new EasyHttp();
        $news = $http->request($uri);
        if (is_object($news)) return false;
        if (empty($news['body'])) return false;
        preg_match_all($this->pattern, $news['body'], $list);
        unset($list[0]);
        
        $ret = array(
            'domain' => parse_url($this->uriExp, PHP_URL_HOST),
            'next' => false,
            'list' => array()
        );
        if(false !== strpos($news['body'], "class=\"Next\">下页</a>"))
        {
            $ret['next'] = $page + 1;
        }
        foreach ($list[1] as $key => $value) {
            $ret['list'][] = array(
                'date' => $list[3][$key],
                'title' => $list[2][$key],
                'link' => $value,
            );
        }
        if($this->sort)
        array_multisort($ret['list'], SORT_DESC);
        return $ret;
    }

    // 通用拉取模板2
    public function v2_pull()
    {
        $path = "{$this->dir}/{$this->source}";
        if (!file_exists($path)) mkdir($path);

        foreach ($this->tags as $key => $value) {
            $i = 1;
            $ret = null;
            $page = null;
            while (($ret = $this->v2_list($value['name'], $page)) && ($page = $ret['next'])) {
                unset($ret['next']);
                $ret['errorCode'] = $ret['status'] = 2000;
                file_put_contents("{$path}/{$value['name']}_{$i}.json", json_encode($ret));
                $i++;
            }
            if ($ret) {
                unset($ret['next']);
                $ret['errorCode'] = $ret['status'] = 2000;
                file_put_contents("{$path}/{$value['name']}_{$i}.json", json_encode($ret));
            }
            $this->tags[$key]['total'] = $i;
        }
        file_put_contents("{$path}/tags.json", json_encode($this->tags));
    }
    private function v2_list($tag, $page)
    {
        
        $uri = str_replace(
            "#tag#",
            $tag,
            $this->uriExp
        );
        $uri .= ($page ? "/{$page}" : ".htm");
        $http = new EasyHttp();
        $cc = $http->request($uri);
        if (is_object($cc)) return false;

        $link_pre = preg_replace("/\w+\.htm/", '', $uri, 1);
        $body = $cc['body'];
        preg_match_all($this->pattern, $body, $list);
        if (!isset($list[3][0])) return false;

        unset($list[0]);
        preg_match("/(\d+\.htm)\" class=\"Next/", $body, $next);

        $ret = array(
            'domain' => parse_url($this->uriExp, PHP_URL_HOST),
            'next' => isset($next[1]) ? $next[1] : false,
            'list' => array()
        );
        foreach ($list[1] as $key => $value) {
            // 真实路径处理
            $link = $link_pre . $value;
            $link = parse_url($link, PHP_URL_PATH);
            $link = $this->getRealPath($link);

            $ret['list'][] = array(
                'date' => $list[3][$key],
                'title' => $list[2][$key],
                'link' => $link,
            );
        }
        if($this->sort)
        array_multisort($ret['list'], SORT_DESC);
        return $ret;
    }
    
    /**
     * 获取真实路径
     * @param string $filename 需要处理的路径
     * @param string $split 分隔符
     * @return string
     * @author: unknown
     */
    private function getRealPath(string $filename, string $split = '/')
    {
        while(true) {
            if (FALSE === strpos($filename, $split . '.')) {
                break;
            }
            $filename = explode($split, $filename);
            foreach($filename as $k => $f) {
                if (($k && $f == '') || $f == '.') {
                    unset($filename[$k]);
                    break;
                }
                elseif ($f == '..') {
                    unset($filename[$k]);
                    if(isset($filename[$k-1]))
                        unset($filename[$k-1]);
                    break;
                }
            }
            $filename = implode($split, $filename);
        }
        return $filename;
    }
    // 统计学院
    // public static function tj_pull(String $dir)
    // {
    //     $path = "{$dir}/tj";
    //     if (!file_exists($path)) mkdir($path);
        
    //     $tags = array(
    //         array(
    //             "name" => 1003,
    //             "text" => "通知公告"
    //         ),
    //     );
    //     $page = 1;
    //     $i = 1;
    //     foreach($tags as $key => $value){
    //         while(($ret = self::tj_list($value['name'], $page)) && ($page = $ret['next']))
    //         {
    //             unset($ret['next']);
    //             $ret['status'] = 2000;
                // $ret['errorCode'] = 2000;
    //             file_put_contents("{$path}/{$value['name']}_{$i}.json", json_encode($ret));
    //             $i++;
    //         }
    //         if ($ret) {
    //             unset($ret['next']);
    //             $ret['status'] = 2000;
                // $ret['errorCode'] = 2000;
    //             file_put_contents("{$path}/{$value['name']}_{$i}.json", json_encode($ret));
    //         }
    //         $tags[$key]['total'] = $i;
    //     }
    //     file_put_contents("{$path}/tags.json", json_encode($tags));
    // }
    // public static function tj_list($tag, $page = 1)
    // {
    //     # code...
    //     $uri = "http://tjx.cuit.edu.cn/list.jsp?a6p={$page}&wbtreeid={$tag}";
    //     $http = new EasyHttp();
    //     $news = $http->request($uri);
    //     if (is_object($news)) return false;
    //     if (empty($news['body'])) return false;
    //     preg_match_all("/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+-\d+-\d+)&nbsp;<\/td>/i", $news['body'], $list);
    //     unset($list[0]);
        
    //     $ret = array(
    //         'domain' => "tjx.cuit.edu.cn",
    //         'next' => false,
    //         'list' => array()
    //     );
    //     if(false !== strpos($news['body'], "class=\"Next\">下页</a>"))
    //     {
    //         $ret['next'] = $page + 1;
    //     }
    //     foreach ($list[1] as $key => $value) {
    //         $ret['list'][] = array(
    //             'date' => $list[3][$key],
    //             'title' => $list[2][$key],
    //             'link' => $value,
    //         );
    //     }
    //     return $ret;
    // }

    // // 管理学院
    // public static function gl_pull(String $dir)
    // {
    //     $path = "{$dir}/gl";
    //     if (!file_exists($path)) mkdir($path);
        
    //     $tags = array(
    //         array(
    //             "name" => 1961,
    //             "text" => "学院公告"
    //         ),
    //     );
    //     $page = 1;
    //     $i = 1;
    //     foreach($tags as $key => $value){
    //         while(($ret = self::gl_list($value['name'], $page)) && ($page = $ret['next']))
    //         {
    //             unset($ret['next']);
    //             $ret['status'] = 2000;
                // $ret['errorCode'] = 2000;
    //             file_put_contents("{$path}/{$value['name']}_{$i}.json", json_encode($ret));
    //             $i++;
    //         }
    //         if ($ret) {
    //             unset($ret['next']);
    //             $ret['status'] = 2000;
                // $ret['errorCode'] = 2000;
    //             file_put_contents("{$path}/{$value['name']}_{$i}.json", json_encode($ret));
    //         }
    //         $tags[$key]['total'] = $i;
    //     }
    //     file_put_contents("{$path}/tags.json", json_encode($tags));
    // }
    // public static function gl_list($tag, $page = 1)
    // {
    //     # code...
    //     $uri = "http://glxy.cuit.edu.cn/lby.jsp?wbtreeid={$tag}&a8p={$page}";
    //     $http = new EasyHttp();
    //     $news = $http->request($uri);
    //     if (is_object($news)) return false;
    //     if (empty($news['body'])) return false;
    //     preg_match_all("/href=\"(.*?)\" target=\"_blank\"   title=\"(.*?)\"[\s\S]*?(\d+\/\d+\/\d+)&nbsp;<\/td>/i", $news['body'], $list);
    //     unset($list[0]);
        
    //     $ret = array(
    //         'domain' => "glxy.cuit.edu.cn",
    //         'next' => false,
    //         'list' => array()
    //     );
    //     if(false !== strpos($news['body'], "class=\"Next\">下页</a>"))
    //     {
    //         $ret['next'] = $page + 1;
    //     }
    //     foreach ($list[1] as $key => $value) {
    //         $ret['list'][] = array(
    //             'date' => $list[3][$key],
    //             'title' => $list[2][$key],
    //             'link' => $value,
    //         );
    //     }
    //     array_multisort($ret['list'], SORT_DESC);
    //     return $ret;
    // }

    // 学校首页
    public static function home_pull($dir){
        $path = "{$dir}/home";
        if (!file_exists($path)) mkdir($path);

        $tags = array(
            array(
                'name' => 1,
                'text' => "综合新闻"
            ),
            array(
                'name' => 2,
                'text' => "信息公告"
            ),
            array(
                'name' => 4,
                'text' => "学术动态"
            ),
            array(
                'name' => 5,
                'text' => "工作交流"
            ),
            array(
                'name' => 10,
                'text' => "文化活动"
            ),
            array(
                'name' => 7,
                'text' => "媒体成信"
            ),
        );
        foreach($tags as $value){
            if($ret = self::home_list($value['name'])){
                file_put_contents("{$path}/{$value['name']}_1.json", json_encode($ret));
            }
            $value['total'] = 1;
        }
        file_put_contents("{$path}/tags.json", json_encode($tags));
    }
    public static function home_list($tag){
        $http = new EasyHttp();
        $news = $http->request('https://www.cuit.edu.cn/NewsList?id=' . $tag);
        if (is_object($news)) return false;
        if (empty($news['body'])) return false;
        $news['body'] = str_replace(array("\r\n", "\r", "\n"), "", $news['body']);

        $newsList = array(array());
        preg_match("/<!--中间列表简目 start-->(.*?)<!--中间列表简目 end-->/i", $news['body'], $newsList);
        if (!isset($newsList[1])) {
            throw new cuitException("服务器数据异常");
        }
        preg_match_all("/<a href='(.*?)' target='_blank'>(.*?)<\/a> <font class='datetime'>(.*?)<\/font><\/li>/i", $newsList[1], $newsList);
        if (!isset($newsList[0])) {
            throw new cuitException("服务器数据异常");
        }
        $ret = array(
            'status' => 2000,
            'errorCode' => 2000,
            'domain' => "www.cuit.edu.cn",
            'list' => array()
        );
        foreach ($newsList[0] as $key => $value) {
            $ret['list'][] = array(
                'title' => $newsList[2][$key],
                'link' => $newsList[1][$key],
                'date' => $newsList[3][$key]
            );
        }
        return $ret;
    }

}
