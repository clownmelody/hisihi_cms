<?php

namespace User\Model;

use Think\Model;

class UserWorkExperienceModel extends Model
{
    protected $_auto = array(
        array('status', 1, self::MODEL_INSERT),
    );

    public function save($data){
        return $this->add($data);
    }

    public function getUserWorkExperiences($uid){
        return $this->where('status=1 and uid='.$uid)->select();
    }

    public function getLastWorkExperience($uid){
        return $this->where('status=1 and uid='.$uid)->order('end_time desc')->limit(1)->select();
    }

}
