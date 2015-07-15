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
define ('APP_DEBUG', true);

if ($_REQUEST['client'] && $_REQUEST['client'] == 'pc') {
    ini_set("session.use_cookies", 1);
    ini_set("session.use_trans_sid", 1);
} else {
//从URL获取SESSION编号
    ini_set("session.use_cookies", 0);
    ini_set("session.use_trans_sid", 1);
}
if ($_REQUEST['session_id']) {
    session_id($_REQUEST['session_id']);
    session_start();
}

//调用Application/App应用
// 绑定访问App模块
define('BIND_MODULE', 'App');
define ('APP_PATH', './Application/');
define ('RUNTIME_PATH', './Runtime/');
require './ThinkPHP/ThinkPHP.php';