<?php
/**
 *测试endpoint类.
 */
namespace Home\Controller;

class TestendpointController extends CommonController {

	/**
	 *测试页面.
	 */
	public function index() {
		if (cookie("access_token")) {
			//echo create_guid();
			$this->display();
		} else {
			$this->display("access");
		}

	}

	/**
	 * 授权操作
	 */
	public function auth() {
		$oop = M('member');
		$map['account'] = I('usr_name');
		$map['status'] = 1;
		$authInfo = $oop->where($map)->find();

		//使用用户名、密码和状态的方式进行认证
		if (!$authInfo) {
			$this->error("001非法授权！");
		}

		//二因子验证

		$g = new \Google\Authenticator\GoogleAuthenticator();
		if (!$g->checkCode($authInfo['verify'], I("usr_pwd"))) {
			$this->error("002非法授权！");
		}

		cookie("access_token", $map['account']);
		$this->success("授权成功！");

	}

	/**
	 * 取消授权
	 */
	public function voidAuth() {
		cookie("access_token", null);
		$this->redirect('Testendpoint/index', array(), 3, '页面跳转中...');
	}

	/**
	 *网关接口提交处理，包括生成加密字符串和提交接口处理功能.
	 */
	public function test() {
		if (I("cn_id")) {
			//网站ID为1 修改通道模板ID
			$data['site_id'] = 1;
			$data['site_channel_tpl'] = I("cn_id");
			$data['site_channel_tpl1'] = 0;
			$data['site_channel_tpl2'] = 0;
			$data['site_channel_tpl3'] = 0;
			M("site")->save($data);
		}

		switch (I('type')) {
		case 1:
			//mid+site_id+oid+order_amount+order_currency+site_key
			$oop = M('merchant');
			$list = $oop->find(I('mid'));
			$list || $this->error('网站记录不存在！');
			//加密
			$plain_txt = I('mid') . I('site_id') . I('oid') . I('order_amount') . I('order_currency') . $list['mh_api_key'];
			$input_txt = hash('sha256', $plain_txt);
			echo 'Origin:<hr>' . $plain_txt . '<hr>hash info:<hr>' . $input_txt;
			break;
		case 2:
			A("Gateway")->securepay();
			break;
		case 4:
			//$this->redirect("Gateway/paygates");
			A("Gateway")->paygates();
			break;
		default:
			header('Content-type: application/json');
			$oop = new \boss420\common\Directpaykit(
				I("mid"),
				I("site_id"),
				I("api_key"),
				$this->web_config['web_domain']
			);
			$arr = [
				'oid' => I('oid'),
				'order_amount' => I('order_amount'),
				'order_currency' => I('order_currency'),
				'card_no' => I('card_no'),
				'card_cvv' => I('card_cvv'),
				'card_ex_year' => I('card_ex_year'),
				'card_ex_month' => I('card_ex_month'),
				'bill_email' => I('bill_email'),
				'bill_phone' => I('bill_phone'),
				'bill_country' => I('bill_country'),
				'bill_state' => I('bill_state'),
				'bill_city' => I('bill_city'),
				'bill_street' => I('bill_street'),
				'bill_zip' => I('bill_zip'),
				'bill_firstname' => I('bill_firstname'),
				'bill_lastname' => I('bill_lastname'),
				'syn_url' => I('syn_url'),
				'asyn_url' => I('asyn_url'),
				'uuid' => I("uuid"),
			];
			$result = $oop->sendGateway($arr);
			echo $result;
		}
	}

	/**
	 * 周期订单测试.
	 */
	public function testdirectrebill() {
		$order = D('OrdersView');
		$con['order_id'] = I('pid');
		$order_list = $order->where($con)->find();
		if (!$order_list) {
			$this->error('原交易不存在');
		}
		//dump($order_list);

		require_once './Application/Common/Common/Directpaykit.class.php';
		$oop = new \Directpaykit($order_list['mh_id'],
			$order_list['site_id'],
			$order_list['mh_api_key'],
			$this->web_config['web_domain']);
		$arr = array(
			'pid' => I('pid'),
			'oid' => I('oid'),
			'order_amount' => I('order_amount'),
			'order_currency' => I('order_currency'),
		);

		$result = $oop->sendRebill($arr['pid'], $arr['oid'], $arr['order_amount'], $arr['order_currency']);
		$arr_result = json_decode($result, true);
		$this->ajaxReturn($arr_result);
	}

	/**
	 * getInfo endpoint测试
	 */
	public function testgetInfo() {
		//dump(I());
		$list = M("merchant")->find(I("mid"));
		if (!$list) {
			exit("merchant not found");
		}
		switch (I("type")) {
		case 1: //加密
			$str = I("mid") . I("site_id") . I("oid") . I("mh_oid") . $list["mh_api_key"];
			$hash_info = hash("sha256", $str);
			echo $str . "<br>hash:<br>" . $hash_info;
			break;
		case 2: //接口提交
			A("Openapi/Orders")->getInfo(I("mid"), I("site_id"), I("oid"), I("mh_oid"), I("hash_info"));
			break;
		default: //direct kit提交
			header('Content-type: application/json');
			$oop = new \boss420\common\Directpaykit(
				I("mid"), // your merchant_id
				I("site_id"), //your site_id
				$list["mh_api_key"], //your api_key
				$this->web_config['web_domain']
			);
			$result = $oop->getOrderInfo(I("oid"), I("mh_oid"));
			echo $result;
		}
	}

}
