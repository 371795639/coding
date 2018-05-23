<?php
//前后台公用类
namespace Common\Controller;

use Think\Controller;

abstract class BaseController extends Controller {
	protected $web_config;
	protected $brand_config;

	public function _initialize() {
		header("Content-type: text/html; charset=utf-8");
		$this->web_config = M('config')->cache('web_config')->getField("conf_name,conf_value");
		$this->assign("web_config", $this->web_config);

		$arr = C('domain');
		if ($arr[$_SERVER['SERVER_NAME']]) {
			$this->brand_config = $arr[$_SERVER['SERVER_NAME']];
		} else {
			$this->brand_config = $arr['www.ipasswallet.biz'];
		}
		$this->assign($this->brand_config);
	}

	//删除COOKIE条件
	public function cleancon() {
		cookie(I("path.2"), null);
		$this->redirect("index");
	}

	/**
	 *
	 */
	public function showInfo($message, $url) {
		$this->assign("message", $message);
		$this->assign("url", $url);
		$this->display(C("TPL_SHOW_INFO"));
	}

	/**
	 *文件上传方法
	 *@param $exts 可以上传的文件类型
	 *@param $thumb 是否允许生成缩略图
	 *@return 返回上次是否成功的数组以及相关信息
	 */
	protected function uploadfile($exts = array('jpg', 'gif', 'png', 'jpeg'), $thumb = false) {
		$upload = new \Think\Upload(); // 实例化上传类
		$upload->maxSize = 8388608; // 设置附件上传大小
		$upload->exts = $exts; // 设置附件上传类型
		$upload->rootPath = './Public/Uploads/'; // 设置附件上传根目录
		$upload->savePath = ''; // 设置附件上传（子）目录
		// 上传文件
		$info = $upload->upload();
		//dump($info);
		if ($info) {
			if ($thumb) {
				$image = new \Think\Image();
				$thumb_arr = explode(",", $this->web_config['web_thumb']);
				foreach ($info as $k => $v) {
					$image->open('./Public/Uploads/' . $v['savepath'] . $v['savename']);
					// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
					$image->thumb($thumb_arr[0], $thumb_arr[1])->save('./Public/Uploads/' . $v['savepath'] . "thumb_" . $v['savename']);
				}
			}
			$result['status'] = true;
			$result['info'] = $info;
		} else {
			$result['status'] = false;
			$result['info'] = $upload->getError();
		}
		return $result;
	}

//验证码
	public function verify() {
		$verify = new \Think\Verify;
		$verify->imageH = 30;
		$verify->length = 4;
		$verify->fontSize = 14;
		//$verify->useCurve=false;
		$verify->useNoise = false;
		// 设置验证码字符为纯数字
		$verify->codeSet = '0123456789';
		$verify->entry();
	}

//对比验证码
	protected function checkCode() {
		$verify = new \Think\Verify;
		$verify->reset = false; //不重置
		$verify->check(I('verify')) || $this->error(L("invalid_verify"));
	}

//空方法
	public function _empty() {
		$this->display("Public:error404");
	}

	//$to 表示收件人地址 $subject 表示邮件标题 $body表示邮件正文
	protected function postmail($to, $subject, $body, $config = "") {
		if ($config == "") {
			$config = explode("|", $this->web_config['web_email_notify']); //C("EMAIL_CONFIG.NOTIFY");
		}

		//error_reporting(E_ALL);
		error_reporting(E_STRICT);
		date_default_timezone_set('Asia/Shanghai'); //设定时区东八区
		$mail = new \PHPMailer(); //new一个PHPMailer对象出来
		$body = eregi_replace("[\]", '', $body); //对邮件内容进行必要的过滤
		$mail->CharSet = "UTF-8"; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
		$mail->IsSMTP(); // 设定使用SMTP服务
		//$mail->SMTPDebug  = 1;                     // 启用SMTP调试功能
		// 1 = errors and messages
		// 2 = messages only
		$mail->SMTPAuth = true; // 启用 SMTP 验证功能
		$mail->SMTPSecure = "ssl"; // 安全协议
		$mail->Host = $config[0]; // SMTP 服务器
		$mail->Port = 465; // SMTP服务器的端口号

		$mail->Username = $config[1]; // SMTP服务器用户名
		$mail->Password = $config[2]; // SMTP服务器密码
		$mail->SetFrom($config[1], "ipasswallet Messager");
		$mail->AddReplyTo($config[1], "iPasswallet Messager");

		$mail->Subject = $subject;
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional, comment out and test
		$mail->MsgHTML($body);
		$address = $to;
		$mail->AddAddress($address, '');
		//$mail->AddAttachment("images/phpmailer.gif");      // attachment
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
		try {
			if (!$mail->Send()) {
				//echo 'Mailer Error: ' . $mail->ErrorInfo;
				return false;
			} else {
				//echo "Message sent!恭喜，邮件发送成功！";
				return true;
			}} catch (\Exception $e) {
			return false;
		}
	}

/**
 *根据ISO国家代码，获取所有的州信息
 *@param string country_name ISO国家代码
 *@param int  可选2，3 表示ISO国际代码的位数
 *@return  array 返回州数组
 **/
	protected function getState($country_name, $iso) {
		$oop = D("CountriesView");
		if ($iso == 3) {
			$con["countries_iso_code_3"] = $country_name;
		} else {
			$con["countries_iso_code_2"] = $country_name;
		}

		$list = $oop->where($con)->select();
		//echo $oop->_sql();
		/*
		<option value="">--Please choose--</option>
		<option value="Not applicable">Not Applicable</option>
		*/
		if (!$list) {
			$list[0]['zone_code'] = "Not Applicable";
			$list[0]['zone_name'] = "Not Applicable";
		}
		return $list;
	}

/**
 *备份数据库操作
 */
	protected function backup($include_tables = array(), $exclude_tables = array("ips_innotice", "ips_news", "ips_areas", "ips_zones")) {
		$dumpSettings = array(
			'include-tables' => $include_tables,
			'exclude-tables' => $exclude_tables,
		);
		try {
			$dir = "./Public/dbbackup/" . date("Y-m-d");
			if (!is_dir($dir)) {
				mkdir($dir, 0777, true) or die('DIR cannot create');
			}

			$dump = new \Ifsnop\Mysqldump\Mysqldump("mysql:host=" . C("DB_HOST") . ";dbname=" . C("DB_NAME"), C("DB_USER"), C("DB_PWD"), $dumpSettings);
			$dump->start($dir . "/" . time() . ".sql");
			return true;
		} catch (\Exception $e) {
			//echo 'mysqldump-php error: ' . $e->getMessage();
			return false;
		}
	}

	//下载文件
	protected function downloadCsv($file_name) {
		$file = "./Public/Csv/" . $file_name;
		//echo unlink($file);exit;
		if (!file_exists($file)) {
			//检查文件是否存在
			$this->error(L("file_not_found"));
		} else {
			$file_handler = fopen($file, "r"); // 打开文件
			// 输入文件标签
			Header("Content-type: application/octet-stream");
			//header("Content-type:text/csv");
			Header("Accept-Ranges: bytes");
			Header("Accept-Length: " . filesize($file));
			Header("Content-Disposition: attachment; filename=" . $file_name);
			// 输出文件内容
			$csv_data = fread($file_handler, filesize($file));
			echo $csv_data;
			fclose($file_handler);
			//unlink($file);
			exit();
		}
	}

	/**
	 *生成CSV订单,使用非TP的方法游标
	 *@param string $sql 执行SQL语句
	 *@param array $arr 数组，键名为表字段名|操作方法名，键值为CSV文件表头，对应相应的字段 如 $arr=array("order_id"=>"订单ID")，表示字段为 order_id
	 *@param string $csv_name 生成的CSV字段名
	 */
	protected function createCsv($sql, $arr, $csv_name) {
		//echo $sql;
		try {
			$file_url = "./Public/Csv/" . $csv_name . ".csv";
			$file = fopen($file_url, "w");
			fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM
			$csv_title = implode(",", $arr) . "\n";
			//写CSV表头
			fwrite($file, $csv_title);
			$stmt = fetchOnebyone($sql);
			while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
				$csv_data = '';
				//写CSV内容
				foreach ($arr as $key => $value) {
					$key_arr = explode("|", $key);
					//dump($key_arr);
					if ($key_arr[1]) {
						$v = $key_arr[1]($row[$key_arr[0]]);
					} else {
						$v = $row[$key_arr[0]];
					}

					$v = '"' . $v . '"';
					if ($csv_data) {
						$csv_data .= ',' . $v;
					} else {
						$csv_data = $v;
					}

				}
				$csv_data .= "\n";
				fwrite($file, $csv_data);
			}
			fclose($file);
		} catch (\Exception $e) {
			dump($e->getMessage());
		}
	}

}
