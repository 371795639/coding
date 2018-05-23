<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends CommonController {

	public function _initialize() {
		//继承前面的配置
		parent::_initialize();
		$this->redirect("Public/login");
	}

	public function index() {
		$this->display();
	}

	public function minfraud() {
		$url = "http://apis.haoservice.com/lifeservice/exchange/rmbquot?key=" . $this->web_config['web_haoservice_exchange_apikey'];
		$oop = new \boss420\Common\AsynHandle();
		$result = $oop->get($url);
		dump($result);
	}

	public function test() {
		echo getLocationFromIp(get_rand_ip());
	}

}
