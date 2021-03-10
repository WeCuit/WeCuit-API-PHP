<?php
chdir(__DIR__ . "/..");
require "./wecuit/libraries/easyHttp.class.php";
header("content-type:application/json");
// print_r($_SERVER);
$host = 'localhost';
//$_SERVER['SERVER_ADDR']?$_SERVER['SERVER_ADDR']:gethostbyname($_SERVER['HOSTNAME']);

$http = new EasyHttp();
try {

    // 自动打卡
    if((int)date('H') >= 6){
        echo "***********************自动打卡***********************\r\n";
        $res = $http->request("http://{$host}/api/task/doAutoCheckIn");
        echo $res['body'] . "\r\n\r\n";
    }
    
    // AccessToken 刷新
    echo "***********************refreshToken***********************\r\n";
    $res = $http->request("http://{$host}/api/task/refreshMiniToken");
    echo $res['body'] . "\r\n\r\n";

    echo "***********************delExpireCDNToken***********************\r\n";
    $res = $http->request("http://{$host}/api/cdn/delExpireToken");
    echo $res['body'] . "\r\n\r\n";
    
    // echo "***********************成绩监控***********************\r\n";
    // $res = $http->request("http://{$host}/api/task/doGradeNotice", array(
    //     'timeout' => 50            //	超时的秒数
    // ));
    // if(is_object($res))
    // print_r($res);
    // else
    // echo $res['body'] . "\r\n\r\n";

    
    echo "***********************update news***********************\r\n";
    // 10分钟一次
    if(date('i') % 10 == 0)
    {
        $res = $http->request("http://{$host}/api/Task/pullNews", array('timeout' => 600));
        if(is_object($res))
        print_r($res);
        else
        echo $res['body'] . "\r\n\r\n";
    }

    // @Deprecated
    $res = $http->request("http://{$host}/api/www/updateNewsList");
    if(is_object($res))
    print_r($res);
    else
    echo $res['body'] . "\r\n\r\n";
} catch (Exception $e) {
    print_r($e);
}
file_put_contents('runtime/logs/task_run.log', time());