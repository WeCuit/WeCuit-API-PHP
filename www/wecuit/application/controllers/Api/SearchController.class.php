<?php
// @Deprecated
class SearchController extends BaseController
{
    public function infoAction()
    {
        if(!isset(PARAM['keyword']))throw new cuitException("参数错误");
        $sm = new SearchModel();
        $sm->getInfo(PARAM['keyword']);
    }

    public function getDetailAction()
    {
        if(!isset($_GET['id']))throw new cuitException("参数缺失");
        $sm = new SearchModel();
        $sm->getDetail($_GET['id']);
    }
}