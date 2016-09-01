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

class TeachingCourseController extends BaseController
{
    private $log;
    public function __construct(){
        parent::__construct();
        $this->log = new ApiInfoLog('TeachingCourseController');
    }

    /**
     * 获取3.0.1 软件类机构按专业筛选课程
     * @param $type_id
     * @param $longitude
     * @param $latitude
     */
    public function data($type_id, $longitude=null, $latitude=null) {
        switch ($this->_method){
            case 'get':
                $org_list = D('App/TeachingCourse','Service')->getNearbyOrgByCourseType($type_id, $longitude, $latitude);
                foreach ($org_list as &$org) {
                    $org['info'] = D('App/Organization', 'Service')->getBaseInfoById($org['org_id']);
                    $course_list = D('App/TeachingCourse','Service')
                        ->getTeachingCourseListByOrgIdAndTypeId($org['org_id'], $type_id);
                    $org['teaching_course_list'] = $course_list;
                }
                $extra['data'] = $org_list;
                $this->apiSuccess('根据课程类型获取最近的机构成功', null, $extra);
                break;
            default:
                $this->response_json('not support this method!');
                break;
        }
    }

    /**
     * 获取3.0.1 手绘类机构按专业筛选课程
     * @param $type_id
     * @param $longitude
     * @param $latitude
     */
    public function shouhui($type_id, $longitude=null, $latitude=null) {
        switch ($this->_method){
            case 'get':
                $org_list = D('App/TeachingCourse','Service')->getNearbyShouHuiOrgByCourseType($type_id, $longitude, $latitude);
                foreach ($org_list as &$org) {
                    $org['info'] = D('App/Organization', 'Service')->getBaseInfoById($org['org_id']);
                    $course_list = D('App/TeachingCourse','Service')
                        ->getShouHuiTeachingCourseListByOrgIdAndTypeId($org['org_id'], $type_id);
                    $org['teaching_course_list'] = $course_list;
                }
                $extra['data'] = $org_list;
                $this->apiSuccess('根据课程类型获取最近的机构成功', null, $extra);
                break;
            default:
                $this->response_json('not support this method!');
                break;
        }
    }


}