<?php
/**
 *商户账户主控制器
 */
namespace Home\Controller;
use Think\Controller;

class UsersController extends CommonController {

	//存储用户信息
	protected $user_info;

	public function _initialize() {
		parent::_initialize();
		$con['user_id'] = session('user_id');
		$con['user_status'] = array("neq", 3);
		$this->user_info = M("users")->where($con)->find();
		if ($this->user_info) {
			$this->assign("user_info", $this->user_info);
		} else {
			session("user_id", null);
			$this->redirect("Public/login");
		}
	}

/**
 *登录成功后的主页
 */
	public function index() {
		$this->display();
	}

	/**
	 * 修改密钥
	 */
	public function updateKey() {
		$this->sandboxCheck(true);
		$data['status'] = 0;
		$data['info'] = "Now allowed";
		$this->ajaxReturn($data);
		$data['mh_id'] = $this->merchant_info['mh_id'];
		$data['mh_api_key'] = makePass(25);
		if (M("merchant")->save($data)) {
			$data['status'] = 1;
			$data['info'] = L("action_success");
		} else {
			$data['status'] = 0;
			$data['info'] = L("action_failed");
		}
		$this->ajaxReturn($data);
	}

	protected function sandboxCheck($json = false) {
		if (!$this->merchant_info["mh_islive"]) {
			if ($json) {
				$data['status'] = 0;
				$data['info'] = L("sandbox_forbidden_tip");
				$this->ajaxReturn($data);
			} else {
				$this->error(L("sandbox_forbidden_tip"));
			}
		}
	}

/**
 *修改登录密码
 */
	public function modifyPwd() {
		$this->sandboxCheck();

		if (md5(I("old_pwd") . $this->merchant_info['mh_salt']) != $this->merchant_info['mh_pwd']) {
			$this->error(L("invalid_pwd"));
		}

		if (I("old_pwd") == I("new_pwd")) {
			$this->error(L("same_pwd"));
		}

		if (I("new_pwd") != I("confirm_new_pwd")) {
			$this->error(L("pwd_not_match"));
		}

		$con['mh_id'] = $this->merchant_info['mh_id'];
		$data['mh_pwd'] = md5(I("new_pwd") . $this->merchant_info['mh_salt']);
		$result = M("merchant")->where($con)->save($data);
		if ($result) {
			$this->success(L("action_success"));
		} else {
			$this->error(L("action_failed"));
		}

	}

	//操作余额之前的准备
	/**
	 * @parameter $pay_pwd 支付密码
	 * @parameter $pay_amt 支付金额
	 * @parameter $pay_type 支付方式 0转账 1提现
	 */
	protected function premoney($pay_pwd, $pay_amt, $pay_type = 0) {

		$money_reg = "/^(([1-9]\d*)(\.\d{1,2})?)$|^(0\.0?([1-9]\d?))$/";
		//货币正则表达式
		if (!preg_match($money_reg, $pay_amt)) {
			$this->error(L("invalid_amt_format"));
		}

		//支付密码是否正确
		if (MD5($pay_pwd . $this->user_info['user_salt']) != $this->user_info['user_pay_pwd']) {
			$this->error(L("invalid_pay_pwd"));
		}
		//金额超过余额
		if ($pay_amt > $this->user_info['user_balance_usd']) {
			$this->error(L("max_amt_limit"));
		}
		//最小金额限制
		if (($pay_type == 0 && $pay_amt < 5) || ($pay_type == 1 && $pay_amt < $this->user_info['user_min_withdraw'])) {
			$this->error(L("min_amt_limit"));
		}

		return number_format($pay_amt, 2, ".", "");

	}

/**
 *修改支付密码
 */
	public function modifyPaypwd() {
		$this->sandboxCheck();

		//对比旧的支付密码是否正确
		if (md5(I("old_pwd") . $this->merchant_info['mh_salt']) != $this->merchant_info['mh_paypwd']) {
			$this->error(L("invalid_pwd"));
		}

		//新密码与旧密码是否一样
		if (I("old_pwd") == I("new_pwd")) {
			$this->error(L("same_pwd"));
		}

		//两次密码是否一致
		if (I("new_pwd") != I("confirm_new_pwd")) {
			$this->error(L("pwd_not_match"));
		}

		//邮箱验证码是否正确
		if (I("verify") != cookie("email_verify")) {
			$this->error(L("invalid_verify"));
		}

		$con['mh_id'] = $this->merchant_info['mh_id'];
		$data['mh_paypwd'] = md5(I("new_pwd") . $this->merchant_info['mh_salt']);
		$result = M("merchant")->where($con)->save($data);
		if ($result) {
			$this->success(L("action_success"));
		} else {
			$this->error(L("action_failed"));
		}

	}

	public function mailTo() {
		$data['status'] = 0;
		$data['data'] = 0;

		if (cookie("email_verify")) {
			$data['info'] = L("repeat_error");
			$this->ajaxReturn($data);
		}

		$verify_code = makePass(5);
		//cookie保存60秒
		cookie("email_verify", $verify_code, 120);

		$body = "Your code:" . $verify_code . "<br>" . L("change_pwd_verify_code_info") . "<p>" . $this->web_config['web_domain'] . "</p>";

		$result = parent::postmail($this->merchant_info['mh_email'], "[" . L("web_name") . "]" . L("email_verify_code"), $body);
		if ($result) {
			$data['status'] = 1;
			$data['info'] = L("send_success");
			$this->ajaxReturn($data);
		} else {
			$data['info'] = L("send_error");
			$this->ajaxReturn($data);
		}
	}

	//生成CSV,继承base类的方法，但是商户可以控制开关
	protected function createCsv($sql, $arr, $csv_name) {
		parent::createCsv($sql, $arr, $csv_name);
		$this->assign("download_flag", 1);
	}

}
