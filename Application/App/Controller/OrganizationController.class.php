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
    public function getSMS($mobile=null, $type='register'){
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
     * @param int $uid
     * @param null $type
     * @param int $version
     * @return mixed
     */
    public function appGetBaseInfo($organization_id, $uid=0, $type=null, $version=0){
        if($uid==0){
            $uid = $this->getUid();
        }
        $model=M("Organization");
        if(floatval($version) >= 2.95){
            $result = $model->where(array('id'=>$organization_id,'status'=>1))
                ->field('name,slogan,location,logo,introduce,advantage,view_count,guarantee_num,available_num,light_authentication,location_img, type, is_listen_preview, listen_preview_text')
                ->find();
        } else {
            $result = $model->where(array('id'=>$organization_id,'status'=>1))
                ->field('name,slogan,location,logo,introduce,advantage,view_count,guarantee_num,available_num,light_authentication,location_img, type')->find();
        }
        if($result){
            $logo = $result['logo'];
            if(!$logo){
                $logo='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            if(floatval($version) >= 2.8){
                if(intval($result['type']) == 31){//判断留学机构，返回留学计划
                    $plan = M('OverseasPlan')->where('status=1 and organization_id='.$organization_id)->field('id, url')->limit(1)->order('create_time desc')->select();
                    if($plan){
                        $result['plan'] = $plan;
                    }else{
                        $result['plan'] = null;
                    }
                }
            }
            $result['phone_num'] = $this->get400PhoneNum();
            $result['logo'] = $logo;
            if((float)$version>=2.95){
                $result['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($organization_id);
                $result['is_favorite'] = $this->isOrganizationFavorite($uid, $organization_id);
                $result['is_enroll'] = $this->isYuYueOrganization($uid, $organization_id);
            } else {
                $result['authenticationInfo'] = $this->getAuthenticationInfo($organization_id);
            }
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
            $result['advantage']=$advantage;
            $relationModel = M('OrganizationRelation');
            $isExist = $relationModel->where('status=1 and organization_id='.$organization_id.' and uid='.$uid)->find();
            if(!$isExist){
                $result['isStudent']=false;
            }else{
                $result['isStudent']=true;
            }
            if((float)$version>=3.02){
                $course_tag_list = $this->getOrgCourseTagListByOrgId($organization_id);
                $result['teaching_course_tag_list'] = $course_tag_list;
            }
            if($type=="view"){
                return $result;
            }else{
                $extra['data'] = $result;
                $model->where('id='.$organization_id)->setInc('view_count');
                $this->apiSuccess("获取机构信息成功",null,$extra);
            }
        }else{
            $this->apiError(-1,"获取机构信息失败");
        }
    }

    public function getOrgCourseTagListByOrgId($organization_id){
        $model = M();
        $sql = "select t.id, t.value, t.extra
                from
                hisihi_organization_tag_relation r,
                hisihi_organization_tag t
                where t.id=r.tag_id
                and t.type=9
                and t.status=1
                and r.status=1
                and r.organization_id=".$organization_id." order by t.create_time desc";
        $course_tag_list = $model->query($sql);
        return $course_tag_list;
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
    public function appGetCoursesList($organization_id=null,$type_id=null,$courses_id=null,$order=null,$type=null, $page=1, $count=5, $version=0){
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
            $course = $this->findCoursesById($course['id'], $version);
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
     * 视频课程搜索
     * @param null $key_words
     * @param int $page
     * @param int $count
     */
    public function courseSearch($key_words=null, $page=1, $count=10){
        if(empty($key_words)){
            $this->apiError(-1, "搜索关键词不能为空!");
        }
        $model = M('OrganizationCourse');
        $order = 'create_time desc';
        $map['auth'] = 1;
        $map['status'] = 1;
        $map['title'] = array('like','%'.$key_words.'%');
        $totalCount = $model->where($map)->count();
        $course_list = $model->field('id')
            ->order($order)->where($map)->page($page, $count)->select();
        $video_course = array();
        foreach($course_list as &$course){
            $course = $this->findCoursesById($course['id'], 2.9);
            $video_course[] = $course;
        }
        $extra['total_count'] = $totalCount;
        $extra['coursesList'] = $video_course;
        $this->apiSuccess('搜索视频课程成功', null, $extra);
    }

    /**
     * @param null $courses_id
     * @param int $version
     * @return mixed
     */
    public function findCoursesById($courses_id=null, $version=0){
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
        if(!empty($course['lecture'])){
            $course['lecturer_name'] = $member_model->where('uid='.$course['lecturer'])->getField('nickname');
        } else {
            $course['lecturer_name'] = "小嘿出品";
        }
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
        if(floatval($version) > 2.6){
            $org_name = $org_model->where(array('id'=>$course['organization_id'],'status'=>1))->getField('name');
            if($org_name){
                $course['organization_name'] = $org_name;
            }else{
                $course['organization_name'] = '';
            }
        }
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

    /**
     * 取消收藏机构课程
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
     * 获取全国城市列表数据
     * @param int $data_version
     */
    public function getAllCityList($data_version=0){
        $current_data_version = 1;
        $data_version = (int)$data_version;
        if($data_version!=0||$current_data_version<=$data_version){
            $this->apiSuccess('城市数据未变更，不需要更新', null);
        } else {
            $list = json_decode('[{"city":"\u5317\u4eac","code":"101010100"},{"city":"\u5929\u6d25","code":"101030100"},{"city":"\u4e0a\u6d77","code":"101020100"},{"city":"\u77f3\u5bb6\u5e84","code":"101090101"},{"city":"\u5f20\u5bb6\u53e3","code":"101090301"},{"city":"\u627f\u5fb7","code":"101090402"},{"city":"\u5510\u5c71","code":"101090501"},{"city":"\u79e6\u7687\u5c9b","code":"101091101"},{"city":"\u6ca7\u5dde","code":"101090701"},{"city":"\u8861\u6c34","code":"101090801"},{"city":"\u90a2\u53f0","code":"101090901"},{"city":"\u90af\u90f8","code":"101091001"},{"city":"\u4fdd\u5b9a","code":"101090201"},{"city":"\u5eca\u574a","code":"101090601"},{"city":"\u90d1\u5dde","code":"101180101"},{"city":"\u65b0\u4e61","code":"101180301"},{"city":"\u8bb8\u660c","code":"101180401"},{"city":"\u5e73\u9876\u5c71","code":"101180501"},{"city":"\u4fe1\u9633","code":"101180601"},{"city":"\u5357\u9633","code":"101180701"},{"city":"\u5f00\u5c01","code":"101180801"},{"city":"\u6d1b\u9633","code":"101180901"},{"city":"\u5546\u4e18","code":"101181001"},{"city":"\u7126\u4f5c","code":"101181101"},{"city":"\u9e64\u58c1","code":"101181201"},{"city":"\u6fee\u9633","code":"101181301"},{"city":"\u5468\u53e3","code":"101181401"},{"city":"\u6f2f\u6cb3","code":"101181501"},{"city":"\u9a7b\u9a6c\u5e97","code":"101181601"},{"city":"\u4e09\u95e8\u5ce1","code":"101181701"},{"city":"\u6d4e\u6e90","code":"101181801"},{"city":"\u5b89\u9633","code":"101180201"},{"city":"\u5408\u80a5","code":"101220101"},{"city":"\u829c\u6e56","code":"101220301"},{"city":"\u6dee\u5357","code":"101220401"},{"city":"\u9a6c\u978d\u5c71","code":"101220501"},{"city":"\u5b89\u5e86","code":"101220601"},{"city":"\u5bbf\u5dde","code":"101220701"},{"city":"\u961c\u9633","code":"101220801"},{"city":"\u4eb3\u5dde","code":"101220901"},{"city":"\u9ec4\u5c71","code":"101221001"},{"city":"\u6ec1\u5dde","code":"101221101"},{"city":"\u6dee\u5317","code":"101221201"},{"city":"\u94dc\u9675","code":"101221301"},{"city":"\u5ba3\u57ce","code":"101221401"},{"city":"\u516d\u5b89","code":"101221501"},{"city":"\u5de2\u6e56","code":"101221601"},{"city":"\u6c60\u5dde","code":"101221701"},{"city":"\u868c\u57e0","code":"101220201"},{"city":"\u676d\u5dde","code":"101210101"},{"city":"\u821f\u5c71","code":"101211101"},{"city":"\u6e56\u5dde","code":"101210201"},{"city":"\u5609\u5174","code":"101210301"},{"city":"\u91d1\u534e","code":"101210901"},{"city":"\u7ecd\u5174","code":"101210501"},{"city":"\u53f0\u5dde","code":"101210601"},{"city":"\u6e29\u5dde","code":"101210701"},{"city":"\u4e3d\u6c34","code":"101210801"},{"city":"\u8862\u5dde","code":"101211001"},{"city":"\u5b81\u6ce2","code":"101210401"},{"city":"\u91cd\u5e86","code":"101040100"},{"city":"\u5408\u5ddd","code":"101040300"},{"city":"\u5357\u5ddd","code":"101040400"},{"city":"\u6c5f\u6d25","code":"101040500"},{"city":"\u4e07\u76db","code":"101040600"},{"city":"\u6e1d\u5317","code":"101040700"},{"city":"\u5317\u789a","code":"101040800"},{"city":"\u5df4\u5357","code":"101040900"},{"city":"\u957f\u5bff","code":"101041000"},{"city":"\u9ed4\u6c5f","code":"101041100"},{"city":"\u4e07\u5dde\u5929\u57ce","code":"101041200"},{"city":"\u4e07\u5dde\u9f99\u5b9d","code":"101041300"},{"city":"\u6daa\u9675","code":"101041400"},{"city":"\u5f00\u53bf","code":"101041500"},{"city":"\u57ce\u53e3","code":"101041600"},{"city":"\u4e91\u9633","code":"101041700"},{"city":"\u5deb\u6eaa","code":"101041800"},{"city":"\u5949\u8282","code":"101041900"},{"city":"\u5deb\u5c71","code":"101042000"},{"city":"\u6f7c\u5357","code":"101042100"},{"city":"\u57ab\u6c5f","code":"101042200"},{"city":"\u6881\u5e73","code":"101042300"},{"city":"\u5fe0\u53bf","code":"101042400"},{"city":"\u77f3\u67f1","code":"101042500"},{"city":"\u5927\u8db3","code":"101042600"},{"city":"\u8363\u660c","code":"101042700"},{"city":"\u94dc\u6881","code":"101042800"},{"city":"\u74a7\u5c71","code":"101042900"},{"city":"\u4e30\u90fd","code":"101043000"},{"city":"\u6b66\u9686","code":"101043100"},{"city":"\u5f6d\u6c34","code":"101043200"},{"city":"\u7da6\u6c5f","code":"101043300"},{"city":"\u9149\u9633","code":"101043400"},{"city":"\u79c0\u5c71","code":"101043600"},{"city":"\u6c99\u576a\u575d","code":"101043700"},{"city":"\u6c38\u5ddd","code":"101040200"},{"city":"\u798f\u5dde","code":"101230101"},{"city":"\u6cc9\u5dde","code":"101230501"},{"city":"\u6f33\u5dde","code":"101230601"},{"city":"\u9f99\u5ca9","code":"101230701"},{"city":"\u664b\u6c5f","code":"101230509"},{"city":"\u5357\u5e73","code":"101230901"},{"city":"\u53a6\u95e8","code":"101230201"},{"city":"\u5b81\u5fb7","code":"101230301"},{"city":"\u8386\u7530","code":"101230401"},{"city":"\u4e09\u660e","code":"101230801"},{"city":"\u5170\u5dde","code":"101160101"},{"city":"\u5e73\u51c9","code":"101160301"},{"city":"\u5e86\u9633","code":"101160401"},{"city":"\u6b66\u5a01","code":"101160501"},{"city":"\u91d1\u660c","code":"101160601"},{"city":"\u5609\u5cea\u5173","code":"101161401"},{"city":"\u9152\u6cc9","code":"101160801"},{"city":"\u5929\u6c34","code":"101160901"},{"city":"\u6b66\u90fd","code":"101161001"},{"city":"\u4e34\u590f","code":"101161101"},{"city":"\u5408\u4f5c","code":"101161201"},{"city":"\u767d\u94f6","code":"101161301"},{"city":"\u5b9a\u897f","code":"101160201"},{"city":"\u5f20\u6396","code":"101160701"},{"city":"\u5e7f\u5dde","code":"101280101"},{"city":"\u60e0\u5dde","code":"101280301"},{"city":"\u6885\u5dde","code":"101280401"},{"city":"\u6c55\u5934","code":"101280501"},{"city":"\u6df1\u5733","code":"101280601"},{"city":"\u73e0\u6d77","code":"101280701"},{"city":"\u4f5b\u5c71","code":"101280800"},{"city":"\u8087\u5e86","code":"101280901"},{"city":"\u6e5b\u6c5f","code":"101281001"},{"city":"\u6c5f\u95e8","code":"101281101"},{"city":"\u6cb3\u6e90","code":"101281201"},{"city":"\u6e05\u8fdc","code":"101281301"},{"city":"\u4e91\u6d6e","code":"101281401"},{"city":"\u6f6e\u5dde","code":"101281501"},{"city":"\u4e1c\u839e","code":"101281601"},{"city":"\u4e2d\u5c71","code":"101281701"},{"city":"\u9633\u6c5f","code":"101281801"},{"city":"\u63ed\u9633","code":"101281901"},{"city":"\u8302\u540d","code":"101282001"},{"city":"\u6c55\u5c3e","code":"101282101"},{"city":"\u97f6\u5173","code":"101280201"},{"city":"\u5357\u5b81","code":"101300101"},{"city":"\u67f3\u5dde","code":"101300301"},{"city":"\u6765\u5bbe","code":"101300401"},{"city":"\u6842\u6797","code":"101300501"},{"city":"\u68a7\u5dde","code":"101300601"},{"city":"\u9632\u57ce\u6e2f","code":"101301401"},{"city":"\u8d35\u6e2f","code":"101300801"},{"city":"\u7389\u6797","code":"101300901"},{"city":"\u767e\u8272","code":"101301001"},{"city":"\u94a6\u5dde","code":"101301101"},{"city":"\u6cb3\u6c60","code":"101301201"},{"city":"\u5317\u6d77","code":"101301301"},{"city":"\u5d07\u5de6","code":"101300201"},{"city":"\u8d3a\u5dde","code":"101300701"},{"city":"\u8d35\u9633","code":"101260101"},{"city":"\u5b89\u987a","code":"101260301"},{"city":"\u90fd\u5300","code":"101260401"},{"city":"\u5174\u4e49","code":"101260906"},{"city":"\u94dc\u4ec1","code":"101260601"},{"city":"\u6bd5\u8282","code":"101260701"},{"city":"\u516d\u76d8\u6c34","code":"101260801"},{"city":"\u9075\u4e49","code":"101260201"},{"city":"\u51ef\u91cc","code":"101260501"},{"city":"\u6606\u660e","code":"101290101"},{"city":"\u7ea2\u6cb3","code":"101290301"},{"city":"\u6587\u5c71","code":"101290601"},{"city":"\u7389\u6eaa","code":"101290701"},{"city":"\u695a\u96c4","code":"101290801"},{"city":"\u666e\u6d31","code":"101290901"},{"city":"\u662d\u901a","code":"101291001"},{"city":"\u4e34\u6ca7","code":"101291101"},{"city":"\u6012\u6c5f","code":"101291201"},{"city":"\u9999\u683c\u91cc\u62c9","code":"101291301"},{"city":"\u4e3d\u6c5f","code":"101291401"},{"city":"\u5fb7\u5b8f","code":"101291501"},{"city":"\u666f\u6d2a","code":"101291601"},{"city":"\u5927\u7406","code":"101290201"},{"city":"\u66f2\u9756","code":"101290401"},{"city":"\u4fdd\u5c71","code":"101290501"},{"city":"\u547c\u548c\u6d69\u7279","code":"101080101"},{"city":"\u4e4c\u6d77","code":"101080301"},{"city":"\u96c6\u5b81","code":"101080401"},{"city":"\u901a\u8fbd","code":"101080501"},{"city":"\u963f\u62c9\u5584\u5de6\u65d7","code":"101081201"},{"city":"\u9102\u5c14\u591a\u65af","code":"101080701"},{"city":"\u4e34\u6cb3","code":"101080801"},{"city":"\u9521\u6797\u6d69\u7279","code":"101080901"},{"city":"\u547c\u4f26\u8d1d\u5c14","code":"101081000"},{"city":"\u4e4c\u5170\u6d69\u7279","code":"101081101"},{"city":"\u5305\u5934","code":"101080201"},{"city":"\u8d64\u5cf0","code":"101080601"},{"city":"\u5357\u660c","code":"101240101"},{"city":"\u4e0a\u9976","code":"101240301"},{"city":"\u629a\u5dde","code":"101240401"},{"city":"\u5b9c\u6625","code":"101240501"},{"city":"\u9e70\u6f6d","code":"101241101"},{"city":"\u8d63\u5dde","code":"101240701"},{"city":"\u666f\u5fb7\u9547","code":"101240801"},{"city":"\u840d\u4e61","code":"101240901"},{"city":"\u65b0\u4f59","code":"101241001"},{"city":"\u4e5d\u6c5f","code":"101240201"},{"city":"\u5409\u5b89","code":"101240601"},{"city":"\u6b66\u6c49","code":"101200101"},{"city":"\u9ec4\u5188","code":"101200501"},{"city":"\u8346\u5dde","code":"101200801"},{"city":"\u5b9c\u660c","code":"101200901"},{"city":"\u6069\u65bd","code":"101201001"},{"city":"\u5341\u5830","code":"101201101"},{"city":"\u795e\u519c\u67b6","code":"101201201"},{"city":"\u968f\u5dde","code":"101201301"},{"city":"\u8346\u95e8","code":"101201401"},{"city":"\u5929\u95e8","code":"101201501"},{"city":"\u4ed9\u6843","code":"101201601"},{"city":"\u6f5c\u6c5f","code":"101201701"},{"city":"\u8944\u6a0a","code":"101200201"},{"city":"\u9102\u5dde","code":"101200301"},{"city":"\u5b5d\u611f","code":"101200401"},{"city":"\u9ec4\u77f3","code":"101200601"},{"city":"\u54b8\u5b81","code":"101200701"},{"city":"\u6210\u90fd","code":"101270101"},{"city":"\u81ea\u8d21","code":"101270301"},{"city":"\u7ef5\u9633","code":"101270401"},{"city":"\u5357\u5145","code":"101270501"},{"city":"\u8fbe\u5dde","code":"101270601"},{"city":"\u9042\u5b81","code":"101270701"},{"city":"\u5e7f\u5b89","code":"101270801"},{"city":"\u5df4\u4e2d","code":"101270901"},{"city":"\u6cf8\u5dde","code":"101271001"},{"city":"\u5b9c\u5bbe","code":"101271101"},{"city":"\u5185\u6c5f","code":"101271201"},{"city":"\u8d44\u9633","code":"101271301"},{"city":"\u4e50\u5c71","code":"101271401"},{"city":"\u7709\u5c71","code":"101271501"},{"city":"\u51c9\u5c71","code":"101271601"},{"city":"\u96c5\u5b89","code":"101271701"},{"city":"\u7518\u5b5c","code":"101271801"},{"city":"\u963f\u575d","code":"101271901"},{"city":"\u5fb7\u9633","code":"101272001"},{"city":"\u5e7f\u5143","code":"101272101"},{"city":"\u6500\u679d\u82b1","code":"101270201"},{"city":"\u94f6\u5ddd","code":"101170101"},{"city":"\u4e2d\u536b","code":"101170501"},{"city":"\u56fa\u539f","code":"101170401"},{"city":"\u77f3\u5634\u5c71","code":"101170201"},{"city":"\u5434\u5fe0","code":"101170301"},{"city":"\u897f\u5b81","code":"101150101"},{"city":"\u9ec4\u5357","code":"101150301"},{"city":"\u6d77\u5317","code":"101150801"},{"city":"\u679c\u6d1b","code":"101150501"},{"city":"\u7389\u6811","code":"101150601"},{"city":"\u6d77\u897f","code":"101150701"},{"city":"\u6d77\u4e1c","code":"101150201"},{"city":"\u6d77\u5357","code":"101150401"},{"city":"\u6d4e\u5357","code":"101120101"},{"city":"\u6f4d\u574a","code":"101120601"},{"city":"\u4e34\u6c82","code":"101120901"},{"city":"\u83cf\u6cfd","code":"101121001"},{"city":"\u6ee8\u5dde","code":"101121101"},{"city":"\u4e1c\u8425","code":"101121201"},{"city":"\u5a01\u6d77","code":"101121301"},{"city":"\u67a3\u5e84","code":"101121401"},{"city":"\u65e5\u7167","code":"101121501"},{"city":"\u83b1\u829c","code":"101121601"},{"city":"\u804a\u57ce","code":"101121701"},{"city":"\u9752\u5c9b","code":"101120201"},{"city":"\u6dc4\u535a","code":"101120301"},{"city":"\u5fb7\u5dde","code":"101120401"},{"city":"\u70df\u53f0","code":"101120501"},{"city":"\u6d4e\u5b81","code":"101120701"},{"city":"\u6cf0\u5b89","code":"101120801"},{"city":"\u897f\u5b89","code":"101110101"},{"city":"\u5ef6\u5b89","code":"101110300"},{"city":"\u6986\u6797","code":"101110401"},{"city":"\u94dc\u5ddd","code":"101111001"},{"city":"\u5546\u6d1b","code":"101110601"},{"city":"\u5b89\u5eb7","code":"101110701"},{"city":"\u6c49\u4e2d","code":"101110801"},{"city":"\u5b9d\u9e21","code":"101110901"},{"city":"\u54b8\u9633","code":"101110200"},{"city":"\u6e2d\u5357","code":"101110501"},{"city":"\u592a\u539f","code":"101100101"},{"city":"\u4e34\u6c7e","code":"101100701"},{"city":"\u8fd0\u57ce","code":"101100801"},{"city":"\u6714\u5dde","code":"101100901"},{"city":"\u5ffb\u5dde","code":"101101001"},{"city":"\u957f\u6cbb","code":"101100501"},{"city":"\u5927\u540c","code":"101100201"},{"city":"\u9633\u6cc9","code":"101100301"},{"city":"\u664b\u4e2d","code":"101100401"},{"city":"\u664b\u57ce","code":"101100601"},{"city":"\u5415\u6881","code":"101101100"},{"city":"\u4e4c\u9c81\u6728\u9f50","code":"101130101"},{"city":"\u77f3\u6cb3\u5b50","code":"101130301"},{"city":"\u660c\u5409","code":"101130401"},{"city":"\u5410\u9c81\u756a","code":"101130501"},{"city":"\u5e93\u5c14\u52d2","code":"101130601"},{"city":"\u963f\u62c9\u5c14","code":"101130701"},{"city":"\u963f\u514b\u82cf","code":"101130801"},{"city":"\u5580\u4ec0","code":"101130901"},{"city":"\u4f0a\u5b81","code":"101131001"},{"city":"\u5854\u57ce","code":"101131101"},{"city":"\u54c8\u5bc6","code":"101131201"},{"city":"\u548c\u7530","code":"101131301"},{"city":"\u963f\u52d2\u6cf0","code":"101131401"},{"city":"\u963f\u56fe\u4ec0","code":"101131501"},{"city":"\u535a\u4e50","code":"101131601"},{"city":"\u514b\u62c9\u739b\u4f9d","code":"101130201"},{"city":"\u62c9\u8428","code":"101140101"},{"city":"\u5c71\u5357","code":"101140301"},{"city":"\u963f\u91cc","code":"101140701"},{"city":"\u660c\u90fd","code":"101140501"},{"city":"\u90a3\u66f2","code":"101140601"},{"city":"\u65e5\u5580\u5219","code":"101140201"},{"city":"\u6797\u829d","code":"101140401"},{"city":"\u53f0\u5317\u53bf","code":"101340101"},{"city":"\u9ad8\u96c4","code":"101340201"},{"city":"\u53f0\u4e2d","code":"101340401"},{"city":"\u6d77\u53e3","code":"101310101"},{"city":"\u4e09\u4e9a","code":"101310201"},{"city":"\u4e1c\u65b9","code":"101310202"},{"city":"\u4e34\u9ad8","code":"101310203"},{"city":"\u6f84\u8fc8","code":"101310204"},{"city":"\u510b\u5dde","code":"101310205"},{"city":"\u660c\u6c5f","code":"101310206"},{"city":"\u767d\u6c99","code":"101310207"},{"city":"\u743c\u4e2d","code":"101310208"},{"city":"\u5b9a\u5b89","code":"101310209"},{"city":"\u5c6f\u660c","code":"101310210"},{"city":"\u743c\u6d77","code":"101310211"},{"city":"\u6587\u660c","code":"101310212"},{"city":"\u4fdd\u4ead","code":"101310214"},{"city":"\u4e07\u5b81","code":"101310215"},{"city":"\u9675\u6c34","code":"101310216"},{"city":"\u897f\u6c99","code":"101310217"},{"city":"\u5357\u6c99\u5c9b","code":"101310220"},{"city":"\u4e50\u4e1c","code":"101310221"},{"city":"\u4e94\u6307\u5c71","code":"101310222"},{"city":"\u743c\u5c71","code":"101310102"},{"city":"\u957f\u6c99","code":"101250101"},{"city":"\u682a\u6d32","code":"101250301"},{"city":"\u8861\u9633","code":"101250401"},{"city":"\u90f4\u5dde","code":"101250501"},{"city":"\u5e38\u5fb7","code":"101250601"},{"city":"\u76ca\u9633","code":"101250700"},{"city":"\u5a04\u5e95","code":"101250801"},{"city":"\u90b5\u9633","code":"101250901"},{"city":"\u5cb3\u9633","code":"101251001"},{"city":"\u5f20\u5bb6\u754c","code":"101251101"},{"city":"\u6000\u5316","code":"101251201"},{"city":"\u9ed4\u9633","code":"101251301"},{"city":"\u6c38\u5dde","code":"101251401"},{"city":"\u5409\u9996","code":"101251501"},{"city":"\u6e58\u6f6d","code":"101250201"},{"city":"\u5357\u4eac","code":"101190101"},{"city":"\u9547\u6c5f","code":"101190301"},{"city":"\u82cf\u5dde","code":"101190401"},{"city":"\u5357\u901a","code":"101190501"},{"city":"\u626c\u5dde","code":"101190601"},{"city":"\u5bbf\u8fc1","code":"101191301"},{"city":"\u5f90\u5dde","code":"101190801"},{"city":"\u6dee\u5b89","code":"101190901"},{"city":"\u8fde\u4e91\u6e2f","code":"101191001"},{"city":"\u5e38\u5dde","code":"101191101"},{"city":"\u6cf0\u5dde","code":"101191201"},{"city":"\u65e0\u9521","code":"101190201"},{"city":"\u76d0\u57ce","code":"101190701"},{"city":"\u54c8\u5c14\u6ee8","code":"101050101"},{"city":"\u7261\u4e39\u6c5f","code":"101050301"},{"city":"\u4f73\u6728\u65af","code":"101050401"},{"city":"\u7ee5\u5316","code":"101050501"},{"city":"\u9ed1\u6cb3","code":"101050601"},{"city":"\u53cc\u9e2d\u5c71","code":"101051301"},{"city":"\u4f0a\u6625","code":"101050801"},{"city":"\u5927\u5e86","code":"101050901"},{"city":"\u4e03\u53f0\u6cb3","code":"101051002"},{"city":"\u9e21\u897f","code":"101051101"},{"city":"\u9e64\u5c97","code":"101051201"},{"city":"\u9f50\u9f50\u54c8\u5c14","code":"101050201"},{"city":"\u5927\u5174\u5b89\u5cad","code":"101050701"},{"city":"\u957f\u6625","code":"101060101"},{"city":"\u5ef6\u5409","code":"101060301"},{"city":"\u56db\u5e73","code":"101060401"},{"city":"\u767d\u5c71","code":"101060901"},{"city":"\u767d\u57ce","code":"101060601"},{"city":"\u8fbd\u6e90","code":"101060701"},{"city":"\u677e\u539f","code":"101060801"},{"city":"\u5409\u6797","code":"101060201"},{"city":"\u901a\u5316","code":"101060501"},{"city":"\u6c88\u9633","code":"101070101"},{"city":"\u978d\u5c71","code":"101070301"},{"city":"\u629a\u987a","code":"101070401"},{"city":"\u672c\u6eaa","code":"101070501"},{"city":"\u4e39\u4e1c","code":"101070601"},{"city":"\u846b\u82a6\u5c9b","code":"101071401"},{"city":"\u8425\u53e3","code":"101070801"},{"city":"\u961c\u65b0","code":"101070901"},{"city":"\u8fbd\u9633","code":"101071001"},{"city":"\u94c1\u5cad","code":"101071101"},{"city":"\u671d\u9633","code":"101071201"},{"city":"\u76d8\u9526","code":"101071301"},{"city":"\u5927\u8fde","code":"101070201"},{"city":"\u9526\u5dde","code":"101070701"}]
', true);
            $extra['require_refresh'] = true;
            $extra['current_data_version'] = $current_data_version;
            $extra['data'] = $list;
            $this->apiSuccess('获取全国城市列表成功', null, $extra);
        }
    }

    /**
     * 获取城市列表数据
     * @param int $data_version
     */
    public function getCityList($data_version=0){
        $current_data_version = 4;
        $data_version = (int)$data_version;
        if($data_version!=0||$current_data_version<=$data_version){
            $this->apiSuccess('城市数据未变更，不需要更新', null);
        } else {
            $list = json_decode('[
    {
        "city":"武汉",
        "code":"101200101"
    },
    {
        "city":"北京",
        "code":"101010100"
    },
    {
        "city":"上海",
        "code":"101020100"
    },
    {
        "city":"广州",
        "code":"101280101"
    },
    {
        "city":"深圳",
        "code":"101280601"
    },
    {
        "city":"南京",
        "code":"101190101"
    },
    {
        "city":"重庆",
        "code":"101040100"
    },
    {
        "city":"天津",
        "code":"101030100"
    },
    {
        "city":"大连",
        "code":"101070201"
    },
    {
        "city":"沈阳",
        "code":"101070101"
    },
    {
        "city":"成都",
        "code":"101270101"
    },
    {
        "city":"长沙",
        "code":"101250101"
    },
    {
        "city":"青岛",
        "code":"101120201"
    },
    {
        "city":"杭州",
        "code":"101210101"
    },
    {
        "city":"厦门",
        "code":"101230201"
    },
    {
        "city":"西安",
        "code":"101110101"
    },
    {
        "city":"郑州",
        "code":"101180101"
    },
    {
        "city":"南昌",
        "code":"101240101"
    },
    {
        "city":"合肥",
        "code":"101220101"
    },
    {
        "city":"临沂",
        "code":"101120901"
    },
    {
        "city":"济南",
        "code":"101120101"
    },
    {
        "city":"哈尔滨",
        "code":"101050101"
    },
    {
        "city":"贵阳",
        "code":"101260101"
    },
    {
        "city":"太原",
        "code":"101100101"
    },
    {
        "city":"兰州",
        "code":"101160101"
    },
    {
        "city":"石家庄",
        "code":"101090101"
    },
    {
        "city":"苏州",
        "code":"101190401"
    },
    {
        "city":"福州",
        "code":"101230101"
    },
    {
        "city":"吉林",
        "code":"101060201"
    },
    {
        "city":"长春",
        "code":"101060101"
    },
    {
        "city":"泰安",
        "code":"101120801"
    },
    {
        "city":"秦皇岛",
        "code":"101091101"
    },
    {
        "city":"徐州",
        "code":"101190801"
    },
    {
        "city":"宜昌",
        "code":"101200901"
    },
    {
        "city":"株洲",
        "code":"101250301"
    },
    {
        "city":"周口",
        "code":"101181401"
    },
    {
        "city":"洛阳",
        "code":"101180901"
    },
    {
        "city":"芜湖",
        "code":"101220301"
    },
    {
        "city":"马鞍山",
        "code":"101220501"
    },
    {
        "city":"绵阳",
        "code":"101270401"
    },
    {
        "city":"南宁",
        "code":"101300101"
    },
    {
        "city":"昆明",
        "code":"101290101"
    },
    {
        "city":"唐山",
        "code":"101090501"
    },
    {
        "city":"邯郸",
        "code":"101091001"
    },
    {
        "city":"呼和浩特",
        "code":"101080101"
    },
    {
        "city":"扬州",
        "code":"101190601"
    },
    {
        "city":"淮安",
        "code":"101190901"
    },
    {
        "city":"无锡",
        "code":"101190201"
    },
    {
        "city":"泉州",
        "code":"101230501"
    },
    {
        "city":"佛山",
        "code":"101280800"
    },
    {
        "city":"东莞",
        "code":"101281601"
    },
    {
        "city":"中山",
        "code":"101281701"
    },
    {
        "city":"珠海",
        "code":"101280701"
    },
    {
        "city":"三亚",
        "code":"101310201"
    },
    {
        "city":"海口",
        "code":"101310101"
    },
    {
        "city":"济宁",
        "code":"101310102"
    },
    {
        "city":"泸州",
        "code":"101310103"
    },
    {
        "city":"常州",
        "code":"101310104"
    },
    {
        "city":"宁波",
        "code":"101310105"
    },
    {
        "city":"余姚",
        "code":"101310106"
    }
]', true);
            $extra['require_refresh'] = true;
            $extra['current_data_version'] = $current_data_version;
            $extra['data'] = $list;
            $this->apiSuccess('获取城city列表成功', null, $extra);
        }
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
     * @param int $version
     */
    public function localOrganizationList($uid=0, $city=null, $type=null,
                                          $name=null, $page=1, $count=10,
                                          $version=0){
        if($uid==0){
            $uid = is_login();
        }
        $model = M('Organization');
        $select_where = "application_status=2 and status=1";
        if(!empty($city)){
            if($city == '吉林'){//区分吉林省和吉林市
                $select_where = $select_where . " and city like '% " .$city . "%'";
            }else{
                $select_where = $select_where . " and city like '%" .$city . "%'";
            }
        }
        if(!empty($type)){
            if($type != '软件' && $type != '留学' && $type != '手绘'){
                $type = '软件';
            }
            $type_id = M('OrganizationTag')->where('type=7 and value=\''.$type.'\'')->getField('id');
            $select_where = $select_where . " and type=".$type_id;
        }
        if((float)$version<=2.7){
            $type = "软件";
            $type_id = M('OrganizationTag')->where('type=7 and value=\''.$type.'\'')->getField('id');
            $select_where = $select_where . " and type=".$type_id;
        }
        if(!empty($name)){
            $select_where = $select_where . " and name like '%".$name."%'";
        }
        $org_list = $model->field('id, name, slogan, city,type, view_count, logo, light_authentication,sort, advantage, is_listen_preview, listen_preview_text')->order("sort asc")
            ->where($select_where)->page($page, $count)->select();
        $totalCount = $model->where($select_where)->count();

        foreach($org_list as &$org){
            $org_id = $org['id'];
            if((float)$version<3.0){
                unset($org['advantage']);
                unset($org['is_listen_preview']);
                unset($org['listen_preview_text']);
            }
            if((float)$version>=2.95){
                $org['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($org_id);
            } else {
                $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
            }
            $org['followCount'] = $this->getFollowCount($org_id);
            if(floatval($version) > 2.7){
                $org['enrollCount'] = $this->getTeachingCourseEnrollCount($org_id);
            }else{
                $org['enrollCount'] = $this->getEnrollCount($org_id);
            }
            if(floatval($version) > 2.5){
                $org['type_tag'] = $this->getOrganizationType($org['type']);
            }else{
                unset($org['type_tag']);
            }
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
            if((float)$version>=2.9&&(float)$version<3.02){
                $course_promotion_model = new Model();
                $org_promotion_list = $course_promotion_model->query('
                SELECT DISTINCT (promotion_id), teaching_course_id from hisihi_teaching_course_organization_promotion_relation
                where status=1 and organization_id='.$org_id);
                $promotion_list = array();
                foreach($org_promotion_list as &$promotion){
                    $coupon_list = M('TeachingCourseCouponRelation')
                        ->where(array('teaching_course_id'=>$promotion['teaching_course_id'], 'status'=>1))
                        ->select();
                    $valid_coupon_count = 0;
                    foreach($coupon_list as $_coupon){
                        $now = time();
                        $is_valid = M('Coupon')
                            ->where('end_time>='.$now.' and status=1 and id='.$_coupon['coupon_id'])
                            ->count();
                        if($is_valid){
                            $valid_coupon_count++;
                        }
                    }
                    if($valid_coupon_count>0){
                        $obj = M('Promotion')->field('id, title, tag_url')
                            ->where('id='.$promotion['promotion_id'])->find();
                        $money = $this->getMaxMoneyCouponByOrganizationIdAndPromotionId($org_id, $obj['id']);
                        if($money){
                            $obj['title'] = $obj['title'].', 最高优惠'.$money.'元';
                        }
                        $obj['detail_web_url'] = C('HOST_NAME_PREFIX').'api.php?s=/Promotion/promotion_detail/promotion_id/'.$obj['id'].'/organization_id/'.$org_id;
                        $obj['share_detail_web_url'] = C('HOST_NAME_PREFIX').'api.php?s=/Promotion/promotion_detail_share/promotion_id/'.$obj['id'].'/organization_id/'.$org_id;
                        $promotion_list[] = $obj;
                    }
                }
                $pid_array = array();
                $uni_promotion_list = array();
                foreach($promotion_list as &$promotion){
                    if((in_array($promotion['id'], $pid_array))){
                        unset($promotion);
                    } else {
                        $pid_array[] = $promotion['id'];
                        $uni_promotion_list[] = $promotion;
                    }
                }
                $org['promotion_list'] = $uni_promotion_list;
            }
            // 抵扣券信息
            if((float)$version>=3.02){
                $course_rebate_model = new Model();
                $time = time();
                $course_rebate_list = $course_rebate_model->query(
                                        "SELECT rebate.id, rebate.name, rebate.value,
                    rebate.rebate_value FROM
                    hisihi_organization_teaching_course course,
                    hisihi_teaching_course_rebate_relation crr,
                    hisihi_rebate rebate
                    where course.organization_id=".$org_id." and rebate.buy_end_time > ".$time."
                    and crr.teaching_course_id=course.id
                    and crr.rebate_id=rebate.id and course.status=1
                    and crr.status=1 and rebate.status=1 order by rebate.rebate_value desc");
                $org['rebate_list'] = $course_rebate_list;
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
    public function searchOrganization($name=null, $page=1, $count=10, $version){
        if(empty($name)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $model = M('Organization');
        $select_where = "application_status=2 and status=1 and name like '%".$name."%'";
        if((float)$version<=2.7){
            $type = "软件";
            $type_id = M('OrganizationTag')->where('type=7 and value=\''.$type.'\'')->getField('id');
            $select_where = $select_where . " and type=".$type_id;
        }
        $org_list = $model->field('id, name, slogan, city, view_count, logo, light_authentication')
                ->where($select_where)->page($page, $count)->select();
        $totalCount = $model->where("application_status=2 and status=1 and name like '%".$name."%'")->count();
        $uid = is_login();
        foreach($org_list as &$org){
            $org_id = $org['id'];
            if((float)$version>=2.95){
                $org['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($org_id);
            } else {
                $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
            }
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
     * @param null $type
     * @param null $version
     * @return mixed
     */
    public function appGetTeacherList($organization_id=0,$page = 1, $count = 10, $type=null, $version=null){
        if($organization_id==0){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $Model = new \Think\Model();
        $totalCount = $Model->query('select count(*) as count from (select distinct uid from hisihi_organization_relation where `status`=1 and `group`=6 and `organization_id`='.$organization_id.')m');
        $totalCount = $totalCount[0]['count'];
        if((float)$version>=2.8){
            $teacher_ids = M('OrganizationRelation')->distinct('uid')
                ->field('uid')
                ->where(array('organization_id'=>$organization_id,'status'=>1,'group'=>6))
                ->order('create_time desc')
                ->select();
        } else {
            $teacher_ids = M('OrganizationRelation')->distinct('uid')->field('uid')
                ->where(array('organization_id'=>$organization_id,'status'=>1,'group'=>6))
                ->order('create_time desc')
                ->page($page, $count)->select();
        }
        $org_name = M('Organization')->where(array('id'=>$organization_id))->getField('name');
        foreach($teacher_ids as &$teacher){
            $teacher = $this->findTeacherById($teacher['uid'], $version);
            $teacher['info']['institution'] = $org_name;
            if((float)$version>=2.8){
                $lecture_info = M('OrganizationRelation')->field('teacher_good_at_subjects, teacher_introduce, teacher_group_id')->where(array('organization_id'=>$organization_id,'uid'=>$teacher['uid']))->limit(1)->find();
                $teacher['info']['teacher_good_at_subjects'] = $lecture_info['teacher_good_at_subjects'];
                $teacher['info']['teacher_introduce'] = $lecture_info['teacher_introduce'];
                $teacher['info']['teacher_group'] = M('OrganizationLectureGroup')->where('id='.$lecture_info['teacher_group_id'])->getField('title');
            }
        }
        unset($teacher);
        if($type=="view") {
            return $teacher_ids;
        } else {
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
     * @param null $version
     */
    public function shareOrganization($organization_id=0, $version=null){
        $result = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->find();
        if($result){
            if((float)$version>=2.95){
                $extra['org_share_url'] = "api.php/Organization/OrganizationBasicInfo_v2_9_5/organization_id/".$organization_id;
            } else {
                $extra['org_share_url'] = "api.php/Organization/OrganizationBasicInfo/organization_id/".$organization_id;
            }
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
     * 机构web详情页 v2.9.5
     * @param int $organization_id
     */
    public function OrganizationBasicInfo_v2_9_5($organization_id=0){
        $this->assign("organization_id", $organization_id);
        $organization_name = M('Organization')->where(array('id'=>$organization_id))->getField('name');
        $this->assign("organization_name", $organization_name);
        $this->display('orgbasicinfo_v2_9_5');
    }

    /**
     * 机构报名
     * @param int $organization_id
     * @param null $student_name
     * @param null $phone_num
     * @param null $student_university
     * @param null $course_id
     */
    public function enroll($organization_id=0,$student_name=null,
                           $phone_num=null,$student_university=null,$course_id=null,$qq=null){
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
        $data['qq'] = $qq;
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
     * @param null $type
     * @param int $version
     * @return mixed
     */
    public function appGetStudentWorks($organization_id=null, $page=1, $count=3, $type=null, $version=0){
        if(!$organization_id){
            $this->apiError(-1, '传入机构id不能为空');
        }
        $model = M('OrganizationResource');
        $map['organization_id'] = $organization_id;
        $map['type'] = 1;
        $map['status'] = 1;
        $totalCount = $model->where($map)->count();
        $field = 'id, url, description, create_time';
        if(floatval($version) > 2.7){
            $field = $field.', author_avatar, author_name, author_company';
        }
        $list = $model->field($field)->order('create_time desc')->where($map)->page($page, $count)->select();
        foreach ($list as &$work) {
            $pic_url = $work['url'];
            $new_pic_url = preg_replace("/.oss-cn-qingdao.aliyuncs.com/", ".img-cn-qingdao.aliyuncs.com", $pic_url);
            $new_pic_url = $new_pic_url . '@info';
            $origin_img_info = getOssImgSizeInfo($new_pic_url);
            $img_info = json_decode($origin_img_info);
            $src_size = Array();
            $src_size['width'] = $img_info->width; // width
            $src_size['height'] = $img_info->height; // height
            $work['picture'] = array(
                'url'=>$pic_url,
                'size'=>$src_size
            );
            $pic_small = $pic_url . '@50p';
            $new_pic_url = preg_replace("/.oss-cn-qingdao.aliyuncs.com/", ".img-cn-qingdao.aliyuncs.com", $pic_small);
            $new_pic_url = $new_pic_url . '&info';
            $origin_img_info = getOssImgSizeInfo($new_pic_url);
            $img_info = json_decode($origin_img_info);
            $thumb_size = Array();
            $thumb_size['width'] = $img_info->width; // width
            $thumb_size['height'] = $img_info->height; // height
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
     * @param null $type
     * @return mixed
     */
    public function appGetOrganizationEnvironment($organization_id=null, $page=1, $count=3, $type=null){
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
            $new_pic_url = preg_replace("/.oss-cn-qingdao.aliyuncs.com/", ".img-cn-qingdao.aliyuncs.com", $pic_url);
            $new_pic_url = $new_pic_url . '@info';
            $origin_img_info = getOssImgSizeInfo($new_pic_url);
            $img_info = json_decode($origin_img_info);
            $src_size = Array();
            $src_size['width'] = $img_info->width; // width
            $src_size['height'] = $img_info->height; // height
            $work['picture'] = array(
                'url'=>$pic_url,
                'size'=>$src_size
            );
            $pic_small = $pic_url . '@50p';
            $new_pic_url = preg_replace("/.oss-cn-qingdao.aliyuncs.com/", ".img-cn-qingdao.aliyuncs.com", $pic_small);
            $new_pic_url = $new_pic_url . '&info';
            $origin_img_info = getOssImgSizeInfo($new_pic_url);
            $img_info = json_decode($origin_img_info);
            $thumb_size = Array();
            $thumb_size['width'] = $img_info->width; // width
            $thumb_size['height'] = $img_info->height; // height
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
            $courseInfo['ViewCount'] = $courseInfo['view_count'];//兼容iOS老版本
            $courseInfo['organization'] = $this->findOrganizationById($courseInfo['organization_id']);
            if((int)$courseInfo['lecturer']!=0){
                $lecturer_info = $this->findTeacherById($courseInfo['lecturer']);
                if(!empty($lecturer_info)){
                    $courseInfo['lecturer'] = $lecturer_info;
                    $courseInfo['lecturer']['info']['institution'] = $courseInfo['organization']['name'];
                }
            }else{
                $courseInfo['lecturer'] = null;
            }
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
            $map['organization_id'] = $organization_id;
            $map['authentication_id'] = $config['id'];
            if($authModel->where($map)->find()){
                $config['status'] = true;
            } else {
                $config['status'] = false;
            }
            if($config['id']==4){ // 嘿设汇认证
                $org_info =M('Organization')->field('light_authentication')
                    ->where('id='.$organization_id)->find();
                if($org_info['light_authentication']){
                    $config['status'] = true;
                }
            }
        }
        return $config_list;
    }

    /**
     * 获取机构的认证信息v2.9.5  需要对“嘿设汇认证”做特殊标记
     * @param $organization_id
     */
    public function getAuthenticationInfo_v2_9_5($organization_id=0){
        $model = M('OrganizationAuthenticationConfig');
        $authModel = M('OrganizationAuthentication');
        $config_list = $model->field('id, name, pic_url, disable_pic_url, tag_pic_url, content, default_display')->where('status=1 and flag=0')->select();
        foreach($config_list as &$config){
            $map['organization_id'] = $organization_id;
            $map['authentication_id'] = $config['id'];
            if($authModel->where($map)->find()){
                $config['status'] = true;
            } else {
                $config['status'] = false;
            }
            if($config['id']==4){ // 嘿设汇认证
                $org_info =M('Organization')->field('light_authentication')
                    ->where('id='.$organization_id)->find();
                if($org_info['light_authentication']){
                    $config['status'] = true;
                }
                $config['hisihi_add_v'] = true;
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

    private function getOrganizationType($type=null){
        $value = M('OrganizationTag')->where('status = 1 and type=7 and id='.$type)->getField('extra');
        if(empty($value)){
            return null;
        }else{
            return $value;
        }
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
     * @param null $version
     * @return null
     */
    private function findTeacherById($teacher_id=null, $version=null){
        if(empty($teacher_id)){
            return null;
            //$this->apiError(-1,'老师id不能为空');
        }
        $teacher['uid'] = $teacher_id;
        if((float)$version>=2.8){
            $teacher['info'] = query_user(array('avatar256', 'avatar128', 'group', 'nickname'), $teacher_id);
        } else {
            $isfollowing = M('Follow')->where(array('who_follow'=>get_uid(),'follow_who'=>$teacher_id))->find();
            $isfans = M('Follow')->where(array('who_follow'=>$teacher_id,'follow_who'=>get_uid()))->find();
            $isfollowing = $isfollowing ? 2:0;
            $isfans = $isfans ? 1:0;
            $teacher['relationship'] = $isfollowing | $isfans;
            $teacher['info'] = query_user(array('avatar256', 'avatar128', 'group', 'nickname'), $teacher_id);
        }
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
     * @param float $version
     * @return mixed
     */
    public function findOrganizationById($organization_id=null, $version=2.9){
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
            if((float)$version>=2.95){
                $organization['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($organization_id);
            } else {
                $organization['authentication'] = $this->getAuthenticationInfo($organization_id);
            }
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

    /**
     * 获取400电话
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

    /**
     * 获取机构主页banner
     * @param int $page
     * @param int $per_page
     */
    public function getBanner($page=1, $per_page=5){
        $org_banner_list = M('InformationFlowBanner')->field('id, pic_url, url')->order("create_time desc")
            ->where('status=1 and show_pos=0')->page($page, $per_page)->select();
        $total_count = M('InformationFlowBanner')->where('status=1 and show_pos=0')->count();
        $extra['data'] = $org_banner_list;
        $extra['total_count'] = $total_count;
        $this->apiSuccess('获取机构banner列表成功', null, $extra);
    }

    /**
     * 用户找机构请求
     * @param null $username
     * @param null $city_name
     * @param null $phone_num
     * @param string $content
     */
    public function findOrganizationRequest($username=null, $city_name=null, $phone_num=null, $content=""){
        if($city_name==null||$phone_num==null){
            $this->apiError(-1, "城市和电话不能为空");
        }
        $model = M('UserFindOrgRequest');
        $data['username'] = $username;
        $data['city_name'] = $city_name;
        $data['phone_num'] = $phone_num;
        $data['content'] = $content;
        $data['create_time'] = time();
        $model->add($data);
        $this->apiSuccess("处理成功");
    }

    /**
     * 获取帮助找到机构总人数
     */
    public function getHelpUsersToFindOrganizationTotalCount(){
        $model = M('OrganizationTag');
        $fakeInfo = $model->where('type=6')->field('value')->find();
        $total_count = $fakeInfo['value'];
        $this->apiSuccess('获取帮助找到机构总人数成功', null, array("total_count"=>$total_count));
    }

    /**
     * 获取机构大全的筛选类型列表
     */
    public function getCateOrgSearchTypeList(){
        $model = M('OrganizationTag');
        $list = $model->field('id, value')->where('type=7 and status=1')->select();
        $list[] = array('id'=>'0', 'value'=>'全部');
        $this->apiSuccess('获取筛选类型成功', null, array('data'=>$list));
    }

    /**
     * 获取机构大全省份列表
     */
    public function getOrgProvinceList(){
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
            $data['province'] = mb_substr($data['province'], 0, mb_strlen($data['province'], 'utf-8')-1, 'utf-8');
        } else {
            $data['province'] = '湖北';
        }
        $data['value'] = $data['province'].'机构';

        $model = M('Province');
        $province_list = $model->field('province_name')->select();
        $result = array();
        foreach ($province_list as $province) {
            if($data['province']!==$province['province_name']){
                $object['province'] = $province['province_name'];
                $object['value'] = $object['province'] . '机构';
                $result[] = $object;
            }
        }
        array_unshift($result, array('value'=>'推荐机构'), $data);
        $this->apiSuccess('获取机构大全省份列表成功', null, array('data'=>$result));
    }

    /**
     * 根据省份名称和类型来筛选机构
     * @param int $uid
     * @param null $province
     * @param int $type
     * @param int $page
     * @param int $count
     */
    public function filterOrgByProvinceAndType($uid=0, $province=null, $type=0,
                                               $page=1, $count=10, $version=0){
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $tmparray = explode('com.xuniu.hisihi/16041801', $user_agent);
        if(count($tmparray)>1){
            $page += 1;

        }

        if($uid==0){
            $uid = is_login();
        }
        $model = M('Organization');
        if($type!=0){
            $select_where = "status=1 and type=".$type." and city like '%".$province."%' and application_status=2";
        } else {
            $select_where = "status=1 and city like '%".$province."%' and application_status=2";
        }
        if((float)$version<=2.7){
            $type = "软件";
            $type_id = M('OrganizationTag')->where('type=7 and value=\''.$type.'\'')->getField('id');
            $select_where = $select_where . " and type=".$type_id;
        }
        $org_list = $model->field('id, name, slogan, city, type, view_count, logo, light_authentication, sort')->order("sort asc")
            ->where($select_where)->page($page, $count)->select();
        $totalCount = $model->where($select_where)->count();
        foreach($org_list as &$org){
            $org_id = $org['id'];
            if(empty($org['logo'])){
                $org['logo']='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            $org['type_tag'] = $this->getOrganizationType($org['type']);

            if((float)$version>=2.95){
                $org['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($org_id);
            } else {
                $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
            }
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
     * 获取推荐机构列表
     * @param int $uid
     * @param int $type
     * @param int $page
     * @param int $count
     */
    public function getRecommendOrganization($uid=0, $type=0, $page=1, $count=10, $version=0){
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $tmparray = explode('com.xuniu.hisihi/16041801', $user_agent);
        if(count($tmparray)>1){
            $page += 1;

        }
        if($uid==0){
            $uid = is_login();
        }
        $model = M('Organization');
        if($type!=0){
            $select_where = "status=1 and application_status=2 and is_recommend=1 and type=".$type;
        } else {
            $select_where = "status=1 and application_status=2 and is_recommend=1";
        }
        if((float)$version<=2.7){
            $type = "软件";
            $type_id = M('OrganizationTag')->where('type=7 and value=\''.$type.'\'')->getField('id');
            $select_where = $select_where . " and type=".$type_id;
        }
        $org_list = $model->field('id, name, slogan, city, type, view_count, logo, light_authentication, sort')->order("sort asc")
            ->where($select_where)->page($page, $count)->select();
        $totalCount = $model->where($select_where)->count();
        foreach($org_list as &$org){
            $org_id = $org['id'];
            if(empty($org['logo'])){
                $org['logo']='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            $org['type_tag'] = $this->getOrganizationType($org['type']);
            if((float)$version>=2.95){
                $org['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($org_id);
            } else {
                $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
            }
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
        $this->apiSuccess('获取机构推荐列表成功', null, $data);
    }

    /**
     * 获取客服400电话
     */
    public function getServicePhoneNum(){
        $phone = $this->get400PhoneNum();
        $data['phone_num'] = $phone;
        $this->apiSuccess('获取机构客服电话成功', null, $data);
    }

    /**
     * 2.8机构列表软件热门视频
     * @param null $city
     * @param string $type
     * @param null $version
     * @param int $page
     * @param int $count
     */
    public function getLocationSoftwareHotTeachingCourse($city=null, $type='软件',
                                                         $version=null, $page=1, $count=2){
        $model = M('Organization');
        $select_where = "application_status=2 and status=1";
        if(!empty($city)){
            if($city == '吉林'){//区分吉林省和吉林市
                $select_where = $select_where . " and city like '% " .$city . "%'";
            }else{
                $select_where = $select_where . " and city like '%" .$city . "%'";
            }
        }
        if($type != '软件' && $type != '留学' && $type != '手绘'){
            $type = '软件';
        }
        $type_id = M('OrganizationTag')->where('type=7 and value=\''.$type.'\'')->getField('id');
        if(!empty($type_id)){
            $select_where = $select_where . " and type=".$type_id;
        }
        if(!empty($name)){
            $select_where = $select_where . " and name like '%".$name."%'";
        }
        $org_list = $model->field('id')->where($select_where)->select();
        $org_id_list = array();
        foreach($org_list as $org){
            $org_id_list[] = $org['id'];
        }
        $where['organization_id'] = array('in', $org_id_list);
        $where['is_hot'] = 1;
        $where['status'] = array('gt', 0);
        $totalCount = M('OrganizationTeachingCourse')->where($where)->count();
        $list = M('OrganizationTeachingCourse')->field('id, organization_id, course_name, cover_pic, start_course_time, lesson_period, student_num, lecture_name, price, already_registered')
            ->where($where)->order('create_time desc')->page($page, $count)->select();
        foreach($list as &$course){
            $org_info = $model->field('name')->where('id='.$course['organization_id'])->find();
            if($org_info){
                $course['organization_name'] = $org_info['name'];
            } else {
                $course['organization_name'] = null;
            }
            if((float)$version>=2.9) {
                $tccr = M('TeachingCourseCouponRelation')
                    ->where('status=1 and teaching_course_id='.$course['id'])
                    ->order('create_time desc')->limit(0, 1)->find();
                if($tccr){
                    $coupon_info = M('Coupon')->where('status=1 and id='.$tccr['coupon_id'])->find();
                    unset($coupon_info['create_time']);
                    unset($coupon_info['status']);
                    unset($coupon_info['create_time']);
                    unset($coupon_info['service_condition']);
                    unset($coupon_info['using_method']);
                    unset($coupon_info['instructions_for_use']);
                    $sel_where['coupon_id'] = $coupon_info['id'];
                    $sel_where['uid'] = is_login();
                    $sel_where['status'] = array('gt', 0);
                    $sel_where['teaching_course_id'] = $course['id'];
                    $obtain = M('UserCoupon')->where($sel_where)->find();
                    if($obtain){
                        $coupon_info['is_obtain'] = true;
                        $coupon_info['obtain_id'] = $obtain['id'];
                    } else {
                        $coupon_info['is_obtain'] = false;
                    }
                    $sel_where['status'] = 2;
                    if(M('UserCoupon')->where($sel_where)->count()){
                        $coupon_info['is_used'] = true;
                    } else {
                        $coupon_info['is_used'] = false;
                    }
                    if(time() > $coupon_info['end_time']){
                        $coupon_info['is_out_of_date'] = true;
                    }else{
                        $coupon_info['is_out_of_date'] = false;
                    }
                    $obj['coupon_info'] = $coupon_info;
                    $coupon_list = array();
                    $coupon_list[] = $obj;
                    $course['coupon_list'] = $coupon_list;
                } else {
                    $course['coupon_list'] = null;
                }
            }
        }
        if(empty($list)){
            $list = null;
        }
        if(empty($totalCount)){
            $totalCount=0;
        }
        $this->apiSuccess('获取热门课程列表成功', null, array('data'=>$list, 'totalCount'=>$totalCount));
    }

    /**
     * 获取诚信机构列表
     * @param bool|false $well_chosen
     * @param string $type
     * @param null $city
     * @param null $version
     * @param bool|false $is_prelisten
     * @param bool|false $has_coupon
     * @param int $page
     * @param int $count
     */
    public function getIntegrityOrganization($well_chosen=false, $type='软件', $city=null, $version=null,
                                             $is_prelisten=null, $has_coupon=null, $page=1, $count=10){
        $uid = is_login();
        $model = M('Organization');
        if($type != '软件' && $type != '留学' && $type != '手绘'){
            $type = '软件';
        }
        $type_id = M('OrganizationTag')->where('type=7 and value=\''.$type.'\'')->getField('id');
        $select_where['status'] = 1;
        $select_where['application_status'] = 2;
        if($type != '留学'){
            $select_where['light_authentication'] = 1;
        }
        $select_where['type'] = $type_id;
        if(!empty($city)){
            $select_where['city'] = array('like','%'.$city.'%');
        }
        if($well_chosen === 'true'){//兼容安卓2.9bug
            $well_chosen = 0;
        }
        if($well_chosen){
            if($type == '留学'){
                $select_where['is_hot'] = 1;
            }else{
                $select_where['well_chosen'] = 1;
            }
        }
        if((float)$version>=3.00){
            if($is_prelisten!=null){
                $select_where['is_listen_preview'] = $is_prelisten;
            }
            if($has_coupon==1){
                $ids_list = D('App/TeachingCourse','Service')->getHasCouponOrganizationIdList();
                $select_where['id'] = array('in', $ids_list);
            }
            if($has_coupon==0){
                $ids_list = D('App/TeachingCourse','Service')->getHasCouponOrganizationIdList();
                $select_where['id'] = array('not in', $ids_list);
            }
        }
        $org_list = $model->field('id, name, slogan, city, type, advantage, view_count, logo, light_authentication, sort')
            ->order("sort asc, create_time desc")
            ->where($select_where)->page($page, $count)->select();
        $totalCount = $model->where($select_where)->count();
        foreach($org_list as &$org){
            $org_id = $org['id'];
            if(empty($org['logo'])){
                $org['logo']='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            $org['type_tag'] = $this->getOrganizationType($org['type']);
            if((float)$version>=2.95){
                $org['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($org_id);
            } else {
                $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
            }
            $org['followCount'] = $this->getFollowCount($org_id);
            $org['enrollCount'] = $this->getTeachingCourseEnrollCount($org_id);
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
            if((float)$version>=2.9){
                $course_promotion_model = new Model();
                $org_promotion_list = $course_promotion_model->query('
                SELECT DISTINCT (promotion_id), teaching_course_id from hisihi_teaching_course_organization_promotion_relation
                where status=1 and organization_id='.$org_id);
                $promotion_list = array();
                foreach($org_promotion_list as &$promotion){
                    $coupon_list = M('TeachingCourseCouponRelation')
                        ->where(array('teaching_course_id'=>$promotion['teaching_course_id'], 'status'=>1))
                        ->select();
                    $valid_coupon_count = 0;
                    foreach($coupon_list as $_coupon){
                        $now = time();
                        $is_valid = M('Coupon')
                            ->where('end_time>='.$now.' and status=1 and id='.$_coupon['coupon_id'])
                            ->count();
                        if($is_valid){
                            $valid_coupon_count++;
                        }
                    }
                    if($valid_coupon_count>0){
                        $obj = M('Promotion')->field('id, title, tag_url')
                            ->where('id='.$promotion['promotion_id'])->find();
                        $money = $this->getMaxMoneyCouponByOrganizationIdAndPromotionId($org_id, $obj['id']);
                        if($money){
                            $obj['title'] = $obj['title'].', 最高优惠'.$money.'元';
                        }
                        $obj['detail_web_url'] = C('HOST_NAME_PREFIX').'api.php?s=/Promotion/promotion_detail/promotion_id/'.$obj['id'].'/organization_id/'.$org_id;
                        $obj['share_detail_web_url'] = C('HOST_NAME_PREFIX').'api.php?s=/Promotion/promotion_detail_share/promotion_id/'.$obj['id'].'/organization_id/'.$org_id;
                        $promotion_list[] = $obj;
                    }
                }
                $pid_array = array();
                $uni_promotion_list = array();
                foreach($promotion_list as &$promotion){
                    if((in_array($promotion['id'], $pid_array))){
                        unset($promotion);
                    } else {
                        $pid_array[] = $promotion['id'];
                        $uni_promotion_list[] = $promotion;
                    }
                }
                $org['promotion_list'] = $uni_promotion_list;
            }

            if(floatval($version) < 3.0){
                unset($org['advantage']);
            }
        }
/*        //机构列表按报名数排序
        $sort = array(
            'direction'=>'SORT_DESC',
            'field'=>'enrollCount'
        );
        $org_list = $this->sort_list($sort, $org_list);*/

        //机构列表按排序字段排序
        $sort2 = array(
            'direction'=>'SORT_ASC',
            'field'=>'sort'
        );
        //$org_list = $this->sort_list($sort2, $org_list);
        //去掉sort字段
        foreach($org_list as &$org){
            unset($org['sort']);
        }
        $data['totalCount'] = $totalCount;
        $data['list'] = $org_list;
        $this->apiSuccess('获取诚信机构列表成功', null, $data);
    }

    /**
     * 获取机构专业列表
     * @param bool|false $is_hot
     */
    public function getOrganizationMajor($is_hot=false){
        $map['type'] = 8;
        if($is_hot){
            $map['status'] = 2;
        }else{
            $map['status'] = array('gt', 0);
        }
        $major = M('OrganizationTag')->field('id, value, extra')->where($map)->order('id asc')->select();
        $data['list'] = $major;
        $this->apiSuccess('获取机构专业列表成功', null, $data);
    }

    /**
     * 根据专业获取机构列表
     * @param null $major_id
     * @param string $type
     * @param null $city
     * @param null $version
     * @param int $page
     * @param int $count
     */
    public function getOrganizationByMajor($major_id=null, $type='手绘', $city=null,
                                           $version=null, $page=1, $count=10){
        $uid = is_login();
        $model = M('Organization');
        if(!empty($major_id)){
            $org_id = M('OrganizationTagRelation')->field('organization_id')
                ->where('status=1 and tag_type=8 and tag_id='.$major_id)->select();
            $org_in_major = array();
            foreach($org_id as $item){
                $org_in_major[] = $item['organization_id'];
            }
            $select_where['id'] = array('in', $org_in_major);
        }
        if($type != '软件' && $type != '留学' && $type != '手绘'){
            $type = '手绘';
        }
        $type_id = M('OrganizationTag')->where('type=7 and value=\''.$type.'\'')->getField('id');
        $select_where['status'] = 1;
        $select_where['application_status'] = 2;
        $select_where['type'] = $type_id;
        if(!empty($city)){
            $select_where['city'] = array('like','%'.$city.'%');
        }
        $org_list = $model->field('id, name, slogan, city, type, view_count, logo, light_authentication, sort')
            ->order("sort asc, create_time desc")
            ->where($select_where)->page($page, $count)->select();
        $totalCount = $model->where($select_where)->count();
        foreach($org_list as &$org){
            $org_id = $org['id'];
            if(empty($org['logo'])){
                $org['logo']='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            $org['type_tag'] = $this->getOrganizationType($org['type']);
            if((float)$version>=2.95){
                $org['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($org_id);
            } else {
                $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
            }
            $org['followCount'] = $this->getFollowCount($org_id);
            $org['enrollCount'] = $this->getTeachingCourseEnrollCount($org_id);
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
            if((float)$version>=2.9){
                $course_promotion_model = new Model();
                $org_promotion_list = $course_promotion_model->query('
                SELECT DISTINCT (promotion_id), teaching_course_id from hisihi_teaching_course_organization_promotion_relation
                where status=1 and organization_id='.$org_id);
                $promotion_list = array();
                foreach($org_promotion_list as &$promotion){
                    $coupon_list = M('TeachingCourseCouponRelation')
                        ->where(array('teaching_course_id'=>$promotion['teaching_course_id'], 'status'=>1))
                        ->select();
                    $valid_coupon_count = 0;
                    foreach($coupon_list as $_coupon){
                        $now = time();
                        $is_valid = M('Coupon')
                            ->where('end_time>='.$now.' and status=1 and id='.$_coupon['coupon_id'])
                            ->count();
                        if($is_valid){
                            $valid_coupon_count++;
                        }
                    }
                    if($valid_coupon_count>0){
                        $obj = M('Promotion')->field('id, title, tag_url')
                            ->where('id='.$promotion['promotion_id'])->find();
                        $money = $this->getMaxMoneyCouponByOrganizationIdAndPromotionId($org_id, $obj['id']);
                        if($money){
                            $obj['title'] = $obj['title'].', 最高优惠'.$money.'元';
                        }
                        $obj['detail_web_url'] = C('HOST_NAME_PREFIX').'api.php?s=/Promotion/promotion_detail/promotion_id/'.$obj['id'].'/organization_id/'.$org_id;
                        $obj['share_detail_web_url'] = C('HOST_NAME_PREFIX').'api.php?s=/Promotion/promotion_detail_share/promotion_id/'.$obj['id'].'/organization_id/'.$org_id;
                        $promotion_list[] = $obj;
                    }
                }
                $pid_array = array();
                $uni_promotion_list = array();
                foreach($promotion_list as &$promotion){
                    if((in_array($promotion['id'], $pid_array))){
                        unset($promotion);
                    } else {
                        $pid_array[] = $promotion['id'];
                        $uni_promotion_list[] = $promotion;
                    }
                }
                $org['promotion_list'] = $uni_promotion_list;
            }
        }
        /*//机构列表按报名数排序
        $sort = array(
            'direction'=>'SORT_DESC',
            'field'=>'enrollCount'
        );
        $org_list = $this->sort_list($sort, $org_list);*/

        //机构列表按排序字段排序
        $sort2 = array(
            'direction'=>'SORT_ASC',
            'field'=>'sort'
        );
        //$org_list = $this->sort_list($sort2, $org_list);
        //去掉sort字段
        foreach($org_list as &$org){
            unset($org['sort']);
        }
        $data['totalCount'] = $totalCount;
        $data['list'] = $org_list;
        $this->apiSuccess('获取机构列表成功', null, $data);
    }

    /**
     * 根据大学获取留学机构列表
     * @param null $university_id
     * @param string $type
     * @param null $city
     * @param null $version
     * @param int $page
     * @param int $count
     */
    public function getOrganizationByUniversity($university_id=null, $type='留学', $city=null,
                                                $version=null, $page=1, $count=10){
        $uid = is_login();
        $model = M('Organization');
        if(!empty($university_id)){
            $org_id = M('OrganizationToUniversity')->field('organization_id')
                ->where('status=1 and university_id='.$university_id)->group('organization_id')->select();
            $org_in_major = array();
            foreach($org_id as $item){
                $org_in_major[] = $item['organization_id'];
            }
            $select_where['id'] = array('in', $org_in_major);
        }
        if($type != '软件' && $type != '留学' && $type != '手绘'){
            $type = '手绘';
        }
        $type_id = M('OrganizationTag')->where('type=7 and value=\''.$type.'\'')->getField('id');
        $select_where['status'] = 1;
        $select_where['application_status'] = 2;
        $select_where['type'] = $type_id;
        if(!empty($city)){
            $select_where['city'] = array('like','%'.$city.'%');
        }
        $org_list = $model->field('id, name, slogan, city, type, view_count, logo, light_authentication, sort')
            ->order("sort asc, create_time desc")
            ->where($select_where)->page($page, $count)->select();
        $totalCount = $model->where($select_where)->count();
        foreach($org_list as &$org){
            $org_id = $org['id'];
            if(empty($org['logo'])){
                $org['logo']='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            $org['type_tag'] = $this->getOrganizationType($org['type']);
            if((float)$version>=2.95){
                $org['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($org_id);
            } else {
                $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
            }
            $org['followCount'] = $this->getFollowCount($org_id);
            $org['enrollCount'] = $this->getTeachingCourseEnrollCount($org_id);
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
            if((float)$version>=2.9){
                $course_promotion_model = new Model();
                $org_promotion_list = $course_promotion_model->query('
                SELECT DISTINCT (promotion_id), teaching_course_id from hisihi_teaching_course_organization_promotion_relation
                where status=1 and organization_id='.$org_id);
                $promotion_list = array();
                foreach($org_promotion_list as &$promotion){
                    $coupon_list = M('TeachingCourseCouponRelation')
                        ->where(array('teaching_course_id'=>$promotion['teaching_course_id'], 'status'=>1))
                        ->select();
                    $valid_coupon_count = 0;
                    foreach($coupon_list as $_coupon){
                        $now = time();
                        $is_valid = M('Coupon')
                            ->where('end_time>='.$now.' and status=1 and id='.$_coupon['coupon_id'])
                            ->count();
                        if($is_valid){
                            $valid_coupon_count++;
                        }
                    }
                    if($valid_coupon_count>0){
                        $obj = M('Promotion')->field('id, title, tag_url')
                            ->where('id='.$promotion['promotion_id'])->find();
                        $money = $this->getMaxMoneyCouponByOrganizationIdAndPromotionId($org_id, $obj['id']);
                        if($money){
                            $obj['title'] = $obj['title'].', 最高优惠'.$money.'元';
                        }
                        $obj['detail_web_url'] = C('HOST_NAME_PREFIX').'api.php?s=/Promotion/promotion_detail/promotion_id/'.$obj['id'].'/organization_id/'.$org_id;
                        $obj['share_detail_web_url'] = C('HOST_NAME_PREFIX').'api.php?s=/Promotion/promotion_detail_share/promotion_id/'.$obj['id'].'/organization_id/'.$org_id;
                        $promotion_list[] = $obj;
                    }
                }
                $pid_array = array();
                $uni_promotion_list = array();
                foreach($promotion_list as &$promotion){
                    if((in_array($promotion['id'], $pid_array))){
                        unset($promotion);
                    } else {
                        $pid_array[] = $promotion['id'];
                        $uni_promotion_list[] = $promotion;
                    }
                }
                $org['promotion_list'] = $uni_promotion_list;
            }
        }
/*        //机构列表按报名数排序
        $sort = array(
            'direction'=>'SORT_DESC',
            'field'=>'enrollCount'
        );
        $org_list = $this->sort_list($sort, $org_list);
*/
        //机构列表按排序字段排序
        $sort2 = array(
            'direction'=>'SORT_ASC',
            'field'=>'sort'
        );
        //$org_list = $this->sort_list($sort2, $org_list);

        //去掉sort字段
        foreach($org_list as &$org){
            unset($org['sort']);
        }
        $data['totalCount'] = $totalCount;
        $data['list'] = $org_list;
        $this->apiSuccess('获取机构列表成功', null, $data);
    }

    /**
     * 获取大学里机构的课程
     * @param null $organization_id
     * @param null $university_id
     * @return null
     */
    public function getUniversityCourses($organization_id=null, $university_id=null, $count=1){
        $courses = M('OrganizationToUniversity')
            ->field('teaching_course_id')
            ->where('status=1 and teaching_course_id>0 and university_id='.$university_id.' and organization_id='.$organization_id)->order('id desc')->limit($count)
            ->select();
        $courses_list = null;
        if($courses){
            $courses_id = array();
            foreach($courses as $item){
                $courses_id[] = $item['teaching_course_id'];
            }
            $map['id'] = array('in', $courses_id);
            $map['status'] = 1;
            $map['organization_id'] = $organization_id;
            $courses_list = M('OrganizationTeachingCourse')->field('id, course_name')->where($map)->select();
        }
        return $courses_list;
    }

    /**
     * 获取机构下课程的报名数
     * @param null $organization_id
     * @return int
     */
    public function getTeachingCourseEnrollCount($organization_id=null){
        $map['organization_id'] = $organization_id;
        $map['status'] = 1;
        $courses = M('OrganizationTeachingCourse')->field('id')->where($map)->select();
        $enroll_count = 0;
        if($courses){
            $courses_id = array();
            foreach($courses as $item){
                $courses_id[] = $item['id'];
            }
            $where['course_id'] = array('in', $courses_id);
            $where['status'] = 1;
            $enroll_count = M("OrganizationTeachingCourseEnroll")->where($where)->count();
        }
        return $enroll_count;
    }

    public function searchOrgAndUniversity($name='', $type=null, $page=1, $count=10, $version=0){
        $uid = is_login();
        $org_model = M('Organization');
        $u_model = M('AbroadUniversity');
        $map['name'] = array('like', '%'.$name.'%');
        $map['status'] = 1;
        $org_map['application_status'] = 2;
        if(empty($type)){
            $org_list = $org_model->field('id, name, slogan, city, type, view_count, logo, light_authentication')
                ->where($map)->where($org_map)->limit(3)->order('id desc')->select();
            $org_count = $org_model->where($map)->where($org_map)->count();
            foreach($org_list as &$org){
                $org_id = $org['id'];
                if(empty($org['logo'])){
                    $org['logo']='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
                }
                $org['type_tag'] = $this->getOrganizationType($org['type']);
                if((float)$version>=2.95){
                    $org['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($org_id);
                } else {
                    $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
                }
                $org['followCount'] = $this->getFollowCount($org_id);
                $org['enrollCount'] = $this->getTeachingCourseEnrollCount($org_id);
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

            $university_list = $u_model->field('id, name, logo_url')
                ->where($map)->limit(3)->order('id desc')->select();
            $university_count = $u_model->where($map)->count();
            foreach($university_list as &$university){
                $u_id = $university['id'];
                $university['organization_total_count'] = $this->getOrgCountInUniversity($u_id);
                $university['enroll_total_count'] = $this->getEnrollCountInUniversity($u_id);
            }
            $data['org_list'] = $org_list;
            $data['org_count'] = $org_count;
            $data['university_list'] = $university_list;
            $data['university_count'] = $university_count;
            $data['type'] = $type;
            $this->apiSuccess('获取搜索列表成功', null, $data);
        }elseif($type == 'organization'){
            $org_list = $org_model->field('id, name, slogan, city, type, view_count, logo, light_authentication')
                ->where($map)->where($org_map)->page($page, $count)->order('sort asc, id desc')->select();
            $org_count = $org_model->where($map)->where($org_map)->count();
            foreach($org_list as &$org){
                $org_id = $org['id'];
                if(empty($org['logo'])){
                    $org['logo']='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
                }
                $org['type_tag'] = $this->getOrganizationType($org['type']);
                if((float)$version>=2.95){
                    $org['authenticationInfo'] = $this->getAuthenticationInfo_v2_9_5($org_id);
                } else {
                    $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
                }
                $org['followCount'] = $this->getFollowCount($org_id);
                $org['enrollCount'] = $this->getTeachingCourseEnrollCount($org_id);
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
            $data['org_count'] = $org_count;
            $data['org_list'] = $org_list;
            $data['type'] = $type;
            $this->apiSuccess('获取机构列表成功', null, $data);
        }elseif($type == 'university'){
            $university_list = $u_model->field('id, name, logo_url')
                ->where($map)->page($page, $count)->order('id desc')->select();
            $university_count = $u_model->where($map)->count();
            foreach($university_list as &$university){
                $u_id = $university['id'];
                $university['organization_total_count'] = $this->getOrgCountInUniversity($u_id);
                $university['enroll_total_count'] = $this->getEnrollCountInUniversity($u_id);
            }
            $data['university_count'] = $university_count;
            $data['university_list'] = $university_list;
            $data['type'] = $type;
            $this->apiSuccess('获取大学列表成功', null, $data);
        }
    }

    /**获取大学下的机构数量
     * @param null $u_id
     * @return mixed
     */
    public function getOrgCountInUniversity($u_id=null){
        $Model = new \Think\Model();
        $org_count = $Model->query('select COUNT(*) as count from (
SELECT
	organization_id
FROM
	hisihi_organization_to_university
WHERE
	`status` = 1
AND university_id = 1
AND teaching_course_id = 0
GROUP BY
	organization_id
) a');
        return $org_count[0]['count'];
    }

    /**获取大学下的课程报名数
     * @param null $u_id
     * @return int
     */
    public function getEnrollCountInUniversity($u_id=null){
        $map['status'] = 1;
        $map['university_id'] = $u_id;
        $map['teaching_course_id'] = array('gt', 0);
        $course = M('OrganizationToUniversity')->where($map)->field('teaching_course_id')->select();
        $enroll_count = 0;
        if($course){
            $course_id = array();
            foreach($course as $item){
                $course_id[] = $item['teaching_course_id'];
            }
            $enroll_map['course_id'] = array('in', $course_id);
            $enroll_map['status'] = 1;
            $enroll_count = M('OrganizationTeachingCourseEnroll')->where($enroll_map)->count();
        }

        return $enroll_count;
    }

    /**
     * 根据id获取大学
     * @param null $u_id
     * @return mixed
     */
    public function findUniversityById($u_id=null){
        $u_model = M('AbroadUniversity');
        $map['status'] = 1;
        $map['id'] = $u_id;
        $university = $u_model->field('id, name, logo_url')
            ->where($map)->find();
        $university['organization_total_count'] = $this->getOrgCountInUniversity($u_id);
        $university['enroll_total_count'] = $this->getEnrollCountInUniversity($u_id);
        return $university;
    }

    /**
     * 收藏机构
     * @param int $uid
     * @param int $organization_id
     */
    public function doFavoriteOrganization($uid=0, $organization_id=0){
        if(empty($organization_id)){
            $this->apiError(-1, '传入机构id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }
        $favorite['appname'] = 'OrganizationInfo';
        $favorite['table'] = 'organization';
        $favorite['row'] = $organization_id;
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

    /**
     * 取消收藏机构
     * @param int $uid
     * @param int $organization_id
     */
    public function undoFavoriteOrganization($uid=0,$organization_id=0){
        if(empty($organization_id)){
            $this->apiError(-1, '传入机构id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }

        $favorite['appname'] = 'OrganizationInfo';
        $favorite['table'] = 'organization';
        $favorite['row'] = $organization_id;
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
     * 机构是否被收藏
     * @param $uid
     * @param $organization_id
     * @return bool
     */
    public function isOrganizationFavorite($uid, $organization_id){
        $favorite['appname'] = 'OrganizationInfo';
        $favorite['table'] = 'organization';
        $favorite['row'] = $organization_id;
        $favorite['uid'] = $uid;
        if (D('Favorite')->where($favorite)->count()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 收藏大学
     * @param int $uid
     * @param int $university_id
     */
    public function doFavoriteUniversity($uid=0, $university_id=0){
        if(empty($university_id)){
            $this->apiError(-1, '传入大学id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }
        $favorite['appname'] = 'University';
        $favorite['table'] = 'university';
        $favorite['row'] = $university_id;
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

    /**
     * 取消收藏大学
     * @param int $uid
     * @param int $university_id
     */
    public function undoFavoriteUniversity($uid=0,$university_id=0){
        if(empty($university_id)){
            $this->apiError(-1, '传入机构id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }

        $favorite['appname'] = 'University';
        $favorite['table'] = 'university';
        $favorite['row'] = $university_id;
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
     * @param int $course_id
     */
    public function showteachingcoursemainpage($course_id=0){
        $model = M('OrganizationTeachingCourse');
        $info = $model->field('organization_id')->where('id='.$course_id)->find();
        $this->assign('course_id', $course_id);
        $this->assign('organization_id', $info['organization_id']);
        $this->display('teaching_course_main_page');
    }


    /**
     * @param int $course_id
     */
    public function showteachingcoursemainpage_v2_9($course_id=0){
        $model = M('OrganizationTeachingCourse');
        $info = $model->field('organization_id')->where('id='.$course_id)->find();
        $this->assign('course_id', $course_id);
        $this->assign('organization_id', $info['organization_id']);
        $this->display('teaching_course_main_page_v2.9');
    }

    /**
     * @param int $course_id
     */
    public function teaching_course_main_page_v3_02($course_id=0){
        $model = M('OrganizationTeachingCourse');
        $info = $model->field('organization_id')->where('id='.$course_id)->find();
        $this->assign('course_id', $course_id);
        $this->assign('organization_id', $info['organization_id']);
        $this->display('teaching_course_main_page_v3.02');
    }

    private function isCouponOutOfDate($end_time){
        $now = time();
        if ($now <= (int)$end_time){
            return false;
        }
        return true;
    }

    /**
     * @param int $organization_id
     * @param float $version
     */
    public function getPromotionCouponList($organization_id=0, $version=2.9){
        if($organization_id==0){
            $this->apiError(-1, '机构id不能为空');
        }
        if((float)$version>=2.95){
            $coupon_list = M()->query('select t2.teaching_course_id, t1.promotion_id, t3.id, t3.name, t3.type, t3.start_time, t3.end_time, t3.money from
hisihi.hisihi_teaching_course_organization_promotion_relation t1,
hisihi.hisihi_teaching_course_coupon_relation t2, hisihi.hisihi_coupon t3
where t1.teaching_course_id=t2.teaching_course_id
            and t2.coupon_id=t3.id and t2.status=1 and t1.status=1 and t3.status=1
            and t1.organization_id='.$organization_id.' order by t3.money desc');
            $model = M('OrganizationTeachingCourse');
            foreach($coupon_list as &$coupon){
                $coupon['is_out_of_date'] = $this->isCouponOutOfDate($coupon['end_time']);
                $course = $model->field('course_name')->where('id='.$coupon['teaching_course_id'])
                    ->find();
                $coupon['course_name'] = $course['course_name'];
                unset($coupon['teaching_course_id']);
            }
            $data['totalCount'] = count($coupon_list);
            $data['list'] = $coupon_list;
            $this->apiSuccess('获取机构优惠券列表成功', null, $data);
        }
        $promotion_list = M()->query('select distinct(promotion_id) from hisihi_teaching_course_organization_promotion_relation where status=1 and organization_id='.$organization_id);
        $_promotion_list = array();
        foreach($promotion_list as $pro){
            $obj = M('Promotion')->field('id, title, little_logo_url')
                ->where('id='.$pro['promotion_id'])
                ->find();
            $now = time();
            $coupon_list = M()->query('select t3.id, t3.name, t3.type, t3.start_time, t3.end_time, t3.money from
hisihi_teaching_course_organization_promotion_relation t1,
hisihi_teaching_course_coupon_relation t2, hisihi_coupon t3 where t1.teaching_course_id=t2.teaching_course_id
 and t2.coupon_id=t3.id and t2.status=1 and t1.status=1 and t3.status=1 and t3.end_time>='.$now.' and t1.promotion_id='.$obj["id"].' and t1.organization_id='.$organization_id.' order by t3.money desc limit 0,2');
            foreach($coupon_list as &$coupon){
                $coupon['is_out_of_date'] = $this->isCouponOutOfDate($coupon['end_time']);
                unset($coupon['teaching_course_id']);
            }
            $obj['coupon_list'] = $coupon_list;
            $obj['detail_web_url'] = C('HOST_NAME_PREFIX').'api.php?s=/Promotion/promotion_detail/promotion_id/'.$obj['id'].'/organization_id/'.$organization_id;
            $obj['share_detail_web_url'] = C('HOST_NAME_PREFIX').'api.php?s=/Promotion/promotion_detail_share/promotion_id/'.$obj['id'].'/organization_id/'.$organization_id;
            $_promotion_list[] = $obj;
        }
        $data['totalCount'] = count($_promotion_list);
        $data['list'] = $_promotion_list;
        $this->apiSuccess('获取机构活动列表成功', null, $data);
    }

    /**
     * 获取活动的最高优惠券金额
     * @param int $organization_id
     * @param int $promotion_id
     * @return int
     */
    public function getMaxMoneyCouponByOrganizationIdAndPromotionId($organization_id=0, $promotion_id=0){
        $money = 0;
        $now = time();
        $coupon_list = M()->query('select t3.id, t3.name, t3.type, t3.start_time, t3.end_time, t3.money from
hisihi_teaching_course_organization_promotion_relation t1,
hisihi_teaching_course_coupon_relation t2, hisihi_coupon t3 where t1.teaching_course_id=t2.teaching_course_id
 and t2.coupon_id=t3.id and t2.status=1 and t1.status=1 and t3.status=1 and t3.end_time>='.$now.' and t1.promotion_id='.$promotion_id.' and t1.organization_id='.$organization_id.' order by t3.money desc limit 0,1');
        foreach($coupon_list as $coupon){
            $money = $coupon['money'];
        }
        return $money;
    }

    /**
     * 机构培训课程收藏
     * @param int $uid
     * @param int $courses_id
     */
    public function doFavoriteTeachingCourses($uid=0, $courses_id=0){
        if(empty($courses_id)){
            $this->apiError(-1, '传入课程id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }

        $favorite['appname'] = 'OrganizationTeachingCourse';
        $favorite['table'] = 'organization_teaching_courses';
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

    /**
     * 取消收藏机构培训课程
     * @param int $uid
     * @param int $courses_id
     */
    public function undoFavoriteTeachingCourses($uid=0,$courses_id=0){
        if(empty($courses_id)){
            $this->apiError(-1, '传入课程id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }

        $favorite['appname'] = 'OrganizationTeachingCourse';
        $favorite['table'] = 'organization_teaching_courses';
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

    public function getTeachingCourseInfo($id){
        $info = M('OrganizationTeachingCourse')->field('id, organization_id, course_name, cover_pic, start_course_time, lesson_period, student_num, lecture_name, price, already_registered')
            ->where('id='.$id)->find();
        return $info;
    }

    /**
     * 预约报名
     * @param null $mobile
     * @param null $username
     * @param int $organization_id
     * @param int $course_id
     * @param null $education
     * @param null $major
     */
    public function yuyue($mobile=null, $username=null, $organization_id=0,
                          $course_id=0, $education=null, $major=null){
        //$this->requireLogin();
        $uid = $this->getUid();
        if(empty($mobile)){
            $this->apiError(-1, "手机号不能为空");
        }
        if($uid==0){
            $data['uid'] = $mobile;
        } else {
            $data['uid'] = $uid;
        }
        $data['mobile'] = $mobile;
        $data['username'] = $username;
        $data['education'] = $education;
        $data['major'] = $major;
        $data['organization_id'] = $organization_id;
        $data['course_id'] = $course_id;
        $data['create_time'] = time();
        $organization = M('Organization')->field('name')->where('id='.$organization_id)->find();
        if(empty($course_id)){
            if(!empty($username)){
                $sms_content = "用户".$username." 手机号".$mobile." 成功报名".$organization['name']."机构，快去处理吧";
            } else {
                $sms_content = "用户".$mobile." 成功报名".$organization['name']."机构，快去处理吧";
            }
        } else {
            if(!$this->isCanSendSMSTeachingCourseFrequence($mobile, $course_id)){
                $this->apiSuccess('预约报名成功!');
            }
            $teaching_course = M('OrganizationTeachingCourse')->field('course_name')->where('id='.$course_id)->find();
            if(!empty($username)){
                $sms_content = "用户".$username." 手机号".$mobile." 成功报名".$teaching_course['course_name']."课程，快去处理吧";
            } else {
                $sms_content = "用户".$mobile." 成功报名".$teaching_course['course_name']."课程，快去处理吧";
            }
        }
        if($this->sendSMS(C('o_phone_array'), $sms_content)){
            $data['send_sms_time'] = time();
        } else {
            $data['send_sms_time'] = 0;
        }
        if($this->isYuyue($data['uid'], $organization_id, $course_id)){
            $this->apiError(-2, '已经预约过');
        }
        M('OrganizationYuyue')->add($data);
        if($course_id!=0){
            M('OrganizationTeachingCourse')->where('id='.$course_id)->setInc('already_registered', 1);
        }
        $this->apiSuccess('预约报名成功!');
    }

    /**
     * 留学大学报考
     * @param null $mobile
     * @param null $username
     * @param int $university_id
     * @param string $education
     * @param string $major
     */
    public function baokao($mobile=null, $username=null, $university_id=0,
                           $education='', $major=''){
        //$this->requireLogin();
        $uid = $this->getUid();
        if(empty($mobile)||empty($university_id)){
            $this->apiError(-1, "大学id和手机号不能为空");
        }
        if($uid==0){
            $data['uid'] = $mobile;
        } else {
            $data['uid'] = $uid;
        }
        $data['mobile'] = $mobile;
        $data['username'] = $username;
        $data['education'] = $education;
        $data['major'] = $major;
        $data['university_id'] = $university_id;
        $data['create_time'] = time();
        $university = M('AbroadUniversity')->field('name')->where('id='.$university_id)->find();
        if(!empty($username)){
            $sms_content = "用户".$username." 手机号".$mobile." 成功报名".$university['name']."大学 ".
                $education . " " . $major ."，快去处理吧";
        } else {
            $sms_content = "用户".$mobile." 成功报名".$university['name']."大学 ".
                $education . " " . $major ."，快去处理吧";
        }
        if($this->sendSMS(C('liuxue_phone_array'), $sms_content)){
            $data['send_sms_time'] = time();
        } else {
            $data['send_sms_time'] = 0;
        }
        if($this->isBaokao($data['uid'], $university_id)){
            $this->apiError(-2, '已经报考过');
        }
        M('OrganizationYuyue')->add($data);
        $this->apiSuccess('大学报考成功!');
    }

    public function isCanSendSMSTeachingCourseFrequence($mobile, $teaching_course_id=0){
        if($teaching_course_id!=0){
            $where_array['mobile'] = $mobile;
            $where_array['course_id'] = $teaching_course_id;
            $yuyue_info = M('OrganizationYuyue')->field('send_sms_time')->where($where_array)->find();
            $last_send_time = (int)$yuyue_info['send_sms_time'];
            $now = time();
            if($now-$last_send_time>=300){
                return true;
            }
            return false;
        }
        return true;
    }

    public function isYuyue($uid=0, $organization_id=0, $teaching_course_id=0){
        $where_array['uid'] = $uid;
        $where_array['organization_id'] = $organization_id;
        $where_array['course_id'] = $teaching_course_id;
        $where_array['status'] = 1;
        $is_yuyue = M('OrganizationYuyue')->where($where_array)->count();
        return $is_yuyue;
    }

    public function isBaokao($uid=0, $university_id=0){
        $where_array['uid'] = $uid;
        $where_array['university_id'] = $university_id;
        $where_array['status'] = 1;
        $is_baokao = M('OrganizationYuyue')->where($where_array)->count();
        return $is_baokao;
    }

    public function sendSMS($mobile=array(), $content=null){
        if(empty($mobile)||empty($content)){
            $this->apiError(-1, '传入手机号为空或短信内容为空');
        }
        $url = C('bmob_sms_url');
        $headers['X-Bmob-Application-Id'] = C('bmob_application_id');
        $headers['X-Bmob-REST-API-Key'] = C('bmob_api_key');
        $headers['Content-Type'] = 'application/json';
        $headerArr = array();
        foreach( $headers as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        foreach($mobile as $phone_num){
            $post_data = array('mobilePhoneNumber'=>urlencode($phone_num), 'content'=>$content);
            $post_data = json_encode($post_data);
            $result = $this->request_by_curl($url, $headerArr, $post_data);
        }
        if($result){
            return true;
        } else {
            return false;
        }
    }

    /**
     * 是否已预约/报名机构
     * @param $uid
     * @param $organization_id
     * @return bool
     */
    public function isYuYueOrganization($uid, $organization_id){
        $where_array['uid'] = $uid;
        $where_array['organization_id'] = $organization_id;
        $where_array['status'] = 1;
        if(M('OrganizationYuyue')->where($where_array)->count()){
            return true;
        }
        return false;
    }

}