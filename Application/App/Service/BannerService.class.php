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
            ->select();
        return $list;
    }
}