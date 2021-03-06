<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Controller;

use Think\Controller;
use Think\Model;
use Weibo\Api\WeiboApi;
use Think\Hook;

require('Application/Forum/Cache/ForumFilterCache.class.php');

define('TOP_ALL', 2);
define('TOP_FORUM', 1);


class ForumController extends AppController
{
    protected $forumModel;
    protected $forum_list;
    protected $forum_type;
    private static $adv_index = 1;
    private $recommend_uids;
    private $recommend_uid_arr;

    public function _initialize()
    {
        $this->forumModel = D('Forum/Forum');
        $this->forum_list = $this->forumModel->getForumList();
        $this->forum_type = D("ForumType")->where(array('status' => 1))->order('sort desc')->select();
        //判断板块能否发帖
        foreach ($this->forum_list as &$e) {
            $e['allow_publish'] = $this->isForumAllowPublish($e['id']);
        }
        unset($e);
        C('SHOW_PAGE_TRACE', false);
        $uids = M('Member')->where('status = 4')->field('uid')->select();
        foreach ($uids as $id){
            $uid_arr[] = $id['uid'];
        }
        $this->recommend_uid_arr = $uid_arr;
        $this->recommend_uids = implode(',', $uid_arr);
    }

    //分类列表
    private function getForumsByType($type_id = 0)
    {
        $forum_key_value = array();
        foreach ($this->forum_list as $f) {
            $forum_key_value[$f['id']] = $f;
        }
        foreach ($this->forum_type as $t) {
            if($type_id != 0) {
                if($t['id'] != $type_id)
                    continue;
            }
            foreach($forum_key_value as $tt){
                if($tt['type_id'] == $t['id'])
                    $types[] = array("title"=>$tt['title'],"id"=>$tt['id']);
            }

            $t['forums'] = $types;
            unset($types);
            unset($t['status']);
            unset($t['pid']);
            unset($t['sort']);
            $forum_type[] = $t;
        }
        return $forum_type;
    }

    //根据发帖位置分类
    private function getForumsByPosition($position = null)
    {
        $map['type'] = 0;
        $map['pos'] = array('like', '%'.$position.'%');
        $post_ids = M('ForumPos')->where($map)->field('post_id')->select();
        return $post_ids;
    }

    //根据发帖人的年级分类
    private function getForumsByGrade($grade=null){
        if($grade == '其他'){
            $model = new \Think\Model();
            $uids = $model->query("select distinct uid from hisihi_forum_post"
                                 ." where status=1 and uid != 0 and uid not in"
                                 ." (select distinct uid from hisihi_field where field_id=38)");
        }else{
            $map['field_id'] = 38;
            $map['field_data'] = $grade;
            $uids = M('Field')->where($map)->field('uid')->select();
        }
        return $uids;
    }

    //获取学生发帖
    public function getForumsFromStudents(){
        $model = new \Think\Model();
        $uids_list = $model->query('select distinct uid from hisihi_auth_group_access where group_id=5');
        $uids = array();
        foreach($uids_list as $uid_info){
            $uids[] = $uid_info['uid'];
        }
        $where_array['status'] = 1;
        $where_array['uid'] = array('neq', 0);
        $where_array['uid'] = array('in', $uids);
        $ids = M('ForumPost')->field('id')->where($where_array)->select();
        return $ids;
    }

    //获取老师发帖
    private function getForumsFromTeachers(){
        $model = new \Think\Model();
        $uids_list = $model->query('select distinct uid from hisihi_auth_group_access where group_id=6');
        $uids = array();
        foreach($uids_list as $uid_info){
            $uids[] = $uid_info['uid'];
        }
        $where_array['status'] = 1;
        $where_array['uid'] = array('neq', 0);
        $where_array['uid'] = array('in', $uids);
        $ids = M('ForumPost')->field('id')->where($where_array)->select();
        return $ids;
    }

    //获取老师发帖
    private function getForumsFromRandTeachers($uid=null){
        $model = new \Think\Model();
        $time1 = time();
        $uids = $model->query("SELECT DISTINCT
	uid
FROM
	hisihi_auth_group_access
WHERE
	group_id = 6
AND uid IN (
	SELECT DISTINCT
		uid
	FROM
		hisihi_field
	WHERE
		field_data = (
			SELECT
				field_data
			FROM
				hisihi_field
			WHERE
				field_id = 37
			AND uid = ".$uid."
		)
	AND uid != ".$uid."
) order by id asc LIMIT 10");
        $time2 = time();
        $hs1 = $time2 - $time1;
        if(empty($uids)){
            $time3 = time();
            $uids = $model->query("SELECT DISTINCT
	uid
FROM
	hisihi_auth_group_access
WHERE
	group_id = 6
	AND uid != ".$uid."
 order by id asc LIMIT 10");
            $time4 = time();
            $hs2 = $time4 - $time3;
        }
        $uid_arr = array();
        foreach ($uids as $id){
            $uid_arr[] = $id['uid'];
        }
        $uid_str = implode(',', $uid_arr);
        $time5 = time();
        $ids = $model->query("SELECT * from (SELECT
	id,
	uid,
	create_time
FROM
	hisihi_forum_post
WHERE
	STATUS = 1
AND uid != 0
AND uid IN (".$uid_str.") ORDER BY id desc) a
GROUP BY
	a.uid");
        $time6 = time();
        $hs3 = $time6 - $time5;
        return $ids;
    }

    //获取推荐人发帖
    private function getForumsFromRecommendUser(){
        $model = new \Think\Model();
       /* $ids = $model->query("SELECT * from (SELECT
	id,
	uid,
	create_time
FROM
	hisihi_forum_post
WHERE
	STATUS = 1
AND uid != 0
AND uid IN (".$this->recommend_uids.") ORDER BY id desc) a
GROUP BY
	a.uid");*/

        foreach ($this->recommend_uid_arr as $uid){
            $pid = M('ForumPost')->where('status=1 and uid='.$uid)->field('id')->limit(1)->order('id desc')->select();
            if($pid){
                $pids[]['id'] = $pid[0]['id'];
            }
        }
        $ids = $pids;
        return $ids;
    }

    private function getSameMajorUsers($major=null){
        if($major == '其他'||$major == '其它'){
            $tem_list = M()->query('select distinct uid from hisihi_field where field_id=37');
            $not_in_uid_list = array();
            foreach($tem_list as $item){
                $not_in_uid_list[] = $item['uid'];
            }
            $where_array['status'] = 1;
            $where_array['uid'] = array('neq', 0);
            $where_array['uid'] = array('not in', $not_in_uid_list);
            // 发了帖子没填专业的用户id
            $uids_list = M('ForumPost')->distinct(true)->field('uid')->where($where_array)->select();
            $uids = array();
            foreach($uids_list as $item){
                $uids[] = $item['uid'];
            }
            $major_list = M('RecomendMajors')->where('status=1')->field('name')->select();
            $except_uids = array();
            $map['field_id'] = 37;
            foreach($major_list as $item){
                $m = $item['name'];
                if($m!='其他'&&$m!='其它'){
                    $map['field_data'] = array('like', '%'.$m.'%');
                    $ids_list = M('Field')->where($map)->field('uid')->select();
                    foreach ($ids_list as $item) {
                        $except_uids[] = $item['uid'];
                    }
                }
            }

            $temp_map['field_id'] = 37;
            //$map['field_data'] = '其他';
            $temp_map['uid'] = array('not in', $except_uids);
            $second_uids_list = M('Field')->where($temp_map)->field('uid')->select();
            foreach($second_uids_list as $item){
                $uids[] = $item['uid'];
            }
        }else{
            $major_array = explode("#", $major);
            $map['field_id'] = 37;
            $uids = array();
            foreach($major_array as $item){
                $map['field_data'] = array('like', '%'.$item.'%');
                $ids_list = M('Field')->where($map)->field('uid')->select();
                foreach ($ids_list as $item) {
                    $uids[] = $item['uid'];
                }
            }
        }
        return $uids;
    }

    //获取关注人发帖
    private function getForumsFromFollows($uid=null){
        $model = new \Think\Model();
        $ids = $model->query("select id from hisihi_forum_post"
            ." where status=1 and uid != 0 and uid in"
            ." (select distinct follow_who from hisihi_follow where who_follow=".$uid.")");
        return $ids;
    }

    private function getForumsFromFollowsAndMajor($uid=null, $map=null){
        $uids = $map[1];
        $uid_str = implode(',', $uids);
        $model = new \Think\Model();
        $ids = $model->query("select id from hisihi_forum_post"
            ." where status=1 and uid != 0 and uid in"
            ." (select distinct follow_who from hisihi_follow where follow_who in (".$uid_str.") and who_follow=".$uid.")");
        return $ids;
    }

    private function hasFollowsWithMajor($uid=null, $map=null){
        $uids = $map[1];
        $uid_str = implode(',', $uids);
        $model = new \Think\Model();
        $ids = $model->query("select distinct follow_who from hisihi_follow where follow_who in (".$uid_str.") and who_follow=".$uid);
        if($ids){
            return true;
        }else{
            return false;
        }
    }

    //获取非关注人发帖
    private function getPostsFromUnFollowers($uid=null){
        $model = new \Think\Model();
        $ids = $model->query("select id from hisihi_forum_post"
            ." where status=1 and uid != 0 and uid not in"
            ." (select distinct follow_who from hisihi_follow where who_follow=".$uid.")"
            ." order by create_time desc");
        return $ids;
    }

    //获取上次发帖的forum_id
    private function getLastForumId($uid){
        $forum_id = M('ForumPost')->where('status=1 and uid='.$uid)
            ->order('create_time desc')->getField('forum_id');
        return $forum_id;
    }

    public function forumType()
    {
        $forum_type = $this->getForumsByType();
        $this->apiSuccess("获取类别标签成功", null, array('types' => $forum_type));
    }

    private function formatList($list, $version, $circle_type, $uid=0)
    {
        $map_support['appname'] = 'Forum';
        $map_support['table'] = 'post';

        foreach ($list as &$v) {
            $v['post_id'] = $v['id'];
            $forum = D('Forum')->find($v['forum_id']);
            $mapx = array('id' => $forum['type_id'], 'status' => 1);
            $forumType = D('ForumType')->where($mapx)->select();
            if(!empty($forum['title'])&&!empty($forumType[0]['title'])){
                $v['forumTitle'] = $forum['title'] . '/' . $forumType[0]['title'];
            } else {
                $v['forumTitle'] = null;
            }


            $v['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $v['uid']);
            if((float)$version>=2.2){
                $profile_group = A('User')->_profile_group($v['uid']);
                $info_list = A('User')->_info_list($profile_group['id'], $v['uid'], $version);
                $v['userInfo']['extinfo'] = $info_list;
            }
            if((float)$version>2.4){//2.5以后版本
                if($v['userInfo']['group'] == 5){
                    $v['first_teacher'] = $this->getFirstReplyTeacher($v['post_id']);
                }else{
                    $v['first_teacher'] = null;
                }
            }

            if((float)$version>2.5){//2.6版本
                $v['community'] = $circle_type;
            }

            if((float)$version>2.8){ // 2.9及以后
                $fttpr = M('ForumTopicToPostRelation')->field('topic_id')->where('status=1 and post_id='.$v['post_id'])->find();
                $topic_id = 0;
                if($fttpr){
                    $topic_id = $fttpr['topic_id'];
                }
                $record = M('ForumTopic')->field('id, title, description, img_url, is_hot')->where('status=1 and id='.$topic_id)->find();
                $v['topic_info'] = $record;
            }

            if((float)$version>=2.96 && $circle_type==3){
                $follow_other = D('Follow')->where(array('who_follow'=>$uid,'follow_who'=>$v['uid']))->find();
                $be_follow = D('Follow')->where(array('who_follow'=>$v['uid'],'follow_who'=>$uid))->find();
                if($follow_other&&$be_follow){
                    $v['userInfo']['relationship'] = 3;
                } else if($follow_other&&(!$be_follow)){
                    $v['userInfo']['relationship'] = 2;
                } else if((!$follow_other)&&$be_follow){
                    $v['userInfo']['relationship'] = 1;
                } else {
                    $v['userInfo']['relationship'] = 0;
                }
                $v['newly_comment'] = $this->getNewlyThreeCommentByPostId($v['post_id']);
            }

            //解析并成立图片数据
            $v['img'] = $this->match_img($v['content']);

            $v['sound'] = $this->fetchSound($v['id'],0);

            if((float)$version<2.96){
                $v['content'] = strip_tags($v['content'], '<user><topic>');
                $v['content'] = trim($this->parseAtAndTopic($v['content']));
            } else {
                $v['content'] = trim(strip_tags($v['content'], '<user><topic>'));
                $topic_id_list = $this->resolveTopicIdFromContent($v['content']);
                foreach($topic_id_list as $topic_id) {
                    if($v['topic_info']['id']==$topic_id){
                        $v['topic_info'] = null;
                    }
                }
            }

            $map_support['row'] = $v['id'];
            $supportCount = $this->getSupportCountCache($map_support);

            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = M('Support')->where($map_supported)->count();

            $v['supportCount'] = $supportCount;
            $v['isSupportd'] = $supported;

            $map_pos['type'] = 0;
            $map_pos['post_id'] = (int)$v['id'];
            $pos = $this->getForumPos($map_pos);
            $v['pos'] = $pos['pos'];
            $v['shareUrl'] = 'app.php/forum/detail/type/view/post_id/'.$v['id'].'/version/'.$version;

            unset($pos);
            unset($v['id']);
            unset($v['uid']);
            unset($v['forum']);
            unset($v['parse']);
            unset($v['status']);
            unset($v['forum_id']);
            unset($v['is_top']);
            unset($v['content_md5']);
            unset($v['post_type']);
            unset($v['is_out_link']);
            unset($v['link_url']);
            unset($v['update_time']);
            unset($v['title']);
            unset($v['is_elite']);
            unset($v['topic_id']);
        }
        unset($v);

        return $list;
    }

    /**
     * 论坛列表
     * @param int $type_id 帖子类型
     * @param int $page  分页参数
     * @param int $count  分页参数部 0 无回答 1 有回答
     * @param string $order  帖子列表排序
     * @param bool|false $show_adv
     * @param int $is_reply -1 全  是否在帖子列表中插入广告
     * @param int $post_type  1 论坛普通帖； 2 论坛公司帖
     */
    public function forum($type_id = 0, $page = 1, $count = 10, $is_reply = -1, $order = 'ctime',
                          $show_adv=false, $post_type=1)
    {
        $type_id = intval($type_id);
        $page = intval($page);
        $count = intval($count);
        $order = op_t($order);

        if ($order == 'ctime') {
            $order = 'create_time desc';
        } else {
            $order = 'last_reply_time desc';
        }

        //读取帖子列表
        $map = array('status' => 1);
        if($post_type==2){
            $map['post_type'] = 2;
        } else {
            $map['post_type'] = 1;
        }
        if ($type_id != 0) {
            $forums = $this->getForumsByType($type_id);
            if($forums == null){
                $this->apiError(404,"该分类下无任何提问");
            }
            $forums = $forums[0]['forums'];
            foreach($forums as $forum){
                $forumIds[]=$forum['id'];
            }
            $ids= implode(',',$forumIds);
            $map['forum_id'] = array('in',$ids);
        }

        if($is_reply == 0)
            $map['reply_count'] = $is_reply;
        if($is_reply == 1)
            $map['reply_count'] = array('gt',0);
        $map['is_top'] = 0;
        $list = D('ForumPost')->where($map)->order($order)->page($page, $count)->select();
        if(S('app_forum_forum_post_total_count'.md5($map))){
            $totalCount = S('app_forum_forum_post_total_count'.md5($map));
        } else {
            $totalCount = D('ForumPost')->where($map)->count();
            S('app_forum_forum_post_total_count'.md5($map), $totalCount, 600);
        }
        $list = $this->formatList($list);

        if($show_adv==true){
            $len = count($list);
            $now = time();
            $total_count = M('Advs')->where('position=4 and status=1 and '.$now.' between create_time and end_time')->count();
            if($total_count>0){
                $list[$len] = $this->getOneForumAdv(640, 960);
            }
        }

        $this->apiSuccess("获取提问列表成功", null, array( 'total_count' => $totalCount, 'forumList' => $list));
    }

    /**
     * forumFilter拦截器，用于缓存Api结果
     * created by leilei @2016.4.6
     */
    public function _before_forumFilter(){
        if (intval(I('get.no_read_cache')) || intval(I('post.no_read_cache'))){
            // 如果no_cache参数不传或者传入1，则不去读缓存
            return;
        }
        $cache = new \ForumFilterCache();
        $res_array = $cache->getPublicResCache();
        if(!$res_array){
            return;
        }
        else{
            $this->apiSuccess('获取提问列表成功', null, array( 'total_count' => $res_array['total_count'],
                'forumList' => $res_array['list']));
        }
    }

    /**
     * 论坛数据筛选
     * @param int $field_type -1 最新 -2 无回答 -3 有回答 -4 热门 1..帖子小类别
     * @param int $page
     * @param int $count
     * @param string $order
     * @param bool|false $show_adv
     * @param int $post_type  区别公司热门话题和普通论坛
     * @param null $circle_type 圈子类型
     * @param null $reply_type 评论类型,1未评论，2已评论
     * @param null $version 版本号
     * @param null $position 地理位置
     * @param null $grade 年级
     * @param null $major
     * @param bool|false $no_read_cache
     */
    public function forumFilter($field_type = -1, $page = 1, $count = 10, $order = 'reply',
                                $show_adv=false, $post_type=1, $version=null, $circle_type=null,
                                $reply_type=null,$position=null, $grade=null, $major=null,
                                $no_read_cache=false)
    {
        $no_read_cache = intval($no_read_cache);
        $field_type = intval($field_type);
        $page = intval($page);
        $count = intval($count);
        $order = op_t($order);
        $circle_type = intval($circle_type);
        $reply_type = intval($reply_type);

        if ($order == 'ctime') {
            $order = 'create_time desc';
        } else if ($order == 'reply') {
            $order = 'last_reply_time desc';
        } else if($order == 'hot'){//2.2以上版本"最热"
            $order = "reply_count desc";
        } else {
            $order = 'last_reply_time desc';//默认的
        }

        //读取帖子列表
        $map = array('status' => 1);
        if($post_type==2){
            $map['post_type'] = 2;
        } else {
            $map['post_type'] = 1;
        }
        if ($field_type > 0) {
            $forums = $this->getForumsByType($field_type);
            if($forums == null){
                $this->apiError(404,"该分类下无任何提问");
            }
            $forums = $forums[0]['forums'];
            foreach($forums as $forum){
                $forumIds[]=$forum['id'];
            }
            $ids= implode(',',$forumIds);
            $map['forum_id'] = array('in',$ids);
        }

        if($field_type==-1&&$circle_type!=3){
            if((float)$version>=2.96){
                $uid = $this->getUid();
                $ids = $this->getPostsFromUnFollowers($uid);
                $post_ids = array();
                foreach($ids as &$post_id){
                    $post_ids[] = $post_id['id'];
                }
                $map['id'] = array('in', $post_ids);
            }
        }
        if($field_type == -2)  // 无回复
            $map['reply_count'] = 0;
        if($field_type == -3)  // 有回复
            $map['reply_count'] = array('gt',0);
        if($field_type == -4){  // 热门
            $order = "reply_count desc";
        }

        if((float)$version>=2.96){
            if(!empty($major)){
                $ids = $this->getSameMajorUsers($major);
                $map['uid'] = array('in', $ids);
            }
        }

        //2.2以上版本筛选条件
        if($circle_type == 1){//学习圈
            $ids = $this->getForumsFromStudents();
            $post_ids = array();
            foreach($ids as &$post_id){
                $post_ids[] = $post_id['id'];
            }
            $map['id'] = array('in', $post_ids);
        }
        if($circle_type == 2){//老师圈
            $ids = $this->getForumsFromTeachers();
            $post_ids = array();
            foreach($ids as &$post_id){
                $post_ids[] = $post_id['id'];
            }
            $map['id'] = array('in', $post_ids);
        }
        if($circle_type == 3){//朋友圈
            $uid = $this->getUid();//session_id
            if(floatval($version) >= 2.96 && !empty($map['uid'])){
                $ids = $this->getForumsFromFollowsAndMajor($uid, $map['uid']);
                $has_major_follow = true;
                if(empty($ids)){
                    $has_major_follow = false;//该专业下没有关注的人发帖
                }
                //判断改专业下是否有关注的人
                $hasFollowsWithMajor = $this->hasFollowsWithMajor($uid, $map['uid']);
            }else{
                $ids = $this->getForumsFromFollows($uid);
            }
            if(floatval($version) >= 2.96){
                $extra['is_recommend'] = 2;
            }
            if(empty($ids)){
                unset($map['uid']);
                if(floatval($version) >= 2.96){
                    $ids = $this->getForumsFromRecommendUser();
                    if(!empty($major)){
                        if(!$hasFollowsWithMajor){//该专业下没有关注的人
                            $extra['is_recommend'] =3;
                        }else{
                            if(!$has_major_follow){//该专业下有关注的人但没有帖子
                                $extra['is_recommend'] = 1;
                            }
                        }
                    }else{
                        $extra['is_recommend'] =1;
                        $has_follow = M('Follow')->where('who_follow='.$uid)->find();
                        if(empty($has_follow)){
                            $extra['is_recommend'] = 0;
                        }
                    }
                }else{
                    $this->apiSuccess("你还没有关注的朋友", null, array('total_count' =>'0', 'forumList'=>array()));
                }
            }
            $post_ids = array();
            foreach($ids as &$post_id){
                $post_ids[] = $post_id['id'];
            }
            $map['id'] = array('in', $post_ids);
        }
        if($circle_type == 4){//精华圈
            $map['is_elite'] = 1;
        }

        if($reply_type == 1){//未评论
            $map['reply_count'] = 0;
        }else if($reply_type == 2){//已评论
            $map['reply_count'] = array('gt',0);
        }

        if(!empty($position)){//发帖位置
            $posts = $this->getForumsByPosition($position);
            $post_ids = array();
            foreach($posts as &$post_id){
                $post_ids[] = $post_id['post_id'];
            }
            if(!empty($map['id'])){//取帖子id交集
                $intersect_ids = array_intersect($map['id'][1], $post_ids);
                $map['id'] = array('in', $intersect_ids);
            }else{
                $map['id'] = array('in', $post_ids);
            }
        }
        if(!empty($grade)){//发帖人的年级
            $uids = $this->getForumsByGrade($grade);
            $post_uids = array();
            foreach($uids as &$post_uid){
                $post_uids[] = $post_uid['uid'];
            }
            $map['uid'] = array('in', $post_uids);
        }
        $map['is_top'] = 0;
        $forumPost = M('ForumPost');
        /*if(S('app_forum_forumfilter_post_total_count'.md5($map))){
            $totalCount = S('app_forum_forumfilter_post_total_count'.md5($map));
        } else {
            $totalCount = $forumPost->where($map)->count();
            S('app_forum_forumfilter_post_total_count'.md5($map), $totalCount, 600);
        }*/
        $totalCount = $forumPost->where($map)->count();
        $list = $forumPost->where($map)->order($order)->page($page, $count)->select();
        if($list){
            $list = $this->list_sort_by($list, 'last_reply_time');
            $list = $this->formatList($list, $version, $circle_type, is_login());
            if($show_adv==true){
                $adv_pos = $this->getAdvsPostion();
                foreach($adv_pos as $pos){
                    $list = $this->mergeAdvertismentToForumList($list, $pos, true, $circle_type);
                }
            }
        } else {
            $totalCount = 0;
            $list = array();
        }

        // setPublicResCache将删除某些属性，这里复制一份
        $list_copy = $list;
        $totalCount_copy = $totalCount;
        if($circle_type != 3 && !$no_read_cache){
            // 不缓存朋友圈数据, 如果no_cache=true，也不缓存数据
            $cache = new \ForumFilterCache();
            $cache->setPublicResCache($list, $totalCount);
            $cache->close();
        }
        $extra['total_count'] = $totalCount_copy;
        $extra['forumList'] = $list_copy;
        $this->apiSuccess("获取提问列表成功", null, $extra);
    }


    /**
     * 话题下帖子列表
     * @param int $topicId
     * @param null $position
     * @param int $page
     * @param int $count
     */
    public function forumFilterByTopic($topicId = 0, $position=null, $version=2.9, $page = 1, $count = 10)
    {
        $topicId = intval($topicId);
        $page = intval($page);
        $count = intval($count);
        $order = 'hisihi_forum_topic_to_post_relation.create_time desc';

        //读取帖子列表
        $map = array( 'topic_id' => $topicId,
            'hisihi_forum_topic_to_post_relation.status' => 1,
            'hisihi_forum_post.status' => 1);
        $map['post_type'] = 1;  // 个人提问

        if(!empty($position)){//发帖位置
            $posts = $this->getForumsByPosition($position);
            $post_ids = array();
            foreach($posts as &$post_id){
                $post_ids[] = $post_id['post_id'];
            }
            if(!empty($map['id'])){//取帖子id交集
                $intersect_ids = array_intersect($map['id'][1], $post_ids);
                $map['id'] = array('in', $intersect_ids);
            }else{
                $map['id'] = array('in', $post_ids);
            }
        }
        if(!empty($grade)){//发帖人的年级
            $uids = $this->getForumsByGrade($grade);
            $post_uids = array();
            foreach($uids as &$post_uid){
                $post_uids[] = $post_uid['uid'];
            }
            $map['uid'] = array('in', $post_uids);
        }
        $map['is_top'] = 0;
        $forumPost = M('ForumPost');
        $totalCount = $forumPost->join('hisihi_forum_topic_to_post_relation ON hisihi_forum_topic_to_post_relation.post_id = hisihi_forum_post.id')
            ->where($map)->count();
        $list = $forumPost->join('hisihi_forum_topic_to_post_relation ON hisihi_forum_topic_to_post_relation.post_id = hisihi_forum_post.id')
            ->where($map)
            ->field('hisihi_forum_post.*, hisihi_forum_topic_to_post_relation.topic_id, hisihi_forum_topic_to_post_relation.post_id')
            ->order($order)->page($page, $count)->select();
        if($list){
            $list = $this->list_sort_by($list, 'last_reply_time');
            $list = $this->formatList($list, $version, null);
        } else {
            $totalCount = 0;
            $list = array();
        }
        $this->apiSuccess("获取话题下提问列表成功", null, array( 'total_count' => $totalCount, 'forumList' => $list));
    }

    /**
     * 将广告内容和论坛内容合并
     * @param null $forum_list
     * @param int $pos
     * @param bool|true $show_adv
     * @param int $community
     * @return null
     */
    private function mergeAdvertismentToForumList($forum_list=null, $pos=3, $show_adv=true, $community=0){
        $pos = $pos - 1;
        if($show_adv){
            $adv = $this->getOneForumAdv(640, 960, $community);
            if($adv){
                array_splice($forum_list, $pos, 0, $adv);
            }
        }
        return $forum_list;
    }

    /**
     * 获取广告显示位置
     * @return array
     */
    private function getAdvsPostion(){
        $configModel = M('CompanyConfig');
        $pos = $configModel->field('value')->where('type=12 and status=1')->find();
        if($pos){
            $pos = $pos['value'];
            $pos = explode('#', $pos);
            return $pos;
        }
        return array();
    }

    /**
     * 对结果集排序
     * @param $list
     * @param $field
     * @param string $sortby
     * @return array|bool
     */
    private function list_sort_by($list, $field, $sortby='desc') {
        if(is_array($list)){
            $refer = $resultSet = array();
            foreach ($list as $i => $data)
                $refer[$i] = &$data[$field];
            switch ($sortby) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc':// 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ( $refer as $key=> $val)
                $resultSet[] = &$list[$key];
            return $resultSet;
        }
        return false;
    }

    //提问列表,置顶
    public function forum_top($id = 0, $page = 1, $count = 10, $order = 'reply')
    {
        $id = intval($id);
        $page = intval($page);
        $order = op_t($order);

        $id = intval($id);
        if ($order == 'ctime') {
            $order = 'create_time desc';
        } else if ($order == 'reply') {
            $order = 'last_reply_time desc';
        } else {
            $order = 'last_reply_time desc';//默认的
        }
        $this->requireForumAllowView($id);

        //读取置顶列表
        if ($id == 0) {
            $list_top = D('ForumPost')->where(' status=1 AND is_top=' . TOP_ALL)->order($order)->page($page, $count)->select();
            $totalCount = D('ForumPost')->where(' status=1 AND is_top=' . TOP_ALL)->count();
        } else {
            $list_top = D('ForumPost')->where('status=1 AND (is_top=' . TOP_ALL . ') OR (is_top=' . TOP_FORUM . ' AND forum_id=' . intval($id) . ' and status=1)')->order($order)->page($page, $count)->select();
            $totalCount = D('ForumPost')->where('status=1 AND (is_top=' . TOP_ALL . ') OR (is_top=' . TOP_FORUM . ' AND forum_id=' . intval($id) . ' and status=1)')->count();
        }

        $list_top = $this->formatList($list_top);

        $this->apiSuccess("获取置顶提问列表成功", null, array( 'total_count' => $totalCount,'forumList_top' => $list_top));
    }

    //指定用户提问/回答列表
    public function userForumList($uid = null, $page = 1, $count = 10, $tab = null, $version=null)
    {
        if($uid == null){
            $uid = is_login();
        }
            //$uid = -1;
        $user = query_user('uid',$uid);
        if($user == null)
            $this->apiError(-104,'用户ID有误');

        $type = 'forum';
        $className = ucfirst($type) . 'Protocol';

        $content = D(ucfirst($type) . '/' . $className)->profileList($uid, $page, $count, $tab);
        $content = $this->formatList($content, $version);
        if (empty($content)) {
            $totalCount = 0;
            $content = null;
        } else {
            $totalCount = D(ucfirst($type) . '/' . $className)->getTotalCount($uid, $tab);
        }

        //返回结果
        $this->apiSuccess("获取成功", null, array('total_count' => $totalCount, 'list' => $content));
    }

    public function getPostInfo($id, $lite=0, $version=null)
    {
        //读取帖子内容
        $field = 'id,uid,content,create_time';
        if($lite == 0)
            $field = $field . ',forum_id,title,create_time,last_reply_time, type, view_count,reply_count';
        $post = D('ForumPost')->where(array('id' => $id, 'status' => 1))->field($field)->find();
        if (!$post) {
            if($lite == 0)
                $this->apiError(404,'找不到该提问');
            else
                return null;
        }
        unset($post['id']);
        $post['post_id'] = $id;

        $post['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $post['uid']);
        if(floatval($version) >= 2.96){
            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$post['uid']))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$post['uid'],'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $post['userInfo']['relationship'] = $isfollowing | $isfans;
        }

        if((float)$version>=2.2){
            $profile_group = A('User')->_profile_group($post['uid']);
            $info_list = A('User')->_info_list($profile_group['id'], $post['uid'], $version);
            $post['userInfo']['extinfo'] = $info_list;
        }
        unset($post['uid']);

        if($lite == 0){
            $forum = D('Forum')->find($post['forum_id']);
            $mapx = array('id' => $forum['type_id'], 'status' => 1);
            $forumType = D('ForumType')->where($mapx)->select();
            if(!empty($forum['title'])&&!empty($forumType[0]['title'])){
                $post['forumTitle'] = $forum['title'] . '/' . $forumType[0]['title'];
            } else {
                $post['forumTitle'] = null;
            }
            $map_support['appname'] = 'Forum';
            $map_support['table'] = 'post';
            $map_support['row'] = $id;
            $supportCount = $this->getSupportCountCache($map_support);
            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();
            $post['supportCount'] = (int)$supportCount;
            $post['isSupportd'] = (int)$supported;

            $map_pos['type'] = 0;
            $map_pos['post_id'] = $id;
            $pos = $this->getForumPos($map_pos);
            $post['pos'] = $pos['pos'];

            unset($pos);
            unset($map_pos);
        }

        //解析并成立图片数据
        $post['img'] = $this->match_img($post['content']);

        $post['sound'] = $this->fetchSound($id,0);

        if((float)$version<2.96){
            $post['content'] = strip_tags($post['content'], '<user><topic>');
            $post['content'] = trim($this->parseAtAndTopic($post['content']));
        } else {
            $post['content'] = trim(strip_tags($post['content'], '<user><topic>'));
        }

        return $post;
    }

    public function getTopPostInfo($id, $lite = 0)
    {
        //读取帖子内容
        $field = 'id,uid,content,create_time';
        if($lite == 0)
            $field = $field . ',forum_id,title,create_time,last_reply_time, type, view_count,reply_count';
        $post = D('ForumPost')->where(array('id' => $id, 'status' => 1))->field($field)->find();
        if (!$post) {
            if($lite == 0)
                $this->apiError(404,'找不到该提问');
            else
                return null;
        }
        unset($post['id']);
        $post['post_id'] = $id;

        $post['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $post['uid']);
        unset($post['uid']);

        if($lite == 0){
            $forum = D('Forum')->find($post['forum_id']);
            $mapx = array('id' => $forum['type_id'], 'status' => 1);
            $forumType = D('ForumType')->where($mapx)->select();
            $post['forumTitle'] = $forum['title'] . '/' . $forumType[0]['title'];

            $map_support['appname'] = 'Forum';
            $map_support['table'] = 'post';
            $map_support['row'] = $id;
            $supportCount = $this->getSupportCountCache($map_support);
            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();
            $post['supportCount'] = $supportCount;
            $post['isSupportd'] = $supported;

            $map_pos['type'] = 0;
            $map_pos['post_id'] = $id;
            $pos = $this->getForumPos($map_pos);
            $post['pos'] = $pos['pos'];

            unset($pos);
            unset($map_pos);
        }
        //解析并成立图片数据
        $post['img'] = $this->match_img($post['content']);
        $post['sound'] = $this->fetchSound($id,0);
        return $post;
    }

    public function getReplyInfo($reply_id)
    {
        $map['id'] = $reply_id;
        $reply = D('ForumPostReply')->where($map)->field('uid, create_time, content, reply_to_student')->find();
        if(!$reply)
            return null;
        $reply['reply_id'] = $reply_id;

        $reply['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['uid']);

        unset($reply['uid']);

        //解析并成立图片数据
        $reply['img'] = $this->match_img($reply['content']);

        $reply['sound'] = $this->fetchSound($reply['reply_id'],1);

        $reply['content'] = op_t($reply['content']);

        unset($reply['user']);
        return $reply;
    }

    public function getLzlReplyInfo($lzl_id)
    {
        $lzl = D('ForumLzlReply')->field('to_f_reply_id,to_reply_id,uid,ctime,content')->find(intval($lzl_id));
        if(!$lzl)
            return null;
        $lzl['lzl_id'] = $lzl_id;
        $lzl['create_time'] = $lzl['ctime'];
        unset($lzl['ctime']);
        $lzl['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $lzl['uid']);
        unset($lzl['uid']);

        $lzl['img'] = $this->match_img($lzl['content']);
        $lzl['content'] = op_t($lzl['content']);
        $lzl['sound'] = $this->fetchSound($lzl['lzl_id'],2);
        return $lzl;
    }

    //提问详情
    public function detail($post_id, $page = 1, $count = 10, $type = '', $version=1)
    {
        //$this->requireLogin();\
        if($type == 'view') {
            if((float)$version>=2.96){
                $this->display('detail_2_9_7');
                exit;
            }
        }

        $id = intval($post_id);
        $page = intval($page);
        $count = intval($count);

        $this->requirePostExists($id);

        //判断是否需要显示1楼
        if ($page == 1) {
            $showMainPost = true;
            $post = $this->getPostInfo($id, 0, $version);
            //增加浏览次数
            //D('ForumPost')->where(array('id' => $id))->setInc('view_count');
        } else {
            $showMainPost = false;
        }

        //读取回复列表
        //$map = array('post_id' => $id, 'status' => 1);
        $map['post_id'] = $id;
        $map['status'] = array('in','1,3');
        $replyList = D('Forum/ForumPostReply')->getReplyList($map, 'status desc,create_time', $page, $count);

        $replyTotalCount = D('ForumPostReply')->where($map)->count();

        foreach ($replyList as &$reply) {
            //dump($reply);
            //$reply['content'] = op_h($reply['content'], 'html');
            $reply['reply_id'] = $reply['id'];
            unset($reply['id']);
            $map_pos['type'] = 1;
            $map_pos['post_id'] = $reply['reply_id'];
            $pos = $this->getForumPos($map_pos);
            $reply['pos'] = $pos['pos'];

            $reply['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['uid']);
            if((float)$version>2.2){
                $profile_group = A('User')->_profile_group($reply['uid']);
                $info_list = A('User')->_info_list($profile_group['id'], $reply['uid'], $version);
                $reply['userInfo']['extinfo'] = $info_list;
            }
            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$reply['uid']))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$reply['uid'],'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $reply['userInfo']['relationship'] = $isfollowing | $isfans;

            unset($reply['uid']);

            unset($pos);
            unset($map_pos);

            $map_support['table'] = 'reply';
            $map_support['row'] = $reply['reply_id'];

            $supportCount = $this->getSupportCountCache($map_support);

            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();

            $reply['supportCount'] = $supportCount;
            $reply['isSupportd'] = $supported;

            //解析并成立图片数据
            $reply['img'] = $this->match_img($reply['content']);

            $reply['sound'] = $this->fetchSound($reply['reply_id'],1);

            $reply['content'] = op_t($reply['content']);

            unset($reply['user']);
            $lzlList = D('Forum/ForumLzlReply')->getLZLReplyList($reply['reply_id'],'ctime asc',$page, $limit,false);
            foreach ($lzlList as &$lzl) {
                $lzl['lzl_id'] = $lzl['id'];
                unset($lzl['id']);
                $lzl['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $lzl['uid']);
                unset($lzl['uid']);
                //unset($reply['uid']);

                $map_pos['type'] = 2;
                $map_pos['post_id'] = $lzl['lzl_id'];
                $pos = $this->getForumPos($map_pos);
                $lzl['pos'] = $pos['pos'];

                unset($pos);
                unset($map_pos);

                $lzl['img'] = $this->match_img($lzl['content']);
                $lzl['content'] = op_t($lzl['content']);
                $lzl['sound'] = $this->fetchSound($lzl['lzl_id'],2);
            }
            $reply['lzlList'] = $lzlList;
        }
        unset($reply);
        //判断是否已经收藏
        $isBookmark = D('Forum/ForumBookmark')->exists(is_login(), $id);
        if($type == 'view') {
            $this->assign('post', $post);
            $this->assign('post_img', $post['img']);
            $this->assign('replyList',$replyList);
            $this->setTitle('{$post.title|op_t} — 嘿设汇');
            if((float)$version<2.96){
                $this->display();
            } else {
                $this->display('detail_2_9_7');
            }
        }
        else
            $this->apiSuccess("获取提问内容成功", null, array('isBookmark' => $isBookmark, 'replyTotalCount' =>  $replyTotalCount, 'post' => $post, 'replyList' => $replyList, 'showMainPost' => $showMainPost));
    }

    /**
     * 获取点赞详情
     * @param null $uid
     * @param null $post_id
     * @param int $page
     * @param int $count
     */
    public function getSupportDetailList($uid=null, $post_id=null, $page=1, $count=10){
        if(empty($uid)){
            $uid = is_login();
        }
        if(empty($post_id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $map_support['appname'] = 'Forum';
        $map_support['table'] = 'post';
        $map_support['row'] = $post_id;
        $totalCount = M('Support')->where($map_support)->count();
        $list = M('Support')->field('uid')->where($map_support)->page($page, $count)->order('create_time desc')->select();
        foreach($list as &$user){
            $c_uid = $user['uid'];
            $user['info'] = query_user(array('avatar256', 'avatar128', 'group', 'extinfo', 'nickname'), $c_uid);
            $follow_other = D('Follow')->where(array('who_follow'=>$uid,'follow_who'=>$c_uid))->find();
            $be_follow = D('Follow')->where(array('who_follow'=>$c_uid,'follow_who'=>$uid))->find();
            if($follow_other&&$be_follow){
                $user['relationship'] = 3;
            } else if($follow_other&&(!$be_follow)){
                $user['relationship'] = 2;
            } else if((!$follow_other)&&$be_follow){
                $user['relationship'] = 1;
            } else {
                $user['relationship'] = 0;
            }
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $list;
        $this->apiSuccess('获取点赞详情成功', null, $extra);
    }

    /**
     * 获取贴子详情
     * @param null $post_id
     * @param null $version
     * @param int $community
     */
    public function getPostDetail($post_id=null, $version=null, $community=0){
        if(empty($post_id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $post = $this->getPostInfo($post_id, 0, $version);
        unset($post['reply_count']);
        $map['post_id'] = $post_id;
        $map['status'] = array('in','1,3');
        $model = M('AuthGroupAccess');
        $replyTotalList = D('ForumPostReply')->field('uid, reply_to_student')->where($map)->select();
        $teacherReplyTotalCount = 0;
        $studentReplyTotalCount = 0;
        foreach($replyTotalList as $t_reply){
            $identify = $model->where('group_id=6 and uid='.$t_reply['uid'])->find();  // 判断老师身份
            $toStudent = $t_reply['reply_to_student'];
            if($identify&&$toStudent==0){
                $teacherReplyTotalCount++;
            } else {
                $studentReplyTotalCount++;
            }
        }
        $post['teacherReplyTotalCount'] = $teacherReplyTotalCount;
        $post['studentReplyTotalCount'] = $studentReplyTotalCount;
        $post['reply_count'] = $teacherReplyTotalCount + $studentReplyTotalCount;
        if((float)$version<2.96){
            $post['shareUrl'] = C('HOST_NAME_PREFIX').'app.php/forum/toppostdetailv2/post_id/'.$post_id;
        } else {
            $post['shareUrl'] = C('HOST_NAME_PREFIX').'app.php/forum/detail/version/2.97/post_id/'.$post_id;
        }
        if((float)$version>=2.6){
            $post['post_detail_adv'] = $this->getOneForumPostDetailAdv($community);
            if(!$post['post_detail_adv']){
                $post['post_detail_adv'] = null;
            }
        }
        if((float)$version>2.8){ // 2.9及以后
            $fttpr = M('ForumTopicToPostRelation')->field('topic_id')->where('post_id='.$post['post_id'])->find();
            $topic_id = 0;
            if($fttpr){
                $topic_id = $fttpr['topic_id'];
            }
            $record = M('ForumTopic')->field('id, title, description, img_url, is_hot')->where('status=1 and id='.$topic_id)->find();
            $post['topic_info'] = $record;
        }

        if((float)$version>=2.96){
            $topic_id_list = $this->resolveTopicIdFromContent($post['content']);
            foreach($topic_id_list as $topic_id) {
                if($post['topic_info']['id']==$topic_id){
                    $post['topic_info'] = null;
                }
            }
        }
        $extra['data'] = $post;
        $this->apiSuccess('获取帖子详情成功', null, $extra);
    }


    /**
     * 获取讲师回答列表
     * @param $post_id
     * @param int $page
     * @param int $count
     * @param int $version
     */
    public function teacherReplyList($post_id, $page = 1, $count = 10, $version = 1, $last_id=0){
        $id = intval($post_id);
        $page = intval($page);
        $count = intval($count);

        $this->requirePostExists($id);

        //读取回复列表
        $map['post_id'] = $id;
        $map['status'] = array('in','1,3');
        $replyList = D('Forum/ForumPostReply')->getNoCacheTeacherReplyList($map, 'create_time desc', $page, $count, $last_id);

        $model = M('AuthGroupAccess');
        $replyTotalList = D('ForumPostReply')->field('uid, reply_to_student')->where($map)->select();
        $replyTotalCount = 0;
        $rcount = M()->query('SELECT COUNT(a.id) as count from hisihi_forum_post_reply a LEFT JOIN hisihi_auth_group_access b ON a.uid = b.uid
WHERE b.group_id=6 and a.post_id='.$post_id.' and a.`status` in (1,3) and a.reply_to_student=0
ORDER BY a.create_time DESC');
        $replyTotalCount = intval($rcount[0]['count']);
        /*foreach($replyTotalList as $t_reply){
            $identify = $model->where('group_id=6 and uid='.$t_reply['uid'])->find();  // 判断老师身份
            if($identify&&$t_reply['reply_to_student']==0){
                $replyTotalCount++;
            }
        }*/

        $teacherReplyList = array();
        foreach ($replyList as &$reply) {
            $reply_uid = $reply['uid'];
            $toStudent = $reply['reply_to_student'];
            $access_list = $model->where('group_id=6 and uid='.$reply_uid)->find();  // 只显示老师的回复
            if(empty($access_list)||$toStudent==1){
                continue;
            }
            $reply['reply_id'] = $reply['id'];
            unset($reply['id']);
            $map_pos['type'] = 1;
            $map_pos['post_id'] = $reply['reply_id'];
            $pos = $this->getForumPos($map_pos);
            $reply['pos'] = $pos['pos'];

            $reply['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['uid']);

            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$reply['uid']))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$reply['uid'],'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $reply['userInfo']['relationship'] = $isfollowing | $isfans;
            if(floatval($version) > 2.2){
                $info_from_org = A('User')->getInfoFromOrg($reply['uid']);
                $reply['userInfo']['institution'] = $info_from_org['name'];
            }

            unset($reply['uid']);

            unset($pos);
            unset($map_pos);

            $map_support['table'] = 'reply';
            $map_support['row'] = $reply['reply_id'];

            $supportCount = $this->getSupportCountCache($map_support);

            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();

            $reply['supportCount'] = $supportCount;
            $reply['isSupportd'] = $supported;

            //解析并成立图片数据
            $reply['img'] = $this->match_img($reply['content']);

            $reply['sound'] = $this->fetchSound($reply['reply_id'],1);

            $reply['content'] = op_t($reply['content']);

            unset($reply['user']);
            $lzlList = D('Forum/ForumLzlReply')->getLZLReplyList($reply['reply_id'],'ctime asc', $page, $limit, false);
            foreach ($lzlList as &$lzl) {
                $lzl['lzl_id'] = $lzl['id'];
                unset($lzl['id']);
                $lzl['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $lzl['uid']);
                if(floatval($version) >= 2.96){
                    $lzl['toUserInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $lzl['to_uid']);
                }
                unset($lzl['uid']);

                $map_pos['type'] = 2;
                $map_pos['post_id'] = $lzl['lzl_id'];
                $pos = $this->getForumPos($map_pos);
                $lzl['pos'] = $pos['pos'];

                unset($pos);
                unset($map_pos);

                $lzl['img'] = $this->match_img($lzl['content']);
                $lzl['content'] = op_t($lzl['content']);
                $lzl['sound'] = $this->fetchSound($lzl['lzl_id'],2);
            }
            $reply['lzlList'] = $lzlList;
            $teacherReplyList[] = $reply;
        }
        unset($reply);
        //unset($replyList);
        $this->apiSuccess("获取讲师回复内容成功", null, array('replyTotalCount' =>  $replyTotalCount,
            'replyList' => $teacherReplyList));
    }

    /**
     * 获取学生回答列表
     * @param $post_id
     * @param int $page
     * @param int $count
     */
    public function studentReplyList($post_id=null, $page = 1, $count = 10, $version=0){
        $id = intval($post_id);
        $page = intval($page);
        $count = intval($count);

        $this->requirePostExists($id);

        //读取回复列表
        $map['post_id'] = $id;
        $map['status'] = array('in','1,3');
        $replyList = D('Forum/ForumPostReply')->getNoCacheStudentReplyList($map, 'support_count desc, create_time desc', $page, $count);

        $model = M('AuthGroupAccess');
        $replyTotalList = D('ForumPostReply')->field('uid, reply_to_student')->where($map)->select();
        $replyTotalCount = 0;
        $rcount = M()->query('SELECT COUNT(a.id) as count from hisihi_forum_post_reply a LEFT JOIN hisihi_auth_group_access b ON a.uid = b.uid
WHERE (a.reply_to_student=1 and a.post_id='.$post_id.')
 or (b.group_id=5 and a.post_id='.$post_id.' and a.`status` in (1,3))
ORDER BY a.create_time DESC');
        $replyTotalCount = intval($rcount[0]['count']);
        /*foreach($replyTotalList as $t_reply){
            $identify = $model->where('group_id=5 and uid='.$t_reply['uid'])->find();  // 判断学生身份
            $toStudent = $t_reply['reply_to_student'];
            if($identify||$toStudent==1){
                $replyTotalCount++;
            }
        }*/

        $studentReplyList = array();
        foreach ($replyList as &$reply) {
            $reply_uid = $reply['uid'];
            $toStudent = $reply['reply_to_student'];
            $access_list = $model->where('group_id=5 and uid='.$reply_uid)->find();
            if(empty($access_list)&&$toStudent==0){
                continue;
            }
            $reply['reply_id'] = $reply['id'];
            unset($reply['id']);
            $map_pos['type'] = 1;
            $map_pos['post_id'] = $reply['reply_id'];
            $pos = $this->getForumPos($map_pos);
            $reply['pos'] = $pos['pos'];

            $reply['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['uid']);
            if(floatval($version) >= 2.96){
                if(intval($reply['to_uid']) > 0){
                    $reply['toUserInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['to_uid']);
                }else{
                    $reply['toUserInfo'] = null;
                }
            }

            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$reply['uid']))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$reply['uid'],'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $reply['userInfo']['relationship'] = $isfollowing | $isfans;

            unset($reply['uid']);

            unset($pos);
            unset($map_pos);

            $map_support['table'] = 'reply';
            $map_support['row'] = $reply['reply_id'];

            //$supportCount = $this->getSupportCountCache($map_support);

            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();

            //$reply['supportCount'] = $supportCount;
            $reply['isSupportd'] = $supported;

            //解析并成立图片数据
            $reply['img'] = $this->match_img($reply['content']);

            $reply['sound'] = $this->fetchSound($reply['reply_id'],1);

            $reply['content'] = op_t($reply['content']);

            unset($reply['user']);

            $reply['lzlList'] = null;
            $studentReplyList[] = $reply;
        }
        unset($reply);
        unset($replyList);
        $this->apiSuccess("获取学生回复内容成功", null, array('replyTotalCount' =>  $replyTotalCount,
            'replyList' => $studentReplyList));
    }

    /**
     * 上传图片
     * @author RFly
     */
    public function uploadPicture()
    {
        //TODO: 用户登录检测
        $this->requireLogin();

        /* 调用文件上传组件上传文件 */
        $Picture = D('Admin/Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        ); //TODO:上传到远程服务器
        /* 记录图片信息 */
        if ($info) {
            foreach($info as $key=>&$value){
                $value = $value['id'];
            }
        } else {
            $this->apiError(-1,"上传图片失败".$Picture->getError());
        }
        $this->apiSuccess("上传图片成功",null,array('pictures'=>implode(',',$info)));
    }

    /**
     * 上传声音
     * @author RFly
     */
    public function uploadSound($duration = 0)
    {
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
        /* 调用文件上传组件上传文件 */
        $Sound = D('ForumSound');
        $file_driver = C('DOWNLOAD_UPLOAD_DRIVER');
        $info = $Sound->upload(
            $_FILES,
            C('DOWNLOAD_UPLOAD'),
            C('DOWNLOAD_UPLOAD_DRIVER'),
            C("UPLOAD_{$file_driver}_CONFIG"),
            $duration
        );
        /* 记录附件信息 */
        if ($info) {
            foreach($info as $key=>&$value){
                $value = $value['id'];
            }
        } else {
            $this->apiError(-1,"上传声音失败".$Sound->getError());
        }
        $this->apiSuccess("上传声音成功",null,array('sound'=>implode(',',$info)));
    }

    /**
     * 发表提问
     * @param null $post_id
     * @param int $forum_id
     * @param string $title
     * @param string $content
     * @param null $pos
     * @param null $pictures
     * @param null $sound
     * @param null $atUids
     * @param int $at_type   1 为正常论坛包括@用户提问   2 为@公司提问
     * @param int $topicId
     * @param string $version
     * @throws \Common\Exception\ApiException
     */
    public function doPost($post_id = null, $forum_id = 0, $title='标题', $content = ' ', $pos = null, $pictures = null,
                           $sound = null, $atUids = null, $at_type=1, $topicId=0, $version=null)
    {
        $this->requireLogin();
        $post_id = intval($post_id);
        $forum_id = intval($forum_id);
        $title = op_t($title);
        if(empty($title)){
            $title='标题';
        }
        if($content == ' ' && $pictures == null && $sound == null)
            $this->apiError(-102,'缺少内容，图片、声音、文字至少其一!');

        //判断是不是编辑模式
        $isEdit = $post_id ? true : false;
        $forum_id = intval($forum_id);

        //如果是编辑模式，确认当前用户能编辑帖子
        if ($isEdit) {
            $this->requireAllowEditPost($post_id);
        }

        //确认当前论坛能发帖
        $this->requireForumAllowPublish($forum_id);


        if ($title == '') {
            $this->apiError(-100,'缺少标题!');
        }
        if ($forum_id == 0) {
            $uid = $this->getUid();//需要传session_id
            $forum_id = $this->getLastForumId($uid);
            if(empty($forum_id)){
                $forum_id = 48;//第一次发帖默认：设计吐槽
            }
            //$this->apiError(-101,'未选择标签');
        }

        $pictures_ids_str = $pictures;
        $pictures = $this->fetchImage($pictures);
        foreach($pictures as $pic) {
            $content = $content.$pic;
        }

        $content = filterBase64($content);
        //检测图片src是否为图片并进行过滤
        $content = filterImage($content);

        $origin_content = $content;

        //写入帖子的内容
        $model = D('Forum/ForumPost');
        if ($isEdit) {
            $data = array('id' => intval($post_id), 'title' => $title, 'content' => $content, 'parse' => 0, 'forum_id' => intval($forum_id));
            $result = $model->editPost($data);
            if (!$result) {
                $this->apiError($model->getError(),'编辑失败!');
            }
        } else {
            $content_md5 = md5($content);

            if($at_type == 1){
                $data = array('uid' => is_login(), 'title' => $title, 'content' => $content, 'parse' => 0, 'forum_id' => $forum_id, 'content_md5' => $content_md5);
            } else { //@公司发帖，post_type=2
                $data = array('uid' => is_login(), 'title' => $title, 'content' => $content, 'parse' => 0, 'forum_id' => $forum_id, 'content_md5' => $content_md5, 'post_type'=>2);
            }
            $before = getMyScore();
            $tox_money_before = getMyToxMoney();
            $after = getMyScore();
            $result = $model->createPost($data);
            $tox_money_after = getMyToxMoney();
            if (!$result) {
                $this->apiError($model->getError(),'提问失败!');
            }
            $post_id = $result;

            if($pos != null) {
                $map_pos['post_id'] = $post_id;
                $map_pos['type'] = 0;
                $map_pos['pos'] = $pos;
                $this->setForumPos($map_pos);
            }

            if($sound != null) {
                $Sound = D('ForumSound');
                $data = array('fid' => $post_id,'ftype' => 0);
                $data = $Sound->create($data);
                if (!$data) {
                    $this->apiError(0, $Sound->getError());
                }
                $Sound->where(array('id' => $sound))->save($data);
            }
        }

        // 写入用户作品表
        $pictures_ids_list = explode(',',$pictures_ids_str);
        foreach ($pictures_ids_list as $pic_id) {
            if(!$pic_id){
                continue;
            }
            $user_works_data['uid'] = is_login();
            $user_works_data['forum_id'] = $forum_id;
            $user_works_data['post_id'] = $post_id;
            $user_works_data['picture_id'] = $pic_id;
            $user_works_data['create_time'] = NOW_TIME;
            $user_works_model = D('User/UserWorks');
            $user_works_model->saveWorks($user_works_data);
        }

        // 解析帖子内容绑定话题
        if((float)$version>=2.96){
            $origin_content = stripslashes($origin_content);
            if(!empty($origin_content)){
                $topic_id_list = $this->resolveTopicIdFromContent($origin_content);
                foreach($topic_id_list as $topic_id){
                    $this->bindPostToTopicId($post_id, $topic_id);
                }
            }
        }

        /* -- 记录自动回复数据 -- */
        if($at_type == 1){
            $auto_reply = S('auto_reply');
            if($auto_reply){
                $cache = explode(',', $auto_reply);
            } else {
                $cache = array();
            }
            array_push($cache, $post_id);
            S('auto_reply', implode(',', $cache));
        }
        /* -- -------------- -- */

        //发布帖子成功，发送一条微博消息
        $postUrl = "http://$_SERVER[HTTP_HOST]" . U('Forum/Index/detail', array('id' => $post_id));
        $weiboApi = new WeiboApi();
        $weiboApi->resetLastSendTime();


        //实现发布帖子发布图片微博(公共内容)
        $type = 'feed';
        $feed_data = array();
        //解析并成立图片数据
        $arr = array();
        preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/", $data['content'], $arr); //匹配所有的图片

        if (!empty($arr[0])) {

            $feed_data['attach_ids'] = '';
            $dm = "http://$_SERVER[HTTP_HOST]" . __ROOT__; //前缀图片多余截取
            $max = count($arr['1']) > 9 ? 9 : count($arr['1']);
            for ($i = 0; $i < $max; $i++) {
                $tmparray = strpos($arr['1'][$i], $dm);
                if (!is_bool($tmparray)) {
                    $path = mb_substr($arr['1'][$i], strlen($dm), strlen($arr['1'][$i]) - strlen($dm));
                    $result_id = D('Home/Picture')->where(array('path' => $path))->getField('id');

                } else {
                    $path = $arr['1'][$i];
                    $result_id = D('Home/Picture')->where(array('path' => $path))->getField('id');
                    if (!$result_id) {
                        $result_id = D('Home/Picture')->add(array('path' => $path, 'url' => $path, 'status' => 1, 'create_time' => time()));
                    }
                }
                $feed_data['attach_ids'] = $feed_data['attach_ids'] . ',' . $result_id;
            }
            $feed_data['attach_ids'] = substr($feed_data['attach_ids'], 1);
        }

        $feed_data['attach_ids'] != false && $type = "image";

        //开始发布微博
        if ($isEdit) {
            $weiboApi->sendWeibo("我更新了提问【" . $title . "】：" . $postUrl, $type, $feed_data, 'App');
        } else {
            $weiboApi->sendWeibo("我发表了一个新的提问【" . $title . "】：" . $postUrl, $type, $feed_data, 'App');
        }

        //处理帖子@某人，给@到的人发送消息
        //$uids = get_at_uids($content);
        $uids = explode(',',$atUids);
        $this->sendAtMessage($uids, $post_id, $title);

        // 发送向你提问的推送通知
        $model = D('Home/Member');
        $nickname = $model->getNickName($this->getUid());
        if($content != ' '){
            $content_length = mb_strlen($content);
            if($content_length>30){
                $tail_content = mb_substr($content, 0, 30, 'utf-8');
                $tail_content = $tail_content . '...';
            } else {
                $tail_content = $content;
            }
        } elseif($pictures!=null){
            $tail_content = "[图片]";
        } else {
            $tail_content = "[语音]";
        }
        $param['alert_info'] = $nickname . '向你提问:' . $tail_content;
        $param['question_id'] = $post_id;
        $param['fans_id'] = $this->getUid();

        $reg_ids = array();
        foreach ($uids as $uid) {
            $reg_id = $model->getRegId($uid);
            if(!empty($reg_id)){
                array_push($reg_ids, $reg_id);
            }
            // 添加 @ 数据到对应表中
            if($at_type == 1){//普通帖子
                D('Forum/ForumAt')->addAtPost($param['fans_id'], $uid, $post_id);
            }else {//@公司帖子
                D('Forum/ForumAt')->addAtPost($param['fans_id'], $uid, $post_id, 2);
            }
        }
        $param['reg_id'] = $reg_ids;
        $param['user_id'] = $atUids;
        $param['production'] = C('APNS_PRODUCTION');
        if($param['fans_id']!=$param['user_id']){
            Hook::exec('Addons\\JPush\\JPushAddon', 'push_question_asked', $param);
        }

        // 绑定帖子与话题
        if((float)$version>=2.9&&(float)$version<2.96){
            if($topicId!=0){
                $this->bindPostToTopicId($post_id, $topicId);
            }
        }

        //显示成功消息
        $message = $isEdit ? '编辑成功。' : '提问成功。' . getScoreTip($before, $after) . getToxMoneyTip($tox_money_before, $tox_money_after);
        $extra['post_id'] = $post_id;
        $extra['shareUrl'] = 'app.php/forum/detail/type/view/post_id/'.$post_id.'/version/'.$version;
        $uid = $this->getUid();
        if($this->checkUserDoPostCache($uid)){
            if(increaseScore($uid, 3)){
                $extraData['scoreAdd'] = "3";
                $extraData['scoreTotal'] = getScoreCount($uid);
                $extra['score'] = $extraData;
                insertScoreRecord($uid, 3, '用户发帖');
            }
        }
        $this->apiSuccess($message,null, $extra);
    }

    /**
     * 绑定帖子与话题
     * @param int $postId
     * @param int $topicId
     */
    public function bindPostToTopicId($postId=0, $topicId=0){
        $model = M('ForumTopicToPostRelation');
        if($postId==0 || $topicId==0){
            $this->apiError(-1, '帖子id和话题id不能为空!');
        }
        $select_array['topic_id'] = $topicId;
        $select_array['post_id'] = $postId;
        $select_array['status'] = 1;
        $count = $model->where($select_array)->count();
        if($count==0){
            $data['topic_id'] = $topicId;
            $data['post_id'] = $postId;
            $data['create_time'] = time();
            $model->add($data);
        }
    }

    public function topicPostListView($topicId=0){
        $this->assign("topicId", $topicId);
        $this->display("topicshare");
        exit;
    }

    /**
     * 检查用户发帖行为是否还能加积分
     * @param int $uid
     * @return bool
     */
    public function checkUserDoPostCache($uid=0){
        $data = S($uid.'_doPost');  //  查询用户收藏缓存
        if($data){
            $cacheData['date'] = date('Y-m-d');
            if(strtotime($cacheData['date'])>strtotime($data['date'])){  // 判断缓存是否是今天的，清空今天以前的缓存
                S($uid.'_doPost', array('date'=>$cacheData['date'], 'count'=>1));
                return true;
            } else {
                if($data['count']>3){   // 如果今天发帖次数超过3次，禁止再加积分
                    $count = $data['count'] + 1;
                    S($uid.'_doPost', array('date'=>$cacheData['date'], 'count'=>$count));
                    return false;
                } else {
                    return true;
                }
            }
        }
        return true;
    }

    /**
     * 检查用户回复行为是否还能加积分
     * @param int $uid
     * @return bool
     */
    public function checkUserDoReplyCache($uid=0){
        $data = S($uid.'_doReply');  //  查询用户收藏缓存
        if($data){
            $cacheData['date'] = date('Y-m-d');
            if(strtotime($cacheData['date'])>strtotime($data['date'])){  // 判断缓存是否是今天的，清空今天以前的缓存
                S($uid.'_doReply', array('date'=>$cacheData['date'], 'count'=>1));
                return true;
            } else {
                if($data['count']>5){   // 如果今天回复次数超过5次，禁止再加积分
                    $count = $data['count'] + 1;
                    S($uid.'_doReply', array('date'=>$cacheData['date'], 'count'=>$count));
                    return false;
                } else {
                    return true;
                }
            }
        }
        return true;
    }

    /**
     * 用户普通回复
     * @param $post_id
     * @param string $content
     * @param null $pos
     * @param null $pictures
     * @param null $sound
     */
    public function doReply($post_id, $content = ' ', $pos = null, $pictures = null, $sound = null,
                            $toStudent=0, $toUid=0, $version=0)
    {
        $this->requireLogin();

        $this->requirePostExists($post_id);

        $post_id = intval($post_id);
        $content = $this->filterPostContent($content);

        if($content == ' ' && $pictures == null && $sound == null)
            $this->apiError(-102,'缺少内容，图片、声音、文字至少其一!');

        if($content != ' ')
            $content = op_t($content);

        //确认有权限回复
        $this->requireAllowReply($post_id);

        //检测回复时间限制
        $uid = is_login();
        $near = D('ForumPostReply')->where(array('uid' => $uid))->order('create_time desc')->find();

        $cha = time() - $near['create_time'];
        if ($cha > 10) {
            //根据id获取图片
            $pictures = $this->fetchImage($pictures);
            foreach($pictures as $pic) {
                $content = $content.$pic;
            }
            //添加到数据库
            $model = D('Forum/ForumPostReply');
            $before = getMyScore();
            $tox_money_before = getMyToxMoney();
            if(floatval($version) >= 2.96){
                if(intval($toStudent) > 0 || intval($toUid) > 0)
                    $toStudent = 1;
                $result = $model->addReply($post_id, $content, $toStudent, intval($toUid));
            }else{
                $result = $model->addReply($post_id, $content, $toStudent);
            }
            if (!$result) {
                $this->apiError($model->getError(),'回复失败');
            }
            if($pos != null) {
                $map_pos['post_id'] = $result;
                $map_pos['type'] = 1;
                $map_pos['pos'] = $pos;
                $this->setForumPos($map_pos);
            }

            if($sound != null) {
                $Sound = D('ForumSound');
                $data = array('fid' => $result,'ftype' => 1);
                $data = $Sound->create($data);
                if (!$data) {
                    $this->apiError(0, $Sound->getError());
                }
                $Sound->where(array('id' => $sound))->save($data);
            }

            $after = getMyScore();
            $tox_money_after = getMyToxMoney();
	    
	        //当前用户如果是讲师，则回复需要置顶
            $user = query_user(array('group'),$uid);
            if($user['group'] == 6 && $result)
                $model->setReplyTop($result);
            clean_query_user_cache($uid, array('replycount'));

            // 发送提问被回复的推送通知
            $model = D('Home/Member');
            $nickname = $model->getNickName($uid);
            if($content != ' '){
                $content_length = mb_strlen($content);
                if($content_length>30){
                    $tail_content = mb_substr($content, 0, 30, 'utf-8');
                    $tail_content = $tail_content . '...';
                } else {
                    $tail_content = $content;
                }
            } elseif($pictures!=null){
                $tail_content = "[图片]";
            } else {
                $tail_content = "[语音]";
            }
            $param['alert_info'] = $nickname . '回答了你的提问:' . $tail_content;
            $param['question_id'] = $post_id;
            $param['reply_id'] = $result;
            $param['fans_id'] = $uid;
            $forum_model = D('Forum/ForumPost');
            $map = array('id' => $post_id);
            $forum_detail = $forum_model->where($map)->find();
            $param['user_id'] = $forum_detail['uid'];
            $map = array('uid'=>$param['user_id']);
            $_user = $model->where($map)->find();
            $param['reg_id'] = $_user['reg_id'];
            $param['production'] = C('APNS_PRODUCTION');
            if($param['fans_id']!=$param['user_id']){
                Hook::exec('Addons\\JPush\\JPushAddon', 'push_question_answer', $param);
            }

            //显示成功消息
            $extra['reply_id'] = $result;

            $uid = $this->getUid();
            if($this->checkUserDoReplyCache($uid)){
                if(increaseScore($uid, 3)){
                    $extraData['scoreAdd'] = "3";
                    $extraData['scoreTotal'] = getScoreCount($uid);
                    $extra['score'] = $extraData;
                    insertScoreRecord($uid, 3, '用户回复帖子');
                }
            }
            $this->apiSuccess('回复成功。' . getScoreTip($before, $after) . getToxMoneyTip($tox_money_before, $tox_money_after), null, $extra);
        } else {
            $this->apiError(-101,'请10秒之后再回复');
        }
    }

    /**
     * 用户楼中楼回复
     * @param int $to_f_reply_id
     * @param int $to_f_lzl_id
     * @param string $content
     * @param null $pos
     * @param null $pictures
     * @param null $sound
     */
    public function doSendLZLReply($to_f_reply_id = 0, $to_f_lzl_id = 0, $content = ' ', $pos = null, $pictures = null, $sound = null)
    {
        //确认用户已经登录
        $this->requireLogin();

        if($content == ' ' && $pictures == null && $sound == null)
            $this->apiError(-102,'缺少内容，图片、声音、文字至少其一!');

        $p = 1;
        $post_id = 0;
        $to_uid = 0;

        if($to_f_reply_id == 0 && $to_f_lzl_id == 0)
            $this->apiError(-102,'to_f_reply_id和to_f_lzl_id至少传入1个!');

        if($to_f_reply_id != 0){
            $postReply = D('Forum/ForumPostReply')->field('post_id,uid')->find(intval($to_f_reply_id));
            if(!$postReply)
                $this->apiError(-102,'所传入to_f_reply_id参数错误!');

            $post_id = $postReply['post_id'];
            $to_uid = $postReply['uid'];

            $this->requirePostExists($post_id);
        }

        if($to_f_lzl_id != 0){
            $lzl_reply = D('Forum/ForumLzlReply')->field('post_id,to_f_reply_id,uid')->find(intval($to_f_lzl_id));
            if(!$lzl_reply)
                $this->apiError(-102,'所传入to_f_lzl_id参数错误!');
            $post_id = $lzl_reply['post_id'];
            $to_uid = $lzl_reply['uid'];
            $to_f_reply_id = $lzl_reply['to_f_reply_id'];

            $this->requirePostExists($post_id);
            $this->requirePostReplyExists($to_f_reply_id);
        }

        if($content != ' ')
            $content = op_t($content);
        //根据id获取图片
        $pictures = $this->fetchImage($pictures);

        foreach($pictures as $pic) {
            $content = $content.$pic;
        }

        //写入数据库
        $model = D('Forum/ForumLzlReply');
        $before=getMyScore();
        $tox_money_before=getMyToxMoney();

        $result = $model->addLZLReply($post_id, $to_f_reply_id, $to_f_lzl_id, $to_uid, $content,$p);
        if (!$result['id']) {
            $this->apiError($model->getError(),'追问失败！');
        }
        if($pos != null) {
            $map_pos['post_id'] = $result['id'];
            $map_pos['type'] = 2;
            $map_pos['pos'] = $pos;
            $this->setForumPos($map_pos);
        }
        if($sound != null) {
            $Sound = D('ForumSound');
            $data = array('fid' => $result['id'],'ftype' => 2);
            $data = $Sound->create($data);
            if (!$data) {
                $this->apiError(0, $Sound->getError());
            }
            $Sound->where(array('id' => $sound))->save($data);
        }
        $after=getMyScore();
        $tox_money_after=getMyToxMoney();

        // 发送楼中楼回复的推送通知
        $model = D('Home/Member');
        $nickname = $model->getNickName($this->getUid());
        if($content != ' '){
            $content_length = mb_strlen($content);
            if($content_length>30){
                $tail_content = mb_substr($content, 0, 30, 'utf-8');
                $tail_content = $tail_content . '...';
            } else {
                $tail_content = $content;
            }
        } elseif($pictures!=null){
            $tail_content = "[图片]";
        } else {
            $tail_content = "[语音]";
        }
        $param['alert_info'] = $nickname . '回复了你:' . $tail_content;
        $param['question_id'] = $post_id;
        $param['fans_id'] = $this->getUid();
        $param['lzl_id'] = $result['id'];
        $param['reply_id'] = $to_f_reply_id;

        $param['user_id'] = $to_uid;
        $map = array('uid'=>$param['user_id']);
        $_user = $model->where($map)->find();
        $param['reg_id'] = $_user['reg_id'];
        $param['production'] = C('APNS_PRODUCTION');
        if($param['fans_id']!=$param['user_id']){
            if($result['hide']==0){
                Hook::exec('Addons\\JPush\\JPushAddon', 'push_floor_reply', $param);
            }
        }

        //显示成功
        $this->apiSuccess('追问成功。' . getScoreTip($before, $after) . getToxMoneyTip($tox_money_before, $tox_money_after), null, array('lzl_id' => $result['id']));
    }

    //点赞
    public function doSupport($type, $id)
    {
        $this->requireLogin();

        if($type == 'post') {
            $this->requirePostExists($id);
                $post_info = D('ForumPost')->where(array('id' => intval($id), 'status' => 1))->field('uid, content')->find();
                $message_uid = $post_info['uid'];
                $post_content = $post_info['content'];
        } else if($type == 'reply') {
            $message_uid = D('ForumPostReply')->where(array('id' => intval($id), 'status' => 1))->getField('uid');
        }
        $support['appname'] = 'Forum';
        $support['table'] = $type;
        $support['row'] = $id;
        $support['uid'] = is_login();

        if (D('Support')->where($support)->count()) {
            $this->apiError(-100,'您已经赞过，不能再赞了!');
        } else {
            $support['create_time'] = time();
            if (D('Support')->where($support)->add($support)) {
                $this->clearCache($support);
                $user = query_user(array('username'));

                if($type == 'reply') {
                    M('ForumPostReply')->where(array('id' => intval($id)))->setInc('support_count');
                }
                // 发送推送通知
                if($type == 'post'){
                    $source_id = $id;
                    $map = array('uid' => $message_uid);
                    $_user = D('Home/Member')->where($map)->find();
                    $param['reg_id'] = $_user['reg_id'];
                    $origin_length = strlen(trim($post_content));
                    $text_content = trim(strip_tags($post_content));
                    $new_length = strlen($text_content);
		            if($text_content!=''&&$new_length!=0){
                        $content_length = mb_strlen($text_content,'utf-8');
                        if($content_length>58){
                            $tail_content = mb_substr($text_content, 0, 50, 'utf-8');
                            $tail_content = $tail_content . '...';
                        } else {
                            $tail_content = $text_content;
                        }
                    } else if($text_content =='' && $origin_length>$new_length) {
                        $tail_content = "[图片]";
                    } else {
                        $tail_content = "[语音]";
                    }
                    $map = array('uid' => $this->getUid());
                    $_user = D('Home/Member')->where($map)->find();
                    $alert_info = $_user['nickname'] . '赞了你的提问:' . $tail_content;
                    $param['alert_info'] = $alert_info;
                    $param['question_id'] = $id;
                    $param['fans_id'] = $support['uid'];
                    $param['user_id'] = $message_uid;
                    $param['production'] = C('APNS_PRODUCTION');
                    if($param['fans_id']!=$param['user_id']){
                        Hook::exec('Addons\\JPush\\JPushAddon', 'push_question_like', $param);
                    }
                }

                D('Message')->sendMessage($message_uid, $user['username'] . '给您点了个赞。', $title =$user['username'] . '赞了您。', '', is_login(), 2, null, 'support_post', $source_id);
                if(increaseScore($message_uid, 1)){
                    $extraData['scoreAdd'] = "1";
                    $extraData['scoreTotal'] = getScoreCount($message_uid);
                    $extra['score'] = $extraData;
                    insertScoreRecord($message_uid, 1, '用户被点赞');
                }
                $this->apiSuccess('感谢您的支持', null, $extra);
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }

        }
    }

    //取消点赞
    public function unDoSupport($type, $id)
    {
        $this->requireLogin();

        if($type == 'post') {
            $this->requirePostExists($id);
            $message_uid = D('ForumPost')->where(array('id' => intval($id), 'status' => 1))->getField('uid');
        } else if($type == 'reply') {
            $message_uid = D('ForumPostReply')->where(array('id' => intval($id), 'status' => 1))->getField('uid');
        }
        $support['appname'] = 'Forum';
        $support['table'] = $type;
        $support['row'] = $id;
        $support['uid'] = is_login();

        if (D('Support')->where($support)->count()) {
            if (D('Support')->where($support)->delete()) {

                if($type == 'reply'){
                    M('ForumPostReply')->where(array('id' => intval($id)))->setDec('support_count');
                }

                $this->clearCache($support);
                $user = query_user(array('username'));
                //取消点赞不需要消息
                D('Message')->sendMessage($message_uid, $user['username'] . '取消了给您的赞。', $title =$user['username'] . '取消了赞。', '', is_login(), 2, null, 'unsupport_post');
                $this->apiSuccess('取消支持成功！');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        } else {
            $this->apiError(-102,'您还没有赞过，不能取消!');
        }
    }

    /**
     * 获取论坛置顶帖
     * @param string $version
     * @param int $community
     */
    public function forumTopPost($version='1.0', $community=1){
        if((float)$version>=2.6){
            $model = M('ForumTopPost');
            $toppostlist = $model->field('id, tag, title, type, post_id, url')
                ->where('status=1 and community='.$community)
                ->order('create_time desc')->select();
            foreach($toppostlist as &$toppost){
                $toppost_type = $toppost['type'];
                // 置顶类型  1 新闻列表  2 内部web帖子  3 原生帖子  4 外部url
                switch($toppost_type){
                    case 1:
                        $toppost['url'] = C('HOST_NAME_PREFIX')."app.php/forum/hisihi_news/community/".$community;
                        $toppost['show_type'] = "web";
                        unset($toppost['type']);
                        break;
                    case 2:
                        //$toppost['url'] = C('HOST_NAME_PREFIX').'app.php/forum/topPostDetailv2/post_id/'.$toppost['post_id'];
                        $toppost['show_type'] = "web";
                        break;
                    case 3:
                        $toppost['show_type'] = 'origin';
                        /*$configCount = M('CompanyConfig')->field('value')->where('status=1 and type=11')->find();
                        if($configCount){
                            $configCount['value'] = $configCount['value'] + $this->getAutoIncreseCount();
                            $toppost['title'] = "嘿设汇已经解决".$configCount['value']."个问题";
                        } else {
                            $fakeCount = 330212 + $this->getAutoIncreseCount();
                            $toppost['title'] = "嘿设汇已经解决". $fakeCount ."个问题";
                        }*/
                        break;
                    case 4:
                        $toppost['show_type'] = "web";
                        break;
                }
            }
            $extra['data'] = $toppostlist;
            $this->apiSuccess('获取论坛置顶帖成功', null, $extra);
        }
        if((float)$version>=2.5){
            // 获取第一栏
            $first_post['id'] = "001";
            $first_post['title'] = "嘿设汇新闻";
            $first_post['type'] = "置顶";
            $first_post['post_type'] = 1;
            $first_post['is_out_link'] = 0;
            $first_post['link_url'] = "";
            $first_post['is_inner'] = 0;
            $first_post['url'] = C('HOST_NAME_PREFIX')."app.php/forum/hisihi_news/community/".$community;
            $first_post['show_type'] = "web";
            // 获取第二栏
            $second_post = M('ForumPost')->where('forum_id=0 and is_top=1 and status=1
                                            and is_inner=2 and community='.$community)
                ->order('create_time desc')->find();
            $second_post['show_type'] = 'web';
            // 获取第三栏
            /*$third_post = M('ForumPost')->where('forum_id=0 and is_top=1 and status=1
                                            and is_inner=3 and community='.$community)
                ->order('create_time desc')->find();*/
            $third_post = M('ForumPost')->where('id=67282 and community='.$community)
                ->order('create_time desc')->find();
            $third_post['show_type'] = 'origin';
            if($community==1){
                $configCount = M('CompanyConfig')->field('value')->where('status=1 and type=11')->find();
                if($configCount){
                    $configCount['value'] = $configCount['value'] + $this->getAutoIncreseCount();
                    $third_post['title'] = "嘿设汇已经解决".$configCount['value']."个问题";
                } else {
                    $fakeCount = 330212 + $this->getAutoIncreseCount();
                    $third_post['title'] = "嘿设汇已经解决". $fakeCount ."个问题";
                }
            }
            //$data_list = array($first_post, $second_post, $third_post);
            $data_list = array($first_post);
            if($second_post['id']){
                $data_list[] = $second_post;
            }
            if($third_post['id']){
                $data_list[] = $third_post;
            }
            foreach($data_list as &$value){
                if($value['id']!='001'){
                    $value['url'] = C('HOST_NAME_PREFIX').'app.php/forum/topPostDetailv2/post_id/'.$value['id'];
                }
                unset($value['uid']);
                unset($value['forum_id']);
                unset($value['content_md5']);
                unset($value['parse']);
                unset($value['create_time']);
                unset($value['update_time']);
                unset($value['status']);
                unset($value['last_reply_time']);
                unset($value['view_count']);
                unset($value['reply_count']);
                unset($value['is_top']);
                unset($value['content']);
                unset($value['community']);
            }
            $extra['data'] = $data_list;
            $this->apiSuccess('获取论坛置顶帖成功', null, $extra);
        }
        if((float)$version>=2.2){
            $first_post['id'] = "001";
            $first_post['title'] = "嘿设汇新闻";
            $first_post['type'] = "置顶";
            $first_post['post_type'] = 1;
            $first_post['is_out_link'] = 0;
            $first_post['link_url'] = "";
            $first_post['is_inner'] = 0;
            $first_post['url'] = C('HOST_NAME_PREFIX')."app.php/forum/hisihi_news";
            $list = M('ForumPost')->where('forum_id=0 and is_top=1 and status=1
                                            and is_inner=0 and community='.$community)
                ->order('create_time desc')->page(1, 2)->select();
            array_unshift($list, $first_post);

            foreach($list as &$value){
                if($value['uid']!=0){
                    $configCount = M('CompanyConfig')->field('value')->where('status=1 and type=11')->find();
                    if($configCount){
                        $configCount['value'] = $configCount['value'] + $this->getAutoIncreseCount();
                        $value['title'] = "嘿设汇已经解决".$configCount['value']."个问题";
                    } else {
                        $fakeCount = 330212 + $this->getAutoIncreseCount();
                        $value['title'] = "嘿设汇已经解决". $fakeCount ."个问题";
                    }
                }
            }
        } else if ((float)$version>=2.1){
            $list = M('ForumPost')->where('forum_id=0 and is_top=1 and status=1 and is_inner=1')
                ->order('create_time desc')->page(1, 3)->select();
        } else {  //  老版本不展示包含外链的置顶
            $list = M('ForumPost')->where('forum_id=0 and is_top=1 and is_out_link=0 and status=1')
                ->order('create_time desc')->page(1, 3)->select();
        }
        foreach($list as &$value){
            if((float)$version>=2.2||$value['uid']==0){
                if($value['id']!='001'){
                    $value['url'] = C('HOST_NAME_PREFIX').'app.php/forum/topPostDetailv2/post_id/'.$value['id'];
                }
                $value['show_type'] = 'web';
            }
            if($value['id']!='001'&&$value['uid']!=0){
                $value['url'] = C('HOST_NAME_PREFIX').'app.php/forum/topPostDetail/post_id/'.$value['id'];
                if((float)$version>=2.2){
                    $value['show_type'] = 'origin';
                }
            }
            unset($value['uid']);
            unset($value['forum_id']);
            unset($value['content_md5']);
            unset($value['parse']);
            unset($value['create_time']);
            unset($value['update_time']);
            unset($value['status']);
            unset($value['last_reply_time']);
            unset($value['view_count']);
            unset($value['reply_count']);
            unset($value['is_top']);
            unset($value['content']);
        }
        $extra['data'] = $list;
        $this->apiSuccess('获取论坛置顶帖成功', null, $extra);
    }

    /**
     * 获取推送的置顶帖的信息
     * @param $id
     */
    public function pushTopPostInfo($id){
        $map['status'] = array('in',array(1,3));
        $map['is_top'] = 1;
        $data = M('ForumPost')->where($map)->find($id);
        if(empty($data)){
            $this->apiError(-1, '传入置顶帖ID无效');
        }
        $result['id'] = $id;
        $result['url'] = C('HOST_NAME_PREFIX').'app.php/forum/topPostDetail/post_id/'.$data['id'];
        $result['title'] = $data['title'];
        $result['type'] = $data['type'];
        $this->apiSuccess('获取推送置顶帖信息成功', null, $result);
    }

    /**
     * 嘿设汇新闻内页帖子列表
     * @param int $page
     * @param int $count
     * @param int $removeId
     * @param int $community
     */
    public function newsList($page=1, $count=10, $removeId=0, $community=1){
        $list = M('ForumPost')->where('forum_id=0 and is_top=1 and status=1 and is_inner=1 and id!='.$removeId.' and community='.$community)
            ->order('create_time desc')->page($page, $count)->select();
        $totalCount = M('ForumPost')->where('forum_id=0 and is_top=1 and status=1 and is_inner=1 and community='.$community)->count();
        foreach($list as &$value){
            $value['url'] = C('HOST_NAME_PREFIX').'app.php/forum/toppostdetailv2/post_id/'.$value['id'];
            $value['pic_url'] = $this->fetchImageFromOSS($value['cover_id']);
            unset($value['uid']);
            unset($value['forum_id']);
            unset($value['content_md5']);
            unset($value['parse']);
            unset($value['update_time']);
            unset($value['status']);
            unset($value['last_reply_time']);
            unset($value['reply_count']);
            unset($value['is_top']);
            unset($value['content']);
            unset($value['type']);
            unset($value['post_type']);
            unset($value['is_inner']);
            unset($value['cover_id']);
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $list;
        $this->apiSuccess('获取新闻列表成功', null, $extra);
    }

    /**
     * 跳转嘿设汇新闻列表内页
     * @param int $community
     */
    public function hisihi_news($community=1){
        $this->assign('community', $community);
        $this->display('hisihi_news');
    }

    /**
     * 跳转到小嘿专栏列表网页
     */
    public function xiaoheicolumn(){
        C('SHOW_PAGE_TRACE', false);
        $this->display('xiaoheicolumn');
    }

    /**
     * 小嘿专栏列表
     * @param int $page
     * @param int $count
     */
    public function xiaoheiList($page=1, $count=10){
        $list = M('ForumPost')->where('forum_id=0 and status=1 and is_inner=4')
            ->order('create_time desc')->page($page, $count)->select();
        $totalCount = M('ForumPost')->where('forum_id=0 and status=1 and is_inner=4')->count();
        foreach($list as &$value){
            $value['url'] = C('HOST_NAME_PREFIX').'app.php/forum/toppostdetailv2/post_id/'.$value['id'];
            $value['pic_url'] = $this->fetchImageFromOSS($value['cover_id']);
            unset($value['uid']);
            unset($value['forum_id']);
            unset($value['content_md5']);
            unset($value['parse']);
            unset($value['update_time']);
            unset($value['status']);
            unset($value['last_reply_time']);
            unset($value['reply_count']);
            unset($value['is_top']);
            unset($value['content']);
            unset($value['type']);
            unset($value['post_type']);
            unset($value['is_inner']);
            unset($value['cover_id']);
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $list;
        $this->apiSuccess('获取小嘿专栏列表成功', null, $extra);
    }


    /**
     * 从OSS获取图片地址
     * @param $pic_id
     * @return null|string
     */
    private function fetchImageFromOSS($pic_id){
        if($pic_id == null)
            return null;
        $model = M();
        $pic_info = $model->query("select path from hisihi_picture where id=".$pic_id);
        if($pic_info){
            $path = $pic_info[0]['path'];
            $objKey = substr($path, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $objKey;
            //if(file_exists('.'.$path)){
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if($isExist){
                $picUrl = "http://hisihi-other.oss-cn-qingdao.aliyuncs.com/".$objKey;
            }
            //}
        }
        return $picUrl;
    }

    /**
     * 获取自动增长数
     */
    public function getAutoIncreseCount(){
        $Date_1 = date("Y-m-d");
        $Date_2 = "2015-12-01";
        $d1 = strtotime($Date_1);
        $d2 = strtotime($Date_2);
        $days = round(($d1-$d2)/3600/24);
        return $days * 437;
    }

    /**
     * 置顶帖详情
     * @param $post_id
     * @param int $page
     * @param int $count
     */
    public function toppostdetailv2($post_id, $page=1, $count=10)
    {
        //$this->requireLogin();

        $id = intval($post_id);
        $page = intval($page);
        $count = intval($count);

        $this->requirePostExists($id);

        //判断是否需要显示1楼
        if ($page == 1) {
            $showMainPost = true;
            $post = $this->getTopPostInfo($id);
            //增加浏览次数
            D('ForumPost')->where(array('id' => $id))->setInc('view_count');
        } else {
            $showMainPost = false;
        }

        //读取回复列表
        //$map = array('post_id' => $id, 'status' => 1);
        $map['post_id'] = $id;
        $map['status'] = array('in','1,3');
        $replyList = D('Forum/ForumPostReply')->getReplyList($map, 'create_time desc', $page, $count);

        $replyTotalCount = D('Forum/ForumPostReply')->where($map)->count();

        foreach ($replyList as &$reply) {
            //dump($reply);
            //$reply['content'] = op_h($reply['content'], 'html');
            $reply['reply_id'] = $reply['id'];
            unset($reply['id']);
            $map_pos['type'] = 1;
            $map_pos['post_id'] = $reply['reply_id'];
            $pos = $this->getForumPos($map_pos);
            $reply['pos'] = $pos['pos'];

            $reply['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['uid']);

            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$reply['uid']))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$reply['uid'],'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $reply['userInfo']['relationship'] = $isfollowing | $isfans;

            unset($reply['uid']);

            unset($pos);
            unset($map_pos);

            $map_support['table'] = 'reply';
            $map_support['row'] = $reply['reply_id'];

            $supportCount = $this->getSupportCountCache($map_support);

            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();

            $reply['supportCount'] = $supportCount;
            $reply['isSupportd'] = $supported;

            //解析并成立图片数据
            $reply['img'] = $this->match_img($reply['content']);

            $reply['sound'] = $this->fetchSound($reply['reply_id'],1);

            $reply['content'] = op_t($reply['content']);
            unset($reply['user']);
            $lzlList = D('Forum/ForumLzlReply')->getLZLReplyList($reply['reply_id'],'ctime asc',$page, $limit,false);
            foreach ($lzlList as &$lzl) {
                //unset($lzl['userInfo']['icons_html']);
                //unset($lzl['userInfo']['uid']);
                //unset($lzl['userInfo']['space_url']);
                $lzl['lzl_id'] = $lzl['id'];
                unset($lzl['id']);
                $lzl['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $lzl['uid']);
                unset($lzl['uid']);
                //unset($reply['uid']);

                $map_pos['type'] = 2;
                $map_pos['post_id'] = $lzl['lzl_id'];
                $pos = $this->getForumPos($map_pos);
                $lzl['pos'] = $pos['pos'];

                unset($pos);
                unset($map_pos);

                $lzl['img'] = $this->match_img($lzl['content']);
                $lzl['content'] = op_t($lzl['content']);
                $lzl['sound'] = $this->fetchSound($lzl['lzl_id'],2);
            }
            $reply['lzlList'] = $lzlList;
        }
        unset($reply);
        //判断是否已经收藏
        $isBookmark = D('Forum/ForumBookmark')->exists(is_login(), $id);
        if(count($replyList)){
            $this->assign('isShowReply', true);
        } else {
            $this->assign('isShowReply', false);
        }
        $this->assign('replyTotalCount', $replyTotalCount);
        $this->assign('post', $post);
        $this->assign('post_img', $post['img']);
        $this->assign('replyList',$replyList);
        $this->setTitle('{$post.title|op_t} — 嘿设汇');
        $this->display('toppostdetailv2');
    }

    /**
     * 置顶帖详情
     * @param $post_id
     * @param int $page
     * @param int $count
     */
    public function topPostDetail($post_id, $page=1, $count=10)
    {
        //$this->requireLogin();

        $id = intval($post_id);
        $page = intval($page);
        $count = intval($count);

        $this->requirePostExists($id);

        //判断是否需要显示1楼
        if ($page == 1) {
            $showMainPost = true;
            $post = $this->getTopPostInfo($id);
            //增加浏览次数
            //D('ForumPost')->where(array('id' => $id))->setInc('view_count');
        } else {
            $showMainPost = false;
        }

        //读取回复列表
        //$map = array('post_id' => $id, 'status' => 1);
        $map['post_id'] = $id;
        $map['status'] = array('in','1,3');
        $replyList = D('Forum/ForumPostReply')->getReplyList($map, 'create_time desc', $page, $count);

        $replyTotalCount = D('Forum/ForumPostReply')->where($map)->count();

        foreach ($replyList as &$reply) {
            //dump($reply);
            //$reply['content'] = op_h($reply['content'], 'html');
            $reply['reply_id'] = $reply['id'];
            unset($reply['id']);
            $map_pos['type'] = 1;
            $map_pos['post_id'] = $reply['reply_id'];
            $pos = $this->getForumPos($map_pos);
            $reply['pos'] = $pos['pos'];

            $reply['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['uid']);

            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$reply['uid']))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$reply['uid'],'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $reply['userInfo']['relationship'] = $isfollowing | $isfans;

            unset($reply['uid']);

            unset($pos);
            unset($map_pos);

            $map_support['table'] = 'reply';
            $map_support['row'] = $reply['reply_id'];

            $supportCount = $this->getSupportCountCache($map_support);

            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();

            $reply['supportCount'] = $supportCount;
            $reply['isSupportd'] = $supported;

            //解析并成立图片数据
            $reply['img'] = $this->match_img($reply['content']);

            $reply['sound'] = $this->fetchSound($reply['reply_id'],1);

            $reply['content'] = op_t($reply['content']);
            unset($reply['user']);
            $lzlList = D('Forum/ForumLzlReply')->getLZLReplyList($reply['reply_id'],'ctime asc',$page, $limit,false);
            foreach ($lzlList as &$lzl) {
                //unset($lzl['userInfo']['icons_html']);
                //unset($lzl['userInfo']['uid']);
                //unset($lzl['userInfo']['space_url']);
                $lzl['lzl_id'] = $lzl['id'];
                unset($lzl['id']);
                $lzl['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $lzl['uid']);
                unset($lzl['uid']);
                //unset($reply['uid']);

                $map_pos['type'] = 2;
                $map_pos['post_id'] = $lzl['lzl_id'];
                $pos = $this->getForumPos($map_pos);
                $lzl['pos'] = $pos['pos'];

                unset($pos);
                unset($map_pos);

                $lzl['img'] = $this->match_img($lzl['content']);
                $lzl['content'] = op_t($lzl['content']);
                $lzl['sound'] = $this->fetchSound($lzl['lzl_id'],2);
            }
            $reply['lzlList'] = $lzlList;
        }
        unset($reply);
        //判断是否已经收藏
        $isBookmark = D('Forum/ForumBookmark')->exists(is_login(), $id);
        if(count($replyList)){
            $this->assign('isShowReply', true);
        } else {
            $this->assign('isShowReply', false);
        }
        $this->assign('replyTotalCount', $replyTotalCount);
        $this->assign('post', $post);
        $this->assign('post_img', $post['img']);
        $this->assign('replyList',$replyList);
        $this->setTitle('{$post.title|op_t} — 嘿设汇');
        $this->display('toppostdetail');
    }

    /**
     * 客户端置顶帖ajax获取评论列表
     * @param int $post_id
     * @param $page
     * @param int $count
     */
    public function ajaxPostReplyList($post_id=0, $page, $count=10){
        if($post_id==0){
            $this->apiError(-1, '传入帖子ID不能为空');
        }
        $map['post_id'] = $post_id;
        $map['status'] = array('in','1,3');
        $replyList = D('Forum/ForumPostReply')->getReplyList($map, 'status desc,create_time', $page, $count);

        foreach ($replyList as &$reply) {
            $reply['reply_id'] = $reply['id'];
            unset($reply['id']);
            $map_pos['type'] = 1;
            $map_pos['post_id'] = $reply['reply_id'];
            $pos = $this->getForumPos($map_pos);
            $reply['pos'] = $pos['pos'];

            $reply['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['uid']);

            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$reply['uid']))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$reply['uid'],'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $reply['userInfo']['relationship'] = $isfollowing | $isfans;

            unset($reply['uid']);

            unset($pos);
            unset($map_pos);

            $map_support['table'] = 'reply';
            $map_support['row'] = $reply['reply_id'];

            $supportCount = $this->getSupportCountCache($map_support);

            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();

            $reply['supportCount'] = $supportCount;
            $reply['isSupportd'] = $supported;

            //解析并成立图片数据
            $reply['img'] = $this->match_img($reply['content']);

            $reply['sound'] = $this->fetchSound($reply['reply_id'],1);

            $reply['content'] = op_t($reply['content']);
            unset($reply['user']);
            $lzlList = D('Forum/ForumLzlReply')->getLZLReplyList($reply['reply_id'],'ctime asc',$page,$limit,false);
            foreach ($lzlList as &$lzl) {
                $lzl['lzl_id'] = $lzl['id'];
                unset($lzl['id']);
                $lzl['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $lzl['uid']);
                unset($lzl['uid']);

                $map_pos['type'] = 2;
                $map_pos['post_id'] = $lzl['lzl_id'];
                $pos = $this->getForumPos($map_pos);
                $lzl['pos'] = $pos['pos'];

                unset($pos);
                unset($map_pos);

                $lzl['img'] = $this->match_img($lzl['content']);
                $lzl['content'] = op_t($lzl['content']);
                $lzl['sound'] = $this->fetchSound($lzl['lzl_id'],2);
            }
            $reply['lzlList'] = $lzlList;
        }
        unset($reply);
        if(count($replyList)){
            $this->assign('isShowReply', true);
        } else {
            $this->assign('isShowReply', false);
        }
        $extra['data'] = $replyList;
        $this->apiSuccess('获取帖子回复成功', null, $extra);
    }

    /**
     * 获取公司热门话题
     * @param $id
     * @param int $page
     * @param int $count
     */
    public function companyHotTopics($id, $page = 1, $count = 10, $version=null){
        $id = intval($id);
        if(empty($id)){
            $this->error('传入的id参数不允许为空');
        }
        $page = intval($page);
        $count = intval($count);
        $order = 'create_time desc';

        //读取帖子列表
        $atData['status'] = 1;
        $atData['at_uid'] = $id;
        $atData['type'] = 2;
        $forumAtModel = D('Forum/ForumAt');
        $totalCount = $forumAtModel->where($atData)->count();
        $forumAtList = $forumAtModel->where($atData)->order($order)->page($page, $count)->select();
        $list = array();
        if($forumAtList){
            foreach($forumAtList as $forumAt){
                $postId = $forumAt['post_id'];
                $map['id'] = $postId;
                $map['post_type'] = 2;
                $postInfo = D('ForumPost')->where($map)->find();
                if($postInfo){
                    $list[] =  $postInfo;
                }
            }
        }
        $list = $this->formatList($list, $version);
        $this->apiSuccess("获取公司热门话题列表成功", null, array( 'total_count' => $totalCount, 'forumList' => $list));
    }

    /**
     * 获取社区的圈子接口
     */
    public function getForumCircle(){
        $model = M('ForumConfig');
        $list = $model->field('id, name')->where('type=1 and status=1')->select();
        $this->apiSuccess("获取社区圈子成功", null, array('data'=>$list));
    }

    /**
     * 获取社区Banner
     * @param int $show_pos
     * @param int $page
     * @param int $count
     */
    public function getBannerList($show_pos=1, $page=1, $count=3){
        $show_pos = (int)$show_pos;
        if($show_pos==1 || $show_pos==2 ||$show_pos==4){
            $show_pos = $show_pos * 10;
        }
        $model = M('InformationFlowBanner');
        $totalCount = $model->where('status=1 and show_pos='.$show_pos)->count();
        $banner_list = $model->field('id, pic_url, url, jump_type, organization_id')->where('status=1 and show_pos='.$show_pos)
            ->select();
        foreach($banner_list as &$banner){
            $organization_id = $banner['organization_id'];
            unset($banner['organization_id']);
            switch($banner['jump_type']){
                case 1: // 网址
                    break;
                case 2: // 帖子
                    $post_id = $banner['url'];
                    $banner['url'] = 'hisihi://post/detailinfo?id='.$post_id;
                    break;
                case 3: // 视频
                    $course_id = $banner['url'];
                    $banner['url'] = 'hisihi://course/detailinfo?id='.$course_id;
                    break;
                case 4: // 机构主页
                    $org_id = $banner['url'];
                    $banner['url'] = 'hisihi://organization/detailinfo?id='.$org_id;
                    break;
                case 5: // 大学主页
                    $u_id = $banner['url'];
                    $banner['url'] = 'hisihi://university/detailinfo?id='.$u_id;
                    break;
                case 6: // 活动详情页
                    $promotion_id = $banner['url'];
                    $banner['url'] = 'hisihi://promotion/detailinfo?id='.$promotion_id.'&oid='.$organization_id;
                    break;
            }
        }
        $this->apiSuccess('获取社区banner成功', null, array('data'=>$banner_list, 'totalCount'=>$totalCount));
    }

    /**
     * 定时执行的机器人
     */
    public function startAutoReplyRobot(){
        $cache = S('auto_reply');
        if($cache){
            $list = explode(',', $cache);
            foreach($list as $post_id){
                $reply_count = rand(1,5);
                while($reply_count){
                    $post_info = M('ForumPost')->where('id='.$post_id)->find();
                    if($post_info){
                        $content_list = M('Autoreply')->where(array('forum_id' => $post_info['forum_id'], 'status' => 1))->select();
                        $count = count($content_list);
                        if($count!=0){
                            $index = rand(1, $count);
                            $uid = rand(70, 90);
                            $model = D('Forum/ForumPostReply');
                            $model->addAutoReply($uid, $post_id, $content_list[$index]['content']);
                        }
                        $reply_count = $reply_count - 1;
                    }
                }
            }
            $cache = S('auto_reply');
            $new_list = explode(',', $cache);
            $fresh_list = array_diff($new_list, $list);
            $fresh_str = implode(',', $fresh_list);
            if(empty($fresh_str)){
                S('auto_reply', null);
            } else {
                S('auto_reply', $fresh_str);
            }
            $this->apiSuccess("auto reply success -- post_ids: ".json_encode($list));
        }
    }

    /**
     * 清除自动回复缓存
     */
    public function cleanAutoReplyCache(){
        S('auto_reply', null);
    }

    /**
     * @param $map_support
     * @return mixed
     * @auth RFly
     */
    private function getSupportCountCache($map_support)
    {
        $cache_key = "support_count_" . implode('_', $map_support);
        $count = S($cache_key);
        if (empty($count)) {
            $count = D('Support')->where($map_support)->count();
            S($cache_key, $count);
            return $count;
        }
        return $count;
    }

    /**
     * @param $map_pos
     * @return bool|int
     */
    private function setForumPos($map_pos)
    {
        //如果存在，就不添加了
        $result = D('ForumPos')->where($map_pos)->find();
        if($result ? true : false) {
            return 0;
        }

        //如果不存在，就添加到数据库
        $map_pos = D('ForumPos')->create($map_pos);
        if(!$map_pos) return false;

        return D('ForumPos')->add($map_pos);
    }

    /**
     * @param $map_pos
     * @return mixed
     */
    private function getForumPos($map_pos)
    {
        $pos = M('ForumPos')->cache(true)->where($map_pos)->field('pos')->find();
        return $pos;
    }

    /**
     * @param $support
     * @auth RFly
     */
    private function clearCache($support)
    {
        unset($support['uid']);
        unset($support['create_time']);
        $cache_key = "support_count_" . implode('_', $support);
        S($cache_key, null);
    }

    private function limitPictureCount($content)
    {
        //默认最多显示10张图片
        $maxImageCount = modC('LIMIT_IMAGE', 10);
        //正则表达式配置
        $beginMark = 'BEGIN0000hfuidafoidsjfiadosj';
        $endMark = 'END0000fjidoajfdsiofjdiofjasid';
        $imageRegex = '/<img(.*?)\\>/i';
        $reverseRegex = "/{$beginMark}(.*?){$endMark}/i";

        //如果图片数量不够多，那就不用额外处理了。
        $imageCount = preg_match_all($imageRegex, $content);
        if ($imageCount <= $maxImageCount) {
            return $content;
        }

        //清除伪造图片
        $content = preg_replace($reverseRegex, "<img$1>", $content);

        //临时替换图片来保留前$maxImageCount张图片
        $content = preg_replace($imageRegex, "{$beginMark}$1{$endMark}", $content, $maxImageCount);

        //替换多余的图片
        $content = preg_replace($imageRegex, "[图片]", $content);

        //将替换的东西替换回来
        $content = preg_replace($reverseRegex, "<img$1>", $content);

        //返回结果
        return $content;
    }

    /**过滤输出，临时解决方案
     * @param $content
     * @return mixed|string
     * @auth RFly
     */
    private function filterPostContent($content)
    {
        $content = op_h($content);
        $content = $this->limitPictureCount($content);
        $content = op_h($content);
        return $content;
    }

    /**
     * @param $reply_id
     * @return mixed
     * @auth RFly
     */
    private function checkRelyPermission($reply_id)
    {
        $reply = D('ForumPostReply')->find(intval($reply_id));
        $has_permission = $reply['uid'] == is_login() || is_administrator();
        return $has_permission;
    }

    private function assignAllowPublish()
    {
        $forum_id = $this->get('forum_id');
        $allow_publish = $this->isForumAllowPublish($forum_id);
        $this->assign('allow_publish', $allow_publish);
    }

    private function isLogin()
    {
        return is_login() ? true : false;
    }

    private function requireForumAllowPublish($forum_id)
    {
        $this->requireForumExists($forum_id);
        $this->requireLogin();
        $this->requireForumAllowCurrentUserGroup($forum_id);
    }

    private function isForumAllowPublish($forum_id)
    {
        if (!$this->isLogin()) {
            return false;
        }
        if (!$this->isForumExists($forum_id)) {
            return false;
        }
        if (!$this->isForumAllowCurrentUserGroup($forum_id)) {
            return false;
        }
        return true;
    }

    private function requireAllowEditPost($post_id)
    {
        $this->requirePostExists($post_id);
        $this->requireLogin();

        if (is_administrator()) {
            return true;
        }
        //确认帖子时自己的
        $post = D('ForumPost')->where(array('id' => $post_id, 'status' => 1))->find();
        if ($post['uid'] != is_login()) {
            $this->error('没有权限编辑帖子');
        }
    }

    private function requireForumAllowView($forum_id)
    {
        $this->requireForumExists($forum_id);
    }

    private function requireForumExists($forum_id)
    {
        if (!$this->isForumExists($forum_id)) {
            $this->error('论坛不存在');
        }
    }

    private function isForumExists($forum_id)
    {
        $forum_id = intval($forum_id);
        $forum = D('Forum')->where(array('id' => $forum_id, 'status' => 1));
        return $forum ? true : false;
    }

    private function requireAllowReply($post_id)
    {
        $post_id = intval($post_id);
        $this->requirePostExists($post_id);
        $this->requireLogin();
    }

    private function requirePostExists($post_id)
    {
        $post_id = intval($post_id);
        $post = D('ForumPost')->where(array('id' => $post_id, 'status' => 1))->find();
        if (!$post) {
            $this->apiError(404,'找不到该提问');
        }
    }

    private function requirePostReplyExists($reply_id)
    {
        $reply_id = intval($reply_id);
        //$reply = D('ForumPostReply')->where(array('id' => $reply_id, 'status' => 1))->find();
        $reply = D('ForumPostReply')->where('id='.$reply_id.' and status=1 or status=3')->find();
        if (!$reply) {
            $this->apiError(404,'找不到该回复');
        }
    }

    private function requireForumAllowCurrentUserGroup($forum_id)
    {
        $forum_id = intval($forum_id);
        if (!$this->isForumAllowCurrentUserGroup($forum_id)) {
            $this->error('该板块不允许发帖');
        }
    }

    private function isForumAllowCurrentUserGroup($forum_id)
    {
        $forum_id = intval($forum_id);
        //如果是超级管理员，直接允许
        if (is_login() == 1) {
            return true;
        }

        //如果帖子不属于任何板块，则允许发帖
        if (intval($forum_id) == 0) {
            return true;
        }

        //读取论坛的基本信息
        $forum = D('Forum')->where(array('id' => $forum_id))->find();
        $userGroups = explode(',', $forum['allow_user_group']);

        //读取用户所在的用户组
        $list = M('AuthGroupAccess')->where(array('uid' => is_login()))->select();
        foreach ($list as &$e) {
            $e = $e['group_id'];
        }


        //判断用户组是否有权限
        $list = array_intersect($list, $userGroups);
        return $list ? true : false;
    }

    private function fetchImage($pictures)
    {
        if($pictures == null)
            return null;
        $img_src = "<p><img src=\"img_src\" _src=\"img_src\" style=\"\"/></p>";
        $pictures = explode(',',$pictures);
        foreach ($pictures as $pic_id) {
            $pic_small = getThumbImageById($pic_id, 280, 160);
            $pic = M('Picture')->where(array('status' => 1))->field('path')->getById($pic_id);

            if(!is_bool(strpos( $pic['path'],'http://'))){
                $pic_src = $pic['path'];
            }else{
                //$pic_src =getRootUrl(). substr( $pic['path'],1);
                $pic_src =getRootUrl(). $pic['path'];
            }
            $arrPic[] = str_replace("img_src",$pic_src,$img_src);
        }
        return $arrPic;
    }

    private function fetchSound($fid,$ftype)
    {
        $sound = D('ForumSound')->where(array('fid' => $fid,'ftype' => $ftype))->find();
        if($sound) {
            $root = C('DOWNLOAD_UPLOAD.rootPath');
            $soundRes['url'] = $root.$sound['savepath'].$sound['savename'];
            $soundRes['duration'] = $sound['duration'];
            if(!is_file($soundRes['url'])){
                return null;
            }
            $soundRes['url'] = str_replace('./','',$soundRes['url']);
            if(strpos($soundRes['url'], "Download")) {
                $src = substr($soundRes['url'], 17);
                $soundRes['url'] = "http://".C('OSS_FORUM_SOUND').C('OSS_ENDPOINT').$src;
            }
        }
        return $soundRes;
    }

    private function match_img($content)
    {
        //解析并成立图片数据
        $tmpImgArr = array();
        preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/",  $content, $tmpImgArr); //匹配所有的图片
        $imgArr = $tmpImgArr[1];
        if(!empty($imgArr)){
            $ImgResult = array();
            $dm = "http://$_SERVER[HTTP_HOST]" . __ROOT__; //前缀图片多余截取
            $dmip = "http://$_SERVER[SERVER_ADDR]" . __ROOT__; //前缀图片多余截取
            foreach($imgArr as $v1){
                if(strstr($v1,$dm))
                    $v1 = mb_substr($v1, strlen($dm), strlen($v1) - strlen($dm));
                else if(strstr($v1,$dmip)){
                    $v1 = mb_substr($v1, strlen($dmip), strlen($v1) - strlen($dmip));
                }
                $pic_small = getThumbImage($v1, 280, 160);
                $size[] = $pic_small['width'];
                $size[] = $pic_small['height'];
                $img['src'] = ltrim($v1, '/');
                $img['thumb'] = $pic_small['src'];
                if(strpos($img['src'], "Picture")) {
                    $src = substr($img['src'], 16);
                    $img['src'] = "http://".C('OSS_FORUM_PIC').C('OSS_ENDPOINT').$src;
                    $oss_img_src = "http://".C('OSS_FORUM_PIC').C('IMG_OSS_ENDPOINT').$src.'@info';
                    $origin_img_info = getOssImgSizeInfo($oss_img_src);
                    $img_info = json_decode($origin_img_info);
                    $src_size = array();
                    $src_size[] = $img_info->width; // width
                    $src_size[] = $img_info->height; // height
                    $img['src_size'] = $src_size;
                }
                $img['size'] = $size;
                $ImgResult[] = $img;
                unset($size);

            }
        }
        return $ImgResult;
    }

    private function sendAtMessage($uids, $post_id, $content)
    {
        $my_username = query_user('nickname');
        foreach ($uids as $uid) {
            $message = '内容：' . $content;
            $title = $my_username . '向您提问';
            $url = '';
            $fromUid = get_uid();
            $messageType = 2;
            D('Message')->sendMessage($uid, $message, $title, $url, $fromUid, $messageType, null, 'ask_you', $post_id);
        }
    }

    /**
     * 获取作业源文件总数
     */
    public function getHiworksTotalCount(){
        $cateModel = M('Category');
        $count = $cateModel->where('pid=1 and status=1')->sum('fake_hiworks_count');
        return $count;
    }

    /**
     * 获取入住大学生总数;
     * http://115.28.72.197:8080/hisihi/app_base_data
     */
    public function getAllColleageStudentsCount(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://115.28.72.197:8080/hisihi/app_base_data');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output);
        $data = json_decode($output->data);
        return $data->installations;
    }

    /**
     * 获取一条社区对应圈子内的广告
     * @param int $width
     * @param int $height
     * @param int $community
     * @return array|bool
     */
    public function getOneForumAdv($width=640, $height=960, $community=0){
        $model = M();
        $now = time();
        $picKey = "advspic_".$width.'_'.$height;
        $result = $model->query("select ".$picKey." , title, link from hisihi_advs where ".
            "position=4 and community=".$community." and status=1 and ".$now." between create_time and end_time order by level desc");
        $total_count = M('Advs')->where('position=4 and community='.$community.' and status=1 and '.$now.' between create_time and end_time')->count();
        //$pos = rand(1, $total_count);
        if(self::$adv_index>$total_count){
            self::$adv_index = 1;
            $pos = 1;
        } else {
            $pos = self::$adv_index;
        }
        self::$adv_index++;
        if($result){
            $picID = $result[$pos-1][$picKey];
            $advLink = $result[$pos-1]['link'];
            $advTitle = $result[$pos-1]['title'];
            $pic_result = $model->query("select path from hisihi_picture where id=".$picID);
            if($pic_result){
                $path = $pic_result[0]['path'];
                $objKey = substr($path, 17);
                $picUrl = "http://advs-pic.oss-cn-qingdao.aliyuncs.com/".$objKey;
                $oss_img_src = "http://advs-pic".C('IMG_OSS_ENDPOINT').$objKey.'@info';
                $origin_img_info = getOssImgSizeInfo($oss_img_src);
                $img_info = json_decode($origin_img_info);
                $data['type'] = "advertisment";
                $data['pic'] = $picUrl;
                $data['content_url'] = $advLink;
                $data['title'] = $advTitle;
                //$origin_img_info = getimagesize($picUrl);
                $size[] = $img_info->width; // width
                $size[] = $img_info->height; // height
                $data['size'] = $size;
                return array($data);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 获取一条社区帖子详情页的广告
     * @param int $community
     * @param int $width
     * @param int $height
     * @return array|bool
     */
    public function getOneForumPostDetailAdv($community=0, $width=640, $height=960){
        $model = M();
        $now = time();
        $picKey = "advspic_".$width.'_'.$height;
        $result = $model->query("select ".$picKey." , title, link from hisihi_advs where ".
            "position=5 and community=".$community." and status=1 and ".$now." between create_time and end_time order by level desc");
        $total_count = M('Advs')->where('position=5 and community='.$community.' and status=1 and '.$now.' between create_time and end_time')->count();
        $pos = rand(1, $total_count);
        if($result){
            $picID = $result[$pos-1][$picKey];
            $advLink = $result[$pos-1]['link'];
            $advTitle = $result[$pos-1]['title'];
            $pic_result = $model->query("select path from hisihi_picture where id=".$picID);
            if($pic_result){
                $path = $pic_result[0]['path'];
                $objKey = substr($path, 17);
                $picUrl = "http://advs-pic.oss-cn-qingdao.aliyuncs.com/".$objKey;
                $oss_img_src = "http://advs-pic".C('IMG_OSS_ENDPOINT').$objKey.'@info';
                $origin_img_info = getOssImgSizeInfo($oss_img_src);
                $img_info = json_decode($origin_img_info);
                $data['type'] = "advertisment";
                $data['pic'] = $picUrl;
                $data['content_url'] = $advLink;
                $data['title'] = $advTitle;
                //$origin_img_info = getimagesize($picUrl);
                $size[] = $img_info->width; // width
                $size[] = $img_info->height; // height
                $data['size'] = $size;
                return array($data);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param int $topic_id
     */
    public function forumPostListByTopicId($topic_id=0){

    }

    /**
     * 解析论坛中的旧数据到用户作品表
     */
    public function parse_old_forum_pic(){
        $map['is_top'] = 0;
        $map['status'] = 1;
        $list = M('ForumPost')->where($map)->select();
        foreach ($list as &$v) {
            //解析并成立图片数据
            $tmpImgArr = array();
            preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/",  $v['content'], $tmpImgArr);
            $imgArr = $tmpImgArr[1];
            if(count($imgArr)>0){
                foreach($imgArr as $key => $value){
                    $pic_model = M();
                    $result = $pic_model->query("select id from hisihi_picture where path='".$value."'");
                    if(!empty($result)) {
                        foreach($result as $pic){
                            $user_works_data['uid'] = $v['uid'];
                            $user_works_data['forum_id'] = $v['forum_id'];
                            $user_works_data['post_id'] = $v['id'];
                            $user_works_data['picture_id'] = $pic['id'];
                            $user_works_data['create_time'] = NOW_TIME;
                            $user_works_model = D('User/UserWorks');
                            $user_works_model->add($user_works_data);
                        }
                    }
                }
            }
        }
    }

    /**
     * 解析帖子内容为MD5
     */
    public function parsePostContentMD5(){
        $model = M('ForumPost');
        $list = $model->field('id, content')->page(0, 10)->select();
        foreach ($list as &$v) {
            $content_md5 = md5(str_replace(' ', '', $v['content']));
            $v['content'] = $content_md5;
            $data['content_md5'] = $content_md5;
            $model->where('id='.$v['id'])->save($data);
        }
        //$this->apiSuccess('ok', null, $list);
    }

    public function md5(){
        $sha = sha1('111222333');
        $sha = md5($sha.'m:24iyNJ~1$z(^SjGxe&ngorTfA#7EFu<?.c]Yt+');
        var_dump($sha);
    }

    public function getFirstReplyTeacher($post_id=null){
        $map['post_id'] = $post_id;
        $map['status'] = array('in','1,3');
        $teacher = M()->query('SELECT
	a.uid
FROM
	hisihi_forum_post_reply a
LEFT JOIN hisihi_auth_group_access b ON a.uid = b.uid
WHERE
	a.post_id = '.$post_id.'
AND a.`status` IN (1, 3)
AND b.group_id=6
ORDER BY
	a.create_time DESC
LIMIT 1');
        if(!empty($teacher)){
            $teacher_name = M('Member')->where('uid='.$teacher[0]['uid'])->getField('nickname');
            return $teacher_name;
        } else {
            return null;
        }
    }


    public function parseAtAndTopic($text=null){
        //$text = "我要<user id='1' nickname='Atyyy'/>他要<user id='2' nickname='Atwww'/>哈维大SAV生栋覆屋参与<topic id='33' title='T一个自定义话题'/>按时缴费的无人撒<topic id='55' title='T啥地方拉风'/>我去玩儿sfe";
        $at_preg = "/<user.*?id='(.*?)'(.*?)nickname='(.*?)'\/>/i";
        $topic_preg = "/<topic.*?id='(.*?)'(.*?)title='(.*?)'\/>/i";
        $ee = preg_replace_callback(
            $at_preg,
            function ($matches) {
                $name = '@'.$matches[3].' ';
                str_replace($matches[0], $name, $matches);
                return $name;
            },
            $text
        );
        $rr = preg_replace_callback(
            $topic_preg,
            function ($matches) {
                $topic = '#'.$matches[3].'#';
                str_replace(trim($matches[0]), $topic, $matches[0]);
                return $topic;
            },
            $ee
        );
        return $rr;
    }

    public function resolveTopicIdFromContent($text=null){
        //$text = "送福利卡进入过期日期<user id='112' nickname='孙杨'/>UI温热听你说的方便而<topic id='6' title='里约奥运会'/>考计算机的疯狗娃儿";
        $topic_preg = "/<topic.*?id='(.*?)'(.*?)title='(.*?)'\/>/i";
        $topic_id_list = array();
        preg_match_all($topic_preg, $text, $out);
        foreach($out[1] as $item){
            $topic_id_list[] = $item;
        }
        return $topic_id_list;
    }

    public function getNewlyThreeCommentByPostId($post_id, $page=1, $count=3){
        $id = intval($post_id);
        $page = intval($page);
        $count = intval($count);

        $this->requirePostExists($id);

        //读取回复列表
        $map['post_id'] = $id;
        $map['status'] = array('in','1,3');
        $replyList = D('Forum/ForumPostReply')->getNoCacheTeacherReplyList($map, 'create_time desc', $page, $count);

        $model = M('AuthGroupAccess');
        $replyTotalList = D('ForumPostReply')->field('uid, reply_to_student')->where($map)->select();
        $replyTotalCount = 0;
        foreach($replyTotalList as $t_reply){
            $identify = $model->where('group_id=6 and uid='.$t_reply['uid'])->find();  // 判断老师身份
            if($identify&&$t_reply['reply_to_student']==0){
                $replyTotalCount++;
            }
        }

        $teacherReplyList = array();
        foreach ($replyList as &$reply) {
            $reply_uid = $reply['uid'];
            $toStudent = $reply['reply_to_student'];
            $access_list = $model->where('group_id=6 and uid='.$reply_uid)->find();  // 只显示老师的回复
            if(empty($access_list)||$toStudent==1){
                continue;
            }
            $reply['reply_id'] = $reply['id'];
            unset($reply['id']);

            $reply['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['uid']);

            unset($reply['uid']);
            unset($reply['user']);

            unset($pos);
            unset($map_pos);

            //解析并成立图片数据
            $reply['img'] = $this->match_img($reply['content']);

            $reply['sound'] = $this->fetchSound($reply['reply_id'],1);

            $reply['content'] = op_t($reply['content']);

            $teacherReplyList[] = $reply;
        }
        unset($reply);

        $teacherReplyCount = count($teacherReplyList);
        if($teacherReplyCount<3){
            $replyList = D('Forum/ForumPostReply')->getNoCacheStudentReplyList($map, 'create_time desc', $page, 3-$teacherReplyCount);

            $model = M('AuthGroupAccess');
            $replyTotalList = D('ForumPostReply')->field('uid, reply_to_student')->where($map)->select();
            $replyTotalCount = 0;
            foreach($replyTotalList as $t_reply){
                $identify = $model->where('group_id=5 and uid='.$t_reply['uid'])->find();  // 判断学生身份
                $toStudent = $t_reply['reply_to_student'];
                if($identify||$toStudent==1){
                    $replyTotalCount++;
                }
            }

            foreach ($replyList as &$reply) {
                $reply_uid = $reply['uid'];
                $toStudent = $reply['reply_to_student'];
                $access_list = $model->where('group_id=5 and uid='.$reply_uid)->find();
                if(empty($access_list)&&$toStudent==0){
                    continue;
                }
                $reply['reply_id'] = $reply['id'];
                unset($reply['id']);

                $reply['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['uid']);
                if(intval($reply['to_uid']) > 0){
                    $reply['toUserInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $reply['to_uid']);
                }else{
                    $reply['toUserInfo'] = null;
                }
                unset($reply['uid']);

                unset($pos);
                unset($map_pos);

                //解析并成立图片数据
                $reply['img'] = $this->match_img($reply['content']);

                $reply['sound'] = $this->fetchSound($reply['reply_id'],1);

                $reply['content'] = op_t($reply['content']);

                unset($reply['user']);

                $teacherReplyList[] = $reply;
            }
            unset($reply);
            unset($replyList);
        }
        $result['totalCount'] = count($teacherReplyList);
        $result['data'] = $teacherReplyList;
        return $result;
    }

    public function testForum(){
        $t1 = time();
        $forumPost = M('ForumPost');
        $totalCount = 10;
        $map['id'] = array('in', '5955,5953,5950,5948,5947,5941,5936,5933,5930,5928,5927,5921');
        $list = $forumPost->where('id between 3000 and 5955')->order('last_reply_time desc')->page(5, 10)->select();
        if($list){
            $list = $this->list_sort_by($list, 'last_reply_time');
            $list = $this->formatList($list, 2.96, null, 566);
        } else {
            $totalCount = 0;
            $list = array();
        }
        var_dump(time()-$t1);
        $extra['total_count'] = $totalCount;
        $extra['forumList'] = $list;
        $this->apiSuccess("获列表成功", null, $extra);
    }

}
