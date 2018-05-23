<?php
namespace Admin\Controller;
use Think\Controller;

class HelpController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "help";
		$this->key = "hp_id";
		$this->title_index = "使用指南";
		$this->title_details = "使用指南";
		$this->num = 100;
		$this->assign("auth_admin", C("AUTH_CONFIG.AUTH_ADMIN"));
	}

	public function index() {
		$mist_arr = array('hp_title' => I("hp_title"));
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