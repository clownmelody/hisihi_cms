<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-8
 * Time: PM4:14
 */

namespace Forum\Model;

use Think\Model;

class ForumPostReplyModel extends Model
{
    protected $_validate = array(
        array('content', '1,40000', '内容长度不合法', self::EXISTS_VALIDATE, 'length'),
    );

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME),
        array('status', '1', self::MODEL_INSERT),
    );

    public function addReply($post_id, $content, $toStudent=0, $toUid=0)
    {
        //新增一条回复
        $data = array('uid' => is_login(), 'post_id' => $post_id, 'parse' => 0, 'content' => $content,
            'reply_to_student'=>$toStudent, 'to_uid'=>$toUid);
        $data = $this->create($data);
        if (!$data) return false;
        $result = $this->add($data);
        action_log('add_post_reply', 'ForumPostReply', $result, is_login());

        S('post_replylist_' . $post_id, null);
        //增加帖子的回复数
        $postModel = D('ForumPost');

        $postModel->where(array('id' => $post_id))->setInc('reply_count');

        //更新最后回复时间
        $postModel->where(array('id' => $post_id))->setField('last_reply_time', time());
        $post = $postModel->find($post_id);
        D('Forum')->where(array('id' => $post['forum_id']))->setField('last_reply_time', time());

        $url = $this->sendReplyMessage(is_login(), $post_id, $content, $result);
        $this->handleAt($content, $url);

        //返回结果
        return $result;
    }

    public function addAutoReply($uid, $post_id, $content)
    {
        //新增一条回复
        $data = array('uid' => $uid, 'post_id' => $post_id, 'parse' => 0, 'content' => $content);
        $data = $this->create($data);
        if (!$data) return false;
        $result = $this->add($data);

        S('post_replylist_' . $post_id, null);
        //增加帖子的回复数
        $postModel = D('ForumPost');

        $postModel->where(array('id' => $post_id))->setInc('reply_count');

        //更新最后回复时间
        $postModel->where(array('id' => $post_id))->setField('last_reply_time', time());
        $post = $postModel->find($post_id);
        D('Forum')->where(array('id' => $post['forum_id']))->setField('last_reply_time', time());

        $url = $this->sendReplyMessage(is_login(), $post_id, $content, $result);
        $this->handleAt($content, $url);

        //返回结果
        return $result;
    }


    public function handleAt($content, $url)
    {
        D('ContentHandler')->handleAtWho($content, $url);
    }

    /**
     * @param $uid
     * @param $post_id
     * @param $content
     * @param $reply_id
     * @return string
     * @auth 陈一枭
     */
    private function sendReplyMessage($uid, $post_id, $content, $reply_id)
    {
        $limit = 10;
        $map['status'] = 1;
        $map['post_id'] = $post_id;
        $count = D('ForumPostReply')->where($map)->count();
        $pageCount = ceil($count / $limit);
        //增加微博的评论数量
        $user = query_user(array('nickname', 'space_url'), $uid);
        $post = D('ForumPost')->find($post_id);
        $title = $user['nickname'] . '回复了您的帖子。';
        $content = '回复内容：' . mb_substr(op_t($content), 0, 20);
        $url = U('Forum/Index/detail', array('id' => $post_id, 'page' => $pageCount)) . '#' . $reply_id;
        $from_uid = $uid;
        D('Message')->sendMessage($post['uid'], $content, $title, $url, $from_uid, 2, null, 'reply_post', $post_id, $reply_id);

        return $url;
    }

    public function getReplyList($map, $order, $page, $limit)
    {
        $replyList = S('post_replylist_' . $map['post_id']);
        if ($replyList == null) {
            $replyList = D('ForumPostReply')->where($map)->order($order)->select();
            foreach ($replyList as &$reply) {
                $reply['user'] = query_user(array('avatar128', 'nickname', 'space_url', 'icons_html', 'rank_link'), $reply['uid']);
                $reply['lzl_count'] = D('forum_lzl_reply')->where('is_del=0 and to_f_reply_id=' . $reply['id'])->count();
            }
            unset($reply);
            S('post_replylist_' . $map['post_id'], $replyList, 60);
        }
        $replyList = getPage($replyList, $limit, $page);
        return $replyList;
    }

    public function getNoCacheTeacherReplyList($map, $order, $page, $limit, $last_id=0){
        //$replyList = D('ForumPostReply')->where($map)->order($order)->select();
        $Model = M('ForumPostReply');
        $id = $map['post_id'];
        if(intval($last_id)){
            $replyList = $Model->join('Left JOIN hisihi_auth_group_access ON hisihi_forum_post_reply.uid = hisihi_auth_group_access.uid')
                ->where('hisihi_auth_group_access.group_id=6 and hisihi_forum_post_reply.post_id='.$id.' and hisihi_forum_post_reply.status in(1,3) and hisihi_forum_post_reply.reply_to_student=0 and hisihi_forum_post_reply.id < '.$last_id)
                ->order('hisihi_forum_post_reply.create_time desc')->limit($limit)->field('hisihi_forum_post_reply.*')->select();
        }else{
            $replyList = $Model->join('Left JOIN hisihi_auth_group_access ON hisihi_forum_post_reply.uid = hisihi_auth_group_access.uid')
                ->where('hisihi_auth_group_access.group_id=6 and hisihi_forum_post_reply.post_id='.$id.' and hisihi_forum_post_reply.status in(1,3) and hisihi_forum_post_reply.reply_to_student=0')
                ->order('hisihi_forum_post_reply.create_time desc')->page($page, $limit)->field('hisihi_forum_post_reply.*')->select();
        }

        foreach ($replyList as &$reply) {
            $reply['user'] = query_user(array('avatar128', 'nickname', 'space_url', 'icons_html', 'rank_link'), $reply['uid']);
            $reply['lzl_count'] = D('forum_lzl_reply')->where('is_del=0 and to_f_reply_id=' . $reply['id'])->count();
        }
        unset($reply);
        //$replyList = getPage($replyList, $limit, $page);
        return $replyList;
    }

    public function getNoCacheStudentReplyList($map, $order, $page, $limit){
        //$replyList = D('ForumPostReply')->where($map)->order($order)->select();
        $Model = M('ForumPostReply');
        $id = $map['post_id'];
        $replyList = $Model->join('Left JOIN hisihi_auth_group_access ON hisihi_forum_post_reply.uid = hisihi_auth_group_access.uid')
            ->where('(hisihi_forum_post_reply.reply_to_student=1 and hisihi_forum_post_reply.post_id='.$id.') or hisihi_auth_group_access.group_id=5 and hisihi_forum_post_reply.post_id='.$id.' and hisihi_forum_post_reply.status in(1,3)')
            ->order('hisihi_forum_post_reply.create_time desc')->page($page, $limit)->field('hisihi_forum_post_reply.*')->select();
        foreach ($replyList as &$reply) {
            $reply['user'] = query_user(array('avatar128', 'nickname', 'space_url', 'icons_html', 'rank_link'), $reply['uid']);
            $reply['lzl_count'] = D('forum_lzl_reply')->where('is_del=0 and to_f_reply_id=' . $reply['id'])->count();
        }
        unset($reply);
        //$replyList = getPage($replyList, $limit, $page);
        return $replyList;
    }

    public function delPostReply($id)
    {
        $reply = D('ForumPostReply')->where('id=' . $id)->find();
        $data['status'] = 0;
        CheckPermission(array($reply['uid'])) && $res = $this->where('id=' . $id)->save($data);
        if ($res) {
            $lzlReply_idlist = D('ForumLzlReply')->where('is_del=0 and to_f_reply_id=' . $id)->field('id')->select();
            $info['is_del'] = 1;
            foreach ($lzlReply_idlist as $val) {
                D('ForumLzlReply')->where('id=' . $val['id'])->save($info);
                D('ForumPost')->where(array('id' => $reply['post_id']))->setDec('reply_count');
            }
        }
        D('ForumPost')->where(array('id' => $reply['post_id']))->setDec('reply_count');
        S('post_replylist_' . $reply['post_id'], null);
        return $res;
    }

    public function setReplyTop($id)
    {
        $data['status'] = 3;
        $res = $this->where('id=' . $id)->save($data);
        return $res;
    }

}