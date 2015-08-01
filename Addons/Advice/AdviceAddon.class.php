<?php

namespace Addons\Advice;

use Common\Controller\Addon;

/**
 * 点击提建议，点击反馈BUG插件
 * @author onep2p
 */
class AdviceAddon extends Addon
{

    public $info = array(
        'name' => 'Advice',
        'title' => '建议、BUG反馈插件',
        'description' => '点击提建议，点击反馈BUG插件',
        'status' => 1,
        'author' => 'onep2p',
        'version' => '0.1'
    );

    public function install(){return true;}

    public function uninstall(){return true;}
    
    //实现的Rank钩子方法
    public function Rank($param){
        $this->display('Advice');
    }
}