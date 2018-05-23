<?php
//自定义数组文件
$file_arr = "./Application/Common/Common/arrayList.php";
if (is_readable($file_arr) == true) {
	require $file_arr;
}
//自定义函数文件
$file_local = "./Application/Common/Common/local.php";
if (is_readable($file_local) == true) {
	require $file_local;
}

//查看数组中，是否存在键值为空的项
function isExsitNullArr($data = array()) {
	foreach ($data as $value) {
		if ($value == "") {
			return true;
		}
	}
	return false;
}

/**
 *生成对账单,同时自动更新账户的金额
 *@param $user_id  用户ID
 *@param $type 对账类型，从1到10
 *@param $amount 对账金额
 *@param $remark  对账原因说明
 *@param $fee  本次对账过程的费用，默认为0
 *@return  返回成功或者失败
 */
function createStatement($user_id, $type, $amount, $remark, $fee = 0) {
	//如果变动金额为0，直接返回
	if ($amount == 0) {
		return true;
	}

	$oop = M("users");
	//dump($list);
	switch ($type) {
	case 1: //充值
	case 3: //收款
	case 4: //转账退款
	case 7: //对账转入
		$st_data["st_flag"] = 1;
		break;
	case 2: //转账
	case 5: //收款退款
	case 6: //提现
	case 8: //对账转出
		$st_data["st_flag"] = 0;
		break;
	}
	$con['user_id'] = $user_id;
	//查询的时候，防止其他查询
	$list = $oop->lock(true)->where($con)->find();
	//用户账户金额变动
	if ($st_data['st_flag']) {
		$result1 = $oop->where($con)->setInc("user_balance_usd", $amount);
		$st_data['st_balance'] = $list['user_balance_usd'] + $amount;
	} else {
		$result1 = $oop->where($con)->setDec("user_balance_usd", $amount);
		$st_data['st_balance'] = $list['user_balance_usd'] - $amount;
	}

	$st_data['st_user_id'] = $user_id;
	$st_data['st_name'] = $type;
	$st_data['st_fee'] = $fee;
	$st_data['st_amount'] = $amount;
	$st_data['st_reason'] = $remark;
	//dump($st_data);exit;
	//对账单
	$result2 = M("statement")->add($st_data);

	return $list && $result1 && $result2;
}

/**
 * 多语言对应
 */
function getLangName($name = "") {
	$arr = [
		"en-us" => "English",
		"zh-cn" => "中文",
	];
	if ($name) {
		return $arr[$name];
	} else {
		return $arr;
	}
}

/**
 * 随机生成一个美国的IP地址
 */
function get_rand_ip() {
	$arr_1 = array("48", "49", "52", "54", "55", "56", "61", "62", "63", "64", "65", "66", "67", "68", "128", "129", "130",
		"131", "132", "136", "137", "138", "140", "141", "144", "146", "147", "214", "216", "217", "224");
	$randarr = mt_rand(0, count($arr_1));
	$ip1id = $arr_1[$randarr];
	$ip2id = round(rand(600000, 2550000) / 10000);
	$ip3id = round(rand(600000, 2550000) / 10000);
	$ip4id = round(rand(600000, 2550000) / 10000);
	return $ip1id . "." . $ip2id . "." . $ip3id . "." . $ip4id;
}

function GetMonth($sign = "1") {
	//得到系统的年月
	$tmp_date = date("Ym");
	//切割出年份
	$tmp_year = substr($tmp_date, 0, 4);
	//切割出月份
	$tmp_mon = substr($tmp_date, 4, 2);
	$tmp_nextmonth = mktime(0, 0, 0, $tmp_mon + 1, 1, $tmp_year);
	$tmp_forwardmonth = mktime(0, 0, 0, $tmp_mon - 1, 1, $tmp_year);
	if ($sign == 0) {
		//得到当前月的下一个月
		return $fm_next_month = date("Y-m", $tmp_nextmonth);
	} else {
		//得到当前月的上一个月
		return $fm_forward_month = date("Y-m", $tmp_forwardmonth);
	}
}

/**
前三次字符加***号
 */
function maskWithStar($str, $no = 3) {
	$result = substr($str, $no);
	return "***" . $result;
}

/**
 * 获取数据库查询结果的游标
 */
function fetchOnebyone($sql) {
	try {
		$pdo = new \PDO("mysql:dbname=" . C("DB_NAME") . ";host=" . C("DB_HOST"), C("DB_USER"), C("DB_PWD"));
	} catch (\PDOException $e) {
		exit('DB connection error.' . $e->getMessage());
	}

	//使用query方式执行SELECT语句，建议使用prepare()和execute()形式执行语句
	$pdo->exec("set time_zone='+8:00'");
	$pdo->exec("set names 'utf8';");
	$pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
	//$stmt = $pdo->query($sql);
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	return $stmt;
}

/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $day1
 * @param string $day2
 * @return number
 */
function diffBetweenTwoDays($day1, $day2) {
	$second1 = strtotime($day1);
	$second2 = strtotime($day2);

	if ($second1 < $second2) {
		$tmp = $second2;
		$second2 = $second1;
		$second1 = $tmp;
	}
	return floor(($second1 - $second2) / 86400);
}

//根据提供的2位国家缩写，返回其他信息
function getCountryArr($iso2) {
	$countries = new \Countries\Countries();
	$country_arr = $countries->getCountry($iso2);
	//dump($country_arr);
	return $country_arr;
}

/**
获取字符串内容某两个字符串之间的内容
 */
function GetBetween($content, $start, $end) {
	$r = explode($start, $content);
	if (isset($r[1])) {
		$r = explode($end, $r[1]);
		return $r[0];
	}
	return '';
}

/**
 * 根据标题属性来显示标题
 * @param $title 显示的标题内容
 * @param $property 属性的序列化字符串
 * @return 返回根据属性拼接好的标题字符串
 */
function displayTitle($title, $property) {
	$arr = unserialize($property);
	$str = $title;
	if (in_array(1, $arr)) {
		$str = "<b>" . $str . "</b>";
	}
	if (in_array(2, $arr)) {
		$str = "<font color=red>" . $str . "</font>";
	}
	if (in_array(3, $arr)) {
		$str = "<u>" . $str . "</u>";
	}
	if (in_array(4, $arr)) {
		$str = "<del>" . $str . "</del>";
	}
	if (in_array(5, $arr)) {
		$str = "<i>" . $str . "</i>";
	}
	return $str;
}

/**
 * 显示分隔符
 * @param $num int 显示分隔符的数量
 * @param $space string 分隔符样式
 */
function levelShow($num = 0, $space = '|--&nbsp;') {
	$str = "";
	for ($i = 0; $i < $num; $i++) {
		$str .= $space;
	}
	return $str . "|--&nbsp;";
}

/**
 * 返回分类列表
 */
function getCategoryList() {
	$list = M("category")->cache('category')->order("cat_id desc")->select();
	$options = [
		"primary_key" => "cat_id",
		"parent_key" => "cat_parent",
	];
	$obj = new \Common\Common\Mytree($options);
	$arr = $obj->makeTreeForHtml($list);
	return $arr;
}

/**
 * 返回路径文件列表
 * @param string 文件目录
 * @param string 只选择目录中该文件后缀的
 */
function getFileList($directory, $suffix = "") {
	$files = array();
	if ($suffix != "") {
		$len = -(int) strlen($suffix);
	}

	try {
		$dir = new DirectoryIterator($directory);
	} catch (Exception $e) {
		throw new Exception($directory . ' is not readable');
	}
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		$file_name = $file->getFileName();
		if ($suffix != "") {
			if (substr($file_name, $len) == $suffix) {
				$files[] = $file_name;
			}

		} else {
			$files[] = $file_name;
		}
	}
	return $files;
}

/**
 *返回所有的国家信息列表，同时缓存查询到country_list
 *@param 无输入参数
 *@return array 返回国家数组
 */
function getCountryList() {
	$oop = M("countries");
	$list = $oop->field(true)->cache('country_list')->select();
	return $list;
}

/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * return void
 */
function hook($hook, $params = array()) {
	\Think\Hook::listen($hook, $params);
}

/**
 * 创建UUID
 */
function create_guid() {
	$charid = strtoupper(md5(uniqid(mt_rand(), true)));
	$hyphen = chr(45); // "-"
	$uuid = substr($charid, 0, 8) . $hyphen
	. substr($charid, 8, 4) . $hyphen
	. substr($charid, 12, 4) . $hyphen
	. substr($charid, 16, 4) . $hyphen
	. substr($charid, 20, 12);
	return $uuid;
}

/**
 * 常用货币列表
 */
function getCurrency($id = "") {
	$arr = [
		"CNY" => "人民币",
		"USD" => "美元",
		"JPY" => "日元",
		"EUR" => "欧元",
		"GBP" => "英镑",
		"DEM" => "德国马克",
		"CHF" => "瑞士法郎",
		"FRF" => "法国法郎",
		"CAD" => "加拿大元",
		"AUD" => "澳大利亚元",
		"HKD" => "港币",
		"ATS" => "奥地利先令",
		"FIM" => "芬兰马克",
		"BEF" => "比利时法郎",
		"IEP" => "爱尔兰镑",
		"ITL" => "意大利里拉",
		"LUF" => "卢森堡法郎",
		"NLG" => "荷兰盾",
		"PTE" => "葡萄牙埃斯库多",
		"ESP" => "西班牙比塞塔",
		"IDR" => "印尼盾",
		"MYR" => "马来西亚林吉特",
		"NZD" => "新西兰元",
		"PHP" => "菲律宾比索",
		"SUR" => "俄罗斯卢布",
		"SGD" => "新加坡元",
		"KRW" => "韩国元",
		"THB" => "泰铢",
	];
	if ($id) {
		return $arr[$id];
	} else {
		return $arr;
	}

}

/**
 *返回某个国家的州列表
 *@param int $country_id 国家编号
 *@return array 返回州数组
 */
function getStateList($country_id) {
	$oop = M("zones");
	$con['zone_country_id'] = $country_id;
	$list = $oop->field(true)->where($con)->select();
	return $list;
}

function cardhidden($cardNumber, $numToShow = 4) {
	$char = "x";
	$cardShow = substr($cardNumber, -$numToShow);
	for ($i = 0; $i < strlen($cardNumber) - $numToShow; $i++) {
		$char .= "x";
	}
	return $char . $cardShow;
}

//随机密码生成函数，参数1个，密码的位数
function makePass($length) {
	$possible = "0123456789" .
		"abcdefghijklmnopqrstuvwxyz" .
		"ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$str = "";
	while (strlen($str) < $length) {
		$str .= substr($possible, (rand() % strlen($possible)), 1);
	}
	return ($str);
}

//通用查询方法2
//$mist_arr =>模糊查询的数组key=>value
//$accute_arr =>精确查询数据key=>value
//需要计入cookie的数组key
function searchCon($mist_arr = array(), $accute_arr = array(), $cookie_arr = array()) {
	$con = array();
//如果cookie_arr不为空
	if ($cookie_arr) {
		foreach ($cookie_arr as $key => $value) {
			if (cookie($value) != "") {
				$con[$value] = cookie($value);
			}

		}
	}

/*-----------以上是cookie的查询字段------------*/

//模糊数组
	if ($mist_arr) {

		foreach ($mist_arr as $key => $value) {
			if ($value != "") {

				if (in_array($key, $cookie_arr)) {
					cookie($key, $value);
				}

				$con[$key] = array("like", "%" . $value . "%");
			}
		}
	}

/*-----------以上是模糊查询字段------------*/

//精确数组
	if ($accute_arr) {
		foreach ($accute_arr as $key => $value) {
			if ($value != "") {

				if (in_array($key, $cookie_arr)) {
					cookie($key, $value);
				}

				$con[$key] = $value;
			}
		}
	}

/*-----------以上是精确查询字段------------*/

	return $con;

}

//获得实体信息
function getObjectInfo($id, $object, $field = "") {
	$oop = M($object);
	$list = $oop->find($id);
	if ($field == "") {
		return $list;
	} else {
		return $list[$field];
	}

}

function getObjectList($object, $arr = "1", $sort = "", $num = "") {
	$oop = D($object);
	$list = $oop->field(true)->where($arr)->order($sort)->limit($num)->select();
	return $list;
}

/*
 *获取表及视图列表加强版, 参数不定，最少3个，最多无数个，但是参数个数必须是奇数，否则无效
 *其中前三个，分别是表，排序，列表数
 */
function getObjectPro() {
	$num = func_num_args();
	if ($num % 2 == 0 || $num < 3) {
		return false;
	}

	$arr = func_get_args();
	for ($i = 3; $i < $num; $i += 2) {
		$con[$arr[$i]] = $arr[$i + 1];
	}
	if (strstr($arr[0], "View")) {
		$oop = D($arr[0]);
	} else {
		$oop = M($arr[0]);
	}

	$list = $oop->field(true)->where($con)->order($arr[1])->limit($arr[2])->select();
	return $list;
}

//剪裁字符串
function cutStr($sourcestr, $cutlength) {
	$returnstr = '';
	$i = 0;
	$n = 0;
	$str_length = strlen($sourcestr); //字符串的字节数
	while (($n < $cutlength) and ($i <= $str_length)) {
		$temp_str = substr($sourcestr, $i, 1);
		$ascnum = Ord($temp_str); //得到字符串中第$i位字符的ascii码
		if ($ascnum >= 224) //如果ASCII位高与224，
		{
			$returnstr = $returnstr . substr($sourcestr, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
			$i = $i + 3; //实际Byte计为3
			$n++; //字串长度计1
		} elseif ($ascnum >= 192) //如果ASCII位高与192，
		{
			$returnstr = $returnstr . substr($sourcestr, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
			$i = $i + 2; //实际Byte计为2
			$n++; //字串长度计1
		} elseif ($ascnum >= 65 && $ascnum <= 90) //如果是大写字母，
		{
			$returnstr = $returnstr . substr($sourcestr, $i, 1);
			$i = $i + 1; //实际的Byte数仍计1个
			$n++; //但考虑整体美观，大写字母计成一个高位字符
		} else //其他情况下，包括小写字母和半角标点符号，
		{
			$returnstr = $returnstr . substr($sourcestr, $i, 1);
			$i = $i + 1; //实际的Byte数计1个
			$n = $n + 0.5; //小写字母和半角标点等与半个高位字符宽...
		}
	}
	if ($str_length > $i) {
		$returnstr = $returnstr . "..."; //超过长度时在尾处加上省略号
	}
	return $returnstr;
}

//分页方法
function pageInfo($oop, $sort = "", $con = "1=1", $num = 35) {
	$count = $oop->where($con)->count(); // 查询满足要求的总记录数
	$Page = new \Common\Common\Mypage($count, $num, "uk-clearfix uk-margin-top", "uk-pagination"); // 实例化分页类 传入总记录数和每页显示的记录数
	$data['show'] = $Page->show(); // 分页显示输出 // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
	$data['list'] = $oop->field(true)->where($con)->order($sort)->limit($Page->first_row . ',' . $Page->list_rows)->select();
	//echo $oop->GetLastSql();
	$data['total_num'] = $Page->total_num; //总记录数
	$data['page_num'] = $Page->page_num; //总分页
	return $data;
}

function displayIcon($status = 1) {

	if ($status) {
		return "<font color=green><b>√</b></font>";
	} else {
		return "<font color=red><b>×</b></font>";
	}

}

function getAreaInfo($parent_id, $json = true) {
	$oop = M("areas");
	$con['parent_id'] = $parent_id;
	$list = $oop->where($con)->select();
	$arr[0]['id'] = "";
	$arr[0]['text'] = "--Please select--";

	foreach ($list as $key => $value) {
		$arr[$key + 1]['id'] = $value['area_id'];
		$arr[$key + 1]['text'] = $value['area_name'];
	}

	if ($json) {
		return json_encode($arr);
	} else {
		return $list;
	}

}

//验证身份证
function isIdcard($id) {
	$id = strtoupper($id);
	$regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
	$arr_split = array();
	if (!preg_match($regx, $id)) {
		return false;
	}
	if (15 == strlen($id)) //检查15位
	{
		$regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

		@preg_match($regx, $id, $arr_split);
		//检查生日日期是否正确
		$dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
		if (!strtotime($dtm_birth)) {
			return false;
		} else {
			return true;
		}
	} else //检查18位
	{
		$regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
		@preg_match($regx, $id, $arr_split);
		$dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
		if (!strtotime($dtm_birth)) //检查生日日期是否正确
		{
			return false;
		} else {
			//检验18位身份证的校验码是否正确。
			//校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
			$arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
			$arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
			$sign = 0;
			for ($i = 0; $i < 17; $i++) {
				$b = (int) $id{$i};
				$w = $arr_int[$i];
				$sign += $b * $w;
			}
			$n = $sign % 11;
			$val_num = $arr_ch[$n];
			if ($val_num != substr($id, 17, 1)) {
				return false;
			} else {
				return true;
			}
		}
	}

}

/**
+----------------------------------------------------------
 * 功能：计算文件大小
+----------------------------------------------------------
 * @param int $bytes
+----------------------------------------------------------
 * @return string 转换后的字符串
+----------------------------------------------------------
 */
function byteFormat($bytes) {
	$sizetext = array(" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
	return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $sizetext[$i];
}

// 无需递归生成树
// 数组格式
// $items = array(
//     1 => array('id' => 1, 'pid' => 0, 'name' => '安徽省'),
//     2 => array('id' => 2, 'pid' => 0, 'name' => '浙江省'),
//     3 => array('id' => 3, 'pid' => 1, 'name' => '合肥市'),
//     4 => array('id' => 4, 'pid' => 3, 'name' => '长丰县'),
//     5 => array('id' => 5, 'pid' => 1, 'name' => '安庆市'),
// );
function generateTree($items) {
	$tree = array();
	foreach ($items as $item) {
		if (isset($items[$item['pid']])) {
			$items[$item['pid']]['children'][] = &$items[$item['id']];
		} else {
			$tree[] = &$items[$item['id']];
		}
	}
	return $tree;
}

//信用卡过期年月函数
function getCardExpiryDate($param = "m") {
	if ($param == "m") {
		$month_arr = array(
			"01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12",
		);
		return $month_arr;
	} else {
		$curr_year = date('y');
		for ($i = 0; $i < 10; $i++) {
			$year_arr[$i + $curr_year] = "20" . $curr_year + $i;
		}
		return $year_arr;
	}
}
