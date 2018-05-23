<?php
namespace Admin\Controller;
use Common\Controller\BaseController;

class PublicController extends BaseController {

	//登录页面
	public function login() {
		session("uid") ? $this->redirect('Index/index') : $this->display();
	}

	//登入
	public function checkin() {

		if ($this->web_config['web_verify_code']) {
			$this->checkCode();
		}

		$oop = M('member');
		$map['account'] = I('username');
		$map['status'] = 1;
		$authInfo = $oop->where($map)->find();

		//使用用户名、密码和状态的方式进行认证
		if (!$authInfo || $authInfo['password'] != md5(I('password') . $authInfo['salt'])) {
			$this->error(L("error_pwd"));
		}

		//二因子验证
		if ($this->web_config['web_2fa_code']) {
			$g = new \Google\Authenticator\GoogleAuthenticator();
			if (!$g->checkCode($authInfo['verify'], I("twofa_code"))) {
				$this->error("002非法的登录！");
			}
		}

		session("uid", $authInfo['id']);
		//dump($_SESSION);exit;
		$authInfo['last_login_time'] = date('Y-m-d H:i:s');
		$authInfo['last_login_ip'] = get_client_ip();
		$authInfo['login_count']++;
		$oop->save($authInfo);
		$this->success(L("checkin_success"));

	}

	//登出
	public function logout() {
		session(null);
		$this->success(L("checkout_success"), U('Public/login'));
	}

	//发送确认码
	public function sendConfirmCode() {
		$data['status'] = 0;
		$data['data'] = 0;
		//如果发送过，则禁止60秒内重发
		if (cookie("confirm_code")) {
			$data['info'] = L("repeat_error");
			$this->ajaxReturn($data);
		}

		//查看管理员对应的邮件
		$list = M("member")->where(array("account" => I("username")))->find();
		//dump($list);exit;

		$verify_code = makePass(5);
		//cookie缓存
		cookie("confirm_code", $verify_code, 60);

		$body = "Your code:&nbsp;&nbsp;" . $verify_code . "<p>" . $this->web_config['web_domain'] . "</p>";

		$result = parent::postmail($list["email"], "[" . L("web_name") . "]" . L("email_verify_code"), $body);
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
?>