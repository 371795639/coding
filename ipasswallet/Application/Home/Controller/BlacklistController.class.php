<?php
namespace Home\Controller;
use Think\Controller;

class BlacklistController extends UsersController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "bank"; //表名
		$this->key = "bank_id"; //排序键
		$this->title_index = L('my_bank'); //选项卡标题
	}

	public function index() {
		$mist_arr = array();
		$accute_arr = array();
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		$con['nt_isactive'] = 1;
		parent::home($con);
	}

	public function details() {
		$con['nt_id'] = I("path.2");
		$con['nt_isactive'] = 1;
		parent::details($con);
	}

}
