<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/27
 * Time: 10:42
 */

namespace Common\Controller;


use log\ApiInfoLog;
use Think\Controller\RestController;
require_once('Application/Common/Lib/ApiInfoLog.class.php');

class BaseController extends RestController
{
    protected $base_log;

    public function __construct() {
        parent::__construct();

        if(C('API_LOG')){
            $log_array['REQUEST_TIME'] = $_SERVER['REQUEST_TIME'];
            $log_array['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
            $log_array['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
            $log_array['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            $this->base_log = new ApiInfoLog('BaseController');
            $this->base_log->record($log_array);
        }

    }

    public function response_json($data, $http_code=200){
        parent::response($data, 'json', $http_code);
    }

}