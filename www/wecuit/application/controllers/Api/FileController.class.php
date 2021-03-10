<?php
class FileController extends BaseController
{
    function initAction()
    {
        print_r($_SERVER['REQUEST_URI']);
        if (preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $_SERVER['REQUEST_URI'])) {
            header("Content-Type: image");
            $fp = fopen("https://www.cuit.edu.cn/" . substr($_SERVER['REQUEST_URI'], 10), "rb");
            if (!$fp) throw new cuitException("服务器网络异常");
            while (!feof($fp)) {
                echo fread($fp, 4096);
            }
            fclose($fp);
        }
    }
    
    function redirectAction()
    {
        if (!isset($_GET['link'])) throw new cuitException("参数缺失");
        header('Location: ' . $_GET['link']);
    }
    // @Deprecated
    public static function transferAction()
    {
        $filename = CACHE_PATH . 'files/' . preg_replace('/^.+[\\\\\\/]/', '', $_GET['link']);
        $link = urldecode($_GET['link']);
        if(false === strpos($link, "http"))
        {
            throw new Exception("链接格式错误", 1);
        }
        if (file_exists($filename)) {
            header("content-type:" . mime_content_type($filename));
            echo file_get_contents($filename);
            exit;
        }
        $i = 0;
        while (!($h = get_headers($link)) && $i < 3) {
            $i++;
        }
        if ($i >= 3){
            header('HTTP/1.1 500 Internal Server Error');
            throw new cuitException("请求超时");
        }
        if (false !== strpos(json_encode($h), '404')) {
            header($h[0]);
            throw new cuitException("文件似乎不存在");
        }
        $fp = fopen($link, "rb");
        if (!$fp || !$h) throw new cuitException("服务器网络异常");
        foreach ($h as $value) {
            if (false != strstr($value, 'ype')) {
                header($value);
                break;
            }
        }
        file_put_contents($filename, '');
        while (!feof($fp)) {
            $c = fread($fp, 4096);
            file_put_contents($filename, $c, FILE_APPEND);
            echo $c;
        }
        fclose($fp);
    }
    public static function transferV2Action()
    {
        // 编码文件名
        $pos = strrpos($_GET['link'], '/') + 1;
        $_GET['link'] = substr($_GET['link'], 0, $pos) . urlencode(substr($_GET['link'], $pos));

        $_GET['link'] = str_replace('/http', 'http', $_GET['link']);
        $filename = CACHE_PATH . 'files/' . preg_replace('/^.+[\\\\\\/]/', '', $_GET['link']);
        $link_path = urldecode($_GET['link']);
        $link_pre = preg_replace("/\w+\.htm/", '', $_GET['page']);

        if(0 === strpos($link_path, "/")){
            $link = 'http://' . parse_url($link_pre, PHP_URL_HOST) . $link_path;
        }else if(false === strpos($link_path, 'http')){
            $link = $link_pre . $link_path;
            $link = parse_url($link);
            $link['path'] = self::getRealPath($link['path']);
            $link = self::unparse_url($link);
        }else{
            $link = $_GET['link'];
        }

        if(false === strpos($link, "http"))
        {
            throw new Exception("链接格式错误", 1);
        }
        if (file_exists($filename)) {
            header("content-type:" . mime_content_type($filename));
            echo file_get_contents($filename);
            exit;
        }
        $i = 0;
        while (!($h = get_headers($link)) && $i < 3) {
            $i++;
        }
        if ($i >= 3){
            header('HTTP/1.1 500 Internal Server Error');
            throw new cuitException("请求超时");
        }
        if (false !== strpos(json_encode($h), '404')) {
            header($h[0]);
            throw new cuitException("文件似乎不存在");
        }
        $fp = fopen($link, "rb");
        if (!$fp || !$h) throw new cuitException("服务器网络异常");
        foreach ($h as $value) {
            if (false != strstr($value, 'ype')) {
                header($value);
                break;
            }
        }
        file_put_contents($filename, '');
        while (!feof($fp)) {
            $c = fread($fp, 4096);
            file_put_contents($filename, $c, FILE_APPEND);
            echo $c;
        }
        fclose($fp);
    }
    /**
     * 反转url地址
     * @param array $parsed_url 需要反转parse_url方法
     * @return string
     * @author: An Yang
     * @since: 2019/7/24
     * @time: 11:41
     */
    protected static function unparse_url(array $parsed_url) {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
    private static function getRealPath($filename, $split = '/')
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
    private static function link_urldecode($url)
    {
        $url = rawurlencode($url);
        $a = array("%3A", "%2F", "%40");
        $b = array(":", "/", "@");
        $url = str_replace($a, $b, $url);
        return $url;
    }

    // @Deprecated
    function uploadCourseTableBackImgAction()
    {
        $code = PARAM['code'];
        $upErr = array('文件不能大于' . ini_get('upload_max_filesize'), 'UPLOAD_ERR_FORM_SIZE' . ini_get('MAX_FILE_SIZE'), 'UPLOAD_ERR_PARTIAL', 'UPLOAD_ERR_NO_FILE', 'UPLOAD_ERR_NO_TMP_DIR', 'UPLOAD_ERR_CANT_WRITE');
        if (0 !== $_FILES['file']['error']) throw new cuitException($upErr[$_FILES['file']['error'] - 1]);
        
        // 尝试取得openid START
        $this->loader->library('tool', 'pic');
        $client = $this->getClient();
        $tool = new TOOL();
        $sessionData = $tool->code2Session(PARAM['code'], $client);
        $openid = $sessionData['openid'];
        $id = substr($openid, 3, -5);
        // 尝试取得openid END

        $path = "public/files/user/courseTableBack/{$id}";
        if (!file_exists($path)) mkdir($path);
        if (file_exists("{$path}/name") && file_exists($path . "/" . file_get_contents("{$path}/name"))) 
            unlink($path . "/" . file_get_contents("{$path}/name"));

        // 图片交错处理
        $ret = $tool->imageLace($_FILES['file']['tmp_name']);
        $_FILES['file']['name'] = str_replace(".png", '.jpeg', $_FILES['file']['name']);

        $filepath = "{$path}/{$_FILES['file']['name']}";
        move_uploaded_file($_FILES['file']['tmp_name'], "{$filepath}");
        if (!file_exists("{$filepath}")) throw new cuitException("上传失败，文件转存错误");

        file_put_contents("{$path}/name", $_FILES['file']['name']);
        echo json_encode(
            array(
                'status' => 2000,
                'errorCode' => 2000,
                'img' => "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/{$filepath}"
            )
        );
    }

    // @Deprecated from 1.6.11
    function uploadCourseTableBackImgV2Action()
    {
        $upErr = array('文件不能大于' . ini_get('upload_max_filesize'), 'UPLOAD_ERR_FORM_SIZE' . ini_get('MAX_FILE_SIZE'), 'UPLOAD_ERR_PARTIAL', 'UPLOAD_ERR_NO_FILE', 'UPLOAD_ERR_NO_TMP_DIR', 'UPLOAD_ERR_CANT_WRITE');
        if (0 !== $_FILES['file']['error']) throw new cuitException($upErr[$_FILES['file']['error'] - 1]);
        
        // 请求合法性验证 START
        $this->loader->library('tool', 'pic');
        $client = $this->getClient();
        $sign = $this->genQuerySign(substr($_SERVER['REQUEST_URI'] . '/', 4), PARAM['openid'], $client);
        if($sign !== PARAM['sign']) throw new CuitException("非法请求");

        $tool = new TOOL();
        $openid = PARAM['openid'];
        $id = substr($openid, 3, -5);
        // 请求合法性验证 END

        $path = "public/files/user/courseTableBack/{$id}";
        if (!file_exists($path)) mkdir($path);
        if (file_exists("{$path}/name") && file_exists($path . "/" . file_get_contents("{$path}/name"))) 
            unlink($path . "/" . file_get_contents("{$path}/name"));

        // 图片交错处理
        $ret = $tool->imageLace($_FILES['file']['tmp_name']);
        $_FILES['file']['name'] = str_replace(".png", '.jpeg', $_FILES['file']['name']);

        $filepath = "{$path}/{$_FILES['file']['name']}";
        move_uploaded_file($_FILES['file']['tmp_name'], "{$filepath}");
        if (!file_exists("{$filepath}")) throw new cuitException("上传失败，文件转存错误");

        file_put_contents("{$path}/name", $_FILES['file']['name']);
        echo json_encode(
            array(
                'status' => 2000,
                'errorCode' => 2000,
                'img' => "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/{$filepath}"
            )
        );
    }
}