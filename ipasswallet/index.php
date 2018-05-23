<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境 PHP版本不能低于5.4
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
	die('require PHP > 5.4.0 !');
}

//引入自加载
require './vendor/autoload.php';

//自定义一些常规变量
const UIKIT = "uikit-2.27.2";
const ANGULARJS = "angular-1.2.28";
const UEDITOR = "ueditor1_4_3_3";
const ECHART = "echarts-2.2.7";

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', True);

// 定义应用目录
define('APP_PATH', './Application/');

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单