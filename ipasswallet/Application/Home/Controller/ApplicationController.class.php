<?php
namespace Home\Controller;
use Think\Controller;

class ApplicationController extends CommonController {

	public function _initialize() {
		//继承前面的配置
		parent::_initialize();
		if ($this->web_config['web_login_mode']) {
			$this->redirect("Public/login");
		}
	}

	public function index() {
		//dump($_SESSION);
		session("hash_code", makePass(10));
		$this->assign("hash_code", session("hash_code"));
		$this->display();
	}
	/**
	 *处理申请
	 */
	public function handleApply() {
		if (!I("email_code") || I("email_code") != cookie("verify_code")) {
			$this->error(L("invalid_email_code"));
		}

		$oop = M("application");
		$oop->create();
		$oop->app_ip = get_client_ip();
		if ($oop->add()) {
			$this->success(L("add_success"));
		} else {
			$this->error(L("add_failed"));
		}

	}

	public function postmail() {
		$data['status'] = 0;
		$data['data'] = 0;
		//如果发送过，则禁止60秒内重发
		if (cookie("verify_code")) {
			$data['info'] = L("repeat_error");
			$this->ajaxReturn($data);
		}
		//防止远程提交
		if (!I('hash_code') || !session("hash_code") || I("hash_code") != session("hash_code")) {
			$data['info'] = L("invalid_submit");
			$this->ajaxReturn($data);
		}
		$verify_code = makePass(5);
		//cookie缓存
		cookie("verify_code", $verify_code, 70);

		$body = "Your code:" . $verify_code . "<br>" . L("email_verify_code_info") . "<p>" . $this->web_config['web_domain'] . "</p>";

		$result = parent::postmail(I("email"), "[" . L("web_name") . "]" . L("email_verify_code"), $body);
		if ($result) {
			$data['status'] = 1;
			$data['info'] = L("send_success");
			$this->ajaxReturn($data);
		} else {
			$data['info'] = L("send_error");
			$this->ajaxReturn($data);
		}

	}
}
