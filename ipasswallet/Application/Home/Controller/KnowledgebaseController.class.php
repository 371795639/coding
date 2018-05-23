<?php
namespace Home\Controller;
use Think\Controller;

class KnowledgebaseController extends UsersController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "notice"; //表名
		$this->key = "nt_id"; //排序键
		$this->title_index = L('notice'); //选项卡标题
	}

	public function index() {
		$mist_arr = array();
		$accute_arr = array();
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		$con['nt_lang'] = LANG_SET;
		$con['nt_isactive'] = 1;
		parent::home($con);
	}

	public function details() {
		$con['nt_id'] = I("path.2");
		$con['nt_isactive'] = 1;
		parent::details($con);
	}

}
