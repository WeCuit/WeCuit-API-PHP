<?php

function checkIn_genFormData($html)
{
    $html = preg_replace("/<script[\s\S]*?<\/script>/i", "", $html);
    $html = str2UTF8($html);
    $html = str_replace("gb2312", "UTF-8", $html);

    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $dom->normalize();

    $xpath = new DOMXPath($dom);

    $title = $xpath->query('/html/body/form/div[2]/table/tbody/tr[1]/td/b');
    if(!isset($title->item(0)->textContent))
        $dom->saveHTMLFile('temp.html');
    $title = $title->item(0)->textContent;

    // 打卡时间
    $checkIn_time = $xpath->query('/html/body/form/div[2]/table/tbody/tr[3]/th[2]/table/tr/td/b/span');
    $checkIn_time = $checkIn_time->item(0)->textContent;

    // 审核情况
    $sh = $xpath->query('/html/body/form/div[2]/table/tbody/tr[5]/td[2]/div/span');
    $sh = $sh->item(0)->textContent;
    
    // 匹配打卡内容
    preg_match_all("/type=hidden name=wtOR_(\d{1}) value=\"(.*?)\"/i", $html, $matches);
    $config = array();
    foreach ($matches[2] as $value) {
        $config[] = explode("\|/", $value);
    }

    // 手动
    $form = array(
        'title' => $title,
        'checkTime' => $checkIn_time,
        'data' => [
            // 0
            array(
                'type' => 'textarea',
                'id' => 'th1',
                'lable' => "1. ***个人健康现状***",
                'inputType' => 'text',
                'defaultValue' => "",
                'isRequired' => false,
                'disabled' => true
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21650_1',
                'lable' => '(1)现居住地点：',
                'defaultIdx' => $config[0][0],
                'isRequired' => true,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => 1,
                        'name' => '航空港校内'
                    ),
                    array(
                        'id' => 2,
                        'name' => '龙泉校内'
                    ),
                    array(
                        'id' => 3,
                        'name' => '新气象小区'
                    ),
                    array(
                        'id' => 4,
                        'name' => '成信家园'
                    ),
                    array(
                        'id' => 5,
                        'name' => '成都(校外)'
                    ),
                    array(
                        'id' => 6,
                        'name' => '外地'
                    )
                ]
            ),
            array(
                'type' => 'input',
                'id' => 'sF21650_2',
                'lable' => '外地详址省：',
                'inputType' => 'text',
                'defaultValue' => $config[0][1],
                'isRequired' => false,
            ),
            array(
                'type' => 'input',
                'id' => 'sF21650_3',
                'lable' => '外地详址市：',
                'inputType' => 'text',
                'defaultValue' => $config[0][2],
                'isRequired' => false,
            ),
            array(
                'type' => 'input',
                'id' => 'sF21650_4',
                'lable' => '外地详址区（县）：',
                'inputType' => 'text',
                'defaultValue' => $config[0][3],
                'isRequired' => false,
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21650_5',
                'lable' => '(2)现居住地状态：',
                'defaultIdx' => $config[0][4],
                'isRequired' => true,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => 1,
                        'name' => '一般地区'
                    ),
                    array(
                        'id' => 2,
                        'name' => '疫情防控重点地区'
                    ),
                    array(
                        'id' => 3,
                        'name' => '所在小区被隔离管控'
                    )
                ]
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21650_6',
                'lable' => '(3)今天工作状态：',
                'defaultIdx' => $config[0][5],
                'isRequired' => true,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => 1,
                        'name' => '航空港校内上班或学习'
                    ),
                    array(
                        'id' => 2,
                        'name' => '龙泉校内上班或学习'
                    ),
                    array(
                        'id' => 3,
                        'name' => '在校外完成实习任务'
                    ),
                    array(
                        'id' => 4,
                        'name' => '在校外'
                    ),
                    array(
                        'id' => 5,
                        'name' => '在家'
                    )
                ]
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21650_7',
                'lable' => '(4)个人健康状况：',
                'defaultIdx' => isset($config[0][6])?$config[0][6]:0,
                'isRequired' => true,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => 1,
                        'name' => '正常'
                    ),
                    array(
                        'id' => 2,
                        'name' => '有新冠肺炎可疑症状'
                    ),
                    array(
                        'id' => 3,
                        'name' => '疑似感染新冠肺炎'
                    ),
                    array(
                        'id' => 4,
                        'name' => '确诊感染新冠肺炎'
                    ),
                    array(
                        'id' => 5,
                        'name' => '确诊感染新冠肺炎但已康复'
                    ),
                    array(
                        'id' => 6,
                        'name' => '有呕吐情况'
                    ),
                    array(
                        'id' => 7,
                        'name' => '有腹泻情况'
                    ),
                    array(
                        'id' => 8,
                        'name' => '有呕吐＋腹泻情况'
                    )
                ]
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21650_8',
                'lable' => '(5)个人生活状态：',
                'defaultIdx' => isset($config[0][7])?$config[0][7]:0,
                'isRequired' => true,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => 1,
                        'name' => '正常'
                    ),
                    array(
                        'id' => 2,
                        'name' => '住院治疗'
                    ),
                    array(
                        'id' => 3,
                        'name' => '居家隔离观察'
                    ),
                    array(
                        'id' => 4,
                        'name' => '集中隔离观察'
                    ),
                    array(
                        'id' => 5,
                        'name' => '居家治疗'
                    )
                ]
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21650_9',
                'lable' => '(6)家庭成员状况：',
                'defaultIdx' => isset($config[0][8])?$config[0][8]:0,
                'isRequired' => true,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => 1,
                        'name' => '全部正常'
                    ),
                    array(
                        'id' => 2,
                        'name' => '有人有可疑症状'
                    ),
                    array(
                        'id' => 3,
                        'name' => '有人疑似感染'
                    ),
                    array(
                        'id' => 4,
                        'name' => '有人确诊感染'
                    ),
                    array(
                        'id' => 5,
                        'name' => '有人确诊感染但已康复'
                    )
                ]
            ),
            array(
                'type' => 'textarea',
                'id' => 'sF21650_10',
                'lable' => '(7)其他需要说明的情况：',
                'defaultValue' => isset($config[0][9])?$config[0][9]:0,
            ),

            // 申请进出学校(无需求则不填)---1
            array(
                'type' => 'textarea',
                'id' => 'th2',
                'lable' => "2. ***申请进出学校(无需求则不填)***",
                'inputType' => 'text',
                'defaultValue' => "注意：更改自动打卡时，本部分不会被记录",
                'isRequired' => false,
                'disabled' => true
            ),
            array(
                'type' => 'input',
                'id' => 'sF21912_1',
                'lable' => "目的地：",
                'inputType' => 'text',
                'defaultValue' => $config[1][0],
                'isRequired' => false,
            ),
            array(
                'type' => 'textarea',
                'id' => 'sF21912_2',
                'lable' => '事由：',
                'defaultValue' => isset($config[1][1])?$config[1][1]:0,
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21912_3',
                'lable' => '计划(天)：',
                'defaultIdx' => isset($config[1][2])?$config[1][2]:0,
                'isRequired' => false,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => 1,
                        'name' => '今天'
                    ),
                    array(
                        'id' => 2,
                        'name' => '明天'
                    ),
                    array(
                        'id' => 3,
                        'name' => '后天'
                    ),
                ]
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21912_4',
                'lable' => '计划（时间）：',
                'defaultIdx' => isset($config[1][3]) && (int)$config[1][3]>5?(int)$config[1][3]-5:0,
                'isRequired' => false,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => '06',
                        'name' => '06:00'
                    ),
                    array(
                        'id' => '07',
                        'name' => '07:00'
                    ),
                    array(
                        'id' => '08',
                        'name' => '08:00'
                    ),
                    array(
                        'id' => '09',
                        'name' => '09:00'
                    ),
                    array(
                        'id' => '10',
                        'name' => '10:00'
                    ),
                    array(
                        'id' => '11',
                        'name' => '11:00'
                    ),
                    array(
                        'id' => '12',
                        'name' => '12:00'
                    ),
                    array(
                        'id' => '13',
                        'name' => '13:00'
                    ),
                    array(
                        'id' => '14',
                        'name' => '14:00'
                    ),
                    array(
                        'id' => '15',
                        'name' => '15:00'
                    ),
                    array(
                        'id' => '16',
                        'name' => '16:00'
                    ),
                    array(
                        'id' => '17',
                        'name' => '17:00'
                    ),
                    array(
                        'id' => '18',
                        'name' => '18:00'
                    ),
                    array(
                        'id' => '19',
                        'name' => '19:00'
                    ),
                    array(
                        'id' => '20',
                        'name' => '20:00'
                    ),
                    array(
                        'id' => '21',
                        'name' => '21:00'
                    ),
                    array(
                        'id' => '22',
                        'name' => '22:00'
                    ),
                ]
            ),
            // 至
            array(
                'type' => 'picker',
                'id' => 'sF21912_5',
                'lable' => '至（天）：',
                'defaultIdx' => isset($config[1][4])?($config[1][4]==9?4:$config[1][4]):0,
                'isRequired' => false,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => 1,
                        'name' => '当天'
                    ),
                    array(
                        'id' => 2,
                        'name' => '第2天'
                    ),
                    array(
                        'id' => 3,
                        'name' => '第3天'
                    ),
                    array(
                        'id' => 9,
                        'name' => '下学期'
                    ),
                ]
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21912_6',
                'lable' => '至（时间）：',
                'defaultIdx' => isset($config[1][5]) && (int)$config[1][5]>6?(int)$config[1][5]-6:0,
                'isRequired' => false,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => '07',
                        'name' => '07:00'
                    ),
                    array(
                        'id' => '08',
                        'name' => '08:00'
                    ),
                    array(
                        'id' => '09',
                        'name' => '09:00'
                    ),
                    array(
                        'id' => '10',
                        'name' => '10:00'
                    ),
                    array(
                        'id' => '11',
                        'name' => '11:00'
                    ),
                    array(
                        'id' => '12',
                        'name' => '12:00'
                    ),
                    array(
                        'id' => '13',
                        'name' => '13:00'
                    ),
                    array(
                        'id' => '14',
                        'name' => '14:00'
                    ),
                    array(
                        'id' => '15',
                        'name' => '15:00'
                    ),
                    array(
                        'id' => '16',
                        'name' => '16:00'
                    ),
                    array(
                        'id' => '17',
                        'name' => '17:00'
                    ),
                    array(
                        'id' => '18',
                        'name' => '18:00'
                    ),
                    array(
                        'id' => '19',
                        'name' => '19:00'
                    ),
                    array(
                        'id' => '20',
                        'name' => '20:00'
                    ),
                    array(
                        'id' => '21',
                        'name' => '21:00'
                    ),
                    array(
                        'id' => '22',
                        'name' => '22:00'
                    ),
                    array(
                        'id' => '23',
                        'name' => '23:00'
                    ),
                ]
            ),
            array(
                'type' => 'input',
                'id' => 'sh',
                'lable' => "审核情况：",
                'inputType' => 'text',
                'defaultValue' => $sh,
                'isRequired' => false,
                'disabled'=> true
            ),

            // 2
            array(
                'type' => 'textarea',
                'id' => 'th1',
                'lable' => "3. ***最近14天以来的情况***",
                'inputType' => 'text',
                'defaultValue' => "",
                'isRequired' => false,
                'disabled' => true
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21648_1',
                'lable' => '(1)曾前往疫情防控重点地区？',
                'defaultIdx' => $config[2][0]=='Y'?1:2,
                'isRequired' => true,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => 'Y',
                        'name' => '是'
                    ),
                    array(
                        'id' => 'N',
                        'name' => '否'
                    ),
                ]
            ),
            array(
                'type' => 'textarea',
                'id' => 'sF21648_2',
                'lable' => '若曾前往，请写明时间、地点及简要事由：',
                'defaultValue' => $config[2][1]
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21648_3',
                'lable' => '(2)接触过疫情防控重点地区高危人员？',
                'defaultIdx' => $config[2][2]=='Y'?1:2,
                'isRequired' => true,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '-请选择-'
                    ),
                    array(
                        'id' => 'Y',
                        'name' => '是'
                    ),
                    array(
                        'id' => 'N',
                        'name' => '否'
                    ),
                ]
            ),
            array(
                'type' => 'textarea',
                'id' => 'sF21648_4',
                'lable' => '若接触过，请写明时间、地点及简要事由：',
                'defaultValue' => $config[2][3]
            ),
            array(
                'type' => 'picker',
                'id' => 'sF21648_5',
                'lable' => '(3)接触过感染者或疑似患者？',
                'defaultIdx' => ($config[2][4]=='Y'?1:2),
                'isRequired' => true,
                'range' => [
                    array(
                        'id' => 0,
                        'name' => '—请选择—'
                    ),
                    array(
                        'id' => 'Y',
                        'name' => '是'
                    ),
                    array(
                        'id' => 'N',
                        'name' => '否'
                    ),
                ]
            ),
            array(
                'type' => 'textarea',
                'id' => 'sF21648_6',
                'lable' => '若接触过，请写明时间、地点及简要事由：',
                'defaultValue' => $config[2][5]
            ),

            // 2
            // array(
            //     'type' => 'picker',
            //     'id' => 'sF21649_1',
            //     'lable' => '主要交通方式：',
            //     'defaultIdx' => $config[2][0],
            //     'isRequired' => false,
            //     'range' => [
            //         array(
            //             'id' => 0,
            //             'name' => '—请选择—'
            //         ),
            //         array(
            //             'id' => 1,
            //             'name' => '飞机'
            //         ),
            //         array(
            //             'id' => 2,
            //             'name' => '火车'
            //         ),
            //         array(
            //             'id' => 3,
            //             'name' => '汽车'
            //         ),
            //         array(
            //             'id' => 4,
            //             'name' => '轮船'
            //         ),
            //         array(
            //             'id' => 5,
            //             'name' => '私家车或专车'
            //         ),
            //         array(
            //             'id' => 6,
            //             'name' => '其他'
            //         ),
            //     ]
            // ),
            // array(
            //     'type' => 'input',
            //     'id' => 'sF21649_2',
            //     'lable' => '公共交通的航班号、车次等：',
            //     'defaultValue' => $config[2][1],
            //     'isRequired' => false,
            // ),
            // array(
            //     'type' => 'input',
            //     'id' => 'sF21649_3',
            //     'lable' => '返校（预计）时间(月)：',
            //     'defaultValue' => $config[2][2],
            //     'isRequired' => false
            // ),
            // array(
            //     'type' => 'input',
            //     'id' => 'sF21649_4',
            //     'lable' => '预返时(日)：',
            //     'defaultValue' => $config[2][3],
            //     'isRequired' => false
            // ),
        ]
    );
    // 手动END


    // 自动
    // $items = $xpath->query('/html/body/form/div[2]/table/tbody/tr/td[2]');

    // $tag2type = array(
    //     'select' => 'picker'
    // );
    // for ($i=0; $i < $items->length; $i++) { 
    //     $td = $items->item($i);
    //     $title = $td->childNodes->item(1)->textContent;
    //     $formItems = $td->childNodes->item(3)->childNodes;

    //     $temp = array();
    //     for ($i=0; $i < $formItems->length; $i++) { 
    //         $ele = $formItems->item($i);
    //         if($ele->nodeName == 'br'){
    //             $temp = array();
    //             continue;
    //         }


    //         $temp['type'] = 
    //         print_r($temp);
    //     }
    //     print_r($td->childNodes->item(3)->childNodes);
    //     exit;
    // }

    // echo $dom->saveHTML();
    return $form;
}

function checkIn_json2body($post)
{
    if (!(isset($post['link']))) throw new cuitException("参数缺失");
    parse_str($post['link'], $arr);
    if (!isset($arr['UTp'])) throw new cuitException('参数有误');
    $healthStatus = $post['healthStatus'];
    $outIn = $post['outIn'];
    $monthStatus = $post['monthStatus'];
    $transport = $post['transport'];
    $checkbox = array(
        true => 'Y',
        false => 'N'
    );
    $outTime = array('', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22');
    $inTime = array('', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23');
    $data = array(
        "RsNum" => "4",
        "Id" => $arr['Id'],
        "Tx" => "33_1",
        "canTj" => "1",
        "isNeedAns" => "0",
        "UTp" => $arr['UTp'],
        "ObjId" => $arr['ObjId'],
        // -------------个人健康现状-----------------------
        "th_1" => "21650",
        "wtOR_1" => "a\|/a\|/a\|/a\|/a\|/a\|/a\|/a\|/a\|/a",
        "sF21650_1" => $healthStatus[0]['index'],       // (1)现居住地点为
        "sF21650_2" => str2GBK($healthStatus[0]['outLand']['province']),        // 外地详址[省]
        "sF21650_3" => str2GBK($healthStatus[0]['outLand']['city']),        // 外地详址[市]
        "sF21650_4" => str2GBK($healthStatus[0]['outLand']['area']),        // 外地详址[区(县)]
        "sF21650_5" => $healthStatus[1]['index'],        // (2)现居住地状态
        "sF21650_6" => $healthStatus[2]['index'],        // (3)今天工作状态
        "sF21650_7" => $healthStatus[3]['index'],        // (4)个人健康状况
        "sF21650_8" => $healthStatus[4]['index'],        // (5)个人生活状态
        "sF21650_9" => $healthStatus[5]['index'],        // (6)家庭成员状况
        "sF21650_10" => str2GBK($post['healthStatusOtherInfo']),        // (7)其他需要说明的情况
        "sF21650_N" => "10",
        // -------------申请进出学校(无需求则不填)-----------------------
        "th_2" => "21912",
        "wtOR_2" => "a\|/a\|/a\|/a\|/a\|/a",
        "sF21912_1" => str2GBK($outIn['toPlace']),        // 目的地
        "sF21912_2" => str2GBK($outIn['toResult']),        // 事由
        "sF21912_3" => $outIn['outIndex'][0],        // 出校[今/明/后]
        "sF21912_4" => $outTime[$outIn['outIndex'][1]],        // 出校[几点]
        "sF21912_5" => $outIn['inIndex'][0],        // 回校[当天/第2天/第3天]
        "sF21912_6" => $inTime[$outIn['inIndex'][1]],        // 回校[几点]
        "sF21912_N" => "6",
        // -----------最近一个月以来的情况------------
        "th_3" => "21648",
        "wtOR_3" => "N\|/666\|/N\|/666\|/N\|/666",
        "sF21648_1" => $checkbox[$monthStatus[0]['isGo']],        // (1)曾前往疫情防控重点地区？
        "sF21648_2" => str2GBK($monthStatus[0]['details']),        // 若曾前往，请写明时间、地点及简要事由
        "sF21648_3" => $checkbox[$monthStatus[1]['isGo']],        // (2)接触过疫情防控重点地区高危人员
        "sF21648_4" => str2GBK($monthStatus[1]['details']),        // 若接触过，请写明时间、地点及简要事由
        "sF21648_5" => $checkbox[$monthStatus[2]['isGo']],        // (3)接触过感染者或疑似患者？
        "sF21648_6" => str2GBK($monthStatus[2]['details']),        // 若接触过，请写明时间、地点及简要事由
        "sF21648_N" => "6",
        // -----------从外地返校(预计，目前已在成都的不填)情况------------
        "th_4" => "21649",
        "wtOR_4" => "6\|/666\|/666\|/666",
        "sF21649_1" => $transport['methodIndex'],        // 主要交通方式
        "sF21649_2" => $transport['toolId'],        // 公共交通的航班号、车次等
        "sF21649_3" => $transport['backTime']['month'],        // 返校（预计）时间[月]
        "sF21649_4" => $transport['backTime']['day'],        // 返校（预计）时间[日]
        "sF21649_N" => "4",
        "zw1" => "",
        "cxStYt" => "A",
        "zw2" => "",
        "B2" => str2GBK("提交打卡"),
    );
    return http_build_query($data);
}

function checkIn_form2body($form, $link, $request = true){
    parse_str($link, $arr);
    $ret = array(
        "RsNum" => "3",
        "Id" => $arr['Id'],
        "Tx" => "33_1",
        "canTj" => "1",
        "isNeedAns" => "0",
        "UTp" => $arr['UTp'],
        "ObjId" => $arr['ObjId'],
        // -------------个人健康现状-----------------------
        "th_1" => "21650",
        "wtOR_1" => "a\|/a\|/a\|/a\|/a\|/a\|/a\|/a\|/a\|/a",
        "sF21650_1" => $form['sF21650_1'],       // (1)现居住地点为
        "sF21650_2" => str2GBK($form['sF21650_2']),        // 外地详址[省]
        "sF21650_3" => str2GBK($form['sF21650_3']),        // 外地详址[市]
        "sF21650_4" => str2GBK($form['sF21650_4']),        // 外地详址[区(县)]
        "sF21650_5" => $form['sF21650_5'],        // (2)现居住地状态
        "sF21650_6" => $form['sF21650_6'],        // (3)今天工作状态
        "sF21650_7" => $form['sF21650_7'],        // (4)个人健康状况
        "sF21650_8" => $form['sF21650_8'],        // (5)个人生活状态
        "sF21650_9" => $form['sF21650_9'],        // (6)家庭成员状况
        "sF21650_10" => str2GBK($form['sF21650_10']),        // (7)其他需要说明的情况
        "sF21650_N" => "10",
        // -------------申请进出学校(无需求则不填)-----------------------
        "th_2" => "21912",
        "wtOR_2" => "a\|/a\|/a\|/a\|/a\|/a",
        "sF21912_1" => $request?str2GBK($form['sF21912_1']):'',        // 目的地
        "sF21912_2" => $request?str2GBK($form['sF21912_2']):'',        // 事由
        "sF21912_3" => $request?$form['sF21912_3']:'',        // 出校[今/明/后]
        "sF21912_4" => $request?$form['sF21912_4']:'',        // 出校[几点]
        "sF21912_5" => $request?$form['sF21912_5']:'',        // 回校[当天/第2天/第3天/下学期]
        "sF21912_6" => $request?$form['sF21912_6']:'',        // 回校[几点]
        "sF21912_N" => "6",
        // -----------最近一个月以来的情况------------
        "th_3" => "21648",
        "wtOR_3" => "a\|/666\|/a\|/666\|/a\|/666",
        "sF21648_1" => $form['sF21648_1'],        // (1)曾前往疫情防控重点地区？
        "sF21648_2" => str2GBK($form['sF21648_2']),        // 若曾前往，请写明时间、地点及简要事由
        "sF21648_3" => $form['sF21648_3'],        // (2)接触过疫情防控重点地区高危人员
        "sF21648_4" => str2GBK($form['sF21648_4']),        // 若接触过，请写明时间、地点及简要事由
        "sF21648_5" => $form['sF21648_5'],        // (3)接触过感染者或疑似患者？
        "sF21648_6" => str2GBK($form['sF21648_6']),        // 若接触过，请写明时间、地点及简要事由
        "sF21648_N" => "6",
        // -----------从外地返校(预计，目前已在成都的不填)情况------------
        // "th_3" => "21649",
        // "wtOR_4" => "6\|/666\|/666\|/666",
        // "sF21649_1" => $form['sF21649_1'],        // 主要交通方式
        // "sF21649_2" => str2GBK($form['sF21649_2']),        // 公共交通的航班号、车次等
        // "sF21649_3" => $form['sF21649_3'],        // 返校（预计）时间[月]
        // "sF21649_4" => $form['sF21649_4'],        // 返校（预计）时间[日]
        // "sF21649_N" => "4",
        "zw1" => "",
        "cxStYt" => "A",
        "zw2" => "",
        "B2" => str2GBK("提交打卡"),
    );
    return http_build_query($ret);
}

function str2GBK($str)
{
    $encoding = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GBK', 'GB2312', 'BIG5'));
    if ($encoding != 'GBK') {
        return mb_convert_encoding($str, 'GBK', $encoding);
    }
    return $str;
}
function str2UTF8($str)
{
    // 编码处理
    $encoding = mb_detect_encoding($str, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
    // 如果字符串的编码格式不为UTF_8就转换编码格式
    if ($encoding != 'UTF-8') {
        return mb_convert_encoding($str, 'UTF-8', $encoding);
    }
    return $str;
}
