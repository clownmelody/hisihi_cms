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
     * @param null $type_id
     * @param null $longitude
     * @param null $latitude
     * @param null $city
     * @param null $is_prelisten
     * @param null $has_coupon
     * @param int $page
     * @param int $count
     * @param null $version
     */
    public function data($type_id=null, $longitude=null, $latitude=null, $city=null,
                         $is_prelisten=null, $has_coupon=null, $page=1, $count=10, $version=null) {
        switch ($this->_method){
            case 'get':
                $org_list = array();
                $org_list_array = D('App/TeachingCourse','Service')
                    ->getNearbyOrgByCourseType($type_id, $longitude, $latitude, $city,
                                                $is_prelisten, $has_coupon, $page, $count, $version);
                foreach ($org_list_array['list'] as &$org) {
                    $org_info = D('App/Organization', 'Service')->getBaseInfoById($org['org_id']);
                    $org_info['id'] = $org['org_id'];
                    $org_info['distance'] = $org['distance'];
                    $course_list = D('App/TeachingCourse','Service')
                        ->getTeachingCourseListByOrgIdAndTypeId($org['org_id'], $type_id, $has_coupon, $version);
                    $org_info['teaching_course_list'] = $course_list;
                    $org_list[] = $org_info;
                }
                $extra['data'] = $org_list;
                $extra['totalCount'] = $org_list_array['totalCount'];
                $this->apiSuccess('根据课程类型获取最近的机构成功', null, $extra);
                break;
            default:
                $this->response_json('not support this method!');
                break;
        }
    }

    /**
     * 获取3.0.1 手绘类机构按专业筛选课程
     * @param null $type_id
     * @param null $longitude
     * @param null $latitude
     * @param null $city
     * @param null $is_prelisten
     * @param null $has_coupon
     * @param int $page
     * @param int $count
     * @param null $version
     */
    public function shouhui($type_id=null, $longitude=null, $latitude=null, $city=null,
                            $is_prelisten=null, $has_coupon=null, $page=1, $count=10, $version=null) {
        switch ($this->_method){
            case 'get':
                $org_list = array();
                $org_list_array = D('App/TeachingCourse','Service')
                    ->getNearbyShouHuiOrgByCourseType($type_id, $longitude, $latitude, $city,
                        $is_prelisten, $has_coupon, $page, $count, $version);
                foreach ($org_list_array['list'] as &$org) {
                    $org_info = D('App/Organization', 'Service')->getBaseInfoById($org['org_id']);
                    $org_info['id'] = $org['org_id'];
                    $org_info['distance'] = $org['distance'];
                    $course_list = D('App/TeachingCourse','Service')
                        ->getShouHuiTeachingCourseListByOrgIdAndTypeId($org['org_id'], $type_id,
                            $has_coupon, $version);
                    $org_info['teaching_course_list'] = $course_list;
                    $org_list[] = $org_info;
                }
                $extra['data'] = $org_list;
                $extra['totalCount'] = $org_list_array['totalCount'];
                $this->apiSuccess('根据课程类型获取最近的机构成功', null, $extra);
                break;
            default:
                $this->response_json('not support this method!');
                break;
        }
    }

    public function rebateAndTagInfo($teaching_course_id=null) {
        switch ($this->_method){
            case 'get':
                $info = D('App/TeachingCourse','Service')
                    ->getRebateAndTagInfoByCourseId($teaching_course_id);
                $extra['data'] = $info;
                $this->apiSuccess('根据课程抵扣券和标签信息成功', null, $extra);
                break;
            default:
                $this->response_json('not support this method!');
                break;
        }
    }

}