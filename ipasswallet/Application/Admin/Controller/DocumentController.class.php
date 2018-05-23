<?php
namespace Admin\Controller;

use Think\Controller;

class DocumentController extends CommonController {
	public function _initialize() {
		parent::_initialize();
		$this->model = "document";
		$this->model_view = "DocumentView";
		$this->key = "doc_id";
		$this->title_index = "单页列表";
		$this->title_details = "单页详情";
	}
	public function index() {
		parent::cms_index();
	}

	public function addHandle() {
		parent::cms_addHandle();
	}

	public function updateHandle() {
		parent::cms_updateHandle();
	}

	public function deleteHandle() {
		//查看是否属于自己的文章
		if ($this->authInfo['type_id']) {
			$con['doc_ed_id'] = $this->authInfo['type_id'];
			$con['doc_id'] = I("path.2");
			$list = M($this->model)->where($con)->find();
			if (!$list) {
				$this->error("不允许删除，操作禁止。");
			}
		}
		parent::deleteHandle();
	}

}
