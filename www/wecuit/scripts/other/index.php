<?php
/**
 * 老薛主机自动证书脚本
 * 
 * 
 */
header("content-type: application/json");
if(!isset($_GET['token']) || $_GET['token'] !== '\^*463%%gdvdkc.*5')
{
    echo "error-->{$_GET['token']}";
    exit;
}
$ssl_path = __DIR__ . "/../../../../ssl";
$cert_path = $ssl_path . "/certs";
$key_path = $ssl_path . "/keys";
$ca = "-----BEGIN CERTIFICATE-----
MIIF8TCCA9mgAwIBAgIRAPAdS+57fKN7PAVmrAWXJFgwDQYJKoZIhvcNAQEMBQAwgYUxCzAJBgNV
BAYTAkdCMRswGQYDVQQIExJHcmVhdGVyIE1hbmNoZXN0ZXIxEDAOBgNVBAcTB1NhbGZvcmQxGjAY
BgNVBAoTEUNPTU9ETyBDQSBMaW1pdGVkMSswKQYDVQQDEyJDT01PRE8gUlNBIENlcnRpZmljYXRp
b24gQXV0aG9yaXR5MB4XDTE1MDUxODAwMDAwMFoXDTI1MDUxNzIzNTk1OVowcjELMAkGA1UEBhMC
VVMxCzAJBgNVBAgTAlRYMRAwDgYDVQQHEwdIb3VzdG9uMRUwEwYDVQQKEwxjUGFuZWwsIEluYy4x
LTArBgNVBAMTJGNQYW5lbCwgSW5jLiBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eTCCASIwDQYJKoZI
hvcNAQEBBQADggEPADCCAQoCggEBAIteAVa57GsR70jpQ56byLpTkaW9qyr6Xjo14Q1cNepSqJk0
KA9+WStIa+e010t9L4PP/osmw1l5H2Chaaday583Ie8YvZv9Qet1fLeW2V6GyyoS4qf3A+TO5gX3
QZsevNL20WZpUQzete08CyfPiI4gPeNOlY8VNMYmy/c/ZOn1MCV9zak5mz/qemkri8R9C/hWk7Zr
lsrsz9J7vUO+0/WJ2k10SSHEvfUwvLxJqWUVs9b/vx2QlJwIJbatz/zH2ftV1RnQSr9iRuUk7Y++
ZJgMalGeeoBzIKm02b9Dap4QrSugzWStQDnS4rjbwvI6o+K3FpcfHvbP3zweWOkAB2sCAwEAAaOC
AWwwggFoMB8GA1UdIwQYMBaAFLuvfgI9+qbxPISOre44mOzZMjLUMB0GA1UdDgQWBBR+A1plQWun
fgrhuJ0I6h2OHWrHZTAOBgNVHQ8BAf8EBAMCAYYwEgYDVR0TAQH/BAgwBgEB/wIBADAdBgNVHSUE
FjAUBggrBgEFBQcDAQYIKwYBBQUHAwIwIgYDVR0gBBswGTANBgsrBgEEAbIxAQICNDAIBgZngQwB
AgEwTAYDVR0fBEUwQzBBoD+gPYY7aHR0cDovL2NybC5jb21vZG9jYS5jb20vQ09NT0RPUlNBQ2Vy
dGlmaWNhdGlvbkF1dGhvcml0eS5jcmwwcQYIKwYBBQUHAQEEZTBjMDsGCCsGAQUFBzAChi9odHRw
Oi8vY3J0LmNvbW9kb2NhLmNvbS9DT01PRE9SU0FBZGRUcnVzdENBLmNydDAkBggrBgEFBQcwAYYY
aHR0cDovL29jc3AuY29tb2RvY2EuY29tMA0GCSqGSIb3DQEBDAUAA4ICAQAQn6BgCIF0oaCEeGBM
OTnaZHfvGQpyOSOUO5F9fzSLl1hOWQotaMMQQrCgeoGMe6sxMiA55CJz4N7JF12DxXUt4RFHWQGe
XcD03RJq0G0wIOizyk/fmuCnF58aL4d+61DhU/P4R9mMYPLJZWWc8NoB5rLy2AeYh983iZhVEkLJ
5C3eLb6qZJRO2S7mwtXywObp6hk+NwuJX8k6+E9HQD6vGn+i9oUBiBc2tSPquf66a0gLAiA5rsNh
65WloXPHHF9UM3NXSzaLm1so4z6xC3hcaxSnEMzl2j+66dayLR1wVLpeq31PKYkQ4DqQBMXuuY5D
ouNjWH9Ji3E+V2IjQNFdlmQiYVaflmdHh7zlACCkaOLBoIF7aHMIxG1OcHno3VXXCVy5nQqVpgzZ
2+KKVeu54eealRRMWAZBwRCqqrE64qVKSuDZyR/CoJe7Bu8ZANsCvpbx+1SPk5r6MCI2qXcmH5Qo
k+kTPUXROjVIHpgNgnDAC1ooh6F4UT+1p1ymkSIAQky5gBWAKrEtiU/3uh4YxIxZHnNJo6h7vB/3
Vk1Qn2cWp8cXSOdtVFd2bpdYW3hkpO1itAA7Bn55uFhfboTWQ7xP2zmqKPDBiQnF++MYRLflsotd
lfkjWgty92k61leL4en0YL7EUSsRrP5Is3JzyhNQcw0EdsoB4ULC1yHP+Q==
-----END CERTIFICATE-----
-----BEGIN CERTIFICATE-----
MIIFfjCCBGagAwIBAgIQZ970PvF72uJP9ZQGBtLAhDANBgkqhkiG9w0BAQwFADB7MQswCQYDVQQG
EwJHQjEbMBkGA1UECAwSR3JlYXRlciBNYW5jaGVzdGVyMRAwDgYDVQQHDAdTYWxmb3JkMRowGAYD
VQQKDBFDb21vZG8gQ0EgTGltaXRlZDEhMB8GA1UEAwwYQUFBIENlcnRpZmljYXRlIFNlcnZpY2Vz
MB4XDTA0MDEwMTAwMDAwMFoXDTI4MTIzMTIzNTk1OVowgYUxCzAJBgNVBAYTAkdCMRswGQYDVQQI
ExJHcmVhdGVyIE1hbmNoZXN0ZXIxEDAOBgNVBAcTB1NhbGZvcmQxGjAYBgNVBAoTEUNPTU9ETyBD
QSBMaW1pdGVkMSswKQYDVQQDEyJDT01PRE8gUlNBIENlcnRpZmljYXRpb24gQXV0aG9yaXR5MIIC
IjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAkehUktIKVrGsDSTdxc9EZ3SZKzejfSNwAHG8
U9/E+ioSj0t/EFa9n3Byt2F/yUsPF6c947AEYe7/EZfH9IY+Cvo+XPmT5jR62RRr55yzhaCCenav
cZDX7P0N+pxs+t+wgvQUfvm+xKYvT3+Zf7X8Z0NyvQwA1onrayzT7Y+YHBSrfuXjbvzYqOSSJNpD
a2K4Vf3qwbxstovzDo2a5JtsaZn4eEgwRdWt4Q08RWD8MpZRJ7xnw8outmvqRsfHIKCxH2XeSAi6
pE6p8oNGN4Tr6MyBSENnTnIqm1y9TBsoilwie7SrmNnu4FGDwwlGTm0+mfqVF9p8M1dBPI1R7Qu2
XK8sYxrfV8g/vOldxJuvRZnio1oktLqpVj3Pb6r/SVi+8Kj/9Lit6Tf7urj0Czr56ENCHonYhMsT
8dm74YlguIwoVqwUHZwK53Hrzw7dPamWoUi9PPevtQ0iTMARgexWO/bTouJbt7IEIlKVgJNp6I5M
ZfGRAy1wdALqi2cVKWlSArvX31BqVUa/oKMoYX9w0MOiqiwhqkfOKJwGRXa/ghgntNWutMtQ5mv0
TIZxMOmm3xaG4Nj/QN370EKIf6MzOi5cHkERgWPOGHFrK+ymircxXDpqR+DDeVnWIBqv8mqYqnK8
V0rSS527EPywTEHl7R09XiidnMy/s1Hap0flhFMCAwEAAaOB8jCB7zAfBgNVHSMEGDAWgBSgEQoj
PpbxB+zirynvgqV/0DCktDAdBgNVHQ4EFgQUu69+Aj36pvE8hI6t7jiY7NkyMtQwDgYDVR0PAQH/
BAQDAgGGMA8GA1UdEwEB/wQFMAMBAf8wEQYDVR0gBAowCDAGBgRVHSAAMEMGA1UdHwQ8MDowOKA2
oDSGMmh0dHA6Ly9jcmwuY29tb2RvY2EuY29tL0FBQUNlcnRpZmljYXRlU2VydmljZXMuY3JsMDQG
CCsGAQUFBwEBBCgwJjAkBggrBgEFBQcwAYYYaHR0cDovL29jc3AuY29tb2RvY2EuY29tMA0GCSqG
SIb3DQEBDAUAA4IBAQB/8lY1sG2VSk50rzribwGLh9Myl+34QNJ3UxHXxxYuxp3mSFa+gKn4vHjS
yGMXroztFjH6HxjJDsfuSHmfx8m5vMyIFeNoYdGfHUthgddWBGPCCGkm8PDlL9/ACiupBfQCWmqJ
17SEQpXj6/d2IF412cDNJQgTTHE4joewM4SRmR6R8ayeP6cdYIEsNkFUoOJGBgusG8eZNoxeoQuk
ntlCRiTFxVuBrq2goNyfNriNwh0V+oitgRA5H0TwK5/dEFQMBzSxNtEU/QcCPf9yVasn1iyBQXEp
jUH0UFcafmVgr8vFKHaYrrOoU3aL5iFSa+oh0IQOSU6IU9qSLucdCGbX
-----END CERTIFICATE-----";

// 证书
// $certs = scandir($ssl_path . "/certs");
// 密钥
// $keys = scandir($ssl_path . "/keys");

$host = substr($_SERVER['HTTP_HOST'], 4);
$prefix = str_replace('.', '_', $host);
$localurl = $ssl_path . "/certs/{$prefix}*.crt";
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

$arr = explode('_', substr($cert_name, strlen($prefix) + 1));
$prefix = "{$arr[0]}_{$arr[1]}_";
$keys = glob("{$key_path}/{$prefix}*.key", GLOB_BRACE);
$key = $keys[0];

echo json_encode(array(
    'cert' => file_get_contents($cert),
    'key' => file_get_contents($key),
    'ca' => $ca,
    'expire' => $arr[2]
));