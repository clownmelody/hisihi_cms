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
    public function _initialize()
    {
        C('SHOW_PAGE_TRACE', false);
    }

    /**
     * 获取机构信息
     * @param int $organization_id
     */
    public function info($organization_id=0){
        if(empty($organization_id)){
            $this->apiError(-1, '传入机构ID不允许为空');
        }
        $this->apiSuccess('ok');
    }

    /**
     * 获取机构的教师信息
     * @param int $organization_id
     */
    public function teachers_info($organization_id=0){
        if(empty($organization_id)){
            $this->apiError(-1, '传入机构ID不允许为空');
        }
        $this->apiSuccess('ok');
    }

    /**
     * 获取机构学生的作品
     * @param int $organization_id
     */
    public function students_works($organization_id=0){
        if(empty($organization_id)){
            $this->apiError(-1, '传入机构ID不允许为空');
        }
        $this->apiSuccess('ok');
    }

    /**
     * 获取机构的课程视频
     * @param int $organization_id
     */
    public function courses($organization_id=0){
        if(empty($organization_id)){
            $this->apiError(-1, '传入机构ID不允许为空');
        }
        $this->apiSuccess('ok');
    }

    /**
     * 获取机构的学生信息
     * @param int $organization_id
     */
    public function students_info($organization_id=0){
        if(empty($organization_id)){
            $this->apiError(-1, '传入机构ID不允许为空');
        }
        $this->apiSuccess('ok');
    }

    /**
     * 获取机构的环境图片信息
     * @param int $organization_id
     */
    public function environment($organization_id=0){
        if(empty($organization_id)){
            $this->apiError(-1, '传入机构ID不允许为空');
        }
        $this->apiSuccess('ok');
    }

    /**
     * 用户对机构加关注或取消关注
     * @param int $organization_id
     * @param int $uid
     */
    public function user_follow($organization_id=0, $uid=0, $follow=true){
        if(empty($organization_id)||empty($uid)){
            $this->apiError(-1, '传入机构ID和UID不允许为空');
        }
        $this->apiSuccess('ok');
    }

    /**
     * 用户评论
     * @param int $organization_id
     * @param int $uid
     */
    public function comment($organization_id=0, $uid=0){
        if(empty($organization_id)||empty($uid)){
            $this->apiError(-1, '传入机构ID和UID不允许为空');
        }
        $this->apiSuccess('ok');
    }

    /**
     * 获取短信验证码
     * @param $mobile
     */
    public function getSMS($mobile){
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
     * @param $sms
     * @param $password
     * @param $org_name
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
            M('OrganizationAdmin')->add();
            $this->apiSuccess('注册成功');
        } else {
            $this->apiError(-1, '验证码校验失败');
        }
    }

    /**检查手机号是否已经被注册
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