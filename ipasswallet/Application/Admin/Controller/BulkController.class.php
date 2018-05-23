<?php
/**
 *批量操作控制器
 */
namespace Admin\Controller;
use Think\Controller;

class BulkController extends CommonController {

	/**
	 *批量操作所有功能页面
	 */
	public function index() {
		$this->assign("main_title", L('bulk_operate'));
		$this->display();
	}

	/**
	 *上传文件通用
	 *@return array 返回上传CSV文件的数组
	 */
	private function savefile() {
		$result = $this->uploadfile(array("csv"));
		if (!$result['status']) {
			$this->error($result['info']);
		}

		//dump($result);exit;
		$file = "./Public/Uploads/" . $result['info']['csv_file']['savepath'] . $result['info']['csv_file']['savename'];
		$obj = new \mnshankar\CSV\CSV();
		$csv = $obj->with($file)->toArray();
		//dump($csv);exit;
		unlink($file);
		return $csv;
	}

	/**
	 *批量修改交易状态
	 */
	public function bulkChangeStatus() {
		$this->assign("main_title", L('bulk_operate'));
		if (I("order_status") == "") {
			$this->error("Order status cannot be Null");
		}

		$csv = $this->savefile();
		//dump($csv);
		foreach ($csv as $key => $value) {
			//echo $value['']
			$oop = new \Common\Common\OrderProcess($value['order_id']);
			switch (I("order_status")) {
			case 2:
				$state = $oop->toApprove();
				break;
			case 3:
				$state = $oop->toAuthorize();
				break;
			case 4:
				$state = $oop->toCapture();
				break;
			case 5:
				$state = $oop->toVoid();
				break;
			case 6:
				$state = $oop->toDecline();
				break;
			case 7:
				$state = $oop->toPendingRefund($value["amount"]);
				break;
			case 8:
				$state = $oop->toRefund();
				break;
			case 9:
				$state = $oop->toChargeback($value['amount']);
				break;
			default:
				$state = 0;
			}

			$csv[$key]['state'] = $state;
			$csv[$key]['old_status'] = $oop->order_list['order_status'];
			$csv[$key]['new_status'] = I("order_status");

			//dump($csv[$key]);
		}
		//dump($csv);
		$this->assign("list", $csv);
		$this->display();
	}

/**
 *批量添加通道
 */
	public function bulkAddChannel() {
		$csv = $this->savefile();
		//dump($csv);
		$oop = M("channels");
		$result = $oop->addAll($csv);
		if ($result) {
			$this->success(L("add_success"));
		} else {
			$this->error(L("add_failed"));
		}

	}

/**
 *批量添加物流公司
 */
	public function bulkAddDelivery() {
		$csv = $this->savefile();
		//dump($csv);
		$oop = M("tracking");
		$result = $oop->addAll($csv);
		if ($result) {
			$this->success(L("add_success"));
		} else {
			$this->error(L("add_failed"));
		}

	}

/**
 * 批量添加黑名单
 */
	public function bulkAddBlacklist() {
		$csv = $this->savefile();
		//dump($csv);
		$oop = M("blacklist");
		$result = $oop->addAll($csv);
		if ($result) {
			$this->success(L("add_success"));
		} else {
			$this->error(L("add_failed"));
		}

	}

}