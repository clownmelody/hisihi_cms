<?php
namespace App\Service;
use Think\Model;

/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/31
 * Time: 12:24
 */

class TeachingCourseService extends Model
{
    public function getNearbyOrgByCourseType($type_id, $longitude=null, $latitude=null,
                                             $city=null, $is_prelisten=null, $has_coupon=null, $page, $count){
        $model = M();
        $start = ($page-1)*$count;
        $select_type_id = '';
        if(!empty($type_id)){
            $select_type_id = " and c.type_id=".$type_id;
        }
        $select_prelisten = '';
        if($is_prelisten!=null){
            $select_prelisten = " and o.is_listen_preview=".$is_prelisten;
        }
        $select_has_coupon = '';
        /*if($has_coupon==1){
            $ids_list = $this->getHasCouponOrganizationIdList();
            $comma_separated = implode(",", $ids_list);
            $select_has_coupon = " and o.id in (".$comma_separated.")";
        }
        if($has_coupon==0&&$has_coupon!=null){
            $ids_list = $this->getHasCouponOrganizationIdList();
            $comma_separated = implode(",", $ids_list);
            $select_has_coupon = " and o.id not in (".$comma_separated.")";
        }*/
        if(!empty($longitude)&&!empty($latitude)){
            $city = null;
            if(empty($type_id)){
                $sql = "select distinct(o.id) as org_id,
                ROUND(
                    6378.138*2*ASIN(
                        SQRT(
                            POW(
                                SIN(
                                    (".$latitude."*PI()/180-latitude*PI()/180)/2
                                ),2
                            )+COS(".$latitude."*PI()/180)*COS(latitude*PI()/180)*POW(
                                SIN(
                                    (".$longitude."*PI()/180-longitude*PI()/180)/2
                                ),2
                            )
                        )
                    )*1000
                ) AS distance
                from
                hisihi_organization o
                where o.type=30 and o.status=1".$select_prelisten.$select_has_coupon." order by distance asc limit ".$start.",".$count;
            } else {
                $sql = "select distinct(o.id) as org_id,
                ROUND(
                    6378.138*2*ASIN(
                        SQRT(
                            POW(
                                SIN(
                                    (".$latitude."*PI()/180-latitude*PI()/180)/2
                                ),2
                            )+COS(".$latitude."*PI()/180)*COS(latitude*PI()/180)*POW(
                                SIN(
                                    (".$longitude."*PI()/180-longitude*PI()/180)/2
                                ),2
                            )
                        )
                    )*1000
                ) AS distance
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=30 and o.status=1 and c.status=1".$select_type_id
                    . $select_prelisten.$select_has_coupon." order by distance asc limit ".$start.",".$count;
            }
        } else {
            if(empty($city)){
                if(empty($type_id)){
                    $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o
                where o.type=30 and o.status=1".$select_prelisten.$select_has_coupon." order by sort desc limit ".$start.",".$count;
                } else {
                    $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=30 and o.status=1 and c.status=1".$select_type_id
                        . $select_prelisten.$select_has_coupon." order by sort desc limit ".$start.",".$count;
                }
            } else {
                $city_filter = " and o.city like '%" .$city . "%'";
                if(empty($type_id)){
                    $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o
                where o.type=30 and o.status=1 ". $city_filter
                        . $select_prelisten. $select_has_coupon." order by sort desc limit ".$start.",".$count;
                } else {
                    $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=30 and o.status=1 and c.status=1".$select_type_id
                        . $city_filter . $select_prelisten. $select_has_coupon." order by sort desc limit ".$start.",".$count;
                }
            }
        }
        if(empty($city)){
            if(empty($type_id)){
                $total_count_sql = "select count(distinct(o.id)) as totalCount
                from
                hisihi_organization o
                where o.type=30 and o.status=1".$select_prelisten.$select_has_coupon;
            } else {
                $total_count_sql = "select count(distinct(o.id)) as totalCount
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=30 and o.status=1 and c.status=1"
                    . $select_type_id . $select_prelisten . $select_has_coupon;
            }
        } else {
            $city_filter = " and o.city like '%" .$city . "%'";
            if(empty($type_id)){
                $total_count_sql = "select count(distinct(o.id)) as totalCount
                from
                hisihi_organization o
                where o.type=30 and o.status=1" . $city_filter . $select_prelisten . $select_has_coupon;
            } else {
                $total_count_sql = "select count(distinct(o.id)) as totalCount
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=30 and o.status=1 and c.status=1"
                    . $select_type_id . $city_filter . $select_prelisten . $select_has_coupon;
            }
        }
        $list = $model->query($sql);
        $total_count = $model->query($total_count_sql);
        return array('list'=>$list, 'totalCount'=>$total_count[0]['totalCount']);
    }

    /**
     * 获取附近的手绘机构列表和课程
     * @param $major_id
     * @param null $longitude
     * @param null $latitude
     * @param null $city
     * @param $is_prelisten
     * @param $has_coupon
     * @param $page
     * @param $count
     * @return array
     */
    public function getNearbyShouHuiOrgByCourseType($major_id, $longitude=null, $latitude=null,
                                                    $city=null, $is_prelisten, $has_coupon, $page, $count){
        if(!empty($major_id)){
            $major_id += 10000;
        }
        $model = M();
        $start = ($page-1)*$count;
        $select_prelisten = '';
        if($is_prelisten!=null){
            $select_prelisten = " and o.is_listen_preview=".$is_prelisten;
        }
        $select_has_coupon = '';
        /*if($has_coupon==1){
            $ids_list = $this->getHasCouponOrganizationIdList();
            $comma_separated = implode(",", $ids_list);
            $select_has_coupon = " and o.id in (".$comma_separated.")";
        }
        if($has_coupon==0&&$has_coupon!=null){
            $ids_list = $this->getHasCouponOrganizationIdList();
            $comma_separated = implode(",", $ids_list);
            $select_has_coupon = " and o.id not in (".$comma_separated.")";
        }*/
        if(!empty($longitude)&&!empty($latitude)){
            $city = null;
            if(empty($major_id)){
                $sql = "select distinct(o.id) as org_id,
                ROUND(
                    6378.138*2*ASIN(
                        SQRT(
                            POW(
                                SIN(
                                    (".$latitude."*PI()/180-latitude*PI()/180)/2
                                ),2
                            )+COS(".$latitude."*PI()/180)*COS(latitude*PI()/180)*POW(
                                SIN(
                                    (".$longitude."*PI()/180-longitude*PI()/180)/2
                                ),2
                            )
                        )
                    )*1000
                ) AS distance
                from
                hisihi_organization o
                where o.type=32 and o.status=1".$select_prelisten.$select_has_coupon." order by distance asc limit ".$start.",".$count;
            } else {
                $sql = "select o.id as org_id,
                ROUND(
                    6378.138*2*ASIN(
                        SQRT(
                            POW(
                                SIN(
                                    (".$latitude."*PI()/180-latitude*PI()/180)/2
                                ),2
                            )+COS(".$latitude."*PI()/180)*COS(latitude*PI()/180)*POW(
                                SIN(
                                    (".$longitude."*PI()/180-longitude*PI()/180)/2
                                ),2
                            )
                        )
                    )*1000
                ) AS distance
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 and c.type_id="
                    . $major_id . $select_prelisten.$select_has_coupon." order by distance asc limit ".$start.",".$count;
            }
        } else {
            if(empty($city)){
                if(empty($major_id)){
                    $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o
                where o.type=32 and o.status=1".$select_prelisten.$select_has_coupon." order by sort desc limit ".$start.",".$count;
                } else {
                    $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 and c.type_id="
                        . $major_id . $select_prelisten . $select_has_coupon." order by sort desc limit ".$start.",".$count;
                }
            } else {
                $city_filter = " and o.city like '%" .$city . "%'";
                if(empty($major_id)){
                    $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o
                where o.type=32 and o.status=1"
                        . $city_filter . $select_prelisten . $select_has_coupon." order by sort desc limit ".$start.",".$count;
                } else {
                    $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 and c.type_id="
                        . $major_id . $city_filter . $select_prelisten . $select_has_coupon." order by sort desc limit ".$start.",".$count;
                }
            }
        }
        if(empty($city)){
            if(empty($major_id)){
                $total_count_sql = "select count(distinct(o.id)) as totalCount from
                hisihi_organization o
                where o.type=32 and o.status=1".$select_prelisten.$select_has_coupon;
            } else {
                $total_count_sql = "select count(distinct(o.id)) as totalCount from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 and c.type_id=".$major_id.$select_prelisten.$select_has_coupon;
            }
        } else {
            $city_filter = " and o.city like '%" .$city . "%'";
            if(empty($major_id)){
                $total_count_sql = "select count(distinct(o.id)) as totalCount from
                hisihi_organization o
                where o.type=32 and o.status=1".$city_filter.$select_prelisten.$select_has_coupon;
            } else {
                $total_count_sql = "select count(distinct(o.id)) as totalCount from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 and c.type_id=".$major_id.$city_filter.$select_prelisten.$select_has_coupon;
            }
        }
        $list = $model->query($sql);
        $total_count = $model->query($total_count_sql);
        return array('list'=>$list, 'totalCount'=>$total_count[0]['totalCount']);
    }

    public function getTeachingCourseListByOrgIdAndTypeId($org_id, $type_id){
        $list = D('App/OrganizationTeachingCourse', 'Model')->getByOrgAndType($org_id, $type_id);
        return $list;
    }

    public function getShouHuiTeachingCourseListByOrgIdAndTypeId($org_id, $type_id){
        $list = D('App/OrganizationTeachingCourse', 'Model')->getShouHuiByOrgAndType($org_id, $type_id);
        return $list;
    }

    public function getHasCouponOrganizationIdList(){
        $model = M();
        $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o,
                hisihi_teaching_course_coupon_relation ccr,
                hisihi_organization_teaching_course course,
                hisihi_coupon coupon
                where o.id=course.organization_id and course.id=ccr.teaching_course_id
                and ccr.coupon_id=coupon.id
                and o.status=1
                and course.status=1 and ccr.status=1
                and coupon.status=1";
        $list = $model->query($sql);
        $result = array();
        foreach($list as $item){
            $result[] = $item['org_id'];
        }
        return $result;
    }

}