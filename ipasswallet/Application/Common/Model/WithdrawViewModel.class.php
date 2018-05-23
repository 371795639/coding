<?php
namespace Common\Model;

use Think\Model\ViewModel;

class WithdrawViewModel extends ViewModel {
	public $viewFields = array(
		'Withdraw' => array('_table' => WITHDRAWVIEW, '_type' => "LEFT"),
		'Wdbank' => array('_on' => 'Withdraw.wd_bank_id=Wdbank.withdraw_bank_id'),
	);
}
