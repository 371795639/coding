<?php
namespace Common\Model;
use Think\Model\ViewModel;

class MemberViewModel extends ViewModel {
	public $viewFields = array(
		'Member' => ['_type' => 'LEFT'],
		'Editor' => ['_on' => "Member.type_id=Editor.ed_id"],
	);
}
?>