<?php

namespace Addons\Aliyun_Oss\Controller;
use Home\Controller\AddonsController;
require_once './Addons/Aliyun_Oss/OSS/aliyun.php';
use \Aliyun\OSS\OSSClient;
use Think\Exception;
use Think\Think;

class AliyunOssController extends AddonsController{
    protected $oss_client;
    protected $config;
    public function _initialize() {
        $this->config = get_addon_config('Aliyun_Oss');
        $accessKeyId = $this->config['AccessKeyId'];
        $accessKeySecret = $this->config['AccessKeySecret'];

        $this->oss_client = OSSClient::factory(array(
            'AccessKeyId' => $accessKeyId,
            'AccessKeySecret' => $accessKeySecret,
            'Endpoint' => 'http://oss-cn-qingdao.aliyuncs.com'
        ));
    }

    public function uploadAvatorResource($objectKey){
        if(isset($objectKey)){
            try {
                $content_length = filesize("./Uploads/Avatar/$objectKey");
                $this->oss_client->putObject(array(
                    'Bucket' => 'hisihi-avator',
                    'Key' => $objectKey,
                    'Content' => fopen("./Uploads/Avatar/$objectKey", 'r'),
                    'ContentLength' => $content_length,
                ));
            } catch (\Aliyun\OSS\Exceptions\OSSException $ex) {
                \Think\Log::write("AliYun OSS Service Upload Resource Exception: ".$ex.getErrorCode(), "ERR");
            }
        }
    }

    public function uploadForumPicResource($objectKey){
        if(isset($objectKey)){
            try {
                $content_length = filesize("./Uploads/Picture/$objectKey");
                $this->oss_client->putObject(array(
                    'Bucket' => 'forum-pic',
                    'Key' => $objectKey,
                    'Content' => fopen("./Uploads/Picture/$objectKey", 'r'),
                    'ContentLength' => $content_length,
                ));
            } catch (\Aliyun\OSS\Exceptions\OSSException $ex) {
                \Think\Log::write("AliYun OSS Service Upload Resource Exception: ".$ex.getErrorCode(), "ERR");
            }
        }
    }

    public function uploadForumSoundResource($objectKey){
        if(isset($objectKey)){
            try {
                $content_length = filesize("./Uploads/Download/$objectKey");
                $this->oss_client->putObject(array(
                    'Bucket' => 'forum-sound',
                    'Key' => $objectKey,
                    'Content' => fopen("./Uploads/Download/$objectKey", 'r'),
                    'ContentLength' => $content_length,
                ));
            } catch (\Aliyun\OSS\Exceptions\OSSException $ex) {
                \Think\Log::write("AliYun OSS Service Upload Resource Exception: ".$ex.getErrorCode(), "ERR");
            }
        }
    }

    public function isResourceExistInOSS($bucketName, $objectKey){
        $result = true;
        try {
             $this->oss_client->getObject(array(
                'Bucket' => $bucketName,
                'Key' => $objectKey,
            ));
        } catch (\Aliyun\OSS\Exceptions\OSSException $ex) {
            \Think\Log::write("AliYun OSS Service Exception: ".$ex->getErrorCode().'-'.$bucketName.'-'.$objectKey, "ERR");
            return false;
        }
        return $result;
    }

    public function deleteResource($bucketName, $objectKey){
        if(isset($bucketName)&&isset($objectKey)){
            try {
                $this->oss_client->deleteObject(array(
                    'Bucket' => $bucketName,
                    'Key' => $objectKey,
                ));
            } catch (Exception $ex) {
                \Think\Log::write("AliYun OSS Service Delete Resource Exception: ".$ex.getMessage(), "ERR");
            }
        }
    }

    public function generatePresignedUrl($key, $time = 60){
        $bucket = $this->config['Bucket'];
        $url = $this->oss_client->generatePresignedUrl(array(
            'Bucket' => $bucket,
            'Key' => '2015617122115ooypc/',
            'Expires' => new \DateTime("+60 minutes"),
        ));
        return $url;
    }
}
