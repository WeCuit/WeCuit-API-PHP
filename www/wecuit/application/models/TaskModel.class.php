<?php

// application/models/SubModel.class.php
class TaskModel extends Model
{
    private $queue;

    // 打卡任务
    public function getCheckInStatus($openid, $client)
    {
        $this->db->escape($openid);
        $this->db->escape($client);
        $aci = false;
        $isSub = true;
        if (isset($openid)) {
            // 查询订阅状态
            $sql = "SELECT status FROM cuit_sub,cuit_subTemplate,cuit_users 
        WHERE cuit_sub.userId=cuit_users.userId AND cuit_sub.tplId=cuit_subTemplate.tplId 
        AND cuit_subTemplate.type='checkin' AND cuit_users.client='{$client}' AND cuit_users.openid='{$openid}'";
            $s = $this->db->getRow($sql);
            // print_r($s);
            if (!isset($s['status']) || 0 == $s['status']) {
                // 没有订阅,或关闭提醒
                $isSub = false;
            }
            if (isset($s['status'])) {
                // 有订阅，不管开关
                $sql = "SELECT cTime FROM `cuit_checkin` WHERE `cuit_checkin`.`subId` = 
            (SELECT subId FROM cuit_sub 
                    WHERE userId=(SELECT userId FROM cuit_users WHERE openid='{$openid}')
                    AND tplId=(SELECT tplId FROM cuit_subTemplate WHERE client='{$client}' AND type='checkin'))";
                $c = $this->db->getRow($sql);
                if (isset($c['cTime'])) {
                    $aci = array(
                        'time' => $c['cTime'],
                    );
                }
            }
            $this->db->close();
        }
        return [$aci, $isSub];
    }

    /**
     * 添加自动打卡任务信息
     * 
     * @param String $openid openid of wx or qq
     * @param String $client wx|qq
     * @param Array $data checkIn info
     */
    public function addCheckIn($openid, $client, $data)
    {
        $this->db->escape($openid);
        $this->db->escape($client);
        $this->db->escape($data);
        $sql = "INSERT INTO cuit_checkin (subId, userId, body, cTime,rTime) VALUES((SELECT subId FROM cuit_sub 
        WHERE userId=(SELECT userId FROM cuit_users WHERE openid='{$openid}')
        AND tplId=(SELECT tplId FROM cuit_subTemplate WHERE client='{$client}' AND type='checkin')),(SELECT userId FROM cuit_users WHERE openid='{$openid}'), '{$data['body']}','{$data['cTime']}','" . strtotime(date('Y-m-d', time())) . "')";
        $ret = $this->db->insert($sql);
        $errno = $this->db->errno();
        $error = $this->db->error();
        if (false !== $ret) {
            return array(
                'status' => 2000,
                'errorCode' => 2000,
                'errMsg' => '添加成功'
            );
        } else {
            $msg = '';
            switch ($errno) {
                case 1062:
                    unset($data['rTime']);
                    unset($data['openid']);
                    echo json_encode($this->updateCheckIn($openid, $client, $data));
                    exit;
                    break;
                default:
                    $msg = 'unknow error' . $error;
                    break;
            }
            throw new cuitException($msg);
        }
    }

    /**
     * 更新自动打卡任务信息
     * 
     * @param String $openid openid of wx or qq
     * @param String $client wx|qq
     * @param Array checkIn info
     */
    public function updateCheckIn($openid, $client, $data)
    {
        $sql = "UPDATE cuit_checkin 
        inner join(
            SELECT subId FROM cuit_sub 
                WHERE userId=(SELECT userId FROM cuit_users WHERE openid='{$openid}')
                AND tplId=(SELECT tplId FROM cuit_subTemplate WHERE client='{$client}' AND type='checkin')
            ) c 
        on cuit_checkin.subId=c.subId 
        set ";
        foreach ($data as $k => $v) {
            $sql .= "`{$k}`='{$v}',";
        }
        $sql = rtrim($sql, ',');
        // $sql .= "body = '{$data['body']}', cTime = '{$data['time']}'";

        $ret = $this->db->update($sql);
        $errMsg = $this->db->error();
        if ($ret) {
            $ret = array(
                'status' => 2000,
                'errorCode' => 2000,
                'errMsg' => '已更新'
            );
        } else if (false === $ret) {
            $ret = array(
                'status' => 2500,
                'errorCode' => 20500,
                'errMsg' => '数据库查询失败'
            );
        }else{
            $ret = array(
                'status' => 2001,
                'errorCode' => 2001,
                'errMsg' => '无变动'
            );
        }
        return $ret;
    }

    /**
     * 删除自动打卡任务
     * 
     * @param String $openid openid of wx or qq
     * @param String $client wx|qq
     */
    public function delCheckIn($openid, $client)
    {
        $sql = "DELETE FROM `cuit_checkin` WHERE `cuit_checkin`.`subId` = 
        (SELECT subId FROM cuit_sub 
        WHERE userId=(SELECT userId FROM cuit_users WHERE openid='{$openid}')
        AND tplId=(SELECT tplId FROM cuit_subTemplate WHERE client='{$client}' AND type='checkin'))";

        $ret = $this->db->delete($sql);

        $errMsg = $this->db->error();
        if (false === $ret) {
            $ret = array(
                'status' => 2004,
                'errorCode' => 2004,
                'errMsg' => $errMsg
            );
        } else if (0 === $ret) {
            $ret = array(
                'status' => 2001,
                'errorCode' => 2001,
                'errMsg' => '记录似乎不存在?'
            );
        } else {
            $ret = array(
                'status' => 2000,
                'errorCode' => 2000,
                'errMsg' => '删除成功'
            );
        }
        return $ret;
    }

    /**
     * 新建一个打卡任务队列
     */
    function newCheckInTaskQueue()
    {
        $split_time = strtotime(date('Y-m-d', time()) . " 05:00:00");
        $sql = "SELECT sId,sPass,body,openid,templateId,subCnt,cuit_subTemplate.client ,rTime,cTime 
        FROM cuit_checkin,cuit_sub,cuit_subTemplate,cuit_users 
        WHERE cuit_sub.userId=cuit_users.userId AND cuit_sub.tplId=cuit_subTemplate.tplId 
        AND cuit_subTemplate.type='checkin' AND cuit_checkin.subId=cuit_sub.subId 
        /*今天未打卡的*/ 
        AND cuit_sub.status=1 AND rTime < {$split_time} 
        /*当前时间前的*/
        AND cTime < date_format(CONVERT_TZ(CURTIME(), @@session.time_zone,'+08:00'), '%H:%i:%s') 
        ORDER BY rTime DESC LIMIT 5";
        $this->queue = $this->db->query($sql);
    }
    function nextTaskEle()
    {
        return $this->db->fetch($this->queue);
    }
    function updateCnt($openid, $cnt)
    {
        $sql = "UPDATE cuit_checkin,cuit_sub,cuit_subTemplate,cuit_users 
                set rTime=" . time() . ", subCnt={$cnt}
                WHERE cuit_sub.userId=cuit_users.userId AND cuit_sub.tplId=cuit_subTemplate.tplId 
                AND cuit_subTemplate.type='checkin' AND cuit_checkin.subId=cuit_sub.subId 
                AND cuit_subTemplate.client=(SELECT client FROM cuit_users WHERE openid='{$openid}') 
                AND cuit_users.openid='{$openid}'";
        // echo $sql. " \r\n";
        return $this->db->update($sql);
    }

    // 成绩任务
    public function gradeStatus($openid)
    {
        $sql = "SELECT * FROM (SELECT COUNT(*) AS sysSub FROM cuit_users,cuit_sub,cuit_subTemplate WHERE cuit_sub.userId=cuit_users.userId AND cuit_subTemplate.tplId=cuit_sub.tplId AND cuit_subTemplate.type='grade' AND cuit_users.openid='{$openid}') a,
		(SELECT COUNT(*) AS gradeSub FROM cuit_grade,cuit_users WHERE cuit_grade.userId=cuit_users.userId AND openid='{$openid}') b";
        $c = $this->db->getRow($sql);
        // print_r($c);
        $c['sysSub'] = boolval($c['sysSub']);
        $c['gradeSub'] = boolval($c['gradeSub']);
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'data' => $c
        ));
        $this->db->close();
    }
    public function addGrade($openid)
    {
        $sql = "INSERT INTO `cuit_grade` (`gid`, `subId`, `userId`, `grade`, `qTime`) VALUES (NULL, (SELECT subId FROM cuit_users,cuit_sub,cuit_subTemplate 
        WHERE cuit_sub.userId=cuit_users.userId AND cuit_subTemplate.tplId=cuit_sub.tplId AND
        cuit_subTemplate.type='grade' AND openid='{$openid}'), (SELECT userId FROM cuit_users WHERE openid='{$openid}'), '{}', CURRENT_TIMESTAMP)";
        $ret = $this->db->insert($sql);
        $this->db->close();
        return $ret;
    }
    public function updateGrade()
    {
    }
    public function delGrade($openid)
    {
        $sql = "DELETE FROM `cuit_grade` 
        WHERE `cuit_grade`.`gid` in
        (SELECT gid FROM (SELECT gid FROM cuit_grade,cuit_users,cuit_sub 
        WHERE cuit_grade.subId=cuit_sub.subId AND cuit_sub.userId=cuit_users.userId AND openid='{$openid}') a)";
        $ret = $this->db->delete($sql);
        $this->db->close();
        return $ret;
    }
    function getOldGrade($subId)
    {
        $sql = "SELECT grade FROM cuit_grade WHERE subId={$subId}";
        return $this->db->getRow($sql);
    }
    function upGrade_rTime($gid)
    {
        $sql = "UPDATE `cuit_grade` SET `qTime` = '" . date("Y-m-d H:i:s") . "' WHERE `cuit_grade`.`gid` = {$gid};";
        $this->db->query($sql);
    }
    function upGrade($subId, $newGrade)
    {
        $sql = "UPDATE `cuit_grade` SET `grade` = '{$newGrade}' WHERE `cuit_grade`.`subId` = {$subId};";
        return $this->db->update($sql);
    }
    function upGrade_cnt($subId, $cnt)
    {
        $sql = "UPDATE `cuit_sub` SET `subCnt` = '{$cnt}' WHERE `cuit_sub`.`subId` = {$subId};";
        return $this->db->update($sql);
    }
    function insertGrade($subId, $newGrade)
    {
        $sql = "INSERT INTO `cuit_grade` (`subId`, `grade`, `cookie`) VALUES ('{$subId}', '{$newGrade}', '');";
        return $this->db->insert($sql);
    }
    function newGradeTaskQueue()
    {
        $sql = "SELECT cuit_users.client,sId,sPass,openid,cuit_sub.subId,cuit_users.userId,templateId,subCnt,gid 
        FROM cuit_users,cuit_sub,cuit_subTemplate,cuit_grade 
        WHERE cuit_grade.subId=cuit_sub.subId AND cuit_sub.userId=cuit_users.userId 
        AND cuit_sub.tplId=cuit_subTemplate.tplId AND cuit_subTemplate.type='grade' 
        AND cuit_sub.status=1 AND subCnt>0  ORDER BY `qTime` ASC LIMIT 20";
        $this->queue = $this->db->query($sql);
    }
}
