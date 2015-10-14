<?php

namespace Forum\Model;
use Think\Model;

class ForumAtModel extends Model {
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 添加@某人的帖子
     * @param $uid
     * @param $at_uid
     * @param $post_id
     * @return bool|mixed
     */
    public function addAtPost($uid, $at_uid, $post_id, $type=1) {
        $data = array('uid'=>$uid, 'at_uid'=>$at_uid, 'post_id'=>$post_id, 'type'=>$type);
        $data = $this->create($data);
        if(!$data) return false;
        return $this->add($data);
    }

}
