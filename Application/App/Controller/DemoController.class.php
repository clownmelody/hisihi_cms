<?php
namespace App\Controller;
use Common\Controller\BaseController;
use log\ApiInfoLog;

/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/27
 * Time: 10:01
 */

class DemoController extends BaseController
{
    private $log;
    public function __construct(){
        parent::__construct();
        $this->log = new ApiInfoLog('DemoController');
    }

    Public function index() {
        switch ($this->_method){
            case 'get':
                $this->log->record('test log');
                $this->response_json('get data');
                break;
            case 'put':
                $this->response_json('put data');
                break;
            case 'post':
                $this->response_json('post data');
                break;
        }
    }

}