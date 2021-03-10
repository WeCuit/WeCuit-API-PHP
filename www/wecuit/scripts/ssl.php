<?php
header("content-type: application/json");
if(!isset($_GET['token']) || $_GET['token'] !== '****')
{
    exit;
}
$ssl_path = __DIR__ . "/../../../../ssl";
$cert_path = $ssl_path . "/certs";
$key_path = $ssl_path . "/keys";
$ca = "-----BEGIN CERTIFICATE-----
***
-----END CERTIFICATE-----";

// 证书
// $certs = scandir($ssl_path . "/certs");
// 密钥
// $keys = scandir($ssl_path . "/keys");

$localurl = $ssl_path . "/certs/cuit*.crt";
$certs = glob($localurl, GLOB_BRACE);

// 获取最迟过期的下标
$cert_index = 0;
$cert_expire = 0;
foreach ($certs as $key => $value) {
    $cert_name = substr($value, strlen($cert_path) + 1);
    $arr = explode('_', $cert_name);
    if ($arr[6] > $cert_expire)
        $cert_index = $key;
}

// 证书文件路径
$cert = $certs[$cert_index];
// 证书文件名
$cert_name = substr($cert, strlen($cert_path) + 1);

$arr = explode('_', $cert_name);
$prefix = "{$arr[4]}_{$arr[5]}_";
$keys = glob("{$key_path}/{$prefix}*.key", GLOB_BRACE);
$key = $keys[0];

echo json_encode(array(
    'cert' => file_get_contents($cert),
    'key' => file_get_contents($key),
    'ca' => $ca,
    'expire' => $arr[6]
));