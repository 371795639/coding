<?php
namespace Home\Controller;
use Think\Controller;

class RpageController extends CommonController {

	public function _initialize() {
		//继承前面的配置
		parent::_initialize();
		if ($this->web_config['web_login_mode']) {
			$this->redirect("Public/login");
		}
	}

	public function _empty() {
		if (I('path.1')) {
			$this->cms(I('path.1'));
		} else {
			$this->display("Public:error404");
		}
	}

	protected function cms($name) {
		$oop = M("cms");
		$con['cms_isactive'] = 1;
		$con['cms_lang'] = cookie("think_language");
		$con['cms_unique'] = $name;
		$list = $oop->where($con)->find();
		//echo $oop->_sql();
		if ($list) {
			$this->assign($list);
			$this->display($list['cms_tpl']);
		} else {
			$this->display("Public:error404");
		}
	}

}
