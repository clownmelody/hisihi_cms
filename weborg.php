<?php
/**
 * Created by PhpStorm.
 * User: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */


/**
 * 调试开关
 * 项目正式部署后请设置为false
 */
define ('APP_DEBUG', false);

//调用Application/App应用
// 绑定访问App模块
define('BIND_MODULE', 'Organization');
define ('APP_PATH', './Application/');
define ('RUNTIME_PATH', './Runtime/');
require './ThinkPHP/ThinkPHP.php';