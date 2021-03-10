<?php
class CollegeController extends BaseController{
    
    public function __construct()
    {
        parent::__construct();
    }

    public function configAction()
    {
        
    }

    /**
     * 获取辅导员列表
     * 
     */
    public function getCounselorListAction(){
        $college = PARAM['college'];
        $cm = new CollegeModel();
        $list = $cm->getCounselorList($college);
        if(0 === count($list)){
            $list = [
                array(
                    "cid" => "false",
                    "name" => "辅导员信息待补充！"
                )
            ];
        }
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'list' => $list
        ));
    }
    
    /**
     * 获取辅导员信息
     * 
     */
    public function getCounselorInfoAction(){
        $id = PARAM['id'];
        $cm = new CollegeModel();
        $data = $cm->getCounselorInfo($id);

        $this->infoHandle($data);
    }

    public function introduceAction()
    {
        $college = PARAM['college'];
        $cm = new CollegeModel();
        $data = $cm->getIntroduce($college);
        if(null == $data)
        {
            echo "数据待补充！";
        }else 
            $this->infoHandle($data);
    }

    public function contactAction()
    {
        echo "待补充";
    }

    private function infoHandle(array $data){
        if("html" == $data['type']){
            $html = $data['content'];
        }else if("link" == $data['type'] && 0 === strpos($data['content'], "http"))
        {
            $this->loader->library('easyHttp');
            $http = new EasyHttp();
            $ret = $http->request($data['content']);
            if(is_object($ret))
            {
                $html = "服务器网络错误，请重试！";
            }
            $ret['body'] = preg_replace("/<script([\s\S]*?)<\/script>/i", '', $ret['body']);
            $ret['body'] = preg_replace("/<style([\s\S]*?)<\/style>/i", '', $ret['body']);
            preg_match("/<title>(.*?)<\/title>/i", $ret['body'], $title);
            $html = isset($title[0])?$title[0]:'';

            preg_match("/<.*?=\"vsb[\s\S]*?<span>.*?<span>关闭窗口<\/span>.*?<\/span>/i", $ret['body'], $matches);
            if(!isset($matches[0]))preg_match("/<.*?=\"vsb[\s\S]*?<!--#endeditable-->/i", $ret['body'], $matches);
            if(!isset($matches[0])){
                echo "数据处理失败";
                exit;
            }
            $matches[0] = preg_replace("/<span>.*?<span>关闭窗口<\/span>.*?<\/span>/i", '', $matches[0]);
            $html .= $matches[0];
            // $html = '';
        }else{
            $html = "服务器数据异常！";
        }
        echo $html;
    }
}