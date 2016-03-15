<?php

namespace App\Controller;

use Think\Controller;
use Think\Exception;
use Think\Model;


class RefreshPostController extends AppController {

    public function _initialize() {
        C('SHOW_PAGE_TRACE', false);
    }

    /**
     * 更新主帖数据
     * 主帖id：450 -- 19673
     */
    public function RefreshPost(){
        $refreshPostLogModel = M('RefreshPostLog');
        $currentReFreshPost = $refreshPostLogModel->field('old_post_id')->where('reply_id=0 and status=1')->find();
        $currentPostId = $currentReFreshPost['old_post_id'];
        $tmpLog = $refreshPostLogModel->field('reply_count')->where('reply_id=0 and old_post_id='.$currentPostId)->find();
        if($tmpLog['reply_count']==0){
            $refreshPostLogModel->where('reply_id=0 and old_post_id='.$currentPostId)->save(array('status'=>-1));
            $currentPostId++;
        }
        if($tmpLog['reply_count']>0){
            $this->RefreshPostReply();
        } else {
            $forumPostModel = M('ForumPost');
            $endPostId = 19673;
            if($currentPostId>$endPostId){
                $currentPostId = 450;
            }
            while(!$this->isPostExist($currentPostId)){
                $currentPostId++;
            }
            while(!$this->isPostCanBeRefresh($currentPostId)){
                $currentPostId++;
            }
            $update_data['create_time'] = time() - 300;  // 当前时间前 5min
            $update_data['update_time'] = $update_data['create_time'];
            $forumPostModel->where('id='.$currentPostId)->save($update_data);

            $postReplyModel = M('ForumPostReply');
            $replyCount = $postReplyModel->where('status>0 and post_id='.$currentPostId)->count();

            $refreshPostLogModel->add(array(
                'old_post_id' => $currentPostId,
                'reply_count' => $replyCount,
                'reply_id'    => 0,
                'create_time' => time()
            ));

            $this->apiSuccess('refresh post -- post_id -- '.$currentPostId);
        }
    }

    /**
     * 更新主帖回复数据
     * tips: 修改当前更新帖中最久远的回复
     */
    public function RefreshPostReply(){
        $refreshPostLogModel = M('RefreshPostLog');
        $currentReFreshPost = $refreshPostLogModel->field('old_post_id')->where('reply_id=0 and status=1')->find();
        $currentPostId = $currentReFreshPost['old_post_id'];
        $postReplyModel = M('ForumPostReply');
        $forumPostModel = M('ForumPost');
        $tmpReplyInfo = $refreshPostLogModel->field('reply_count')->where('reply_id=0 and old_post_id='.$currentPostId)->find();
        $replyInfo = $postReplyModel->field('id')->where('status>0 and post_id='.$currentPostId)->order('create_time asc')->find();
        if($replyInfo){
            $rid = $replyInfo['id'];
            $update_data['create_time'] = time();
            $update_data['update_time'] = $update_data['create_time'];
            $postReplyModel->where('id='.$rid)->save($update_data);
            $forumPostModel->where('id='.$currentPostId)->save(array("last_reply_time" => $update_data['create_time']));

            $refreshPostLogModel->where('reply_id=0 and old_post_id='.$currentPostId)->setDec('reply_count');
            $replyLogData['old_post_id'] = $currentPostId;
            $replyLogData['reply_count'] = (int)$tmpReplyInfo['reply_count'] - 1;
            $replyLogData['reply_id'] = $rid;
            $replyLogData['create_time'] = time();
            $refreshPostLogModel->add($replyLogData);

            $this->apiSuccess('refresh post reply -- reply_id -- '.$rid);
        }
        $this->apiSuccess('refresh post reply -- reply info is null -- ');
    }

    /**
     * 判断帖子是否能被更新（需要时自己的账号）
     * @param $post_id
     * @return bool
     */
    private function isPostCanBeRefresh($post_id){
        $uCenterMemberModel = M('UcenterMember');
        $forumPostModel = M('ForumPost');
        $forumPost = $forumPostModel->field('uid')->where('id='.$post_id)->find();
        $user = $uCenterMemberModel->field('username')->where('id='.$forumPost['uid'])->find();
        if(strpos($user['username'], '1001') === 0){  // 自己账号
            return true;
        }
        return false;
    }


    /**
     * 判断帖子是否正常可见
     * @param $postId
     */
    private function isPostExist($postId){
        $forumPostModel = M('ForumPost');
        return $forumPostModel->where('status=1 and id='.$postId)->count();
    }

}