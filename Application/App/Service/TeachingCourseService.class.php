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
                                             $city=null, $page, $count){
        $model = M();
        $start = ($page-1)*$count;
        $select_type_id = '';
        if(!empty($type_id)){
            $select_type_id = " and c.type_id=".$type_id;
        }
        if(!empty($longitude)&&!empty($latitude)){
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
                where o.id=c.organization_id and o.type=30 and o.status=1 and c.status=1".$select_type_id." order by distance asc limit ".$start.",".$count;
        } else {
            if(empty($city)){
                $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=30 and o.status=1 and c.status=1".$select_type_id." order by sort desc limit ".$start.",".$count;
            } else {
                $city_filter = " and o.city like '%" .$city . "%'";
                $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=30 and o.status=1 and c.status=1".$select_type_id . $city_filter . " order by sort desc limit ".$start.",".$count;
            }
        }
        if(empty($city)){
            $total_count_sql = "select count(distinct(o.id)) as totalCount
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=30 and o.status=1 and c.status=1".$select_type_id;
        } else {
            $city_filter = " and o.city like '%" .$city . "%'";
            $total_count_sql = "select count(distinct(o.id)) as totalCount
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=30 and o.status=1 and c.status=1".$select_type_id . $city_filter;
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
     * @param $page
     * @param $count
     * @return array
     */
    public function getNearbyShouHuiOrgByCourseType($major_id, $longitude=null, $latitude=null,
                                                    $city=null, $page, $count){
        $major_id += 10000;
        $model = M();
        $start = ($page-1)*$count;
        if(!empty($longitude)&&!empty($latitude)){
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
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 order by distance asc limit ".$start.",".$count;
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
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 and c.type_id=".$major_id." order by distance asc limit ".$start.",".$count;
            }
        } else {
            if(empty($city)){
                if(empty($major_id)){
                    $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 order by sort desc limit ".$start.",".$count;
                } else {
                    $sql = "select o.id as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 and c.type_id=".$major_id." order by sort desc limit ".$start.",".$count;
                }
            } else {
                $city_filter = " and o.city like '%" .$city . "%'";
                if(empty($major_id)){
                    $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1".$city_filter." order by sort desc limit ".$start.",".$count;
                } else {
                    $sql = "select o.id as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 and c.type_id=".$major_id. $city_filter ." order by sort desc limit ".$start.",".$count;
                }
            }
        }
        if(empty($city)){
            if(empty($major_id)){
                $total_count_sql = "select count(distinct(o.id)) as totalCount from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1";
            } else {
                $total_count_sql = "select count(distinct(o.id)) as totalCount from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 and c.type_id=".$major_id;
            }
        } else {
            $city_filter = " and o.city like '%" .$city . "%'";
            if(empty($major_id)){
                $total_count_sql = "select count(distinct(o.id)) as totalCount from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1".$city_filter;
            } else {
                $total_count_sql = "select count(distinct(o.id)) as totalCount from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and o.status=1 and c.status=1 and c.type_id=".$major_id.$city_filter;
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

}