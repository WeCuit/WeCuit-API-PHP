<?php
require_once 'easy-http/EasyHttp/Curl.php';
require_once 'easy-http/EasyHttp/Cookie.php';
require_once 'easy-http/EasyHttp/Encoding.php';
require_once 'easy-http/EasyHttp/Fsockopen.php';
require_once 'easy-http/EasyHttp/Proxy.php';
require_once 'easy-http/EasyHttp/Streams.php';
require_once 'easy-http/EasyHttp/Error.php';
require_once 'easy-http/EasyHttp.php';

if (isset($GLOBALS['config']['internal']['proxy_port'])) {
    // EasyHttp_Proxy::$host = $GLOBALS['config']['internal']['host'];
    // EasyHttp_Proxy::$port = $GLOBALS['config']['internal']['proxy_port'];
    $proxy = json_decode(file_get_contents("{$GLOBALS['config']['internal']['host']}:{$GLOBALS['config']['internal']['proxy_port']}/get/"), true);
    if (isset($proxy['proxy']) && 1 == $proxy['last_status']) {
        echo "代理设置\r\n";
        echo "{$proxy['proxy']}--------------------\r\n";
        $proxy = explode(":", $proxy['proxy']);
        EasyHttp_Proxy::$host = $proxy[0];
        EasyHttp_Proxy::$port = $proxy[1];
    }
}
