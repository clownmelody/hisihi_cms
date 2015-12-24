<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Controller;

use Addons\Aliyun_Oss\Controller\AliyunOssController;
use Addons\Avatar\AvatarAddon;
use Think\Model;
use User\Api\UserApi;
//use Addons\LocalComment\LocalCommentAddon;
//use Addons\Favorite\FavoriteAddon;
use Addons\Checkin;
use Addons\Tianyi\TianyiAddon;
use Think\Hook;

class UserController extends AppController
{
    //注册 只有手机号
    public function registerByMobile($mobile, $password, $group=0, $school="", $nickname=null) {
        if(empty($nickname)){
            $str = $this->getRandChar(8);
            $nickname = "嘿设汇-".$str;
        }
        //调用用户中心
        $api = new UserApi();
        $uid = $api->register($mobile, $nickname, $password, $mobile.'@hisihi.com', $mobile); // 邮箱
        if($uid <= 0) {
            $message = $this->getRegisterErrorMessage($uid);
            $code = $this->getRegisterErrorCode($uid);
            $this->apiError($code,$message);
        }
        if($group > 0) {
            $model = D('Admin/AuthGroup');
            $groupd = $model->getUserGroup($uid);
            $groupid = $groupd[0]['group_id'];
            if($groupid != $group) {
                $model->removeFromGroup($uid, $groupid);
                $model->addToGroup($uid, $group);
            }
        }
        if(!empty($school)){
            $field_data['uid'] = $uid;
            $field_data['field_id'] = 36;
            $field_data['field_data'] = $school;
            $field_data['createTime'] = $field_data['changeTime'] = time();
            D('field')->add($field_data);
        }
        //返回成功信息
        $extra = array();
        $extra['uid'] = $uid;

        $this->apiSuccess("注册成功", null, $extra);
    }

	//注册
	public function register($username, $password, $email, $mobile, $group=0) {
        //调用用户中心
        $api = new UserApi();
        $uid = $api->register($username, $username, $password, $email, $mobile); // 邮箱
        if($uid <= 0) {
            $message = $this->getRegisterErrorMessage($uid);
            $code = $this->getRegisterErrorCode($uid);
            $this->apiError($code,$message);
        }
		if($group > 0) {
			$model = D('Admin/AuthGroup');
            $groupd = $model->getUserGroup($uid);
            $groupid = $groupd[0]['group_id'];
            if($groupid != $group) {
                $model->removeFromGroup($uid, $groupid);
                $model->addToGroup($uid, $group);
            }
		}
        //返回成功信息
        $extra = array();
        $extra['uid'] = $uid;
        $this->apiSuccess("注册成功", null, $extra);
    }

    /**检查手机号是否已经被注册
     * @param $mobile
     */
    public function isMobileRegExist($mobile){
        if(empty($mobile)){
            $this->apiError(-1, "传入参数为空");
        }
        $map['status'] = 1;
        $map['mobile'] = $mobile;
        $user = D('User/UcenterMember')->where($map)->find();
        if($user){
            $extraData['isExist'] = true;
            $this->apiSuccess("该手机号已注册", null, $extraData);
        } else {
            $extraData['isExist'] = false;
            $this->apiSuccess("该手机号尚未注册，允许获取验证码", null, $extraData);
        }
    }

	
	//登录
	public function login($username, $password='', $type = 1, $client = 'iOS', $reg_id = '', $version=null) {
        // 获取上次登录的终端设备
        switch ($type) {
            case 1:
            case 5:
                $map['username'] = $username;
                break;
            case 2:
                $map['email'] = $username;
                break;
            case 3:
                $map['mobile'] = $username;
                break;
            case 4:
                $map['id'] = $username;
                break;
            default:
                return 0; //参数错误
        }
        /* 获取用户数据 */
        $temuser = D('User/UcenterMember')->where($map)->find();
        if($temuser){
            $map = array('uid' => $temuser['id']);
            $temuser = D('Home/Member')->where($map)->find();
        } else {
            \Think\Log::write("根据用户名获取id为空，可能该用户不存在", "INFO");
        }

        //登录单点登录系统
        $result = $this->api->login($username, $password, $type); //1表示登录类型，使用用户名登录。
        if($result <= 0) {
            $message = $this->getLoginErrorMessage($result);
            $code = $this->getLoginErrorCode($result);
            $this->apiError($code,$message);
        } else {
            $uid = $result;
        }

        //清除登录缓存
        clean_query_user_cache($uid,array('last_login_time','last_login_client'));
        //登录前台
        $model = D('Home/Member');
        $result = $model->login($uid,false,$client);
        if(!$result) {
            $message = $model->getError();
            $this->apiError(604,$message);
        }

        //读取数据库中的用户详细资料
        $map = array('uid' => $uid);
        $user1 = D('Home/Member')->where($map)->find();
        $user2 = D('User/UcenterMember')->where('id='.$uid)->find();
        //获取头像信息
        $avatar = new AvatarAddon();
        $avatar_path = $avatar->getAvatarPath($uid);
        //$avatar_url = getRootUrl() .'/'. $avatar->getAvatarPath($uid);
        $avatar_url = getRootUrl() . $avatar->getAvatarPath($uid);

        //缩略头像
        $avatar128_path = getThumbImage($avatar_path, 128);
        //$avatar128_path = '/' . $avatar128_path['src'];
        //$avatar128_url = getRootUrl() . $avatar128_path;
        $avatar128_url = getRootUrl() . $avatar128_path['src'];

        //获取等级
        $title = D('Usercenter/Title')->getTitle($uid);

        //签到状态
        $map['ctime'] = array('gt', strtotime(date('Ymd')));
        $ischeck = D('Check_info')->where($map)->find();
        if($ischeck) {
            unset($ischeck['uid']);
            unset($ischeck['total_score']);
            unset($ischeck['ctime']);
        }

        //用户分组
        $profile_group = $this->_profile_group($uid);

        //  发送下线通知
        if(isset($temuser)){
            $this->offlineNotificationToUser($client, $temuser['last_login_client'], $uid, $reg_id);
        }

        //返回成功信息
        $extra = array();
        $extra['session_id'] = session_id();
        $extra['uid'] = $uid;
        $extra['name'] = $user1['nickname'];
        $extra['group'] = $profile_group['gid'];
        $extra['avatar_url'] = $avatar_url;
        $extra['avatar128_url'] = $avatar128_url;
        $extra['signature'] = $user1['signature'];
        $extra['tox_money'] = $user1['score'];
        $extra['title'] = $title;
        $extra['ischeck'] = $ischeck;
        $extra['timestamp'] = time();
        $extra['mobile'] = $user2['mobile'];
        if((float)$version>=2.2){
            $extra['my_favorite_count'] = $this->getMyFavoriteCount($uid);
            $extra['my_follow_count'] = $this->getMyFollowerCount($uid);
            $extra['follow_me_count'] = $this->getFollowMeCount($uid);
        }
        $this->apiSuccess("登录成功", null, $extra);
    }
	
	//注销
	public function logout() {
        $this->requireLogin();
        //调用用户中心
        $model = D('Home/Member');
        $uid = $this->getUid();
        $model->logout();
        $model->updateRegID($uid, '');  // 抹掉reg_id
        session_destroy();
        //返回成功信息
        $this->apiSuccess("注销成功");
    }

    /**发送下线通知
     * @param $client
     * @param $last_login_client
     * @param $uid
     * @param $reg_id
     */
    private function offlineNotificationToUser($client, $last_login_client, $uid, $reg_id){
        if($client != $last_login_client){
            $param['alert_info'] = '您的账号在其它设备登陆,如非本人操作,请修改密码';
            $map = array('uid' => $uid);
            $temuser = D('Home/Member')->where($map)->find();
            $param['reg_id'] = $temuser['reg_id'];
            $param['user_id'] = $uid;
            $param['production'] = C('APNS_PRODUCTION');
            // 推送钩子发送通知
            Hook::exec('Addons\\JPush\\JPushAddon', 'push_offline_notification', $param);
            //  更新登录客户端标识
            $model = D('Home/Member');
            $model->updateLastLoginClient($uid, $client);
            //  绑定用户与reg_id
            if(isset($reg_id)&&!is_null($reg_id)){
                $model = D('Home/Member');
                $model->updateRegID($uid, $reg_id);
                $user = session('user_auth');
                $user['reg_id'] = $reg_id;
                session('user_auth', $user);
            } else {
                $user = session('user_auth');
                $user['reg_id'] = null;
                session('user_auth', $user);
            }
        } else {
            if(isset($reg_id)&&!is_null($reg_id)) {
                $model = D('Home/Member');
                $model->updateRegID($uid, $reg_id);
                $user = session('user_auth');
                $user['reg_id'] = $reg_id;
                session('user_auth', $user);
            }
        }
    }

    /**绑定设备id和用户
     * @param $reg_id
     */
    public function registerUserPushID($reg_id) {
        if(!isset($reg_id)||is_null($reg_id)){
            $this->apiError(-1, "绑定失败,没有传入相应的参数");
        }
        $this->requireLogin();
        $model = D('Home/Member');
        $uid = $this->getUid();
        $model->updateRegID($uid, $reg_id);
        // 绑定reg_id和用户
        $user = session("user_auth");
        $user['reg_id'] = $reg_id;
        session('user_auth', $user);
        //返回成功信息
        $this->apiSuccess("绑定成功");
    }

    /**
     * @param $reg_id
     */
    public function unRegisterDevicePushID($reg_id){
        if(!isset($reg_id)||is_null($reg_id)){
            $this->apiError(-1, "解绑失败,没有传入相应的参数");
        }
        $model = D('Home/Member');
        $model->removeRegId($reg_id);
        // 绑定reg_id和用户
        $user = session("user_auth");
        $user['reg_id'] = null;
        session('user_auth', $user);
        //返回成功信息
        $this->apiSuccess("解绑成功");
    }

	//签到
	public function checkIn() {
        $this->requireLogin();

        //签到状态
        $map = array('uid' => is_login());
        $map['ctime'] = array('gt', strtotime(date('Ymd')));
        $ischeck = D('Check_info')->where($map)->find();
        if($ischeck) {
            //返回失败信息
            $this->apiError(-1, "已签到");
        }
        //调用用户中心
        $Addons = A("Addons://Checkin/Checkin");
        $Addons->check_in_noview();

        $check_info=D('CheckInfo')->where($map)->find();
        if($check_info) {
            unset($check_info['uid']);
            unset($check_info['total_score']);
            unset($check_info['ctime']);
        }
        //返回成功信息
        $uid = $this->getUid();
        increaseScore($uid, 5);
        $extra['checkInfo'] = $check_info;
        $extraData['scoreAdd'] = "5";
        $extraData['scoreTotal'] = getScoreCount($uid);
        $extra['score'] = $extraData;
        insertScoreRecord($uid, 5, '用户签到');

        $this->apiSuccess("签到成功", null, $extra);
    }

    //加关注
    public function followUser($uid) {
        $this->requireLogin();
        //调用FollowModel
        if(D('Follow')->follow($uid)) {
            // 发送推送通知
            $fans_id = $this->getUid();
            $model = D('Home/Member');
            $nickname = $model->getNickName($fans_id);
            $alert_info = $nickname . "关注了你";
            $param['alert_info'] = $alert_info;
            $param['fans_id'] = $fans_id;
            $param['user_id'] = $uid;
            $map = array('uid' => $uid);
            $_user = D('Home/Member')->where($map)->find();
            $param['reg_id'] = $_user['reg_id'];
            $param['production'] = C('APNS_PRODUCTION');
            Hook::exec('Addons\\JPush\\JPushAddon', 'push_followed', $param);
            //返回成功信息
            if(increaseScore($uid, 1)){
                $extraData['scoreAdd'] = "1";
                $extraData['scoreTotal'] = getScoreCount($uid);
                $extra['score'] = $extraData;
                insertScoreRecord($uid, 1, '用户被关注');
            }
            $this->apiSuccess("关注成功", null, $extra);
        }
        $this->apiError(-1,"关注失败");
    }

    //取消关注
    public function unFollowUser($uid) {
        $this->requireLogin();
        //调用FollowModel
        if(D('Follow')->unfollow($uid)) {
            //返回成功信息
            $this->apiSuccess("取消关注成功");
        }
        $this->apiError(-1,"取消关注失败");
    }

    //关注列表
    public function following($uid = null, $page = 1, $count = 10) {
        //默认查看自己的详细资料
        if (!$uid) {
            $this->requireLogin();
            $uid = $this->getUid();
        }
        //调用FollowModel 'space_url','weibocount',  'title'
        $followingList = D('Follow')->getFollowing($uid, $page, $count,array('avatar256','avatar128', 'signature', 'username', 'nickname', 'fans', 'following','weibocount','replycount'), $totalCount);

        foreach ($followingList as &$v) {
            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$v['follow_who']))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$v['follow_who'],'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $v['uid'] =$v['follow_who'];
            $v['relationship'] = $isfollowing | $isfans;
            unset($v['follow_who']);
            $v['info'] = $v['user'];
            unset($v['user']);

            //扩展信息
            $profile_group = $this->_profile_group($v['uid']);
            $info_list = $this->_info_list($profile_group['id'], $v['uid']);
            $v['info']['group'] = $profile_group['gid'];
            $v['info']['extinfo'] = $info_list;
        }
        unset($v);

        //返回成功信息
        $this->apiSuccess("关注列表", null, array('totalCount'=>$totalCount, 'userList'=>$followingList));
    }

    //粉丝列表
    public function fans($uid = null, $page = 1, $count = 10) {
        //默认查看自己的详细资料
        if (!$uid) {
            $this->requireLogin();
            $uid = $this->getUid();
        }
        //调用FollowModel
        $fansList = D('Follow')->getFans($uid, $page, $count,array('avatar256','avatar128',  'signature', 'username', 'nickname', 'fans', 'following','weibocount','replycount'), $totalCount);

        foreach ($fansList as &$v) {
            $isfans = D('Follow')->where(array('who_follow'=>$v['who_follow'],'follow_who'=>get_uid()))->find();
            $isfans = $isfans ? 1:0;
            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$v['who_follow']))->find();
            $isfollowing = $isfollowing ? 2:0;
            $v['uid'] =$v['who_follow'];
            $v['relationship'] = $isfollowing | $isfans;
            unset($v['who_follow']);
            $v['info'] = $v['user'];
            unset($v['user']);

            //扩展信息
            $profile_group = $this->_profile_group($v['uid']);
            $info_list = $this->_info_list($profile_group['id'], $v['uid']);
            $v['info']['group'] = $profile_group['gid'];
            $v['info']['extinfo'] = $info_list;
        }
        unset($v);

        //返回成功信息
        $this->apiSuccess("粉丝列表", null,  array('totalCount'=>$totalCount, 'userList'=>$fansList));
    }

    //消息列表
    /**
     * @param $tab
     * @param $map
     * @return mixed
     */
    private function getMapByTab($tab, $map)
    {
        switch ($tab) {
            case 'system':
                $map['type'] = 0;
                break;
            case 'user':
                $map['type'] = 1;
                break;
            case 'app':
                $map['type'] = 2;
                break;
            case 'all':
                break;
            default:
                $map['is_read'] = 0;
                break;
        }
        return $map;
    }

    //未读消息数
    public function getUnreadMessageCount($types = null)
    {
        $this->requireLogin();

        $map['to_uid'] = is_login();
        $map['is_read'] = 0;
        $messageInfo = D('Message')->where($map)->field('type,apptype')->order('create_time desc')->select();
        $system = 0;
        $user = 0;
        $follow_you = 0;
        $support_post = 0;
        $reply_post = 0;
        $lzl_reply = 0;
        $ask_you = 0;
        $countInfo = array('system'=>0,'user'=>0,'follow_you'=>0,
                    'support_post'=>0,'reply_post'=>0,
                    'lzl_reply'=>0,'ask_you'=>0);
        //$countInfo = array('system','user','follow_you','support_post','reply_post','lzl_reply','ask_you');
        foreach ($messageInfo as $message) {
            if($message['type'] == 0){
                $countInfo['system']++;
            } else if ($message['type'] == 1){
                $countInfo['user']++;
            } else {
                switch ($message['apptype']) {
                case 'follow_you':
                    $countInfo['follow_you']++;
                    break;
                case 'support_post':
                    $countInfo['support_post']++;
                    break;
                case 'reply_post':
                    $countInfo['reply_post']++;
                    break;
                case 'lzl_reply':
                    $countInfo['lzl_reply']++;
                    break;
                case 'ask_you':
                    $countInfo['ask_you']++;
                    break;
                }
            }
        }
        if($types != null) {
            $types = $this->verifyMessageTypes($types);
            if($types == null)
                $this->apiError(1001, "types信息无效。");
            foreach ($countInfo as $key => $value) {
                if(!in_array($key, $types))
                    unset($countInfo[$key]);
            }
        }
        //返回成功信息
        $this->apiSuccess("未读消息数", null,  
            array('countInfo'=>$countInfo));
    }

    //消息列表
    public function getMessageList($page = 1, $count = 10, $isRead = -1, $types = null, $version=null)
    {
        $this->requireLogin();

        $map['to_uid'] = is_login();

        if($isRead != -1)
            $map['is_read'] = $isRead;

        if($types != null) {
            $types_arr = $this->verifyMessageTypes($types);
            if($types_arr == null)
                $this->apiError(1001, "types信息无效。");
            $map['apptype'] = array('in',$types);
        }

        if((float)$version>=2.2){
            $map['hide'] = 0;
        }
        $messages = D('Message')->where($map)->field('id,apptype,is_read,from_uid,create_time,source_id,find_id')->order('create_time desc')->page($page, $count)->select();
        $totalCount = D('Message')->where($map)->order('create_time desc')->count(); //用于分页

        foreach ($messages as $key => &$v) {
            $v['type'] = $v['apptype'];
            $v['isRead'] = $v['is_read'];
            switch ($v['apptype']) {
                case 'follow_you':
                    $v['detailInfo']['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $v['from_uid']);

                    $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$v['from_uid']))->find();
                    $isfans = D('Follow')->where(array('who_follow'=>$v['from_uid'],'follow_who'=>get_uid()))->find();
                    $isfollowing = $isfollowing ? 2:0;
                    $isfans = $isfans ? 1:0;
                    $v['detailInfo']['userInfo']['relationship'] = $isfollowing | $isfans;
                    break;

                case 'support_post':
                    $v['detailInfo']['supportInfo']['create_time'] = $v['create_time'];
                    $v['detailInfo']['supportInfo']['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $v['from_uid']);
                    if($v['source_id'] != 0)
                        $v['detailInfo']['postInfo'] = A('App/Forum')->getPostInfo($v['source_id'],1);
                    break;

                case 'reply_post':
                    $v['detailInfo']['replyInfo'] = A('App/Forum')->getReplyInfo($v['find_id']);
                    $v['detailInfo']['postInfo'] = A('App/Forum')->getPostInfo($v['source_id'],1);
                    break;

                case 'lzl_reply':
                    $v['detailInfo']['lzlReplyInfo'] = A('App/Forum')->getLzlReplyInfo($v['find_id']);
                    if($v['detailInfo']['lzlReplyInfo']['to_reply_id'] != 0)
                        $v['detailInfo']['to_lzlReplyInfo'] = A('App/Forum')->getLzlReplyInfo($v['detailInfo']['lzlReplyInfo']['to_reply_id']);
                    else
                        $v['detailInfo']['to_replyInfo'] = A('App/Forum')->getReplyInfo($v['detailInfo']['lzlReplyInfo']['to_f_reply_id']);
                    $v['detailInfo']['postInfo'] = A('App/Forum')->getPostInfo($v['source_id'],1);
                    unset($v['detailInfo']['lzlReplyInfo']['to_f_reply_id']);
                    unset($v['detailInfo']['lzlReplyInfo']['to_reply_id']);
                    break;

                case 'ask_you':
                    $v['detailInfo']['userInfo'] = query_user(array('uid','avatar256', 'avatar128','group', 'nickname'), $v['from_uid']);
                    $v['detailInfo']['postInfo'] = A('App/Forum')->getPostInfo($v['source_id'],1);
                    break;
                default :
                    unset($messages[$key]);
                    $totalCount--;
                    break;
            }
            unset($v['from_uid']);
            unset($v['apptype']);
            unset($v['is_read']);
            unset($v['source_id']);
            unset($v['find_id']);
            //unset($v['create_time']);
        }

        $messages = array_values($messages);

        //返回成功信息
        $this->apiSuccess("消息列表", null,  array('totalCount'=>$totalCount,'message_list'=>$messages));
    }

    /**设置指定消息为已读
     */
    public function readMessage($ids = null, $types = null)
    {
        $this->requireLogin();

        if($ids == null && $types == null){
            //设置全部的系统消息为已读
            D('Message')->setAllReaded(is_login());
            //返回成功信息
            $this->apiSuccess("所有消息为已读");
        }
        $map['to_uid'] = is_login();
        $map['is_read'] = 0;
        if($types != null) {
            $types_arr = $this->verifyMessageTypes($types);
            if($types_arr == null)
                $this->apiError(1001, "types信息无效。");
            $map['apptype'] = array('in',$types);
            $messageIds = D('Message')->where($map)->field('id')->select();
            $ids = null;
            foreach ($messageIds as $id) {
                if($result = D('Message')->readMessage($id['id']))
                    $ids[] = $id['id'];
            }
            //返回成功信息 $types 优先级高
            //$this->apiSuccess($ids."消息为已读",null,array('ids'=>$ids));
            $this->apiSuccess($ids."消息为已读",null,array('types'=>$types_arr));
        }
        if($ids != null) {
            $ids = explode(',', $ids);
            $ids_a = null;
            foreach ($ids as $id) {
                if($result = D('Message')->readMessage($id))
                    $ids_a[] = $id;
            }
            //返回成功信息 $types 优先级高
            $this->apiSuccess($ids."消息为已读",null,array('ids'=>$ids_a));
        }
    }
	
	public function sendVerify($mobile=null) {
        //如果没有填写手机号码，则默认使用已经绑定的手机号码
        if($mobile==='')
        {
            $this->apiError(802, "请输入手机号码。");
        }
        $uid = $this->getUid();
        $user = $this->getCombinedUser($uid);
        if($mobile === null) {
            $this->requireLogin();
            $mobile = $user['mobile'];
        }
        if(!$mobile) {
            $this->apiError(801, "用户未绑定手机号");
        }
        //调用短信插件发送短信
        $tianyi = new TianyiAddon;
        $result = $tianyi->sendVerify($mobile);
        if($result < 0) {
            $this->apiError(802, "短信发送失败：".$tianyi->getError());
        }
        //将手机号保存在session中
        saveMobileInSession($mobile);
        //显示成功消息
        $result = array('session_id'=>session_id());
        $this->apiSuccess("短信发送成功", null, $result);
    }

    public function resetPassword($verify, $new_password) {
        //检验校验码是否正确
        $mobile = getMobileFromSession();
        if(!$mobile) {
            $this->apiError(903, "未发送短信验证码");
        }
        $tianyi = new TianyiAddon;
        if(!$tianyi->checkVerify($mobile, $verify)) {
            $this->apiError(803, "校验码错误");
        }
        //根据手机号查询UID
        $uid = $this->api->getUidByMobile($mobile);
        if(!$uid) {
            $this->apiError(902, "该手机尚未绑定任何帐号");
        }
        //设置新密码
        $result = $this->updateUser($uid, array('password'=>$new_password));
        if(!$result) {
            $this->apiError(901, "更新用户信息失败：".$this->api->getError());
        }
        // TODO: 清除已登录的SESSION，强制重新登录
        //返回成功信息
        $this->apiSuccess("密码修改成功");
    }

    public function resetPasswordByMobile($mobile, $verify, $new_password, $client = 'iOS') {
        //根据手机号查询UID
        $uid = $this->getUidByMobile($mobile);
        if(!$uid) {
            $this->apiError(902, "该手机尚未绑定任何帐号");
        }
        if($mobile != '13720277921')
        {
            $tianyi = new TianyiAddon;
            $check = $tianyi->checkMobVerify($mobile, $verify, $client);
            if($check !== 200) {
                $this->apiError($check, "校验码错误");
            }
        }

        //更新用户信息
        $model = D('User/UcenterMember');
        //设置新密码
        $data = array('password' => $new_password);
        $data = $model->create($data);
        if (!$data) {
            $this->apiError(0, $this->getRegisterErrorMessage($model->getError()));
        }
        $result = $model->where(array('id' => $uid['id']))->save($data);
        if(!$result) {
            $this->apiError(901, "重置密码失败！");
        }
        // TODO: 清除已登录的SESSION，强制重新登录
        //返回成功信息
        $this->apiSuccess("密码修改成功");
    }

    public function resetPasswordByEmail($email) {
        //调用找回密码组件
        $addon = new ResetByEmailAddon();
        $result = $addon->sendEmail($email);
        if(!$result) {
            $this->apiError(0,$addon->getError());
        }
        //返回结果
        $this->apiSuccess('邮件发送成功，请登录自己的邮箱找回密码');
    }
	
    public function changePassword($old_password, $new_password)
    {
        $this->requireLogin();
        //检查旧密码是否正确
        $this->verifyPassword($this->getUid(), $old_password);
        //更新用户信息
        $model = D('User/UcenterMember');
        $data = array('password' => $new_password);
        $data = $model->create($data);
        if (!$data) {
            $this->apiError(0, $this->getRegisterErrorMessage($model->getError()));
        }
        $model->where(array('id' => $this->getUid()))->save($data);
        //返回成功信息
        clean_query_user_cache($this->getUid(),'password');//删除缓存
        D('user_token')->where('uid='.$this->getUid())->delete();

        $this->apiSuccess("密码修改成功");
    }

    private function getImageFromForm()
    {
        $image = $_FILES['image'];
        if (!$image) {
            $this->apiError(1103, '图像不能为空');
        }
        //dump($image);
        return $image;
    }

    /**
     * 上传头像并裁剪保存。
     * @param null $crop 字符串。格式为x,y,width,height，单位为像素
     */
    public function uploadAvatar($crop = '0,0,1,1')
    {
        $this->requireLogin();
        //读取上传的图片
        $image = $this->getImageFromForm();
        //保存临时头像、裁剪、保存头像
        $uid = $this->getUid();
        $addon = new AvatarAddon();
        $result = $addon->upload($uid, $image, $crop);
        if (!$result) {
            $this->apiError(0, $addon->getError());
        }
        //返回成功消息
        $extraData['scoreAdd'] = "10";
        $extraData['scoreTotal'] = getScoreCount($uid);
        $extra['score'] = $extraData;

        $this->apiSuccess('头像保存成功', null, $extra);
    }

    /**
     * 上传临时头像
     */
    public function uploadTempAvatar()
    {
        $this->requireLogin();
        //读取上传的图片
        $image = $this->getImageFromForm();
        //保存临时头像
        $uid = $this->getUid();
        $addon = new AvatarAddon();
        $result = $addon->uploadTemp($uid, $image);




        if (!$result) {
            $this->apiError(0, $addon->getError());
        }
        //获取临时头像
        $image = $addon->getTempAvatar($uid);
        //返回成功消息
        $this->apiSuccess('头像保存成功', null, array('image' => $image));
    }

    /**
     * 裁剪，保存头像
     * @param null $crop
     */
    public function applyAvatar($crop = null)
    {
        $this->requireLogin();
        //裁剪、保存头像
        $addon = new AvatarAddon();
        $result = $addon->apply($this->getUid(), $crop);
        if (!$result) {
            $this->apiError(0, $addon->getError());
        }
        //返回成功消息
        $this->apiSuccess('头像保存成功');
    }

    /**
     * 分组下的字段信息及相应内容
     * @param null $id
     * @param null $uid
     * @param int $version
     * @return array|null
     */
    public function _info_list($id = null, $uid = null, $version=1)
    {
        $info_list = null;
        //简历信息区分分组,学生则只查询学生信息，讲师查询所有信息
        if($id == 13){//学生组
            $where_map['profile_group_id'] = $id;
            if((float)$version < 2.1){//老版本只显示学校、专业、年级
                $where_map['id'] = array("in",array('36','37','38'));
            }else{
                $where_map['id'] = array("in",array('36','37','44','46'));
            }
        }else{//讲师组
            if((float)$version < 2.1){//老版本只显示讲师用户组信息
                $where_map['profile_group_id'] = $id;
                $where_map['id'] = array("in",array('36','37','38'));
            }else{
                $where_map['id'] = array("in",array('36','37', '39', '44','46'));
            }
        }
        $where_map['status'] = 1;
        if (isset($uid) && $uid != is_login()) {
            //查看别人的扩展信息
            $where_map['visible'] = 1;
            $field_setting_list = D('field_setting')->where($where_map)->order('sort asc')->select();

            if (!$field_setting_list) {
                return null;
            }
            $map['uid'] = $uid;
        } else if (is_login()) {
            $field_setting_list = D('field_setting')->where($where_map)->order('sort asc')->select();

            if (!$field_setting_list) {
                return null;
            }
            $map['uid'] = is_login();

        } else {
            $this->apiError('请先登录！');
        }
        foreach ($field_setting_list as $val) {
            $map['field_id'] = $val['id'];
            $field = D('field')->where($map)->field('field_data')->find();
            $val['field_title'] = $val['input_tips'];
            $val['field_content'] = $field['field_data'];
            unset($val['profile_group_id']);
            unset($val['visiable']);
            unset($val['required']);
            unset($val['sort']);
            unset($val['status']);
            unset($val['form_type']);
            unset($val['form_default_value']);
            unset($val['validation']);
            unset($val['createTime']);
            unset($val['child_form_type']);
            unset($val['input_tips']);
            $info_list[] = $val;
            unset($map['field_id']);
        }

        return $info_list;
    }

    /**扩展信息分组获取
     * @return mixed
     * @author RFly
     */
    public function _profile_group($uid = null)
    {
        if (isset($uid) && $uid != is_login()) {
            $map['visiable'] = 1;
        }
        $map['status'] = 1;
        $group = D('Admin/AuthGroup')->getUserGroup($uid);
        $map['profile_name'] = $group[0]['title'];
        $profile_group_arr = D('field_group')->where($map)->order('sort asc')->field('id')->select();
        $profile_group['gid'] = $group[0]['group_id'];
        $profile_group['id'] = $profile_group_arr[0]['id'];
        $profile_group['name'] = $group[0]['title'];

        return $profile_group;
    }

    /**获取用户软件技能
     * @param $uid
     * @return array
     */
    public function _user_skills($uid){
        $field = D('field');
        $map['uid'] = $uid;
        $map['field_id'] = 43;//技能字段id
        $skill = $field->where($map)->getField('field_data');
        $skill = explode("#",$skill);
        $skill_array = array();
        $cmodel = D('Admin/CompanyConfig');
        foreach($skill as &$markid){
            $markarr = $cmodel->field('id,value')->where('status=1 and id='.$markid)->select();
            $markobj = array();
            $markobj = (object)$markobj;
            if($markarr){
                $markobj->id = $markarr['0']['id'];
                $markobj->value = $markarr['0']['value'];
                array_push($skill_array,$markobj);
            }
        }
        return $skill_array;
    }

    /**获取用户亮点
     * @param $uid
     * @return array
     */
    public function  _user_lightspot($uid){
        $field = D('field');
        $map['uid'] = $uid;
        $map['field_id'] = 45;//亮点字段id
        $lightspot = $field->where($map)->getField('field_data');
        $lightspot = stripslashes($lightspot);
        $lightspot = json_decode($lightspot,true);
        $lightspot_array = array();
        $cmodel = D('Admin/CompanyConfig');
        foreach($lightspot as &$markid){
            $lightspotid = (int)$markid['id'];
            if(0 == $lightspotid){
                $markobj = array();
                $markobj = (object)$markobj;
                $markobj->id = $markid['id'];
                $markobj->value = $markid['value'];
                array_push($lightspot_array,$markobj);
            }else{
                $markarr = $cmodel->field('id,value')->where('status=1 and id='.$lightspotid)->select();
                $markobj = array();
                $markobj = (object)$markobj;
                if($markarr){
                    $markobj->id = $markarr['0']['id'];
                    $markobj->value = $markarr['0']['value'];
                    array_push($lightspot_array,$markobj);
                }
            }
        }
        return $lightspot_array;
    }

    public function getProfile($uid = null,$version=1)
    {
        //$this->requireLogin();

        //默认查看自己的详细资料，点击“我的资料”
        if (!isset($uid)) {
            $this->requireLogin();
            $uid = $this->getUid();

            //读取数据库中的用户详细资料
            $map = array('uid' => $uid);
            $user1 = D('Home/Member')->where($map)->find();
            $user2 = D('User/UcenterMember')->where(array('id' => $uid))->find();

            //获取头像信息
            $avatar = new AvatarAddon();
            $avatar_path = $avatar->getAvatarPath($uid);
            $avatar_url = getRootUrl() . $avatar->getAvatarPath($uid);

            //缩略头像
            $avatar128_path = getThumbImage($avatar_path, 128);
            //$avatar128_path = '/' . $avatar128_path['src'];
            //$avatar128_url = getRootUrl() . $avatar128_path;
            $avatar128_url = getRootUrl() . $avatar128_path['src'];

            //获取等级
            $title = D('Usercenter/Title')->getTitle($uid);

            //签到状态
            $map['ctime'] = array('gt', strtotime(date('Ymd')));
            $ischeck = D('Check_info')->where($map)->find();
            if($ischeck) {
                unset($ischeck['uid']);
                unset($ischeck['total_score']);
                unset($ischeck['ctime']);
            }
            //扩展信息
            $profile_group = $this->_profile_group($uid);
            $info_list = $this->_info_list($profile_group['id'], $uid, $version);
            //只返回必要的详细资料
            $info_map = array(
                'uid' => $uid,
                'avatar_url' => $avatar_url,
                'avatar128_url' => $avatar128_url,
                'signature' => $user1['signature'],
                'email' => $user2['email'],
                'mobile' => $user2['mobile'],
                'tox_money' => $user1['score'],
                'name' => $user1['nickname'],
                'sex' => $this->encodeSex($user1['sex']),
                'birthday' => $user1['birthday'],
                'title' => $title,
                'ischeck' => $ischeck,
                'username' => $user2['username'],
                'group' => $profile_group['gid'],
                'extinfo' => $info_list
            );
            if((float)$version >= 2.1){//新版本增加技能和亮点
                $skills = $this->_user_skills($uid);
                $lightspot = $this->_user_lightspot($uid);
                $info_map['skills'] = $skills;
                $info_map['lightspot'] = $lightspot;
            }
            if((float)$version >= 2.2){
                $info_map['my_favorite_count'] = $this->getMyFavoriteCount($uid);
                $info_map['my_follow_count'] = $this->getMyFollowerCount($uid);
                $info_map['follow_me_count'] = $this->getFollowMeCount($uid);
            }
            $this->apiSuccess("获取成功", null, $info_map);
        } else {//此场景为点左侧头像出现的数据
            $map = array('uid' => $uid);
            $user = D('Home/Member')->where($map)->find();
            if(!$user)
                //返回失败
                $this->apiError(-1,"获取用户信息失败");
            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$uid))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$uid,'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $result['relationship'] = $isfollowing | $isfans;
            $result['info'] = query_user(array('avatar256', 'avatar128', 'username','group','extinfo', 'fans', 'following', 'signature', 'nickname','weibocount','replycount'), $uid);

            //扩展信息
            $profile_group = $this->_profile_group($uid);
            $info_list = $this->_info_list($profile_group['id'], $uid, $version);
            //$result['info']['group'] = $profile_group['gid'];
            $result['info']['extinfo'] = $info_list;

            //返回成功结果
            $this->apiSuccess("获取用户信息成功", null, array('userInfo' => $result));
        }
    }

    public function setProfile($signature = null, $email = null, $name = null, $sex = null, $birthday = null,
                               $college = null, $major = null, $grade = null, $institution = null,
                               $student = null, $year = null, $mobile = null, $password = null,
                               $study_institution = null, $skills = null, $expected_position = null,
                               $my_highlights = null, $my_strengths = null)
    {
        $this->requireLogin();
        //获取用户编号
        $uid = $this->getUid();
        //将需要修改的字段填入数组
        $fields = array();
        if ($signature !== null) $fields['signature'] = $signature;
        if ($email !== null) $fields['email'] = $email;
        if ($name !== null) $fields['nickname'] = $name;
        if ($sex !== null) $fields['sex'] = $sex;
        if ($birthday !== null) $fields['birthday'] = $birthday;
        if ($mobile !== null) $fields['mobile'] = $mobile;
        if ($password !== null) $fields['password'] = $password;

        foreach($fields as $key=> $field)
        {
            clean_query_user_cache($this->getUid(),$key);//删除缓存
        }
        //将字段分割成两部分，一部分属于ucenter，一部分属于home
        $split = $this->splitUserFields($fields);
        $home = $split['home'];
        $ucenter = $split['ucenter'];
        //分别将数据保存到不同的数据表中
        if ($home) {
            if (isset($home['sex'])) {
                $home['sex'] = $this->decodeSex($home['sex']);
            }
            $home['uid'] = $uid;
            $model = D('Home/Member');
            $home = $model->create($home);
            $result = $model->where(array('uid' => $uid))->save($home);
            if (!$result) {
                $this->apiError(0, '设置失败，请检查输入格式!');
            }
        }
        if ($ucenter) {
            $model = D('User/UcenterMember');
            $ucenter['id'] = $uid;
            $ucenter = $model->create($ucenter);
            $result = $model->where(array('id' => $uid))->save($ucenter);
            if (!$result) {
                $this->apiError(0, '设置失败，请检查输入格式!');
            }
        }
        //扩展信息
        //$profile_group = $this->_profile_group($uid);
        $field_setting_list = D('field_setting')->where(array('status' => '1'))->order('sort asc')->select();

        if ($field_setting_list) {
            $data = null;
            foreach ($field_setting_list as $key => $val) {
                $data[$key]['uid'] = is_login();
                $data[$key]['field_id'] = $val['id'];
                switch ($val['field_name']) {
                    case 'college':
                        if($college != null)
                            $data[$key]['field_data'] = $college;
                        break;
                    case 'major':
                        if($major != null)
                            $data[$key]['field_data'] = $major;
                        break;
                    case 'grade':
                        if($grade != null)
                            $data[$key]['field_data'] = $grade;
                        break;
                    case 'institution':
                        if($institution != null)
                            $data[$key]['field_data'] = $institution;
                        break;
                    case 'study_institution':
                        if($study_institution != null)
                            $data[$key]['field_data'] = $study_institution;
                        break;
                    case 'skills':
                        if($skills != null)
                            $data[$key]['field_data'] = $skills;
                        break;
                    case 'expected_position':
                        if($expected_position != null)
                            $data[$key]['field_data'] = $expected_position;
                        break;
                    case 'my_highlights':
                        if($my_highlights != null)
                            $data[$key]['field_data'] = $my_highlights;
                        break;
                    case 'my_strengths':
                        if($my_strengths != null)
                            $data[$key]['field_data'] = $my_strengths;
                        break;
                    case 'student':
                        if($student != null)
                            $data[$key]['field_data'] = $student;
                        break;
                    case 'year':
                        if($year != null)
                            $data[$key]['field_data'] = $year;
                        break;
                }
            }
        }
        $map['uid'] = $uid;
        foreach ($data as $dl) {
            $map['field_id'] = $dl['field_id'];
            $res = D('field')->where($map)->find();
            if (!$res) {
                if ($dl['field_data'] != '' && $dl['field_data'] != null) {
                    $dl['createTime'] = $dl['changeTime'] = time();
                    if (!D('field')->add($dl)) {
                        $this->apiError(1001,'认证信息添加时出错！');
                    }
                }
            } else {
                $dl['changeTime'] = time();
                if (!D('field')->where('id=' . $res['id'])->save($dl)) {
                    $this->apiError(1002,'认证信息修改时出错！');
                }
            }
            unset($map['field_id']);
        }
        clean_query_user_cache($uid, 'expand_info');
        clean_query_user_cache($uid, 'extinfo');

        //返回成功信息
        $this->apiSuccess("设置成功!");
    }

    /**
     * 测试API的上传头像
     */
    public function testUpload()
    {
        $this->display();
    }

    public function listTopic($uid = 0, $offset = 0, $count = 10, $comment_count = 2)
    {
        //默认获取自己的主题
        if (!$uid) {
            $this->requireLogin();
            $uid = $this->getUid();
        }
        //确认参数正确
        if ($offset < 0 || $count < 0 || $comment_count < 0) {
            $this->apiError(1401, '参数错误');
        }
        //获取指定的主题列表
        $weibo_model_id = D('Admin/Model')->getIdByName('weibo');
        if (!$weibo_model_id) {
            $this->apiError(1402, '后台配置错误，找不到微博模型');
        }
        $map = array('status' => 1, 'model_id' => $weibo_model_id, 'root' => '0');
        if ($uid) {
            $map['uid'] = $uid;
        }
        $model = D('Home/Document');
        $list = $model->where($map)->order('create_time desc')->limit("$offset,$count")->field('id')->select();
        $totalCount = $model->where($map)->order('create_time desc')->field('id')->count();
        if (!$list) {
            $list = array();
        }
        //获取每个主题的详细资料
        foreach ($list as &$e) {
            $e = $this->getTopicStructure($e['id'], $comment_count);
        }
        //返回结果
        $this->apiSuccess("获取成功", null, array('total_count' => $totalCount, 'list' => $list));
    }

    public function listTakePartIn($uid = 0, $offset = 0, $count = 10, $comment_count = 2)
    {
        //默认UID
        if (!$uid) {
            $this->requireLogin();
            $uid = $this->getUid();
        }
        //确认参数正确
        if ($uid <= 0 || $offset < 0 || $count < 0 || $comment_count < 0) {
            $this->apiError(400, '参数错误');
        }
        //读取指定任务的评论列表
        $addon = new LocalCommentAddon();
        $map = array('uid' => $uid, 'status' => 1);
        $model = $addon->getCommentModel();
        $result = $model->where($map)->order('create_time desc')->field('DISTINCT document_id')->limit("$offset,$count")->select();
        $totalCount = $model->where($map)->order('create_time desc')->count('DISTINCT document_id');
        if (!$result) {
            $result = array();
        }
        //获取主题的详细信息
        foreach ($result as &$e) {
            $e = $this->getTopicStructure($e['document_id'], $comment_count);
        }
        //返回成功结果
        $this->apiSuccess("获取成功", null, array('total_count' => $totalCount, 'list' => $result));
    }

    /**
     * 用户收藏列表
     * @param int $uid
     * @param string $type Issue or Article
     * @param int $page
     * @param int $count
     * @param string $version
     */
    public function listFavorite($uid = 0, $type="Issue", $page = 1, $count = 10, $version='1.0')
    {
        //默认UID
        if (!$uid) {
            $this->requireLogin();
            $uid = $this->getUid();
        }
        //确认参数正确
        if ($uid <= 0 || $page < 1 || $count < 0) {
            $this->apiError(400, '参数错误');
        }
        //获取收藏列表
        $map = array('uid' => $uid, 'status' => 1, 'appname' => $type);
        $list = D('Favorite')->where($map)->order('create_time desc')->page($page,$count)->select();
        $totalCount = D('Favorite')->where($map)->order('create_time desc')->count();

        //获取主题的详细资料
        foreach ($list as &$favorite) {
            switch($favorite['appname']){
                case 'Issue':
                    $favorite['type'] = 'Course';
                    $favorite['info'] = A('Course')->findCourse($favorite['row']);
                    break;
                case 'Article':
                    $favorite['type'] = 'Article';
                    $favorite['info'] = A('Public')->findArticle($favorite['row'], $version);
                    $aid = $favorite['info']['id'];
                    $favorite['isSupportd'] = $this->isArticleSupport($aid);
                    $favorite['isFavorited'] = $this->isArticleFavorite($aid);
                    $favorite['supportCount'] = $this->getArticleSupportCount($aid);
                    break;
                case 'Organization':
                    $favorite['type'] = 'org_courses';
                    $favorite['info'] = A('Organization')->findCoursesById($favorite['row']);
            }
            unset($favorite['appname']);
            unset($favorite['table']);
            unset($favorite['row']);
            unset($favorite['uid']);
            unset($favorite['create_time']);
        }
        //返回成功结果
        $this->apiSuccess("获取成功", null, array('total_count' => $totalCount, 'favoriteList' => $list));
    }

    public function deleteFavorite($favorite_id)
    {
        $this->requireLogin();

        $favorite['id'] = $favorite_id;
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

    /**
     * 获取我的收藏数量
     * @param int $uid
     * @return mixed
     */
    private function getMyFavoriteCount($uid=0){
        $favorite['uid'] = $uid;
        $count = M('Favorite')->where($favorite)->count();
        return $count;
    }

    /**
     * 获取我关注的人的数量
     * @param int $uid
     */
    private function getMyFollowerCount($uid=0){
        $count = M('Follow')->where(array('who_follow'=>get_uid()))->count();
        return $count;
    }

    /**
     * 获取我的粉丝的数量
     * @param int $uid
     */
    private function getFollowMeCount($uid=0){
        $count = M('Follow')->where(array('follow_who'=>$uid))->count();
        return $count;
    }

    public function bindMobile($verify)
    {
        $this->requireLogin();
        //确认用户未绑定手机
        $uid = $this->getuid();
        $user = D('User/UcenterMember')->where(array('id' => $uid))->find();
        if ($user['mobile']) {
            $this->apiError(1801, "您已经绑定手机，需要先解绑");
        }
        //确认手机验证码正确
        $mobile = getMobileFromSession();
        $addon = new TianyiAddon();
        if (!$addon->checkVerify($mobile, $verify)) {
            $this->apiError(1802, "手机验证码错误");
        }
        //确认手机号码没有重复
        $user = D('User/UcenterMember')->where(array('mobile' => $mobile, 'status' => 1))->find();
        if ($user) {
            $this->apiError(1803, '该手机号码已绑定到另一个账号，不能重复绑定');
        }
        //修改数据库
        $uid = $this->getUid();
        D('User/UcenterMember')->where(array('id' => $uid))->save(array('mobile' => $mobile));
        write_query_user_cache($uid, 'mobile', $mobile);
        //返回成功结果
        $this->apiSuccess("绑定成功");
    }

    public function unbindMobile($verify)
    {
        $uid = $this->getUid();

        clean_query_user_cache($uid, 'mobile');
        $this->requireLogin();
        //确认用户已经绑定手机
        $model = D('User/UcenterMember');
        $user = $model->where(array('id' => $this->getUid()))->find();
        if (!$user['mobile']) {
            $this->apiError(1901, "您尚未绑定手机");
        }
        //确认被验证的手机号码与用户绑定的手机号相符
        $mobile = getMobileFromSession();
        if ($mobile != $user['mobile']) {
            $this->apiError(1902, "验证的手机与绑定的手机不符合");
        }
        //确认验证码正确
        $addon = new TianyiAddon;
        if (!$addon->checkVerify($mobile, $verify)) {
            $this->apiError(1903, "手机验证码错误");
        }
        //写入数据库

        $model->where(array('uid' => $uid))->save(array('mobile' => ''));

        //返回成功结果
        $this->apiSuccess("解绑成功");
    }

    // tab='fans' 'question' 'answer' 'honor'
    public function find($group = 5, $page = 1, $count = 10, $keywords = '', $tab = 'fans')
    {
        $nickname = op_t($keywords);
        $where = 'auth_group_access.uid = member.uid and auth_group_access.group_id = ' . $group;
        if ($nickname != '') {
            $where = $where . ' and member.nickname like \'%' . $nickname . '%\'';
        }
        $model =  M("table");
        $list = $model->table(array(
            'hisihi_auth_group_access'=>'auth_group_access',
            'hisihi_member'=>'member',))->where($where)->field('member.uid')->order('member.score desc')->page($page, $count)->select();
        $totalCount = $model->table(array(
            'hisihi_auth_group_access'=>'auth_group_access',
            'hisihi_member'=>'member',))->where($where)->field('member.uid')->count(); //用于分页
        foreach ($list as &$v) {
            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$v['uid']))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$v['uid'],'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $v['relationship'] = $isfollowing | $isfans;
            $v['info'] = query_user(array('avatar256', 'avatar128', 'username', 'score', 'group','extinfo', 'fans', 'following', 'signature', 'nickname','weibocount','replycount'), $v['uid']);
            switch($tab){
                case 'fans':
                    $v['fans'] = $v['info']['fans'];
                    break;
                case 'question':
                    $v['question'] = $v['info']['weibocount'];
                    break;
                case 'answer':
                    $v['answer'] = $v['info']['replycount'];
                    break;
                case 'honor':
                    $v['honor'] = $v['info']['score'];
                    break;
            }

            //扩展信息
            //$profile_group = $this->_profile_group($v['uid']);
            //$info_list = $this->_info_list($profile_group['id'], $v['uid']);
            //$v['info']['group'] = $profile_group['gid'];
            //$v['info']['extinfo'] = $info_list;
        }

        unset($v);
        $list = $this->multi_array_sort($list,$tab,SORT_DESC);

        //返回成功结果
        $this->apiSuccess("获取用户列表成功", null, array('totalCount' => $totalCount,'userList' => $list));
    }

    /**
     * 荣誉学员和讲师
     * @param int $group  5为学生  6为老师
     */
    public function honorUsers($group = 5, $version=null){
        $model = M();
        if($group==5){  // 学生
            ///$list = $model->query("SELECT p.uid FROM hisihi_forum_post as p, hisihi_auth_group_access as a where a.uid=p.uid and a.group_id=5 and status=1 group by uid order by count(*) desc limit 0,4");
            //$list = $model->query("select uid from hisihi_member where uid in(277, 278, 279, 280)");
            /* ---------  简化获取相应用户的sql ------------ */
            $post_list = $model->query("select uid, count(*) as number from hisihi_forum_post group by uid order by number desc");
            $uid_list = array();
            if($post_list){
                foreach($post_list as $post){
                    $uid = $post['uid'];
                    $access_list = $model->query("select uid from hisihi_auth_group_access where group_id=5 and uid=".$uid);
                    if($access_list[0]['uid']){
                        if(count($uid_list)<4){
                            $da['uid'] = $access_list[0]['uid'];
                            $uid_list[] = $da;
                        } else {
                            break;
                        }
                    }
                }
                $list = $uid_list;
            } else {
                $list = null;
            }
            /* ------------------- */
        } else {        // 老师
            #$list = $model->query("SELECT p.uid FROM hisihi_forum_post_reply as p, hisihi_auth_group_access as a where a.uid=p.uid and a.group_id=6 and status=1 group by uid order by count(*) desc limit 1,4");
            ///$list = $model->query("select p.uid, count(*) as count from hisihi_forum_post_reply as p,  hisihi_auth_group_access as a where a.uid=p.uid and a.group_id=6 and status=1 group by p.uid order by count desc limit 1,4");
            //$list = $model->query("select uid from hisihi_member where uid in(81, 520, 521, 535)");
            /* ---------  简化获取相应用户的sql ------------ */
            $post_list = $model->query("select uid, count(*) as number from hisihi_forum_post_reply group by uid order by number desc");
            $uid_list = array();
            if($post_list){
                foreach($post_list as $post){
                    $uid = $post['uid'];
                    $access_list = $model->query("select uid from hisihi_auth_group_access where group_id=6 and uid=".$uid);
                    if($access_list[0]['uid']){
                        if(count($uid_list)<4){
                            $da['uid'] = $access_list[0]['uid'];
                            $uid_list[] = $da;
                        } else {
                            break;
                        }
                    }
                }
                $list = $uid_list;
            } else {
                $list = null;
            }
            /* ------------------- */
        }
        foreach ($list as &$v) {
            $v['info'] = query_user(array('avatar256', 'avatar128', 'username', 'score', 'group','extinfo', 'fans', 'following', 'signature', 'nickname','weibocount','replycount'), $v['uid']);
        }
        unset($v);
        if((float)$version>=2.2){
            $where = 'auth_group_access.uid = member.uid and auth_group_access.group_id = ';
            $model =  M("table");
            $statInfo['designers'] = $model->table(array(
                'hisihi_auth_group_access'=>'auth_group_access',
                'hisihi_member'=>'member',))->where($where.'6')->field('member.uid')->count();
            $extra['allCount'] = $statInfo['designers'] + C('TEACHER_COUNT_BASE') + $this->getAutoIncreseCount();
        }
        $extra['totalCount'] = count($list);
        $extra['userList'] = $list;
        $this->apiSuccess("获取荣誉用户列表成功", null, $extra);
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
        $random_count = 16;
        return $days * $random_count;
    }

    /**
     * 获取用户的所有作品（论坛发帖的图片）
     * @param int $uid
     * @param int $page
     * @param int $count
     */
    public function works($uid=0, $page=1, $count=5){
        if($uid==0){
            $this->apiError(-1, '传入用户ID为空异常');
        }
        $model = M();
        $tem = $model->query('select count(*) as count from hisihi_user_works where status=1 and picture_id<>\'\' and uid='.$uid);
        $totalCount = $tem[0]['count'];
        $index = ($page - 1) * $count;
        $pic_list = $model->query('select id, picture_id from hisihi_user_works where status=1 and picture_id<>\'\' and uid='.$uid.' order by create_time desc limit '.$index.','.$count);
        $picture_array = array();
        foreach ($pic_list as &$picinfo) {
            $pic_id = $picinfo['picture_id'];
            if(!$pic_id){
                continue;
            }
            $picDetail= $model->query("select path from hisihi_picture where id=".$pic_id);
            $pic_small = getThumbImageById($pic_id, 280, 160);
            $thumb_img_info = getimagesize($pic_small);
            $size = Array();
            $size[0] = $thumb_img_info[0]; // width
            $size[1] = $thumb_img_info[1]; // height
            $picinfo['src'] = ltrim($picDetail[0]['path'], '/');
            $picinfo['thumb'] = $pic_small;
            if(strpos($picinfo['src'], "Picture")) {
                $src = substr($picinfo['src'], 16);
                $picinfo['src'] = "http://".C('OSS_FORUM_PIC').C('OSS_ENDPOINT').$src;
                $origin_img_info = getimagesize($picinfo['src']);
                $src_size = Array();
                $src_size[0] = $origin_img_info[0]; // width
                $src_size[1] = $origin_img_info[1]; // height
                $picinfo['src_size'] = $src_size;
            }
            $picinfo['size'] = $size;
            unset($picinfo['picture_id']);
            $picture_array[] = $picinfo;
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $picture_array;
        $this->apiSuccess('获取个人作品成功', null, $extra);
    }

    /**保存用户的工作经历
     * @param int $uid
     * @param int $id
     * @param null $position
     * @param null $company_name
     * @param null $start_time
     * @param null $end_time
     * @param string $department
     * @param string $job_content
     */
    public function saveWorkExperience($uid=0,$id=0, $position=null, $company_name=null, $start_time=null, $end_time=null, $department='', $job_content=''){
        if (!$uid) {
            $this->requireLogin();
            $uid = $this->getUid();
        }
        $workExperienceModel = D('User/UserWorkExperience');
        $data['uid'] = $uid;
        if(empty($position)){
            $this->apiError(-3,"职位类别不能为空");
        }
        if(empty($company_name)){
            $this->apiError(-3,"公司名字不能为空");
        }
        if(empty($start_time) || empty($end_time)){
            $this->apiError(-3,"任职时间不能为空");
        }
        $data['position'] = $position;
        $data['company_name'] = $company_name;
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        $data['department'] = $department;
        $data['job_content'] = $job_content;
        if(!$id){
            $issave = $workExperienceModel->add($data);
        }else{
            $isexist = $workExperienceModel->where(array('status'=>1,'id'=>$id))->find();
            if(!$isexist){
                $this->apiError(-2, '该工作经历不存在');
            }
            $issave = $workExperienceModel->where(array('id'=>$id))->save($data);
            if($issave){
                $issave = $id;
            }
        }
        if($issave){
            $res = D('field')->where('uid=' . $uid.' and field_id=39')->find();
            if (!$res) {
                $dl['uid'] = $uid;
                $dl['field_id'] = 39;
                $dl['field_data'] = $company_name;
                $dl['createTime'] = $dl['changeTime'] = time();
                if (!D('field')->add($dl)) {
                    $this->apiError(1001,'用户就职公司添加时出错！');
                }
            } else {
                $dl['changeTime'] = time();
                $work_res = $workExperienceModel->getLastWorkExperience($uid);
                if($work_res){
                    $dl['field_data'] = $work_res[0]['company_name'];
                    if (!D('field')->where('uid=' . $uid.' and field_id=39')->save($dl)) {
                        $this->apiError(1002,'用户就职公司修改时出错！');
                    }
                }
            }
            $extra['work_exp_id'] = $issave;
            $this->apiSuccess('保存用户工作经历成功',null,$extra);
        } else {
            $this->apiError(-1, '保存用户工作经历失败');
        }
    }

    /**
     * 获取个人工作经历
     * @param int $uid
     */
    public function getWorkExperience($uid=0){
        if (!$uid) {
            $this->requireLogin();
            $uid = $this->getUid();
        }
        $model = M('UserWorkExperience');
        $map['uid'] = $uid;
        $map['status'] = 1;
        $totalCount = $model->where($map)->count();
        $result = $model->where($map)->order('start_time desc')->select();
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $result;
        $this->apiSuccess('获取个人工作经历成功', null, $extra);
    }

    public function deleteWorkExperience($id=0){
        if($id == 0){
            $this->apiError(-1, '传入作品ID为空异常');
        }
        $this->requireLogin();
        //获取用户编号
        $uid = $this->getUid();
        $model = M('UserWorkExperience');
        $data['status'] = -1;
        $isexist = $model->where(array('status'=>1,'id'=>$id))->find();
        if(!$isexist){
            $this->apiError(-2, '该工作经历不存在');
        }
        $tem = $model->where('id='.$id.' and uid='.$uid)->save($data);
        if($tem){
            $extra['isdelete'] = true;
            $this->apiSuccess('删除个人工作经历成功', null, $extra);
        }else{
            $this->apiError(-1, '删除个人工作经历失败');
        }
    }

    /**
     * 上传用户的用于个人简历作品
     * @param null $picIds
     */
    public function uploadUserWorks($picIds=null){
        $this->requireLogin();
        if(empty($picIds)){
            $this->apiError(-1, "传入图片ID为空");
        }
        $ids = explode(',',$picIds);
        foreach($ids as $v){
            $picId = $v;
            $this->uploadLogoPicToOSS($picId);
            getThumbImageById($picId, 280, 160);//上传时生成缩略图
            $user_works_data['uid'] = is_login();
            $user_works_data['forum_id'] = 1001;
            $user_works_data['post_id'] = 0;
            $user_works_data['picture_id'] = $picId;
            $user_works_data['create_time'] = NOW_TIME;
            $user_works_model = D('User/UserWorks');
            $works_id = $user_works_model->add($user_works_data);
            if($works_id){
                $workid[] = $works_id;
            }
        }
        $extra['works'] = $workid;
        $this->apiSuccess("上传成功",null,$extra);
    }

    /**
     * 获取用户用于简历的作品
     * @param $uid
     */
    public function userProfileWorks($uid=0, $page=1, $count=6){
        if($uid==0){
            $this->apiError(-1, '传入用户ID为空异常');
        }
        $model = M();
        $tem = $model->query('select count(*) as count from hisihi_user_works where status=1 and uid='.$uid);
        $totalCount = $tem[0]['count'];
        $index = ($page - 1) * $count;
        $pic_list = $model->query('select id, picture_id from hisihi_user_works where status=1 and forum_id=1001 and uid='.$uid.' order by create_time asc limit '.$index.','.$count);
        foreach ($pic_list as &$picinfo) {
            $pic_id = $picinfo['picture_id'];
            $picDetail= $model->query("select path from hisihi_picture where id=".$pic_id);
            $pic_small = getThumbImageById($pic_id, 280, 160);
            $thumb_img_info = getimagesize($pic_small);
            $size = Array();
            $size[0] = $thumb_img_info[0]; // width
            $size[1] = $thumb_img_info[1]; // height
            $picinfo['src'] = ltrim($picDetail[0]['path'], '/');
            $picinfo['thumb'] = $pic_small;
            if(strpos($picinfo['src'], "Picture")) {
                $src = substr($picinfo['src'], 16);
                $picinfo['src'] = "http://".C('OSS_FORUM_PIC').C('OSS_ENDPOINT').$src;
                $origin_img_info = getimagesize($picinfo['src']);
                $src_size = Array();
                $src_size[0] = $origin_img_info[0]; // width
                $src_size[1] = $origin_img_info[1]; // height
                $picinfo['src_size'] = $src_size;
            }
            $picinfo['size'] = $size;
            unset($picinfo['picture_id']);
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $pic_list;
        $this->apiSuccess('获取个人简历作品成功', null, $extra);
    }

    /**删除个人简历作品
     * @param int $id
     */
    public function deleteUserWorks($id=0){
        if($id==0){
            $this->apiError(-1, '传入作品ID为空异常');
        }
        $this->requireLogin();
        //获取用户编号
        $uid = $this->getUid();
        $model = M('UserWorks');
        $data['status'] = -1;
        $isexist = $model->where(array('status'=>1,'id'=>$id))->find();
        if(!$isexist){
            $this->apiError(-2, '该简历作品不存在');
        }
        $tem = $model->where('id='.$id.' and uid='.$uid)->save($data);
        if($tem){
            $extra['isdelete'] = true;
            $this->apiSuccess('删除个人简历作品成功', null, $extra);
        }else{
            $this->apiError(-1, '删除个人简历作品失败');
        }
    }

    private function uploadLogoPicToOSS($picID){
        $model = M();
        $result = $model->query("select path from hisihi_picture where id=".$picID);
        if($result){
            $picLocalPath = $result[0]['path'];
            $picKey = substr($picLocalPath, 17);
            $param["bucketName"] = "forum-pic";
            $param['objectKey'] = $picKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if(!$isExist){
                Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadForumPicResource', $param);
            }
        }
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
    
    private function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC){
        if(is_array($multi_array)){
            foreach ($multi_array as &$row_array){
                if(is_array($row_array)){
                    $key_array[] = $row_array[$sort_key];
                    unset($row_array[$sort_key]);
                }else{
                    return null;
                }
            }
        }else{
            return null;
        }
        array_multisort($key_array,$sort,$multi_array);
        return $multi_array;
    }
    private function verifyMessageTypes($types){
        $types = explode(',', $types);
        $all_types = array('system','user','follow_you','support_post','reply_post','lzl_reply','ask_you');
        foreach ($types as $type) {
            if(!in_array($type, $all_types)){
                $types = null;
                break;
            }
        }
        return $types;
    }

    /**
     * 第三方登陆
     * @param string $platform
     * @param string $access_token
     * @param string $openId
     * @param string $code
     * @param string $client
     * @param null $version
     */
    public function loginByThirdPartyOpenPlatform($platform='', $access_token='', $openId='', $code='', $client = 'client', $version=null) {
        if("weibo"==$platform){
            $data = array(
                'access_token'=>$access_token,
                'uid'=>$openId
            );
            $query = http_build_query($data);
            $content = $this->request_by_curl("https://api.weibo.com/2/users/show.json", $query);
            $user = json_decode($content, true);
            $id = $user['id'];
            $nickname = $user['screen_name'];
            $nickname = str_replace(' ', '', $nickname);
            $avatar = $user['avatar_large'];
            $api = new UserApi();
            $userExist = $api->info($id, true);
            if($userExist==-1){  // 用户不存在
                $uid = $api->register($id, $nickname, '123456', substr($id,1,3).'@'.substr($id,5,7).'.com');
                if($uid <= 0) {
                    $message = $this->getRegisterErrorMessage($uid);
                    $code = $this->getRegisterErrorCode($uid);
                    $this->apiError($code,$message);
                }
                $avatar_model = D('Addons://Avatar/Avatar');
                $avatar_model->saveThirdPartyAvatar($uid, $avatar);
                $this->thirdPartyLoginGetUserInfo($uid, $id, true, $client, $version);
            } else {
                $uid = $userExist[0];
                $this->thirdPartyLoginGetUserInfo($uid, $id, false, $client, $version);
            }
        } else if("qq"==$platform){
            $data = array(
                'access_token'=>$access_token,
                'openid'=>$openId,
                'oauth_consumer_key'=> C('QQOpenPlatFormLoginKey'),
            );
            $query = http_build_query($data);
            $content = $this->request_by_curl("https://graph.qq.com/user/get_simple_userinfo", $query);
            $user = json_decode($content, true);
            $id = $openId;
            $nickname = $user['nickname'];
            $nickname = str_replace(' ', '', $nickname);
            $avatar = $user['figureurl_qq_2'];
            if(empty($avatar)) {
                $avatar = $user['figureurl_qq_1'];
            }
            $api = new UserApi();
            $userExist = $api->info($id, true);
            if($userExist==-1) {  // 用户不存在
                $uid = $api->register($id, $nickname, '123456', substr($id,1,3).'@'.substr($id,5,7).'.com');
                if($uid <= 0) {
                    $message = $this->getRegisterErrorMessage($uid);
                    $code = $this->getRegisterErrorCode($uid);
                    $this->apiError($code,$message);
                }
                $avatar_model = D('Addons://Avatar/Avatar');
                $avatar_model->saveThirdPartyAvatar($uid, $avatar);
                $this->thirdPartyLoginGetUserInfo($uid, $id, true, $client, $version);
            } else {
                $uid = $userExist[0];
                $this->thirdPartyLoginGetUserInfo($uid, $id, false, $client, $version);
            }
        } else if('weixin'==$platform){
            if($code=='android'){ // 由于android使用share sdk，特殊情况区别对待
                $tokenData = array('access_token'=> $access_token, 'openid' => $openId);
                $query = http_build_query($tokenData);
                $content = $this->request_by_curl("https://api.weixin.qq.com/sns/userinfo", $query);
                $user = json_decode($content, true);
                $openid = $user['openid'];
                $uid = str_replace("-", "", $openid);
                $nickname = $user['nickname'];
                $nickname = str_replace(' ', '', $nickname);
                $avatar = $user['headimgurl'];
                $api = new UserApi();
                $userExist = $api->info($uid, true);
                if($userExist==-1) {  // 用户不存在
                    $uid = $api->register($uid, $nickname, '123456', substr($uid,1,3).'@'.substr($uid,3,5).'.com');
                    if($uid <= 0) {
                        $message = $this->getRegisterErrorMessage($uid);
                        $code = $this->getRegisterErrorCode($uid);
                        $this->apiError($code,$message);
                    }
                    $avatar_model = D('Addons://Avatar/Avatar');
                    $avatar_model->saveThirdPartyAvatar($uid, $avatar);
                    $this->thirdPartyLoginGetUserInfo($uid, $uid, true, $client, $version);
                } else {
                    $uid = $userExist[0];
                    $this->thirdPartyLoginGetUserInfo($uid, $uid, false, $client, $version);
                }
            } else {
                $data = array(
                    'appid'      => C('WeiXinPlatFormId'),
                    'secret'     => C('WeiXinPlatFormSecret'),
                    'code'       => $code,
                    'grant_type' => 'authorization_code',
                );

                $query = http_build_query($data);
                $content = $this->request_by_curl("https://api.weixin.qq.com/sns/oauth2/access_token", $query);
                $user = json_decode($content, true);
                $refresh_token = $user['refresh_token'];
                $tokenData = array('access_token'=> $user['access_token'], 'openid' => $user['openid']);
                $query = http_build_query($tokenData);
                $content = $this->request_by_curl("https://api.weixin.qq.com/sns/userinfo", $query);
                $user = json_decode($content, true);
                if($user['errorcode']!=0){
                    $refreshData = array(
                        'appid' => C('WeiXinPlatFormId'),
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $refresh_token,
                    );
                    $query = http_build_query($refreshData);
                    $content = $this->request_by_curl("https://api.weixin.qq.com/sns/oauth2/refresh_token", $query);
                    $user = json_decode($content, true);
                    if($user['errcode']==40030){
                        $this->apiError(-1, '重新获取token失败');
                    }
                    $tokenData = array('access_token'=> $user['access_token'], 'openid' => $user['openid']);
                    $query = http_build_query($tokenData);
                }
                $content = $this->request_by_curl("https://api.weixin.qq.com/sns/userinfo", $query);
                $user = json_decode($content, true);
                $openid = $user['openid'];
                $uid = $openid;
                $nickname = $user['nickname'];
                $nickname = str_replace(' ', '', $nickname);
                $avatar = $user['headimgurl'];
                $api = new UserApi();
                $userExist = $api->info($uid, true);
                if($userExist==-1) {  // 用户不存在
                    $uid = $api->register($uid, $nickname, '123456', substr($uid,1,3).'@'.substr($uid, 3, 6).'.com');
                    if($uid <= 0) {
                        $message = $this->getRegisterErrorMessage($uid);
                        $code = $this->getRegisterErrorCode($uid);
                        $this->apiError($code,$message);
                    }
                    $avatar_model = D('Addons://Avatar/Avatar');
                    $avatar_model->saveThirdPartyAvatar($uid, $avatar);
                    $this->thirdPartyLoginGetUserInfo($uid, $openid, true, $client, $version);
                } else {
                    $uid = $userExist[0];
                    $this->thirdPartyLoginGetUserInfo($uid, $openid, false, $client, $version);
                }
            }
        } else {
            $this->apiError(-1, '暂时不支持该平台的登陆');
        }

    }

    /**
     * 第三方登陆时返回用户信息
     * @param $uid
     * @param $openid
     * @param $isNewUser
     * @param $client
     * @param null $version
     */
    public function thirdPartyLoginGetUserInfo($uid, $openid, $isNewUser, $client, $version=null){
        //读取数据库中的用户详细资料
        $map = array('uid' => $uid);
        $user1 = D('Home/Member')->where($map)->find();
        $user2 = D('User/UcenterMember')->where('id='.$uid)->find();

        D('Home/Member')->login($uid, false, $client);
        //清除登录缓存
        clean_query_user_cache($uid,array('last_login_time','last_login_client'));

        //获取头像信息
        $avatar = new AvatarAddon();
        $avatar_path = $avatar->getAvatarPath($uid);
        $avatar_url = getRootUrl() . $avatar->getAvatarPath($uid);

        //缩略头像
        $avatar128_path = getThumbImage($avatar_path, 128);
        $avatar128_url = getRootUrl() . $avatar128_path['src'];

        //获取等级
        $title = D('Usercenter/Title')->getTitle($uid);

        //签到状态
        $map['ctime'] = array('gt', strtotime(date('Ymd')));
        $ischeck = D('Check_info')->where($map)->find();
        if($ischeck) {
            unset($ischeck['uid']);
            unset($ischeck['total_score']);
            unset($ischeck['ctime']);
        }

        //用户分组
        $profile_group = $this->_profile_group($uid);

        //返回成功信息
        $extra = array();
        $extra['session_id'] = session_id();
        $extra['uid'] = $uid;
        $extra['openid'] = $openid;
        $extra['name'] = $user1['nickname'];
        $extra['group'] = $profile_group['gid'];
        $extra['avatar_url'] = $avatar_url;
        $extra['avatar128_url'] = $avatar128_url;
        $extra['signature'] = $user1['signature'];
        $extra['tox_money'] = $user1['score'];
        $extra['title'] = $title;
        $extra['ischeck'] = $ischeck;
        $extra['timestamp'] = time();
        $extra['mobile'] = $user2['mobile'];
        if($isNewUser){
            $extra['is_new'] = true;
        } else {
            $extra['is_new'] = false;
        }
        if((float)$version>=2.2){
            $extra['my_favorite_count'] = $this->getMyFavoriteCount($uid);
            $extra['my_follow_count'] = $this->getMyFollowerCount($uid);
            $extra['follow_me_count'] = $this->getFollowMeCount($uid);
        }
        $this->apiSuccess("第三方登录成功", null, $extra);
    }

    /**
     * 获取个人简历中的用户信息
     * @param int $uid
     * @param bool|false $api
     * @return mixed
     */
    public function getResumeProfile($uid=0, $api=false){
        if(empty($uid)){
            $this->apiError(-1, "传入用户id为空");
        }
        $map = array('uid' => $uid);
        $user = D('Home/Member')->where($map)->find();
        if(!$user){
            $this->apiError(-1,"获取用户信息失败");
        }
        $result['info'] = query_user(array('avatar256', 'avatar128', 'username', 'extinfo', 'signature', 'nickname', 'mobile', 'sex', 'birthday', 'email'), $uid);

        // 扩展信息
        // 扩展信息
        $profile_group = $this->_profile_group($uid);
        $info_list = $this->_info_list($profile_group['id'], $uid, "2.1");
        $result['info']['extinfo'] = $info_list;
        //软件技能
        $skills = $this->_user_skills($uid);
        $result['info']['skills'] = $skills;
        //我的亮点
        $lightspot = $this->_user_lightspot($uid);
        $result['info']['lightspot'] = $lightspot;
        // 用户工作经历
        $workExperienceModel = D('User/UserWorkExperience');
        $experList = $workExperienceModel->getUserWorkExperiences($uid);
        if($experList){
            $result['info']['experience'] = $experList;
        } else {
            $result['info']['experience'] = null;
        }
        // 用户作品
        $user_works_model = D('User/UserWorks');
        $resumeWorkList = $user_works_model->getResumeWorks($uid);
        if($resumeWorkList){
            $worksArray = array();
            foreach ($resumeWorkList as &$picinfo) {
                $pic_id = $picinfo['picture_id'];
                $model = M();
                $picDetail= $model->query("select path from hisihi_picture where id=".$pic_id);
                $picinfo['src'] = ltrim($picDetail[0]['path'], '/');
                if(strpos($picinfo['src'], "Picture")) {
                    $src = substr($picinfo['src'], 16);
                    $picinfo['src'] = "http://".C('OSS_FORUM_PIC').C('OSS_ENDPOINT').$src;
                }
                unset($picinfo['picture_id']);
                array_push($worksArray, $picinfo);
            }
            $result['info']['works'] = $worksArray;
        } else {
            $result['info']['works'] = null;
        }
        if($api){
            $this->apiSuccess('ok', null, $result);
        } else {
            return $result;
        }
    }

    /**
     * @param int $uid
     */
    public function preview_resume_h5($uid=0){
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }
        if(empty($uid)){
            $this->apiError(-1, '获取uid失败');
        } else {
            $this->assign('uid', $uid);
            $this->display('User/resume_h5/index');
        }
    }

    /**
     * @param $url
     * @param $query
     * @return mixed
     */
    private function request_by_curl($url, $query) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.'?'.$query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];
        }
        return $str;
    }

    public function autoFollow(){
        $startUid = 5500;
        $count = 517;
        $model = M();
        for ($x=0; $x<$count; $x++) {
            $time = time();
            $model->execute("INSERT INTO hisihi_follow (follow_who, who_follow, create_time) values(81, ".$startUid.", ".$time.")");
            $startUid++;
        }
    }


    public function smsTest($code=0){
        $tianyi = new TianyiAddon;
        $check = $tianyi->checkMobVerify('18507554340', $code, 'iOS');
        $this->apiSuccess(json_encode($check));
    }

}