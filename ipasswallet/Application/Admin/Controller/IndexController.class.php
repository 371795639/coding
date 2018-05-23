<?php
namespace Admin\Controller;
use Think\Controller;

class IndexController extends CommonController {

	//服务器设置
	public function setting() {
		$this->assign("main_title", L("setting"));
		$this->display();
	}

	public function top() {
		$this->display();
	}
	public function left() {
		$this->display();
	}

	public function index() {
		$this->display();
	}

	public function right() {
		$this->display();
	}
}