<?php
namespace Common\Model;

use Think\Model\ViewModel;

class RequestViewModel extends ViewModel {
	public $viewFields = array(
		'Transfer' => array(),
		'Users' => array('_on' => 'Transfer.ts_sender_id=Users.user_id'),
	);
}
