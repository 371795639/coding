<?php
namespace Home\Controller;
use Think\Controller;

class SupportController extends UsersController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "support"; //表名
		$this->key = "sp_id"; //排序键
		$this->title_index = L('support_center'); //选项卡标题
	}

	public function index() {

		$mist_arr = array('sp_title' => I("sp_title"));
		$accute_arr = array('sp_id' => I("sp_id"), "sp_status" => I("sp_status"));
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		$con['sp_user_id'] = $this->user_info['user_id'];
		parent::home($con);
	}

	public function addHandle() {
		$data['sp_uuid'] = I("sp_uuid");
		$data['sp_title'] = I("sp_title");
		$data['sp_type'] = I("sp_type");
		$data['sp_question'] = I("sp_question");
		$data['sp_user_id'] = $this->user_info['user_id'];
		//dump($data);
		if (isExsitNullArr($data)) {
			$this->error(L("invalid_params"));
		}
		parent::addHandle($data);
	}

}
