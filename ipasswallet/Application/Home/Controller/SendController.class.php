<?php
namespace Home\Controller;

use Think\Controller;

class SendController extends UsersController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "transfer"; //表名
		$this->model_view = "SendView";
		$this->key = "ts_id"; //排序键
		$this->title_index = L('send_money'); //选项卡标题
	}

	public function index() {
		$mist_arr = array("ts_remark" => I("ts_remark"));
		$accute_arr = array("ts_receiver_email" => I("ts_receiver_email"), "ts_id" => I("ts_id"), "ts_amount" => I("ts_amount"), "ts_type" => I("ts_type"), "ts_status" => I("ts_status"));
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		$con['ts_sender_id'] = $this->user_info['user_id'];
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

		$con['ts_sender_id'] = $this->user_info['user_id'];
		$con['ts_id'] = I("path.2");
		//dump($con);
		parent::details($con);
	}

	public function process() {
		$ts_amt = $this->premoney(I("pay_pwd"), I("ts_amt"), $pay_type = 0);
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

		//查看收款方是否为个人，如果是个人，每日收款次数为10次

		//创建转账记录和历史
		$ts_oop = M($this->model);
		$ts_oop->startTrans();
		$ts_data['ts_sender_id'] = $this->user_info['user_id'];
		$ts_data['ts_sender_email'] = $this->user_info['user_email'];
		$ts_data['ts_receiver_id'] = $list['user_id'];
		$ts_data['ts_receiver_email'] = $list['user_email'];
		$ts_data['ts_type'] = I('ts_type') ? 1 : 0;
		$ts_data['ts_discount_rate'] = $list['user_discount_rate'];
		$ts_data['ts_fixed_fee'] = $list['user_fixed_fee'];
		$ts_data['ts_fee'] = $ts_amt * $list['user_discount_rate'] + $list['user_fixed_fee'];
		$ts_data['ts_amount'] = $ts_amt;
		$ts_data['ts_remark'] = I("ts_remark");
		$result1 = $ts_oop->add($ts_data);

		//生成转账历史
		$ts_history = M("tsdetails");
		$th_data['td_ts_id'] = $result1;
		$result2 = $ts_history->add($th_data);

		if ($result1 && $result2) {
			$ts_oop->commit();
			$transfer = new \Common\Common\TransferProcess($result1);
			if ($ts_data['ts_type']) {
				$result = $transfer->toComplete();
			} else {
				$result = $transfer->toPendingConfirmed();
			}

			if ($result) {
				$this->success(L("send_money_success"));
			} else {
				$this->error(L("send_money_error"));
			}

		} else {
			$ts_oop->rollback();
			$this->error(L("error_gen_ts"));
		}

	}

	//转账操作页面
	public function send() {
		$this->assign("main_title", $this->title_index);
		$this->display();
	}

	//担保交易/即时到账交易 确认付款
	public function confirmPay() {
		$con['ts_id'] = trim(I("ts_id"));
		$con['ts_status'] = array("in", "1,2,7,8");
		$con['ts_sender_id'] = $this->user_info['user_id'];
		$list = M($this->model)->where($con)->find();
		if (!$list) {
			$this->error(L("action_deny"));
		}
		$ts = new \Common\Common\TransferProcess($list['ts_id']);
		if ($list['ts_status'] == 1 && $list['ts_type'] == 0) {
			$result = $ts->toPendingConfirmed();
		} else {
			$result = $ts->toComplete();
		}
		if ($result) {
			$this->success(L('action_success'));
		} else {
			$this->error(L('action_failed'));
		}
	}

	//退款
	public function confirmRefund() {
		if (I("refund_reason") == "") {
			$this->error(L("invalid_params"));
		}

		$con['ts_id'] = trim(I("ts_id"));
		$con['ts_status'] = 2;
		$con['ts_sender_id'] = $this->user_info['user_id'];
		$list = M($this->model)->where($con)->find();
		if (!$list) {
			$this->error(L("action_deny"));
		}
		$ts = new \Common\Common\TransferProcess($list['ts_id']);

		$result = $ts->toPendingRefund(I("refund_reason"));

		if ($result) {
			$this->success(L('action_success'));
		} else {
			$this->error(L('action_failed'));
		}
	}

	//取消退款
	public function cancelRefund() {
		$con['ts_id'] = trim(I("ts_id"));
		$con['ts_status'] = 5;
		$con['ts_sender_id'] = $this->user_info['user_id'];
		$list = M($this->model)->where($con)->find();
		if (!$list) {
			$this->error(L("action_deny"));
		}
		$ts = new \Common\Common\TransferProcess($list['ts_id']);

		$result = $ts->toPendingConfirmed();

		if ($result) {
			$this->success(L('action_success'));
		} else {
			$this->error(L('action_failed'));
		}
	}

	//争议处理
	public function processDispute() {
		if (I("dispute_reason") == "") {
			$this->error(L("invalid_params"));
		}
		$con['ts_id'] = trim(I("ts_id"));
		$con['ts_status'] = 3;
		$con['ts_sender_id'] = $this->user_info['user_id'];
		$list = M($this->model)->where($con)->find();
		if (!$list) {
			$this->error(L("action_deny"));
		}
		$ts = new \Common\Common\TransferProcess($list['ts_id']);

		$result = $ts->toDispute(I("dispute_reason"));

		if ($result) {
			$this->success(L('action_success'));
		} else {
			$this->error(L('action_failed'));
		}
	}

	//拒绝交易处理
	public function processDecline() {
		if (I("decline_reason") == "") {
			$this->error(L("invalid_params"));
		}
		$con['ts_id'] = trim(I("ts_id"));
		$con['ts_status'] = 1;
		$con['ts_sender_id'] = $this->user_info['user_id'];
		$list = M($this->model)->where($con)->find();
		if (!$list) {
			$this->error(L("action_deny"));
		}
		$ts = new \Common\Common\TransferProcess($list['ts_id']);

		$result = $ts->toDeclined(I("decline_reason"));

		if ($result) {
			$this->success(L('action_success'));
		} else {
			$this->error(L('action_failed'));
		}
	}

}
