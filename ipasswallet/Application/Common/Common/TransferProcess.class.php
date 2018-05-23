<?php
/**
 *订单处理类，包括订单各状态之间的变化，包括正常的订单变化流程和魔术方法（无痕变动）
 */
namespace Common\Common;

class TransferProcess {
	private $ts_id; //需要操作的转账订单号
	private $ts_oop; //转账订单对象
	private $ts_list; //当前转账数据

	private $transfer_md = "transfer"; //订单表
	private $tsdetails_md = "tsdetails"; //订单历史表

	//__set()方法用来设置私有属性
	// public function __set($name,$value){
	// $this->$name = $value;
	// }

	//__get()方法用来获取私有属性
	public function __get($name) {
		return $this->$name;
	}

	/**
	 *构造方法
	 */
	public function __construct($ts_id) {
		$this->ts_id = $ts_id;
		$this->ts_oop = M($this->transfer_md);
		//启动事务
		$this->ts_oop->startTrans();
		$this->ts_list = $this->ts_oop->lock(true)->field(true)->where(array("ts_id" => $this->ts_id))->find();
		if (!$this->ts_list || $this->ts_list['ts_amount'] <= 0) {
			//如果没有订单，则提交事务，构造失败
			$this->ts_oop->commit();
			exit("error construct");
		}
	}

	//已付款，待确认（担保交易特有）
	public function toPendingConfirmed() {
		if ($this->ts_list['ts_type'] != 0) {
			return false;
		}

		if ($this->ts_list['ts_status'] != 1 && $this->ts_list['ts_status'] != 5) {
			return false;
		}

		$ts_data['ts_status'] = 2;

		if ($this->ts_list['ts_status'] == 1) {
			$result0 = $this->normalProcess($ts_data, "Paid,not confirmed", false);
			//3.金额变化和对账单
			$result3 = createStatement($this->ts_list['ts_sender_id'], 2, $this->ts_list['ts_amount'], "Paid,transfer # " . $this->ts_id, 0);
		} else {
			$result0 = $this->normalProcess($ts_data, "Refund Canceled", false);
			$result3 = true;
		}

		if ($result0 && $result3) {
			$this->ts_oop->commit();
			return true;
		} else {
			$this->ts_oop->rollback();
			return false;
		}
	}

	//支付完成
	public function toComplete() {
		if ($this->ts_list['ts_status'] != 1 && $this->ts_list['ts_status'] != 2 && $this->ts_list['ts_status'] != 7 && $this->ts_list['ts_status'] != 8) {
			return false;
		}

		$ts_data['ts_status'] = 3; //已完成
		$result0 = $this->normalProcess($ts_data, "Transfer Completed", false);

		if ($this->ts_list['ts_status'] == 7 || $this->ts_list['ts_status'] == 8) {
			$result3 = $result4 = true;
		} else {

			if ($this->ts_list['ts_type'] == 0) {
				//3.收款方金额增加和对账单
				$result3 = createStatement($this->ts_list['ts_receiver_id'], 3, $this->ts_list['ts_amount'] - $this->ts_list['ts_fee'], "Complete,transfer # " . $this->ts_id, $this->ts_list['ts_fee']);
				$result4 = true;

			} else {
				//付款方金额减少，收款方金额增加
				$result3 = createStatement($this->ts_list['ts_sender_id'], 2, $this->ts_list['ts_amount'], "Paid,transfer # " . $this->ts_id, 0);
				$result4 = createStatement($this->ts_list['ts_receiver_id'], 3, $this->ts_list['ts_amount'] - $this->ts_list['ts_fee'], "Complete,transfer # " . $this->ts_id, $this->ts_list['ts_fee']);

			}
		}

		if ($result0 && $result3 && $result4) {
			$this->ts_oop->commit();
			return true;
		} else {
			$this->ts_oop->rollback();
			return false;
		}
	}

	//取消
	public function toCancel() {
		if ($this->ts_list['ts_status'] != 1) {
			return false;
		}
		$ts_data['ts_status'] = 4; //已取消
		return $this->normalProcess($ts_data, "Transfer Canceled");
	}

	//待退款
	public function toPendingRefund($remark = "Request a refund") {
		if ($this->ts_list['ts_status'] != 2) {
			return false;
		}
		$ts_data['ts_status'] = 5; //等待退款
		return $this->normalProcess($ts_data, $remark);
	}

	//已退款
	public function toRefund() {
		if ($this->ts_list['ts_status'] != 5 || $this->ts_list['ts_status'] != 7) {
			return false;
		}

		$ts_data['ts_status'] = 5; //等待退款
		$result0 = $this->normalProcess($ts_data, "Transfer Refunded", false);

		if ($this->ts_list['ts_status'] == 5) {
			//资金退回到付款方，付款方资金增加
			$result3 = createStatement($this->ts_list['ts_sender_id'], 4, $this->ts_list['ts_amount'], "Refunded,transfer # " . $this->ts_id, 0);
			if ($result0 && $result3) {
				$this->ts_oop->commit();
				return true;
			} else {
				$this->ts_oop->rollback();
				return false;
			}
		} else {
			//收款方资金减少
			$result4 = createStatement($this->ts_list['ts_receiver_id'], 5, $this->ts_list['ts_amount'], "Refunded,transfer # " . $this->ts_id, 0);

			//付款方资金增加
			$result3 = createStatement($this->ts_list['ts_sender_id'], 4, $this->ts_list['ts_amount'], "Refunded,transfer # " . $this->ts_id, 0);
			if ($result0 && $result3 && $result4) {
				$this->ts_oop->commit();
				return true;
			} else {
				$this->ts_oop->rollback();
				return false;
			}

		}

	}

	//到争议
	public function toDispute($remark = "") {
		if ($this->ts_list['ts_status'] != 3 || $remark == "") {
			return false;
		}
		$ts_data['ts_status'] = 7; //争议
		return $this->normalProcess($ts_data, $remark);
	}

	//异常
	public function toException($remark = "") {
		if ($remark == "") {
			return false;
		}
		$ts_data['ts_status'] = 8; //异常
		return $this->normalProcess($ts_data, $remark);
	}

	//失败
	public function toDeclined($remark = "") {
		if ($remark == "" || $this->ts_list['ts_status'] != 1) {
			return false;
		}
		$ts_data['ts_status'] = 9; //失败
		return $this->normalProcess($ts_data, $remark);
	}

	//修改交易状态，但不影响收付款双方账户的操作方法
	private function normalProcess($ts_data, $remark, $type = true) {
		//1.修改订单状态
		$result1 = $this->magicProcess($ts_data);
		//2.写入历史
		$result2 = $this->addHistory($ts_data['ts_status'], $remark);

		if ($type) {
			if ($result1 && $result2) {
				$this->ts_oop->commit();
				return true;
			} else {
				$this->ts_oop->rollback();
				return false;
			}
		} else {
			return $result1 && $result2;
		}
	}

	/**
	 *魔术方法,该方法处理交易的字段变化，并且变化不会影响顾客账户的变化，也不会影响其他表
	 *@param array $ts_data 转账交易数据数组
	 *@param bool 是否事务提交，默认否
	 *@return int  如果操作成功，返回订单号，如果操作失败，返回0
	 */
	public function magicProcess($ts_data, $commit = false) {
		$result = $this->ts_oop->where(array("ts_id" => $this->ts_id))->save($ts_data);
		if ($commit && $result) {
			$this->ts_oop->commit();
		}

		if ($result) {
			return $this->ts_id;
		} else {
			return 0;
		}

	}

	/**
	 * 写入转账历史
	 *@param int $order_status 订单状态
	 *@param text $remark 备注，一般为空，如果交易失败，则为交易失败的原因
	 */
	private function addHistory($ts_status, $remark = "") {
		$oop = M($this->tsdetails_md);
		$data['td_ts_id'] = $this->ts_id;
		$data['td_status'] = $ts_status;
		$data['td_remark'] = $remark;
		if ($oop->add($data)) {
			return $this->ts_id;
		} else {
			return 0;
		}
	}

}
