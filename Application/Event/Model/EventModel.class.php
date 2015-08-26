<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Event\Model;
use Think\Model;
use Think\Page;

/**
 * 活动模型
 * Class EventModel
 * @package Event\Model
 * autor:xjw129xjt
 */
class EventModel extends Model{
    protected $_validate = array(
        array('title', '1,100', '标题长度不合法', self::EXISTS_VALIDATE, 'length'),
        array('explain', '1,40000', '内容长度不合法', self::EXISTS_VALIDATE, 'length'),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_INSERT),
        array('uid', 'is_login',3, 'function'),
    );

    public function getCompetitionEventList($page, $count){
        $totalCount = $this->where("status=1 and type_id=2")->count();
        $list = $this->where("status=1 and type_id=2")->page($page, $count)->order('deadline desc')
                        ->field('title, explain, sTime, eTime, id, cover_id')->select();
        $result['totalCount'] = $totalCount;
        $result['list'] = $list;
        return $result;
    }

    public function getCompetitionDetail($id){
        $competition = $this->where("status=1 and type_id=2 and id=".$id)->field('detail_content')->find();
        return $competition;
    }

}
