<?php

namespace Addons\Aliyun_Oss\Controller;
use Home\Controller\AddonsController;
require_once './Addons/Aliyun_Oss/OSS/aliyun.php';
use \Aliyun\OSS\OSSClient;
use PHPImageWorkshop\ImageWorkshop;
use Think\Exception;
use Think\Think;

class AliyunOssController extends AddonsController{
    protected $oss_client;
    protected $config;
    protected $avator_bucket;
    public function _initialize() {
        $this->config = get_addon_config('Aliyun_Oss');
        $accessKeyId = $this->config['AccessKeyId'];
        $accessKeySecret = $this->config['AccessKeySecret'];
        $this->avator_bucket = $this->config['AvatarBucket'];

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
                $result = $this->oss_client->putObject(array(
                    'Bucket' => 'hisihi-avator',
                    'Key' => $objectKey,
                    'Content' => fopen("./Uploads/Avatar/$objectKey", 'r'),
                    'ContentLength' => $content_length,
                ));
            } catch (Exception $ex) {
                \Think\Log::write("AliYun OSS Service Upload Resource Exception: ".$ex.getMessage(), "ERR");
            }
        }
    }

    public function uploadForumPicResource($objectKey){
        if(isset($objectKey)){
            try {
                $content_length = filesize("./Uploads/Picture/$objectKey");
                $param['a'] = $content_length;
                $param['b'] = "./Uploads/Picture/$objectKey";
                $result = $this->oss_client->putObject(array(
                    'Bucket' => 'forum-pic',
                    'Key' => $objectKey,
                    'Content' => fopen("./Uploads/Picture/$objectKey", 'r'),
                    'ContentLength' => $content_length,
                ));
            } catch (Exception $ex) {
                \Think\Log::write("AliYun OSS Service Upload Resource Exception: ".$ex.getMessage(), "ERR");
            }
        }
    }

    public function deleteAvatorResource($bucketName, $objectKey){
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

    public function getPublicResourceUrl($bucketName, $objectKey){
        try {
            $object = $this->oss_client->getObject(array(
                'Bucket' => $bucketName,
                'Key' => $objectKey,
            ));
        } catch (Exception $ex) {
            \Think\Log::write("AliYun OSS Service Get Resource Url Exception: ".$ex.getMessage(), "ERR");
        }
        if($object){
            return "http://$bucketName.oss-cn-qingdao.aliyuncs.com/$objectKey";
        } else {
            return null;
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
