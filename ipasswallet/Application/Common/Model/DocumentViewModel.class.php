<?php
namespace Common\Model;
use Think\Model\ViewModel;

class DocumentViewModel extends ViewModel {
	public $viewFields = array(
		'Document' => [],
		'Category' => ['_on' => "Document.doc_cat=Category.cat_id"],
	);
}
?>