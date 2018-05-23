<?php
namespace Home\Controller;
use Think\Controller;

class PublicController extends CommonController {
	public function login() {
		session("hash_code", makePass(10));
		session("user_id") ? $this->redirect("Users/index") : $this->display($this->brand_config['brand_login']);
	}

	/**
	 *登录账户
	 */
	public function checkin() {
		//先核对验证码
		$this->checkCode();
		$con['user_email'] = trim(I('user_email'));
		$con['mh_isactive'] = array("neq", 3); //非禁用
		$result = M("users")->where($con)->find();
		if (!$result || md5(I('user_pwd') . $result['user_salt']) != $result['user_pwd']) {
			if ($result) {
				parent::addLog($result['user_id'], $result['user_type'], 0, L("invalid_login"));
			} else {
				parent::addLog(-1, -1, 0, L("invalid_login"));
			}
			$this->error(L("invalid_login"));
		}
		parent::addLog($result['user_id'], $result['user_type'], 1, "Success");
		session("user_id", $result['user_id']);
		$this->redirect("Users/index");

	}

	//注册
	public function signup() {
		$this->display();
	}

	//注册第二步
	public function signup2() {
		//echo $this->web_config['web_email_notify'];
		$this->display();
	}

	public function postmail() {
		$data['status'] = 0;
		$data['data'] = 0;

		switch (I('type')) {
		case 1: //注册
			$body_desc = L("email_signup_desc");
			break;
		case 2: //忘记密码
			$body_desc = L("email_forgetpwd_desc");
			break;
		case 3: //修改密码
			$body_desc = L("email_updatepwd_des");
			break;
		default:
			$data['info'] = L("invalid_submit");
			$this->ajaxReturn($data);
		}

		//验证邮箱
		if (!filter_var(I("user_email"), FILTER_VALIDATE_EMAIL)) {
			$data['info'] = L("invalid_email");
			$this->ajaxReturn($data);
		}

		//查看邮箱账户是否存在
		$oop = M("users");
		$con['user_email'] = I("user_email");
		$list = $oop->lock(true)->where($con)->find();
		if ($list && I("type") == 1) {
			$data['info'] = L("account_not_allowed");
			$this->ajaxReturn($data);
		}

		if ($type != 1 && !$list) {
			$data['info'] = L("unknow_user_account");
			$this->ajaxReturn($data);
		}

		//如果发送过，则禁止120秒内重发
		if (cookie("v_code")) {
			$data['info'] = L("repeat_error");
			$this->ajaxReturn($data);
		}
		//防止远程提交
		if (!I("user_email") || !I('hash_code') || !session("hash_code") || I("hash_code") != session("hash_code")) {
			$data['info'] = L("invalid_submit");
			$this->ajaxReturn($data);
		}

		$to = I("user_email");
		$title = "[" . L("web_name") . "]" . L("email_verify_code");
		$verify_code = makePass(5);
		$body = L("email_verify_code") . "&nbsp;&nbsp;:&nbsp;" . $verify_code . "<br>" . $body_desc . "<p>" . $this->web_config['web_domain'] . "</p>";

		$result = parent::postmail($to, $title, $body);
		if ($result) {
			cookie("v_code", md5($verify_code . I('user_email')), 120);
			$data['status'] = 1;
			$data['info'] = L("send_success");
			$this->ajaxReturn($data);
		} else {
			$data['info'] = L("send_error");
			$this->ajaxReturn($data);
		}

	}

	//注册账户最后一步，提交
	public function register() {
		//验证邮件验证码
		if (!cookie("v_code") || cookie("v_code") != md5(I("verify") . I("user_email"))) {
			$this->error(L("invalid_email_code"));
		}

		if (I("user_name") == "" || I("user_email") == "") {
			$this->error(L("invalid_name_email"));
		}

		if (I("type") == 1) {
			$data['user_type'] = 1; //企业
		} else {
			$data['user_type'] = 0; //个人
			$data['user_discount_rate'] = 0; //手续费是0
		}

		$salt = makePass(6);
		$pwd = makePass(12); //密码
		$ppwd = makePass(12); //支付密码

		$title = "[" . L("web_name") . "]" . L("register_success_title");
		$body = L("register_success_info") .
		"<p>" . L("user_name") . ":" . I("user_name") . "</p>" .
		"<p>" . L("pwd") . ":" . $pwd . "</p>" .
		"<p>" . L("pay_pwd") . ":" . $ppwd . "</p>";

		$oop = M("users");
		$data['user_email'] = trim(strtolower(I("user_email")));
		$data['user_name'] = I("user_name");
		$data['user_api_key'] = makePass(25); //API
		$data['user_pwd'] = md5($pwd . $salt);
		$data['user_pay_pwd'] = md5($ppwd . $salt);
		$data['user_hidden_info'] = $body;
		$data['user_salt'] = $salt; //加盐值
		try {
			$oop->startTrans();
			$result = $oop->add($data);
			if ($result) {
				$rs = parent::postmail(I("user_email"), $title, $body);
				if ($rs) {
					$oop->commit();
					$this->success(L("register_success_note"), 'login', 12);
				} else {
					$oop->rollback();
					$this->error(L("register_error_info"), 'signup', 8);
				}
			} else {
				$this->error(L("register_error_info"), 'signup', 8);
			}
		} catch (\Exception $e) {
			$this->error(L("register_exception_info"), 'signup', 8);
		}

	}

	public function updatePwd() {
		//dump(I());EXIT;
		if (cookie('v_code') != md5(I('code') . I('id') . I('type')) || I('hash_code') != session('hash_code') || strlen(I('pwd')) < 6) {
			$this->error(L("invalid_submit"));
		}

		if (I('type') == 1) {
			$oop = M("merchant");
			$list = $oop->find(I('id'));
			$data['mh_id'] = I("id");
			$data['mh_pwd'] = md5(I('pwd') . $list['mh_salt']);

			if ($data['mh_pwd'] == $list['mh_pwd']) {
				$this->error(L("same_pwd"));
			}
		} else {
			$oop = M("affiliate");
			$list = $oop->find(I('id'));
			$data['aff_id'] = I("id");
			$data['aff_pwd'] = md5(I('pwd') . $list['aff_salt']);
			if ($data['aff_pwd'] == $list['aff_pwd']) {
				$this->error(L("same_pwd"));
			}
		}
		//dump($data);
		if ($oop->save($data)) {
			$this->success(L("action_success"));
		} else {
			$this->error(L("action_failed"));
		}
	}

	public function logout() {
		session(null);
		$this->redirect("Index/index");
	}

//根据国家代码返回州
	public function getState() {
		I("iso") == 3 ? $iso = 3 : $iso = 2;
		$this->ajaxReturn(parent::getState(I("country_name"), $iso));
	}

	public function test() {
		$this->success(L("register_success_note"), 'login', 12);
	}

}