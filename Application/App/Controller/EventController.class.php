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
        C('SHOW_PAGE_TRACE', false);
    }

    public function competitionList($page=1, $count=5){
        $picModel = D('Home/Picture');
        $listData = $this->eventModel->getCompetitionEventList($page, $count);
        foreach($listData['list'] as &$event){
            $event['content_url'] = 'http://www.hisihi.com/app.php/event/competitioncontent/type/view/id/'.$event['id'];
            $event['pic_path'] = null;
            $cover_id = $event['cover_id'];
            $result = $picModel->where('status=1 and id='.$cover_id)->field('path')->find();
            $picKey = substr($result['path'], 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $picKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            unset($event['cover_id']);
            if($isExist){
                $event_pic = "http://hisihi-other".C('OSS_ENDPOINT').$picKey;
                $event['pic_path'] = $event_pic;
            }
        }
        $this->apiSuccess("获取比赛列表成功", null, array('totalCount' => $listData['totalCount'], 'data' => $listData['list']));
    }

    public function competitionDetail($competition_id=0){
        if($competition_id==0){
            $this->apiError(-1, "传入比赛ID错误");
        }
        $info = $this->eventModel->getCompetitionInfo($competition_id);
        if(empty($info)){
            $this->apiError(-1, "id不存在");
        }
        $info['content_url'] = 'http://www.hisihi.com/app.php/event/competitioncontent/type/view/id/'.$info['id'];
        $event['pic_path'] = null;
        $cover_id = $info['cover_id'];
        $picModel = D('Home/Picture');
        $result = $picModel->where('status=1 and id='.$cover_id)->field('path')->find();
        $picKey = substr($result['path'], 17);
        $param["bucketName"] = "hisihi-other";
        $param['objectKey'] = $picKey;
        $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
        unset($info['cover_id']);
        if($isExist){
            $event_pic = "http://hisihi-other".C('OSS_ENDPOINT').$picKey;
            $info['pic_path'] = $event_pic;
        }
        //$info['content_url'] = 'http://www.hisihi.com/app.php/event/competitioncontent/type/view/id/'.$competition_id;
        $this->apiSuccess("获取比赛详情成功", null, array('data' => $info));
    }

    public function competitioncontent($id,$type = ''){
        $detail = $this->eventModel->getCompetitionDetail($id);
        $this->eventModel->incViewCount($id);
        $content = array(
            'content'=>$detail['detail_content'],
        );
        if($type == 'view') {
            $this->assign('top_content_info', $content);
            $this->setTitle('比赛详情 — 嘿设汇');
            $this->display();
        }
    }

}