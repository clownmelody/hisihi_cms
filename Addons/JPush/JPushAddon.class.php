<?php

namespace Addons\JPush;
use Common\Controller\Addon;

/**
 * 极光推送插件
 * @author RFly
 */

    class JPushAddon extends Addon{

        public $info = array(
            'name'=>'JPush',
            'title'=>'极光推送',
            'description'=>'调用极光推送SDK，完成向Android、iOS客户端指定用户推送消息的功能',
            'status'=>1,
            'author'=>'RFly',
            'version'=>'0.1'
        );

        public function install(){
            return true;
        }

        public function uninstall(){
            return true;
        }


    }