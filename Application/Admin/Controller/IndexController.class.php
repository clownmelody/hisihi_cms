<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use Think\Hook;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class IndexController extends AdminController {

    /**
     * 后台首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $param['alert_info'] = '推送钩子测试';
        $param['id'] = 66;
        $param['type'] = 1;
        $param['user_id'] = 36;
        $param['fans_id'] = 22;
        $param['production'] = false;
        #Hook::exec('Addons\\JPush\\JPushAddon', 'push_video_article', $param);
        Hook::exec('Addons\\JPush\\JPushAddon', 'push_followed', $param);
        if(UID){
            $this->meta_title = '管理首页';
            $this->display();
        } else {
            $this->redirect('Public/login');
        }
    }

}
