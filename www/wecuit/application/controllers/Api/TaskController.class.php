<?php 
class TaskController extends BaseController
{
    protected $tm;

    function __construct()
    {
        parent::__construct();
        $this->tm = new TaskModel();
    }

    // @Deprecated
    public function checkInStatusAction()
    {
        if (!isset(PARAM['code'])) throw new CuitException("参数缺失");

        $this->loader->library('tool');
        $client = $this->getClient();
        $tool = new TOOL();
        $sessionData = $tool->code2Session(PARAM['code'], $client);
        $openid = $sessionData['openid'];

        list($aci, $isSub) = $this->tm->getCheckInStatus($openid, $client);
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'autoCheckIn' => $aci,
            'isSubscription' => $isSub,
        ));
    }
    public function checkInStatusV2Action()
    {
        if (!isset(PARAM['openid']) || !isset(PARAM['sign'])) throw new CuitException("参数缺失");

        $sign = $this->genQuerySign(substr($_SERVER['REQUEST_URI'] . '/', 4), PARAM['openid']);
        if($sign !== PARAM['sign']) throw new CuitException("非法请求");

        $client = $this->getClient();
        $openid = PARAM['openid'];

        list($aci, $isSub) = $this->tm->getCheckInStatus($openid, $client);
        echo json_encode(array(
            'status' => 2000,
            'errorCode' => 2000,
            'autoCheckIn' => $aci,
            'isSubscription' => $isSub,
        ));
    }

    function checkInInfoAction()
    {
        if (!(isset(PARAM['data'])))
            throw new cuitException("参数缺失");

        // 解密data
        $rsa = $this->initRSA();
        $data = $rsa->RSAPrivateDecrypt(PARAM['data']);

        $post = json_decode($data, true);
        unset($data);

        // 获取openid
        $this->loader->library('tool');
        $client = $this->getClient();
        $openid = $post['openid'];

        $this->loader->helper('checkIn');
        $body = checkIn_json2body($post['formData'], $post['link']);
        $data = array(
            'body' => $body,
            'openid' => $openid,
            'cTime' => $post['time'],
            'rTime' => strtotime(date('Y-m-d', time()))
        );
        $add = array(
            'body' => $body,
            'cTime' => $post['time']
        );

        switch($post['action'])
        {
            case 0:
                // 添加
                $r = $this->tm->addCheckIn($openid, $client, $add);
                echo json_encode($r);
            break;
            case 1:
                // 删除
                {
                    $r = $this->tm->delCheckIn($openid, $client);
                    echo json_encode($r);
                }
            break;
            case 2:
                // 更新 (TODO:)
                $r = $this->tm->updateCheckIn($openid, $client, $add);
                echo json_encode($r);
            break;
        }
    }
    
    function checkInInfoV2Action()
    {
        if (!(isset(PARAM['data'])))
            throw new cuitException("参数缺失");

        // 解密data
        $rsa = $this->initRSA();
        $data = $rsa->RSAPrivateDecrypt(PARAM['data']);

        if(empty($data))throw new Exception("数据异常", 1);
        
        $post = json_decode($data, true);
        unset($data);

        // 获取openid
        $this->loader->library('tool');
        $client = $this->getClient();
        $openid = $post['openid'];

        $this->loader->helper('checkIn');
        $body = checkIn_form2body($post['form'], $post['link'], false);
        $data = array(
            'body' => $body,
            'openid' => $openid,
            'cTime' => $post['time'],
            'rTime' => strtotime(date('Y-m-d', time()))
        );
        $add = array(
            'body' => $body,
            'cTime' => $post['time']
        );

        switch($post['action'])
        {
            case 0:
                // 添加
                $r = $this->tm->addCheckIn($openid, $client, $add);
                $this->tm->closeDB();
                echo json_encode($r);
            break;
            case 1:
                // 删除
                {
                    $r = $this->tm->delCheckIn($openid, $client);
                    $this->tm->closeDB();
                    echo json_encode($r);
                }
            break;
            case 2:
                // 更新 (TODO:)
                $r = $this->tm->updateCheckIn($openid, $client, $add);
                $this->tm->closeDB();
                echo json_encode($r);
            break;
        }
    }
    
    // @Deprecated
    function gradeStatusAction()
    {
        if (!isset($_GET['code'])) throw new CuitException("参数缺失");

        $this->loader->library('tool');
        $client = $this->getClient();
        $tool = new TOOL();
        $sessionData = $tool->code2Session($_GET['code'], $client);
        $openid = $sessionData['openid'];

        $this->tm->gradeStatus($openid);
    }

    function gradeStatusV2Action()
    {
        if (!isset(PARAM['openid']) || !isset(PARAM['sign'])) throw new CuitException("参数缺失");
        // print_r($_SERVER);
        $sign = $this->genQuerySign(substr($_SERVER['REQUEST_URI'] . '/', 4), PARAM['openid']);
        if($sign !== PARAM['sign']) throw new CuitException("非法请求");
        $openid = PARAM['openid'];
        
        $this->tm->gradeStatus($openid);
    }

    // @Deprecated
    function gradeInfoAction()
    {
        if (!isset(PARAM['code'])) throw new cuitException('参数缺失');

        $this->loader->library('tool');
        $client = $this->getClient();
		$tool = new TOOL();
		$sessionData = $tool->code2Session(PARAM['code'], $client);
		$openid = $sessionData['openid'];

		if ('false' == PARAM['value']) {
			// 删除
			$c = $this->tm->delGrade($openid);
		} else {
			// 新增
			$c = $this->tm->addGrade($openid);
		}
		if (false == $c) {
			throw new cuitException("数据库查询失败");
		}
		echo json_encode(array(
			'status' => 2000,
            'errorCode' => 2000,
			'data' => $c
		));
    }
    
    // 成绩提醒操作
    function gradeInfoV2Action()
    {
        if (!isset(PARAM['openid']) || !isset(PARAM['value']) || !isset(PARAM['sign'])) throw new cuitException('参数缺失');

        $sign = $this->genQuerySign(substr($_SERVER['REQUEST_URI'] . '/', 4), PARAM['openid'], PARAM['value']);
        
        if($sign !== PARAM['sign']) throw new CuitException("非法请求");

		$openid = PARAM['openid'];

		if ('0' == PARAM['value']) {
			// 删除
			$c = $this->tm->delGrade($openid);
		} else {
			// 新增
			$c = $this->tm->addGrade($openid);
		}
		if (false == $c) {
			throw new cuitException("数据库查询失败");
		}
		echo json_encode(array(
			'status' => 2000,
            'errorCode' => 2000,
			'data' => $c
		));
    }

    function doAutoCheckInAction()
    {
        if(date('H') < 6)exit;
        $this->loader->library('tool', 'easyHttp', 'jszx', 'mp', 'q');
        echo "自动打卡===========\r\n";
        // 零点时间戳
        $zero = strtotime(date('Y-m-d', time()));
        $this->tm->newCheckInTaskQueue();
        while ($ele = $this->tm->nextTaskEle()) {
            echo "零点-->{$zero}\r\n最后运行时间-->{$ele['rTime']}\r\n";
            echo "==================================\r\n";
            // 今天运行过了
            if ($ele['rTime'] > $zero){
                echo "今天运行过了\r\n";
                continue;
            }
            $now = date('H:i:s');
            // 还没到打卡时间
            echo "计划打卡时间--->{$ele['cTime']}---当前时间--->" . $now . "\r\n";
            if ($ele['cTime'] > $now) {
                echo "还没到打卡时间~\r\n";
                continue;
            }

            $rsa = $this->initRSA();
            
            $account = array(
                'id' => $ele['sId'],
                'pass' => $rsa->RSAPrivateDecrypt($ele['sPass'])
            );
            
            echo "登录账户{$account['id']}\r\n";
            try{
                $info = JSZX::JSZX_doLogin($account);
            }catch(Exception $e){
                print_r($e);
            }

            if (!isset($info) || is_object($info)) continue;
            $id = 0;

            // 今日打卡信息
            $today = array();
            if (isset($info['status']) && 2000 === $info['status']) {
                echo "登录成功\r\n";

                if (0 === $id) {
                    $list = JSZX::getCheckInListFuncV2($info['cookie'])['list'];
                    $today = $list['today'][0];
                    parse_str($today['link'], $arr);
                    $id = $arr['Id'];
                }

                parse_str($ele['body'], $arr);
                $arr['Id'] = $id;
                $body = http_build_query($arr);

                $ret = JSZX::postCheckIn($info['cookie'], $body);

                if(2000 !== $ret['status'])continue;
                // 连续打卡天数
                $continuous = 0;
                foreach($list['outDate'] as $value)
                {
                    if('√' !== $value['status'])break;
                    $continuous++;
                }

                if (0 >= $ele['subCnt']) {
                    echo "订阅次数用完了~\r\n";
                    $this->tm->updateCheckIn($ele['openid'], $ele['client'], array(
                        'rTime' => time(),
                    ));
                    continue;
                }

                // 发送提醒
                echo "发送提醒\r\n";
                if(!$GLOBALS['config']['sendNotice']){
                    echo "发送提醒功能已关闭！！！\r\n";
                    continue;
                }
                $time = "";
                if (isset($ret['errMsg'])) {
                    $time = substr($ret['errMsg'], 5);
                    if (false !== strpos($time, '：')) {
                        $time = explode('：', $time)[1];
                    }
                }

                if ('wx' === $ele['client']) {
                    $q = "link=UTp%3DXs%26jkdk%3DY%26ObjId%3D{$account['id']}%26Id%3D{$id}";
                    $msg = array(
                        'thing9' => array(
                            'value' => "健康打卡--{$account['id']}"
                        ),
                        'thing12' => array(
                            'value' => $today['title']
                        ),
                        'date2' => array(
                            'value' => $time
                        ),
                        'number7' => array(
                            'value' => $continuous
                        ),
                        'thing8' => array(
                            'value' => "点击查看详情"
                        ),
                    );
                    
                    $page = "/pages/checkIn/edit?{$q}";
                    $mp = new MPAPI($GLOBALS['config']['mini']['mp_appid'], $GLOBALS['config']['mini']['mp_secret']);
                    $ret = $mp->sendSub($ele['openid'], $ele['templateId'], $page, $msg);
                    $ret = json_decode($ret, true);
                    if (43101 == $ret['errcode']) $ele['subCnt'] = 0;
                } else if ('qq' === $ele['client']) {
                    $q = "link=UTp%3DXs%26jkdk%3DY%26ObjId%3D{$account['id']}%26Id%3D{$id}";
                    $msg = array(
                        'keyword1' => array(
                            'value' => '成信大健康打卡'
                        ),
                        // 打卡日期
                        'keyword2' => array(
                            'value' => substr($ret['errMsg'], 5)
                        ),
                        'keyword3' => array(
                            'value' => $today['title']
                        ),
                        'keyword4' => array(
                            'value' => "学号：{$account['id']}"
                        ),
                        'keyword5' => array(
                            'value' => "{$continuous}天"
                        ),
                        'keyword6' => array(
                            'value' => '点击查看详情'
                        ),
                    );
                    $page = "/pages/checkIn/edit?{$q}";
                    $qq = new QAPI($GLOBALS['config']['mini']['qq_appid'], $GLOBALS['config']['mini']['qq_secret']);
                    $ret = $qq->sendSub($ele['openid'], $ele['templateId'], $page, $msg);
                    $ret = json_decode($ret, true);
                    if (46001 == $ret['errcode']) $ele['subCnt'] = 0;
                } else {
                    
                }
                print_r($ret);

                if (0 === $ret['errcode'])
                    $ele['subCnt'] = ($ele['subCnt'] - 1) > 0 ? $ele['subCnt'] - 1 : 0;

                $up = $this->tm->updateCnt($ele['openid'], $ele['subCnt']);
                print_r($up);

                // self::delCheckInTask($ele['openid']);
            } else {
                // 打卡失败
                print_r($info);
                
            }
        }
        $this->tm->closeDB();
    }
    function doGradeNoticeAction()
    {
        $this->loader->library('easyHttp', 'tool', 'sso', 'webvpn', 'jwgl', 'grade', 'mp', 'q');
        $this->tm->newGradeTaskQueue();
        while ($r = $this->tm->nextTaskEle()) {
            // print_r($r);

            $rsa = $this->initRSA();
            $sId = $r['sId'];
            $sPass = $rsa->RSAPrivateDecrypt($r['sPass']);

            // SSO登录
            echo "SSO登录\r\n";
            $sso = new SSO($sId, $sPass);
            if (($d = $sso->prepareLogin()) !== true ||
                ($d = $sso->getCaptcha()) !== true ||
                ($d = $sso->deCaptcha()) !== true ||
                ($d = $sso->login()) !== true
            ) {
                // $this->doLogInfo(__FUNCTION__, print_r($d, true));
                continue;
            }
            $tgc = $sso->getTGC();

            echo "WEBVPN登录检测\r\n";
            $twfid = '';
            if(file_exists(SESSION_PATH. 'twfid'))$twfid = file_get_contents(SESSION_PATH. 'twfid');
            $wb = new WEBVPN($GLOBALS['config']['admin']['id'], $GLOBALS['config']['admin']['pass'], $twfid);
            if (!($l = $wb->checkLogin())) {
                // twfid无效
                echo "twfid无效，尝试登录\r\n";
                $wb->loginAuth();
                list($status, $err) = $wb->login();
                if ($status) {
                    $twfid = $wb->getTWFID();
                    file_put_contents(SESSION_PATH. 'twfid', $twfid);
                }
                else {
                    // CUIT::doLogInfo(__FUNCTION__, print_r($err, true));
                    continue;
                }
            }
            try{
                $j = JWGL::loginFunc("{$tgc}TWFID={$twfid}")['cookie'];
                list($grade, $err) = JWGL::getGradeFuncV2("{$tgc}TWFID={$twfid};" . $j);
            }catch(Exception $e){
                continue;
            }
            // 退出登录
            JWGL::logout("{$tgc}TWFID={$twfid};" . $j);

            if(is_numeric($grade))
            {
                // if(10001 === $status)CUIT::doLogInfo(__FUNCTION__, print_r($name, true));
                continue;
            }
            // print_r($grade);
            $name = $grade[2];
            $grade = $grade[1];
            $data = json_encode($grade);
            $newGrade = addslashes($data);

            // 提取成绩
            $oldGrade = $this->tm->getOldGrade($r['subId']);
            // print_r($oldGrade);

            // 比对成绩
            if (isset($oldGrade["grade"]) && $oldGrade["grade"] == $data) {
                echo "成绩无变化\r\n";
                $this->tm->upGrade_rTime($r['gid']);
                continue;
            }
            if (null === $oldGrade) {
                // 第一次，插入成绩
                $c = $this->tm->insertGrade($r['subId'], $newGrade);
            } else {
                // 非第一次，更新成绩
                $grade = Grade::handleGrade($oldGrade, $grade);
                $c = $this->tm->upGrade($r['subId'], $newGrade);
            }
            if (false == $c) {
                // CUIT::doLogInfo(__FUNCTION__, $db->error());
            }
            $grade['name'] = $name;
            $grade['sId'] = $sId;

            if(!$GLOBALS['config']['sendNotice']){
                echo "发送提醒功能已关闭！！！\r\n";
                continue;
            }

            $errcode = self::sendNotice($r, $grade);
            // 提醒之后根据结果更新数据库订阅
            if (0 === $errcode) {
                // 正常发送
                $r['subCnt'] = ($r['subCnt'] - 1) > 0 ? $r['subCnt'] - 1 : 0;
            } else if (1 === $errcode) {
                // 拒收|未订阅
                $r['subCnt'] = 0;
            }
            $u = $this->tm->upGrade_cnt($r['subId'], $r['subCnt']);
        }
        $this->tm->closeDB();
        return true;
    }
    
    /**
     * 
     * @return errcode 0正常|1拒收或未订阅
     */
    public static function sendNotice($r, $data)
    {
        $semester =  key($data);
        $course = current($data);
        $course_name = key($course);
        $course_data = current($course);

        $page = "/pages/grade/grade";
        $errcode = 0;
        if ('wx' === $r['client']) {
            $msg = array(
                'name1' => array(
                    'value' => $data['name']
                ),
                'character_string2' => array(
                    'value' => $data['sId']
                ),
                'thing3' => array(
                    'value' => $course_name
                ),
                'thing4' => array(
                    'value' => $course_data['learnGrade']
                ),
                'thing5' => array(
                    'value' => "学分：" . $course_data['learnCredit']
                ),
            );
            $mp = new MPAPI($GLOBALS['config']['mini']['mp_appid'], $GLOBALS['config']['mini']['mp_secret']);
            $ret = $mp->sendSub($r['openid'], $r['templateId'], $page, $msg);
            /*
            $ret = String--> {"errcode":0,"errmsg":"ok"}
            */
            var_dump($ret);
            $ret = json_decode($ret);
            if (43101 == $ret->errcode) $errcode = 1;
            else $errcode = $ret->errcode;
        } else if ('qq' === $r['client']) {

            $msg = array(
                // 姓名
                'keyword1' => array(
                    'value' => $data['name']
                ),
                // 学号
                'keyword2' => array(
                    'value' => $data['sId']
                ),
                // 课程名
                'keyword3' => array(
                    'value' => $course_name
                ),
                // 成绩
                'keyword4' => array(
                    'value' => $course_data['learnGrade']
                ),
                // 备注
                'keyword5' => array(
                    'value' => "学分：" . $course_data['learnCredit']
                ),
            );
            $q = new QAPI($GLOBALS['config']['mini']['qq_appid'], $GLOBALS['config']['mini']['qq_secret']);
            $ret = $q->sendSub($r['openid'], $r['templateId'], $page, $msg);
            /**
             * string
             * {
             *      "errcode": 0,
             *     "errmsg": "success"
             *    }
             */
            var_dump($ret);
            $ret = json_decode($ret);
            if (46001 == $ret->errcode || 46003 == $ret->errcode) $errcode = 1;
            else $errcode = $ret->errcode;
        }
        return $errcode;
    }

    function refreshMiniTokenAction()
    {
        $this->loader->library('mp', 'q');
        MP::refreshAccessToken();
        QQ::refreshAccessToken();
    }

    public function pullNewsAction(){
        $this->loader->library('newsList', 'easyHttp');
        NewsList::pull(CACHE_PATH . "/news");
    }
}