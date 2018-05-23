<?php
namespace Admin\Controller;
use Think\Controller;

class GroupController extends CommonController {

	public function _initialize() {
		parent::_initialize();
		$this->model = "authGroup";
		$this->key = "id";
		$this->title_index = L('group_index');
		$this->title_details = L('group_details');
	}

	//分配权限
	public function assignAccess() {
		$group = M($this->model);
		$con['id'] = I('id');
		$group_list = $group->where($con)->find();
		if (!$group_list) {
			$this->error(L("group_not_found"));
		}
		$this->assign($group_list);

		$oop = M('authRule');
		$list = $oop->group("module")->select();

		//dump($list);

		//$result = group_to_rule(I("id"), "管理员");
		//dump($result);
		//dump($list);
		$this->assign("list", $list);
		$this->assign("main_title", L("assign_access"));
		$this->display();
	}

	public function updateRules() {
		//dump(I());
		$oop = M($this->model);
		$con[$this->key] = I($this->key);
		$data['rules'] = I('check_child');
		//dump($data);exit;

		if ($oop->where($con)->save($data)) {
			$this->success(L("update_success"));
		} else {
			$this->error(L("update_failed"));
		}

	}

}
?>