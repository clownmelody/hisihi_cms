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

}
