<?php
//自动处理方法
namespace Home\Controller;

use Think\Controller;

class AutohandleController extends CommonController {
	public function index() {
		$oop = M("autoperform");
		$con['isactive'] = 1;
		I('name') && $con['name'] = I('name');

		$list = $oop->where($con)->select();
		!$list && exit("unknow process method！");
		//启动多线程,这样就不用等待所有方法执行完成了
		$asyn = new \boss420\common\AsynHandle();
		echo "start<hr>";
		foreach ($list as $value) {
			$process_url = U('Autohandle/' . $value['name'] . '@' . $_SERVER['SERVER_NAME']);
			echo $asyn->Request($process_url, 1);
			dump($process_url);
		}

		exit("<hr>complete");
	}

	/**
	 * 检测该功能是否可用
	 */
	private function checkStatus() {
		$oop = M("autoperform");
		$con['isactive'] = 1;
		$con["name"] = ACTION_NAME;
		$list = $oop->where($con)->find();
		//echo $oop->_sql();
		if (!$list) {
			exit("not allowed or not found");
		}
		return $list;
	}

	/**
	 * 富汇通交易查询功能
	 */
	public function autoQueryFht() {

	}

	/**
	 * 预授权自动捕捉
	 */
	public function autoCapture() {
		$auto_result = $this->checkStatus();
		//找到所有预授权的交易
		$oop = M("orders");
		$con['order_status'] = 3; //已授权
		$con['order_islive'] = 1; //真实的交易
		$con['order_isview'] = 1; //可见
		$con['_string'] = "TIMESTAMPDIFF( DAY , `order_time`, now( )) >15"; //超过15天
		$list = $oop->where($con)->field("order_id,order_status,ips_id,order_channel_name,order_channel_key1,order_channel_key2,order_channel_key3,order_channel_key4")->select();
		$success_cnt = 0;
		$error_cnt = 0;
		//dump($list);

		foreach ($list as $value) {
			$result = A("Thirdpay/" . $value['order_channel_name'])->capture($value);
			if ($result) {
				$success_cnt++;
			} else {
				$error_cnt++;
			}
		}

		//parent::postmail("383542899@qq.com","test","test");
		$subject = $this->web_config['web_domain'] . "-自动预授权捕捉-(" . date('Y-m-d H:i:s') . ")";
		$body = "预授权捕捉完成！成功：" . $success_cnt . "，失败：" . $error_cnt;
		if ($auto_result['email'] != 0) {
			$arr_to = explode(",", $auto_result['email']);
			foreach ($arr_to as $v_email) {
				//dump($v_email);
				parent::postmail($v_email, $subject, $body);
			}
		}
		dump($body);

	}

	/**
	 *自动退款操作
	 */
	public function autorefund() {
		$auto_result = $this->checkStatus();
		//找到所有等待退款的交易
		$oop = M("orders");

		$con['order_status'] = 7;
		I("order_id") && $con['order_id'] = I("order_id");
		$list = $oop->where($con)->limit(100)->select();

		$oh_oop = M("orderhistory");
		foreach ($list as $key => $value) {
			$oh_con['oh_oid'] = $value["order_id"];
			$oh_list = $oh_oop->lock(true)->where($oh_con)->order("oh_id desc")->find();

			//dump($value);

			if ($oh_list['oh_status'] != 7) {
				continue;
			}
			//启动事务
			$oh_oop->startTrans();

			if ($value['order_channel_name']) {
				$result[$key]['status'] = A('Thirdpay/' . $value['order_channel_name'])->refund($value, $oh_list['oh_gateway_amount']);
				//dump($value['cn_name']);
			} else {
				$result[$key]['status'] = 0;
			}
			$result[$key]['channel'] = $value['order_channel_name'];
			$result[$key]['refund_amount'] = $oh_list['oh_amount'];
			$result[$key]['refund_gateway_amount'] = $oh_list['oh_gateway_amount'];
			$result[$key]['order_id'] = $value['order_id'];
			$result[$key]['order_time'] = $value['order_time'];
			$result[$key]['refund_time'] = $oh_list['oh_time'];
			$result[$key]['mid'] = $value['order_mid'];
			$result[$key]['site_id'] = $value['order_siteid'];

			if ($oh_list['oh_satus'] == 7 && $result[$key]['status']) {
				$oh_oop->commit();
			} else {
				$oh_oop->rollback();
			}

		}
		//dump($auto_result);
		//发送邮件通知
		$subject = $this->web_config['web_domain'] . "-自动退款-(" . date('Y-m-d H:i:s') . ")";
		$body = $result . "<hr>";
		if ($auto_result['email'] != 0) {
			$arr_to = explode(",", $auto_result['email']);
			foreach ($arr_to as $v_email) {
				echo $v_email;
				parent::postmail($v_email, $subject, $body);
			}
		}
		dump($body);
	}

	/*
			*自动统计每天的交易的状态和数量
	*/
	public function autostatistics() {
		$this->checkStatus();

		$last_day = date("Y-m-d", strtotime("-1 day"));
		$oop = M("statistics");
		$his_oop = M("orderhistory");

		$con1['oh_time'] = array("like", "%" . $last_day . "%");
		$con11['oh_status'] = 1;
		//total num
		$data['total_num'] = $his_oop->where($con1)->where($con11)->count();
		$data['total_num'] = $data['total_num'] ? $data['total_num'] : 0;
		//total amount
		$data['total_amount'] = $his_oop->where($con1)->where($con11)->sum("oh_amount");
		$data['total_amount'] = $data['total_amount'] ? $data['total_amount'] : 0;
		//success_amount
		$con1['oh_status'] = array("in", "2,4");
		$data['success_amount'] = $his_oop->where($con1)->sum("oh_amount");
		$data["success_amount"] = $data["success_amount"] ? $data["success_amount"] : 0;
		//success num
		$data['success_num'] = $his_oop->where($con1)->count();
		$data['success_num'] = $data['success_num'] ? $data['success_num'] : 0;
		//approval_rate
		if ($data["total_num"]) {
			$data["approval_rate"] = $data['success_num'] / $data["total_num"];
		} else {
			$data["approval_rate"] = 0;
		}
		$con2['oh_time'] = array("like", "%" . $last_day . "%");
		$his_list = $his_oop->field("oh_status,count(*) as total")->where($con2)->group("oh_status")->select();
		//echo $his_oop->_sql();
		//dump($his_list);
		foreach ($his_list as $value) {
			$data['status_num'] .= order_status($value['oh_status']) . "(" . $value['total'] . ")&nbsp;&nbsp;";
		}

		$data['ss_date'] = $last_day;
		//dump($data);exit;
		try {
			$oop->add($data);
		} catch (\Exception $e) {
			dump($e->getmessage());
		}
		exit("over");
		//echo $data['status_num'];

	}

	/**
	 *自动备份数据库的功能
	 */
	public function autobackup() {
		$auto_result = $this->checkStatus();
		$result = parent::backup();
		//发送邮件通知
		$subject = $this->web_config['web_domain'] . "-自动备份-(" . date('Y-m-d H:i:s') . ")";
		$body = "备份结果：" . $result;
		if ($auto_result['email'] != 0) {
			$arr_to = explode(",", $auto_result['email']);
			foreach ($arr_to as $v_email) {
				parent::postmail($v_email, $subject, $body);
			}
		}
		exit($body);
	}

	/**
	 *自动更新汇率
	 */
	public function autoroe() {
		$this->checkStatus();
		$result = parent::updateConversion();
		dump($result);
	}

	/**
	 * 自动释放保证金
	 */
	public function autoreleasereserve() {
		$auto_result = $this->checkStatus();

		$now_date = date("Y-m-d H:i:s");
		$oop = M("rolling");
		$con["rl_status"] = 0;
		$con["_string"] = "unix_timestamp('" . $now_date . "') >= unix_timestamp(rl_enddate)";
		$list = $oop->where($con)->select();

		$count = 0;
		//处理金额变动和对账单变化
		foreach ($list as $k => $v) {
			//启动事务
			$oop->startTrans();
			//调用对账单函数
			$result1 = createStatement($v["rl_mid"], 5, $v["rl_amount"], "Release reserve...Order#:" . $v["rl_oid"]);
			//修改保证金为释放状态
			$v['rl_status'] = 1;
			$v['rl_realenddate'] = $now_date;
			$result2 = $oop->save($v);
			if ($result1 && $result2) {
				$oop->commit();
				$count++;
			} else {
				$oop->rollback();
			}
		}

		//发送邮件通知
		$subject = $this->web_config['web_domain'] . "-自动释放保证金-(" . date('Y-m-d H:i:s') . ")";
		$body = "保证金释放完毕！总数量：" . count($list) . ",释放成功数量：" . $count;
		if ($auto_result['email'] != 0) {
			$arr_to = explode(",", $auto_result['email']);
			foreach ($arr_to as $v_email) {
				parent::postmail($v_email, $subject, $body);
			}
		}

		exit($body);

	}

	/**
	 * 自动执行周期扣款
	 */
	public function autorebill() {
		$auto_result = $this->checkStatus();
		$oop = M("cronrebill");
		$con['cr_status'] = 1;
		$con['_string'] = "(`cr_count` < `cr_total`) or (`cr_total` = 0)";
		$con['cr_nextdate'] = date("Y-m-d");
		$list = $oop->where($con)->select();
		//dump($list);
		if (!$list) {
			exit("无周期扣款计划执行。");
		}

		require_once "./Application/Common/Common/Directpaykit.class.php";
		//echo $oop->_sql();
		$order_oop = M("orders");
		$count = 0;
		foreach ($list as $key => $value) {
			//首先，要将这些周期扣款计划的下一个扣款日期变更
			$data['cr_nextdate'] = date("Y-m-d", strtotime("+ " . $value['cr_rebill_period'] . " day"));
			$result1 = $oop->where($value)->save($data);
			//dump($value);
			$order_list = $order_oop->where(array("order_id" => $value['cr_connect_oid']))->find();
			//dump($order_list);

			$oop = new \Directpaykit($order_list['mh_id'], $order_list['site_id'],
				$order_list['mh_api_key'], $this->web_config['web_domain']);
			$arr = array(
				"pid" => $order_list['order_id'],
				"oid" => "auto_rebill_" . date('YmdHis'),
				"order_amount" => $value['cr_amount'],
				"order_currency" => $value['cr_currency'],
			);
			//dump($arr);
			$result = $oop->sendRebill($arr["pid"], $arr["oid"], $arr["order_amount"], $arr["order_currency"]);
			//dump($result);
			$count++;
		}

		//发送邮件通知
		$subject = $this->web_config['web_domain'] . "-自动计划周期交易-(" . date('Y-m-d H:i:s') . ")";
		$body = "周期计划执行完毕,总数：" . $count;
		if ($auto_result['email'] != 0) {
			$arr_to = explode(",", $auto_result['email']);
			foreach ($arr_to as $v_email) {
				parent::postmail($v_email, $subject, $body);
			}
		}

		exit($body);
	}

	/**
	 *自动取消，pending的交易两天后如果不处理，会自动取消
	 */
	public function autovoid() {
		$auto_reuslt = $this->checkStatus();

		$oop = M("orders");
		$con['order_status'] = 1;
		$con['_string'] = "TIMESTAMPDIFF( DAY , `order_time`, now( )) >2"; //超过2天的
		$list = $oop->where($con)->select();
		//echo $oop->_sql();
		$i = 0;
		foreach ($list as $value) {
			$order = new \Common\Common\OrderProcess($value["order_id"]);
			$result = $order->toVoid();
			if ($result) {
				$i++;
			}

		}

		//发送邮件通知
		$subject = $this->web_config['web_domain'] . "-自动取消交易-(" . date('Y-m-d H:i:s') . ")";
		$body = "成功取消交易数：" . $i;
		if ($auto_result['email'] != 0) {
			$arr_to = explode(",", $auto_result['email']);
			foreach ($arr_to as $v_email) {
				parent::postmail($v_email, $subject, $body);
			}
		}

		exit($body);
	}

	/**
	 *异步通知(每5分钟通知一次)
	 */
	public function autonotify() {
		$auto_result = $this->checkStatus();
		/*
					"order_status": 6,
					"oid": "1",
					"pid": 2879,
					"order_amount": "1.00",
					"order_currency": "USD",
					"hash_info": "be17b8c545f13cddca1dd7ed05defb93",
					"syn_url": "http%3A%2F%2Fwww.baidu.com"
		*/
		$oop = M("orders");
		$con['order_status'] = array("neq", 1); //非pending的
		$con['order_asyn_status'] = 0; //没有收到通知的
		$con['order_asyn_count'] = array("lt", 3); //通知次数小于3
		I("order_id") && $con['order_id'] = I("order_id"); //如果有订单号，则只针对此订单号通知
		$list = $oop->field("order_status,mh_oid,order_id,order_amount,order_currency,order_syn_url,order_asyn_url,order_signature_info,order_asyn_count")->where($con)->limit(2000)->select(); //至多查询500条，多了为内存溢出
		//echo $oop->_sql();
		//dump($list);
		$i = 0; //成功的个数
		$j = 0; //失败的个数
		$handler = new \boss420\common\AsynHandle();
		foreach ($list as $value) {
			$post_arr = array(
				"order_status" => $value["order_status"],
				"oid" => $value["mh_oid"],
				"pid" => $value["order_id"],
				"order_amount" => $value["order_amount"],
				"order_currency" => $value["order_currency"],
				"syn_url" => urlencode($value["order_syn_url"]),
				"hash_info" => $value["order_signature_info"],
			);
			//dump($post_arr);
			$result = $handler->Get($value["order_asyn_url"], 1, $cookie = array(), $post_arr, $timeout = 30);
			//dump($result);

			$value["order_asyn_count"]++;

			$result_arr = json_decode($result, true);

			if ($result && $result_arr['status'] == 100) {
				//如果有返回，说明接收成功
				$value['order_asyn_status'] = 1;
				$i++;
			} else {
				$j++;
				$asyn_data['al_status'] = 0;
			}
			$oop->save($value);
			//写入异步通知日志
			$asyn_data['al_send'] = dump($post_arr, false);
			$asyn_data['al_reply'] = dump($result, false);
			$asyn_data['al_oid'] = $value['order_id'];
			M("asynlog")->add($asyn_data);
		}

		//发送邮件通知
		$subject = $this->web_config['web_domain'] . "-自动异步通知-(" . date('Y-m-d H:i:s') . ")";
		$body = "通知有返回：" . $i . "<hr>通知但无返回：" . $j . "<hr>";
		if ($auto_result['email'] != 0) {
			$arr_to = explode(",", $auto_result['email']);
			foreach ($arr_to as $v_email) {
				parent::postmail($v_email, $subject, $body);
			}
		}

		exit($body);
	}

	/*
		* 自动处理预争议
	*/
	public function autoDispute() {
		$auto_reuslt = $this->checkStatus();
		$oop = M("orders");
		$con['order_status'] = array("in", "5,8"); //已退款或者已取消
		$con['order_islive'] = 1; //真实交易
		$con['order_isview'] = 1; //可见
		$con['order_predispute_status'] = 1; //预争议的交易
		I("order_id") && $con['order_id'] = I("order_id"); //如果有订单号，则只针对此订单号通知
		$list = $oop->where($con)->select();
		$i = 0;
		foreach ($list as $value) {
			$order = new \Common\Common\OrderProcess($value["order_id"]);
			$result = $order->toPredispute();
			if ($result) {
				$i++;
				//将预争议通知记录改为已处理
				$fa_oop = M("fraudalert");
				$con1['fa_status'] = 2;
				$con1['fa_oid'] = $value['order_id'];
				$data1['fa_status'] = 4;
				$fa_oop->where($con1)->save($data1);
			}
		}

		//发送邮件通知
		$subject = $this->web_config['web_domain'] . "-自动预争议-(" . date('Y-m-d H:i:s') . ")";
		$body = "预争议交易数：" . $i;
		if ($auto_result['email'] != 0) {
			$arr_to = explode(",", $auto_result['email']);
			foreach ($arr_to as $v_email) {
				parent::postmail($v_email, $subject, $body);
			}
		}

		exit($body);
	}

	/**
	 * 自动生成去年的几个表
	 */
	public function autoCutTable() {
		$this->checkStatus();
		/*
			$last_year = date("Y") - 1;
			$prefix = C('DB_PREFIX');

			$sql_str = "CREATE TABLE  IF NOT EXISTS " . $prefix . ORDERS . $last_year . " like " . $prefix . ORDERS . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . STATEMENT . $last_year . " like " . $prefix . STATEMENT . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . DELIVERY . $last_year . " like " . $prefix . DELIVERY . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . APPLICATION . $last_year . " like " . $prefix . APPLICATION . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . INNOTICE . $last_year . " like " . $prefix . INNOTICE . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . ROLLING . $last_year . " like " . $prefix . ROLLING . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . WEBLOG . $last_year . " like " . $prefix . WEBLOG . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . WITHDRAW . $last_year . " like " . $prefix . WITHDRAW . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . ORDERHISTORY . $last_year . " like " . $prefix . ORDERHISTORY . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . AFFILIATEORDER . $last_year . " like " . $prefix . AFFILIATEORDER . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . AFFILIATEWITHDRAW . $last_year . " like " . $prefix . AFFILIATEWITHDRAW . ";";
			$sql_str .= "CREATE TABLE  IF NOT EXISTS " . $prefix . AFFRECORD . $last_year . " like " . $prefix . AFFRECORD . ";";
			$sql_arr=explode(";",$sql_str);
			dump($sql_arr);
			$result = M()->execute($sql_str);
			dump($result);
		*/
	}

	/**
	 *自动将主数据库表中的过去一年的数据，保存到新表中，同时删除主表中的数据
	 */
	public function keepOldData() {
		$this->checkStatus();
		/*
			//考虑到本操作耗时较长，使用两个函数保证程序执行不被中断
			ignore_user_abort（true）；
			set_time_limit（0）；

			$last_year = date("Y") - 1;
			$prefix = C('DB_PREFIX');

			//订单表
			$sql_str = "insert into " . $prefix . ORDERS . $last_year . " select * from " . $prefix . ORDERS . " where order_time like '" . $last_year . "%';";
			//订单历史表
			$sql_str .= "insert into " . $prefix . ORDERHISTORY . $last_year . " select * from " . $prefix . ORDERHISTORY . " where oh_time like '" . $last_year . "%';";
			//STATEMENT
			$sql_str .= "insert into " . $prefix . STATEMENT . $last_year . " select * from " . $prefix . STATEMENT . " where st_time like '" . $last_year . "%';";
			//delivery
			$sql_str .= "insert into " . $prefix . DELIVERY . $last_year . " select * from " . $prefix . DELIVERY . " where dv_time like '" . $last_year . "%';";
			//application
			$sql_str .= "insert into " . $prefix . APPLICATION . $last_year . " select * from " . $prefix . APPLICATION . " where app_time like '" . $last_year . "%';";
			//innotice
			$sql_str .= "insert into " . $prefix . INNOTICE . $last_year . " select * from " . $prefix . INNOTICE . " where in_time like '" . $last_year . "%';";
			//ROLLING
			$sql_str .= "insert into " . $prefix . ROLLING . $last_year . " select * from " . $prefix . ROLLING . " where rl_date like '" . $last_year . "%';";
			//WEBLOG
			$sql_str .= "insert into " . $prefix . WEBLOG . $last_year . " select * from " . $prefix . WEBLOG . " where log_time like '" . $last_year . "%';";
			//WITHDRAW
			$sql_str .= "insert into " . $prefix . WITHDRAW . $last_year . " select * from " . $prefix . WITHDRAW . " where wd_date like '" . $last_year . "%';";
			//AFFILIATEORDER
			$sql_str .= "insert into " . $prefix . AFFILIATEORDER . $last_year . " select * from " . $prefix . AFFILIATEORDER . " where aff_order_time like '" . $last_year . "%';";
			//AFFILIATEWITHDRAW
			$sql_str .= "insert into " . $prefix . AFFILIATEWITHDRAW . $last_year . " select * from " . $prefix . AFFILIATEWITHDRAW . " where aw_time like '" . $last_year . "%';";
			//AFFRECORD
			$sql_str .= "insert into " . $prefix . AFFRECORD . $last_year . " select * from " . $prefix . AFFRECORD . " where ar_time like '" . $last_year . "%';";
			$sql_arr=explode(";",$sql_str);
			dump($sql_arr);
			$result = M()->execute($sql_str);
			dump($result);
		*/
	}

	/**
	 * 测试自动执行是否成功
	 */
	public function testAuto() {
		$this->checkStatus();
		$oop = M("innotice");
		$data['in_title'] = "测试自动执行以及异步操作";
		$data['in_content'] = "还是测试测试";
		$oop->add($data);
		exit("测试完成");
	}

	/**
	 * 每天统计前一天所有对账单数据
	 */
	public function autoInsertRecord() {
		//$last_day=GetMonth();
		$auto_result = $this->checkStatus();
		$last_day = date("Y-m-d", strtotime("-1 day"));
		$result = parent::calculateRecord($last_day, $mid = "", $show = false);
		dump($result);
	}

	/**
	 * 异步通知返回
	 */
	public function testSuccessReturn() {
		echo 100;
	}

}
