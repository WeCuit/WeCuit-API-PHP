<?php
class JwglController extends BaseController
{

    /**
     * 教务处登录
     **/
    public function loginAction()
    {
        $this->loader->library('easyHttp', 'jwgl');
        if (!isset(PARAM['cookie'])) exit("参数缺失");
        echo json_encode(JWGL::loginFunc(PARAM['cookie']));
    }
    function loginCheckAction()
    {
        $this->loader->library('easyHttp', 'jwgl');
        JWGL::loginCheck();
    }
    public function getGradeTableV2Action()
    {
        $this->loader->library('easyHttp', 'jwgl');
        echo json_encode(JWGL::getGradeTableV2());
    }
    function getCourseOptionAction()
    {
        $this->loader->library('easyHttp', 'jwgl');
        echo json_encode(JWGL::getCourseOption());
    }
    function getCourseTableV2Action()
    {
        $this->loader->library('easyHttp', 'jwgl');
        echo json_encode(JWGL::getCourseTableV2());
    }
    function getExamOptionAction()
    {
        $this->loader->library('easyHttp', 'jwgl');
        echo json_encode(JWGL::getExamOption());
    }
    function getStdExamTableAction()
    {
        $this->loader->library('easyHttp', 'jwgl');
        JWGL::getStdExamTable();
    }
    function getExamTableAction()
    {
        $this->loader->library('easyHttp', 'jwgl');
        JWGL::getExamTable();
    }
}
