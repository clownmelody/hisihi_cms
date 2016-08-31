<?php

namespace App\Model;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/31
 * Time: 11:33
 */

class OrganizationTeachingCourseTypeModel extends Model
{
    protected $tableName = 'organization_teaching_course_type';
    protected $_auto=array(
        array('create_time', 'time', 3, 'function'), // 对create_time字段在更新的时候写入当前时间戳
        array('status', '1'),  // 新增的时候把status字段设置为1
    );

    public function updateType($i, $data){
        $this->where('id='.$i)->save($data);
    }

}