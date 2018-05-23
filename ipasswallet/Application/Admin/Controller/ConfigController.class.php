<?php
namespace Admin\Controller;
use Think\Controller;

class ConfigController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "config";
		$this->key = "conf_name";
		$this->title_index = L('config_index');
		$this->title_details = L('config_details');
	}

	public function phpinfo() {
		phpinfo();
	}

	public function deleteHandle() {

		//dump(I("path.2"));exit;
		if (in_array(I("path.2"), C("WEB_CONFIG"))) {
			$this->error(L("config_delete_failed"));
		}

		$con['conf_name'] = I("path.2");
		if (M($this->model)->where($con)->delete()) {
			$this->success(L("delete_success"));
		} else {
			$this->error(L("config_delete_failed"));
		}

	}

}