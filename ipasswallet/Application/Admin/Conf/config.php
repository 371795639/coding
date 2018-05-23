<?php
return [
	//'配置项'=>'配置值'
	//Auth权限设置
	'AUTH_CONFIG' => array(
		'AUTH_ON' => true, // 认证开关
		'AUTH_TYPE' => 1, // 认证方式，1为实时认证；2为登录认证。
		'AUTH_GROUP' => 'ips_auth_group', // 用户组数据表名
		'AUTH_GROUP_ACCESS' => 'ips_auth_group_access', // 用户-用户组关系表
		'AUTH_RULE' => 'ips_auth_rule', // 权限规则表
		'AUTH_USER' => 'ips_member', // 用户信息表
		'AUTH_ADMIN' => array('1'), //超级管理员的ID
	),

	//系统配置项的KEY，这些不能被删除
	'WEB_CONFIG' => array(
		'web_table_list_count', //信息列表显示的记录条数
		'web_isopen',
		'web_close_desc',
		'web_author',
		'web_copy',
		'web_theme',
		'web_beian', //备案
		'web_domain', //网站域名
		'web_isbeta', //网站是否测试中
		'web_currency', //网站基础货币
		'web_thumb', //网站图片缩略图
		'web_default_lang', //网站默认语言
		'web_cms_cat', //CMS分类
		"web_lang", //平台支持的多语言列表
	),

];