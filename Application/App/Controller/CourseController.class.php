<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 14/5/15
 * Time: 4:20 PM
 */

namespace App\Controller;

use Think\Controller;
use Think\Exception;
use Think\Model;


class CourseController extends AppController
{
    protected $course_type;

    public function _initialize()
    {
        $this->course_type = D('Issue/Issue')->getTree(0, 'id,title,sort,pid,status');
    }

    public function courseType($type_id = 0)
    {
        $course_type = $this->course_type;
        foreach($course_type as $key => &$value){
            if($type_id == $value['id']){
                $type_title = $value['title'];
                break;
            }
            $value['ctypes'] = $value['_'];
            unset($value['pid']);
            unset($value['sort']);
            unset($value['status']);
            unset($value['_']);
            foreach ($value['ctypes'] as $key1 => &$cvalue) {
                if($type_id == $cvalue['id']){
                    //$type_title = $cvalue['title'].'/'.$value['title'];
                    $type_title = $cvalue['title'];
                    break;
                }
                unset($cvalue['pid']);
                unset($cvalue['sort']);
                unset($cvalue['status']);
            }
        }
        if($type_id !== 0)
            return $type_title;
        else
            $this->apiSuccess("获取类别成功", null, array('types' => $course_type));
    }

    //课程列表  order=view|reply|support
    public function listCourses($type_id = 0, $page = 1, $count = 10, $order = '',  $recommend = 0, $id = 0, $keywords = '', $type = '')
    {
        $type_id = intval($type_id);
        $page = intval($page);
        $count = intval($count);
        $order = op_t($order);

        if ($order == 'view') {
            $order = 'view_count desc';
        } else if ($order == 'reply') {
            $order = 'reply_count desc';
        } else {
            $order = 'create_time desc';//默认的
        }

        $map['status'] = array('gt' , 0);
        if($type_id !== 0){
            $issueType = D('Issue')->field('pid')->find($type_id);
            if(!$issueType)
                $this->apiError(-404, '未找到该课程分类！');
            if($issueType['pid'] == 0){
                $issueTypeList = D('Issue')->field('id')->where('pid='.$type_id)->select();
                $issueTypeIds[] = $type_id;
                foreach($issueTypeList as $issueType){
                    $issueTypeIds[] = $issueType['id'];
                }
                $ids= implode(',',$issueTypeIds);
                $map['issue_id'] = array('in',$ids);
            } else {
                $map['issue_id'] = $type_id;
            }
        }
        if($keywords != ''){
            $keywords = '%'.$keywords.'%';
            $map['title'] = array('like',$keywords);
        }
        if($recommend == 1)
            $map['status'] = 2;
        if($id != 0){
            $course = D('IssueContent')->field('issue_id')->find($id);
            if(!$course){
                $this->apiError(-404, '未找到该课程！');
            }
            $map['id'] = array('neq' , $id);
            $map['issue_id'] = $course['issue_id'];
        }
        $coursesList = D('IssueContent')->where($map)->order($order)->page($page, $count)->select();
        $totalCount = D('IssueContent')->where($map)->count();

        if($type == 'view')
            $fetchImage = 1;
        $coursesList = $this->formatList($id, $coursesList,$fetchImage);

        if($type == 'view') {
            return $coursesList;
        } else {
            $this->apiSuccess("获取课程列表成功", null, array('coursesList' => $coursesList, 'total_count' => $totalCount));
        }
    }

    //相关课程列表
    public function relatedCourses($id, $page = 1, $count = 10, $type = '')
    {
        return $this->listCourses(0,$page,$count,'',0,$id,'',$type);
    }

    //推荐课程列表
    public function recommendCourses($page = 1, $count = 10)
    {
        $this->listCourses(0,$page,$count,'',1);
    }

    //猜你喜欢课程列表
    public function guessULikeCourses($page = 1, $count = 10)
    {
        $this->listCourses(0,$page,$count,'view',1);
    }

    //课程详情
    public function courseDetail($id, $type = '')
    {
        if(($course_content = $this->findCourse($id,1)) != null){
            if($type == 'view') {
                $course_content['duration'] = $this->sec2time($course_content['duration']);
                $this->assign('course_content', $course_content);
                $commentList = $this->commentList($id, 1, 10, $type);
                $this->assign('commentList', $commentList);
                $relatedList = $this->relatedCourses($id,1,10,$type);
                $this->assign('relatedList',$relatedList);
                $this->setTitle('{$course_content.title|op_t} — 嘿设汇');
                $this->display();
            }
            else
                $this->apiSuccess("获取课程详情成功", null, array('course' => $course_content));
        }

        else
            $this->apiError(-404, '未找到该课程！');
    }

    //获取课程分享URL
    public function courseShareURL($id)
    {
        if(($course_content = $this->findCourse($id)) != null){
            $extra['course_share_url'] = 'app.php/course/coursedetail/type/view/id/'.$id;
            $uid = $this->getUid();
            if($uid){
                if(increaseScore($uid, 1)){
                    $extraData['scoreAdd'] = "1";
                    $extraData['scoreTotal'] = getScoreCount($uid);
                    $extra['score'] = $extraData;
                }
            }
            $this->apiSuccess("获取课程分享链接成功", null, $extra);
        }
        else
            $this->apiError(-404, '未找到该课程！');
    }

    //获取课程视频URL
    public function courseVideoURL($id)
    {
        if(($course_content = $this->findCourse($id)) != null){
            //调用OSS插件
            //$Addons = A("Addons://Aliyun_Oss/AliyunOss");
            //$url = $Addons->generatePresignedUrl($id);

            $this->apiSuccess("获取课程视频链接成功", null, array('course_video_url' => $course_content['url']));
        }

        else
            $this->apiError(-404, '未找到该课程对应Video！');
    }

    public function findCourse($id, $fetchImage = 0){
        $course_content = D('IssueContent')->find($id);
        if($course_content){
            D('IssueContent')->where(array('id' => $id))->setInc('view_count');

            if($course_content['update_time'] == 0){
                $course_content['update_time'] = $course_content['create_time'];
            }
            $course_content['type'] = $this->courseType($course_content['issue_id']);
            $course_content['type_id'] = $course_content['issue_id'];

            //调用OSS接口获取URL
            

            //解析并成立图片数据
            $oss_pic_pre = 'http://game-pic.oss-cn-qingdao.aliyuncs.com/';
            if($course_content['cover_id'] == -1){
                $course_content['img'] = str_replace('OSS-', $oss_pic_pre, $course_content['img']);
            } else {
                $course_content['img'] = $this->fetchImage($course_content['cover_id'],$fetchImage);
            }

            //生成URL
            $oss_video_pre = 'http://game-video.oss-cn-qingdao.aliyuncs.com/';
            $oss_video_post = '/p.m3u8';
            $course_content['url'] = $oss_video_pre . $course_content['url'] . $oss_video_post;

            $course_content['content'] = op_t($course_content['content']);

            $map_support['appname'] = 'Issue';
            $map_support['table'] = 'issue_content';
            $map_support['row'] = $course_content['id'];
            $supportCount = $this->getSupportCountCache($map_support);

            //查询条件同support
            $favoriteCount = $this->getFavoriteCountCache($map_support);

            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();

            //查询条件同supported
            $favorited = D('Favorite')->where($map_supported)->count();

            $course_content['isRecommend'] = $course_content['status'] == 2 ? '1':'0';
            $course_content['ViewCount'] = $course_content['view_count'];
            $course_content['ReplyCount'] = $course_content['reply_count'];
            $course_content['favoriteCount'] = $favoriteCount + C('VIDEO_BASE_FAVORITE');
            $course_content['isFavorited'] = $favorited;
            $course_content['supportCount'] = $supportCount + C('VIDEO_BASE_SUPPORT');
            $course_content['isSupportd'] = $supported;

            unset($course_content['uid']);
            unset($course_content['create_time']);
            unset($course_content['issue_id']);
            unset($course_content['cover_id']);
            unset($course_content['view_count']);
            unset($course_content['reply_count']);
            unset($course_content['status']);

            return $course_content;
        } else {
            return null;
        }
    }

    //评论
    public function doComment($id,$content)
    {
        $this->requireLogin();

        //获取参数
        $app = 'Issue';
        $mod = 'issueContent';
        $row_id = $id;
        $content = op_t($content);
        $uid = is_login();

        //调用API接口，添加新评论
        $data = array('app' => $app, 'mod' => $mod, 'row_id' => $row_id, 'content' => $content,'uid'=>$uid);
        D($app.'/'.$mod)->where(array('id'=>$row_id))->setInc('reply_count');
        $commentModel = D('Addons://LocalComment/LocalComment');
        $data = $commentModel->create($data);
        if (!$data) {
            $this->apiError(-100,'评论失败：' . $commentModel->getError());
        }
        $result = $commentModel->add($data);

        //通知被@到的人
        $uids = get_at_uids($content);
        $uids = array_unique($uids);
        $uids = array_subtract($uids, array($uid));
        foreach ($uids as $uid) {
            $user = D('Member')->find(get_uid());
            $title = $user['nickname'] . '@了您';
            $message = '评论内容：' . $content;
            $url = $_SERVER['HTTP_REFERER'];
            D('Common/Message')->sendMessage($uid, $message, $title, $url, get_uid(), 0, $app);
        }
        //返回结果
        $extra['commentID'] = $result;

        $uid = $this->getUid();
        if(increaseScore($uid, 2)){
            $extraData['scoreAdd'] = "2";
            $extraData['scoreTotal'] = getScoreCount($uid);
            $extra['score'] = $extraData;
        }
        $this->apiSuccess('评论成功', null, $extra);
    }

    //评论列表
    public function commentList($id, $page = 1, $count = 10, $type='')
    {
        $app = 'Issue';
        $mod = 'issueContent';
        $row_id = $id;
        $commentModel = D('Addons://LocalComment/LocalComment');
        $map = array('app' => $app, 'mod' => $mod, 'row_id' => $row_id, 'status' => 1);
        $list = $commentModel->where($map)->order('create_time desc')->page($page, $count)->select();
        $totalCount = intval(0);
        foreach($list as &$comment){
            $comment['course_id'] = $comment['row_id'];
            $comment['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $comment['uid']);
            unset($comment['uid']);
            unset($comment['app']);
            unset($comment['mod']);
            unset($comment['row_id']);
            unset($comment['parse']);
            unset($comment['pid']);
            unset($comment['status']);
            $totalCount++;
        }
        $totalCount = $commentModel->where($map)->count();
        //返回结果
        if($type == 'view') {
            return($list);
        } else {
            $this->apiSuccess('获取评论列表成功', null, array('commentList' => $list, 'total_count' => $totalCount));
        }
    }

    //收藏
    public function doFavorite($id)
    {
        $this->requireLogin();

        $favorite['appname'] = 'Issue';
        $favorite['table'] = 'issue_content';
        $favorite['row'] = $id;
        $favorite['uid'] = is_login();

        if (D('Favorite')->where($favorite)->count()) {
            $this->apiError(-100,'您已经收藏，不能再收藏了!');
        } else {
            $favorite['create_time'] = time();
            if (D('Favorite')->where($favorite)->add($favorite)) {
                $this->clearCache($favorite,'favorite');
                $uid = $this->getUid();
                if(increaseScore($uid, 1)){
                    $extraData['scoreAdd'] = "1";
                    $extraData['scoreTotal'] = getScoreCount($uid);
                    $extra['score'] = $extraData;
                }
                $this->apiSuccess('感谢您的支持', null, $extra);
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }

        }
    }

    //删除收藏
    public function deleteFavorite($id)
    {
        $this->requireLogin();

        $favorite['appname'] = 'Issue';
        $favorite['table'] = 'issue_content';
        $favorite['row'] = $id;
        $favorite['uid'] = is_login();

        if (D('Favorite')->where($favorite)->count()) {
            if (D('Favorite')->where($favorite)->delete()) {
                $this->clearCache($favorite,'favorite');

                $this->apiSuccess('删除收藏成功！');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        } else {
            $this->apiError(-102,'您还没有收藏过，不能删除!');
        }
    }

    //点赞
    public function doSupport($id)
    {
        $this->requireLogin();

        $support['appname'] = 'Issue';
        $support['table'] = 'issue_content';
        $support['row'] = $id;
        $support['uid'] = is_login();

        if (D('Support')->where($support)->count()) {
            $this->apiError(-100,'您已经赞过，不能再赞了!');
        } else {
            $support['create_time'] = time();
            if (D('Support')->where($support)->add($support)) {
                $this->clearCache($support);

                $this->apiSuccess('感谢您的支持');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }

        }
    }
    //取消点赞
    public function unDoSupport($id)
    {
        $this->requireLogin();

        $support['appname'] = 'Issue';
        $support['table'] = 'issue_content';
        $support['row'] = $id;
        $support['uid'] = is_login();

        if (D('Support')->where($support)->count()) {
            if (D('Support')->where($support)->delete()) {
                $this->clearCache($support);

                $this->apiSuccess('取消支持成功！');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        } else {
            $this->apiError(-102,'您还没有赞过，不能取消!');
        }
    }

    public function userFeedBack($course_name='', $school='', $email=''){
        if(empty($course_name)||empty($school)||empty($email)){
            $this->apiError(-1, "传入参数为空");
        } else {
            $model = D("Admin/CourseFeedBack");
            $data['course_name'] = $course_name;
            $data['school'] = $school;
            $data['email'] = $email;
            $data['create_time'] = time();
            $data['resolved'] = 0;
            $result = $model->addFeedBack($data);
            if($result){
                $this->apiSuccess("用户反馈成功");
            } else {
                $this->apiError(-1, "用户反馈异常");
            }
        }
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
     * @param $map_support
     * @return mixed
     * @auth RFly
     */
    private function getFavoriteCountCache($map_favorite)
    {
        $cache_key = "favorite_count_" . implode('_', $map_favorite);
        $count = S($cache_key);
        if (empty($count)) {
            $count = D('Favorite')->where($map_favorite)->count();
            S($cache_key, $count);
            return $count;
        }
        return $count;
    }

    /**
     * @param $condition
     * @auth RFly
     */
    private function clearCache($condition,$type='support')
    {
        unset($condition['uid']);
        unset($condition['create_time']);
        if($type == 'support')
            $cache_key = "support_count_" . implode('_', $condition);
        else if($type == 'favorite')
            $cache_key = "favorite_count_" . implode('_', $condition);
        S($cache_key, null);
    }

    private function formatList($id, $list, $fetchImage = 0)
    {
        $map_support['appname'] = 'Issue';
        $map_support['table'] = 'issue_content';

        $forum_key_value = array();
        foreach ($this->forum_list as $f) {
            $forum_key_value[$f['id']] = $f;
        }

        foreach ($list as $key=>&$v) {
            // if($v['id'] == $id) {
            //     unset($list[$key]);
            //     continue;
            // }
            if($v['update_time'] == 0){
                $v['update_time'] = $v['create_time'];
            }
            $v['type_id'] = $v['issue_id'];
            $v['type'] = $this->courseType($v['issue_id']);

            //解析并生成图片数据
            $oss_pic_pre = 'http://game-pic.oss-cn-qingdao.aliyuncs.com/';
            if($v['cover_id'] == -1){
                $v['img'] = str_replace('OSS-', $oss_pic_pre, $v['img']);
            } else {
                $v['img'] = $this->fetchImage($v['cover_id'],$fetchImage);
            }            

            $v['content'] = op_t($v['content']);

            if($fetchImage == 1) {
                $v['duration'] = $this->sec2time($v['duration']);
            }

            $map_support['row'] = $v['id'];
            $supportCount = $this->getSupportCountCache($map_support);

            //查询条件同support
            $favoriteCount = $this->getFavoriteCountCache($map_support);

            $map_support['appname'] = 'Issue';
            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = D('Support')->where($map_supported)->count();

            //查询条件同supported
            $favorited = D('Favorite')->where($map_supported)->count();

            $v['ViewCount'] = $v['view_count'];
            $v['ReplyCount'] = $v['reply_count'];
            $v['supportCount'] = $supportCount + C('VIDEO_BASE_SUPPORT');
            $v['isSupportd'] = $supported;
            $v['favoriteCount'] = $favoriteCount + C('VIDEO_BASE_FAVORITE');
            $v['isFavorited'] = $favorited;

            $v['isRecommend'] = $v['status'] == 2 ? '1':'0';

            unset($v['create_time']);
            unset($v['url']);
            unset($v['uid']);
            unset($v['issue_id']);
            unset($v['cover_id']);
            unset($v['view_count']);
            unset($v['reply_count']);
            unset($v['status']);
        }
        unset($v);

        return $list;
    }

    private function fetchImage($pic_id, $withRoot = 0)
    {
        if($pic_id == null)
            return null;

        $pic_small = getThumbImageById($pic_id, 280, 160);
        $pic = M('Picture')->where(array('status' => 1))->field('path')->getById($pic_id);

        if(!is_bool(strpos( $pic['path'],'http://'))){
            $pic_src = $pic['path'];
        }else{
            if($withRoot == 0)
                $pic_src =getRootUrl(). substr( $pic['path'],1);
            else
                $pic_src =getRootUrl(). $pic['path'];
        }
        return $pic_src;
    }

    private function sec2time($sec){
        $sec = round($sec/60);
        if ($sec >= 60){
            $hour = floor($sec/60);
            $min = $sec%60;
            $res = $hour.' 小时 ';
            $min != 0  &&  $res .= $min.' 分';
        }else{
            $res = $sec.' 分钟';
        }
        return $res;
    }
}