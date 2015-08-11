<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Hook;
use Think\Model;
use Admin\Model\AuthGroupModel;

/**
 * 文档基础模型
 */
class CourseFeedBackModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(
        array('course_name', 'require', '课程名不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('school', 'require', '学校不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('email', 'require', '邮箱不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time', 'getCreateTime', Model:: MODEL_INSERT,'callback'),
    );

    public function resolve($id){
        $data['resolved'] = 1;
        return $this->where('id='.$id)->save($data);
    }

    public function deleteFeedback($id){
        return $this->where('id='.$id)->delete();
    }

    public function addFeedBack($data){
        return $this->add($data);
    }

    protected function getCreateTime(){
        return time();
    }

}
