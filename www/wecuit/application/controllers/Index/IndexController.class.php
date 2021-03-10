<?php
class IndexController extends BaseController
{
    function indexAction()
    {
        include CURR_VIEW_PATH . 'index.html';
    }
}