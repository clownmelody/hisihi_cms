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
            $data['password'] = md5($password);
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
            $data['password'] = md5($password);
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
        $map['password'] = md5($password);
        $user = M('OrganizationAdmin')->where($map)->find();
        if($user){
            $org_model = M('Organization');
            $orginfo = $org_model->where('status=1 and id='.$user['organization_id'])->find();
            $logo = $this->fetchImage($orginfo['logo']);
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
                $uid = is_login();
                $res = M('OrganizationAdmin')->where(array('id'=>$uid,'status'=>1))->save(array('organization_id'=>$result));
                if($res){
                    $extra['organization_id'] = $result;
                    $extra['organization_name'] = $name;
                    $url = $this->fetchImage($logo);
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
            $logo = $this->fetchImage($logo_id);
            if(!$logo){
                $logo='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            $result['logo']=array(
                'id'=>$logo_id,
                'url'=>$logo
            );
            $advantage = $result['advantage'];
            $advantage = stripslashes($advantage);
            $advantage = json_decode($advantage,true);
            $advantage_array = array();
            $cmodel = M('OrganizationConfig');
            foreach($advantage as &$markid){
                $advantageid = $markid['id'];
                if(0 == $advantageid){
                    $markobj = array(
                        'id'=>(string)$markid['id'],
                        'value'=>$markid['value']
                    );
                    $advantage_array[] = $markobj;
                }else{
                    $markarr = $cmodel->field('id,value')->where('type=2 and status=1 and id='.$advantageid)->find();
                    if($markarr){
                        $markobj = array(
                            'id'=>$markarr['id'],
                            'value'=>$markarr['value']
                        );
                        $advantage_array[] = $markobj;
                    }
                }
            }
            $result['advantage']=$advantage_array;
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
            $advantage = stripslashes($advantage);
            $advantage = json_decode($advantage,true);
            $advantage_array = array();
            $cmodel = M('OrganizationConfig');
            foreach($advantage as &$markid){
                $advantageid = $markid['id'];
                if(0 == $advantageid){
                    $markobj = array(
                        'id'=>(string)$markid['id'],
                        'value'=>$markid['value']
                    );
                    $advantage_array[] = $markobj;
                }else{
                    $markarr = $cmodel->field('id,value')->where('type=2 and status=1 and id='.$advantageid)->find();
                    if($markarr){
                        $markobj = array(
                            'id'=>$markarr['id'],
                            'value'=>$markarr['value']
                        );
                        $advantage_array[] = $markobj;
                    }
                }
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
            $notice['detail_url'] = 'http://hisihi.com/api.php?s=/organization/noticedetail/id/'.$notice['id'];
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
            $data['type'] = 1;
            $data['organization_id'] = $organization_id;
            $data['pic_id'] = $pic_id;
            $data['description'] = $description;
            $data['create_time'] = time();
            $res = $model->add($data);
            if($res){
                $this->uploadLogoPicToOSS($pic_id);
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
            $data['type'] = 2;
            $data['organization_id'] = $organization_id;
            $data['pic_id'] = $pic_id;
            $data['description'] = $description;
            $data['create_time'] = time();
            $res = $model->add($data);
            if($res){
                $this->uploadLogoPicToOSS($pic_id);
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

    /**更新图片描述
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
        $list = $model->field('id, pic_id, description, create_time')->where($map)->page($page, $count)->select();
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
        $list = $model->field('id, pic_id, description, create_time')->where($map)->page($page, $count)->select();
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

    /**添加机构课程
     * @param null $organization_id
     * @param null $title
     * @param null $content
     * @param null $category_id
     * @param null $img
     * @param null $lecturer
     * @param null $auth
     */
    public function addCourse($id = null,$organization_id=null, $title=null, $content=null,$category_id=null, $img=null, $lecturer=null, $auth=1){
        $this->requireAdminLogin();
        $model = M('OrganizationCourse');
        if(!empty($id)){
            $data['organization_id'] = $organization_id;
            $data['title'] = $title;
            $data['content'] = $content;
            $data['img'] = $img;
            $data['category_id']=$category_id;
            $data['lecturer'] = $lecturer;
            $data['auth'] = $auth;
            $data['update_time'] = time();
            $result = $model->where(array('id'=>$id,'status'=>1))->save($data);
            if($result){
                $this->uploadLogoPicToOSS($img);
                $extra['courses_id'] = $id;
                $this->apiSuccess('修改课程成功',null,$extra);
            } else {
                $this->apiError(-1, '修改课程信息失败');
            }
        }else{
            $data['organization_id'] = $organization_id;
            $data['title'] = $title;
            $data['content'] = $content;
            $data['img'] = $img;
            $data['category_id']=$category_id;
            $data['lecturer'] = $lecturer;
            $data['auth'] = $auth;
            $data['create_time'] = time();
            $data['update_time'] = time();
            $result = $model->add($data);
            if($result){
                $this->uploadLogoPicToOSS($img);
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
        $this->requireAdminLogin();
        $model = M('OrganizationCourse');
        $config_model = M("OrganizationConfig");
        $map['organization_id'] = $organization_id;
        $map['status'] = 1;
        $totalCount = $model->where($map)->count();
        $course_list = $model->field('id, title, content, img, category_id, view_count, lecturer, auth, create_time')->where($map)->page($page, $count)->select();
        $video_course = array();
        foreach($course_list as &$course){
            $category_id = $course['category_id'];
            $category = $config_model->where('status=1 and type=1002 and id='.$category_id)->field('value')->find();
            if($category){
                $course['category_name'] = $category['value'];
            }
            $course['url'] = $this->fetchImage($course['img']);
            $video_course[] = $course;
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $video_course;
        $this->apiSuccess('获取所有课程成功', null, $extra);
    }

    /**删除课程
     * @param int $id
     */
    public function deleteCourses($id=0){
        $this->requireAdminLogin();
        if(!$id){
            $this->apiError(-1,"参数不能为空");
        }else{
            $model = M('OrganizationCourse');
            $data['status'] = -1;
            $result = $model->where(array('id'=>$id))->save($data);
            if($result){
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
        $course['img_url'] = $this->fetchImage($course['img']);
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
            $this->apiSuccess('获取位置成功', null, $data);
        } else {
            $data['city'] = '武汉';
            $this->apiSuccess('定位失败，返回默认城市', null, $data);
        }
    }

    /**
     * 获取城市列表数据
     */
    public function getCityList(){
        $list = json_decode('{"list":[{"province":"北京","city":[{"city":"北京", "code":"101010100"}]},{"province":"天津市","市":[{"city":"天津", "code":"101030100"}]},{"province":"上海","市":[{"city":"上海", "code":"101020100"}]},{"province":"河北","市":[{"city":"石家庄","code":"101090101"},{"city":"张家口","code":"101090301"},{"city":"承德","code":"101090402"},{"city":"唐山","code":"101090501"},{"city":"秦皇岛","code":"101091101"},{"city":"沧州","code":"101090701"},{"city":"衡水","code":"101090801"},{"city":"邢台","code":"101090901"},{"city":"邯郸","code":"101091001"},{"city":"保定","code":"101090201"},{"city":"廊坊","code":"101090601"}]},{"province":"河南","市":[{"city":"郑州","code":"101180101"},{"city":"新乡","code":"101180301"},{"city":"许昌","code":"101180401"},{"city":"平顶山","code":"101180501"},{"city":"信阳","code":"101180601"},{"city":"南阳","code":"101180701"},{"city":"开封","code":"101180801"},{"city":"洛阳","code":"101180901"},{"city":"商丘","code":"101181001"},{"city":"焦作","code":"101181101"},{"city":"鹤壁","code":"101181201"},{"city":"濮阳","code":"101181301"},{"city":"周口","code":"101181401"},{"city":"漯河","code":"101181501"},{"city":"驻马店","code":"101181601"},{"city":"三门峡","code":"101181701"},{"city":"济源","code":"101181801"},{"city":"安阳","code":"101180201"}]},{"province":"安徽","市":[{"city":"合肥","code":"101220101"},{"city":"芜湖","code":"101220301"},{"city":"淮南","code":"101220401"},{"city":"马鞍山","code":"101220501"},{"city":"安庆","code":"101220601"},{"city":"宿州","code":"101220701"},{"city":"阜阳","code":"101220801"},{"city":"亳州","code":"101220901"},{"city":"黄山","code":"101221001"},{"city":"滁州","code":"101221101"},{"city":"淮北","code":"101221201"},{"city":"铜陵","code":"101221301"},{"city":"宣城","code":"101221401"},{"city":"六安","code":"101221501"},{"city":"巢湖","code":"101221601"},{"city":"池州","code":"101221701"},{"city":"蚌埠","code":"101220201"}]},{"province":"浙江","市":[{"city":"杭州","code":"101210101"},{"city":"舟山","code":"101211101"},{"city":"湖州","code":"101210201"},{"city":"嘉兴","code":"101210301"},{"city":"金华","code":"101210901"},{"city":"绍兴","code":"101210501"},{"city":"台州","code":"101210601"},{"city":"温州","code":"101210701"},{"city":"丽水","code":"101210801"},{"city":"衢州","code":"101211001"},{"city":"宁波","code":"101210401"}]},{"province":"重庆","市":[{"city":"重庆","code":"101040100"},{"city":"合川","code":"101040300"},{"city":"南川","code":"101040400"},{"city":"江津","code":"101040500"},{"city":"万盛","code":"101040600"},{"city":"渝北","code":"101040700"},{"city":"北碚","code":"101040800"},{"city":"巴南","code":"101040900"},{"city":"长寿","code":"101041000"},{"city":"黔江","code":"101041100"},{"city":"万州天城","code":"101041200"},{"city":"万州龙宝","code":"101041300"},{"city":"涪陵","code":"101041400"},{"city":"开县","code":"101041500"},{"city":"城口","code":"101041600"},{"city":"云阳","code":"101041700"},{"city":"巫溪","code":"101041800"},{"city":"奉节","code":"101041900"},{"city":"巫山","code":"101042000"},{"city":"潼南","code":"101042100"},{"city":"垫江","code":"101042200"},{"city":"梁平","code":"101042300"},{"city":"忠县","code":"101042400"},{"city":"石柱","code":"101042500"},{"city":"大足","code":"101042600"},{"city":"荣昌","code":"101042700"},{"city":"铜梁","code":"101042800"},{"city":"璧山","code":"101042900"},{"city":"丰都","code":"101043000"},{"city":"武隆","code":"101043100"},{"city":"彭水","code":"101043200"},{"city":"綦江","code":"101043300"},{"city":"酉阳","code":"101043400"},{"city":"秀山","code":"101043600"},{"city":"沙坪坝","code":"101043700"},{"city":"永川","code":"101040200"}]},{"province":"福建","市":[{"city":"福州","code":"101230101"},{"city":"泉州","code":"101230501"},{"city":"漳州","code":"101230601"},{"city":"龙岩","code":"101230701"},{"city":"晋江","code":"101230509"},{"city":"南平","code":"101230901"},{"city":"厦门","code":"101230201"},{"city":"宁德","code":"101230301"},{"city":"莆田","code":"101230401"},{"city":"三明","code":"101230801"}]},{"province":"甘肃","市":[{"city":"兰州","code":"101160101"},{"city":"平凉","code":"101160301"},{"city":"庆阳","code":"101160401"},{"city":"武威","code":"101160501"},{"city":"金昌","code":"101160601"},{"city":"嘉峪关","code":"101161401"},{"city":"酒泉","code":"101160801"},{"city":"天水","code":"101160901"},{"city":"武都","code":"101161001"},{"city":"临夏","code":"101161101"},{"city":"合作","code":"101161201"},{"city":"白银","code":"101161301"},{"city":"定西","code":"101160201"},{"city":"张掖","code":"101160701"}]},{"province":"广东","市":[{"city":"广州","code":"101280101"},{"city":"惠州","code":"101280301"},{"city":"梅州","code":"101280401"},{"city":"汕头","code":"101280501"},{"city":"深圳","code":"101280601"},{"city":"珠海","code":"101280701"},{"city":"佛山","code":"101280800"},{"city":"肇庆","code":"101280901"},{"city":"湛江","code":"101281001"},{"city":"江门","code":"101281101"},{"city":"河源","code":"101281201"},{"city":"清远","code":"101281301"},{"city":"云浮","code":"101281401"},{"city":"潮州","code":"101281501"},{"city":"东莞","code":"101281601"},{"city":"中山","code":"101281701"},{"city":"阳江","code":"101281801"},{"city":"揭阳","code":"101281901"},{"city":"茂名","code":"101282001"},{"city":"汕尾","code":"101282101"},{"city":"韶关","code":"101280201"}]},{"province":"广西","市":[{"city":"南宁","code":"101300101"},{"city":"柳州","code":"101300301"},{"city":"来宾","code":"101300401"},{"city":"桂林","code":"101300501"},{"city":"梧州","code":"101300601"},{"city":"防城港","code":"101301401"},{"city":"贵港","code":"101300801"},{"city":"玉林","code":"101300901"},{"city":"百色","code":"101301001"},{"city":"钦州","code":"101301101"},{"city":"河池","code":"101301201"},{"city":"北海","code":"101301301"},{"city":"崇左","code":"101300201"},{"city":"贺州","code":"101300701"}]},{"province":"贵州","市":[{"city":"贵阳","code":"101260101"},{"city":"安顺","code":"101260301"},{"city":"都匀","code":"101260401"},{"city":"兴义","code":"101260906"},{"city":"铜仁","code":"101260601"},{"city":"毕节","code":"101260701"},{"city":"六盘水","code":"101260801"},{"city":"遵义","code":"101260201"},{"city":"凯里","code":"101260501"}]},{"province":"云南","市":[{"city":"昆明","code":"101290101"},{"city":"红河","code":"101290301"},{"city":"文山","code":"101290601"},{"city":"玉溪","code":"101290701"},{"city":"楚雄","code":"101290801"},{"city":"普洱","code":"101290901"},{"city":"昭通","code":"101291001"},{"city":"临沧","code":"101291101"},{"city":"怒江","code":"101291201"},{"city":"香格里拉","code":"101291301"},{"city":"丽江","code":"101291401"},{"city":"德宏","code":"101291501"},{"city":"景洪","code":"101291601"},{"city":"大理","code":"101290201"},{"city":"曲靖","code":"101290401"},{"city":"保山","code":"101290501"}]},{"province":"内蒙古","市":[{"city":"呼和浩特","code":"101080101"},{"city":"乌海","code":"101080301"},{"city":"集宁","code":"101080401"},{"city":"通辽","code":"101080501"},{"city":"阿拉善左旗","code":"101081201"},{"city":"鄂尔多斯","code":"101080701"},{"city":"临河","code":"101080801"},{"city":"锡林浩特","code":"101080901"},{"city":"呼伦贝尔","code":"101081000"},{"city":"乌兰浩特","code":"101081101"},{"city":"包头","code":"101080201"},{"city":"赤峰","code":"101080601"}]},{"province":"江西","市":[{"city":"南昌","code":"101240101"},{"city":"上饶","code":"101240301"},{"city":"抚州","code":"101240401"},{"city":"宜春","code":"101240501"},{"city":"鹰潭","code":"101241101"},{"city":"赣州","code":"101240701"},{"city":"景德镇","code":"101240801"},{"city":"萍乡","code":"101240901"},{"city":"新余","code":"101241001"},{"city":"九江","code":"101240201"},{"city":"吉安","code":"101240601"}]},{"province":"湖北","市":[{"city":"武汉","code":"101200101"},{"city":"黄冈","code":"101200501"},{"city":"荆州","code":"101200801"},{"city":"宜昌","code":"101200901"},{"city":"恩施","code":"101201001"},{"city":"十堰","code":"101201101"},{"city":"神农架","code":"101201201"},{"city":"随州","code":"101201301"},{"city":"荆门","code":"101201401"},{"city":"天门","code":"101201501"},{"city":"仙桃","code":"101201601"},{"city":"潜江","code":"101201701"},{"city":"襄樊","code":"101200201"},{"city":"鄂州","code":"101200301"},{"city":"孝感","code":"101200401"},{"city":"黄石","code":"101200601"},{"city":"咸宁","code":"101200701"}]},{"province":"四川","市":[{"city":"成都","code":"101270101"},{"city":"自贡","code":"101270301"},{"city":"绵阳","code":"101270401"},{"city":"南充","code":"101270501"},{"city":"达州","code":"101270601"},{"city":"遂宁","code":"101270701"},{"city":"广安","code":"101270801"},{"city":"巴中","code":"101270901"},{"city":"泸州","code":"101271001"},{"city":"宜宾","code":"101271101"},{"city":"内江","code":"101271201"},{"city":"资阳","code":"101271301"},{"city":"乐山","code":"101271401"},{"city":"眉山","code":"101271501"},{"city":"凉山","code":"101271601"},{"city":"雅安","code":"101271701"},{"city":"甘孜","code":"101271801"},{"city":"阿坝","code":"101271901"},{"city":"德阳","code":"101272001"},{"city":"广元","code":"101272101"},{"city":"攀枝花","code":"101270201"}]},{"province":"宁夏","市":[{"city":"银川","code":"101170101"},{"city":"中卫","code":"101170501"},{"city":"固原","code":"101170401"},{"city":"石嘴山","code":"101170201"},{"city":"吴忠","code":"101170301"}]},{"province":"青海province","市":[{"city":"西宁","code":"101150101"},{"city":"黄南","code":"101150301"},{"city":"海北","code":"101150801"},{"city":"果洛","code":"101150501"},{"city":"玉树","code":"101150601"},{"city":"海西","code":"101150701"},{"city":"海东","code":"101150201"},{"city":"海南","code":"101150401"}]},{"province":"山东","市":[{"city":"济南","code":"101120101"},{"city":"潍坊","code":"101120601"},{"city":"临沂","code":"101120901"},{"city":"菏泽","code":"101121001"},{"city":"滨州","code":"101121101"},{"city":"东营","code":"101121201"},{"city":"威海","code":"101121301"},{"city":"枣庄","code":"101121401"},{"city":"日照","code":"101121501"},{"city":"莱芜","code":"101121601"},{"city":"聊城","code":"101121701"},{"city":"青岛","code":"101120201"},{"city":"淄博","code":"101120301"},{"city":"德州","code":"101120401"},{"city":"烟台","code":"101120501"},{"city":"济宁","code":"101120701"},{"city":"泰安","code":"101120801"}]},{"province":"陕西province","市":[{"city":"西安","code":"101110101"},{"city":"延安","code":"101110300"},{"city":"榆林","code":"101110401"},{"city":"铜川","code":"101111001"},{"city":"商洛","code":"101110601"},{"city":"安康","code":"101110701"},{"city":"汉中","code":"101110801"},{"city":"宝鸡","code":"101110901"},{"city":"咸阳","code":"101110200"},{"city":"渭南","code":"101110501"}]},{"province":"山西","市":[{"city":"太原","code":"101100101"},{"city":"临汾","code":"101100701"},{"city":"运城","code":"101100801"},{"city":"朔州","code":"101100901"},{"city":"忻州","code":"101101001"},{"city":"长治","code":"101100501"},{"city":"大同","code":"101100201"},{"city":"阳泉","code":"101100301"},{"city":"晋中","code":"101100401"},{"city":"晋城","code":"101100601"},{"city":"吕梁","code":"101101100"}]},{"province":"新疆","市":[{"city":"乌鲁木齐","code":"101130101"},{"city":"石河子","code":"101130301"},{"city":"昌吉","code":"101130401"},{"city":"吐鲁番","code":"101130501"},{"city":"库尔勒","code":"101130601"},{"city":"阿拉尔","code":"101130701"},{"city":"阿克苏","code":"101130801"},{"city":"喀什","code":"101130901"},{"city":"伊宁","code":"101131001"},{"city":"塔城","code":"101131101"},{"city":"哈密","code":"101131201"},{"city":"和田","code":"101131301"},{"city":"阿勒泰","code":"101131401"},{"city":"阿图什","code":"101131501"},{"city":"博乐","code":"101131601"},{"city":"克拉玛依","code":"101130201"}]},{"province":"西藏","市":[{"city":"拉萨","code":"101140101"},{"city":"山南","code":"101140301"},{"city":"阿里","code":"101140701"},{"city":"昌都","code":"101140501"},{"city":"那曲","code":"101140601"},{"city":"日喀则","code":"101140201"},{"city":"林芝","code":"101140401"}]},{"province":"台湾","市":[{"city":"台北县","code":"101340101"},{"city":"高雄","code":"101340201"},{"city":"台中","code":"101340401"}]},{"province":"海南province","市":[{"city":"海口","code":"101310101"},{"city":"三亚","code":"101310201"},{"city":"东方","code":"101310202"},{"city":"临高","code":"101310203"},{"city":"澄迈","code":"101310204"},{"city":"儋州","code":"101310205"},{"city":"昌江","code":"101310206"},{"city":"白沙","code":"101310207"},{"city":"琼中","code":"101310208"},{"city":"定安","code":"101310209"},{"city":"屯昌","code":"101310210"},{"city":"琼海","code":"101310211"},{"city":"文昌","code":"101310212"},{"city":"保亭","code":"101310214"},{"city":"万宁","code":"101310215"},{"city":"陵水","code":"101310216"},{"city":"西沙","code":"101310217"},{"city":"南沙岛","code":"101310220"},{"city":"乐东","code":"101310221"},{"city":"五指山","code":"101310222"},{"city":"琼山","code":"101310102"}]},{"province":"湖南","市":[{"city":"长沙","code":"101250101"},{"city":"株洲","code":"101250301"},{"city":"衡阳","code":"101250401"},{"city":"郴州","code":"101250501"},{"city":"常德","code":"101250601"},{"city":"益阳","code":"101250700"},{"city":"娄底","code":"101250801"},{"city":"邵阳","code":"101250901"},{"city":"岳阳","code":"101251001"},{"city":"张家界","code":"101251101"},{"city":"怀化","code":"101251201"},{"city":"黔阳","code":"101251301"},{"city":"永州","code":"101251401"},{"city":"吉首","code":"101251501"},{"city":"湘潭","code":"101250201"}]},{"province":"江苏","市":[{"city":"南京","code":"101190101"},{"city":"镇江","code":"101190301"},{"city":"苏州","code":"101190401"},{"city":"南通","code":"101190501"},{"city":"扬州","code":"101190601"},{"city":"宿迁","code":"101191301"},{"city":"徐州","code":"101190801"},{"city":"淮安","code":"101190901"},{"city":"连云港","code":"101191001"},{"city":"常州","code":"101191101"},{"city":"泰州","code":"101191201"},{"city":"无锡","code":"101190201"},{"city":"盐城","code":"101190701"}]},{"province":"黑龙江","市":[{"city":"哈尔滨","code":"101050101"},{"city":"牡丹江","code":"101050301"},{"city":"佳木斯","code":"101050401"},{"city":"绥化","code":"101050501"},{"city":"黑河","code":"101050601"},{"city":"双鸭山","code":"101051301"},{"city":"伊春","code":"101050801"},{"city":"大庆","code":"101050901"},{"city":"七台河","code":"101051002"},{"city":"鸡西","code":"101051101"},{"city":"鹤岗","code":"101051201"},{"city":"齐齐哈尔","code":"101050201"},{"city":"大兴安岭","code":"101050701"}]},{"province":"吉林","市":[{"city":"长春","code":"101060101"},{"city":"延吉","code":"101060301"},{"city":"四平","code":"101060401"},{"city":"白山","code":"101060901"},{"city":"白城","code":"101060601"},{"city":"辽源","code":"101060701"},{"city":"松原","code":"101060801"},{"city":"吉林","code":"101060201"},{"city":"通化","code":"101060501"}]},{"province":"辽宁","市":[{"city":"沈阳","code":"101070101"},{"city":"鞍山","code":"101070301"},{"city":"抚顺","code":"101070401"},{"city":"本溪","code":"101070501"},{"city":"丹东","code":"101070601"},{"city":"葫芦岛","code":"101071401"},{"city":"营口","code":"101070801"},{"city":"阜新","code":"101070901"},{"city":"辽阳","code":"101071001"},{"city":"铁岭","code":"101071101"},{"city":"朝阳","code":"101071201"},{"city":"盘锦","code":"101071301"},{"city":"大连","code":"101070201"},{"city":"锦州","code":"101070701"}]}]}');
        $extra['require_refresh'] = false;
        $extra['data'] = $list;
        $this->apiSuccess('获取城市列表成功', null, $extra);
    }

    /**
     * 获取热门城市列表
     */
    public function getHotCityList(){
        $model = M('OrganizationConfig');
        $map['organization_id'] = 0;
        $map['status'] = 1;
        $map['type'] = 4;
        $list = $model->field('value')->where($map)->select();
        $extra['data'] = $list;
        $this->apiSuccess('获取城市列表成功', null, $extra);
    }

    /**
     * 获取机构列表
     * @param null $city
     * @param null $category
     * @param int $page
     * @param int $count
     */
    public function localOrganizationList($city=null, $type=null, $page=1, $count=10){
        $model = M('Organization');
        if(!empty($city)&&!empty($type)){
            $org_list = $model->field('id, name, slogan, city, view_count, logo')
                ->where("application_status=2 and status=1 and city like '%".$city."% and type=".$type)->page($page, $count)->select();
        }
        if(!empty($city)&&empty($type)){
            $org_list = $model->field('id, name, slogan, city, view_count, logo')
                ->where("application_status=2 and status=1 and city like '%".$city."%")->page($page, $count)->select();
        }
        if(empty($city)&&!empty($type)){
            $org_list = $model->field('id, name, slogan, city, view_count, logo')
                ->where("application_status=2 and status=1 and type=".$type)->page($page, $count)->select();
        }
        if(empty($city)&&empty($type)){
            $org_list = $model->field('id, name, slogan, city, view_count, logo')
                ->where("application_status=2 and status=1")->page($page, $count)->select();
        }
        foreach($org_list as &$org){
            $org_id = $org['id'];
            $logo_id = $org['logo'];
            $org['logo'] = $this->fetchImage($logo_id);
            $org['authenticationInfo'] = $this->getAuthenticationInfo($org_id);
            $org['followCount'] = $this->getFollowCount($org_id);
            $org['enrollCount'] = $this->getEnrollCount($org_id);
        }
        $data['list'] = $org_list;
        $this->apiSuccess('获取机构列表成功', null, $data);
    }

    /**
     * @param int $organization_id
     */
    public function followOrganization($organization_id=0){
        if($organization_id==0){
            $this->apiError(-1, '传入机构id不能为空');
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
            $this->apiError(-1, '传入机构id不能为空');
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
     * 获取机构的认证信息
     * @param $organization_id
     */
    private function getAuthenticationInfo($organization_id=0){
        $model = M('OrganizationAuthenticationConfig');
        $authModel = M('OrganizationAuthentication');
        $config_list = $model->field('id, name, pic_id')->where('status=1')->select();
        foreach($config_list as &$config){
            $config['pic_url'] = $this->fetchImage($config['pic_id']);
            $map['organization_id'] = $organization_id;
            $map['authentication_id'] = $config['id'];
            if($authModel->where($map)->find()){
                $config['status'] = true;
            } else {
                $config['status'] = false;
            }
        }
        return $config;
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
        $data['status'] = array('in','1,2');
        $count = $model->where($data)->count();
        return $count;
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