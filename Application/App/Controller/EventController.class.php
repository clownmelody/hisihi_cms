<?php
/**
 * Created by PhpStorm.
 * User: andyYang
 * Date: 2015/8/14
 * Time: 17:12
 */

namespace App\Controller;


use Think\Hook;

class EventController extends AppController{
    protected $eventModel;

    function _initialize(){
        $this->eventModel = D('Event/Event');
    }

    public function competitionList($page=1, $count=5){
        $picModel = D('Home/Picture');
        $listData = $this->eventModel->getCompetitionEventList($page, $count);
        foreach($listData['list'] as &$event){
            $event['pic_path'] = null;
            $cover_id = $event['cover_id'];
            $result = $picModel->where('status=1 and id='.$cover_id)->field('path')->find();
            $picKey = substr($result['path'], 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $picKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if($isExist){
                $event_pic = "http://hisihi-other".C('OSS_ENDPOINT').$picKey;
                $event['pic_path'] = $event_pic;
                unset($event['cover_id']);
            }
        }
        $this->apiSuccess("获取比赛列表成功", null, array('totalCount' => $listData['totalCount'], 'data' => $listData['list']));
    }

    public function competitionDetail($competition_id=0){
        if($competition_id==0){
            $this->apiError(-1, "传入比赛ID错误");
        }
        $detail = $this->eventModel->getCompetitionDetail($competition_id);
        $this->apiSuccess("获取比赛详情成功", null, array('data' => $detail));
    }

}