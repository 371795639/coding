<?php
function commonType($status, $status_color, $id, $color) {
	if (isset($id)) {
		return $color ? $status_color[$id] : $status[$id];
	} else {
		return $color ? $status_color : $status;
	}

}

//客户状态
function user_status($id = "", $color = false) {
	$status = L("user_status");
	$status_color = L("user_status_color");
	return commonType($status, $status_color, $id, $color);
}

//客户类型
function user_type($id = "", $color = false) {
	$status = L("user_type_arr");
	$status_color = L("user_type_arr");
	//dump($status);
	return commonType($status, $status_color, $id, $color);
}

//转账状态
function transfer_status($id = "", $color = false) {
	$status = L("transfer_status");
	$status_color = L("transfer_status_color");
	return commonType($status, $status_color, $id, $color);
}

//提现状态
function withdraw_status($id = "", $color = false) {
	$status = L("withdraw_status");
	$status_color = L("withdraw_status_color");
	return commonType($status, $status_color, $id, $color);
}

//对账状态
function statement_type($id = "", $color = false) {
	$status = L("st_type");
	$status_color = L("st_type_color");
	return commonType($status, $status_color, $id, $color);
}

//工单状态
function ticket_status($id = "", $color = false) {
	$status = L("sp_status");
	$status_color = L("sp_status_color");
	return commonType($status, $status_color, $id, $color);
}

//所有的信用卡
function creditcardList() {
	return array(
		"visa", "mastercard", "amex", "dinersclub", "discover", "unionpay", "jcb",
	);
}
