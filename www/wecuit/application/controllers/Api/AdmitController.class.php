<?php
class AdmitController extends BaseController
{
    private $cm;

    function __construct()
    {
        parent::__construct();
    }

    public function queryAction()
    {
        $ksh = PARAM['ksh'];
        $sfz = PARAM['sfz'];

        $this->loader->library('easyHttp');
        $http = new EasyHttp();
        $list = $http->request('http://zjc.cuit.edu.cn/Zs/LqXsCx.asp', array(
            'method' => 'POST',        //	GET/POST
            'timeout' => 5,            //	超时的秒数
            'redirection' => 0,        //	最大重定向次数
            'httpversion' => '1.0',    //	1.0/1.1
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
            'blocking' => true,        //	是否阻塞
            'headers' => array(
            ),    //	header信息
            'cookies' => array(),    //	关联数组形式的cookie信息
            'body' => "SfzhR=&Ksbh={$ksh}&Sfzh={$sfz}&Button=%B2%E9%26%23160%3B%D1%AF",
            'compress' => false,    //	是否压缩
            'decompress' => true,    //	是否自动解压缩结果
            'sslverify' => true,
            'stream' => false,
            'filename' => null        //	如果stream = true，则必须设定一个临时文件名
        ));
        if(is_object($list))throw new Exception("服务器网络错误", 10511);
        
        $body = $list['body'];

        $dom = new DOMDocument();
        @$dom->loadHTML($body);
        $dom->normalize();

        $xpath = new DOMXPath($dom);

        // 查询请求结果
        $result = $xpath->query('//*[@id="form1"]/table/tbody/tr[3]/td/div/table/tbody/tr/td');
        $result = $dom->saveHTML($result->item(0));

        // 数据更新时间
        $update = $xpath->query('//*[@id="form1"]/table/tbody/tr[5]/td/table/tbody/tr[2]/td');
        $update = $update->item(0)->childNodes->item(1)->textContent;

        // 数据
        $items = $xpath->query('//*[@id="form1"]/table/tbody/tr[5]/td/table/tbody/tr[2]/td/table/tr/td[not(@*) and string-length(text())>1]');
        
        $list = [];
        for ($i=0; $i < $items->length; $i++) {
            $site = $items->item($i++);
            $lowest = $items->item($i);
            $list[] = array(
                'site' => $site->textContent,
                'lowest' => $lowest->textContent
            );
        }
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'result' => $result,
            'update' => $update,
            'list' => $list
        ));
    }
}