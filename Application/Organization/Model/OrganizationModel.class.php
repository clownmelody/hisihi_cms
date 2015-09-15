<?php
/**
 * Created by PhpStorm.
 * User: shaolei
 * Date: 2015/9/15 0015
 * Time: 15:00
 */

namespace Organization\Model;
use Think\Model;
use Think\Page;

class OrganizationModel extends Model
{


    /**
     * 获取机构详细信息
     * @param $id
     * @param bool $field
     * @return mixed
     *
     */
    public function info($id, $field = true){
        /* 获取分类信息 */
        $map = array();
        if(is_numeric($id)){ //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['name'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }
}