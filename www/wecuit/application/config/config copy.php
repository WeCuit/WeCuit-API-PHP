<?php
return array(
    'db' => array(
        'host' => 'wecuit_mysql',
        'port' => '3306',
        'user' => '',
        'prefix' => 'cuit_',
        'dbname' => '',
        'password' => '',
        'charset' => 'utf8'
    ),
    'mini' => array(
        'mp_appid' => '',
        'mp_secret' => '',
        'qq_appid' => '',
        'qq_secret' => ''
    ),
    'admin' => array(
        'qq_openid' => '',
        'wx_openid' => '',
        'id' => '',
        'pass' => ''
    ),
    'internal' => array(
        'host' => 'http://wecuit_py',
        'py_port' => 4006,
        // 'proxy_port' => 5010
    ),
    'CDN_SALT' => '',
    'rsa' => array(
        'pi_key' => '-----BEGIN PRIVATE KEY-----*********-----END PRIVATE KEY-----',
        'pu_key' => '-----BEGIN PUBLIC KEY-----**********-----END PUBLIC KEY-----'
    )
);