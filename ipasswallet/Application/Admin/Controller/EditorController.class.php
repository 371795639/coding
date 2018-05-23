<?php
namespace Admin\Controller;
use Think\Controller;

class EditorController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "editor";
		$this->key = "ed_id";
		$this->title_index = "编辑组列表";
		$this->title_details = "编辑组详情";
	}

	public function deleteHandle() {
		//如果有管理员属于某个分组，则禁止删除
		$oop = M("member");
		$con['type_id'] = I("path.2");
		if ($oop->where($con)->find()) {
			$this->error("删除前，请先解除已经分配编辑组的管理员！");
		}
		parent::deleteHandle();
	}

}