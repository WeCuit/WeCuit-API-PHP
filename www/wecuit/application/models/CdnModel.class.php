<?php
class CdnModel extends Model
{
    function getToken()
    {
        $token = md5(uniqid(microtime(true),true));
        $etime = time() + 60 * 10;
        $sql = "INSERT INTO `cuit_cdntoken` (`token`, `etime`) VALUES ('{$token}', '{$etime}')";

        $q = $this->db->query($sql);
        $this->db->close();
        if($q){
            echo json_encode(
                array(
                    'status' => 2000,
                    'errorCode' => 2000,
                    'token' => $token,
                    'etime' => $etime
                )
            );
        }else throw new cuitException("记录失败");
    }

    function validate()
    {
        if(!isset($_GET['token']) || !isset($_GET['sign'])){
            header('HTTP/1.1 401 Unauthorized');
            throw new cuitException("参数缺失");
        }
        
        // RSA验证具有更高安全性，但是无法缓存流量消耗大
        // $util = new UTILS();
        // // CUIT::doLogInfo('log', $_GET['sign']);
        // $decrypt = $util->RSAPrivateDecrypt($_GET['sign']);
        // // CUIT::doLogInfo('log', $decrypt);
        // $arr = explode('|', $decrypt);
        // // 小程序端生成签名的时间与当前的时间差
        // if($arr[0] !== $_GET['token'] || time() - $arr[1] > 60 * 5){
        //     header('HTTP/1.1 401 Unauthorized');
        //     throw new cuitException("认证失败");
        // }
        $this->db->escape($_GET['token']);
        $sql = "SELECT * FROM `cuit_cdntoken` WHERE `token`='{$_GET['token']}'";
        
        $row = $this->db->getRow($sql);
        $this->db->close();
        
        // CUIT::doLogInfo('log', print_r($row, true));
        if(!isset($row['etime'])){
            header('HTTP/1.1 401 Unauthorized');
            throw new cuitException("认证失败");
        }
        $str = $_GET['token'] . $GLOBALS['config']['CDN_SALT'] . $row['etime'];
        if($_GET['sign'] !== md5($str)){
            header('HTTP/1.1 401 Unauthorized');
            throw new cuitException("认证失败");
        }
        echo 'success';
    }

    function delExpireToken()
    {
        $sql = "DELETE FROM `cuit_cdntoken` WHERE etime < unix_timestamp(now())";
        $this->db->query($sql);
        $this->db->close();
    }
}