<?php
header("content-type:application/json");
$cert = file_get_contents("http://www.{$_SERVER['HTTP_HOST']}/?token=\^*463%%gdvdkc.*5");
echo $cert;
$cert = json_decode($cert, true);
$cert_path = __DIR__ . "/../../ssl/{$_SERVER['HTTP_HOST']}/";
if(!file_exists($cert_path))mkdir($cert_path);
file_put_contents("{$cert_path}certificate.crt", $cert['cert']);
file_put_contents("{$cert_path}private.key", $cert['key']);
file_put_contents("{$cert_path}ca_bundle.crt", $cert['ca']);