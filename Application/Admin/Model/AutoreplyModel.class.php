<?php

namespace Admin\Model;
use Think\Hook;
use Think\Model;
use Admin\Model\AuthGroupModel;

/**
 * 自动回复模型
 * Class AutoreplyModel
 * @package Admin\Model
 */
class AutoreplyModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(
        array('forum_id', 'require', '配置类型不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('content', 'require', '配置值不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time', 'getCreateTime', Model:: MODEL_INSERT,'callback'),
    );

    public function add($data){
        return $this->add($data);
    }

    protected function getCreateTime(){
        return time();
    }

}
