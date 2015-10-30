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


class OrganizationController extends AppController
{
    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    /**
     * 获取短信验证码
     * @param $mobile
     */
    public function getSMS($mobile=null){
        if(empty($mobile)){
            $this->apiError(-1, '传入手机号为空');
        } else {
            if(!preg_match('/^1([0-9]{9})/',$mobile)){
                $this->apiError(-2, '传入手机号不符合格式');
            }
        }
        $this->isMobileExist($mobile);
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
            $data['password'] = md5($password);
            $data['create_time'] = time();
            $result = M('OrganizationAdmin')->data($data)->add();
            if($result){
                $this->apiSuccess('注册成功');
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
            $map['mobile'] = $mobile;
            $data['password'] = md5($password);
            $result = M('OrganizationAdmin')->where($map)->save($data);
            if($result){
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
        $map['password'] = md5($password);
        $user = M('OrganizationAdmin')->where($map)->find();
        if($user){
            $org_model = M('Organization');
            $orginfo = $org_model->where('status=1 and id='.$user['organization_id'])->find();
            $logo = $this->fetchImage($orginfo['logo']);
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
            $extra['organization_name'] = $orginfo['name'];
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
            $extra['logo'] = array(
                'id'=>$info['download']['id'],
                'path'=>$info['download']['path']
            );
            $this->apiSuccess("上传Logo成功",null,$extra);
        } else {
            $this->apiError(-1,"上传Logo失败，".$Picture->getError());
        }
    }

    /**
     * 修改机构logo
     * @param int $organization_id
     * @param int $pic_id
     */
    public function updateLogo($organization_id=0, $pic_id=0){
        $this->requireAdminLogin();
        if(empty($organization_id)||empty($pic_id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $model = M('Organization');
        $data['logo'] = $pic_id;
        $result = $model->where('id='.$organization_id)->save($data);
        if($result){
            $this->uploadLogoPicToOSS($pic_id);
            $this->apiSuccess('修改机构logo成功');
        } else {
            $this->apiError(-1, '修改机构logo失败，请重试');
        }
    }

    /**
     * 裁剪图片
     */
    public function tailorPicture(){
        /* 调用文件上传组件上传文件 */
        $Picture = D('Admin/Picture');
        $pic_driver = C('PICTURE_UPLOAD_DRIVER');
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C("UPLOAD_{$pic_driver}_CONFIG")
        ); //TODO:上传到远程服务器
        $path = $info['download']['path'];
        $image = new \Think\Image();
        $org_path = '.'. substr($path,11);
        $image->open($org_path);
        $crop_path = substr($org_path,0,strlen($org_path)-4).'_crop'.substr($org_path,-4);
        $image->crop(50, 50,20,20)->save($crop_path);
        //原图片路径
        $info['org_path'] = $path;
        //裁剪后图片路径
        $info['crop_path'] = $crop_path;
        $crop_md5 = md5_file($crop_path);
        $crop_sha1 = sha1_file($crop_path);
        //图片ID
        $info['pic_id'] = $info['download']['id'];
        $this->apiSuccess("裁剪成功",null,$info);
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
            $data['logo'] = $logo;
            $this->uploadLogoPicToOSS($logo);
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
                $this->apiSuccess('添加机构基本信息成功');
            } else {
                $this->apiError(-1, '添加机构基本信息失败，请重试');
            }
        } else {  // 修改机构基本信息
            $result = $model->where('id='.$organization_id)->save($data);
            if($result){
                $this->apiSuccess('修改机构基本信息成功');
            } else {
                $this->apiError(-1, '修改机构基本信息失败，请重试');
            }
        }
    }

    /**获取机构基本信息
     * @param $organization_id
     */
    public function getBaseInfo($organization_id){
        $this->requireAdminLogin();
        $model=M("Organization");
        $result = $model->where(array('id'=>$organization_id,'status'=>1))
            ->field('name,slogan,location,logo,introduce,advantage,phone_num')->find();
        if($result){
            $logo_id = $result['logo'];
            $result['logo']=array(
                'id'=>$logo_id,
                'url'=>$this->fetchImage($logo_id)
            );
            $advantage = explode("#",$result['advantage']);
            $map['type']=2;
            $map['status']=1;
            $map['id'] = array('in',$advantage);
            $list = M('OrganizationConfig')->field('id, value')->where($map)->select();
            $result['advantage']=$list;
            $extra['data'] = $result;
            $this->apiSuccess("获取机构信息成功",null,$extra);
        }else{
            $this->apiError(-1,"获取机构信息失败");
        }
    }

    /**
     * 获取机构通用优势标签
     */
    public function getCommonAdvantageTags(){
        $model = M('OrganizationConfig');
        $list = $model->field('id, value')->where('type=2 and status=1')->select();
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
            $advantage = explode("#", $advantage);
            $advantage_array = array();
            $t_model = M('OrganizationConfig');
            foreach($advantage as &$advantage_id){
                $markarr = $t_model->field('id, value')->where('status=1 and id='.$advantage_id)->find();
                array_push($advantage_array, $markarr);
            }
            $extra['data'] = $advantage_array;
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
     * 获取机构公告
     * @param int $page
     * @param int $count
     */
    public function getNotice($page=1, $count=10){
        $this->requireAdminLogin();
        $model = M('OrganizationNotice');
        $totoalCount = $model->where('status=1')->count();
        $list = $model->where('status=1')->page($page, $count)->select();
        foreach($list as &$notice){
            $notice['detail_url'] = 'http://115.28.72.197/api.php?s/organization/noticedetail/id/'.$notice['id'];
            unset($notice['content']);
            unset($notice['status']);
        }
        $extra['totalCount'] = $totoalCount;
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
     * @param null $organization_id
     * @param null $pic_id
     * @param null $description
     * @param string $type
     */
    public function studentWorks($organization_id=null, $pic_id=null, $description=null, $type='add'){
        $this->requireAdminLogin();
        if(empty($organization_id)||empty($pic_id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $model = M('OrganizationResource');
        if('add'==$type){  // 添加学生作品
            $data['type'] = 1;
            $data['organization_id'] = $organization_id;
            $data['pic_id'] = $pic_id;
            $data['description'] = $description;
            $data['create_time'] = time();
            $res = $model->add($data);
            if($res){
                $this->uploadLogoPicToOSS($pic_id);
                $this->apiSuccess('添加学生作品成功');
            } else {
                $this->apiError(-1, '添加学生作品失败');
            }
        } else {  // 删除学生作品
            $data['status'] = -1;
            $map['type'] = 1;
            $map['organization_id'] = $organization_id;
            $map['pic_id'] = $pic_id;
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
     * @param null $organization_id
     * @param null $pic_id
     * @param null $description
     * @param string $type
     */
    public function organizationEnvironment($organization_id=null, $pic_id=null, $description=null, $type='add'){
        $this->requireAdminLogin();
        if(empty($organization_id)||empty($pic_id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $model = M('OrganizationResource');
        if('add'==$type){
            $data['type'] = 2;
            $data['organization_id'] = $organization_id;
            $data['pic_id'] = $pic_id;
            $data['description'] = $description;
            $data['create_time'] = time();
            $res = $model->add($data);
            if($res){
                $this->uploadLogoPicToOSS($pic_id);
                $this->apiSuccess('添加机构环境图片成功');
            } else {
                $this->apiError(-1, '添加机构环境图片失败');
            }
        } else {
            $data['status'] = -1;
            $map['type'] = 2;
            $map['organization_id'] = $organization_id;
            $map['pic_id'] = $pic_id;
            $res = $model->where($map)->save($data);
            if($res){
                $this->apiSuccess('删除机构环境图片成功');
            } else {
                $this->apiError(-1, '删除机构环境图片失败');
            }
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
        $list = $model->field('pic_id, description, create_time')->where($map)->page($page, $count)->select();
        foreach ($list as &$work) {
            $pic_id = $work['pic_id'];
            $work['url'] = $this->fetchImage($pic_id);
            unset($work['pic_id']);
        }
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
        $list = $model->field('pic_id, description, create_time')->where($map)->page($page, $count)->select();
        foreach ($list as &$work) {
            $pic_id = $work['pic_id'];
            $work['url'] = $this->fetchImage($pic_id);
            unset($work['pic_id']);
        }
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
        $map['nickname'] = array('like', '%'.$name.'%');
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
        $model = M('OrganizationConfig');
        $data['organization_id'] = $organization_id;
        $data['type'] = 1001;
        $data['value'] = $group_name;
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
        $model = M('OrganizationConfig');
        $data['value'] = $group_name;
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
        $model = M('OrganizationConfig');
        $data['status'] = -1;
        $result = $model->where('id='.$teacher_group_id)->save($data);
        if($result){
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
        if($model->where('`status`=1 and `group`=6 and `uid`='.$uid)->count()){
            $this->apiError(-2, '该老师已经添加过了');
        }
        $result = $model->add($data);
        if($result){
            $extra['relation_id'] = $result;
            $this->apiSuccess("添加成功",null,$extra);
        }else{
            $this->apiError(-1, '新增教师到分组失败');
        }
    }

    /**从分组中移除老师
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
        $model = M('OrganizationConfig');
        $list = $model->field('id, value')->where('status=1 and type=1001 and organization_id='.$organization_id)->select();
        $extra['data'] = $list;
        $this->apiSuccess('获取机构所有分组成功', null, $extra);
    }

    /**
     * 获取所有分组和老师信息
     * @param null $organization_id
     */
    public function getAllGroupsTeachers($organization_id=null){
        $this->requireAdminLogin();
        $model = M('OrganizationConfig');
        $list = $model->field('id, value')->where('status=1 and type=1001 and organization_id='.$organization_id)->select();
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
                'group_name'=> $group['value']
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
        $model = M("OrganizationConfig");
        $result = $model->where('status=1 and type=1002')->field('id,value')->select();
        if($result){
            $extra['data'] = $result;
            $this->apiSuccess('获取视频分类列表成功', null, $extra);
        } else {
            $this->apiError(-1,"获取视频分类列表失败");
        }
    }

    /**
     * 添加机构课程
     * @param null $title
     * @param null $content
     * @param null $img
     * @param null $lecturer
     * @param null $auth
     */
    public function addCourse($organization_id=null, $title=null, $content=null, $img=null, $lecturer=null, $auth=null){
        $model = M('OrganizationCourse');
        $data['organization_id'] = $organization_id;
        $data['title'] = $title;
        $data['content'] = $content;
        $data['img'] = $img;
        /*
         * 需要添加图片到oss
         */
        $data['lecturer'] = $lecturer;
        $data['auth'] = $auth;
        $result = $model->add($data);
        if($result){
            $this->apiSuccess('添加课程成功');
        } else {
            $this->apiError(-1, '保存课程信息失败');
        }
    }

    /**
     * 获取当前机构的所有课程
     * @param null $organization_id
     */
    public function getCourses($organization_id=null){
        $model = M('OrganizationCourse');
        $config_model = M("OrganizationConfig");
        $map['organization_id'] = $organization_id;
        $map['status'] = 1;
        $course_list = $model->field('id, title, content, img, category_id, lecturer, auth, create_time')->where($map)->select();
        $video_course = array();
        foreach($course_list as &$course){
            $category_id = $course['category_id'];
            $category = $config_model->where('status=1 and type=1002 and id='.$category_id)->field('value')->find();
            if($category){
                $course['category_name'] = $category['value'];
            }
            $course['url'] = $this->fetchImage($course['img']);
        }
        $extra['data'] = $video_course;
        $this->apiSuccess('获取所有课程成功', null, $extra);
    }

    /**
     * 获取课程下视频列表
     * @param null $course_id
     */
    public function getCourseVideoList($course_id=null){
        if(empty($course_id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $model = M('OrganizationCourse');
        $video_model = M('OrganizationVideo');
        $config_model = M("OrganizationConfig");
        $member_model = M("Member");
        $map['id'] = $course_id;
        $map['status'] = 1;
        $course = $model->field('id, title, content, img, category_id, lecturer, auth')->where($map)->find();
        $category_id = $course['category_id'];
        $category = $config_model->where('status=1 and type=1002 and id='.$category_id)->field('value')->find();
        if($category){
            $course['category_name'] = $category['value'];
        }
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
    private function isMobileExist($mobile){
        if(empty($mobile)){
            $this->apiError(-1, "传入参数为空");
        }
        $map['status'] = 1;
        $map['mobile'] = $mobile;
        $user = M('OrganizationAdmin')->where($map)->find();
        if($user){
            $extraData['isExist'] = true;
            $this->apiSuccess("该手机号已注册", null, $extraData);
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

}