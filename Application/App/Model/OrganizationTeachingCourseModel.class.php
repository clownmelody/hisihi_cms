<?php

namespace App\Model;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/31
 * Time: 11:33
 */

class OrganizationTeachingCourseModel extends Model
{
    protected $tableName = 'organization_teaching_course';
    protected $_auto=array(
        array('create_time', 'time', 3, 'function'), // 对create_time字段在更新的时候写入当前时间戳
        array('status', '1'),  // 新增的时候把status字段设置为1
    );

    public function getByOrgAndType($org_id, $type_id){
        $where_array['organization_id'] = $org_id;
        $where_array['status'] = 1;
        if(!empty($type_id)){
            $where_array['type_id'] = $type_id;
        }
        $info = $this->field('id, organization_id,
        course_name, price')
            ->where($where_array)
            ->order('create_time desc')
            ->select();
        return $info;
    }

    public function getShouHuiByOrgAndType($org_id, $type_id){
        $where_array['organization_id'] = $org_id;
        $where_array['status'] = 1;
        if(!empty($type_id)){
            $where_array['type_id'] = $type_id + 10000;
        }
        $info = $this->field('id, organization_id,
        course_name, price')
            ->where($where_array)
            ->order('create_time desc')
            ->select();
        return $info;
    }

}