<?php
class JWGL
{

	// 考试安排表(Jwgl控制器调用) TODO:废弃
	public static function getStdExamTable()
	{
		if (!isset(PARAM['cookie'])) throw new cuitException("参数缺失");
		if (!isset(PARAM['batchId'])) throw new cuitException("参数缺失");
		$http = new EasyHttp();
		$exam = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/stdExamTable!examTable.action?examBatch.id=' . PARAM['batchId'], array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 5,		//	最大重定向次数
			'httpversion' => '1.0',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				'cookie' => PARAM['cookie']
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($exam)) throw new cuitException("服务器网络错误", 10511);
		$body = $exam['body'];
		$body = str_replace(array("\n", "\r", "\r\n", "<font color=\"BBC4C3\">", "</font>", "\t"), "", $body);
		if (strpos($body, "考试地点") === false) throw new cuitException("似乎还没有登录呢");
		preg_match_all("/%\" >(.*?)<\/th>/i", $body, $header);
		preg_match_all("/<td>(.*?)<\/td>/i", $body, $matches);
		$type = array(
			'courseId',
			'courseName',
			'examType',
			'examDate',
			'examTime',
			'examSite',
			'credit',
			'examStatus',
			'remark'
		);
		$i = 0;
		$th = array();
		foreach ($header[1] as $value) {
			$th[] = array(
				'text' => $type[$i % count($header[1])],
				'display' => $value
			);
			$i++;
		}
		$table = array(
			'th' => $th,
			'tb' => array()
		);
		$temp = array();
		foreach ($matches[1] as $value) {
			if (strpos($value, "href") != false) {
				$value = preg_replace('/<a[^>]*>(.*?)<\/a>/is', '$1', $value);
			} else if (strpos($value, "sup") != false) {
				$value = preg_replace('/<sup[^>]*>(.*?)<\/sup>/is', '($1)', $value);
			}
			$temp[$type[$i % count($table['th'])]] = $value;
			if (count($temp) == count($table['th'])) {
				if (empty($temp[$type[$i % count($table['th'])]]))
					$temp[$type[$i % count($table['th'])]] = "无";
				$table['tb'][] = $temp;
				$temp = array();
			}
			$i++;
		}
		$table['errorCode'] = $table['status'] = 2000;
		echo json_encode($table);
		exit;
	}
	// 考试安排表(Jwgl控制器调用) 
	public static function getExamTable()
	{
		if (!isset(PARAM['cookie'])) throw new cuitException("参数缺失");
		if (!isset(PARAM['batchId'])) throw new cuitException("参数缺失");
		$http = new EasyHttp();
		$exam = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/stdExamTable!examTable.action?examBatch.id=' . PARAM['batchId'], array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 5,		//	最大重定向次数
			'httpversion' => '1.0',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				'cookie' => PARAM['cookie']
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($exam)) throw new cuitException("服务器网络错误", 10511);
		$body = $exam['body'];
		$body = str_replace(array("\n", "\r", "\r\n", "<font color=\"BBC4C3\">", "</font>", "\t"), "", $body);
		if (strpos($body, "考试地点") === false) throw new cuitException("似乎还没有登录呢");

		preg_match_all("/<td>(.*?)<\/td>/i", $body, $matches);
		$type = array(
			'courseId',
			'courseName',
			'examType',
			'examDate',
			'examTime',
			'examSite',
			'credit',
			'examStatus',
			'remark'
		);
		$i = 0;
		$table = array(
			'examList' => array()
		);
		$temp = array();
		foreach ($matches[1] as $value) {
			if (strpos($value, "href") != false) {
				$value = preg_replace('/<a[^>]*>(.*?)<\/a>/is', '$1', $value);
			} else if (strpos($value, "sup") != false) {
				$value = preg_replace('/<sup[^>]*>(.*?)<\/sup>/is', '($1)', $value);
			}
			$temp[$type[$i % count($type)]] = $value;
			if (count($temp) == count($type)) {
				if (empty($temp[$type[$i % count($type)]]))
					$temp[$type[$i % count($type)]] = "无";
				$table['examList'][] = $temp;
				$temp = array();
			}
			$i++;
		}
		$table['errorCode'] = $table['status'] = 2000;
		echo json_encode($table);
	}

	// 考试查询表单用(Jwgl控制器间接调用)
	private static function getBatchAndSemester($semester = '')
	{
		$cookie = PARAM['cookie'];
		if ($semester)
			$cookie = "semester.id={$semester};{$cookie}";
		$http = new EasyHttp();
		$exam = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/stdExamTable.action', array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.1',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				'cookie' => $cookie
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($exam)) throw new cuitException("服务器网络错误", 10511);
		if (200 !== $exam['response']['code']) throw new cuitException("阁下似乎还未登录", 13401);
		if (!isset($exam['cookies'][0]->value)) throw new cuitException("未知错误");
		$semesterId =  $exam['cookies'][0]->value;
		preg_match_all("/<option value=\"(\d+)\"(.*?)>(.*?)<\/option>/i", $exam['body'], $matches);
		$batch = array();
		foreach ($matches[1] as $key => $value) {
			$batch[] = array(
				'id' => $value,
				'name' => $matches[3][$key],
			);
		}
		return array(
			'semester' => $semesterId,
			'batch' => $batch
		);
	}

	// 获取学期列表(Jwgl控制器调用)
	private static function getTermList($semester = '')
	{
		$http = new EasyHttp();
		// 激活
		$exam = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/teach/grade/course/person.action?_=1602123116051', array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.0',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				'cookie' => PARAM['cookie']
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($exam)) throw new cuitException("服务器网络错误", 10511);
		$exam = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/dataQuery.action?sf_request_type=ajax', array(
			'method' => 'POST',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.1',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				'cookie' => PARAM['cookie'],
				'Referer' => 'http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/stdExamTable!examTable.action',
				'X-Requested-With' => 'XMLHttpRequest'
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => "tagId=semesterBar17596100931Semester&dataType=semesterCalendar&value={$semester}&empty=false",
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($exam)) throw new cuitException("服务器网络错误", 10511);
		if (200 !== $exam['response']['code']) throw new cuitException("{$exam['response']['code']}阁下似乎还未登录", 13401);
		$body = $exam['body'];
		$body = str_replace(array("\n", "\r\n", "\r", "\t"), "", $body);
		// 	print_r($body);
		// 	$body = str_replace(":\"", "\":\"", $body);
		// 	$body = str_replace("\",", "\",\"", $body);
		// 	$body = str_replace("{yearDom", "{\"yearDom", $body);
		// 	$body = str_replace("id:", "\"id\":", $body);
		// 	$body = str_replace(",y", ",\"y", $body);
		// 	$body = str_replace(":[", "\":[", $body);
		// 	$body = str_replace(",schoo", ",\"schoo", $body);
		// 	$body = str_replace(":{", "\":{\"", $body);
		$body = preg_replace("/\{(\w+?):/i", "{\"$1\":", $body);
		$body = preg_replace("/,(\w+?):/i", ",\"$1\":", $body);
		// 	print_r($body);
		$body = json_decode($body, true);
		if (!isset($body['semesters'])) {
			$err = array(
				"status" => 2001,
				"errMsg" => "学期信息获取失败"
			);
			echo json_encode($err);
			exit;
		}
		$sem = $body['semesters'];
		$semester = array();
		foreach ($sem as $value) {
			$semester[0][] = array(
				'name' => $value[0]['schoolYear']
			);
			unset($value[0]['schoolYear']);
			unset($value[1]['schoolYear']);
			$value[0]['name'] = "第{$value[0]['name']}学期";
			$value[1]['name'] = "第{$value[1]['name']}学期";
			$semester[1][] = $value;
		}
		if (!isset($semester[0])) {
			// self::doLogInfo(__FUNCTION__ . "响应体", $exam['body']);
			throw new cuitException("系统错误，已记录");
		}
		$semester[0] = array_reverse($semester[0]);
		$semester[1] = array_reverse($semester[1]);
		$semester['len'] = count($semester[1]);
		// 	$body['originalTermData'] = $body['semesters'];
		$body['yearIndex'] = -1 == $body['yearIndex'] ? 0 : count($semester[1]) - 1 - $body['yearIndex'];
		$body['semesters'] = $semester;
		return $body;
	}

	// 考试查询表单(Jwgl控制器调用)
	public static function getExamOption()
	{
		if (!isset(PARAM['cookie'])) throw new cuitException("参数缺失");
		/**
		 * GET http://jwgl.cuit.edu.cn/eams/stdExamTable!examTable.action?examBatch.id=1103
		 * */
		preg_match("/semester.id=(\d+);/u", PARAM['cookie'], $matches);
		$semester = '';
		if(isset($matches[1]))$semester = $matches[1];
		$bs = self::getBatchAndSemester($semester);
		$term = self::getTermList($bs['semester']);
		$bs['semesterCalendar'] = $term;
		$bs['errorCode'] = $bs['status'] = 2000;
		// echo json_encode($bs);
		// $termList = self::examGetTermList($semesterId);
		return $bs;
	}

	// 获取课表选项(Jwgl控制器调用)
	public static function getCourseOption()
	{
		$ret = array();
		if (!isset(PARAM['cookie'])) throw new cuitException("参数缺失");
		$http = new EasyHttp();
		$course = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/courseTableForStd.action', array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.0',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				'cookie' => PARAM['cookie']
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($course)) throw new cuitException("服务器网络错误", 10511);
		if (200 !== $course['response']['code']) throw new cuitException("阁下似乎还未登录", 13401);
		$body = $course['body'];
		preg_match_all("/<option value=\"(.*?)\">(.*?)<\/option>/i", $body, $matches);
		$i = 0;
		$ret['courseType'] = array();
		$ret['courseWeek'] = array();
		foreach ($matches[1] as $key => $value) {
			$t = array(
				'key' => $value,
				'name' => $matches[2][$key]
			);
			if ($i < 2)
				$ret['courseType'][] = $t;
			else
				$ret['courseWeek'][] = $t;
			$i++;
		}
		$ret['errorCode'] = $ret['status'] = 2000;
		$ret['semesters'] = self::getTermList();
		return $ret;
	}

	// 获取最新课表(Jwgl控制器调用)
	public static function getCourseTableV2()
	{
		// if(!isset(PARAM['maintenance']))
		//     throw new cuitException("抱歉课表系统维护");
		if (!isset(PARAM['cookie'])) throw new cuitException("参数缺失");
		if (!(isset(PARAM['courseType']) && isset(PARAM['semester'])))
			throw new cuitException("参数缺失");
		$http = new EasyHttp();
		$course = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/courseTableForStd.action?_=1602082567619', array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.0',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				'cookie' => PARAM['cookie']
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($course)) throw new cuitException("服务器网络错误", 10511);
		if (200 != $course['response']['code']) throw new cuitException("似乎还没有登录呢", 13401);

		$body = $course['body'];
		$body = str_replace(array("\n", "\r\n", "\r"), "", $body);
		preg_match_all("/if\(jQuery\(\"#courseTableType\"\)\.val\(\)==\"std\"\)\{(.*?)form\.addInput\(form,\"ids\",\"(\d+)\"(.*?)form\.addInput\(form,\"ids\",\"(\d+)\"/", $body, $id);
		// 	print_r($id);
		$ids = array(
			'std' => $id[2][0],
			'class' => $id[4][0]
		);
		// 	exit;

		$semesterId = $course['cookies'][0]->value;
		$course = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/courseTableForStd!courseTable.action?sf_request_type=ajax', array(
			'method' => 'POST',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.0',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				'cookie' => PARAM['cookie']
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => "ignoreHead=1&setting.kind=" . PARAM['courseType'] . "&startWeek=&semester.id={$semesterId}&ids=" . $ids[PARAM['courseType']],
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($course)) throw new cuitException("服务器网络错误", 10511);
		if (200 != $course['response']['code']) throw new cuitException("似乎还没有登录呢", 13401);
		$body = $course['body'];
		$ret['classtable'] = self::courseHandleV2($body);
		$ret['start'] = self::getStartDateOfTerm();
		$ret['errorCode'] = $ret['status'] = 2000;
		$ret['location'] = self::getStdPos();
		return $ret;
	}

	// 获取学生所在校区(Jwgl控制器间接调用)
	static function getStdPos()
	{
		$http = new EasyHttp();
		$detail = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/stdDetail.action', array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.0',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				'cookie' => PARAM['cookie']
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($detail)) throw new cuitException("服务器网络错误", 10511);
		if (200 != $detail['response']['code']) throw new cuitException("似乎还没有登录呢", 13401);
		if (false !== strpos($detail['body'], "航空港")) {
			return 'hkg';
		}
		return 'lq';
	}

	// 获取一学期的开始时间(Jwgl控制器间接调用)
	private static function getStartDateOfTerm()
	{
		$http = new EasyHttp();
		$request = $http->request('https://jwc.cuit.edu.cn', array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.0',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($request)) throw new cuitException("服务器网络错误", 10511);
		$body = $request['body'];
		preg_match("/datedifference\(s1, '(\d+)-(\d+)-(\d+)'\);/", $body, $matches);
		return array(
			'year' => $matches[1],
			'month' => $matches[2],
			'day' => $matches[3]
		);
	}

	// 获取成绩表v2(Jwgl控制器调用)
	public static function getGradeTableV2()
	{
		if (!isset(PARAM['cookie'])) throw new cuitException("参数缺失");
		list($grade, $err) = self::getGradeFuncV2(PARAM['cookie']);
		// print_r($grade);
		// list($grade1, , $count)
		$grade1 = $grade;
		$count = $grade[3];
		if (is_numeric($grade)) {
			echo json_encode(['status' => $grade, 'errorCode' => $grade, 'errMsg' => $err]);
			exit;
		}
		$grade1 = $grade1[0];
		$count = str_replace(" ", "", $count);
		preg_match_all("/\d+\.?\d*/i", $count, $total);
		$total = array(
			'learnTime' => $total[0][0],
			'creditTotal' => $total[0][1],
			'creditGet' => $total[0][2],
			'point' => $total[0][3]
		);
		// print_r($grade1);
		return ['status' => 2000, 'errorCode' => 2000, 'grade' => array_reverse($grade1), 'total' => $total];
	}

	/**
	 * 解析课程html转换为数组(Jwgl间接调用)
	 */
	private static function courseHandleV2($html)
	{
		$body = str_replace(array("\r\n", "\r", "\n"), "", $html);
		// echo $body;
		preg_match("/function CourseTable in TaskActivity.js(.*?)div id='tasklesson' /i", $body, $m);
		if (!isset($m[1])) {
			// self::doLogInfo(__FUNCTION__, "cookie:" . PARAM['cookie'] . "|||" . $html);
			throw new cuitException("系统异常，已记录");
		}
		$t = explode("var teachers =", $m[1]); //异常
		unset($t[0]);
		$courses = array();
		foreach ($t as $key => $value) {
			// 教师 id & 名称
			preg_match("/var actTeachers = \[\{id:(\d+),name:\"(.*?)\"(.*?)\]/iu", $value, $matches0);
			// 课程信息
			preg_match("/actTeacherName\.join\(','\),\"(.*?)\",\"(.*?)\",\"(\d+)\",\"(.*?)\",\"(\d+)\",/iu", $value, $matches1);
			// 课程安排
			preg_match_all("/index =(\d?)\*unitCount\+(\d{0,2});/iU", $value, $matches2);
			if (empty($matches0[1])) $matches0[1] = "";
			if (empty($matches0[2])) $matches0[2] = "";
			$temp = array(
				'teacherId' => $matches0[1],
				'teacherName' => $matches0[2],
				'name' => $matches1[2],
				'place' => $matches1[4]
			);
			if (!isset($matches2[1][0])) {
				// self::doLogInfo(__FUNCTION__, json_encode($_POST) . "|||" . $html);
				throw new cuitException("系统异常，已记录");
			}
			$temp['day_of_week'] = $matches2[1][0] + 1;
			$temp['class_of_day'] = $matches2[2][0] + 1;
			$temp['duration'] = count($matches2[0]);
			// 处理上课周数
			for ($i = 0; $i < strlen($matches1[5]); $i++) {
				if ($matches1[5][$i] == 1)
					$temp['week_num'][] = $i;
			}
			$courses[] = $temp;
		}
		return $courses;
	}

	/**
	 * (Jwgl、Task控制器调用)
	 */
	public static function loginFunc($c)
	{
		$http = new EasyHttp();
		$login = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/home.action', array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.1',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				"Content-Type" => "application/x-www-form-urlencoded",
				"referer" => "http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/",
				'cookie' => $c
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($login)) throw new cuitException("服务器网络错误", 10511);
		if($login['response']['code'] != 200 && $login['response']['code'] != 302){
			throw new cuitException("教务处异常->{$login['response']['code']}", 13 . $login['response']['code']);
		}
		if (strpos($login['headers']['location'], "//webvpn") !== false) throw new cuitException("WebVpn未登录", 11401);
		$cookie = "JSESSIONID={$login['cookies'][0]->value}; GSESSIONID={$login['cookies'][1]->value}";

		// http://jwgl.cuit.edu.cn/eams/login.action;jsessionid=*****
		$login = $http->request($login['headers']['location'], array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.1',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				"Content-Type" => "application/x-www-form-urlencoded",
				"referer" => "http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/",
				'cookie' => "{$cookie}; {$c}"
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($login)) throw new cuitException("服务器网络错误", 10511);
		// https://sso.cuit.edu.cn/authserver/login?service=***
		$login = $http->request($login['headers']['location'], array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.1',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				"Content-Type" => "application/x-www-form-urlencoded",
				"referer" => "http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/",
				'cookie' => "{$cookie}; {$c}"
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($login) || !isset($login['headers']['location'])) throw new cuitException("请求失败,请重试", 10511);
		// 	http://jwgl.cuit.edu.cn/eams/login.action;jsessionid=*****?ticket=****
		$login = $http->request($login['headers']['location'], array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.1',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				"Content-Type" => "application/x-www-form-urlencoded",
				"referer" => "http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/",
				'cookie' => "{$cookie}; {$c}"
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($login) || !isset($login['headers']['location'])) throw new cuitException("请求失败,请重试", 10511);
		// 	http://jwgl.cuit.edu.cn/eams/login.action;jsessionid=****
		$login = $http->request($login['headers']['location'], array(
			'method' => 'GET',		//	GET/POST
			'timeout' => 5,			//	超时的秒数
			'redirection' => 0,		//	最大重定向次数
			'httpversion' => '1.1',	//	1.0/1.1
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
			'blocking' => true,		//	是否阻塞
			'headers' => array(
				"Content-Type" => "application/x-www-form-urlencoded",
				"referer" => "http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/",
				'cookie' => "{$cookie}; {$c}"
			),	//	header信息
			'cookies' => array(),	//	关联数组形式的cookie信息
			'body' => null,
			'compress' => false,	//	是否压缩
			'decompress' => true,	//	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null		//	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($login)) throw new cuitException("服务器网络错误", 10511);
		$ret = array(
			'status' => 2000,
			'errorCode' => 2000,
			'cookie' => $cookie
		);
		return $ret;
	}

	/**
	 * 教务处登录检查(Jwgl控制器调用)
	 **/
	public static function loginCheck()
	{
		if (!isset(PARAM['cookie'])) throw new cuitException("参数缺失");
		$http = new EasyHttp();
		$i = 0;
		$login = null;
		while ($i < 3) {
			$login = $http->request('http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/home.action', array(
				'method' => 'GET',		//	GET/POST
				'timeout' => 5,			//	超时的秒数
				'redirection' => 0,		//	最大重定向次数
				'httpversion' => '1.1',	//	1.0/1.1
				'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.68 Safari/537.36 Edg/86.0.622.31',
				'blocking' => true,		//	是否阻塞
				'headers' => array(
					"Content-Type" => "application/x-www-form-urlencoded",
					"referer" => "http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/",
					'cookie' => PARAM['cookie']
				),	//	header信息
				'cookies' => array(),	//	关联数组形式的cookie信息
				'body' => null,
				'compress' => false,	//	是否压缩
				'decompress' => true,	//	是否自动解压缩结果
				'sslverify' => true,
				'stream' => false,
				'filename' => null		//	如果stream = true，则必须设定一个临时文件名
			));
			if (!is_object($login)) break;
			$i++;
		}
		if (is_object($login)) throw new cuitException("服务器网络错误", 10511);
		if (200 != $login['response']['code']) {
			$ret = array(
				'status' => 2005,
				'errorCode' => 2005,
				'errMsg' => 'Not Login'
			);
			echo json_encode($ret);
		} else {
			$ret = array(
				'status' => 2000,
				'errorCode' => 2000
			);
			echo json_encode($ret);
		}
	}

	// 成绩(Grade控制器调用)
	public static function getGradeFuncV2($cookie)
	{
		$http = new EasyHttp();
		$g = $http->request("http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/teach/grade/course/person!myHistory.action", array(
			'method' => 'POST',        //	GET/POST
			'timeout' => 5,            //	超时的秒数
			'redirection' => 0,        //	最大重定向次数
			'httpversion' => '1.1',    //	1.0/1.1
			'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
			'blocking' => true,        //	是否阻塞
			'headers' => array(
				'cookie' => $cookie,
				'referer' => 'http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/teach/grade/course/person!search.action?semesterId=302&projectType='
			),    //	header信息
			'cookies' => null,    //	关联数组形式的cookie信息
			// 'cookies' => $cookies,
			'body' => "template=report_latest_mode",
			'compress' => false,    //	是否压缩
			'decompress' => true,    //	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null        //	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($g)) return [10511, '服务器网络错误', $g];
		if (200 != $g['response']['code']) return [13401, '似乎未登录'];
		preg_match("/<td style=\"width:130px\">(\d+)<\/td>/", $g['body'], $id);
		if (!$g['body'] || false !== strpos($g['body'], "actionError")) throw new Exception("error");
		// file_put_contents(LOG_PATH . "/html/{$id[1]}.html", $g['body']);
		return self::getTableDataV2($g['body']);
	}

	/**
	 * 成绩html解析(Grade控制器间接调用)
	 * 
	 * @return array(
	 * 		array(
	 * 			0 => gradeGroup,
	 * 			1 => gradeGroupByTerm,
	 * 			2 => stdName,
	 * 			3 => totalScoreInfo
	 * 		) | num(err),
	 * 		errMsg
	 * )
	 */
	public static function getTableDataV2($html)
	{
		$dom = new DOMDocument();
		@$dom->loadHTML($html);
		$dom->normalize();
	
		$xpath = new DOMXPath($dom);

		// 学生姓名
		$stdName = $xpath->query('//*[@id="stuBasicInfo"]/tr[2]/td[4]');
		$stdName = $stdName->item(0)->textContent;

		// 三、 学习成绩卡（各门课程最终成绩）
		$items = $xpath->query('/html/body/table[3]/tr[not(@valign="bottom")]/td/..');
	
		// 总成绩
		$total = $items->item(0)->firstChild->firstChild->textContent;
		$total = str_replace(array(" ", " ", "\t", "\r\n", "\r", "\n"), "", $total);
	
		$ret = [];
		$ret2 = array();
		$cnt = 0;
		for ($i = 1; $i < $items->length; $i++) {
			$tds = $items->item($i)->childNodes;
			if (2 == $tds->length) {
				// 学期标题 -----> 第2019-2020学年 第1学期 (*****)
				$semester = $tds->item(0)->textContent;
	
				$semester_t = explode(" (", $semester);
				preg_match_all("/\d+\.?\d*/i", $semester_t[1], $semester_info);
	
				// 以学期为key
				if($cnt > 0){
					$ret2[$ret[$cnt - 1]['text']] = $ret[$cnt - 1]['data'];
					unset($ret[$cnt - 1]['text']);
				}
	
				$ret[$cnt++] = array(
					'name' => $semester_t[0],
					'text' => $semester,
					'total' => array(
						'learnTime' => $semester_info[0][0],
						'creditTotal' => $semester_info[0][1],
						'creditGet' => $semester_info[0][2],
						'point' => $semester_info[0][3]
					)
				);
	
			} else {
				// 条目
	
				$id = $tds->item(0)->textContent;							// 序号
				$name = $tds->item(2)->textContent	;						// 名称
				$learnTime = trim($tds->item(4)->textContent);					// 学时
				$learnCredit = trim($tds->item(6)->textContent);					// 学分
				$lessonGrade = trim($tds->item(8)->textContent);					// 平时成绩
				$examGrade = trim($tds->item(10)->textContent);					// 考试成绩
				$learnGrade = trim($tds->item(12)->textContent);					// 总评成绩
	
				$ret[$cnt - 1]['data'][$name] = array(
					'learnTime' => $learnTime,
					'learnCredit' => $learnCredit,
					'lessonGrade' => $lessonGrade,
					'examGrade' => $examGrade,
					'learnGrade' => $learnGrade,
				);
	
				if($tds->length > 20){
	
					$id = $tds->item(14)->textContent;							// 序号
					$name = $tds->item(16)->textContent	;						// 名称
					$learnTime = trim($tds->item(18)->textContent);					// 学时
					$learnCredit = trim($tds->item(20)->textContent);					// 学分
					$lessonGrade = trim($tds->item(22)->textContent);					// 平时成绩
					$examGrade = trim($tds->item(24)->textContent);					// 考试成绩
					$learnGrade = trim($tds->item(26)->textContent);					// 总评成绩
	
					$ret[$cnt - 1]['data'][$name] = array(
						'learnTime' => $learnTime,
						'learnCredit' => $learnCredit,
						'lessonGrade' => $lessonGrade,
						'examGrade' => $examGrade,
						'learnGrade' => $learnGrade,
					);
				}
			}
		}
	
		// 以学期为key
		$ret2[$ret[$cnt - 1]['text']] = $ret[$cnt - 1]['data'];
		unset($ret[$cnt - 1]['text']);
		
		return [[$ret, $ret2, $stdName, $total], ''];
	}


	/**
	 * (Grade控制器间接调用)
	 */
	public static function get_between($input, $start, $end)
	{
		$substr = substr(
			$input,
			strlen($start) + strpos($input, $start),
			(strlen($input) - strpos($input, $end)) * (-1)
		);
		return $substr;
	}

	/**
	 * (Task控制器调用)
	 */
	static function logout($cookie)
	{
		// http://jwgl.cuit.edu.cn/eams/logout.action
		$http = new EasyHttp();
		$logout = $http->request("http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/logout.action", array(
			'method' => 'POST',        //	GET/POST
			'timeout' => 5,            //	超时的秒数
			'redirection' => 0,        //	最大重定向次数
			'httpversion' => '1.1',    //	1.0/1.1
			'user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.0 Safari/537.36 Edg/84.0.521.0",
			'blocking' => true,        //	是否阻塞
			'headers' => array(
				'cookie' => $cookie,
				'referer' => 'http://jwgl-cuit-edu-cn.webvpn.cuit.edu.cn:8118/eams/home.action'
			),    //	header信息
			'cookies' => null,    //	关联数组形式的cookie信息
			// 'cookies' => $cookies,
			'body' => null,
			'compress' => false,    //	是否压缩
			'decompress' => true,    //	是否自动解压缩结果
			'sslverify' => true,
			'stream' => false,
			'filename' => null        //	如果stream = true，则必须设定一个临时文件名
		));
		if (is_object($logout)) return [10001, '服务器网络错误', $logout];
	}
}
