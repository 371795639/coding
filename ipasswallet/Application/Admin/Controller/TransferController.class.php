<?php
namespace Admin\Controller;
use Think\Controller;

class TransferController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "transfer";
		$this->key = "ts_id";
		$this->title_index = L("transfer_index");
		$this->title_details = L("transfer_details");
	}

	public function index() {
		$mist_arr = array();
		$accute_arr = array();
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		parent::index($con);
	}

	public function details() {
		$oop = M($this->model);
		$con['hp_id'] = I("path.2");
		$oop->where($con)->setInc("hp_count");
		parent::details();
	}

}