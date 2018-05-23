<?php
namespace Admin\Controller;
use Think\Controller;

class AccessController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "authRule";
		$this->key = "id";
		$this->title_index = L('access_index');
		$this->title_details = L('access_details');
		$this->title_add = L('add');
	}

	public function index() {
		$mist_arr = array("title" => I("title"), "name" => I("name"));
		$accute_arr = array();
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		parent::index($con);
	}

}
?>