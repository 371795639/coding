<?php
namespace Admin\Controller;
use Think\Controller;
class DBController extends CommonController {

public function index(){
        $db=new \Common\Common\Rdatabase();
		$this->assign($db->table_data);
		$this->assign("main_title", L('db_backup'));
        $this->display();
    }
 
  public function export(){
	  $t1 = microtime(true);
	  //dump(I("path.2"));
	  $include_tables=explode(",",I("path.2"));
	  //dump($include_tables);
	  //exit;
	  $result=parent::backup($include_tables);
	  $t2 = microtime(true);
      $time_int='耗时'.round($t2-$t1,3).'秒';
	  
	  if($result)
		 $this->redirect('DB/index', '', 5, $time_int.',备份成功。页面跳转中...');
	 else
		 $this->redirect('DB/index', '', 5, $time_int.',备份失败。页面跳转中...');
	  
	  // $db=new \Common\Common\Rdatabase();
	  // $db->backup_dir="./Public/dbbackup/".date("Y-m-d");
	  // $result=$db->backall();
	  // dump($result);
  }
  
 }
    
