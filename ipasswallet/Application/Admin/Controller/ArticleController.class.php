<?php

namespace Admin\Controller;

class ArticleController extends CommonController {
	public function _initialize() {
		parent::_initialize();
		$this->model = 'article';
		$this->model_view = 'ArticleView';
		$this->key = 'doc_id';
		$this->title_index = '文章列表';
		$this->title_details = '文章详情';
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
}