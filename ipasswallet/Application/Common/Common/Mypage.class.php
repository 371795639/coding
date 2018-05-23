<?php
namespace Common\Common;
class Mypage {

public $total_num;//总记录数
public $list_rows='25';//每页记录数
public $first_row;//起始行数
public $page_num;//页面数量
protected $now_page;//当前页面
protected  $pre_page;//上一页
protected  $next_page;//下一页

protected $page_class_name="pagination";//外部DIV的类名
protected $page_inner_class_name="";//内部ul的类名
protected $current_class_name="current";//当前页面类名
protected $disabled_class_name="disabled";

//构造函数
public function __construct($total_num,$list_rows='',$page_class_name='',$page_inner_class_name='',$current_class_name='',$disabled_class_name=''){
$this->total_num=$total_num;
$this->list_rows=$list_rows==""?$this->list_rows:$list_rows;
$this->page_num=ceil($this->total_num/$this->list_rows);


$this->now_page      =   !empty($_GET['p'])?intval($_GET['p']):1;
if($this->now_page<1){
$this->now_page  =   1;
}elseif(!empty($this->total_num) && $this->now_page>$this->page_num) {
$this->now_page  =   $this->page_num;
}


$this->pre_page=$this->now_page-1;
$this->next_page=$this->now_page+1;


$this->first_row = $this->list_rows*($this->now_page-1);
$page_class_name && $this->page_class_name=$page_class_name;
$page_inner_class_name && $this->page_inner_class_name=$page_inner_class_name;
$current_class_name && $this->current_class_name=$current_class_name;
$disabled_class_name && $this->disabled_class_name=$disabled_class_name;

}

//页面显示
public function show(){

//dump($this->get_parse(3));

//如果总数为0，则返回为空
if(0 == $this->total_num) return '';
if(1 == $this->page_num) return '';

//$pre_page=U('',array("p"=>$this->pre_page));
//$next_page=U('',array("p"=>$this->next_page));


$show="<div class='".$this->page_class_name."'><ul class='".$this->page_inner_class_name."'>
	";
//上一页
if(1!=$this->now_page)
	$show.="<li><a href='".$this->get_parse($this->pre_page)."'> &laquo; </a></li>";

/*****************************
中间显示部分开始
*****************************/


if($this->page_num<=10){//如果最多只有10页
for($i=1;$i<=$this->page_num;$i++){
if($i==$this->now_page)
	$show.="<li class='".$this->current_class_name."'><span>".$i."</span></li>";
else
	$show.="<li><a href='".$this->get_parse($i)."'>".$i."</a></li>";
}

/***************************************************/

}else{//如果不止10页


if($this->now_page<=6){
for($i=1;$i<=10;$i++){
if($i==$this->now_page)
	$show.="<li class='".$this->current_class_name."'><span>".$i."</span></li>";
else
	$show.="<li><a href='".$this->get_parse($i)."'>".$i."</a></li>";
}

}else{
if($this->page_num-$this->now_page>=4 )
	$start=$this->now_page-5;
else
	$start=$this->page_num-9;
	
//如果当前页数大于6，应该加上第一页
$show.="<li><a href='".$this->get_parse(1)."'>1</a></li><li><span>...</span></li>";	

	
for($i=1;$i<=10;$i++){
$show_page=$i+$start-1;

if($show_page==$this->now_page)
	$show.="<li class='".$this->current_class_name."'><span>".$show_page."</span></li>";
else
	$show.="<li><a href='".$this->get_parse($show_page)."'>".$show_page."</a></li>";

}

}

//如果总页数减去当前页大于等于4，应加上最后一页
if($this->page_num-$this->now_page>4 ){
	$show.="<li><span>...</span></li><li><a href='".$this->get_parse($this->page_num)."'>".$this->page_num."</a></li>";
}



}

/*****************************
中间显示部分结束
*****************************/

//下一页
if($this->page_num!=$this->now_page)
	$show.="<li><a href='".$this->get_parse($this->next_page)."'> &raquo; </a></li>";

$show.="<li><span>Total:".$this->total_num."</span></li></ul></div>";	
	
return $show;
}

/**
 * 根据url返回此url的get参数（以数据形式返回）
 * @param unknown_type $url
 */
private function get_parse($p){
 $var =  !empty($_POST)?$_POST:$_GET;
if(empty($var))
   $parameter  =   array();
else
   $parameter  =   $var;
		   
   foreach($parameter as $key=>$value){
   if($key!="_URL_" && $key!="__hash__" && $value!="")
		$params[$key]=$value;
   }
   
   $params['p']=$p;			
   $url=U('',$params);
return $url;   
}


}
?>