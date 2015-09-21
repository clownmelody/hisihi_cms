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

}