<?php
namespace Common\Model;
use Think\Model\ViewModel;

class ArticleViewModel extends ViewModel {
	public $viewFields = array(
		'Article' => [],
		'Category' => ['_on' => "Article.doc_cat=Category.cat_id"],
	);
}
?>