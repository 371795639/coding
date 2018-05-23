<?php
namespace Home\Controller;

use Think\Controller;

class RequestController extends UsersController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "transfer"; //表名
		$this->model_view = "RequestView";
		$this->key = "ts_id"; //排序键
		$this->title_index = L('request_money'); //选项卡标题
	}

	public function index() {
		$mist_arr = array("ts_remark" => I("ts_remark"));
		$accute_arr = array("ts_sender_email" => I("ts_sender_email"), "ts_id" => I("ts_id"), "ts_amount" => I("ts_amount"), "ts_type" => I("ts_type"), "ts_status" => I("ts_status"));
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		$con['ts_receiver_id'] = $this->user_info['user_id'];
		if (I('time_start') && I('time_end')) {
			$con['ts_time'] = array("between", array(I('time_start'), I('time_end')));
		}
		parent::home($con);
	}

	public function details() {

		$oop = M("tsdetails");
		$con1['td_ts_id'] = I("path.2");
		$list = $oop->where($con1)->order("td_id asc")->select();
		//dump($list);
		$this->assign("list", $list);

		$con['ts_receiver_id'] = $this->user_info['user_id'];
		$con['ts_id'] = I("path.2");
		//dump($con);
		parent::details($con);
	}

	public function request() {
		$this->assign("main_title", $this->title_index);
		$this->display();
	}

	//处理收款
	public function process() {

		$money_reg = "/^(([1-9]\d*)(\.\d{1,2})?)$|^(0\.0?([1-9]\d?))$/";
		//货币正则表达式
		if (!preg_match($money_reg, I("ts_amt"))) {
			$this->error(L("invalid_amt_format"));
		}

		//支付密码是否正确
		if (MD5(I('pay_pwd') . $this->user_info['user_salt']) != $this->user_info['user_pay_pwd']) {
			$this->error(L("invalid_pay_pwd"));
		}

		//最小金额限制
		if (I('ts_amt') < 5) {
			$this->error(L("min_amt_limit"));
		}
		//转账金额
		$ts_amt = number_format(I('ts_amt'), 2, ".", "");

		//不能转账给自己
		if (strtolower(trim(I("ts_target_email"))) == $this->user_info['user_email']) {
			$this->error(L("invalid_target"));
		}

		//查看收款邮箱是否有注册账户
		$oop = M("users");
		$con['user_status'] = array("neq", 3); //未禁用
		$con['user_email'] = strtolower(trim(I("ts_target_email")));
		$list = $oop->where($con)->find();
		if (!$list) {
			$this->error(L("invalid_user_acct"));
		}

		//创建转账记录和历史
		$ts_oop = M($this->model);
		$ts_oop->startTrans();
		$ts_data['ts_sender_id'] = $list['user_id'];
		$ts_data['ts_sender_email'] = $list['user_email'];
		$ts_data['ts_receiver_id'] = $this->user_info['user_id'];
		$ts_data['ts_receiver_email'] = $this->user_info['user_email'];
		$ts_data['ts_type'] = I('ts_type') ? 1 : 0;
		$ts_data['ts_discount_rate'] = $this->user_info['user_discount_rate'];
		$ts_data['ts_fixed_fee'] = $this->user_info['user_fixed_fee'];
		$ts_data['ts_fee'] = $ts_amt * $this->user_info['user_discount_rate'] + $this->user_info['user_fixed_fee'];
		$ts_data['ts_amount'] = $ts_amt;
		$ts_data['ts_remark'] = I("ts_remark");
		$result1 = $ts_oop->add($ts_data);

		//生成转账历史
		$ts_history = M("tsdetails");
		$th_data['td_ts_id'] = $result1;
		$result2 = $ts_history->add($th_data);

		if ($result1 && $result2) {
			$ts_oop->commit();
			$this->success(L("request_money_success"));
		} else {
			$ts_oop->rollback();
			$this->error(L("request_money_error"));
		}

	}

	//确认取消交易
	public function confirmCancel() {
		$con['ts_id'] = trim(I("ts_id"));
		$con['ts_status'] = 1;
		$con['ts_receiver_id'] = $this->user_info['user_id'];
		$list = M($this->model)->where($con)->find();
		if (!$list) {
			$this->error(L("action_deny"));
		}
		$ts = new \Common\Common\TransferProcess($list['ts_id']);

		$result = $ts->toCancel();

		if ($result) {
			$this->success(L('action_success'));
		} else {
			$this->error(L('action_failed'));
		}
	}

}
