<?php
namespace Home\Controller;
use Think\Controller;

class EmailController extends CommonController {

	public function _initialize() {
		//继承前面的配置
		parent::_initialize();
	}

	/**
	 * 商户账户关闭邮件模板内容
	 */
	public function TerMerchant($mid = "") {
		$this->assign("mid", $mid);
		//$this->display("Email:ter_merchant");
		return $this->fetch("Home@Email:ter_merchant");
	}

	/**
	 * 顾客接收到的通知邮件的内容
	 * @param $order_id 订单号
	 */
	protected function trxToCustomer($order_id, $order_hash, $order_status = 2) {
		$oop = M("orders");
		$con['order_id'] = $order_id;
		$con['order_isview'] = 1;
		$con['order_status'] = $order_status;
		$con['order_signature_info'] = $order_hash;
		$list = $oop->where($con)->find();
		if (!$list) {
			exit("Invalid order data");
		} else {
			$this->assign($list);
			//$this->display("Email:trx_to_customer");
			return $this->fetch("Email:trx_to_customer");
		}
	}

	/**
	 * 发送交易邮件给顾客
	 */
	public function notifyTrxToCustomer($order_id, $cs_email, $order_hash, $order_status = 2) {
		$subject = "[" . $this->web_config['web_short_domain'] . "]Approved transaction notification";
		$body = $this->trxToCustomer($order_id, $order_hash, $order_status);
		parent::postmail($cs_email, $subject, $body);
	}

	/**
	 * 通知商户的拒付邮件模板
	 */
	protected function cbToMerchant($order_id, $order_hash) {
		$oop = D("OrdersdetailsView");
		$con['order_id'] = $order_id;
		$con['order_isview'] = 1;
		$con['order_status'] = 9;
		$con['order_signature_info'] = $order_hash;
		$list = $oop->where($con)->find();
		if (!$list) {
			exit("Invalid order data");
		} else {
			$this->assign($list);
			//$this->display("Email:cb_to_merchant");
			return $this->fetch("Email:cb_to_merchant");
		}
	}

	/**
	 * 邮件通知到商户拒付
	 */
	public function notifyCbToMerchant($order_id, $mh_email, $order_hash) {
		$subject = "[" . $this->web_config['web_short_domain'] . "]New chargeback notification";
		$body = $this->cbToMerchant($order_id, $order_hash);
		$result = parent::postmail($mh_email, $subject, $body);
		if ($result) {
			//记入IPS通知
			$in_title = "商户拒付通知邮件-" . $order_id . "-" . $mh_email;
			$in_content = $body;
			$in_oid = $order_id;
			$this->inputIPS($in_title, $in_content, $in_oid, 1);
			$this->success("拒付邮件通知发送成功！");
		} else {
			$this->error("拒付邮件通知发送失败！");
		}
	}

	/**
	 * 商户长时间不交易，通知商户相关信息的提醒邮件模板
	 */
	public function noprocesstpl($mid = "", $mh_name = "", $order_id = "") {
		if ($order_id) {
			$con['order_mid'] = $mid;
			$con['order_id'] = $order_id;
			$con['order_isview'] = 1;
			$list = M("orders")->where($con)->find();
			if ($list) {
				$this->assign($list);
			}
		}
		$this->assign("mid", $mid);
		$this->assign("mh_name", $mh_name);
		//$this->display("Email:noprocess_tpl");
		return $this->fetch("Home@Email:noprocess_tpl");
	}

	/**
	 * 商户开户成功的邮件通知模板
	 */
	public function setupMCtpl($login_pwd = "", $pay_pwd = "", $mid = "", $bank_id = "") {
		$this->assign("login_pwd", $login_pwd);
		$this->assign("pay_pwd", $pay_pwd);
		$mh_list = M("merchant")->find($mid);
		$this->assign("mh_list", $mh_list);
		$bank_list = M("bank")->find($bank_id);
		$this->assign("bank_list", $bank_list);

		if (!$mh_list || !$bank_list) {
			$this->error("非法的商户或银行信息！");
		}

		return $this->fetch("Home@Email:setup_merchant");
	}

	/**
	 * 发送商户成功交易通知
	 */
	public function notifyMerchant() {
		/*
			order id,merchant order id,order amount,order currency,order_time,merchant_id,customer email
		*/
		$merchant_order_arr = [
			'order_id' => I("order_id"),
			'mh_order_id' => I("mh_order_id"),
			'order_amount' => I("order_amount"),
			'order_currency' => I("order_currency"),
			'order_time' => I('order_time'),
			'mid' => I('mid'),
			'cs_email' => I('cs_email'),
		];
		$to = I("mh_email");
		$subject = "[" . L("web_name") . "]Approvedtransactionnotification";
		$body = $this->trxToMerchant($merchant_order_arr);
		parent::postmail($to, $subject, $body);
	}

	/**
	 * 邮件模板
	 */
	public function mailTpl() {
		return $this->fetch("Email:email_tpl");
	}

	/**
	 * 测试显示
	 */
	public function testshow() {
		$this->display("Email:setup_merchant");
	}

}
