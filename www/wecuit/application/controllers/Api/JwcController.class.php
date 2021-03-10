<?php
// @Deprecated
class JwcController extends BaseController
{
    public function downFileAction()
    {
        $this->loader->library('easyHttp', 'jwc');
        JWC::downFile();
    }

    public function labAllAction(){

        foreach($_GET as $key => $value){
            $_GET[$key] = $this->str2GBK($value);
        }
        $this->loader->library("easyHttp");
        $this->loader->helper("jwc");

        $http = new EasyHttp();
        $lab = $http->request("http://jxgl.cuit.edu.cn/Jxgl/Js/sysYxgl/Cx/sysHz.asp", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 1,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => null,    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $_GET,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($lab)) throw new cuitException("服务器网络错误", 10511);
        $html = $lab['body'];
        // $html = file_get_contents("temp.html");
        $ret = LAB_ListHtml2json($html);
        echo json_encode(array(
            'errorCode' => 2000,
            'data' => $ret
        ));
    }

    
    public function labDetailAction(){

        $this->loader->library("easyHttp");
        $this->loader->helper("jwc");
        foreach($_GET as $key => $value){
            $_GET[$key] = $this->str2GBK($value);
        }
        /*
            cxZtO: 8
            cxZcO: 
            Kkxq: 20202                  ----学期
            Yx: 0                        ----院系
            Rw: 1                        ----承担任务
            Sys:                         ----实验室名称（可输入名称的一部分）
            Fj: HSZXB215                 ----房间（可只输入房间编号的左边部分）
            Jxb:                         ----教学班（可只输入班简名的左边部分）
            Zjjs:                        ----教师（可只输入姓名的左边部分）
            Jxkc:                        ----课程（可只输入课程名称的左边部分）
            cxZt: 8                      ----时间
            cxZc:                        ----周次
            Lb: 1                        ----类别
        */
        $http = new EasyHttp();
        $lab = $http->request("http://jxgl.cuit.edu.cn/Jxgl/Js/sysYxgl/Cx/sysKb.asp", array(
            'method' => 'GET',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 1,        //	最大重定向次数
            'httpversion' => '1.1',    //	1.0/1.1
            'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
            'blocking' => true,        //	是否阻塞
            'headers' => null,    //	header信息
            'cookies' => null,    //	关联数组形式的cookie信息
            // 'cookies' => $cookies,
            'body' => $_GET,
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if (is_object($lab)) throw new cuitException("服务器网络错误", 10511);
        $html = $lab['body'];
        $html = str_replace($this->str2GBK("请输入实验室名称"), '', $html);
        // file_put_contents("temp.html", $html);
        // $html = file_get_contents("temp.html");
        $ret = LAB_DetailHtml2json($html);
        echo json_encode(array(
            'errorCode' => 2000,
            'data' => $ret
        ));
    }

}
