<?php

namespace Addons\QiNiu;
use Common\Controller\Addon;

/**
 * 七牛云存储插件
 * @author walterYang
 */

    class QiNiuAddon extends Addon{

        public $info = array(
            'name'=>'QiNiu',
            'title'=>'七牛云存储',
            'description'=>'七牛存储插件',
            'status'=>1,
            'author'=>'walterYang',
            'version'=>'0.1'
        );

        public function install(){
            return true;
        }

        public function uninstall(){
            return true;
        }


    }