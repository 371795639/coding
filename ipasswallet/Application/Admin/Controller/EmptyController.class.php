<?php
namespace Admin\Controller;
use Think\Controller;

class EmptyController extends Controller {

	//空方法
	public function _empty() {
		$this->display("Public:error404");
	}

}