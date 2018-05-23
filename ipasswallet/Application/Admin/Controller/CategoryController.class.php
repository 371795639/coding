<?php
namespace Admin\Controller;

use Think\Controller;

class CategoryController extends CommonController {
	public function _initialize() {
		parent::_initialize();
		$this->model = "category";
		$this->key = "cat_id";
		$this->title_index = "分类";
		$this->title_details = "分类详情";
	}

	public function index() {
		//dump($list);
		$this->assign("main_title", $this->title_index);
		$arr = getCategoryList();
		//dump($arr);
		$this->assign("list", $arr);
		$this->display();
	}

	public function deleteHandle() {

	}

}
