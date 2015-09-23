<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Controller;
//use Addons\ResetByEmail\ResetByEmailAddon;
use Addons\Avatar\AvatarAddon;
use Think\Controller;
use Think\Hook;
use User\Api\UserApi;
use Addons\Tianyi\TianyiAddon;
use Weibo\Api\WeiboApi;

class PublicController extends AppController {

    private $weiboApi;

    public function _initialize()
    {
        $this->weiboApi = new WeiboApi();
        C('SHOW_PAGE_TRACE', false);
    }
    public function login($username, $password) {
        //登录单点登录系统
        $result = $this->api->login($username, $password, 1); //1表示登录类型，使用用户名登录。
        if($result <= 0) {
            $message = $this->getLoginErrorMessage($result);
            $code = $this->getLoginErrorCode($result);
            $this->apiError($code,$message);
        } else {
            $uid = $result;
        }
        //登录前台
        $model = D('Home/Member');
        $result = $model->login($uid);
        if(!$result) {
            $message = $model->getError();
            $this->apiError(604,$message);
        }
        //返回成功信息
        $extra = array();
        $extra['session_id'] = session_id();
        $extra['uid'] = $uid;
        $this->apiSuccess("登录成功", null, $extra);
    }

    public function logout() {
        $this->requireLogin();
        //调用用户中心
        $model = D('Home/Member');
        $model->logout();
        session_destroy();
        //返回成功信息
        $this->apiSuccess("登出成功");
    }

    public function register($username, $password) {
        //调用用户中心
        $api = new UserApi();
        $uid = $api->register($username, $password, $username.'@username.com'); // 邮箱为空
        if($uid <= 0) {
            $message = $this->getRegisterErrorMessage($uid);
            $code = $this->getRegisterErrorCode($uid);
            $this->apiError($code,$message);
        }
        //返回成功信息
        $extra = array();
        $extra['uid'] = $uid;
        $this->apiSuccess("注册成功", null, $extra);
    }

    public function sendSms($mobile=null) {
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

    public function statInfo() {
        $map['status'] = array('egt',0);

        $where = 'auth_group_access.uid = member.uid and auth_group_access.group_id = ';
        $model =  M("table");

        $statInfo['students'] = $model->table(array(
            'hisihi_auth_group_access'=>'auth_group_access',
            'hisihi_member'=>'member',))->where($where.'5')->field('member.uid')->count();
        $statInfo['designers'] = $model->table(array(
            'hisihi_auth_group_access'=>'auth_group_access',
            'hisihi_member'=>'member',))->where($where.'6')->field('member.uid')->count();
        $statInfo['questions'] = M('Weibo')->count();

        //////////////////////统计数字 特殊处理/////////////////////////////
        //$statInfo['hiworks'] = D('DocumentDownload')->count();
        $hiworksCount = S('Hiworks_count_all');
        if($hiworksCount == 0){
            $countAll = 0;
            $hiworksCate = D('Hiworks/Category')->getSameLevel('2');
            foreach ($hiworksCate as $cate) {
                $map = array('category_id' => $cate['id']);
                //////////////////////统计数字 特殊处理/////////////////////////////
                $count = D('Document')->where($map)->count('id') + 2000;
                if(($count+10) != S('Hiworks_count_'.$cate['id'])) {
                    $count = $count + 10;
                    S('Hiworks_count_'.$cate['id'],$count);
                }
                $countAll += S('Hiworks_count_'.$cate['id']);
                ///////////////////////////////////////////////////////////////////
            }
            S('Hiworks_count_all',$countAll);
            $hiworksCount = S('Hiworks_count_all');
        }
        $statInfo['hiworks'] = $hiworksCount + C('HIWORK_COUNT_BASE');
        $statInfo['students'] = $statInfo['students'] + C('STUDENT_COUNT_BASE');
        $statInfo['designers'] = $statInfo['designers'] + C('TEACHER_COUNT_BASE');
        $statInfo['questions'] = $statInfo['questions'] + C('QUESTION_COUNT_BASE');
        ////////////////////////////////////////////////////////////////////

        //返回结果
        $this->apiSuccess('获取统计信息成功', null, array('statInfo' => $statInfo));
    }

    public function suggest($content) {
        $this->requireLogin();
        $this->requireSendInterval();

        $content = '#建议#'.$content;

        //写入数据库
        $weibo_id = D('Weibo/Weibo')->addWeibo(get_uid(), $content, 'feed', '', '');
        if (!$weibo_id) {
            $this->apiError(-1,D('Weibo/Weibo')->getError());
        }
        $this->updateLastSendTime();

        //返回结果
        $this->apiSuccess('建议发送成功！');
    }

    public function topList($page=1, $count=5, $version="1.0"){
        /* 获取当前分类列表 */
        $Document = D('Blog/Document');
        //获取当前分类下的文章
        $all_list = $Document->lists(47);
        $totalCount = count($all_list);
        $list = $Document->page($page, $count)->lists(47);
        foreach($list as &$topic){
            $did = $topic['id'];
            $topic['source_name'] = $this->getSourceName($did);
            $topic['logo_pic'] = $this->getSourceLogoPic($did);
            //解析并成立图片数据
            $topic['img'] = $this->fetchImage($topic['cover_id']);
            if($version=="2.0"){
                $topic['content_url'] = 'http://www.hisihi.com/app.php/public/topcontent/version/2.0/type/view/id/'.$topic['id'];
                $topic['share_url'] = 'http://www.hisihi.com/app.php/public/topcontent/type/view/id/'.$topic['id'];
            } else {
                $topic['content_url'] = 'http://www.hisihi.com/app.php/public/topcontent/type/view/id/'.$topic['id'];
            }

            $topic['isSupportd'] = $this->isArticleSupport($did);
            $topic['isFavorited'] = $this->isArticleFavorite($did);
            $topic['supportCount'] = $this->getArticleSupportCount($did);
            unset($topic['uid']);
            unset($topic['name']);
            unset($topic['category_id']);
            unset($topic['type']);
            unset($topic['root']);
            unset($topic['pid']);
            unset($topic['model_id']);
            unset($topic['position']);
            unset($topic['link_id']);
            unset($topic['cover_id']);
            unset($topic['deadline']);
            unset($topic['attach']);
            unset($topic['extend']);
            unset($topic['level']);
            unset($topic['display']);
            unset($topic['comment']);
            unset($topic['status']);
            unset($topic['isrecommend']);
        }
        $this->apiSuccess("获取首页顶部列表成功", null, array('totalCount' => $totalCount,'course' => $list));
    }

    /**
     * 获取头条详情
     * @param $id
     * @param $version
     */
    public function findArticle($id, $version){
        $doc_model = M();
        $article = $doc_model->query("select id, title, cover_id, description, view, create_time, update_time from hisihi_document where id=".$id);
        foreach($article as &$info){
            $info['source_name'] = $this->getSourceName($id);
            $info['logo_pic'] = $this->getSourceLogoPic($id);
            //解析并成立图片数据
            $info['img'] = $this->fetchImage($info['cover_id']);
            if($version=='2.0'){
                $info['content_url'] = 'http://www.hisihi.com/app.php/public/topcontent/version/2.0/type/view/id/'.$id;
                $info['share_url'] = 'http://www.hisihi.com/app.php/public/topcontent/type/view/id/'.$id;
            } else {
                $info['content_url'] = 'http://www.hisihi.com/app.php/public/topcontent/type/view/id/'.$id;
            }
            unset($info['uid']);
            unset($info['name']);
            unset($info['category_id']);
            unset($info['type']);
            unset($info['root']);
            unset($info['pid']);
            unset($info['model_id']);
            unset($info['position']);
            unset($info['link_id']);
            unset($info['cover_id']);
            unset($info['deadline']);
            unset($info['attach']);
            unset($info['extend']);
            unset($info['level']);
        }
        return $article[0];
    }

    private function getSourceName($id){
        $model = M();
        $result = $model->query('SELECT source_name FROM hisihi_document_article WHERE id='.$id);
        if($result){
            return $result[0]['source_name'];
        }
        return null;
    }

    private function getSourceLogoPic($id){
        $logo_pic = null;
        $model = M();
        $result = $model->query('SELECT logo_pic FROM hisihi_document_article WHERE id='.$id);
        if($result){
            $pic_id = $result[0]['logo_pic'];
            $picDetail= $model->query("select path from hisihi_picture where id=".$pic_id);
            if($picDetail){
                $picLocalPath = $picDetail[0]['path'];
                $picKey = substr($picLocalPath, 17);
                $param["bucketName"] = "hisihi-other";
                $param['objectKey'] = $picKey;
                $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
                if($isExist){
                    $logo_pic = "http://hisihi-other".C('OSS_ENDPOINT').$picKey;
                }
            }
        }
        return $logo_pic;
    }

    public function topContent($id, $type = '', $version='1.0'){
        /* 获取当前分类列表 */
        $Document = D('Blog/Document');
        $Article = D('Blog/Article', 'Logic');

        //获取当前分类下的文章
        //$info = $Document->field('id,title,description,display,view,comment,create_time,update_time,cover_id')->find($id);
        $info = $Document->field('id,title,description,view,create_time,update_time,cover_id')->find($id);
        if(empty($info)){
            $this->apiError(-1, "id不存在");
        }
        $Document->where(array('id' => $id))->setInc('view');
        $content = $Article->detail($id);
        $content = array_merge($info, $content);
        if($type == 'view') {
            $this->assign('top_content_info', $content);
            $this->setTitle('{$top_content_info.title|op_t} — 嘿设汇');
            if($version=="2.0"){
                $this->display('v2content');
            } else {
                $this->display();
            }
        } else {
            $info['img'] = $this->fetchImage($info['cover_id']);
            if($version=="2.0"){
                $info['content_url'] = 'app.php/public/topcontent/version/2.0/type/view/id/'.$info['id'];
                $info['share_url'] = 'http://www.hisihi.com/app.php/public/topcontent/type/view/id/'.$info['id'];
            } else {
                $info['content_url'] = 'app.php/public/topcontent/type/view/id/'.$info['id'];
            }
            $info['source_name'] = $this->getSourceName($id);
            $info['logo_pic'] = $this->getSourceLogoPic($id);
            // 是否点赞和收藏
            $info['isSupportd'] = $this->isArticleSupport($id);
            $info['isFavorited'] = $this->isArticleFavorite($id);
            // 头条点赞数
            $info['supportCount'] = $this->getArticleSupportCount($id);
            unset($info['uid']);
            unset($info['name']);
            unset($info['category_id']);
            unset($info['type']);
            unset($info['root']);
            unset($info['pid']);
            unset($info['model_id']);
            unset($info['position']);
            unset($info['link_id']);
            unset($info['cover_id']);
            unset($info['deadline']);
            unset($info['attach']);
            unset($info['extend']);
            unset($info['level']);
            unset($info['status']);
            $this->apiSuccess("获取首页顶部列表成功", null, array('TopContent' => $info));
        }
    }


    /**
     * 发送微博、评论等，不能太频繁，否则抛出异常。
     */
    protected function requireSendInterval()
    {
        //获取最后的时间
        $lastSendTime = session('last_send_time');
        if (time() - $lastSendTime < 10) {
            $this->apiError(-2,'操作太频繁，请稍后重试');
        }
    }

    protected function updateLastSendTime()
    {
        //更新最后发送时间
        session('last_send_time', time());
    }

    private function fetchImage($pic_id)
    {
        if($pic_id == null)
            return null;

        $pic_small = getThumbImageById($pic_id, 280, 160);
        //http://forum-pic.oss-cn-qingdao.aliyuncs.com/2015-07-17/55a8a7873cd94_280_160.jpg
        $pathArray = explode("_",$pic_small);
        //var_dump($pathArray);
        $pic_small = $pathArray[0].'.jpg';
//        $pic = M('Picture')->where(array('status' => 1))->field('path')->getById($pic_id);
//
//        if(!is_bool(strpos( $pic['path'],'http://'))){
//            $pic_src = $pic['path'];
//        }else{
//            $pic_src =getRootUrl(). substr( $pic['path'],1);
//            //$pic_src =getRootUrl(). $pic['path'];
//        }
//        return $pic_src;
        return $pic_small;
    }

    /**
     * 应用启动页广告
     */
    public function indexAdv($width=0, $height=0){
        if($width==0||$height==0){
            $this->apiError(-1, "未传入图片宽高参数");
        }
        $data['showAdv'] = false;
        $data['pic'] = null;
        $model = M();
        $now = time();
        $picKey = "advspic_".$width.'_'.$height;
        $result = $model->query("select ".$picKey." from hisihi_advs where ".
            "position=3 and status=1 and ".$now." between create_time and end_time order by id desc");
        if($result){
            $picID = $result[0][$picKey];
            $result = $model->query("select path from hisihi_picture where id=".$picID);
            if($result){
                $path = $result[0]['path'];
                $objKey = substr($path, 17);
                $param["bucketName"] = "advs-pic";
                $param['objectKey'] = $objKey;
                $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
                if($isExist){
                    $picUrl = "http://advs-pic.oss-cn-qingdao.aliyuncs.com/".$objKey;
                    $data['showAdv'] = true;
                    $data['pic'] = $picUrl;
                }
                $this->apiSuccess("获取广告数据成功", null, $data);
            } else {
                $this->apiSuccess("获取广告数据失败", null, $data);
            }
        } else {
            $this->apiSuccess("不存在可用的广告", null, $data);
        }
    }

    /**
     * 论坛内广告
     */
    public function forumAdv(){
        $data['showAdv'] = false;
        $data['pic'] = null;
        $model = M();
        $now = time();
        $result = $model->query("select advspic from hisihi_advs where ".
            "position=4 and status=1 and ".$now." between create_time and end_time order by id desc");
        if($result){
            $picID = $result[0]['advspic'];
            $result = $model->query("select path from hisihi_picture where id=".$picID);
            if($result){
                $path = $result[0]['path'];
                $objKey = substr($path, 17);
                $param["bucketName"] = "advs-pic";
                $param['objectKey'] = $objKey;
                $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
                if($isExist){
                    $picUrl = "http://advs-pic.oss-cn-qingdao.aliyuncs.com/".$objKey;
                    $data['showAdv'] = true;
                    $data['pic'] = $picUrl;
                }
                $this->apiSuccess("获取论坛广告数据成功", null, $data);
            } else {
                $this->apiSuccess("获取论坛广告数据失败", null, $data);
            }
        } else {
            $this->apiSuccess("不存在当前时间段的论坛广告", null, $data);
        }
    }

    /**
     * 获取公司列表banner
     * @param int $page
     * @param int $count
     */
    public function bannerlist($page=1, $count=5){
        $Document = D('Blog/Document');
        $all_list = $Document->where("category_id=47 and position='5'")->select();
        $totalCount = count($all_list);
        $list = $Document->where("category_id=47 and position='5'")->page($page, $count)->select();
        foreach($list as &$topic){
            $did = $topic['id'];
            $topic['source_name'] = $this->getSourceName($did);
            $topic['logo_pic'] = $this->getSourceLogoPic($did);
            //解析并成立图片数据
            $topic['img'] = $this->fetchImage($topic['cover_id']);
            $topic['content_url'] = 'http://www.hisihi.com/app.php/public/topcontent/version/2.0/type/view/id/'.$topic['id'];
            $topic['share_url'] = 'http://www.hisihi.com/app.php/public/topcontent/type/view/id/'.$topic['id'];
            $topic['isSupportd'] = $this->isArticleSupport($did);
            $topic['isFavorited'] = $this->isArticleFavorite($did);
            $topic['supportCount'] = $this->getArticleSupportCount($did);
            unset($topic['uid']);
            unset($topic['name']);
            unset($topic['category_id']);
            unset($topic['type']);
            unset($topic['root']);
            unset($topic['pid']);
            unset($topic['model_id']);
            #unset($topic['position']);
            unset($topic['link_id']);
            unset($topic['cover_id']);
            unset($topic['deadline']);
            unset($topic['attach']);
            unset($topic['extend']);
            unset($topic['level']);
            unset($topic['display']);
            unset($topic['comment']);
            unset($topic['status']);
            unset($topic['isrecommend']);
        }
        $this->apiSuccess("获取公司列表Banner成功", null, array('totalCount' => $totalCount,'course' => $list));
    }


}