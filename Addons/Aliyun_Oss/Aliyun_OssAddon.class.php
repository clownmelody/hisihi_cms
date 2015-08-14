<?php

namespace Addons\Aliyun_Oss;
use Addons\Aliyun_Oss\Controller\AliyunOssController;
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

        /**上传头像数据到OSS
         * @param $param
         */
        public function uploadAvatorResource($param){
            $oss_controller = new AliyunOssController();
            $oss_controller->uploadAvatorResource($param["objectKey"]);
        }

        /**上传论坛图片到OSS
         * @param $param
         */
        public function uploadForumPicResource($param){
            $oss_controller = new AliyunOssController();
            $oss_controller->uploadForumPicResource($param["objectKey"]);
        }

        /**上传论坛声音到OSS
         * @param $param
         */
        public function uploadForumSoundResource($param){
            $oss_controller = new AliyunOssController();
            $oss_controller->uploadForumSoundResource($param["objectKey"]);
        }

        /**上传广告图片到OSS
         * @param $param
         */
        public function uploadAdvsPicResource($param){
            $oss_controller = new AliyunOssController();
            $oss_controller->uploadAdvsPicResource($param["objectKey"]);
        }

        public function uploadOtherResource($param){
            $oss_controller = new AliyunOssController();
            $oss_controller->uploadOtherResource($param["objectKey"]);
        }

        public function isResourceExistInOSS($param){
            $oss_controller = new AliyunOssController();
            return $oss_controller->isResourceExistInOSS($param["bucketName"], $param["objectKey"]);
        }

    }