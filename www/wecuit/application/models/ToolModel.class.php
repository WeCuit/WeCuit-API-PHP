<?php

class ToolModel extends Model{
    function getUsers(){
        $sql = "SELECT openid,sId FROM cuit_users WHERE `sId` NOT REGEXP '[0-9]{10}'";
        $q = $this->db->query($sql);
        $users = array();
        while($r = $this->db->fetch($q)){
            $users[] = $r;
        }
        return $users;
    }
    function updateSid($openid, $sid){
        $sql = "UPDATE cuit_users SET `sId` = '$sid' WHERE `openid` = '$openid'";
        $q = $this->db->update($sql);
    }
}