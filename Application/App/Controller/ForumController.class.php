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

define('TOP_ALL', 2);
define('TOP_FORUM', 1);

class ForumController extends AppController
{
    protected $forumModel;
    protected $forum_list;
    protected $forum_type;

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
        //$myInfo = query_user(array('avatar128', 'avatar64', 'nickname', 'uid', 'space_url', 'icons_html'), is_login());
        //$this->assign('myInfo', $myInfo);
        //赋予论坛列表
        //$this->assign('forum_list', $this->forum_list);
    }

    //分类列表
    private function getForumsByType($type_id = 0)
    {
        $forum_key_value = array();
        foreach ($this->forum_list as $f) {
            $forum_key_value[$f['id']] = $f;
        }
        //dump($forum_key_value);
        foreach ($this->forum_type as $t) {
            //$t['forums'] = D("Forum/Forum")->getForumList(array('status' => 1, 'type_id' => $t['id']));
            //$t['forums1'] = $forum_key_value[$t['id']];
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
    public function forumType()
    {
        $forum_type = $this->getForumsByType();

        $this->apiSuccess("获取类别标签成功", null, array('types' => $forum_type));
    }
    private function formatList($list)
    {
        $map_support['appname'] = 'Forum';
        $map_support['table'] = 'post';

        $forum_key_value = array();
        foreach ($this->forum_list as $f) {
            $forum_key_value[$f['id']] = $f;
        }

        foreach ($list as &$v) {
            $forumInfo = $forum_key_value[$v['forum_id']];
            $mapx = array('id' => $forumInfo['type_id'], 'status' => 1);
            $forumType = D('ForumType')->where($mapx)->select();
            $v['post_id'] = $v['id'];
            $v['forumTitle'] = $forumInfo['title'] . '/' . $forumType[0]['title'];

            $v['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $v['uid']);

            //解析并成立图片数据
            $v['img'] = $this->match_img($v['content']);
            /*
            $tmpImgArr = array();
            preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/",  $v['content'], $tmpImgArr); //匹配所有的图片
            $imgArr = $tmpImgArr[1];
            if(!empty($imgArr)){
                $dm = "http://$_SERVER[HTTP_HOST]" . __ROOT__; //前缀图片多余截取
                $dmip = "http://$_SERVER[SERVER_ADDR]" . __ROOT__; //前缀图片多余截取
                foreach($imgArr as &$v1){
                    if(strstr($v1,$dm))
                        $v1 = mb_substr($v1, strlen($dm), strlen($v1) - strlen($dm));
                    else if(strstr($v1,$dmip)){
                        $v1 = mb_substr($v1, strlen($dmip), strlen($v1) - strlen($dmip));
                    }
                    $pic_small = getThumbImage($v1, 280, 160);
                    $v1['src'] = $v1;
                    $v1['thumb'] = $pic_small['src'];
                    $v1['size'] = $pic_small['width'];
                    //dump($pic_small);
                }
                //$v['img']['thumb'] = $imgArr;
                $v['img']['src'] = $imgArr;
            }
            */

            $v['sound'] = $this->fetchSound($v['id'],0);
            /*
            $sound = D('ForumSound')->where(array('fid' => $v['id'],'ftype' => 0))->find();
            if($sound) {
                $root = C('DOWNLOAD_UPLOAD.rootPath');
                $sound = $root.$sound['savepath'].$sound['savename'];
                if(!is_file($sound)){
                    $sound = null;
                }
            }
            $v['sound'] = $sound;
            */

            $v['content'] = op_t($v['content']);

            $map_support['row'] = $v['id'];
            $supportCount = $this->getSupportCountCache($map_support);

            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();

            $v['supportCount'] = $supportCount;
            $v['isSupportd'] = $supported;

            $map_pos['type'] = 0;
            $map_pos['id'] = $v['id'];
            $pos = $this->getForumPos($map_pos);
            $v['pos'] = $pos['pos'];
            $v['shareUrl'] = 'app.php/forum/detail/type/view/post_id/'.$v['id'];

            unset($pos);
            unset($v['id']);
            unset($v['uid']);
            unset($v['forum']);
            unset($v['parse']);
            unset($v['status']);
        }
        unset($v);

        return $list;
    }

    /**
     * @param int $type_id  帖子类型
     * @param int $page
     * @param int $count
     * @param int $is_reply  -1 全部 0 无回答 1 有回答
     * @param string $order
     */
    public function forum($type_id = 0, $page = 1, $count = 10, $is_reply = -1, $order = 'ctime', $show_adv=false, $post_type=1)
    {
        //$this->requireLogin();
        $type_id = intval($type_id);
        //$id = intval($id);
        $page = intval($page);
        $count = intval($count);
        $order = op_t($order);

//        $count = S('forum_count' . $id);
//        if (empty($count)) {
//
//            $map['status'] = 1;
//            $count['forum'] = D('Forum')->where($map)->count();
//            $count['post'] = D('ForumPost')->where($map)->count();
//            $count['all'] = $count['post'] + D('ForumPostReply')->where($map)->count() + D('ForumLzlReply')->where($map)->count();
//            S('forum_count', $count, 60);
//        }
//        $this->assign('count', $count);

        if ($order == 'ctime') {
            $order = 'create_time desc';
        } else if ($order == 'reply') {
            $order = 'last_reply_time desc';
        } else {
            $order = 'last_reply_time desc';//默认的
        }

        //$this->requireForumAllowView($id);

//        //读取置顶列表
//        if ($id == 0) {
//            $map = array('status' => 1);
//            $list_top = D('ForumPost')->where(' status=1 AND is_top=' . TOP_ALL)->order($order)->select();
//        } else {
//            $map = array('forum_id' => $id, 'status' => 1);
//            $list_top = D('ForumPost')->where('status=1 AND (is_top=' . TOP_ALL . ') OR (is_top=' . TOP_FORUM . ' AND forum_id=' . intval($id) . ' and status=1)')->order($order)->select();
//        }
//
//        $list_top = $this->formatList($list_top);

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
        $totalCount = D('ForumPost')->where($map)->count();

        $list = $this->formatList($list);

        if($show_adv==true){
            $len = count($list);
            $list[$len] = $this->getOneForumAdv(640, 960);
        }

        $this->apiSuccess("获取提问列表成功", null, array( 'total_count' => $totalCount, 'forumList' => $list));
    }

    /**
     * 论坛数据筛选
     * @param int $field_type -1 最新 -2 无回答 -3 有回答 -4 热门 1..帖子小类别
     * @param int $page
     * @param int $count
     * @param string $order
     */
    public function forumFilter($field_type = -1, $page = 1, $count = 10, $order = 'ctime', $show_adv=false, $post_type=1)
    {
        $field_type = intval($field_type);
        $page = intval($page);
        $count = intval($count);
        $order = op_t($order);

        if ($order == 'ctime') {
            $order = 'create_time desc';
        } else if ($order == 'reply') {
            $order = 'last_reply_time desc';
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

        if($field_type == -2)  // 无回复
            $map['reply_count'] = 0;
        if($field_type == -3)  // 有回复
            $map['reply_count'] = array('gt',0);
        if($field_type == -4){  // 热门
            $order = "reply_count desc";
        }
        $list = D('ForumPost')->where($map)->order($order)->page($page, $count)->select();
        $list = $this->list_sort_by($list, 'last_reply_time');
        $totalCount = D('ForumPost')->where($map)->count();

        $list = $this->formatList($list);

        if($show_adv==true){
            $len = count($list);
            $adv = $this->getOneForumAdv(640, 960);
            if($adv){
                $list[$len] = $adv;
            }
        }

        $this->apiSuccess("获取提问列表成功", null, array( 'total_count' => $totalCount, 'forumList' => $list));
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
    public function userForumList($uid = null, $page = 1, $count = 10, $tab = null)
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
        $content = $this->formatList($content);
        if (empty($content)) {
            $totalCount = 0;
            $content = null;
        } else {
            $totalCount = D(ucfirst($type) . '/' . $className)->getTotalCount($uid, $tab);
        }

        //返回结果
        $this->apiSuccess("获取成功", null, array('total_count' => $totalCount, 'list' => $content));
    }

    public function getPostInfo($id, $lite = 0)
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
            $map_pos['id'] = $id;
            $pos = $this->getForumPos($map_pos);
            $post['pos'] = $pos['pos'];

            unset($pos);
            unset($map_pos);
        }

        //$post['content'] = op_h($post['content'], 'html');
        //解析并成立图片数据
        $post['img'] = $this->match_img($post['content']);
        /*
        $tmpImgArr = array();
        preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/",  $post['content'], $tmpImgArr); //匹配所有的图片
        $imgArr = $tmpImgArr[1];
        if(!empty($imgArr)){
            $dm = "http://$_SERVER[HTTP_HOST]" . __ROOT__; //前缀图片多余截取
            $dmip = "http://$_SERVER[SERVER_ADDR]" . __ROOT__; //前缀图片多余截取
            foreach($imgArr as &$v1){
                if(strstr($v1,$dm))
                    $v1 = mb_substr($v1, strlen($dm), strlen($v1) - strlen($dm));
                else if(strstr($v1,$dmip)){
                    $v1 = mb_substr($v1, strlen($dmip), strlen($v1) - strlen($dmip));
                }
            }
            $post['img'] = $imgArr;
        }
        */

        $post['sound'] = $this->fetchSound($id,0);
        /*
        $sound = D('ForumSound')->where(array('fid' => $id,'ftype' => 0))->find();
        if($sound) {
            $root = C('DOWNLOAD_UPLOAD.rootPath');
            $sound = $root.$sound['savepath'].$sound['savename'];
            if(!is_file($sound)){
                $sound = null;
            }
        }
        $post['sound'] = $sound;
        */

        $post['content'] = op_t($post['content']);

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
            $map_pos['id'] = $id;
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
        $reply = D('ForumPostReply')->where($map)->field('uid,create_time,content')->find();
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
    public function detail($post_id, $page = 1, $count = 10, $type = '')
    {
        //$this->requireLogin();

        $id = intval($post_id);
        $page = intval($page);
        $count = intval($count);

        $this->requirePostExists($id);

        //判断是否需要显示1楼
        if ($page == 1) {
            $showMainPost = true;
            $post = $this->getPostInfo($id);
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
            $map_pos['id'] = $reply['reply_id'];
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
            /*
            $tmpImgArr = array();
            preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/",  $reply['content'], $tmpImgArr); //匹配所有的图片
            $imgArr = $tmpImgArr[1];
            if(!empty($imgArr)){
                $dm = "http://$_SERVER[HTTP_HOST]" . __ROOT__; //前缀图片多余截取
                $dmip = "http://$_SERVER[SERVER_ADDR]" . __ROOT__; //前缀图片多余截取
                foreach($imgArr as &$v1){
                    if(strstr($v1,$dm))
                        $v1 = mb_substr($v1, strlen($dm), strlen($v1) - strlen($dm));
                    else if(strstr($v1,$dmip)){
                        $v1 = mb_substr($v1, strlen($dmip), strlen($v1) - strlen($dmip));
                    }
                }
                $reply['img'] = $imgArr;
            }
            */

            $reply['sound'] = $this->fetchSound($reply['reply_id'],1);
            /*
            $sound = D('ForumSound')->where(array('fid' => $reply['reply_id'],'ftype' => 1))->find();
            if($sound) {
                $root = C('DOWNLOAD_UPLOAD.rootPath');
                $sound = $root.$sound['savepath'].$sound['savename'];
                if(!is_file($sound)){
                    $sound = null;
                }
            }
            $reply['sound'] = $sound;
            */

            $reply['content'] = op_t($reply['content']);
            //$reply['userinfo']['nickname'] = $reply['user']['nickname'];
            //$reply['userinfo']['avatar128'] = $reply['user']['avatar128'];

            unset($reply['user']);
            $lzlList = D('Forum/ForumLzlReply')->getLZLReplyList($reply['reply_id'],'ctime asc',$page,$limit,false);
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
                $map_pos['id'] = $lzl['lzl_id'];
                $pos = $this->getForumPos($map_pos);
                $lzl['pos'] = $pos['pos'];

                unset($pos);
                unset($map_pos);

                $lzl['img'] = $this->match_img($lzl['content']);
                $lzl['content'] = op_t($lzl['content']);
                $lzl['sound'] = $this->fetchSound($lzl['lzl_id'],2);
                /*
                $sound = D('ForumSound')->where(array('fid' => $lzl['lzl_id'],'ftype' => 2))->find();
                if($sound) {
                    $root = C('DOWNLOAD_UPLOAD.rootPath');
                    $sound = $root.$sound['savepath'].$sound['savename'];
                    if(!is_file($sound)){
                        $sound = null;
                    }
                }
                $lzl['sound'] = $sound;
                */
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
            $this->display();
        }
        else
            $this->apiSuccess("获取提问内容成功", null, array('isBookmark' => $isBookmark, 'replyTotalCount' =>  $replyTotalCount, 'post' => $post, 'replyList' => $replyList, 'showMainPost' => $showMainPost));
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

    //发表提问
    public function doPost($post_id = null, $forum_id = 0, $title='标题', $content = ' ', $pos = null, $pictures = null, $sound = null, $atUids = null, $iscompany=0)
    {
        $this->requireLogin();
        //dump($content);
        //include_once './Application/App/Common/emoji.php';
        //$content = emoji_softbank_to_unified($content);
        //dump($content);
        $post_id = intval($post_id);
        $forum_id = intval($forum_id);
        $title = op_t($title);
        if(empty($title)){
            $title='标题';
        }
        // if($content != ' ')
        //     $content = op_t($content);

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
            $this->apiError(-101,'未选择标签');
        }
        //if (strlen($content) < 5) {
        //    $this->apiError(-102,'提问失败：内容长度不能小于5');
        //}

        $pictures_ids_str = $pictures;
        $pictures = $this->fetchImage($pictures);
        foreach($pictures as $pic) {
            $content = $content.$pic;
        }

        $content = filterBase64($content);
        //检测图片src是否为图片并进行过滤
        $content = filterImage($content);

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
            /*$t_uid = is_login();
            $t_list = D('Forum/ForumPost')->where(array('uid' => $t_uid, 'content_md5' => $content_md5))->find();
            if($t_list){
                $this->apiError(-2, "你已经发过该帖子了");
            }*/

            $data = array('uid' => is_login(), 'title' => $title, 'content' => $content, 'parse' => 0, 'forum_id' => $forum_id, 'content_md5' => $content_md5);

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
                $map_pos['id'] = $post_id;
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
        $user_works_data['uid'] = is_login();
        $user_works_data['forum_id'] = $forum_id;
        $user_works_data['post_id'] = $post_id;
        $user_works_data['picture_id'] = $pictures_ids_str;
        $user_works_data['create_time'] = NOW_TIME;
        $user_works_model = D('User/UserWorks');
        $user_works_model->saveWorks($user_works_data);

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
            if(!$iscompany){
                D('Forum/ForumAt')->addAtPost($param['fans_id'], $uid, $post_id, 2);
            }else{
                D('Forum/ForumAt')->addAtPost($param['fans_id'], $uid, $post_id);
            }
        }
        $param['reg_id'] = $reg_ids;
        $param['user_id'] = $atUids;
        $param['production'] = C('APNS_PRODUCTION');
        if($param['fans_id']!=$param['user_id']){
            Hook::exec('Addons\\JPush\\JPushAddon', 'push_question_asked', $param);
        }

        //显示成功消息
        $message = $isEdit ? '编辑成功。' : '提问成功。' . getScoreTip($before, $after) . getToxMoneyTip($tox_money_before, $tox_money_after);
        $extra['post_id'] = $post_id;
        $extra['shareUrl'] = 'app.php/forum/detail/type/view/post_id/'.$post_id;
        /*$uid = $this->getUid();
        if(increaseScore($uid, 1)){
            $extraData['scoreAdd'] = "1";
            $extraData['scoreTotal'] = getScoreCount($uid);
            $extra['score'] = $extraData;
        }*/
        $this->apiSuccess($message,null, $extra);
    }

    public function doReply($post_id, $content = ' ', $pos = null, $pictures = null, $sound = null)
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
            $result = $model->addReply($post_id, $content);
            if (!$result) {
                $this->apiError($model->getError(),'回复失败');
            }
            if($pos != null) {
                $map_pos['id'] = $result;
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
            /*if(increaseScore($uid, 3)){
                $extraData['scoreAdd'] = "3";
                $extraData['scoreTotal'] = getScoreCount($uid);
                $extra['score'] = $extraData;
            }*/
            $this->apiSuccess('回复成功。' . getScoreTip($before, $after) . getToxMoneyTip($tox_money_before, $tox_money_after), null, $extra);
        } else {
            $this->apiError(-101,'请10秒之后再回复');
        }
    }

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
        if (!$result) {
            $this->apiError($model->getError(),'追问失败！');
        }
        if($pos != null) {
            $map_pos['id'] = $result;
            $map_pos['type'] = 2;
            $map_pos['pos'] = $pos;
            $this->setForumPos($map_pos);
        }
        if($sound != null) {
            $Sound = D('ForumSound');
            $data = array('fid' => $result,'ftype' => 2);
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
        $param['lzl_id'] = $result;
        $param['reply_id'] = $to_f_reply_id;

        $param['user_id'] = $to_uid;
        $map = array('uid'=>$param['user_id']);
        $_user = $model->where($map)->find();
        $param['reg_id'] = $_user['reg_id'];
        $param['production'] = C('APNS_PRODUCTION');
        if($param['fans_id']!=$param['user_id']){
            Hook::exec('Addons\\JPush\\JPushAddon', 'push_floor_reply', $param);
        }

        //显示成功
        $this->apiSuccess('追问成功。' . getScoreTip($before, $after) . getToxMoneyTip($tox_money_before, $tox_money_after), null, array('lzl_id' => $result));
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
                /*if(increaseScore($message_uid, 1)){
                    $extraData['scoreAdd'] = "1";
                    $extraData['scoreTotal'] = getScoreCount($message_uid);
                    $extra['score'] = $extraData;
                }*/
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
     */
    public function forumTopPost(){
        $list = M('ForumPost')->where('forum_id=0 and is_top=1 and status=1')
            ->order('create_time desc')->page(1, 3)->select();
        foreach($list as &$value){
            $value['url'] = 'http://hisihi.com/app.php/forum/topPostDetail/post_id/'.$value['id'];
            unset($value['uid']);
            unset($value['forum_id']);
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
        $data = M('ForumPost')->where('status=1 and is_top=1')->find($id);
        if(empty($data)){
            $this->apiError(-1, '传入置顶帖ID无效');
        }
        $result['id'] = $id;
        $result['url'] = 'http://hisihi.com/app.php/forum/topPostDetail/post_id/'.$data['id'];
        $result['title'] = $data['title'];
        $result['type'] = $data['type'];
        $this->apiSuccess('获取推送置顶帖信息成功', null, $result);
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
            $map_pos['id'] = $reply['reply_id'];
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
                //unset($lzl['userInfo']['icons_html']);
                //unset($lzl['userInfo']['uid']);
                //unset($lzl['userInfo']['space_url']);
                $lzl['lzl_id'] = $lzl['id'];
                unset($lzl['id']);
                $lzl['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $lzl['uid']);
                unset($lzl['uid']);
                //unset($reply['uid']);

                $map_pos['type'] = 2;
                $map_pos['id'] = $lzl['lzl_id'];
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
            $map_pos['id'] = $reply['reply_id'];
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
                $map_pos['id'] = $lzl['lzl_id'];
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
    public function companyHotTopics($id, $page = 1, $count = 10){
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
        $this->apiSuccess('ok', null, $list);
        $list = $this->formatList($list);
        $this->apiSuccess("获取公司热门话题列表成功", null, array( 'total_count' => $totalCount, 'forumList' => $list));
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
     * @param $id
     * @param $type
     * @return mixed
     * @auth RFly
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
     * @param $id
     * @param $type
     * @return mixed
     * @auth RFly
     */
    private function getForumPos($map_pos)
    {
        $cache_key = "forum_pos_" . implode('_', $map_pos);
        $pos = S($cache_key);
        if (empty($pos)) {
            $pos = D('ForumPos')->where($map_pos)->field('pos')->find();
            S($cache_key, $pos);
            return $pos;
        }
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
                    $origin_img_info = getimagesize($img['src']);
                    $src_size[] = $origin_img_info[0]; // width
                    $src_size[] = $origin_img_info[1]; // height
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
     * 获取一条社区内的广告
     * @param $width
     * @param $height
     * @return bool
     */
    public function getOneForumAdv($width, $height){
        $model = M();
        $now = time();
        $picKey = "advspic_".$width.'_'.$height;
        $result = $model->query("select ".$picKey." , title, link from hisihi_advs where ".
            "position=4 and status=1 and ".$now." between create_time and end_time order by id desc");
        if($result){
            $picID = $result[0][$picKey];
            $advLink = $result[0]['link'];
            $advTitle = $result[0]['title'];
            $result = $model->query("select path from hisihi_picture where id=".$picID);
            if($result){
                $path = $result[0]['path'];
                $objKey = substr($path, 17);
                $picUrl = "http://advs-pic.oss-cn-qingdao.aliyuncs.com/".$objKey;
                $data['type'] = "advertisment";
                $data['pic'] = $picUrl;
                $data['content_url'] = $advLink;
                $data['title'] = $advTitle;
                $origin_img_info = getimagesize($picUrl);
                $size[] = $origin_img_info[0]; // width
                $size[] = $origin_img_info[1]; // height
                $data['size'] = $size;
                return $data;
            } else {
                return false;
            }
        } else {
            return false;
        }
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
}
