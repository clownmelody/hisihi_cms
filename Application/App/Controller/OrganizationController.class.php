<?php
/**
 * Created by PhpStorm.
 * Author: walterYang
 * Date: 21/9/15
 * Time: 3:30 PM
 */

namespace App\Controller;

use Addons\Avatar\AvatarAddon;
use Think\Controller;
use Think\Exception;
use Think\Hook;
use Think\Model;

require_once(APP_PATH . 'User/Conf/config.php');
require_once(APP_PATH . 'User/Common/common.php');


class OrganizationController extends AppController
{
    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    /**
     * 获取短信验证码
     * @param $mobile
     */
    public function getSMS($mobile=null,$type='register'){
        if(empty($mobile)){
            $this->apiError(-1, '传入手机号为空');
        } else {
            if(!preg_match('/^1([0-9]{9})/',$mobile)){
                $this->apiError(-2, '传入手机号不符合格式');
            }
        }
        $this->isMobileExist($mobile,$type);
        $url = C('bmob_send_sms_url');
        $headers['X-Bmob-Application-Id'] = C('bmob_application_id');
        $headers['X-Bmob-REST-API-Key'] = C('bmob_api_key');
        $headers['Content-Type'] = 'application/json';
        $headerArr = array();
        foreach( $headers as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        $post_data = array('mobilePhoneNumber'=>urlencode($mobile));
        $post_data = json_encode($post_data);
        $result = $this->request_by_curl($url, $headerArr, $post_data);
        if($result){
            $this->apiSuccess('验证码发送成功');
        } else {
            $this->apiError(-1, '验证码发送失败');
        }
    }

    /**
     * web和app用户注册
     * @param $mobile
     * @param $sms_code
     * @param null $password
     */
    public function register($mobile, $sms_code, $password=null){
        if(empty($mobile)||empty($sms_code)||empty($password)){
            $this->apiError(-1, '传入参数不完整');
        }
        $url = C('bmob_verify_smscode_url').$sms_code;
        $headers['X-Bmob-Application-Id'] = C('bmob_application_id');
        $headers['X-Bmob-REST-API-Key'] = C('bmob_api_key');
        $headers['Content-Type'] = 'application/json';
        $headerArr = array();
        foreach( $headers as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        $post_data = array('mobilePhoneNumber'=>urlencode($mobile));
        $post_data = json_encode($post_data);
        $result = $this->request_by_curl($url, $headerArr, $post_data);
        if($result){
            $data['mobile'] = $mobile;
            $data['password'] = think_ucenter_md5($password, UC_AUTH_KEY);
            $data['create_time'] = time();
            $result = M('OrganizationAdmin')->data($data)->add();
            if($result){
                $auth = array(
                    'uid' => $result,
                    'mobile' => $mobile,
                    'organization_id' => 0
                );
                session('user_auth', $auth);
                session('user_auth_sign', data_auth_sign($auth));
                $extra['uid'] = $result;
                $extra['session_id'] = session_id();
                $this->apiSuccess('注册成功',null,$extra);
            } else {
                $this->apiError(-2, '注册失败');
            }
        } else {
            $this->apiError(-1, '验证码校验失败');
        }
    }

    /**
     * 重置密码
     * @param null $mobile
     * @param null $sms_code
     * @param null $password
     */
    public function resetPassword($mobile=null, $sms_code=null, $password=null){
        if(empty($mobile)||empty($sms_code)||empty($password)){
            $this->apiError(-3, '传入参数不完整');
        }
        $url = C('bmob_verify_smscode_url').$sms_code;
        $headers['X-Bmob-Application-Id'] = C('bmob_application_id');
        $headers['X-Bmob-REST-API-Key'] = C('bmob_api_key');
        $headers['Content-Type'] = 'application/json';
        $headerArr = array();
        foreach( $headers as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        $post_data = array('mobilePhoneNumber'=>urlencode($mobile));
        $post_data = json_encode($post_data);
        $result = $this->request_by_curl($url, $headerArr, $post_data);
        if($result){
            $map['mobile'] = $mobile;
            $data['password'] = think_ucenter_md5($password, UC_AUTH_KEY);
            $result = M('OrganizationAdmin')->where($map)->save($data);
            if($result!==false){
                $this->apiSuccess('重置密码成功');
            } else {
                $this->apiError(-2, '重置密码失败');
            }
        } else {
            $this->apiError(-1, '验证码校验失败');
        }
    }

    /**
     * 用户登陆
     * @param null $mobile
     * @param null $password
     */
    public function login($mobile=null, $password=null){
        if(empty($mobile)||empty($password)){
            $this->apiError(-1, '传入参数不完整');
        }
        $map['status'] = 1;
        $map['mobile'] = $mobile;
        $map['password'] = think_ucenter_md5($password, UC_AUTH_KEY);
        $user = M('OrganizationAdmin')->where($map)->find();
        if($user){
            $org_model = M('Organization');
            $org_info = $org_model->where('status=1 and id='.$user['organization_id'])->find();
            $logo = $org_info['logo'];
            if(!$logo){//返回机构默认logo
                $logo = 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            $auth = array(
                'uid' => $user['id'],
                'mobile' => $user['mobile'],
                'organization_id' => $user['organization_id']
            );
            session('user_auth', $auth);
            session('user_auth_sign', data_auth_sign($auth));
            $extra['uid'] = $user['id'];
            $extra['username'] = $user['username'];
            $extra['session_id'] = session_id();
            $extra['organization_id'] = $user['organization_id'];
            $extra['organization_name'] = $org_info['name'];
            $extra['organization_logo'] = $logo;
            $this->apiSuccess("登陆成功", null, $extra);
        } else {
            $this->apiError(-1, '用户不存在或密码错误');
        }
    }

    /**
     * 用户登出
     */
    public function logout(){
        $session_id = $_REQUEST['session_id'];
        session_id($session_id);
        session('user_auth', null);
        session('user_auth_sign', null);
        $this->apiSuccess('注销成功');
    }

    /**
     * 机构相关图片上传
     */
    public function uploadPicture(){
        //$this->requireAdminLogin();
        $Picture = D('Admin/Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        );
        if ($info) {
            foreach($info as $key=>&$value){
                $value = $value['id'];
            }
        } else {
            $this->apiError(-1,"上传机构图片失败".$Picture->getError());
        }
        $this->apiSuccess("上传机构图片成功",null,array('pictures'=>implode(',',$info)));
    }

    /**
     * 上传Logo图片
     * @author huajie <banhuajie@163.com>
     */
    public function uploadLogo(){
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
        if($info){
            $this->uploadLogoPicToOSS($info['download']['id']);
            $cdn_path = $this->fetchCdnImage($info['download']['id']);
            $extra['logo'] = array(
                'id'=>$info['download']['id'],
                'path'=>$cdn_path
            );
            $this->apiSuccess("上传Logo成功",null,$extra);
        } else {
            $this->apiError(-1,"上传Logo失败，".$Picture->getError());
        }
    }

    /**
     * 修改机构logo
     * @param int $organization_id
     * @param null $pic_url
     */
    public function updateLogo($organization_id=0, $pic_url=null){
        $this->requireAdminLogin();
        if(empty($organization_id)||empty($pic_id)){
            $this->apiError(-1, '传入参数不能为空');
        }

        /*$this->uploadLogoPicToOSS($pic_id);
        $cdn_path = $this->fetchCdnImage($pic_id);*/

        $model = M('Organization');
        $data['logo'] = $pic_url;
        $model->where('id='.$organization_id)->save($data);

        $this->apiSuccess('修改机构logo成功');
    }

    /**
     * 裁剪图片
     * @param null $picture_id
     * @param int $pointX
     * @param int $pointY
     * @param int $width
     * @param int $height
     */
    public function tailorPicture($picture_id=null,$pointX=0,$pointY=0,$width=0,$height=0){
        if(!$picture_id){
            $this->apiError(-1,'传入图片id不能为空');
        }
        $path = M('Picture')->where('id='.$picture_id)->getField('path');
        //上传裁剪的图片到OSS
        $picKey = substr($path, 17);
        $param["bucketName"] = "hisihi-other";
        $param['objectKey'] = $picKey;
        $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
        if(!$isExist){
            Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadOtherResource', $param);
            $crop_url = 'http://pic.hisihi.com/'.$picKey.'@'.$pointX.'-'.$pointY.'-'.$width.'-'.$height.'a';
        }else{
            $crop_url = 'http://pic.hisihi.com/'.$picKey.'@'.$pointX.'-'.$pointY.'-'.$width.'-'.$height.'a';
        }
        $extra['crop_url'] = $crop_url;
        $this->apiSuccess("裁剪成功",null,$extra);
    }

    /**
     * 新增或修改机构基本信息
     * @param int $organization_id
     * @param null $name
     * @param null $slogan
     * @param null $introduce
     * @param null $logo
     * @param null $advantage
     * @param null $location
     * @param null $phone_num
     */
    public function saveBaseInfo($organization_id=0, $name=null, $slogan=null, $introduce=null, $logo=null,
                                 $advantage=null, $location=null, $phone_num=null){
        $this->requireAdminLogin();
        $model = M('Organization');
        if(!empty($name)){
            $data['name'] = $name;
        }
        if(!empty($slogan)){
            $data['slogan'] = $slogan;
        }
        if(!empty($introduce)){
            $data['introduce'] = $introduce;
        }
        if(!empty($logo)){
            $this->uploadLogoPicToOSS($logo);
            $cdn_path = $this->fetchCdnImage($logo);
            $data['logo'] = $cdn_path;
        }
        if(!empty($advantage)){
            $data['advantage'] = $advantage;
        }
        if(!empty($location)){
            $data['location'] = $location;
        }
        if(!empty($phone_num)){
            $data['phone_num'] = $phone_num;
        }
        if(!$organization_id){  // 新增结构基本信息
            $result = $model->data($data)->add();
            if($result){
                $uid = is_login();
                $res = M('OrganizationAdmin')->where(array('id'=>$uid,'status'=>1))->save(array('organization_id'=>$result));
                if($res){
                    $extra['organization_id'] = $result;
                    $extra['organization_name'] = $name;
                    $url = $this->fetchCdnImage($logo);
                    if(!$url){
                        $url = 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
                    }
                    $extra['logo'] = $url;
                    $this->apiSuccess('添加机构基本信息成功',null,$extra);
                }else{
                    $this->apiError(-2, '关联机构信息到管理员失败，请重试');
                }
            } else {
                $this->apiError(-1, '添加机构基本信息失败，请重试');
            }
        } else {  // 修改机构基本信息
            $model->where('id='.$organization_id)->save($data);
            $this->apiSuccess('修改机构基本信息成功');
        }
    }

    /**
     * 获取机构基本信息
     * @param $organization_id
     */
    public function getBaseInfo($organization_id){
        //$this->requireAdminLogin();
        $model=M("Organization");
        $result = $model->where(array('id'=>$organization_id,'status'=>1))
            ->field('name,slogan,location,logo,introduce,advantage,phone_num,location_img')->find();
        if($result){
            $logo = $result['logo'];
            //$logo = $this->getOrganizationLogo($logo_id);
            if(!$logo){
                $logo = 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            $result['logo'] = $logo;
            $advantage = $result['advantage'];
            $result['advantage']=$advantage;
            $extra['data'] = $result;
            $this->apiSuccess("获取机构信息成功",null,$extra);
        }else{
            $this->apiError(-1,"获取机构信息失败");
        }
    }

    /**
     * app获取机构基本信息
     * @param $organization_id
     */
    public function appGetBaseInfo($organization_id, $uid=0,$type=null){
        if($uid==0){
            $uid = $this->getUid();
        }
        $model=M("Organization");
        $result = $model->where(array('id'=>$organization_id,'status'=>1))
            ->field('name,slogan,location,logo,introduce,advantage,view_count,guarantee_num,light_authentication,location_img')->find();
        if($result){
            $logo = $result['logo'];
            //$logo = $this->getOrganizationLogo($logo_id);
            if(!$logo){
                $logo='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            $result['ViewCount'] = $result['view_count'];//兼容iOS老版本
            $result['phone_num'] = $this->get400PhoneNum();
            $result['logo'] = $logo;
            $result['authenticationInfo'] = $this->getAuthenticationInfo($organization_id);
            $result['followCount'] = $this->getFollowCount($organization_id);
            $result['teachersCount'] = $this->getTeachersCount($organization_id);
            $result['groupCount'] = $this->getGroupCount($organization_id);
            $user['info'] = query_user(array('avatar256', 'avatar128', 'group', 'extinfo', 'nickname'), $uid);
            $follow_other = D('Follow')->where(array('who_follow'=>$uid,'follow_who'=>$organization_id, 'type'=>2))->find();
            $be_follow = D('Follow')->where(array('who_follow'=>$organization_id,'follow_who'=>$uid, 'type'=>2))->find();
            if($follow_other&&$be_follow){
                $result['relationship'] = 3;
            } else if($follow_other&&(!$be_follow)){
                $result['relationship'] = 2;
            } else if((!$follow_other)&&$be_follow){
                $result['relationship'] = 1;
            } else {
                $result['relationship'] = 0;
            }
            $advantage = $result['advantage'];
            $enroll_ = M('OrganizationEnroll')->distinct(true)->field('student_uid')->where(array('organization_id'=>$organization_id,'status'=>array('gt',0)))->select();
            $enroll_count = count($enroll_);
            $result['available_num'] = $result['guarantee_num'] - $enroll_count;
            $result['advantage']=$advantage;
            $relationModel = M('OrganizationRelation');
            $isExist = $relationModel->where('status=1 and organization_id='.$organization_id.' and uid='.$uid)->find();
            if(!$isExist){
                $result['isStudent']=false;
            }else{
                $result['isStudent']=true;
            }
            if($type=="view"){
                return $result;
            }else{
                $extra['data'] = $result;
                //$model->where('id='.$organization_id)->setInc('view_count');
                $this->apiSuccess("获取机构信息成功",null,$extra);
            }
        }else{
            $this->apiError(-1,"获取机构信息失败");
        }
    }

    /**
     * 获取机构通用优势标签
     */
    public function getCommonAdvantageTags(){
        $model = M('OrganizationTag');
        $list = $model->field('id, value')->where('type=1 and status=1')->select();
        $extra['totalCount'] = count($list);
        $extra['data'] = $list;
        $this->apiSuccess('获取推荐优势标签成功', null, $extra);
    }

    /**
     * 获取机构自己的标签
     * @param null $organization_id
     */
    public function getOrganizationAdvantageTags($organization_id=null){
        $this->requireAdminLogin();
        $model = M('Organization');
        $res = $model->field('advantage')->where('id='.$organization_id)->find();
        if($res){
            $advantage = $res['advantage'];
            $extra['advantage'] = $advantage;
            $this->apiSuccess('获取机构优势标签成功', null, $extra);
        } else {
            $this->apiError(-1, '获取数据异常');
        }
    }

    /**
     * 保存机构优势标签
     * @param null $organization_id
     * @param null $advantage
     */
    public function saveOrganizationAdvantageTags($organization_id=null, $advantage=null){
        $this->requireAdminLogin();
        $model = M('Organization');
        $data['advantage'] = $advantage;
        $model->where('id='.$organization_id)->save($data);
        $this->apiSuccess('保存机构优势标签成功');
    }

    /**
     * 机构web版获取机构公告
     * @param int $page
     * @param int $count
     */
    public function getNotice($page=1, $count=10){
        $this->requireAdminLogin();
        $model = M('OrganizationNotice');
        $totalCount = $model->where('status=1')->count();
        $list = $model->where('status=1')->page($page, $count)->select();
        foreach($list as &$notice){
            $notice['detail_url'] = C('HOST_NAME_PREFIX').'api.php?s=/organization/noticedetail/id/'.$notice['id'];
            unset($notice['content']);
            unset($notice['status']);
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $list;
        $this->apiSuccess('获取公告信息列表成功', null, $extra);
    }

    /**
     * 机构公告详情
     * @param int $id
     */
    public function noticeDetail($id=0){
        if(empty($id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $model = M('OrganizationNotice');
        $notice = $model->where('id='.$id)->find();
        $this->assign('notice', $notice);
        $this->display('noticedetail');
    }

    /**
     * 学生作品添加或删除
     * @param null id
     * @param null $organization_id
     * @param null $pic_id
     * @param null $description
     * @param string $type
     */
    public function studentWorks($id=null, $organization_id=null, $pic_id=null, $description=null, $type='add'){
        $this->requireAdminLogin();
        $model = M('OrganizationResource');
        if('add'==$type){  // 添加学生作品
            if(empty($organization_id)||empty($pic_id)){
                $this->apiError(-1, '传入参数不能为空');
            }

            $this->uploadLogoPicToOSS($pic_id);
            $url = $this->fetchCdnImage($pic_id);

            $data['type'] = 1;
            $data['organization_id'] = $organization_id;
            //$data['pic_id'] = $pic_id;
            $data['url'] = $url;
            $data['description'] = $description;
            $data['create_time'] = time();
            //getThumbImageById($pic_id,280,160);
            $res = $model->add($data);
            if($res){
                $extra['works_id'] = $res;
                $this->apiSuccess('添加学生作品成功',null,$extra);
            } else {
                $this->apiError(-1, '添加学生作品失败');
            }
        } else {  // 删除学生作品
            if(empty($id)){
                $this->apiError(-1, '传入id参数不能为空');
            }
            $data['status'] = -1;
            $map['id'] = $id;
            $res = $model->where($map)->save($data);
            if($res){
                $this->apiSuccess('删除学生作品成功');
            } else {
                $this->apiError(-1, '删除学生作品失败');
            }
        }
    }

    /**
     * 机构环境图片添加或删除
     * @param null $id
     * @param null $organization_id
     * @param null $pic_id
     * @param null $description
     * @param string $type
     */
    public function organizationEnvironment($id=null, $organization_id=null, $pic_id=null, $description=null, $type='add'){
        $this->requireAdminLogin();
        $model = M('OrganizationResource');
        if('add'==$type){
            if(empty($organization_id)||empty($pic_id)){
                $this->apiError(-1, '传入参数不能为空');
            }

            $url = $this->fetchCdnImage($pic_id);

            $data['type'] = 2;
            $data['organization_id'] = $organization_id;
            //$data['pic_id'] = $pic_id;
            $data['url'] = $url;
            $data['description'] = $description;
            $data['create_time'] = time();
            //getThumbImageById($pic_id,280,160);
            $res = $model->add($data);
            if($res){
                $extra['environment_id'] = $res;
                $this->apiSuccess('添加机构环境图片成功',null,$extra);
            } else {
                $this->apiError(-1, '添加机构环境图片失败');
            }
        } else {
            if(empty($id)){
                $this->apiError(-1, '传入id参数不能为空');
            }
            $data['status'] = -1;
            $map['id'] = $id;
            $res = $model->where($map)->save($data);
            if($res){
                $this->apiSuccess('删除机构环境图片成功');
            } else {
                $this->apiError(-1, '删除机构环境图片失败');
            }
        }
    }

    /**
     * 更新图片描述
     * @param int $id
     * @param string $description
     */
    public function updatePictureDescription($id=0,$description=''){
        $this->requireAdminLogin();
        if(!$id){
            $this->apiError(-1,"参数ID不能为空");
        }
        $model = M('OrganizationResource');
        $data['description'] = $description;
        $map['status'] = 1;
        $map['id'] = $id;
        $result = $model->where($map)->save($data);
        if($result){
            $this->apiSuccess("修改成功");
        }else{
            $this->apiError(-1,"修改失败");
        }
    }

    /**
     * 获取学生作品
     * @param null $organization_id
     * @param int $page
     * @param int $count
     */
    public function getStudentWorks($organization_id=null, $page=1, $count=12){
        $model = M('OrganizationResource');
        $map['organization_id'] = $organization_id;
        $map['type'] = 1;
        $map['status'] = 1;
        $totalCount = $model->where($map)->count();
        $list = $model->field('id, url, description, create_time')->order('create_time desc')->where($map)->page($page, $count)->select();
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $list;
        $this->apiSuccess('获取机构学生作品成功', null, $extra);
    }

    /**
     * 获取机构环境图片
     * @param null $organization_id
     * @param int $page
     * @param int $count
     */
    public function getOrganizationEnvironment($organization_id=null, $page=1, $count=12){
        $model = M('OrganizationResource');
        $map['organization_id'] = $organization_id;
        $map['type'] = 2;
        $map['status'] = 1;
        $totalCount = $model->where($map)->count();
        $list = $model->field('id, url, description, create_time')->order('create_time desc')->where($map)->page($page, $count)->select();
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $list;
        $this->apiSuccess('获取机构环境图片成功', null, $extra);
    }

    /**
     * 获取所有老师列表
     * @param int $page
     * @param int $count
     */
    public function teachersList($page=1, $count=10){
        $model = M('AuthGroupAccess');
        $list = $model->where('group_id=6')->page($page, $count)->select();
        $teacher_list = array();
        foreach($list as $access){
            $uid = $access['uid'];
            $user = D('User/Member')->where(array('uid' => $uid))->find();
            $nickname = $user['nickname'];
            $avatar = new AvatarAddon();
            $avatar_path = $avatar->getAvatarPath($uid);
            $avatar128_path = getThumbImage($avatar_path, 128);
            $teacher['uid'] = $uid;
            $teacher['nickname'] = $nickname;
            $teacher['avatar'] = $avatar128_path['src'];
            array_push($teacher_list, $teacher);
        }
        $extra['data'] = $teacher_list;
        $this->apiSuccess('获取老师列表成功', null, $extra);
    }

    /**
     * 根据教师名字进行模糊搜索
     * @param null $name
     */
    public function teachersFilter($name=null){
        if(empty($name)){
            $this->apiError(-1, '传入参数为空');
        }
        $map['nickname'] = array('like', $name.'%');
        $map['status'] = 1;
        $list = D('User/Member')->field('uid, nickname')->where($map)->select();
        $a_model = M('AuthGroupAccess');
        $t_list = array();
        foreach($list as &$user){
            $uid = $user['uid'];
            $r_res = $a_model->where('group_id=6 and uid='.$uid)->find();
            if($r_res){
                $avatar = new AvatarAddon();
                $avatar_path = $avatar->getAvatarPath($uid);
                $avatar128_path = getThumbImage($avatar_path, 128);
                $user['avatar'] = $avatar128_path['src'];
                array_push($t_list, $user);
            }
        }
        $extra['data'] = $t_list;
        $this->apiSuccess('搜索教师成功', null, $extra);
    }

    /**
     * 新增老师分组
     * @param null $organization_id
     * @param null $group_name
     */
    public function addTeachersGroup($organization_id=null, $group_name=null){
        $this->requireAdminLogin();
        $model = M('OrganizationLectureGroup');
        $data['organization_id'] = $organization_id;
        $data['title'] = $group_name;
        $data['create_time'] = time();
        $result = $model->data($data)->add();
        if($result){
            $extra['id'] = $result;
            $this->apiSuccess('新增分组成功', null, $extra);
        } else {
            $this->apiError(-1, '新增分组失败');
        }
    }

    /**
     * 修改老师分组名称
     * @param null $teacher_group_id
     * @param null $group_name
     */
    public function updateTeachersGroup($teacher_group_id=null, $group_name=null){
        $this->requireAdminLogin();
        $model = M('OrganizationLectureGroup');
        $data['title'] = $group_name;
        $result = $model->where('id='.$teacher_group_id)->save($data);
        if($result){
            $this->apiSuccess('修改成功');
        } else {
            $this->apiError(-1, '修改失败');
        }
    }

    /**
     * 删除老师分组
     * @param null $teacher_group_id
     */
    public function deleteTeachersGroup($teacher_group_id=null){
        $this->requireAdminLogin();
        $model = M('OrganizationLectureGroup');
        $data['status'] = -1;
        $result = $model->where('id='.$teacher_group_id)->save($data);
        $organization_id = $model->where(array('id'=>$teacher_group_id))->getField('organization_id');
        $res = M('OrganizationRelation')->where(array('organization_id'=>$organization_id,'teacher_group_id'=>$teacher_group_id))->save($data);
        if($result && $res){
            $this->apiSuccess('删除成功');
        } else {
            $this->apiError(-1, '删除失败');
        }
    }

    /**
     * 添加教师到分组
     * @param null $teacher_group_id
     * @param null $uid
     * @param null $organization_id
     */
    public function addTeacherToGroup($uid=null, $organization_id=null, $teacher_group_id=null){
        $this->requireAdminLogin();
        if(!is_numeric($uid) || !is_numeric($organization_id) || !is_numeric($teacher_group_id)){
            $this->apiError(-4, '请求参数错误');
        }
        $model = M('OrganizationRelation');
        $data['uid'] = $uid;
        $data['teacher_group_id'] = $teacher_group_id;
        $data['organization_id'] = $organization_id;
        $data['group'] = 6;
        $data['status'] = 1;
        if(!M('Member')->where('uid='.$uid)->count()){
            $this->apiError(-3, '该老师不存在');
        }
        if($model->where('`status`=1 and `group`=6 and `uid`='.$uid.' and `organization_id`='.$organization_id)->count()){
            $this->apiError(-2, '该老师已经添加过了');
        }
        if($model->where('`status`=1 and `group`=6 and `uid`='.$uid.' and `organization_id`<>'.$organization_id)->count()){
            $this->apiError(-5, '该老师已加入其他机构');
        }
        $result = $model->add($data);
        if($result){
            $extra['relation_id'] = $result;
            $this->apiSuccess("添加成功",null,$extra);
        }else{
            $this->apiError(-1, '新增教师到分组失败');
        }
    }

    /**
     * 从分组中移除老师
     * @param $relation_id
     */
    public function deleteTeacherFromGroup($relation_id=null){
        $this->requireAdminLogin();
        $model = M('OrganizationRelation');
        $data['status'] = -1;
        if(!$model->where('status=1 and id='.$relation_id)->count()){
            $this->apiError(-2, '该老师不存在');
        }
        $result = $model->where('id='.$relation_id)->save($data);
        if($result){
            $this->apiSuccess('删除成功');
        } else {
            $this->apiError(-1, '删除失败');
        }
    }

    /**
     * 获取当前机构所有分组信息
     * @param null $organization_id
     */
    public function getAllGroups($organization_id=null){
        $this->requireAdminLogin();
        $model = M('OrganizationLectureGroup');
        $list = $model->field('id, title')->where('status=1 and organization_id='.$organization_id)->select();
        $extra['data'] = $list;
        $this->apiSuccess('获取机构所有分组成功', null, $extra);
    }

    /**
     * 获取所有分组和老师信息
     * @param null $organization_id
     */
    public function getAllGroupsTeachers($organization_id=null){
        $this->requireAdminLogin();
        $model = M('OrganizationLectureGroup');
        $list = $model->field('id, title')->where('status=1 and organization_id='.$organization_id)->select();
        $t_model = M('OrganizationRelation');
        $all_list = array();
        foreach($list as &$group){
            $id = $group['id'];
            $map['group'] = 6;
            $map['status'] = 1;
            $map['organization_id'] = $organization_id;
            $map['teacher_group_id'] = $id;
            $u_list = $t_model->field('id,uid')->where($map)->select();
            $teacher_list = array();
            foreach ($u_list as $user) {
                $uid = $user['uid'];
                $relation_id = $user['id'];
                $user = D('User/Member')->where(array('uid' => $uid))->find();
                $nickname = $user['nickname'];
                $avatar = new AvatarAddon();
                $avatar_path = $avatar->getAvatarPath($uid);
                $avatar128_path = getThumbImage($avatar_path, 128);
                $teacher['uid'] = $uid;
                $teacher['relation_id'] = $relation_id;
                $teacher['nickname'] = $nickname;
                $teacher['avatar'] = $avatar128_path['src'];
                array_push($teacher_list, $teacher);
            }
            $obj['group_info'] = array(
                'group_id' => $group['id'],
                'group_name'=> $group['title']
            );
            $obj['teachers'] = $teacher_list;
            array_push($all_list, $obj);
        }
        $extra['data'] = $all_list;
        $this->apiSuccess('获取机构所有分组教师列表', null, $extra);
    }

    /**
     *获取视频分类
     */
    public function getVideoCategory(){
        /*$model = M("OrganizationTag");
        $result = $model->where('status=1 and type=5')->field('id,value')->select();
        if($result){
            $extra['data'] = $result;
            $this->apiSuccess('获取视频分类列表成功', null, $extra);
        } else {
            $this->apiError(-1,"获取视频分类列表失败");
        }*/
        $model = M("Issue");
        $result = $model->where('status=1 and pid=0')->field('id, title as value')->select();
        if($result){
            $extra['data'] = $result;
            $this->apiSuccess('获取视频分类列表成功', null, $extra);
        } else {
            $this->apiError(-1,"获取视频分类列表失败");
        }
    }

    /**
     * 添加机构课程
     * @param null $id
     * @param null $organization_id
     * @param null $title
     * @param null $content
     * @param null $category_id
     * @param null $img
     * @param null $lecturer
     * @param int $auth
     */
    public function addCourse($id = null,$organization_id=null, $title=null, $content=null,$category_id=null, $img=null, $lecturer=null, $auth=1){
        $this->requireAdminLogin();
        $model = M('OrganizationCourse');
        if(!empty($id)){
            $this->uploadLogoPicToOSS($img);
            $img_str = $this->fetchCdnImage($img);
            $data['organization_id'] = $organization_id;
            $data['title'] = $title;
            $data['content'] = $content;
            $data['category_id']=$category_id;
            $data['lecturer'] = $lecturer;
            $data['auth'] = $auth;
            $data['update_time'] = time();
            $data['img_str'] = $img_str;
            $result = $model->where(array('id'=>$id,'status'=>1))->save($data);
            if($result){
                $extra['courses_id'] = $id;
                $this->apiSuccess('修改课程成功',null,$extra);
            } else {
                $this->apiError(-1, '修改课程信息失败');
            }
        }else{
            $this->uploadLogoPicToOSS($img);
            $img_str = $this->fetchCdnImage($img);
            $data['organization_id'] = $organization_id;
            $data['title'] = $title;
            $data['content'] = $content;
            $data['category_id']=$category_id;
            $data['lecturer'] = $lecturer;
            $data['auth'] = $auth;
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['img_str'] = $img_str;
            $result = $model->add($data);
            if($result){
                $extra['courses_id'] = $result;
                $this->apiSuccess('添加课程成功',null,$extra);
            } else {
                $this->apiError(-1, '添加课程信息失败');
            }
        }
    }

    /**
     * 获取当前机构的所有课程
     * @param null $organization_id
     * @param int $page
     * @param int $count
     */
    public function getCourses($organization_id=null, $page=1, $count=9){
        //$this->requireAdminLogin();
        $model = M('OrganizationCourse');
        $config_model = M("OrganizationTag");
        $map['organization_id'] = $organization_id;
        $map['status'] = 1;
        $totalCount = $model->where($map)->count();
        $course_list = $model->field('id, title, content, category_id, view_count, lecturer, auth, create_time, img_str')->order('create_time desc')->where($map)->page($page, $count)->select();
        $video_course = array();
        foreach($course_list as &$course){
            $category_id = $course['category_id'];
            $category = $config_model->where('status=1 and type=5 and id='.$category_id)->field('value')->find();
            if($category){
                $course['category_name'] = $category['value'];
            }
            //$course['url'] = $this->fetchImage($course['img']);
            $oss_pic_pre = 'http://game-pic.oss-cn-qingdao.aliyuncs.com/';
            if(strpos($course['img_str'], 'OSS')){
                $course['url'] = str_replace('OSS-', $oss_pic_pre, $course['img_str']);
            } else {
                $course['url'] = $course['img_str'];
            }
            unset($course['img_str']);
            $video_course[] = $course;
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $video_course;
        $this->apiSuccess('获取所有课程成功', null, $extra);
    }

    /**
     * 获取机构课程列表，展示用
     * @param null $organization_id
     * @param null $type_id
     * @param null $courses_id
     * @param null $order
     * @param null $type
     * @param int $page
     * @param int $count
     * @return array
     */
    public function appGetCoursesList($organization_id=null,$type_id=null,$courses_id=null,$order=null,$type=null, $page=1, $count=5){
        $model = M('OrganizationCourse');
        $issue_model = M('Issue');
        if($organization_id){//按机构查询/默认全部
            $map['organization_id'] = $organization_id;
        }
        if($type_id){//按分类查询
            $issueType = $issue_model->field('pid')->find($type_id);
            if(!$issueType)
                $this->apiError(-404, '未找到该课程分类！');
            if($issueType['pid'] == 0){
                $issueTypeList = $issue_model->field('id')->where('pid='.$type_id)->select();
                $issueTypeIds[] = $type_id;
                foreach($issueTypeList as $issueType){
                    $issueTypeIds[] = $issueType['id'];
                }
                $ids= implode(',',$issueTypeIds);
                $map['category_id'] = array('in',$ids);
            } else {
                $map['category_id'] = $type_id;
            }
        }
        $order = op_t($order);
        if ($order == 'view') {//排序
            $order = 'view_count desc';
        } else {
            $order = 'create_time desc';//默认的
        }
        if($courses_id){//相关推荐
            $course = $model->field('category_id')->find($courses_id);
            if(!$course){
                $this->apiError(-404, '未找到该课程！');
            }
            $map['id'] = array('neq' , $courses_id);
            $map['category_id'] = $course['category_id'];
        }
        if($type=='private') {//视频回放
            $enrollModel = M('OrganizationEnroll');
            $org_ids = $enrollModel->field('organization_id')->where('status=2 and student_uid='.$this->getUid())->select();
            if(!$org_ids){
                $this->apiError(-2, '你还未报名任何机构');
            }
            $enroll_org = array();
            foreach($org_ids as &$org_id){
                $enroll_org[] = $org_id['organization_id'];
            }
            $map['organization_id'] = array('in', $enroll_org);
            $map['auth'] = 2;
        }else{
            $map['auth'] = 1;
        }

        $map['status'] = 1;
        $totalCount = $model->where($map)->count();
        $course_list = $model->field('id')
            ->order($order)->where($map)->page($page, $count)->select();
        $video_course = array();
        foreach($course_list as &$course){
            $course = $this->findCoursesById($course['id']);
            $video_course[] = $course;
        }
        if($type=='view'){
            return $video_course;
        }else{
            $extra['total_count'] = $totalCount;
            $extra['coursesList'] = $video_course;
            $this->apiSuccess('获取机构课程成功', null, $extra);
        }
    }

    /**
     * 返回收藏列表数据
     * @param null $courses_id
     * @return mixed
     */
    public function findCoursesById($courses_id=null){
        if(empty($courses_id)){
            $this->apiError(-1,"传入课程id不能为空");
        }
        $org_model = M('Organization');
        $model = M('OrganizationCourse');
        $video_model = M('OrganizationVideo');
        $issue_model = M('Issue');
        $member_model = M('Member');
        $map['id'] = $courses_id;
        $map['status'] = 1;
        $course = $model->field('id, organization_id, title, category_id, view_count, lecturer,img_str')
            ->where($map)->find();
        if(!$course){
            return null;
        }
        $category_id = $course['category_id'];
        $course['ViewCount'] = $course['view_count'];
        $course['type'] = $issue_model->where('id='.$category_id)->getField('title');
        $course['lecturer_name'] = $member_model->where('uid='.$course['lecturer'])->getField('nickname');
        //解析并生成图片数据
        $oss_pic_pre = 'http://game-pic.oss-cn-qingdao.aliyuncs.com/';
        if(substr_count($course['img_str'], 'OSS')){
            $course['img'] = str_replace('OSS-', $oss_pic_pre, $course['img_str']);
        } else {
            $course['img'] = $course['img_str'];
        }
        $course['organization_logo'] = $org_model->where(array('id'=>$course['organization_id'],'status'=>1))->getField('logo');
        $course_duration = $video_model->where(array('course_id'=>$course['id'],'status'=>1))->sum('duration');
        $course['duration'] = $course_duration;

        unset($course['category_id']);
        unset($course['img_str']);
        unset($course['view_count']);
        unset($course['organization_id']);

        return $course;
    }

    /**
     * 课程收藏
     * @param int $uid
     * @param int $courses_id
     */
    public function doFavoriteCourses($uid=0, $courses_id=0){
        if(empty($courses_id)){
            $this->apiError(-1, '传入课程id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }

        $favorite['appname'] = 'Organization';
        $favorite['table'] = 'organization_courses';
        $favorite['row'] = $courses_id;
        $favorite['uid'] = $uid;
        $favorite_model = M('Favorite');
        if ($favorite_model->where($favorite)->count()) {
            $this->apiError(-100,'您已经收藏，不能再收藏了!');
        } else {
            $favorite['create_time'] = time();
            if ($favorite_model->where($favorite)->add($favorite)) {
                $this->apiSuccess('收藏成功');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        }
    }

    /**取消收藏机构课程
     * @param int $uid
     * @param int $courses_id
     */
    public function undoFavoriteCourses($uid=0,$courses_id=0){
        if(empty($courses_id)){
            $this->apiError(-1, '传入课程id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }

        $favorite['appname'] = 'Organization';
        $favorite['table'] = 'organization_courses';
        $favorite['row'] = $courses_id;
        $favorite['uid'] = $uid;
        $favorite_model = M('Favorite');
        if (!$favorite_model->where($favorite)->count()) {
            $this->apiError(-102,'您还没有收藏，不能取消收藏!');
        } else {
            if ($favorite_model->where($favorite)->delete()) {
                $this->clearCache($favorite,'favorite');
                $this->apiSuccess('取消收藏成功');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        }
    }

    /**
     * 课程点赞
     * @param int $uid
     * @param int $courses_id
     */
    public function doSupportCourses($uid=0, $courses_id=0){
        if(empty($courses_id)){
            $this->apiError(-1, '传入课程id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }
        $courses = M('OrganizationCourse')->where(array('id'=>$courses_id,'status'=>1))
            ->field('is_old_hisihi_data,issue_content_id')->find();
        if($courses['is_old_hisihi_data']){
            $favorite['appname'] = 'Issue';
            $favorite['table'] = 'issue_content';
            $favorite['row'] = $courses['issue_content_id'];
        }else{
            $favorite['appname'] = 'Organization';
            $favorite['table'] = 'organization_courses';
            $favorite['row'] = $courses_id;
        }
        $favorite['uid'] = $uid;
        $favorite_model = M('Support');
        if ($favorite_model->where($favorite)->count()) {
            $this->apiError(-100,'您已经点赞了，不能再点赞了!');
        } else {
            $favorite['create_time'] = time();
            if ($favorite_model->where($favorite)->add($favorite)) {
                $this->apiSuccess('感谢您的支持');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        }
    }

    /**
     * 取消点赞机构课程
     * @param int $uid
     * @param int $courses_id
     */
    public function undoSupportCourses($uid=0,$courses_id=0){
        if(empty($courses_id)){
            $this->apiError(-1, '传入课程id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }
        $courses = M('OrganizationCourse')->where(array('id'=>$courses_id,'status'=>1))
            ->field('is_old_hisihi_data,issue_content_id')->find();
        if($courses['is_old_hisihi_data']){
            $favorite['appname'] = 'Issue';
            $favorite['table'] = 'issue_content';
            $favorite['row'] = $courses['issue_content_id'];
        }else{
            $favorite['appname'] = 'Organization';
            $favorite['table'] = 'organization_courses';
            $favorite['row'] = $courses_id;
        }
        $favorite['uid'] = $uid;
        $favorite_model = M('Support');
        if (!$favorite_model->where($favorite)->count()) {
            $this->apiError(-102,'您还没有点赞，不能取消点赞!');
        } else {
            if ($favorite_model->where($favorite)->delete()) {
                $this->clearCache($favorite,'favorite');
                $this->apiSuccess('取消点赞成功');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        }
    }

    /**
     * 删除课程
     * @param int $id
     */
    public function deleteCourses($id=0){
        $this->requireAdminLogin();
        if(!$id) {
            $this->apiError(-1,"参数不能为空");
        } else {
            $model = M('OrganizationCourse');
            $data['status'] = -1;
            $result = $model->where(array('id'=>$id))->save($data);
            $res = M('OrganizationVideo')->where(array('course_id'=>$id))->save($data);
            if($result && $res){
                $this->apiSuccess("删除成功");
            }else{
                $this->apiError(-1,"删除失败");
            }
        }
    }

    /**
     * 获取课程下视频列表
     * @param null $course_id
     */
    public function getCourseVideoList($course_id=null){
        $this->requireAdminLogin();
        if(empty($course_id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $model = M('OrganizationCourse');
        $video_model = M('OrganizationVideo');
        $config_model = M("OrganizationTag");
        $member_model = M("Member");
        $map['id'] = $course_id;
        $map['status'] = 1;
        $course = $model->field('id, title, content, category_id, lecturer, auth, img_str')->where($map)->find();
        $category_id = $course['category_id'];
        $category = $config_model->where('status=1 and type=5 and id='.$category_id)->field('value')->find();
        if($category){
            $course['category_name'] = $category['value'];
        }
        $oss_pic_pre = 'http://game-pic.oss-cn-qingdao.aliyuncs.com/';
        if(substr_count($course['img_str'], 'OSS')){
            $course['img_url'] = str_replace('OSS-', $oss_pic_pre, $course['img_str']);
        } else {
            $course['img_url'] = $course['img_str'];
        }
        unset($course['img_str']);
        $teacher_id = $course['lecturer'];
        $teacher = $member_model->where('status=1 and uid='.$teacher_id)->find();
        $course['teacher_name'] = $teacher['nickname'];
        $course_id = $course['id'];
        $v_map['course_id'] = $course_id;
        $v_map['status'] = 1;
        $video_list = $video_model->field('id, name, view_count, create_time')->where($v_map)->select();
        $course['video'] = $video_list;
        $extra['data'] = $course;
        $this->apiSuccess('获取课程视频列表成功', null, $extra);
    }

    /**
     * 当前用户登录状态检测
     */
    private function requireAdminLogin(){
        $uid = $this->isLogin();
        if(!$uid) {
            $this->apiError(401,"需要登录");
        }
    }

    /**
     * @return int
     */
    private function isLogin(){
        $session_id = $_REQUEST['session_id'];
        session_id($session_id);
        $id = get_uid();
        return $id;
    }

    /**
     * 检查手机号是否已经被注册
     * @param $mobile
     */
    private function isMobileExist($mobile,$type){
        if(empty($mobile)){
            $this->apiError(-1, "传入参数为空");
        }
        $map['status'] = 1;
        $map['mobile'] = $mobile;
        $user = M('OrganizationAdmin')->where($map)->find();
        if($user){
            if($type == 'register'){
                $extraData['isExist'] = true;
                $this->apiSuccess("该手机号已注册", null, $extraData);
            }
        }else{
            if($type != 'register'){
                $extraData['isExist'] = false;
                $this->apiSuccess("该手机号未注册", null, $extraData);
            }
        }
    }

    /**
     * 定位到城市
     */
    public function location(){
        $ip = get_client_ip();
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/lbswebapi/iplocation?ip='.$ip;
        $header = array(
            'apikey: e1edb99789e6a40950685b5e3f0ee282',
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        $res = json_decode($res);
        if($res->errNum==0){
            $data['city'] = $res->retData->content->address_detail->city;
            $data['city'] = mb_substr($data['city'], 0, mb_strlen($data['city'], "UTF-8")-1, "UTF-8");
            $this->apiSuccess('获取位置成功', null, $data);
        } else {
            $data['city'] = '武汉';
            $this->apiSuccess('定位失败，返回默认城市', null, $data);
        }
    }

    /**
     * 定位到省份
     */
    public function locationToProvince(){
        $ip = get_client_ip();
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/lbswebapi/iplocation?ip='.$ip;
        $header = array(
            'apikey: e1edb99789e6a40950685b5e3f0ee282',
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        $res = json_decode($res);
        if($res->errNum==0){
            $data['province'] = $res->retData->content->address_detail->province;
            $this->apiSuccess('获取位置成功', null, $data);
        } else {
            $data['province'] = '湖北省';
            $this->apiSuccess('定位失败，返回默认城市', null, $data);
        }
    }

    /**
     * 获取城市列表数据
     */
    public function getCityList(){
        $list = json_decode('[
	{
        "city":"\u6B66\u6C49",
        "code":"101200101"
    },
    {
        "city":"\u5317\u4EAC",
        "code":"101010100"
    },
    {
        "city":"\u4E0A\u6D77",
        "code":"101020100"
    },
    {
        "city":"\u5E7F\u5DDE",
        "code":"101280101"
    },
    {
        "city":"\u6DF1\u5733",
        "code":"101280601"
    },
    {
        "city":"\u5357\u4EAC",
        "code":"101190101"
    },
    {
        "city":"\u91CD\u5E86",
        "code":"101040100"
    },
    {
        "city":"\u5929\u6D25",
        "code":"101030100"
    },
    {
        "city":"\u5927\u8FDE",
        "code":"101070201"
    },
    {
        "city":"\u6C88\u9633",
        "code":"101070101"
    },
    {
        "city":"\u6210\u90FD",
        "code":"101270101"
    },
    {
        "city":"\u957F\u6C99",
        "code":"101250101"
    },
    {
        "city":"\u9752\u5C9B",
        "code":"101120201"
    },
    {
        "city":"\u676D\u5DDE",
        "code":"101210101"
    },
    {
        "city":"\u53A6\u95E8",
        "code":"101230201"
    },
    {
        "city":"\u897F\u5B89",
        "code":"101110101"
    },
    {
        "city":"\u90D1\u5DDE",
        "code":"101180101"
    },
    {
        "city":"\u5357\u660C",
        "code":"101240101"
    },
    {
        "city":"\u5408\u80A5",
        "code":"101220101"
    },
    {
        "city":"\u4E34\u6C82",
        "code":"101120901"
    },
    {
        "city":"\u6D4E\u5357",
        "code":"101120101"
    },
    {
        "city":"\u54C8\u5C14\u6EE8",
        "code":"101050101"
    },
	{
        "city":"\u8D35\u9633",
        "code":"101260101"
    },
	{
        "city":"\u592A\u539F",
        "code":"101100101"
    },
	{
        "city":"\u5170\u5DDE",
        "code":"101160101"
    },
	{
        "city":"\u77F3\u5BB6\u5E84",
        "code":"101090101"
    },
	{
        "city":"\u82CF\u5DDE",
        "code":"101190401"
    },
	{
        "city":"\u798F\u5DDE",
        "code":"101230101"
    },
	{
        "city":"\u5409\u6797",
        "code":"101060201"
    },
	{
        "city":"\u957F\u6625",
        "code":"101060101"
    },
	{
        "city":"\u6CF0\u5B89",
        "code":"101120801"
    },
	 {
        "city":"\u79E6\u7687\u5C9B",
        "code":"101091101"
    },
	{
        "city":"\u5F90\u5DDE",
        "code":"101190801"
    },
	{
        "city":"\u5B9C\u660C",
        "code":"101200901"
    },
	 {
        "city":"\u682A\u6D32",
        "code":"101250301"
    },
	{
        "city":"\u5468\u53E3",
        "code":"101181401"
    },
	{
        "city":"\u6D1B\u9633",
        "code":"101180901"
    },
	{
        "city":"\u829C\u6E56",
        "code":"101220301"
    },
	 {
        "city":"\u9A6C\u978D\u5C71",
        "code":"101220501"
    },
	 {
        "city":"\u7EF5\u9633",
        "code":"101270401"
    },
	 {
        "city":"\u5357\u5B81",
        "code":"101300101"
    },
	 {
        "city":"\u6606\u660E",
        "code":"101290101"
    },
	{
        "city":"\u5510\u5C71",
        "code":"101090501"
    },
	 {
        "city":"\u90AF\u90F8",
        "code":"101091001"
    },
	 {
        "city":"\u547C\u548C\u6D69\u7279",
        "code":"101080101"
    },
	 {
        "city":"\u626C\u5DDE",
        "code":"101190601"
    },
	 {
        "city":"\u6DEE\u5B89",
        "code":"101190901"
    },
	{
        "city":"\u65E0\u9521",
        "code":"101190201"
    },
	 {
        "city":"\u6CC9\u5DDE",
        "code":"101230501"
    },
	{
        "city":"\u4F5B\u5C71",
        "code":"101280800"
    },
	{
        "city":"\u4E1C\u839E",
        "code":"101281601"
    },
	 {
        "city":"\u4E2D\u5C71",
        "code":"101281701"
    },
	{
        "city":"\u73E0\u6D77",
        "code":"101280701"
    },
	{
        "city":"\u4E09\u4E9A",
        "code":"101310201"
    },
	{
        "city":"\u6D77\u53E3",
        "code":"101310101"
    }
]', true);
        $extra['require_refresh'] = false;
        $extra['data'] = $list;
        $this->apiSuccess('获取城city列表成功', null, $extra);
    }

    /**
     * 城市列表是否需要同步
     */
    public function isCityListNeedSync(){
        $extra['require_sync'] = false;
        $this->apiSuccess('城市列表状态', null, $extra);
    }

    /**
     * 获取热门城市列表
     */
    public function getHotCityList(){
        $model = M('OrganizationTag');
        $map['status'] = 1;
        $map['type'] = 3;
        $list = $model->field('value')->where($map)->select();
        $extra['data'] = $list;
        $this->apiSuccess('获取城市列表成功', null, $extra);
    }

    /**
     * 获取机构列表
     * @param int $uid
     * @param null $city
     * @param null $type
     * @param null $name
     * @param int $page
     * @param int $count
     */
    public function localOrganizationList($uid=0, $city=null, $type=null, $name=null, $page=1, $count=10){
        if($uid==0){
            $uid = is_login();
        }
        $model = M('Organization');
        $select_where = "application_status=2 and status=1";
        if(!empty($city)){
            $select_where = $select_where . " and city like '%" .$city . "%'";
        }
        if(!empty($type)){
            $select_where = $select_where . " and type=".$type;
        }
        if(!empty($name)){
            $select_where = $select_where . " and name like '%".$name."%'";
        }
        $org_list = $model->field('id, name, slogan, city, view_count, logo, light_authentication,sort')->order("sort asc")
            ->where($select_where)->page($page, $count)->select();
        $totalCount = $model->where("application_status=2 and status=1")->count();
        /*if(!empty($city)&&!empty($type)){
            $org_list = $model->field('id, name, slogan, city, view_count, logo')
                ->where("application_status=2 and status=1 and city like '%".$city."%' and type=".$type)->page($page, $count)->select();
            $totalCount = $model->where("application_status=2 and status=1 and city like '%".$city."%' and type=".$type)->count();
        }
        if(!empty($city)&&empty($type)){
            $org_list = $model->field('id, name, slogan, city, view_count, logo')
                ->where("application_status=2 and status=1 and city like '%".$city."%'")->page($page, $count)->select();
            $totalCount = $model->where("application_status=2 and status=1 and city like '%".$city."%'")->count();
        }
        if(empty($city)&&!empty($type)){
            $org_list = $model->field('id, name, slogan, city, view_count, logo')
                ->where("application_status=2 and status=1 and type=".$type)->page($page, $count)->select();
            $totalCount = $model->where("application_status=2 and status=1 and type=".$type)->count();
        }
        if(empty($city)&&empty($type)){
            $org_list = $model->field('id, name, slogan, city, view_count, logo')
                ->where("application_status=2 and status=1")->page($page, $count)->select();
            $totalCount = $model->where("application_status=2 and status=1")->count();
        }*/
        foreach($org_list as &$org){
            $org_id = $org['id'];
            $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
            $org['followCount'] = $this->getFollowCount($org_id);
            $org['enrollCount'] = $this->getEnrollCount($org_id);

            //$user['info'] = query_user(array('avatar256', 'avatar128', 'group', 'extinfo', 'nickname'), $uid);
            $follow_other = D('Follow')->where(array('who_follow'=>$uid,'follow_who'=>$org_id, 'type'=>2))->find();
            $be_follow = D('Follow')->where(array('who_follow'=>$org_id,'follow_who'=>$uid, 'type'=>2))->find();
            if($follow_other&&$be_follow){
                $org['relationship'] = 3;
            } else if($follow_other&&(!$be_follow)){
                $org['relationship'] = 2;
            } else if((!$follow_other)&&$be_follow){
                $org['relationship'] = 1;
            } else {
                $org['relationship'] = 0;
            }
        }
        //机构列表按报名数排序
        $sort = array(
            'direction'=>'SORT_DESC',
            'field'=>'enrollCount'
        );
        $org_list = $this->sort_list($sort, $org_list);

        //机构列表按排序字段排序
        $sort2 = array(
            'direction'=>'SORT_ASC',
            'field'=>'sort'
        );
        $org_list = $this->sort_list($sort2, $org_list);
        //去掉sort字段
        foreach($org_list as &$org){
            unset($org['sort']);
        }

        $data['totalCount'] = $totalCount;
        $data['list'] = $org_list;
        $this->apiSuccess('获取机构列表成功', null, $data);
    }

    /**
     * 机构名称搜索
     * @param null $name
     * @param int $page
     * @param int $count
     */
    public function searchOrganization($name=null, $page=1, $count=10){
        if(empty($name)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $model = M('Organization');
        $org_list = $model->field('id, name, slogan, city, view_count, logo, light_authentication')
                ->where("application_status=2 and status=1 and name like '%".$name."%'")->page($page, $count)->select();
        $totalCount = $model->where("application_status=2 and status=1 and name like '%".$name."%'")->count();
        $uid = is_login();
        foreach($org_list as &$org){
            $org_id = $org['id'];
            $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
            $org['followCount'] = $this->getFollowCount($org_id);
            $org['enrollCount'] = $this->getEnrollCount($org_id);
            $follow_other = D('Follow')->where(array('who_follow'=>$uid,'follow_who'=>$org_id, 'type'=>2))->find();
            $be_follow = D('Follow')->where(array('who_follow'=>$org_id,'follow_who'=>$uid, 'type'=>2))->find();
            if($follow_other&&$be_follow){
                $org['relationship'] = 3;
            } else if($follow_other&&(!$be_follow)){
                $org['relationship'] = 2;
            } else if((!$follow_other)&&$be_follow){
                $org['relationship'] = 1;
            } else {
                $org['relationship'] = 0;
            }
        }
        $data['totalCount'] = $totalCount;
        $data['list'] = $org_list;
        $this->apiSuccess('搜索机构成功', null, $data);
    }

    /**
     * 获取机构公告头条信息
     * @param int $organization_id
     * @param int $page
     * @param int $count
     * @param null $type
     * @return mixed
     */
    public function topPost($organization_id=0, $page=1, $count=3, $type=null){
        if($organization_id==0){
            $this->apiError(-1, '传入机构ID不能为空');
        }
        $model = M('OrganizationNotice');
        $totalCount = $model->where('status=1 and push_to_organization=1 or organization_id in (0, '.$organization_id.')')->count();
        $list = $model->field('id, tag, title, create_time')->where('status=1 and (push_to_organization=1 or organization_id in (0, '.$organization_id.'))')->order('create_time desc')->page($page, $count)->select();
        foreach($list as &$notice){
            $notice['detail_url'] = C('HOST_NAME_PREFIX').'api.php?s=/organization/noticedetail/id/'.$notice['id'];
        }
        if($type=="view"){
            return $list;
        }else{
            $extra['totalCount'] = $totalCount;
            $extra['data'] = $list;
            $this->apiSuccess('获取机构公告列表成功', null, $extra);
        }
    }

    /**
     * 获取用户报名信息
     * @param int $organization_id
     * @param int $page
     * @param int $count
     * @param null $type
     * @return array
     */
    public function enrollList($organization_id=0, $page=1, $count=3, $type=null){
        if($organization_id==0){
            $this->apiError(-1, '传入机构ID不能为空');
        }
        $model = M('OrganizationEnroll');
        $total = $model->distinct(true)->field('student_uid')->where('status>0 and organization_id='.$organization_id)->select();
        $total_count = count($total);
        if($type=="all"){
            $list = $model->field('student_name,phone_num, create_time')->where('status>0 and organization_id='.$organization_id)->group('student_uid')->select();
        }else{
            $list = $model->field('student_name,phone_num, create_time')->where('status>0 and organization_id='.$organization_id)->group('student_uid')->page($page, $count)->select();
        }

        foreach($list as &$user){
            $user['student_name'] = mb_substr($user['student_name'],0,1,'utf-8') . '**';
            $user['phone_num'] = mb_substr($user['phone_num'],0,3,'utf-8') . '********';
        }
        $guarantee_num = M('Organization')->where('status=1 and id='.$organization_id)->getField('guarantee_num');
        if($type=="view"){
            return array(
                'available_count'=>(int)$guarantee_num - $total_count,
                'data'=>$list
            );
        }else{
            $extra['available_count'] = (int)$guarantee_num - $total_count;
            $extra['total_count'] = $total_count;
            $extra['data'] = $list;
            $this->apiSuccess('获取报名列表成功', null, $extra);
        }
    }

    /**
     *  获取机构的分数统计
     * @param int $organization_id
     * @param null $type
     * @return array
     */
    public function fractionalStatistics($organization_id=0,$type=null){
        if($organization_id==0){
            $this->apiError(-1, '传入机构ID不能为空');
        }
        $configModel = M('OrganizationTag');
        $commentModel = M('OrganizationComment');
        $commentStarModel = M('OrganizationCommentStar');
        $comprehensiveScore = $commentModel->where('status=1 and organization_id='.$organization_id)->avg('comprehensive_score');
        $configList = $configModel->field('id, value')->where('status=1 and type=4')->select();
        foreach($configList as &$config){
            $config_id = $config['id'];
            $score = $commentStarModel->where('status=1 and organization_id='.$organization_id.' and comment_type='.$config_id)
                ->avg('star');
            $config['score'] = round($score, 1);
        }
        if($type=="view"){
            return array(
                'comprehensiveScore'=>round($comprehensiveScore, 1),
                'data'=>$configList
            );
        }else{
            $extra['comprehensiveScore'] = round($comprehensiveScore, 1);
            $extra['data'] = $configList;
            $this->apiSuccess('获取评论统计分数成功', null, $extra);
        }
    }

    /**
     * 获取机构的评论列表
     * @param int $organization_id
     * @param null $type
     * @param int $page
     * @param int $count
     * @return array
     */
    public function commentList($organization_id=0,$type=null, $page=1, $count=10){
        $model = M('OrganizationComment');
        $totalCount = $model->where('status=1 and organization_id='.$organization_id)->count();
        $goodCount = $model->where('comprehensive_score>3 and status=1 and organization_id='.$organization_id)->count();
        $mediumCount = $model->where('comprehensive_score<4 and comprehensive_score>1 and status=1 and organization_id='.$organization_id)->count();
        $badCount = $model->where('comprehensive_score<2 and status=1 and organization_id='.$organization_id)->count();
        if($type == 'good'){
            $totalCount = $goodCount;
            $list = $model->where('comprehensive_score>3 and status=1 and organization_id='.$organization_id)->page($page, $count)->order('create_time desc')->select();
        }else if($type == 'medium'){
            $totalCount = $mediumCount;
            $list = $model->where('comprehensive_score<4 and comprehensive_score>1 and status=1 and organization_id='.$organization_id)->page($page, $count)->order('create_time desc')->select();
        }else if($type == 'bad'){
            $totalCount = $badCount;
            $list = $model->where('comprehensive_score<2 and status=1 and organization_id='.$organization_id)->page($page, $count)->order('create_time desc')->select();
        }else{
            $list = $model->where('status=1 and organization_id='.$organization_id)->order('create_time desc')->page($page, $count)->select();
        }
        foreach($list as &$comment){
            $uid = $comment['uid'];
            $comment['userInfo'] = query_user(array('uid', 'avatar128', 'avatar256', 'nickname'), $uid);
            $comment['userInfo']['nickname'] = "嘿设汇用户";
            $comment['userInfo']['avatar128'] = "http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/default_avatar.png";
            $comment['userInfo']['avatar256'] = "http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/default_avatar.png";
            unset($comment['organization_id']);
            unset($comment['uid']);
            unset($comment['status']);
        }
        if($type=="view"){
            return array(
                'totalCount'=>$totalCount,
                'data'=>$list
            );
        }else{
            $extra['totalCount'] = $totalCount;
            $extra['goodCount'] = $goodCount;
            $extra['mediumCount'] = $mediumCount;
            $extra['badCount'] = $badCount;
            $extra['data'] = $list;
            $this->apiSuccess('获取机构评论列表成功', null, $extra);
        }
    }

    /**
     * 获取机构评分种类的列表
     */
    public function getCommentScoreList(){
        $configModel = M('OrganizationTag');
        $configList = $configModel->field('id, value')->where('status=1 and type=4')->select();
        $extra['data'] = $configList;
        $this->apiSuccess('获取评分种类列表成功', null, $extra);
    }

    /**
     * 获取机构的类型列表
     */
    public function getTypeList(){
        $configModel = M('OrganizationTag');
        $configList = $configModel->field('id, value')->where('status=1 and type=2')->select();
        $extra['data'] = $configList;
        $this->apiSuccess('获取机构类型列表成功', null, $extra);
    }

    /**
     * 用户评论机构
     * @param int $organization_id
     * @param int $uid
     * @param int $comprehensiveScore
     * @param null $content
     * @param null $strScoreList
     */
    public function doComment($organization_id=0, $uid=0, $comprehensiveScore=5, $content=null, $strScoreList=null){
        if(empty($content)||empty($strScoreList)||$organization_id==0){
            $this->apiError(-1, '传入参数不能为空');
        }
        if(!$uid){
            $this->requireLogin();
            $uid = $this->getUid();
        }
//        $isExist = M('OrganizationEnroll')->where('status=2 and organization_id='.$organization_id)->select();
//        if(!$isExist){
//            $this->apiError(-2, '你不是该机构学员，不允许评论');
//        }
        $commentModel = M('OrganizationComment');
        $commentStarModel = M('OrganizationCommentStar');
        $data['organization_id'] = $organization_id;
        $data['uid'] = $uid;
        $data['comprehensive_score'] = $comprehensiveScore;
        $data['comment'] = $content;
        $data['create_time'] = time();
        $res = $commentModel->add($data);
        if($res){
            unset($data['comprehensive_score']);
            unset($data['comment']);
            $strScoreList = stripslashes($strScoreList);
            $scoreList = json_decode($strScoreList, true);
            foreach($scoreList as $score){
                $id = $score['id'];
                $score = $score['score'];
                $data['comment_type'] = $id;
                $data['star'] = $score;
                $commentStarModel->add($data);
            }
        } else {
            $this->apiError(-1, '评论失败');
        }
        $extra['comment_id'] = $res;
        $this->apiSuccess('评论成功',null,$extra);
    }

    /**
     * @param int $organization_id
     */
    public function followOrganization($organization_id=0){
        if($organization_id==0){
            $this->apiError(-2, '传入机构id不能为空');
        }
        $this->requireLogin();
        $model = M('Follow');
        $data['follow_who'] = $organization_id;
        $data['who_follow'] = $this->getUid();
        $data['type'] = 2;
        $data['create_time'] = time();
        if($model->add($data)) {
            $this->apiSuccess("关注成功");
        } else {
            $this->apiError(-1, "关注失败");
        }
    }

    /**
     * @param int $organization_id
     */
    public function unFollowOrganization($organization_id=0){
        if($organization_id==0){
            $this->apiError(-2, '传入机构id不能为空');
        }
        $this->requireLogin();
        $model = M('Follow');
        $data['follow_who'] = $organization_id;
        $data['who_follow'] = $this->getUid();
        $data['type'] = 2;
        if($model->where($data)->delete()) {
            $this->apiSuccess("取消关注成功");
        } else {
            $this->apiError(-1, "取消关注失败");
        }
    }

    /**
     * app获取机构老师列表
     * @param int $organization_id
     * @param int $page
     * @param int $count
     */
    public function appGetTeacherList($organization_id=0,$page = 1, $count = 10,$type=null){
        if($organization_id==0){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $Model = new \Think\Model();
        $totalCount = $Model->query('select count(*) as count from (select distinct uid from hisihi_organization_relation where `status`=1 and `group`=6 and `organization_id`='.$organization_id.')m');
        $totalCount = $totalCount[0]['count'];
        $teacher_ids = M('OrganizationRelation')->distinct('uid')->field('uid')
            ->where(array('organization_id'=>$organization_id,'status'=>1,'group'=>6))
            ->page($page, $count)->select();
        $org_name = M('Organization')->where(array('id'=>$organization_id))->getField('name');
        foreach($teacher_ids as &$teacher){
            $teacher = $this->findTeacherById($teacher['uid']);
            $teacher['info']['institution'] = $org_name;
        }
        unset($teacher);
        if($type=="view"){
            return $teacher_ids;
        }else{
            //返回成功结果
            $this->apiSuccess("获取机构老师列表成功", null, array('totalCount' => $totalCount,'teacherList' => $teacher_ids));
        }
    }

    /**
     * app获取机构学生列表
     * @param int $organization_id
     * @param int $page
     * @param int $count
     */
    public function appGetStudentList($organization_id=0,$page = 1, $count = 10, $type=null){
        if($organization_id==0){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $Model = new \Think\Model();
        $totalCount = $Model->query('select count(*) as count from (select distinct uid from hisihi_organization_relation where `status`=1 and `group`=5 and `organization_id`='.$organization_id.')m');
        $totalCount = $totalCount[0]['count'];
        $student_ids = M('OrganizationRelation')->distinct('uid')->field('uid')
            ->where(array('organization_id'=>$organization_id,'status'=>1,'group'=>5))
            ->page($page, $count)->select();
        $org_name = M('Organization')->where(array('id'=>$organization_id))->getField('name');
        foreach($student_ids as &$student){
            $student = $this->findStudentById($student['uid']);
            $student['info']['institution'] = $org_name;
        }
        unset($student);
        if($type=="view"){
            return $student_ids;
        }else{
            //返回成功结果
            $this->apiSuccess("获取机构学生列表成功", null, array('totalCount' => $totalCount,'studentList' => $student_ids));
        }
    }

    /**
     * 获取机构粉丝列表
     * @param int $organization_id
     * @param int $page
     * @param int $count
     */
    public function fansList($organization_id=0,$page = 1, $count = 10){
        if($organization_id==0){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $totalCount = M('Follow')->where(array('follow_who'=>$organization_id,'status'=>1,'type'=>2))->count();
        $fans = M('Follow')->field('who_follow as uid')
            ->where(array('follow_who'=>$organization_id,'status'=>1,'type'=>2))
            ->page($page, $count)->select();
        foreach($fans as &$fan){
            $isfollowing = D('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$fan['uid']))->find();
            $isfans = D('Follow')->where(array('who_follow'=>$fan['uid'],'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $fan['relationship'] = $isfollowing | $isfans;
            $fan['info'] = query_user(array('avatar256', 'avatar128', 'username', 'score', 'group','extinfo', 'fans', 'following', 'signature', 'nickname','weibocount','replycount'), $fan['uid']);
        }
        unset($fan);
        //返回成功结果
        $this->apiSuccess("获取机构粉丝列表成功", null, array('totalCount' => $totalCount,'fansList' => $fans));
    }

    /**
     * 分享机构详情
     * @param int $organization_id
     */
    public function shareOrganization($organization_id=0){
        $result = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->find();
        if($result){
            $extra['org_share_url'] = "api.php/Organization/OrganizationBasicInfo/organization_id/".$organization_id;
            $uid = $this->getUid();
            if($uid){
                if($this->checkUserDoShareCache($uid)){
                    if(increaseScore($uid, 1)){
                        $extraData['scoreAdd'] = "1";
                        $extraData['scoreTotal'] = getScoreCount($uid);
                        $extra['score'] = $extraData;
                        insertScoreRecord($uid, 1, '用户分享');
                    }
                }
            }
            $this->apiSuccess("获取机构分享链接成功", null, $extra);
        }
        else{
            $this->apiError(-404, '未找到该机构！');
        }
    }

    //机构详情
    public function organizationDetail($id, $type = 'view')
    {
        $result = M('Organization')->where(array('id'=>$id,'status'=>1))->find();
        if($result){
            $organization = $this->appGetBaseInfo($id,0,"view");
            $organization = $this->formatOrganizationInfo($organization);
            $toppost = $this->topPost($id,1,2,"view");
            $toppost = $this->formatTopPostInfo($toppost);
            $enroll = $this->enrollList($id,1,3,"view");
            $enroll = $this->formatEnrollInfo($enroll);
            $org_video = $this->getPropagandaVideo($id,"view");
            $teacher = $this->appGetTeacherList($id,1,4,"view");
            $teacher = $this->formatTeacherInfo($teacher);
            //$student_works = $this->appGetStudentWorks($id,1,3,"view");
            //$courses = $this->appGetCoursesList($id,null,null,null,"view",1,3);
            //$environment = $this->appGetOrganizationEnvironment($id,1,3,"view");
            $fractionalStatistics = $this->fractionalStatistics($id,"view");
            $comment = $this->commentList($id,"view",1,10);
            $comment = $this->formatCommentInfo($comment);
            $this->assign('comment',$comment);
            $this->assign('fractionalStatistics',$fractionalStatistics);
            //$this->assign('environment',$environment);
            //$this->assign('courses',$courses);
            //$this->assign('student_works',$student_works);
            $this->assign('teacher',$teacher);
            $this->assign('org_video',$org_video);
            $this->assign('enroll',$enroll);
            $this->assign('toppost',$toppost);
            $this->assign('organization',$organization);
            $this->setTitle('{$organization.name|op_t} — 嘿设汇');
            $this->display('organizationdetail');
        } else{
            $this->apiError(-404, '未找到该机构！');
        }
    }

    /**
     * 机构认证报告
     * @param int $organization_id
     */
    public function OrganizationAuthenticationReport($organization_id=0){
        $report = M('OrganizationCertificate')
            ->where('status=1 and organization_id='.$organization_id)
            ->field('name, content')->find();
        if($report){
            $this->assign('report',$report);
        }else{
            $report = M('OrganizationCertificate')
                ->where('status=1 and organization_id=0')
                ->field('name, content')->find();
            $this->assign('report',$report);
        }
        $this->display('organizationauthenticationreport');
    }

    /**
     * 机构web详情页
     * @param int $organization_id
     */
    public function OrganizationBasicInfo($organization_id=0){
        $this->assign("organization_id", $organization_id);
        $organization_name = M('Organization')->where(array('id'=>$organization_id))->getField('name');
        $this->assign("organization_name", $organization_name);
        $this->display('orgbasicinfo');
    }

    /**
     * 机构报名
     * @param int $organization_id
     * @param null $student_name
     * @param null $phone_num
     * @param null $student_university
     * @param null $course_id
     */
    public function enroll($organization_id=0,$student_name=null,$phone_num=null,$student_university=null,$course_id=null){
        if($organization_id==0){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $this->requireLogin();
        $uid = $this->getUid();
        if(!$student_name){
            $this->$this->apiError(-2,'未填写学生姓名');
        }
        if(!$phone_num){
            $this->$this->apiError(-2,'未填写手机号码');
        }
        if(!$course_id){
            //$this->$this->apiError(-2,'未选择课程');
            $course_id=0;
        }
        $relationModel = M('AuthGroupAccess');
        $is_exist = $relationModel->where('group_id=6 and uid='.$uid)->find();
        if($is_exist){
            $this->apiError(-5,'老师不能报名');
        }

        $map['organization_id']=$organization_id;
        $map['student_uid']=$uid;
        $map['student_name']=$student_name;
        $map['phone_num']=$phone_num;
        $map['course_id']=$course_id;
        $map['status']=array('in',array(1,2));
        $model = M('OrganizationEnroll');
        $count = $model->where($map)->count();
        if($count){
            $this->apiError(-3,'你已经报名过该课程了');
        }
        $data['organization_id']=$organization_id;
        $data['student_uid']=$uid;
        $data['student_name']=$student_name;
        $data['phone_num']=$phone_num;
        $data['course_id']=$course_id;
        $data['student_university']=$student_university;
        $data['create_time']=time();
        $now = strval(date("YmdHis"));
        $char = strval($this->getRandChar(5));
        $data['blz_id'] = $now.$char;
        $result = $model->add($data);
        if($result){
            $this->apiSuccess('报名成功');
        }else{
            $this->apiError(-4,'添加报名信息失败');
        }
    }

    /**
     * 获取随机字符串
     * @param $length
     * @return null|string
     */
    private function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];
        }
        return $str;
    }

    /**
     * 获取机构报名课程列表，用于选择课程
     * @param null $organization_id
     */
    public function appGetCourses($organization_id=null){
        if(!$organization_id){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $model = M('OrganizationConfig');
        $map['organization_id'] = $organization_id;
        $map['status'] = 1;
        $map['type']=1003;
        $totalCount = $model->where($map)->count();
        $course_list = $model->field('id, value')->order('create_time desc')->where($map)->select();

        $extra['totalCount'] = $totalCount;
        $extra['data'] = $course_list;
        $this->apiSuccess('获取报名课程成功', null, $extra);
    }

    /**
     * 删除课程里的视频
     * @param null $video_id
     */
    public function deleteCourseVideo($video_id=null){
        $this->requireLogin();
        if(!$video_id){
            $this->apiError(-1, '传入视频id不能为空');
        }
        M('OrganizationVideo')->where(array('id'=>$video_id))->save(array('status'=>-1));
        $this->apiSuccess('删除成功');
    }

    /**
     * 获取机构的宣传视频
     * @param null $organization_id
     */
    public function getPropagandaVideo($organization_id=null,$type=null){
        if (!$organization_id) {
            $this->apiError(-1, '传入机构id不能为空');
        }
        $model = M('Organization');
        $info = $model->field('video, video_img')->where('status=1 and id=' . $organization_id)->find();
        if ($info) {
            $video_id = $info['video'];
            $video_img = $info['video_img'];
            $videoModel = M('OrganizationVideo');
            $videoInfo = $videoModel->field('url')->where('status=1 and id=' . $video_id)->find();
            $oss_video_pre = 'http://game-video.oss-cn-qingdao.aliyuncs.com/';
            $oss_video_post = '/p.m3u8';
            if(empty($videoInfo['url'])){
                $url = null;
            } else {
                $url = $oss_video_pre . $videoInfo['url'] . $oss_video_post;
            }
            $extra['data']['video_img'] = $video_img;
            $extra['data']['video_url'] = $url;
            if($type=="view"){
                return $extra['data'];
            }else{
                $this->apiSuccess('获取宣传视频成功', null, $extra);
            }
        } else {
            $this->apiError(-1, '未找到机构的宣传视频，可能没有上传');
        }
    }

    /**
     * app获取学生作品
     * @param null $organization_id
     * @param int $page
     * @param int $count
     */
    public function appGetStudentWorks($organization_id=null,$page=1,$count=3,$type=null){
        if(!$organization_id){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $model = M('OrganizationResource');
        $map['organization_id'] = $organization_id;
        $map['type'] = 1;
        $map['status'] = 1;
        $totalCount = $model->where($map)->count();
        $list = $model->field('id, url, description, create_time')->order('create_time desc')->where($map)->page($page, $count)->select();
        foreach ($list as &$work) {
            $pic_url = $work['url'];
            $origin_img_info = getimagesize($pic_url);
            $src_size = Array();
            $src_size['width'] = $origin_img_info[0]; // width
            $src_size['height'] = $origin_img_info[1]; // height
            $work['picture'] = array(
                'url'=>$pic_url,
                'size'=>$src_size
            );
            $pic_small = $pic_url . '@50p';
            $origin_img_info = getimagesize($pic_small);
            $thumb_size = Array();
            $thumb_size['width'] = $origin_img_info[0]; // width
            $thumb_size['height'] = $origin_img_info[1]; // height
            $work['thumb'] = array(
                'url'=>$pic_small,
                'size'=>$thumb_size
            );
        }
        if($type=="view"){
            return $list;
        }else{
            $extra['totalCount'] = $totalCount;
            $extra['data'] = $list;
            $this->apiSuccess('获取机构学生作品成功', null, $extra);
        }
    }

    /**
     * 获取机构环境图片
     * @param null $organization_id
     * @param int $page
     * @param int $count
     */
    public function appGetOrganizationEnvironment($organization_id=null,$page=1,$count=3,$type=null){
        if(!$organization_id){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $model = M('OrganizationResource');
        $map['organization_id'] = $organization_id;
        $map['type'] = 2;
        $map['status'] = 1;
        $totalCount = $model->where($map)->count();
        $list = $model->field('id, url, description, create_time')->order('create_time desc')->where($map)->page($page, $count)->select();
        foreach ($list as &$work) {
            $pic_url = $work['url'];
            $origin_img_info = getimagesize($pic_url);
            $src_size = Array();
            $src_size['width'] = $origin_img_info[0]; // width
            $src_size['height'] = $origin_img_info[1]; // height
            $work['picture'] = array(
                'url'=>$pic_url,
                'size'=>$src_size
            );
            $pic_small = $pic_url . '@50p';
            $origin_img_info = getimagesize($pic_small);
            $thumb_size = Array();
            $thumb_size['width'] = $origin_img_info[0]; // width
            $thumb_size['height'] = $origin_img_info[1]; // height
            $work['thumb'] = array(
                'url'=>$pic_small,
                'size'=>$thumb_size
            );
        }
        if($type=="view"){
            return $list;
        }else{
            $extra['totalCount'] = $totalCount;
            $extra['data'] = $list;
            $this->apiSuccess('获取机构环境图片成功', null, $extra);
        }
    }

    /**
     *  获取课程详情
     * @param int $uid
     * @param int $course_id
     * @param null $type
     */
    public function getCourseDetail($uid=0, $course_id=0,$type=null){
        if($course_id==0){
            $this->apiError(-1, '传入课程id不能为空');
        }
        if($uid==0){
            $uid = $this->getUid();
        }
        $courseModel = M('OrganizationCourse');
        $videoModel = M('OrganizationVideo');
        $courseInfo = $courseModel->where('status=1 and id='.$course_id)->find();
        if($courseInfo){
            $courseInfo['organization'] = $this->findOrganizationById($courseInfo['organization_id']);
            $courseInfo['lecturer'] = $this->findTeacherById($courseInfo['lecturer']);
            $courseInfo['lecturer']['info']['institution'] = $courseInfo['organization']['name'];
            $videoDuration = $videoModel->field('name, url')->where('status=1 and course_id='.$course_id)->sum('duration');
            $courseInfo['duration'] = $videoDuration;
            $video_list = $videoModel->field('id,name, duration')->where('status=1 and course_id='.$course_id)->select();

            $issue_model = M('Issue');
            $favorite_model = M('Favorite');
            $support_model = M('Support');
            $courseInfo['type'] = $issue_model->where('id='.$courseInfo['category_id'])->getField('title');
            //解析并生成图片数据
            $oss_pic_pre = 'http://game-pic.oss-cn-qingdao.aliyuncs.com/';
            if(substr_count($courseInfo['img_str'], 'OSS')){
                $courseInfo['img'] = str_replace('OSS-', $oss_pic_pre, $courseInfo['img_str']);
            } else {
                $courseInfo['img'] = $courseInfo['img_str'];
            }
            if($courseInfo['is_old_hisihi_data']){
                $favorite['appname'] = 'Issue';
                $favorite['table'] = 'issue_content';
                $favorite['row'] = $courseInfo['issue_content_id'];
                $favorite['uid'] = $this->getUid();
                //获取点赞信息
                if ($support_model->where($favorite)->count()) {
                    $courseInfo['isSupportd'] = 1;
                } else {
                    $courseInfo['isSupportd'] = 0;
                }
                $supportCount = $support_model->where(array('appname'=>'Issue',
                    'table'=>'issue_content','row'=>$courseInfo['issue_content_id']))->count();
                $courseInfo['supportCount'] = $supportCount + $courseInfo['fake_support_count'];
            }else{
                $favorite['appname'] = 'Organization';
                $favorite['table'] = 'organization_courses';
                $favorite['row'] = $courseInfo['id'];
                $favorite['uid'] = $this->getUid();
                //获取点赞信息
                if ($support_model->where($favorite)->count()) {
                    $courseInfo['isSupportd'] = 1;
                } else {
                    $courseInfo['isSupportd'] = 0;
                }
                $supportCount = $support_model->where(array('appname'=>'Organization',
                    'table'=>'organization_courses','row'=>$courseInfo['id']))->count();
                $courseInfo['supportCount'] = $supportCount + $courseInfo['fake_support_count'];
            }
            //获取收藏信息
            $favorite['appname'] = 'Organization';
            $favorite['table'] = 'organization_courses';
            $favorite['row'] = $courseInfo['id'];
            $favorite['uid'] = $this->getUid();
            if ($favorite_model->where($favorite)->count()) {
                $courseInfo['isFavorite'] = 1;
            } else {
                $courseInfo['isFavorite'] = 0;
            }
            $favoriteCount = $favorite_model->where(array('appname'=>'Organization',
                'table'=>'organization_courses','row'=>$courseInfo['id']))->count();
            $courseInfo['favoriteCount'] = $favoriteCount + $courseInfo['fake_favorite_count'];
            unset($courseInfo['is_old_hisihi_data']);
            unset($courseInfo['issue_content_id']);
            unset($courseInfo['img_str']);
            unset($courseInfo['category_id']);
            unset($courseInfo['status']);
            unset($courseInfo['create_time']);
            unset($courseInfo['fake_support_count']);
            unset($courseInfo['fake_favorite_count']);
            unset($courseInfo['organization_id']);
            unset($courseInfo['auth']);
            if($videoModel){
                $courseInfo['video_list'] = $video_list;
                $extra['data'] = $courseInfo;
                if($type == 'view'){//用于页面分享
                    $courseInfo['duration'] = $this->sec2time($courseInfo['duration']);
                    $this->assign('course_content', $courseInfo);
                    $relatedList = $this->appGetCoursesList(null,$courseInfo['type_id'],$course_id,null,'view');
                    foreach($relatedList as &$video){
                        $video['duration'] = $this->sec2time($video['duration']);
                    }
                    $this->assign('relatedList',$relatedList);
                    $this->setTitle('{$course_content.title|op_t} — 嘿设汇');
                    $this->display('coursedetail');
                }else{
                    $this->apiSuccess('获取视频详情成功', null, $extra);
                }
            }
        } else {
            $this->apiError(-404, '未找到对应的课程');
        }
    }

    /**
     * 根据视频id获取视频地址
     * @param null $video_id
     */
    public function getVideoUrl($video_id=null){
        $url = M('OrganizationVideo')->where(array('id'=>$video_id,'status'=>1))->getField('url');
        $oss_video_pre = 'http://game-video.oss-cn-qingdao.aliyuncs.com/';
        $oss_video_post = '/p.m3u8';
        if(empty($url)){
            $video_url = null;
        } else {
            $video_url = $oss_video_pre . $url . $oss_video_post;
        }
        $extra['video_url'] = $video_url;
        $this->apiSuccess('获取视频地址成功', null, $extra);
    }

    /**
     * 获取机构认证列表
     * @param null $organization_id
     */
    public function getAuthenticationList($organization_id=null){
        if(!$organization_id){
            $this->error('机构id不能为空');
        }
        $auth_list = M('OrganizationAuthenticationConfig')->field('id,name,pic_url,disable_pic_url,content')
            ->where('flag=1 and status=1')->select();
        foreach($auth_list as &$auth){
            $map['organization_id'] = $organization_id;
            $map['authentication_id'] = $auth['id'];
            $map['status'] = 1;
            $count = M('OrganizationAuthentication')->where($map)->count();
            if($count){
                $auth['status'] = true;
            }else{
                $auth['status'] = false;
            }
        }
        $extra['data'] = $auth_list;
        $this->apiSuccess('获取认证列表成功', null, $extra);
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

    /**
     * 获取机构的认证信息
     * @param $organization_id
     */
    private function getAuthenticationInfo($organization_id=0){
        $model = M('OrganizationAuthenticationConfig');
        $authModel = M('OrganizationAuthentication');
        $config_list = $model->field('id, name, pic_url, disable_pic_url, tag_pic_url, content, default_display')->where('status=1 and flag=0')->select();
        foreach($config_list as &$config){
            //$config['pic_url'] = $config['pic_id'];
            $map['organization_id'] = $organization_id;
            $map['authentication_id'] = $config['id'];
            if($authModel->where($map)->find()){
                $config['status'] = true;
            } else {
                $config['status'] = false;
            }
        }
        return $config_list;
    }

    /**
     * @param int $organization_id
     */
    private function getFollowCount($organization_id=0){
        if($organization_id==0){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $model = M('Follow');
        $data['follow_who'] = $organization_id;
        $data['type'] = 2;
        $count = $model->where($data)->count();
        $model = M('Organization');
        $fake_info = $model->field('fake_fans_count')->where('id='.$organization_id)->find();
        $count = $count + (int)$fake_info['fake_fans_count'];
        return $count;
    }

    /**
     * @param int $organization_id
     */
    private function getEnrollCount($organization_id=0){
        if($organization_id==0){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $model = M('OrganizationEnroll');
        $data['organization_id'] = $organization_id;
        $data['status'] = array('gt', 0);
        $count = $model->where($data)->count();
        return $count;
    }

    /**
     * @param int $organization_id
     */
    private function getTeachersCount($organization_id=0){
        if($organization_id==0){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $Model = M();
        $totalCount = $Model->query('select count(*) as count from (select distinct uid from hisihi_organization_relation where `status`=1 and `group`=6 and `organization_id`='.$organization_id.')m');
        $totalCount = $totalCount[0]['count'];
        return $totalCount;
    }

    private function getGroupCount($organization_id=0){
        if($organization_id==0){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $group_count = M('ImGroups')->where('status=1 and organization_id='.$organization_id)->count();
        return $group_count;
    }

    /**
     * 上传图片到OSS
     * @param $picID
     */
    private function uploadLogoPicToOSS($picID){
        $model = M();
        $result = $model->query("select path from hisihi_picture where id=".$picID);
        if($result){
            $picLocalPath = $result[0]['path'];
            $picKey = substr($picLocalPath, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $picKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if(!$isExist){
                Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadOtherResource', $param);
            }
        }
    }

    /**
     * 获取图片地址
     * @param $pic_id
     * @return null|string
     */
    private function fetchImage($pic_id){
        if($pic_id == null)
            return null;
        $model = M();
        $pic_info = $model->query("select path from hisihi_picture where id=".$pic_id);
        if($pic_info){
            $path = $pic_info[0]['path'];
            $objKey = substr($path, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $objKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if($isExist){
                $picUrl = "http://hisihi-other.oss-cn-qingdao.aliyuncs.com/".$objKey;
            }
        }
        return $picUrl;
    }

    /**
     * 获取cdn oss图片地址
     * @param $pic_id
     * @return null|string
     */
    private function fetchCdnImage($pic_id){
        if($pic_id == null)
            return null;
        $model = M();
        $pic_info = $model->query("select path from hisihi_picture where id=".$pic_id);
        if($pic_info){
            $path = $pic_info[0]['path'];
            $objKey = substr($path, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $objKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if($isExist){
                $picUrl = "http://pic.hisihi.com/".$objKey;
            } else {
                $picUrl = null;
            }
        }
        return $picUrl;
    }

    /**
     * 获取裁剪图片
     * @param $pic_id
     * @return null|string
     */
    private function fetchCropImage($pic_id=null,$pointX=0,$pointY=0,$width=0,$height=0){
        if($pic_id == null)
            return null;
        $model = M();
        $pic_info = $model->query("select path from hisihi_picture where id=".$pic_id);
        if($pic_info){
            $path = $pic_info[0]['path'];
            $path = substr($path, 17);
            $objKey = $path.'@'.$pointX.'-'.$pointY.'-'.$width.'-'.$height.'a';
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $objKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if($isExist){
                $picUrl = "http://hisihi-other.oss-cn-qingdao.aliyuncs.com/".$objKey;
            }
        }
        return $picUrl;
    }

    /**
     * 根据图片路径获取oss地址
     * @param null $path
     * @return null|string
     */
    private function fetchImageByPath($path=null){
        if(empty($path)){
            return null;
        }
        if(preg_match("/^http:\/\//",$path)){
            return $path;
        }
        $objKey = substr($path,0,strlen($path)-4).'_256_256'.substr($path,-4);
        $param["bucketName"] = "hisihi-avator";
        $param['objectKey'] = $objKey;
        $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
        if($isExist){
            $picUrl = "http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/".$objKey;
            return $picUrl;
        }else{
            return 'http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_256_256.jpg';
        }
    }

    /**
     * @param $url
     * @param $header_data
     * @param $post_data
     * @return mixed
     */
    private function request_by_curl($url, $header_data, $post_data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($httpCode != 200){
            return false;
        }
        curl_close($ch);
        return true;
    }

    private function formatOrganizationInfo($organization=null){
        unset($organization['slogan']);
        unset($organization['phone_num']);
        unset($organization['guarantee_num']);
        unset($organization['location']);
        unset($organization['relationship']);
        unset($organization['isStudent']);
        $organization['advantage'] = explode("#",$organization['advantage']);

        return $organization;
    }

    private function formatTopPostInfo($topPost=nulll){
        unset($topPost['id']);
        unset($topPost['create_time']);
        unset($topPost['detail_url']);

        return $topPost;
    }

    private function formatEnrollInfo($enroll=null){
        foreach($enroll['data'] as &$enroll_item){
            $enroll_item['create_time'] = time_format($enroll_item['create_time'],'Y-m-d');
        }
        return $enroll;
    }

    private function formatTeacherInfo($teacher=null){
        unset($teacher['uid']);
        unset($teacher['teacher_group_id']);
        unset($teacher['relationship']);
        unset($teacher['teacher_group']);
        unset($teacher['info']['score']);
        unset($teacher['info']['avatar256']);
        unset($teacher['info']['username']);
        unset($teacher['info']['group']);
        unset($teacher['info']['fans']);
        unset($teacher['info']['following']);
        unset($teacher['info']['signature']);
        unset($teacher['info']['weibocount']);
        unset($teacher['info']['replycount']);

        return $teacher;
    }

    private function formatCommentInfo($comment=null){
        foreach($comment['data'] as &$comment_item){
            unset($comment_item['id']);
            unset($comment_item['userInfo']['uid']);
            unset($comment_item['userInfo']['avatar256']);
            $comment_item['create_time'] = time_format($comment_item['create_time'],'Y-m-d H:i');
        }
        return $comment;
    }

    /**
     * 根据id获取机构老师信息
     * @param null $teacher_id
     * @return mixed
     */
    private function findTeacherById($teacher_id=null){
        if(empty($teacher_id)){
            return null;
            //$this->apiError(-1,'老师id不能为空');
        }
        $teacher['uid'] = $teacher_id;
        $isfollowing = M('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$teacher_id))->find();
        $isfans = M('Follow')->where(array('who_follow'=>$teacher_id,'follow_who'=>get_uid()))->find();
        $isfollowing = $isfollowing ? 2:0;
        $isfans = $isfans ? 1:0;
        $teacher['relationship'] = $isfollowing | $isfans;
        $teacher['info'] = query_user(array('avatar256', 'avatar128','group','nickname'), $teacher_id);
        return $teacher;
    }

    /**
     * 根据id获取机构学生信息
     * @param null $student_id
     * @return mixed
     */
    private function findStudentById($student_id=null){
        if(empty($student_id)){
            return null;
        }
        $student['uid'] = $student_id;
        $isfollowing = M('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$student_id))->find();
        $isfans = M('Follow')->where(array('who_follow'=>$student_id,'follow_who'=>get_uid()))->find();
        $isfollowing = $isfollowing ? 2:0;
        $isfans = $isfans ? 1:0;
        $student['relationship'] = $isfollowing | $isfans;
        $student['info'] = query_user(array('avatar256', 'avatar128','group','nickname'), $student_id);
        return $student;
    }

    /**
     * 根据机构id获取机构基本信息
     * @param null $organization_id
     * @return mixed
     */
    private function findOrganizationById($organization_id=null){
        $organization['id'] = $organization_id;
        $follow_other = M('Follow')->where(array('who_follow'=>$this->getUid(),'follow_who'=>$organization_id, 'type'=>2))->find();
        $be_follow = M('Follow')->where(array('who_follow'=>$organization_id,'follow_who'=>$this->getUid(), 'type'=>2))->find();
        if($follow_other&&$be_follow){
            $organization['relationship'] = 3;
        } else if($follow_other&&(!$be_follow)){
            $organization['relationship'] = 2;
        } else if((!$follow_other)&&$be_follow){
            $organization['relationship'] = 1;
        } else {
            $organization['relationship'] = 0;
        }
        $organizationInfo = M('Organization')->where('status=1 and id='.$organization_id)->find();
        if($organizationInfo){
            $organization['name'] = $organizationInfo['name'];
            $organization['introduce'] = $organizationInfo['introduce'];
            $organization['logo'] = $organizationInfo['logo'];
            $organization['view_count'] = $organizationInfo['view_count'];
            $organization['light_authentication'] = $organizationInfo['light_authentication'];
            $organization['followCount'] = $this->getFollowCount($organization_id);
            $organization['enrollCount'] = $this->getEnrollCount($organization_id);
            $organization['authentication'] = $this->getAuthenticationInfo($organization_id);
        }
        return $organization;
    }

    public function sort_list($sort_array, $data_list){
        $arrSort = array();
        foreach($data_list as $uniqid => $row){
            foreach($row as $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort_array['direction']){
            array_multisort($arrSort[$sort_array['field']], constant($sort_array['direction']), $data_list);
        }
        return $data_list;
    }

    /**获取400电话
     * @return string
     */
    public function get400PhoneNum(){
        $tag = M('OrganizationTag')->where('type=5 and status=1')->find();
        if($tag){
            return $tag['value'];
        }else{
            return '4000340033';
        }
    }

}