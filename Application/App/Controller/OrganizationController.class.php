<?php
/**
 * Created by PhpStorm.
 * Author: walterYang
 * Date: 21/9/15
 * Time: 3:30 PM
 */

namespace App\Controller;

use Think\Controller;
use Think\Exception;
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
     * 修改机构logo
     * @param int $organization_id
     * @param int $pic_id
     */
    public function updateLogo($organization_id=0, $pic_id=0){
        if(empty($organization_id)||empty($pic_id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        $model = M('Organization');
        $data['logo'] = $pic_id;
        $result = $model->where('id='.$organization_id)->save($data);
        if($result){
            $this->apiSuccess('修改机构logo成功');
        } else {
            $this->apiError(-1, '修改机构logo失败，请重试');
        }
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
        if(!empty($organization_id)){  // 新增结构基本信息
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
     * 获取机构之间的标签
     */
    public function getOrganizationAdvantageTags(){

    }

    /**
     * 获取机构公告
     * @param int $page
     * @param int $count
     */
    public function getNotice($page=1, $count=10){
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
     * @param null $type  'add' or 'delete'
     */
    public function studentWorks($organization_id=null, $pic_id=null, $type='add'){
        if(empty($organization_id)||empty($pic_id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        if('add'==$type){  // 添加学生作品

        } else {  // 删除学生作品

        }
    }

    /**
     * 机构环境图片添加或删除
     * @param null $organization_id
     * @param null $pic_id
     * @param string $type
     */
    public function organizationEnvironment($organization_id=null, $pic_id=null, $type='add'){
        if(empty($organization_id)||empty($pic_id)){
            $this->apiError(-1, '传入参数不能为空');
        }
        if('add'==$type){  // 添加环境图片

        } else {  // 删除环境图片

        }
    }

    /**
     * 获取所有老师列表
     */
    public function teachersList(){

    }

    /**
     * 新增老师分组
     * @param null $organization_id
     * @param null $group_name
     */
    public function addTeachersGroup($organization_id=null, $group_name=null){

    }

    /**
     * 修改老师分组名称
     * @param null $teacher_group_id
     * @param null $group_name
     */
    public function updateTeachersGroup($teacher_group_id=null, $group_name=null){

    }

    /**
     * 删除老师分组
     * @param null $teacher_group_id
     */
    public function deleteTeachersGroup($teacher_group_id=null){

    }

    /**
     * 添加教师到分组
     * @param null $teacher_group_id
     * @param null $uid
     */
    public function addTeacherToGroup($uid=null, $teacher_group_id=null){

    }

    /**
     * 从分组中移除老师
     * @param null $uid
     * @param null $teacher_group_id
     */
    public function deleteTeacherFromGroup($uid=null, $teacher_group_id=null){

    }

    /**
     * 获取当前机构所有分组信息
     */
    public function getAllGroups(){

    }

    /**
     * 获取所有分组和老师信息
     */
    public function getAllGroupsTeachers(){

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