<?php
namespace Admin\Controller;

use Think\Controller;

class DepositController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "deposit";
		$this->key = "dp_id";
		$this->title_index = L('deposit_index');
		$this->title_details = L('deposit_details');
	}

	public function index() {
		$mist_arr = array();
		$accute_arr = array('dp_status' => I("dp_status"), 'dp_user_id' => I("dp_user_id"), 'dp_method' => I("dp_method"), 'dp_reference_id' => I("dp_reference_id"));
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		parent::index($con);
	}

}
