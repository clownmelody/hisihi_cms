<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Controller;
use App\Exception\ReturnException;
use Think\Controller;
use Think\Controller\RestController;
use User\Api\UserApi;
//use Addons\Digg\DiggAddon;
//use Addons\LocalComment\LocalCommentAddon;
use Addons\Avatar\AvatarAddon;
require_once(dirname(__FILE__).'/../Common/function.php');

abstract class AppController extends RestController {
    protected $api;
    protected $isInternalCall;

    public function _initialize() {
        //读取站点信息
        $config = api('Config/lists');
        C($config); //添加配置
        //站点关闭，显示关闭消息
        if(!C('WEB_SITE_CLOSE')){
            $this->apiError(403, '站点已经关闭，请稍后访问~');
        }
        //定义API
        $this->api = new UserApi();
    }

    public function setInternalCallApi($value=true) {
        $this->isInternalCall = $value ? true : false;
    }

    /**
     * 找不到接口时调用该函数
     */
    public function _empty() {
        $this->apiError(404, "找不到该接口");
    }

    protected function apiReturn($success, $error_code=0, $message=null, $redirect=null, $extra=null) {
        //生成返回信息
        $result = array();
        $result['success'] = $success;
        $result['error_code'] = $error_code;
        if($message !== null) {
            $result['message'] = $message;
        }
        if($redirect !== null) {
            $result['redirect'] = $redirect;
        }
        foreach($extra as $key=>$value) {
            $result[$key] = $value;
        }
        //将返回信息进行编码
        $format = $_REQUEST['format'] ? $_REQUEST['format'] : 'json';//返回值格式，默认json
        if($this->isInternalCall) {
            throw new ReturnException($result);
        } else if($format == 'json') {
            $this->response($result,'json');
            //echo json_encode($result);
            exit;
        } else if($format == 'xml') {
            echo xml_encode($result);
            exit;
        } else {
            $_GET['format'] = 'json';
            $_REQUEST['format'] = 'json';
            return $this->apiError(400, "format参数错误");
        }
    }

    public function apiSuccess($message, $redirect=null, $extra=null) {
        return $this->apiReturn(true, 0, $message, $redirect, $extra);
    }

    protected function apiError($error_code, $message, $redirect=null, $extra=null) {
        return $this->apiReturn(false, $error_code, $message, $redirect, $extra);
    }

    /**
     * 返回当前登录用户的UID
     * @return int
     */
    protected function getUid() {
        return is_login();
    }

    protected function requireLogin() {
        $uid = $this->getUid();
        if(!$uid) {
            $this->apiError(401,"需要登录");
        }
        $user = session('user_auth');
        $last_login = query_user(array('last_login_time','last_login_client'));
        // && $last_login['last_login_client'] != $user['client']
        //var_dump("last_login_time:".$last_login['last_login_time']);
        //var_dump("now_login_time:".$user['last_login_time']);
        if($last_login['last_login_time'] != $user['last_login_time']){
            $model = D('Home/Member');
            $model->logout();
            session_destroy();
            $this->apiError(418,"已在其他位置登录,强制退出，如不是本人操作，请修改密码");
        }
    }

    protected function getCombinedUser($uid) {
        if(!$uid) {
            return null;
        }
        //获取UCenter的用户信息
        $ucenter_info = $this->getUcenterUserInfo($uid);
        //获取Home的用户信息
        $map = array();
        $map['uid'] = $uid;
        $map['status'] = 1;
        $home_info = D('Home/Member')->where($map)->find();
        if(!$home_info) {
            $home_info = array();
        }
        //合并两者的信息
        $result = array_merge($ucenter_info, $home_info);
        return $result;
    }

    protected function getUcenterUserInfo($uid) {
        //获取用户信息的数组
        $api = new UserApi();
        $info = $api->info($uid);
        if(!$info) {
            return array();
        }
        //添加key信息，并返回结果
        $result = array(
            'uid' => $info[0],
            'username' => $info[1],
            'email' => $info[2],
            'mobile' => $info[3],
        );
        return $result;
    }

    protected function updateUser($uid, $data) {
        //检查参数
        if(!$uid || !$data) {
            $this->apiError(400,'参数不能为空');
        }
        //将数据分配到两张表中
        $split = $this->splitUserFields($data);
        $home_user= $split['home'];
        $ucenter_user = $split['ucenter'];
        dump($ucenter_user);
        //写入数据库
        $api = $this->api;
        if($ucenter_user && !$api->updateInfos($uid, $ucenter_user)) {
            $this->apiError(0,$api->getError());
        }
        if($home_user) {
            $map = array();
            $map['uid'] = $uid;
            $model = D('Home/Member');
            $result = $model->where($map)->save($home_user);
            if(!$result) {
                $this->apiError(0,'写入数据库错误');
            }
        }
        //返回成功
        return true;
    }

    protected function getLoginErrorMessage($error_code) {
        switch($error_code) {
            case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
            case -2: $error = '密码错误！'; break;
            default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
        }
        return $error;
    }

    protected function getLoginErrorCode($error_code) {
        switch($error_code) {
            case -1: return 601;
            case -2: return 602;
            default: return 600;
        }
    }

    protected function getRegisterErrorMessage($error_code) {
        switch ($error_code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            case -12:$error='用户名必须以中文或字母开始，只能包含拼音数字，字母，汉字！';break;
            default:  $error = '未知错误';
        }
        return $error;
    }

    protected function getRegisterErrorCode($error_code) {
        switch ($error_code) {
            case -1:  return 701;
            case -2:  return 702;
            case -3:  return 703;
            case -4:  return 704;
            case -5:  return 705;
            case -6:  return 706;
            case -7:  return 707;
            case -8:  return 708;
            case -9:  return 709;
            case -10: return 710;
            case -11: return 711;
            default:  return 700;
        }
    }

    protected function splitUserFields($data) {
        $result = array();
        $home_fields = array('nickname','sex','qq','name','signature','birthday');
        $result['home'] = array_gets($data, $home_fields);
        $ucenter_fields = array('email','password', 'mobile');
        $result['ucenter'] = array_gets($data, $ucenter_fields);
        return $result;
    }

    protected function getUidByMobile($mobile) {
        return $this->api->getUidByMobile($mobile);
    }

    protected function verifyPassword($uid, $password) {
        $result = D('User/UcenterMember')->verifyUser($uid, $password);
        if(!$result) {
            $this->apiError(-1, '密码错误');
        }
    }

    protected function encodeSex($sex) {
        $map = array(0=>'s', 1=>'m', 2=>'f');
        return $map[$sex];
    }

    protected function decodeSex($sex) {
        $map = array('s'=>0, 'm'=>1,'f'=>2);
        return $map[$sex];
    }

    protected function getTopicStructure($document_id, $comment_count) {
        $document = D('Home/Document')->where(array('id'=>$document_id,'status'=>1))->find();
        $e = $document;
        //添加文档编号
        $e['document_id'] = $e['id'];
        //添加主题内容
        $model_name = D('Admin/Model')->where(array('id'=>$e['model_id'],'status'=>1))->find();
        $model_name = $model_name['name'];
        if($model_name == 'weibo') {
            $detail = D('Home/Document')->detail($e['id']);
            $e['content'] = $detail['content'];
        } else {
            $e['content'] = '';
        }
        //添加用户信息
        $e['user'] = $this->getAuthorStructure($e['uid']);
        //添加评论信息
        $e['comment_count'] = $e['comment'];
        $e['comment'] = $this->getCommentList($e['document_id'], 0, $comment_count);
        //添加赞数量
        $addon = new DiggAddon;
        $digg_count = $addon->getDiggCount($document_id);
        $e['digg_count'] = $digg_count;
        //筛选需要的字段
        $e = array(
            'document_id'=>$e['document_id'],
            'title'=>$e['title'],
            'user'=>$e['user'],
            'type'=>$model_name,
            'content'=>$e['content'],
            'create_time'=>$e['create_time'],
            'update_time'=>$e['update_time'],
            'view_count'=>strval(intval($e['view'])),
            'comment_count'=>strval(intval($e['comment_count'])),
            'digg_count'=>strval(intval($e['digg_count'])),
            'comment'=>$e['comment'],
        );
        //返回结果
        return $e;
    }

    protected function getAuthorStructure($uid) {
        //查询数据库中的基本信息
        $map = array('id'=>$uid);
        $user = D('User/UcenterMember')->where($map)->find();
        //查询头像
        $addon = new AvatarAddon;
        $avatar = $addon->getAvatarUrl($uid);
        //返回结果
        return array(
            'uid'=>$user['id'],
            'avatar_url'=>$avatar,
            'username'=>$user['username']);
    }

    protected function getCommentCount($document_id) {
        $addon = new LocalCommentAddon();
        return $addon->getCommentCount($document_id);
    }

    protected function getCommentList($document_id, $offset=0, $count=2) {
        //调用评论插件读取评论该列表
        $addon = new LocalCommentAddon();
        $result = $addon->getCommentList($document_id, $offset, $count);
        if(!$result) {
            $result = array();
        }
        //添加评论编号
        foreach($result as &$e) {
            $e['comment_id'] = $e['id'];
            unset($e['id']);
        }
        //添加用户信息
        foreach($result as &$e) {
            $e['user'] = $this->getAuthorStructure($e['uid']);
        }
        //筛选需要的字段
        foreach($result as &$e) {
            $e = array(
                'comment_id' => $e['comment_id'],
                'user' =>$e['user'],
                'content'=>$e['content'],
                'create_time'=>$e['create_time'],
            );
        }
        //返回结果
        return $result;
    }

    /**
     * 头条是否被点赞
     * @param $id
     * @return mixed
     */
    protected function isArticleSupport($id){
        $map_support['row'] = $id;
        $map_support['appname'] = 'Article';
        $map_supported = array_merge($map_support, array('uid' => is_login()));
        $supported = D('Support')->where($map_supported)->count();
        return $supported;
    }

    /**
     * 头条是否被收藏
     * @param $id
     * @return mixed
     */
    protected function isArticleFavorite($id){
        $map_support['row'] = $id;
        $map_support['appname'] = 'Article';
        $map_supported = array_merge($map_support, array('uid' => is_login()));
        $favorited = D('Favorite')->where($map_supported)->count();
        return $favorited;
    }

    /**
     * 获取头条的点赞数
     * @param $id
     * @return mixed
     */
    protected function getArticleSupportCount($id){
        $map_support_count['row'] = $id;
        $map_support_count['appname'] = 'Article';
        $supportCount = $this->getSupportCountCache($map_support_count);
        $doc = M('DocumentArticle')->field('fake_support_count')->where('id='.$id)->find();
        if($doc){
            $supportCount = $supportCount + $doc['fake_support_count'];
        }
        return $supportCount;
    }

    /**
     * 头条是否被点踩
     * @param $id
     * @return mixed
     */
    protected function isArticleOppose($id){
        $map_oppose['row'] = $id;
        $map_oppose['appname'] = 'Article';
        $map_oppose = array_merge($map_oppose, array('uid' => is_login()));
        $opposed = M('Oppose')->where($map_oppose)->count();
        return $opposed;
    }

    /**
     * 获取头条的点踩数
     * @param $id
     * @return mixed
     */
    protected function getArticleOpposeCount($id){
        $map_oppose_count['row'] = $id;
        $map_oppose_count['appname'] = 'Article';
        $opposeCount = M('Oppose')->where($map_oppose_count)->count();
        $doc = M('DocumentArticle')->field('fake_support_count')->where('id='.$id)->find();
        if($doc){
            $fake_count = (int)$doc['fake_support_count'] * 0.05;
            $opposeCount = $opposeCount + $fake_count;
        }
        return $opposeCount;
    }

    /**
     * @param $map_support
     * @return mixed
     * @auth RFly
     */
    private function getSupportCountCache($map_support)
    {
        /*$cache_key = "support_count_" . implode('_', $map_support);
        $count = S($cache_key);
        if (empty($count)) {
            $count = D('Support')->where($map_support)->count();
            S($cache_key, $count);
            return $count;
        }*/
        $count = D('Support')->where($map_support)->count();
        return $count;
    }
}