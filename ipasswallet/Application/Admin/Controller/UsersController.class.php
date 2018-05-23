<?php
namespace Admin\Controller;
use Think\Controller;

class UsersController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "users";
		$this->key = "user_id";
		$this->title_index = L('user_index');
		$this->title_details = L('user_details');
	}

	/**
	 *商户列表页面，由于涉及到搜索问题，重写父类方法
	 */
	public function index() {
		$mist_arr = array('user_name' => I("user_name"));
		$accute_arr = array("user_email" => I("user_email"), "user_id" => I("user_id"), "user_status" => I("user_status"), "user_type" => I("user_type"));
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		parent::index($con);
	}

	/**
	 * 商户对账功能，即因为操作失误导致的账户金额错误，需要增加或者扣除
	 */
	public function transfer() {
		$oop = M();
		$oop->startTrans();
		//调用对账单方法
		$result = createStatement(I("mh_id"), I("transfer_type"), I("transfer_money"), I("remark"));
		if ($result) {
			$oop->commit();
			$this->success(L("action_success"));
		} else {
			$oop->rollback();
			$this->error(L("action_failed"));
		}

	}

	/**
	 *因为涉及到密码操作，重新添加方法
	 */
	public function addHandle() {
		$salt = makePass(6);
		$pwd = makePass(12);
		$ppwd = makePass(12);
		$hidden_info = "pwd: " . $pwd . " ; pay_pwd: " . $ppwd;
		try {
			$oop = M($this->model);
			$oop->create();
			$oop->user_email = strtolower(I("user_email"));
			$oop->user_salt = $salt; //密码加盐值
			$oop->user_pwd = md5($pwd . $salt);
			$oop->user_pay_pwd = md5($ppwd . $salt);
			$oop->user_api_key = makePass(25); //API_KEY
			$oop->user_hidden_info = $hidden_info;

			if ($mid = $oop->add()) {
				//$this->success(L("add_success"));
				$this->showInfo(L("add_success") . " " . $hidden_info, "index");
			} else {
				$this->error(L("add_failed"));
			}

		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
	}

	/**
	 *因为涉及到密码操作，重新保存方法
	 */
	public function updateHandle() {
		//dump(I());EXIT;
		$oop = M($this->model);
		$oop->create();
		if (I("new_pwd")) {
			$oop->user_pwd = md5(I("new_pwd") . I("user_salt"));
		}
		if (I("new_pay_pwd")) {
			$oop->user_pay_pwd = md5(I("new_pay_pwd") . I("user_salt"));
		}
		$oop->user_email = strtolower(I("user_email"));
		if ($oop->save()) {
			$this->success(L("update_success"));
		} else {
			$this->error(L("update_failed"));
		}

	}

	/*
		* 以商户身份登录前台
	*/
	public function logas() {
		session("user_id", I("path.2"));
		header("Location: " . U("Home/Users/index") . "");
	}

}