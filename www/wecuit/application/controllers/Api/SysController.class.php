<?php

class SysController extends BaseController{

    public function testAction()
    {
        echo "测试控制器<br />\r\n";
        
    }
    function getHtmlAction(){
        if (file_exists(CURR_VIEW_PATH . PARAM['name'] . '.html'))
            echo file_get_contents(CURR_VIEW_PATH . PARAM['name'] . '.html');
        else echo "Help File Lost....";
    }
    public function getConfigAction()
    {
        $notice = array(
            'errorCode' => 2000,
            'notice' => array(
                array(
                    'src' => "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/public/images/notice/4.jpg",
                    'text' => "成绩提醒设置帮助",
                    'type' => "html",
                    'data' => "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/api/Sys/getHtml/name/gradeHelp",
                ),
                array(
                    'src' => "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/public/images/notice/4.jpg",
                    'text' => "公告日志",
                    'type' => "html",
                    'data' => "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/api/Sys/getHtml/name/changelog",
                ),
                array(
                    'src' => "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/public/images/notice/help.png",
                    'text' => "使用说明",
                    'type' => "html",
                    'data' => "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}/api/Sys/getHtml/name/helper",
                )
            ),
            'github' => '敬请期待',
            'group' => '940309953'
        );
        echo json_encode($notice);
    }

    public function qrCodeAction(){
        $act = isset(PARAM['action'])?PARAM['action']: '';
        $url = isset(PARAM['url'])?PARAM['url']:'';
        $type = isset(PARAM['type'])?PARAM['type']:'unknown';

        $file = CACHE_PATH . "/" . $type;
        if("update" == $act)
        {
            // 更新url
            file_put_contents($file, $url);
            echo "success";
        }else if(file_exists($file)){
            $url = file_get_contents($file);
            if("qq" === $type)
                header("Location: {$url}");
            else if("wx" === $type)
                header("Location: https://qrcode.jp/qr?q={$url}");
                // echo "<img src=\"https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$url}\" />";
            else
                echo "type error";
            
        }else{
            echo "error";
        }
    }

    public function aboutAction()
    {
        include CURR_VIEW_PATH . "about.html";
    }

    public function isAdminAction()
    {
        if (!isset($_GET['code'])) throw new CuitException("参数缺失");
        $this->loader->library('tool');

        $tool = new TOOL();
        $sessionData = $tool->code2Session($_GET['code'], $this->getClient());
        $openid = $sessionData['openid'];

        $admin = false;
        // if ($openid === $GLOBALS['config']['admin']['qq_openid'] || $openid === $GLOBALS['config']['admin']['wx_openid']) $admin = true;

        $info = array(
            'admin' => $admin,
        );
        echo json_encode($info);
    }
    
    /**
     * 获取用户属性
     * 
     * @Deprecated from v1.6.7
     */
    public function getUserInfoAction()
    {
        if (!isset($_GET['code'])) throw new CuitException("参数缺失");
        $this->loader->library('tool');

        $tool = new TOOL();
        $sessionData = $tool->code2Session($_GET['code'], $this->getClient());
        $openid = $sessionData['openid'];

        $admin = false;
        if ($openid === $GLOBALS['config']['admin']['qq_openid'] || $openid === $GLOBALS['config']['admin']['wx_openid']) $admin = true;

        echo json_encode(array(
            'errorCode' => 2000,
            'isAdmin' => $admin,
            'openid' => $openid,
            // 'session' => $sessionData
        ));
    }

    /**
     * 获取用户属性
     */
    public function getUserInfoV2Action()
    {
        if (!isset($_GET['code'])) throw new CuitException("参数缺失");
        $this->loader->library('tool');

        $tool = new TOOL();
        $sessionData = $tool->code2Session($_GET['code'], $this->getClient());
        $openid = $sessionData['openid'];

        $admin = false;
        if ($openid === $GLOBALS['config']['admin']['qq_openid'] || $openid === $GLOBALS['config']['admin']['wx_openid']) $admin = true;

        echo json_encode(array(
            'errorCode' => 2000,
            'info' => array(
                'isAdmin' => $admin,
                'openid' => $openid,
            )
            // 'session' => $sessionData
        ));
    }

    public function getAdminTWFIDAction()
    {
        if (!isset($_GET['code'])) throw new cuitException("参数缺失");

        $this->loader->library('tool');

        $tool = new TOOL();
        $sessionData = $tool->code2Session($_GET['code'], $this->getClient());
        $openid = $sessionData['openid'];

        if ($openid !== $GLOBALS['config']['admin']['qq_openid'] && $openid !== $GLOBALS['config']['admin']['wx_openid'])
            throw new cuitException("滚~");

        $twfid = '';
        if(file_exists(SESSION_PATH . 'twfid'))
            $twfid = file_get_contents(SESSION_PATH . 'twfid');

        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'twfid' => $twfid
        ));
    }
}