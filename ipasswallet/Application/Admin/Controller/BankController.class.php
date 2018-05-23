<?php
namespace Admin\Controller;
use Think\Controller;

class BankController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "bank";
		$this->key = "bank_id";
		$this->title_index = L('bank_index');
		$this->title_details = L('bank_details');
	}

	public function index() {
		$mist_arr = array("bank_branch" => I("bank_branch"), "bank_remark" => I("bank_remark"));
		$accute_arr = array('bank_user_id' => I("bank_user_id"), 'bank_client' => I('bank_client'), "bank_account" => I("bank_account"),
		);
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		parent::index($con);
	}

	public function addHandle() {
		//首先查看用户账户是否存在
		$oop = M("users");
		$con['user_id'] = I("bank_user_id");
		$list = $oop->where($con)->find();
		if ($list) {
			$this->error(L("operation_forbiden"));
		}
		parent::addHandle();
	}

}