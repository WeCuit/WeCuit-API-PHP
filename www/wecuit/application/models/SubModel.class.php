<?php

// application/models/SubModel.class.php
class SubModel extends Model
{

    public function getTplList($client)
    {
        $this->db->escape($client);
        $sql = "SELECT * FROM cuit_subTemplate WHERE client='{$client}'";
        $list = $this->db->getAll($sql);
        $this->db->close();
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'data' => $list
        ));
    }

    public function getStatus($openid)
    {
        $this->db->escape($openid);
        $sql = "SELECT * FROM cuit_sub WHERE userId=(SELECT userId FROM cuit_users WHERE openid='{$openid}')";
        $sub = $this->db->getAll($sql);
        $this->db->close();
        // echo "{$sql}\r\n-------------\r\n";
        // print_r($sub);
        echo json_encode(array('status' => 2000, 
        'errorCode' => 2000,'sub' => $sub));
    }

    public function del($openid)
    {
        $this->db->escape($openid);
        $sql = "DELETE FROM cuit_users WHERE openid='{$openid}'";
        
        $r = $this->db->delete($sql);
        if (false === $r) throw new cuitException("数据库查询错误");
        $this->db->close();
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'errMsg' => 'success'
        ));
    }

    public function addCnt($openid, $tplId)
    {
        $this->db->escape($openid);
        $sql = "UPDATE cuit_sub SET subCnt=subCnt+1 
        WHERE userId=(SELECT userId FROM cuit_users WHERE openid='{$openid}') AND tplId='{$tplId}'";
        
        $up = $this->db->update($sql);
        if ($up == 0) {
            print_r($this->db->error());
        }
        $this->db->close();
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'errMsg' => '+1'
        ));
    }

    /**
     * 
     */
    public function changeStatus($openid, $client, $status, $sId, $sPass, $tplId)
    {
        $this->db->escape($openid);
        $this->db->escape($client);
        $this->db->escape($status);
        // 查找用户id
        $sql = "SELECT userId FROM cuit_users WHERE openid='{$openid}'";
        $u = $this->db->getRow($sql);
        if (is_array($u)) {
            $sql = "UPDATE `cuit_users` SET `sId` = '{$sId}', `sPass` = '{$sPass}' WHERE `cuit_users`.`openid` = '{$openid}';";
            if (false === $this->db->update($sql)) {
                $this->db->close();
                throw new cuitException("数据库查询出错");
            }
            $userId = $u['userId'];
        } else if (null === $u) {
            // 未找到用户，插入用户
            $sql = "INSERT INTO cuit_users (openid, client, sId, sPass) VALUES('{$openid}', '{$client}', '{$sId}', '{$sPass}')";
            $userId = $this->db->insert($sql);
            // 插入订阅信息，带上模板id
            $sql = "INSERT INTO cuit_sub (userId, tplId, status) VALUES('{$userId}', '{$tplId}', '{$status}')";
            $subId = $this->db->insert($sql);
            $this->db->close();

            if (false === $subId) {
                throw new cuitException("数据库查询失败");
            }
            echo json_encode(array(
                'status' => 2000,
                'errorCode' => 2000,
                'errMsg' => '已添加'
            ));
            exit;
            return;
        } else if (false === $u) {
            $this->db->close();
            throw new cuitException("数据库查询失败");
        }

        $sql = "UPDATE cuit_sub SET status='{$status}' 
        WHERE userId='{$userId}' AND tplId='{$tplId}'";
        $i = $this->db->update($sql);
        if (0 === $i) {
            // 影响0行，可能不存在
            $sql = "INSERT INTO cuit_sub (userId, tplId, status) VALUES('{$userId}', '{$tplId}', '{$status}')";
            $i = $this->db->insert($sql);
        }
        $this->db->close();
        return $i;
    }

}
