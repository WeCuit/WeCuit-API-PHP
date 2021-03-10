<?php
class CdnController extends BaseController
{
    private $cm;

    function __construct()
    {
        parent::__construct();
        $this->cm = new CdnModel();
    }
    function getCDNTokenAction()
    {
        if(!isset(PARAM['code']))throw new cuitException("参数错误", 1);
        $this->loader->library('tool');
        $client = $this->getClient();
        $tool = new TOOL();
        if(!$tool->checkCode(PARAM['code'], $client))
            if(!$tool->checkCode(PARAM['code'], $client))throw new cuitException("GET OPENID ERROR");
        $this->cm->getToken();
    }
    function validateAction()
    {
        $this->cm->validate();
    }
    function delExpireTokenAction()
    {
        $this->cm->delExpireToken();
    }
}