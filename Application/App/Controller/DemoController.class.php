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

    /**
     * @Before
     */
    public function index() {
        switch ($this->_method){
            case 'get':
                G('startSqlTime');
                $model = D('Forum/Forum');
                $model->getForumList();
                G('endSqlTime');
                var_dump($model->getLastSql());
                var_dump(G('startSqlTime','endSqlTime', 6));
                $this->response_json('get data');
                break;
            case 'put':
                $t = new DemoController();
                $t->test();
                $this->response_json('put data');
                break;
            case 'post':
                $this->response_json('post data');
                break;
            default:
                $this->response_json('not support this method!');
                break;
        }
    }

    public function test(){
        var_dump('222');
    }

}