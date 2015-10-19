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
 * 找灵感模型
 */
class InspirationModel extends Model{

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time', 'getCreateTime', Model:: MODEL_INSERT,'callback'),
    );

    public function delete($id){
        return $this->where('id='.$id)->delete();
    }

    public function saveData($data){
        return $this->add($data);
    }

    public function updateData($id, $data){
        return $this->where('id='.$id)->save($data);
    }

    protected function getCreateTime(){
        return time();
    }

}
