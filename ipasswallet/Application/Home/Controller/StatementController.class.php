<?php
namespace Home\Controller;
use Think\Controller;

class StatementController extends UsersController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "statement"; //表名
		$this->key = "st_id"; //排序键
		$this->title_index = L("my_statement"); //选项卡标题
	}

	public function index() {
		$mist_arr = array('st_reason' => I("st_reason"));
		$accute_arr = array('st_amount' => I('st_amount'), 'st_name' => I('st_name'));
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		if (I('st_time_start') && I('st_time_end')) {
			$con['st_time'] = array("between", array(I('st_time_start'), I('st_time_end')));
		}
		$con['st_user_id'] = $this->user_info['user_id'];
		//生成CSV
		if (I('create_csv')) {
			$count = M($this->model)->where($con)->count();
			//超过200000条记录不予下载
			if ($count > 200000) {
				$this->assign("download_flag", 0);
			} else {
				$sql = M($this->model)->fetchSql(true)->where($con)->order($this->key . " desc")->select();
				//echo $sql;
				parent::createCsv($sql, C("STATEMENT_CSV"), "statement_" . $this->user_info['user_id']);
			}
		}

		//统计
		//增加的
		$con1['st_flag'] = 1;
		$result['plus'] = M($this->model)->where($con)->where($con1)->sum("st_amount");
		//减少的
		$con1['st_flag'] = 0;
		$result['minus'] = M($this->model)->where($con)->where($con1)->sum("st_amount");
		//费用
		$result['fee'] = M($this->model)->where($con)->sum("st_fee");
		$this->assign($result);

		parent::home($con);
	}

	public function downloadCsv() {
		parent::downloadCsv("statement_" . $this->user_info['user_id'] . ".csv");
	}

}
