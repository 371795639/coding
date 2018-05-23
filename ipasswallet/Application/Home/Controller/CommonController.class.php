<?php
//前台页面公用类
namespace Home\Controller;
use Common\Controller\BaseController;

abstract class CommonController extends BaseController {

	protected $num = 15; //分页数
	//从子类中获取
	protected $model = null; //表名
	protected $model_view = null; //视图名
	protected $key = null; //排序键
	protected $title_index = null; //选项卡标题

	//域名相关配置
	protected $brand_config;

	public function _initialize() {
		//继承前面的配置
		parent::_initialize();
		//调用后台配置，启用设置主题
		C("DEFAULT_THEME", $this->web_config['web_theme']);
		//配置多语言
		//cookie("think_language", $this->web_config['web_default_lang']);
		$this->web_config['web_isopen'] || exit($this->web_config['web_close_desc']);
	}

	public function home($con = '1=1') {
		$this->model_view ? $oop = D($this->model_view) : $oop = M($this->model);
		$data = pageInfo($oop, $this->key . " desc", $con, $this->num);
		//echo $oop->_sql();
		//dump($data['list']);
		$this->assign("list", $data['list']);
		$this->assign("page", $data['show']);
		$this->assign("total_num", $data['total_num']);
		$this->assign("main_title", $this->title_index);
		$this->display();
	}
	//详情页
	public function details($con) {
		$this->model_view ? $oop = D($this->model_view) : $oop = M($this->model);
		$list = $oop->field(true)->where($con)->find();
		$list || $this->error(L("no_record"));
		$this->assign($list);
		$this->assign("main_title", $this->title_index);
		$this->display();
	}

	//保存
	public function updateHandle($con) {
		//dump(I());EXIT;
		try {
			$oop = M($this->model);
			$oop->create();
			if ($oop->where($con)->save()) {
				$this->success(L("update_success"));
			} else {
				$this->error(L("update_failed"));
			}

		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
	}

	//新增
	public function addHandle($data = array()) {
		try {
			$oop = M($this->model);
			if ($oop->add($data)) {
				$this->success(L("add_success"));
			} else {
				$this->error(L("add_failed"));
			}

		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
	}

	//删除
	public function deleteHandle($con) {
		try {
			$oop = M($this->model);
			if ($oop->where($con)->delete()) {
				$this->success(L("delete_success"));
			} else {
				$this->error(L("delete_failed"));
			}

		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}

	}

	/**
	 * 前台用户登录日志
	 *@param $cid int 用户ID
	 *@param $type int 登录的类型 1个人 2 企业
	 *@param $log_status int 登录的状态0 失败 1成功
	 *@param $log_desc string 登录的描述
	 */
	protected function addLog($cid, $type = 1, $log_status = 0, $log_desc = "Default") {
		$data['log_client_id'] = $cid;
		$data['log_ip'] = get_client_ip();
		$data['log_user_agent'] = $_SERVER['HTTP_USER_AGENT'] . "";
		$data['log_source'] = $_SERVER['HTTP_REFERER'] . "";
		$data['log_type'] = $type;
		$data['log_lang'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "";
		$data['log_status'] = $log_status;
		$data['log_desc'] = $log_desc;
		//dump($data);
		try {
			$result = M("loginlog")->add($data);
			if ($result) {
				return $result;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}
	}

}
