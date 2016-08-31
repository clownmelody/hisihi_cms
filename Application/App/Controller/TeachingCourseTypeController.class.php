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

class TeachingCourseTypeController extends BaseController
{
    private $log;
    public function __construct(){
        parent::__construct();
        $this->log = new ApiInfoLog('TeachingCourseTypeController');
    }

    /**
     * 获取3.0.1 培训课程类别列表
     * @param int $page
     * @param int $count
     */
    public function data($page=1, $count=10) {
        switch ($this->_method){
            case 'get':
                $list = D('App/TeachingCourseType','Service')->getTypeList($page, $count);
                $extra['data'] = $list;
                $extra['totalCount'] = count($list);
                $this->apiSuccess('获取培训课程类型列表成功', null, $extra);
                break;
            default:
                $this->response_json('not support this method!');
                break;
        }
    }

}