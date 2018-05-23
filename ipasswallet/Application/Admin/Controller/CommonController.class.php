<?php
namespace Admin\Controller;

use Common\Controller\BaseController;

abstract class CommonController extends BaseController {
	//管理员信息
	protected $authInfo;
	protected $num = 35; //分页数
	//从子类中获取
	protected $model = null; //表名
	protected $model_view = null; //视图名
	protected $key = null; //排序键
	protected $title_index = null; //列表页头
	protected $title_details = null; //详情页头
	protected $title_add = null; //添加页头

	public function _initialize() {
		parent::_initialize();
		if (!session('uid')) {
			$this->redirect('Public/login');
		}

		//类库位置应该位于ThinkPHP\Library\Think\
		$AUTH = new \Think\Auth();
		$access = CONTROLLER_NAME . '/' . ACTION_NAME;
		if (in_array(session("uid"), C("AUTH_CONFIG.AUTH_ADMIN")) ||
			$access == "Index/index" || $access == "Index/top" || $access == "Index/left" || $access == "Index/right") {
			return true;
		} elseif (!$AUTH->check($access, session('uid'))) {
			$this->error(L('access_deny'));
		}

		$this->authInfo = D('MemberView')->find(session('uid'));
		if (in_array(session("uid"), C("AUTH_CONFIG.AUTH_ADMIN"))) {
			$this->authInfo['type_id'] = "";
		}

		$this->num = $this->web_config['web_table_list_count'] ? $this->web_config['web_table_list_count'] : 35;

		$this->assign('authInfo', $this->authInfo);
		//dump($this->authInfo);
	}

	//首页列表
	public function index($con = '1=1') {
		$this->model_view ? $oop = D($this->model_view) : $oop = M($this->model);
		$data = pageInfo($oop, $this->key . " desc", $con, $this->num);
		$this->assign("list", $data['list']);
		//dump($con);
		//echo $oop->_sql();
		$this->assign("page", $data['show']);
		$this->assign("main_title", $this->title_index);
		$this->display();
	}

	//CMS列表
	protected function cms_index() {
		$mist_arr = array('doc_title' => I("doc_title"));
		$accute_arr = array('doc_cat' => I("doc_cat"));
		$cookie_arr = array();
		$con = searchCon($mist_arr, $accute_arr, $cookie_arr);
		if ($this->authInfo['type_id']) {
			$con['doc_ed_id'] = $this->authInfo['type_id'];
		}
		$this->model_view ? $oop = D($this->model_view) : $oop = M($this->model);
		$data = pageInfo($oop, $this->key . " desc", $con, $this->num);
		$this->assign("list", $data['list']);
		//dump($con);
		//echo $oop->_sql();
		$this->assign("page", $data['show']);
		$this->assign("main_title", $this->title_index);
		$this->display();
	}

	//详情页
	public function details() {
		//dump($this->web_config);
		$this->model_view ? $oop = D($this->model_view) : $oop = M($this->model);
		$con[$this->key] = I('path.2');
		$list = $oop->field(true)->where($con)->find();
		//echo $oop->_sql();exit;
		$list || $this->error(L("no_record"));
		$this->assign($list);
		$this->assign("main_title", $this->title_details);
		$this->display();
	}
	//删除
	public function deleteHandle() {
		$oop = M($this->model);
		if ($oop->delete(I('path.2'))) {
			$this->success(L("delete_success"));
		} else {
			$this->error(L("delete_failed"));
		}

	}

	//保存
	public function updateHandle() {
		//dump(I());EXIT;
		try {
			$oop = M($this->model);
			$oop->create();
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

	//cms保存
	protected function cms_updateHandle() {
		$oop = M($this->model);
		$oop->create();

		if (!empty($_FILES['file']['tmp_name'])) {
			$result = parent::uploadfile($exts = array('jpg', 'gif', 'png', 'jpeg'), $thumb = true);
			if (!$result['status']) {
				$this->error($result['info']);
			}
			$oop->doc_dir = $result['info']['file']['savepath'];
			$oop->doc_img = $result['info']['file']['savename'];
		}
		//dump(I());
		//dump($result);exit;
		try {
			$oop->doc_property = serialize(I('doc_property'));
			$oop->doc_ed_id = $this->authInfo['type_id'] . "";
			if ($oop->save()) {
				$this->success(L('update_success'));
			} else {
				$this->error(L('update_failed'));
			}
		} catch (\Exception $e) {
			//dump($e);exit;
			if ($e->getCode() == 23000) {
				$this->error(L('unique_error'));
			}
		}
	}

	//添加页面
	public function add() {
		$this->assign("main_title", L("add_new"));
		$this->display();
	}

	//新增
	public function addHandle() {
		try {
			$oop = M($this->model);
			$oop->create();
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

	protected function cms_addHandle() {
		$oop = M($this->model);
		$oop->create();

		if (!empty($_FILES['file']['tmp_name'])) {
			$result = parent::uploadfile($exts = array('jpg', 'gif', 'png', 'jpeg'), $thumb = true);
			if (!$result['status']) {
				$this->error($result['info']);
			}
			$oop->doc_dir = $result['info']['file']['savepath'];
			$oop->doc_img = $result['info']['file']['savename'];
		}
		//dump(I());
		//dump($result);exit;
		try {
			$oop->doc_property = serialize(I('doc_property'));
			$oop->doc_ed_id = $this->authInfo['type_id'] . "";
			if ($oop->add()) {
				$this->success(L('add_success'));
			} else {
				$this->error(L('add_failed'));
			}
		} catch (\Exception $e) {
			//dump($e);exit;
			if ($e->getCode() == 23000) {
				$this->error(L('unique_error'));
			}
		}
	}

	//批量删除
	public function batchDelete() {
		$oop = M($this->model);
		$con[$this->key] = array("in", I('path.2'));
		if ($oop->where($con)->delete()) {
			$this->success(L("batch_delete_success"));
		} else {
			$this->error(L("batch_delete_failed"));
		}

	}

	//操作日志
	protected function weblog() {
		if ($this->authInfo['account']) {
			$data['log_admin'] = $this->authInfo['account'];
			$data['log_action'] = CONTROLLER_NAME . "/" . ACTION_NAME;
			$data['log_sql'] = M()->_sql();
			M("weblog")->add($data);
		}
	}

	/**
	 * 清空查询列表的缓存
	 */
	public function clearCache() {
		S(I("path.2"), null);
		$this->success(L("refresh_cache_success"));
	}

	// 析构函数
	public function __destruct() {
		if (ACTION_NAME != "index" &&
			ACTION_NAME != "details" &&
			ACTION_NAME != "add" &&
			ACTION_NAME != "cleancon" &&
			ACTION_NAME != "clearCache") {
			//parent::__construct(); // 调用父类的构造函数必须显示的使用parent调用父类构造函数
			$this->weblog(); //调用日志
		}
	}

}
