<?php
namespace App\Service;
use Think\Model;

/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/31
 * Time: 12:24
 */

class OrganizationService extends Model
{
    public function getBaseInfoById($org_id){
        $model=M("Organization");
        $info = $model->where(array('id'=>$org_id,'status'=>1))
            ->field('name,logo,introduce,advantage,light_authentication, is_listen_preview, listen_preview_text')
            ->find();
        $info['authenticationInfo'] = A('App/Organization')->getAuthenticationInfo_v2_9_5($org_id);
        return $info;
    }

}