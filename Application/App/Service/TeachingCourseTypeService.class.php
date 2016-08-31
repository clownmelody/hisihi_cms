<?php
namespace App\Service;
use Think\Model;

/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/31
 * Time: 12:24
 */

class TeachingCourseTypeService extends Model
{
    public function getTypeList($page, $count){
        $where_array['status'] = 1;
        $list = D('App/OrganizationTeachingCourseType', 'Model')
            ->field('id, name, pic_url, special_type, sort')
            ->where($where_array)
            ->page($page, $count)
            ->order('sort desc')
            ->select();
        return $list;
    }

}