<?php
require_once "db.config.php";
require_once "brand.config.php";
$global_config = array(
	//"DEFAULT_TIMEZONE"=>"Etc/GMT-8",//比格林尼治时间早8个小时，也就是北京时间,系统默认使用PRC中国时间

	//'配置项'=>'配置值'
	'MODULE_ALLOW_LIST' => array('Home', 'Admin'),
	'DEFAULT_MODULE' => 'Home', //默认模块
	//'URL_MODEL' => '1', //URL模式

	//默认错误跳转对应的模板文件
	'TMPL_ACTION_ERROR' => './Public/Common/dispatch_jump.tpl',
	//默认成功跳转对应的模板文件
	'TMPL_ACTION_SUCCESS' => './Public/Common/dispatch_jump.tpl',

	//仅仅显示信息的页面
	'TPL_SHOW_INFO' => './Public/Common/show_info.html',

	//自定义session数据库存储
	'SESSION_TYPE' => 'Db',
	'SESSION_TABLE' => 'ips_session', //存session的表
	'SESSION_EXPIRE' => 18000, //session过期时间

	//表单命令行
	/*
	'TOKEN_ON'=>true,//验证令牌环，防止重复提交 $oop->autoCheckToken($_POST)
	'TOKEN_NAME'=>'__hash__',
	'TOKEN_TYPE'=>'md5',
	'TOKEN_RESET'=>true,
	*/

	//通用邮箱配置
	"EMAIL_CONFIG" => array(
		"NOTIFY" => array(
			"usr" => "",
			"pwd" => "",
		),
		"BILLING" => array(
			"usr" => "",
			"pwd" => "",
		),
	),

	//数据库备份路径
	'DB_BACKUP' => "./Public/dbbackup/",
	//数据库备份分卷大小
	'DB_BACKUP_SIZE' => 2048,

	//多语言
	'LANG_SWITCH_ON' => true, // 开启语言包功能
	'LANG_AUTO_DETECT' => true, // 自动侦测语言 开启多语言功能后有效
	'DEFAULT_LANG' => 'en-us', // 默认语言
	'LANG_LIST' => 'zh-cn,en-us', // 允许切换的语言列表 用逗号分隔
	'VAR_LANGUAGE' => 'l', // 默认语言切换变量
);

return array_merge($global_db_config, $global_config, $brand_config);
