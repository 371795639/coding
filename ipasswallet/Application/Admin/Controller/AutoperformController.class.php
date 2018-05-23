<?php
namespace Admin\Controller;
use Think\Controller;

class AutoperformController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "autoperform";
		$this->key = "id";
		$this->title_index = L('auto_index');
		$this->title_details = L('auto_details');
	}

}