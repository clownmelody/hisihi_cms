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

    public function _empty() {
        $this->apiError(404, "找不到该接口");
    }

    protected function apiReturn($success, $error_code=0, $message=null, $redirect=null, $extra=null) {
        $result = array();
        $result['success'] = $success;
        $result['error_code'] = $error_code;
        if($message !== null) {
            $result['message'] = $message;
        }
        if($redirect !== null) {
            $result['redirect'] = $redirect;
        }
        foreach($extra as $key=>$value) {
            $result[$key] = $value;
        }
        //将返回信息进行编码
        $format = $_REQUEST['format'] ? $_REQUEST['format'] : 'json';//返回值格式，默认json
        if($format == 'json') {
            $this->response($result,'json');
            exit;
        } else if($format == 'xml') {
            echo xml_encode($result);
            exit;
        } else {
            $_GET['format'] = 'json';
            $_REQUEST['format'] = 'json';
            return $this->apiError(400, "format参数错误");
        }
    }

    public function response_json($data, $http_code=200){
        parent::response($data, 'json', $http_code);
    }

    public function apiSuccess($message, $redirect=null, $extra=null) {
        return $this->apiReturn(true, 0, $message, $redirect, $extra);
    }

    protected function apiError($error_code, $message, $redirect=null, $extra=null) {
        return $this->apiReturn(false, $error_code, $message, $redirect, $extra);
    }

}