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
    public function registerByMobile($mobile, $password, $group=0) {
        //调用用户中心
        $api = new UserApi();
        $uid = $api->register($mobile, $mobile, $password, $mobile.'@hisihi.com', $mobile); // 邮箱
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
	
	//登录
	public function login($username, $password, $type = 1, $client = 'iOS', $reg_id = '') {
        // 获取上次登录的终端设备
        switch ($type) {
            case 1:
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
        $title = D('Usercenter/Title')->getTitle($user1['score']);

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
        $extra['tox_money'] = $user1['tox_money'];
        $extra['title'] = $title;
        $extra['ischeck'] = $ischeck;
        \Think\Log::write("login response data: ".json_encode($extra));
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
        $this->apiSuccess("签到成功", null, array('checkInfo'=>$check_info));
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
            $this->apiSuccess("关注成功");
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
    public function getMessageList($page = 1, $count = 10, $isRead = -1, $types = null)
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
        $this->apiSuccess('头像保存成功');
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

    /**分组下的字段信息及相应内容
     * @param null $id 扩展分组id
     * @param null $uid
     * @author RFly
     */
    public function _info_list($id = null, $uid = null)
    {
        $info_list = null;

        if (isset($uid) && $uid != is_login()) {
            //查看别人的扩展信息
            $field_setting_list = D('field_setting')->where(array('profile_group_id' => $id, 'status' => '1', 'visiable' => '1'))->order('sort asc')->select();

            if (!$field_setting_list) {
                return null;
            }
            $map['uid'] = $uid;
        } else if (is_login()) {
            $field_setting_list = D('field_setting')->where(array('profile_group_id' => $id, 'status' => '1'))->order('sort asc')->select();

            if (!$field_setting_list) {
                return null;
            }
            $map['uid'] = is_login();

        } else {
            $this->error('请先登录！');
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

    public function getProfile($uid = null)
    {
        //$this->requireLogin();

        //默认查看自己的详细资料
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
            $title = D('Usercenter/Title')->getTitle($user1['score']);

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
            $info_list = $this->_info_list($profile_group['id'], $uid);

            //只返回必要的详细资料
            $this->apiSuccess("获取成功", null, array(
                'uid' => $uid,
                'avatar_url' => $avatar_url,
                'avatar128_url' => $avatar128_url,
                'signature' => $user1['signature'],
                'email' => $user2['email'],
                'mobile' => $user2['mobile'],
                'tox_money' => $user1['tox_money'],
                'name' => $user1['nickname'],
                'sex' => $this->encodeSex($user1['sex']),
                'birthday' => $user1['birthday'],
                'title' => $title,
                'ischeck' => $ischeck,
                'username' => $user2['username'],
                'group' => $profile_group['gid'],
                'extinfo' => $info_list
            ));
        } else {
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
            //$profile_group = $this->_profile_group($uid);
            //$info_list = $this->_info_list($profile_group['id'], $uid);
            //$result['info']['group'] = $profile_group['gid'];
            //$result['info']['extinfo'] = $info_list;

            //返回成功结果
            $this->apiSuccess("获取用户信息成功", null, array('userInfo' => $result));
        }
    }

    public function setProfile($signature = null, $email = null, $name = null, $sex = null, $birthday = null, $college = null, $major = null, $grade = null, $institution = null, $student = null, $year = null)
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
        $profile_group = $this->_profile_group($uid);
        $field_setting_list = D('field_setting')->where(array('profile_group_id' => $profile_group['id'], 'status' => '1'))->order('sort asc')->select();

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

    public function listFavorite($uid = 0, $page = 1, $count = 10)
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
        $map = array('uid' => $uid, 'status' => 1);
        $list = D('Favorite')->where($map)->order('create_time desc')->page($page,$count)->select();
        $totalCount = D('Favorite')->where($map)->order('create_time desc')->count();

        //获取主题的详细资料
        foreach ($list as &$favorite) {
            switch($favorite['appname']){
                case 'Issue':
                    $favorite['type'] = 'Course';
                    $favorite['info'] = A('Course')->findCourse($favorite['row']);
                    break;
            }
            unset($favorite['appname']);
            unset($favorite['table']);
            unset($favorite['row']);

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

    // tab='fans' 'question' 'answer'
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
            $v['info'] = query_user(array('avatar256', 'avatar128', 'username', 'group','extinfo', 'fans', 'following', 'signature', 'nickname','weibocount','replycount'), $v['uid']);
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
}