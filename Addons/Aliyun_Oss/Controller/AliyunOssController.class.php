<?php

namespace Addons\Aliyun_Oss\Controller;
use Home\Controller\AddonsController;

require_once '/aliyun-php-sdkv2/aliyun.php';
use \Aliyun\OSS\OSSClient;

class AliyunOssController extends AddonsController{
    protected $oss_client;
    protected $config;
    public function _initialize() {
        $this->config=  get_addon_config('Aliyun_Oss');
        $accessKeyId = $this->config['AccessKeyId'];
        $accessKeySecret = $this->config['AccessKeySecret'];

        $this->oss_client = OSSClient::factory(array(
            'AccessKeyId' => $accessKeyId,
            'AccessKeySecret' => $accessKeySecret,
        ));
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
