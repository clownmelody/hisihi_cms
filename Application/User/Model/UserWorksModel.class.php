<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace User\Model;
use Think\Model;
use Think\Page;

/**
 * 用户作品模型
 */
class UserWorksModel extends Model{

	/* 自动完成规则 */
	protected $_auto = array(
		array('create_time', NOW_TIME, self::MODEL_BOTH),
		array('status', 1, self::MODEL_INSERT),
	);

	public function saveWorks($data){
		return $this->add($data);
	}

	public function getResumeWorks($uid){
		return $this->field('picture_id')->where('post_id=0 and forum_id=1001 and status=1 and uid='.$uid)->select();
	}

}
