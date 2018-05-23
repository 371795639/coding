<?php
namespace Admin\Controller;
use Think\Controller;

class AdvanceController extends CommonController {
	//高级操作
	public function index() {
		$this->assign("main_title", L("senior_operation"));
		$this->display();
	}

	public function processsql() {
		$regm = "/drop|ips_auth_group|ips_auth_group_access|ips_auth_rule|ips_member|ips_session/";
		$result = preg_match($regm, I("sql_string"));
		if ($result) {
			$this->error(L("action_deny"));
		}

		$Model = M(); // 实例化一个model对象 没有对应任何数据表
		//$Model->query("set time_zone='+8:00'");
		if (I("type") == 0) {
			$result = $Model->query(I("sql_string"));
		} else {
			$result = $Model->execute(I("sql_string"));
		}
		echo $Model->_sql();
		dump($result);
	}

}
?>