<?php
// SSL证书
if(false !== strpos($_SERVER['REQUEST_URI'], ".well-known"))
{
    echo file_get_contents("http://www.{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    exit;
}
// $referer = $_SERVER['HTTP_REFERER'];
// $ver = explode('/', $referer)[4];
// if($ver == "1.6.4"){
//     echo json_encode(array(
//         'errorCode' => 1000,
//         'status' => 1000,
//         'errMsg' => '请升级'
//     ));
//     exit;
// }
// 维护检测
if(file_exists('.maintenance')){
    $maintenance = json_decode(file_get_contents('.maintenance'), true);
    $now = time();
    if($now > $maintenance['start'] && $now < $maintenance['end'])
    {
        echo json_encode(array(
            'errorCode' => 10503,
            'errMsg' => '系统维护',
            'maintenance' => $maintenance
        ));
        exit;
    }
}
define('APP_DEBUG', true);
require "./wecuit/core/WeCuit.class.php";
WeCuit::run();