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

    public function getCompetitionEventList($page, $count, $removeId, $type_id){
        $now = time();
        $totalCount = $this->where("status=1 and type_id=".$type_id." and id!=".$removeId)->count();
        $unend_count = $this->where("status=1 and type_id=".$type_id." and eTime>".$now)->count();
        $ended_count = $totalCount - $unend_count;
        $list = $this->where("status=1 and type_id=".$type_id." and eTime>".$now." and id!=".$removeId)
            ->page($page, $count)->order('eTime asc')
            ->field('title, explain, sTime, eTime, id, cover_id, view_count')->select();

        $can_page_count = ceil($unend_count/$count);  // 未结束比赛可以形成的分页数
        $start_limit = 0;
        if($page-$can_page_count==0){  // 当前页需要已完成比赛数据的填充
            $start_limit = 0;
        }
        if($page-$can_page_count==1)
            $start_limit = $can_page_count*$count - $unend_count;
        if($page-$can_page_count>1)
            $start_limit = $count*($page-$can_page_count-1) + $can_page_count*$count - $unend_count;

        if($count>count($list)){  // 当前页中进行中的比赛数量不够，加入已结束的比赛
            $expire_list = $this->where("status=1 and type_id=".$type_id." and eTime<".$now." and id!=".$removeId)->limit($start_limit, $count-count($list))/*->page($page-$page_count, $count-count($list))*/->order('eTime desc')
                ->field('title, explain, sTime, eTime, id, cover_id, view_count')->select();
            if(empty($list)){
                $list = array();
            }
            if(!empty($expire_list)){
                $list = array_merge($list, $expire_list);
            }
        }
        $result['totalCount'] = $totalCount;
        $result['list'] = $list;
        return $result;
    }

    public function getCompetitionDetail($id){
        $competition = $this->where("id=".$id)
            ->field('title, detail_content, create_time')->find();
        return $competition;
    }

    public function getCompetitionInfo($id){
        $competition = $this->where("id=".$id)
            ->field('id, title, explain, sTime, eTime, cover_id, view_count, address, organizer')->find();
        return $competition;
    }

    public function incViewCount($id){
        $this->where(array('id' => $id))->setInc('view_count');
    }

}
