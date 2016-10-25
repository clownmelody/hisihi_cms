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

    public function competitionList($page=1, $count=5, $version=null, $removeId=0, $type_id=2){
        if((float)$version>=2.2){
            $eventModel = M();
            $allCount = $eventModel->query('select sum(view_count) as allCount from hisihi_event where status=1');
            $extra['allCount'] = $allCount[0]['allCount'];
        }
        $picModel = D('Home/Picture');
        $listData = $this->eventModel->getCompetitionEventList($page, $count, $removeId, $type_id);
        foreach($listData['list'] as &$event){
            $event['content_url'] = C('HOST_NAME_PREFIX').'app.php/event/competitioncontent/type/view/id/'.$event['id'];
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
        $extra['totalCount'] = $listData['totalCount'];
        $extra['data'] = $listData['list'];
        $this->apiSuccess("获取比赛活动列表成功", null, $extra);
    }

    public function competitionDetail($competition_id=0){
        if($competition_id==0){
            $this->apiError(-1, "传入比赛ID错误");
        }
        $info = $this->eventModel->getCompetitionInfo($competition_id);
        if(empty($info)){
            $this->apiError(-1, "id不存在");
        }
        $info['content_url'] = C('HOST_NAME_PREFIX').'app.php/event/competitioncontent/type/view/id/'.$info['id'];
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
        $info['is_enroll'] = $this->is_enroll($competition_id);
        $this->apiSuccess("获取比赛详情成功", null, array('data' => $info));
    }

    private function is_enroll($competition_id){
        $model = M('EventAttend');
        $data['uid'] = $this->getUid();
        $data['event_id'] = $competition_id;
        $data['status'] = 1;
        return $model->where($data)->count();
    }

    /**
     * 比赛详情
     * @param $id
     * @param string $type
     */
    public function competitioncontent($id,$type = ''){
        $detail = $this->eventModel->getCompetitionDetail($id);
        $this->eventModel->incViewCount($id);
        $content = array(
            'content'=>$detail['detail_content'],
            'title'=>$detail['title'],
            'create_time'=>$detail['create_time']
        );
        if($type == 'view') {
            $this->assign('top_content_info', $content);
            $this->assign('articleId', $id);
            $this->assign('title', $detail['title']);
            $this->assign('create_time', $detail['create_time']);
            $this->setTitle('比赛详情 — 嘿设汇');
            $this->display();
        }
    }

    public function activityv3_2($id){
        $this->assign('id', $id);
        $this->display('activityv3.2');
    }

    public function competitionv3_2($id){
        $this->assign('id', $id);
        $this->display('competitionv3.2');
    }

    public function competitionworkv3_2($id){
        $this->assign('id', $id);
        $this->display('competitionworkv3.2');
    }

    /**
     * web 比赛列表
     */
    public function shareCompetitionList(){
        $this->display('sharecompetitionlist');
    }

    public function enroll($uid=0, $event_id, $mobile, $username){
        $model = M('EventAttend');
        $data['uid'] = $uid;
        $data['event_id'] = $event_id;
        $data['status'] = 1;
        $count = $model->where($data)->count();
        if($count){
            $this->apiError(-1, '你已经报名');
        }
        $data['mobile'] = $mobile;
        $data['username'] = $username;
        $data['create_time'] = time();
        $model->add($data);
        $this->apiSuccess('预约报名成功');
    }

    public function worksList($page=1, $count=5, $version=null, $competition_id=null){
        $picModel = D('Home/Picture');
        $map['status'] = array('gt', 0);
        $map['competition_id'] = $competition_id;
        $listData = M('EventWorks')->where($map)->field('id, name , cover_id')->page($page, $count)->select();
        foreach($listData as &$event){
            $event['content_url'] = C('HOST_NAME_PREFIX').'app.php/event/workscontent/type/view/id/'.$event['id'];
            $event['cover_path'] = null;
            $cover_id = $event['cover_id'];
            $result = $picModel->where('status=1 and id='.$cover_id)->field('path')->find();
            $picKey = substr($result['path'], 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $picKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            unset($event['cover_id']);
            if($isExist){
                $event_pic = "http://hisihi-other".C('OSS_ENDPOINT').$picKey;
                $event['cover_path'] = $event_pic;
            }
        }
        $extra['totalCount'] = M('EventWorks')->where($map)->count();
        $extra['data'] = $listData;
        $this->apiSuccess("获取比赛作品列表成功", null, $extra);
    }

    /**
     * 比赛详情
     * @param $id
     * @param string $type
     */
    public function workscontent($id,$type = ''){
        $detail = M('EventWorks')->where('id='.$id)->find();
        $content = array(
            'content'=>$detail['content'],
            'name'=>$detail['name'],
            'create_time'=>$detail['create_time']
        );
        if($type == 'view') {
            $this->assign('top_content_info', $content);
            $this->assign('articleId', $id);
            $this->assign('name', $detail['name']);
            $this->assign('create_time', $detail['create_time']);
            $this->setTitle('作品详情 — 嘿设汇');
            $this->display();
        }
    }
}