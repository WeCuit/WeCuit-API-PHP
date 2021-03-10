<?php

class SubController extends BaseController
{

    // 获取订阅模板列表
    public function getTemplateIdListAction()
    {
        $client = $this->getClient();
        $sm = new SubModel('subTemplate');
        $sm->getTplList($client);
    }

    // @Deprecated
    public function getStatusAction()
    {
        if (!isset(PARAM['code'])) throw new cuitException("参数缺失");

        $this->loader->library('tool');
        $client = $this->getClient();
        $tool = new TOOL();
        $sessionData = $tool->code2Session(PARAM['code'], $client);
        $openid = $sessionData['openid'];

        $sm = new SubModel();
        $sm->getStatus($openid);
    }

    public function getStatusV2Action()
    {
        if (!isset(PARAM['openid'])) throw new cuitException("参数缺失");

        $sign = $this->genQuerySign(substr($_SERVER['REQUEST_URI'] . '/', 4), PARAM['openid']);
        if(PARAM['sign'] !== $sign) throw new cuitException("非法请求");
        $openid = PARAM['openid'];

        $sm = new SubModel();
        $sm->getStatus($openid);
    }

    // @Deprecated
    public function changeStatusAction()
    {
        if (!(isset(PARAM['code']) && isset(PARAM['status']) && isset(PARAM['tplId']))) throw new cuitException("参数缺失");

        $this->loader->library('tool', 'easyHttp', 'jszx');
        $client = $this->getClient();

        $tool = new TOOL();
        $sessionData = $tool->code2Session(PARAM['code'], $this->getClient());
        $openid = $sessionData['openid'];

        // 检账号密码
        $rsa = $this->initRSA();
        
        if(strlen(PARAM['userId']) > 15)
        {
            $sId = $rsa->RSAPrivateDecrypt(PARAM['userId']);
        }else{
            $sId = PARAM['userId'];
        }
        $sPass = $rsa->RSAPrivateDecrypt(PARAM['userPass']);
        if (empty($sId) || empty($sPass)) throw new cuitException("账号密码缺失");

        $j = new JSZX();
        $info = $j->checkAcc($sId, $sPass);
        if (2000 !== $info['status']) throw new cuitException("账号密码有误");

        $status = 0;
        if ('true' == PARAM['status']) $status = 1;

        $md = new SubModel();
        $ret = $md->changeStatus($openid, $client, $status, $sId, PARAM['userPass'], PARAM['tplId']);
        if (false === $ret || 0 === $ret) throw new cuitException("更新失败" . __LINE__);

        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'errMsg' => $status ? '已订阅' : '已取消'
        ));
    }
    public function changeStatusV2Action()
    {
        if (!(isset(PARAM['openid']) && isset(PARAM['status']) && isset(PARAM['tplId']))) throw new cuitException("参数缺失");

        $this->loader->library('easyHttp', 'jszx');

        $client = $this->getClient();
        $sign = $this->genQuerySign(substr($_SERVER['REQUEST_URI'] . '/', 4), PARAM['openid'], PARAM['tplId']);
        if(PARAM['sign'] !== $sign) throw new cuitException("非法请求");

        $openid = PARAM['openid'];

        // 检账号密码
        $rsa = $this->initRSA();
        
        if(strlen(PARAM['userId']) > 15)
        {
            $sId = $rsa->RSAPrivateDecrypt(PARAM['userId']);
        }else{
            $sId = PARAM['userId'];
        }
        $sPass = $rsa->RSAPrivateDecrypt(PARAM['userPass']);
        if (empty($sId) || empty($sPass)) throw new cuitException("账号密码缺失");

        // $j = new JSZX();
        // $info = $j->checkAcc($sId, $sPass);
        // if (2000 !== $info['status']) throw new cuitException("账号密码有误");

        $status = 0;
        if ('true' == PARAM['status']) $status = 1;

        $md = new SubModel();
        $ret = $md->changeStatus($openid, $client, $status, $sId, PARAM['userPass'], PARAM['tplId']);
        if (false === $ret || 0 === $ret) throw new cuitException("更新失败" . __LINE__);

        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'errMsg' => $status ? '已订阅' : '已取消'
        ));
    }

    // @Deprecated
    public function addCntAction()
    {
        if (!(isset(PARAM['code']) && isset(PARAM['tplId']))) throw new cuitException("参数缺失");

        $this->loader->library('tool');
        $client = $this->getClient();
        $tool = new TOOL();
        $sessionData = $tool->code2Session(PARAM['code'], $client);
        $openid = $sessionData['openid'];

        $sm = new SubModel();
        $sm->addCnt($openid, PARAM['tplId']);
    }

    public function addCntV2Action()
    {
        if (!(isset(PARAM['openid']) && isset(PARAM['tplId']) && isset(PARAM['sign']))) throw new cuitException("参数缺失");

        $sign = $this->genQuerySign(substr($_SERVER['REQUEST_URI'] . '/', 4), PARAM['openid'], PARAM['tplId']);
        if(PARAM['sign'] !== $sign) throw new cuitException("非法请求");

        $openid = PARAM['openid'];

        $sm = new SubModel();
        $sm->addCnt($openid, PARAM['tplId']);
    }
    // @Deprecated
    public function deleteAction()
    {
        if (!isset(PARAM['code'])) throw new cuitException("参数缺失");

        $this->loader->library('tool');
        $client = $this->getClient();
        $tool = new TOOL();
        $sessionData = $tool->code2Session(PARAM['code'], $client);
        $openid = $sessionData['openid'];

        $sm = new SubModel();
        $sm->del($openid);
    }
    public function deleteV2Action()
    {
        if (!isset(PARAM['openid']) || !isset(PARAM['sign'])) throw new cuitException("参数缺失");

        $sign = $this->genQuerySign(substr($_SERVER['REQUEST_URI'] . '/', 4), PARAM['openid']);
        if(PARAM['sign'] !== $sign) throw new cuitException("非法请求");
        
        $openid = PARAM['openid'];

        $sm = new SubModel();
        $sm->del($openid);
    }
}
