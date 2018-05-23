<?php
namespace Admin\Controller;
use Think\Controller;

class MemberController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "member";
		$this->model_view = "MemberView";
		$this->key = "id";
		$this->title_index = L('member_index');
		$this->title_details = L('member_details');
	}

	//保存
	public function updateHandle() {
		try {
			$oop = M($this->model);
			$oop->create();
			$oop->salt = makePass(20); //密码加盐值
			if (I('new_pwd')) {
				$oop->password = md5(I('new_pwd') . $oop->salt);
			}

			if ($oop->save()) {
				$this->success(L("update_success"));
			} else {
				$this->error(L("update_failed"));
			}
		} catch (\Exception $e) {
			if ($e->getCode() == 23000) {
				$this->error(L("unique_error"));
			}
		}

	}

	public function addHandle() {
		try {
			$oop = M($this->model);
			$oop->create();
			$oop->salt = makePass(20); //密码加盐值
			$oop->password = md5(I('new_pwd') . $oop->salt);
			if ($oop->add()) {
				$this->success(L("add_success"));
			} else {
				$this->error(L("add_failed"));
			}
		} catch (\Exception $e) {
			if ($e->getCode() == 23000) {
				$this->error(L("unique_error"));
			}
		}

	}

	//删除
	public function deleteHandle() {
		if (in_array(I($this->key), C("AUTH_CONFIG.AUTH_ADMIN"))) {
			$this->error(L("admin_not_delete"));
		}

		parent::deleteHandle();
	}

	//分配管理组
	public function assignRole() {
		$oop = M('authGroupAccess');
		$con['uid'] = I('uid');
		$oop->where($con)->delete();
		$oop->create();
		if ($oop->add()) {
			$this->success(L("assign_success"));
		} else {
			$this->error(L("assign_failed"));
		}

	}

}