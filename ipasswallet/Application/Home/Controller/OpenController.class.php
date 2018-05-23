<?php
namespace Home\Controller;
use Think\Controller;

class OpenController extends CommonController {
	public function testasyn() {
		if (I("order_status")) {
			$data['status'] = 100;
			$data['data'] = I();
			$this->ajaxReturn($data);
		}
	}
}
