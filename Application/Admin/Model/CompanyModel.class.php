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
 * 公司模型
 */
class CompanyModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(
        array('name', 'require', '公司名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('city', 'require', '公司所在城市不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('slogan', 'require', '公司宣传语不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('filtrate_mark', 'require', '筛选标签不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('introduce', 'require', '公司简介不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('marks', 'require', '公司标签不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('scale', 'require', '公司规模不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('website', 'require', '公司网站不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('fullname', 'require', '公司注册名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('location', 'require', '公司地址不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('hr_email', 'require', 'HR邮箱不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('hr_email','email','email格式错误'),
        array('picture', 'require', '图片不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time', 'getCreateTime', Model:: MODEL_INSERT,'callback'),
    );

    public function delete($id){
        return $this->where('id='.$id)->delete();
    }

    public function add($data){
        return $this->add($data);
    }

    public function updateCompany($id, $data){
        return $this->where('id='.$id)->save($data);
    }

    protected function getCreateTime(){
        return time();
    }

}
