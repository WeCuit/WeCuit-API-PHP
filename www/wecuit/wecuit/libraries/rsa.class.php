<?php
class RSA
{
    // 私钥
    private $private_key;
    // 公钥
    private $public_key;

    private $pi_key;
    private $pu_key;

    const  RSA_ENCRYPT_BLOCK_SIZE = 117;
    const  RSA_DECRYPT_BLOCK_SIZE = 128;

    // 判断公钥和私钥是否可用
    public function __construct($pubKey, $priKey)
    {
        $this->private_key = $priKey;
        $this->public_key = $pubKey;
        if ($pubKey) $this->private_key = $pubKey;
        $this->pi_key = openssl_pkey_get_private($this->private_key);
        //这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $this->pu_key = openssl_pkey_get_public($this->public_key);
        //这个函数可用来判断公钥是否是可用的
    }

    // RSA私钥加密
    public function RSAPrivateEncrypt($data)
    {
        $crypto = '';
        foreach (str_split($data, self::RSA_ENCRYPT_BLOCK_SIZE) as $chunk) {
            openssl_private_encrypt($chunk, $encryptData, $this->pi_key);
            $crypto .= $encryptData;
        }
        $encrypted = $this->urlsafe_b64encode($crypto);
        //加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }

    //私钥加密的内容通过公钥可用解密出来
    public function RSAPublicDecrypt($encrypted)
    {
        // $encrypted = $this->urlsafe_b64decode($encrypted);
        $crypto = '';
        foreach (str_split($this->urlsafe_b64decode($encrypted), self::RSA_DECRYPT_BLOCK_SIZE) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, $this->pu_key);
            $crypto .= $decryptData;
        }
        //openssl_public_decrypt($encrypted,$decrypted,$this->pu_key);//私钥加密的内容通过公钥可用解密出来
        return $crypto;
    }

    //公钥加密
    public function RSAPublicEncrypt($data)
    {
        //openssl_public_encrypt($data,$encrypted,$this->pu_key);//公钥加密
        $crypto = '';
        foreach (str_split($data, self::RSA_ENCRYPT_BLOCK_SIZE) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $this->pu_key);
            $crypto .= $encryptData;
        }
        // $encrypted = $this->urlsafe_b64encode($crypto);
        $crypto = base64_encode($crypto);
        return $crypto;
    }

    //私钥解密
    public function RSAPrivateDecrypt($encrypted)
    {
        $crypto = '';
        $data = str_split(base64_decode($encrypted), self::RSA_DECRYPT_BLOCK_SIZE);
        foreach ($data as $chunk) {
            // echo bin2hex($chunk) . "\r\n\r\n";
            openssl_private_decrypt($chunk, $decryptData, $this->pi_key);
            $crypto .= $decryptData;
            unset($decryptData);
        }
        //$encrypted = $this->urlsafe_b64decode($encrypted);
        //openssl_private_decrypt($encrypted,$decrypted,$this->pi_key);
        return $crypto;
    }

    //加密码时把特殊符号替换成URL可以带的内容
    function urlsafe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    //解密码时把转换后的符号替换特殊符号
    function urlsafe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
}
