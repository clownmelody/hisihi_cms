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


class CompanyRecruitModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(

    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time', 'getCreateTime', Model:: MODEL_INSERT,'callback'),
        array('end_time','time',self::MODEL_BOTH,'function'),
    );

    public function delete($id){
        return $this->where('id='.$id)->save(array('status'=>-1));
    }

    public function add($data){
        return $this->add($data);
    }

    public function updateCompanyRecruit($id, $data){
        return $this->where('id='.$id)->save($data);
    }

    protected function getCreateTime(){
        return time();
    }

}
