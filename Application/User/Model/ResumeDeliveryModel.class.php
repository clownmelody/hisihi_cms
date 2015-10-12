<?php

namespace User\Model;
use Think\Model;
use Think\Page;

/**
 * 用户简历投递模型
 */
class ResumeDeliveryModel extends Model{

	/* 自动完成规则 */
	protected $_auto = array(
		array('create_time', NOW_TIME, self::MODEL_BOTH),
		array('status', 1, self::MODEL_INSERT),
	);

	public function save($data){
		return $this->add($data);
	}

}
