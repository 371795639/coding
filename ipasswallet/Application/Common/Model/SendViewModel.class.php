<?php
namespace Common\Model;

use Think\Model\ViewModel;

class SendViewModel extends ViewModel {
	public $viewFields = array(
		'Transfer' => array(),
		'Users' => array('_on' => 'Transfer.ts_receiver_id=Users.user_id'),
	);
}
