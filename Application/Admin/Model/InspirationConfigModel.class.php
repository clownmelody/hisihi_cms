<?php

namespace Admin\Model;
use Think\Hook;
use Think\Model;
use Admin\Model\AuthGroupModel;


class InspirationConfigModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(
        array('type', 'require', '配置类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('value', 'require', '配置值不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
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

    public function updateInspirationConfig($id, $data){
        return $this->where('id='.$id)->save($data);
    }

    protected function getCreateTime(){
        return time();
    }

}
