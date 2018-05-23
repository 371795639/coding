<?php 
/**
 * 数据库备份类，本类需要配合thinkphp框架使用
 * @author Jan
 * @copyright  Copyright (c) 2015 ruinet-studio.com
 * @version 1.0
 * 创建时间： 2015年11月17日
*/
namespace Common\Common;
class Rdatabase{

private $size=500;//分卷大小，默认2M
private $backup_dir="./Public/dbbackup/";//备份文件存储的路径	
private $table_data;
private $file_name;

//__set()方法用来设置私有属性
public function __set($name,$value){
$this->$name = $value;
if(!is_dir($this->backup_dir))
	mkdir ( $this->backup_dir, 0777, true ) or die ( 'Error create the directory' );

	$this->file_name =$this->backup_dir."/".time().".sql";			
}
//__get()方法用来获取私有属性
public function __get($name){
return $this->$name;
} 

/**
*构造函数
*/
public function __construct(){
        $M = M();
        $tabs = $M->query('SHOW TABLE STATUS');
        $total = 0;
        foreach ($tabs as $k => $v) {
		   $tabs[$k]['size'] = $this->byteFormat($v['data_length'] + $v['index_length']);
            $total+=$v['data_length'] + $v['index_length'];
        }
        $this->table_data["list"]= $tabs;
        $this->table_data["total"] =$this->byteFormat($total );
        $this->table_data["tables"]= count($tabs);
}
/**
*将表转化为 SQL语句
*/
public function table2sql($table){  
		$M=M();
		$tabledump = "DROP TABLE IF EXISTS $table;\n";  
		$result = $M->query("SHOW CREATE TABLE $table");  
		return $tabledump.$result[0]['create table']."\n\n";
} 

/**
*显示一张表的所有字段属性列表
*/ 
public function showFields($table){
	    $M=M();
		$result=$M->query("show full fields from $table");
		return $result;  	
}

/**
*将表字段属性序列化
*/
public function lineFields($table){
	foreach($this->showFields($table) as $value){
		$str.="`".$value['field']."`";
		if(end($this->showFields($table)) != $value)
			$str.=",";
	}
	return "(".$str.")";
}


/**
*将一张表中数据取出来存入文件中
*/
public function data2sql($table) {  
	$M=M();
	$count=$M->query("select count(*) as total from $table");
	$page=ceil($count[0]['total']/5000);
	for($i=0;$i<$page;$i++){
		$str="\n--\n";
		$str.="-- 转存表中的数据 `".$table."`";
		$str.="\n--\n";
		$str.="insert into $table ".$this->lineFields($table);
		$start=$i*20;
		$list=$M->query("select * from $table limit $start,5000");
		//dump($M->_sql());
		$sql="";
		foreach($list as $value){
			$str1="(";	
			foreach($this->showFields($table) as $v){
				$str1.="'".$value[$v['field']]."'";
				if(end($this->showFields($table)) != $v)
					$str1.=",";
				else
					$str1.=")";
			}
			
		if(end($list)==$value)
			$str1.=";\n";
		else
			$str1.=",\n";
		
		$sql.=$str1;
		}
		$sql=$str." values \n".$sql;
		$this->createFile($sql);
	}
} 


/**
*备份所有的数据库
*/	
public function backall(){	
	
	foreach($this->table_data['list'] as $value){
	$this->data2sql($value['name']);	
	}
	//return $this->data2sql("ips_zones");
}

/**
*创建新的SQL文件
*/
private function createFile($sql){
	//这里开始写入
   $fh = fopen($this->file_name, "a"); //w从开头写入 a追加写入
   fwrite($fh, $sql);
   fclose($fh);
   if($this->byteFormat(filesize($this->file_name)) > $this->size )
	   	$this->file_name =$this->backup_dir."/".time().".sql";	
}

	
  /**
  +----------------------------------------------------------
  * 功能：计算文件大小
  +----------------------------------------------------------
  * @param int $bytes
  +----------------------------------------------------------
  * @return string 转换后的字符串
  +----------------------------------------------------------
  */
  private function byteFormat($bytes) {
     $sizetext = array(" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
     return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $sizetext[$i];
  }	
}