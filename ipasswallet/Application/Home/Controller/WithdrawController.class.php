<?php
namespace Home\Controller;

use Think\Controller;

class WithdrawController extends UsersController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "withdraw"; //表名
		//$this->model_view = "WithdrawView";
		$this->key = "wd_id"; //排序键
		$this->title_index = L('withdraw'); //选项卡标题
	}

	public function index() {
		$data['now_day'] = date("Y-m-d", strtotime("+1 day"));
		$data['pass_day'] = date("Y-m-d", strtotime("-" . ($this->merchant_info['mh_covered_days']) . " days"));
		//计算提现汇率
		//$result = payoutCR();
		//dump($result);
		$this->assign("ce_list", $result['list']);
		$this->assign($result['content']);

		//查看提现银行信息
		$oop = M('bank');
		$con['bank_mid'] = $this->merchant_info["mh_id"];
		$list = $oop->field(true)->where($con)->order("bank_id desc")->select();
		$list && $this->assign("bank_info", $list);

		$mist_arr = array();
		$accute_arr = array();
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		$con['wd_mid'] = $this->merchant_info['mh_id'];
		parent::home($con);
	}

	//提现操作
	public function process() {
		$this->sandboxCheck();

		MD5(I('pay_pwd') . $this->merchant_info['mh_salt']) == $this->merchant_info['mh_paypwd'] || $this->error(L("invalid_pay_pwd"));
		I('wd_money') >= $this->merchant_info['mh_min_withdraw_amount'] || $this->error(L("min_withdraw_limit"));
		I('wd_money') <= $this->withdrawBalance() || $this->error(L("withdraw_amount_limit"));
		//是否可以提现
		$this->merchant_info['mh_withdraw_status'] || $this->error(L("withdraw_forbidden"));

		$oop = M($this->model);
		//先看是否有正在处理的提现
		$con['wd_mid'] = $this->merchant_info["mh_id"];
		$con['wd_status'] = 1;
		$list1 = $oop->where($con)->find();
		if ($list1) {
			$this->error(L("pending_withdraw_notice"));
		}

		//今天是否有已经成功的提现
		$con['wd_status'] = 2; //提现成功
		$con['wd_enddate'] = array("like", "%" . date("Y-m-d") . "%");
		$list2 = $oop->where($con)->find();
		if ($list2 && !$this->merchant_info['mh_multi_withdraw']) {
			$this->error(L("withdraw_done_notice"));
		}

		//查看是否余额小于风控备用金
		if (($this->merchant_info['mh_balance'] - I("wd_money")) < $this->riskBalance()) {
			$this->error(L("withdraw_risk_balance_notice"));
		}

		$ce_oop = M("exchange");
		$ce_list = $ce_oop->field('ex_currency_name,1/ex_currency_value*ex_payout_rate as payout_rate')->select();

		//开启事务
		$oop->startTrans();
		//提现银行新增记录
		$result0 = M("wdbank")->add(M("bank")->find(I('wd_bank_id')));
		//提现记录增加
		$data['wd_mid'] = $this->merchant_info["mh_id"];
		$data['wd_amount'] = I("wd_money");
		$data['wd_fee'] = $this->merchant_info['mh_withdraw_fee'];
		$data['wd_bank_id'] = $result0;
		$data['wd_total'] = $this->merchant_info['mh_balance'];
		$data['wd_holdamount'] = $this->merchant_info['mh_balance'] - $this->withdrawBalance();
		$data['wd_mh_remark'] = I("wd_mh_remark") . "";
		$data['wd_remark'] = json_encode($ce_list);
		$result = $oop->add($data);
		//写入对账单，并且修改账户余额
		$result1 = createStatement($this->merchant_info['mh_id'], 6, I("wd_money"),
			"Merchants withdraw money,withdraw #:" . $result, $this->merchant_info['mh_withdraw_fee']);

		if ($result0 && $result && $result1) {
			$oop->commit(); //成功则提交
			$this->success(L("withdraw_success"));
		} else {
			$oop->rollback(); //不成功则回滚
			$this->error(L("withdraw_failed"));
		}
	}

	//风控金额
	public function riskBalance() {
		$oop = M("orders");
		$con['order_islive'] = 1; //真实交易
		$con['order_mid'] = $this->merchant_info['mh_id'];
		$con['order_status'] = array("in", "2,4");
		$con['_string'] = "TO_DAYS(NOW()) - TO_DAYS(order_time) <= " . $this->merchant_info['mh_wd_limit_days'];
		$base_sum = $oop->where($con)->sum("base_amount");
		$avg_funds = ($base_sum / $this->merchant_info['mh_wd_limit_days']) * $this->merchant_info['mh_wd_limit_rate'] * $this->merchant_info['mh_covered_days'];
		$avg_funds = number_format($avg_funds, 2, '.', '');
		return $avg_funds;
	}

	/**
	 * 提现银行信息
	 */
	public function mybank() {
		$this->model = "bank"; //表名
		$this->model_view = "bank";
		$this->key = "bank_id"; //排序键
		$this->title_index = L("my_bank"); //选项卡标题
		$mist_arr = array();
		$accute_arr = array();
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		$con['bank_mid'] = $this->merchant_info["mh_id"];
		parent::home($con);
	}

	/**
	 * 银行详情
	 */
	public function bankinfo() {
		$this->model = "bank";
		$this->model_view = "bank";
		$this->key = "bank_id";
		$this->title_index = L("my_bank");
		$con['bank_mid'] = $this->merchant_info['mh_id'];
		$con['bank_id'] = I('path.2');
		parent::details($con);
	}

	/**
	 * 更新
	 */
	public function updateBank() {
		MD5(I('pay_pwd') . $this->merchant_info['mh_salt']) == $this->merchant_info['mh_paypwd'] || $this->error(L("invalid_pay_pwd"));
		$this->model = "bank";
		$this->key = "bank_id";
		$this->title_index = L("my_bank");
		$con['bank_mid'] = $this->merchant_info['mh_id'];
		$con['bank_id'] = I('bank_id');
		parent::updateHandle($con);
	}

	/**
	 * 移除银行信息
	 */
	public function delBank() {
		MD5(I('pay_pwd') . $this->merchant_info['mh_salt']) == $this->merchant_info['mh_paypwd'] || $this->error(L("invalid_pay_pwd"));
		$this->model = "bank";
		$this->key = "bank_id";
		$this->title_index = L("my_bank");
		$con['bank_mid'] = $this->merchant_info['mh_id'];
		$con['bank_id'] = I('bank_id');
		parent::deleteHandle($con);
	}

	/**
	 * 新增银行信息
	 */
	public function addBank() {
		MD5(I('pay_pwd') . $this->merchant_info['mh_salt']) == $this->merchant_info['mh_paypwd'] || $this->error(L("invalid_pay_pwd"));
		$this->model = "bank";
		$this->key = "bank_id";
		$this->title_index = L("my_bank");
		$data = I();
		$data['bank_mid'] = $this->merchant_info['mh_id'];
		parent::addHandle($data);
	}

}
