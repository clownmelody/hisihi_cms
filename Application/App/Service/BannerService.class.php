<?php
namespace App\Service;
use Think\Model;

/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/31
 * Time: 12:24
 */

class BannerService extends Model
{
    public function getIndexBanner(){
        $where_array['status'] = 1;
        $where_array['show_pos'] = -5;  // App 首頁banner
        $list = D('App/InformationFlowBanner', 'Model')
            ->field('pic_url, url, jump_type, organization_id, sort')
            ->where($where_array)
            ->order('sort desc')
            ->select();
        foreach($list as &$banner){
            $organization_id = $banner['organization_id'];
            unset($banner['organization_id']);
            switch($banner['jump_type']){
                case 1: // 网址
                    break;
                case 2: // 帖子
                    $post_id = $banner['url'];
                    $banner['url'] = 'hisihi://post/detailinfo?id='.$post_id;
                    break;
                case 3: // 视频
                    $course_id = $banner['url'];
                    $banner['url'] = 'hisihi://course/detailinfo?id='.$course_id;
                    break;
                case 4: // 机构主页
                    $org_id = $banner['url'];
                    $banner['url'] = 'hisihi://organization/detailinfo?id='.$org_id;
                    break;
                case 5: // 大学主页
                    $u_id = $banner['url'];
                    $banner['url'] = 'hisihi://university/detailinfo?id='.$u_id;
                    break;
                case 6: // 活动详情页
                    $promotion_id = $banner['url'];
                    $banner['url'] = 'hisihi://promotion/detailinfo?id='.$promotion_id.'&oid='.$organization_id;
                    break;
                case 7: // 招聘主页
                    $banner['url'] = 'hisihi://recruitment';
                    break;
            }
        }
        return $list;
    }

    public function getIndexAdvBanner(){
        $where_array['status'] = 1;
        $where_array['show_pos'] = -6;  // App 首頁广告
        $list = D('App/InformationFlowBanner', 'Model')
            ->field('pic_url, url, jump_type, organization_id, sort')
            ->where($where_array)
            ->order('create_time desc')
            ->limit(1)
            ->select();
        foreach($list as &$banner){
            $organization_id = $banner['organization_id'];
            unset($banner['organization_id']);
            switch($banner['jump_type']){
                case 1: // 网址
                    break;
                case 2: // 帖子
                    $post_id = $banner['url'];
                    $banner['url'] = 'hisihi://post/detailinfo?id='.$post_id;
                    break;
                case 3: // 视频
                    $course_id = $banner['url'];
                    $banner['url'] = 'hisihi://course/detailinfo?id='.$course_id;
                    break;
                case 4: // 机构主页
                    $org_id = $banner['url'];
                    $banner['url'] = 'hisihi://organization/detailinfo?id='.$org_id;
                    break;
                case 5: // 大学主页
                    $u_id = $banner['url'];
                    $banner['url'] = 'hisihi://university/detailinfo?id='.$u_id;
                    break;
                case 6: // 活动详情页
                    $promotion_id = $banner['url'];
                    $banner['url'] = 'hisihi://promotion/detailinfo?id='.$promotion_id.'&oid='.$organization_id;
                    break;
                case 7: // 招聘主页
                    $banner['url'] = 'hisihi://recruitment';
                    break;
            }
        }
        return $list;
    }
}