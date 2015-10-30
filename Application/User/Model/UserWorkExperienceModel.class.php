<?php

namespace User\Model;

use Think\Model;

class UserWorkExperienceModel extends Model
{
    protected $_auto = array(
        array('status', 1, self::MODEL_INSERT),
    );

//    public function save($data){
//        return $this->add($data);
//}

    public function getUserWorkExperiences($uid){
        $result = $this->where('status=1 and uid='.$uid)->select();
        foreach($result as $key=>$value){
            $value['start_time'] = time_format($value['start_time'],'Y年');
            $value['end_time'] = time_format($value['end_time'],'Y年');
            $result[$key] = $value;
        }
        return $result;
    }

    public function getLastWorkExperience($uid){
        return $this->where('status=1 and uid='.$uid)->order('end_time desc')->limit(1)->select();
    }

}
