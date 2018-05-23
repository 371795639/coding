<?php
namespace Admin\Controller;

use Think\Controller;

class WeblogController extends CommonController {
	public function _initialize() {
		parent::_initialize();
		$this->model = "weblog";
		$this->key = "log_id";
		$this->title_index = L('weblog_index');
		$this->title_details = L('weblog_details');
	}

	public function index() {
		$mist_arr = array('log_action' => I("log_action"));
		$accute_arr = array('log_admin' => I("log_admin"));
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		parent::index($con);
	}

}
