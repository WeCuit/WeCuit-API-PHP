<?php
class CardController extends BaseController
{
    private $cm;

    function __construct()
    {
        parent::__construct();
    }
    /**
     * 登录
     */
    public function loginAction(){
        if (!isset(PARAM['cookie'])) throw new cuitException("参数缺失");
        if (false === strpos($_SERVER['HTTP_REFERER'], 'servicewechat.com') && false === strpos($_SERVER['HTTP_REFERER'], 'appservice.qq.com')) throw new cuitException('error');
        $this->loader->library('easyHttp','card');
        CARD::login();
    }
    public function getDealRecAction(){
        if (!isset(PARAM['AccNum'])) throw new cuitException("参数缺失");
        $this->loader->library('easyHttp','card');
        CARD::getDealRec();
    }
    public function getAccWalletAction(){
        if (!isset(PARAM['AccNum'])) throw new cuitException("参数缺失");
        $this->loader->library('easyHttp','card');
        CARD::getAccWallet();
    }
    public function getQRCodeAction(){
        if (!isset(PARAM['AccNum']) || !isset(PARAM['sign'])) throw new cuitException("参数缺失");
        if(PARAM['sign'] !== $this->genSign(PARAM['AccNum'])) throw new cuitException("请求非法");
        $this->loader->library('easyHttp','card');
        CARD::getQRCode();
    }
    private function genSign($str){
        return md5(md5($str) . "|{$str}|" . md5($str));
    }
    public function getQRCodeInfoAction(){
        if (!isset(PARAM['QRCode'])) throw new cuitException("参数缺失");
        $this->loader->library('easyHttp','card');
        CARD::getQRCodeInfo();
    }
}