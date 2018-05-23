<?php
namespace Admin\Controller;

use Think\Controller;

class StatementController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "statement";
		$this->key = "st_id";
		$this->title_index = L('st_index');
		$this->title_details = L('st_details');
	}

	//对账单列表
	public function index() {
		$mist_arr = array('st_reason' => I('st_reason'));
		$accute_arr = array('st_type' => I("st_type"), 'st_user_id' => I("st_user_id"));
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);

		//时间区间
		if (I("order_time_start") && I("order_time_end")) {
			$con['st_time'] = array("between", array(I("order_time_start"), I("order_time_end")));
		}

		if (I("gen_csv")) {
			$sql = M($this->model)->fetchSql(true)->where($con)->order($this->key . " desc")->select();
			parent::createCsv($sql, C("admin_statement_csv"), "admin-statement");
		}

		parent::index($con, ['st_amount']);
	}

	/**
	 *下载CSV文件
	 */
	public function downloadCsv() {
		parent::downloadCsv("admin-statement.csv");
	}

}
