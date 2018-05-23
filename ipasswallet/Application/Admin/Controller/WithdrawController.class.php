<?php
namespace Admin\Controller;

use Think\Controller;

class WithdrawController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "withdraw";
		//$this->model_view = "WithdrawView";
		$this->key = "wd_id";
		$this->title_index = L('withdraw_index');
		$this->title_details = L('withdraw_details');
	}

	public function index() {
		$mist_arr = array();
		$accute_arr = array('wd_status' => I("wd_status"), 'wd_user_id' => I("wd_user_id"));
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		parent::index($con);
	}

	public function updateHandle() {

		if (I("wd_status") == 3) {
			$oop = M($this->model);
			$con['wd_id'] = I("wd_id");
			$con['wd_status'] = 1;
			$list = $oop->where($con)->find();
			//启动事务
			$oop->startTrans();
			$result = createStatement(I("wd_mid"), 7, I("wd_amount"), "Withdraw voided");
			if ($result && $list) {
				$oop->commit();
			} else {
				$oop->rollback();
				$this->error(L("action_failed"));
			}
		}

		parent::updateHandle();

	}

	//查看提现银行信息
	public function wdbank() {
		$result = M("wdbank")->find(I("wd_bank_id"));
		parent::showInfo(dump($result, false), U("Withdraw/index"));
	}

}
