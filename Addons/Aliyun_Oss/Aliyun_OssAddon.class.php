<?php

namespace Addons\Aliyun_Oss;
use Common\Controller\Addon;
/**
 * Aliyun_Oss插件
 * @author RFly
 */

    class Aliyun_OssAddon extends Addon{

        public $info = array(
            'name'=>'Aliyun_Oss',
            'title'=>'阿里云OSS',
            'description'=>'阿里云OSS存储插件',
            'status'=>1,
            'author'=>'RFly',
            'version'=>'1.0'
        );

        public function install(){
            return true;
        }

        public function uninstall(){
            return true;
        }


    }