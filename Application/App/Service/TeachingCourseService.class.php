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
    public function getNearbyOrgByCourseType($type_id, $longitude=null, $latitude=null){
        $model = M();
        if(!empty($longitude)&&!empty($latitude)){
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
                where o.id=c.organization_id and o.type=30 and c.type_id=".$type_id." order by distance asc
                ";
        } else {
            $sql = "select o.id as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=30 and c.type_id=".$type_id." order by sort desc
                ";
        }
        $list = $model->query($sql);
        return $list;
    }

    /**
     * 获取附近的手绘机构列表和课程
     * @param $major_id
     * @param null $longitude
     * @param null $latitude
     */
    public function getNearbyShouHuiOrgByCourseType($major_id, $longitude=null, $latitude=null){
        $major_id += 10000;
        $model = M();
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
                where o.id=c.organization_id and o.type=32 order by distance asc
                ";
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
                where o.id=c.organization_id and o.type=32 and c.type_id=".$major_id." order by distance asc
                ";
            }
        } else {
            if(empty($major_id)){
                $sql = "select distinct(o.id) as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 order by sort desc
                ";
            } else {
                $sql = "select o.id as org_id
                from
                hisihi_organization o, hisihi_organization_teaching_course c
                where o.id=c.organization_id and o.type=32 and c.type_id=".$major_id." order by sort desc
                ";
            }
        }
        $list = $model->query($sql);
        return $list;
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