<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-10
 * Time: PM7:40
 */
use Addons\Aliyun_Oss\Controller\AliyunOssController;
use Think\Hook;

function getImageUrlByPath($path, $size)
{
    //TODO 重新开启缩略
    $thumb = getThumbImage($path, $size, $size);
    // $thumb['src']=$path;
    $thumb = $thumb['src'];
    if (!is_sae()) {
        //$thumb = getRootUrl() . $thumb;
    }
    return $thumb;
}

/**兼容SAE
 * @param        $filename
 * @param int $width
 * @param string $height
 * @param int $type
 * @param bool $replace
 * @return mixed|string
 * @auth 陈一枭
 */
function getThumbImage($filename, $width = 100, $height = 'auto', $type = 0, $replace = false)
{
    $UPLOAD_URL = '';
    $UPLOAD_PATH = '';
    $filename = str_ireplace($UPLOAD_URL, '', $filename); //将URL转化为本地地址
    $info = pathinfo($filename);
    $oldFile = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . $info['extension'];
    $thumbFile = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '_' . $width . '_' . $height . '.' . $info['extension'];

    $oldFile = str_replace('\\', '/', $oldFile);
    $thumbFile = str_replace('\\', '/', $thumbFile);


    $filename = ltrim($filename, '/');
    $oldFile = ltrim($oldFile, '/');
    $thumbFile = ltrim($thumbFile, '/');
    $oldFile = rtrim($oldFile, '.');
    $thumbFile = ltrim($thumbFile, '.');

    //兼容SAE的中心裁剪缩略
    if (strtolower(C('PICTURE_UPLOAD_DRIVER')) == 'sae') {
        $storage = new SaeStorage();
        $thumbFilePath = str_replace(C('UPLOAD_SAE_CONFIG.rootPath'), '', $thumbFile);
        if(!$storage->fileExists(C('UPLOAD_SAE_CONFIG.domain'),$thumbFilePath)){
            $f = new SaeFetchurl();
            $img_data = $f->fetch($oldFile);
            $img = new SaeImage();
            $img->setData($img_data);
            $info_img = $img->getImageAttr();
            if ($height == "auto") $height = $info_img[1] * $height / $info_img[0];

            $w = $info_img[0];
            $h = $info_img[1];

            /* 居中裁剪 */
            //计算缩放比例
            $w_scale = $width / $w;
            if ($w_scale > 1) {
                $w_scale = 1 / $w_scale;
            }
            $h_scale = $height / $h;

            if ($h_scale > $w_scale) {
                //按照高来放缩
                $x1 = (1 - 1.0 * $width * $h / $w / $height) / 2;
                $x2 = (1 - $x1);
                $img->crop($x1, $x2, 0, 1);
                $img_temp = $img->exec();
                $img1 = new SaeImage();
                $img1->setData($img_temp);
                $img1->resizeRatio($h_scale);
            } else {
                $y1 = (1 - 1 * 1.0 / ($width * $h / $w / $height)) / 2;
                $y2 = (1 - $y1);
                $img->crop(0, 1, $y1, $y2);
                $img_temp = $img->exec();
                $img1 = new SaeImage();
                $img1->setData($img_temp);
                $img1->resizeRatio($w_scale);
            }

            $img1->improve();
            $new_data = $img1->exec(); // 执行处理并返回处理后的二进制数据
            if ($new_data === false)
                return $oldFile;
            // 或者可以直接输出
            $thumbed = $storage->write(C('UPLOAD_SAE_CONFIG.domain'), $thumbFilePath, $new_data);
            $info['width'] = $width;
            $info['height'] = $height;
            $info['src'] = $thumbed;
            //图片处理失败时输出错误码和错误信息
        }else{
            $info['width'] = $width;
            $info['height'] = $height;
            $info['src'] =$storage->getUrl(C('UPLOAD_SAE_CONFIG.domain'),$thumbFilePath);
        }
        return $info;
    }

    if(strpos($filename, "Avatar")) {
        $file_key = substr($filename, 15);
        $thumb_file_key = substr($thumbFile, 15);
        $oss = new AliyunOssController();
        $isOriginAvatarExist = $oss->isResourceExistInOSS("hisihi-avator", $file_key);
        $isThumbAvatarExist = $oss->isResourceExistInOSS("hisihi-avator", $thumb_file_key);
        if(!$isOriginAvatarExist){
            $info['src'] = "http://".C('OSS_AVATAR').C('OSS_ENDPOINT').$file_key;
            $info['width'] = intval($width);
            $info['height'] = intval($height);
            return $info;
        } else if($isThumbAvatarExist && !$replace){
            $thumb_file_url = "http://".C('OSS_AVATAR').C('OSS_ENDPOINT').$thumb_file_key;
            $oss_img_src = "http://".C('OSS_AVATAR').C('IMG_OSS_ENDPOINT').$thumb_file_key.'@info';
            $origin_img_info = getOssImgSizeInfo($oss_img_src);
            $img_info = json_decode($origin_img_info);
            $info['src'] = $thumb_file_url;
            $info['width'] = $img_info->width;
            $info['height'] = $img_info->height;
            return $info;
        }
    }
    if(strpos($filename, "Picture")) {
        $file_key = substr($filename, 16);
        $thumb_file_key = substr($thumbFile, 16);
        $oss = new AliyunOssController();
        $isOriginAvatarExist = $oss->isResourceExistInOSS("forum-pic", $file_key);
        $isThumbAvatarExist = $oss->isResourceExistInOSS("forum-pic", $thumb_file_key);
        if(!$isOriginAvatarExist){
            $info['src'] = "http://".C('OSS_FORUM_PIC').C('OSS_ENDPOINT').$file_key;
            $info['width'] = intval($width);
            $info['height'] = intval($height);
            return $info;
        } else if($isThumbAvatarExist && !$replace){
            $thumb_file_url = "http://".C('OSS_FORUM_PIC').C('OSS_ENDPOINT').$thumb_file_key;
            $oss_img_src = "http://".C('OSS_FORUM_PIC').C('IMG_OSS_ENDPOINT').$thumb_file_key.'@info';
            $origin_img_info = getOssImgSizeInfo($oss_img_src);
            $img_info = json_decode($origin_img_info);
            //$imageinfo = getimagesize($thumb_file_url);
            $info['src'] = $thumb_file_url;
            $info['width'] = intval($img_info->width);
            $info['height'] = intval($img_info->height);
            return $info;
        }
    }

    //原图不存在直接返回
    if (!file_exists($UPLOAD_PATH . $oldFile)) {
        @unlink($UPLOAD_PATH . $thumbFile);
        $info['src'] = $oldFile;
        $info['width'] = intval($width);
        $info['height'] = intval($height);
        return $info;
        //缩图已存在并且  replace替换为false
    } elseif (file_exists($UPLOAD_PATH . $thumbFile) && !$replace) {
        $imageinfo = getimagesize($UPLOAD_PATH . $thumbFile);
        $info['src'] = $thumbFile;
        $info['width'] = intval($imageinfo[0]);
        $info['height'] = intval($imageinfo[1]);
        return $info;
        //执行缩图操作
    } else {
        $oldimageinfo = getimagesize($UPLOAD_PATH . $oldFile);
        $old_image_width = intval($oldimageinfo[0]);
        $old_image_height = intval($oldimageinfo[1]);
        if ($old_image_width <= $width && $old_image_height <= $height) {
            @unlink($UPLOAD_PATH . $thumbFile);
            @copy($UPLOAD_PATH . $oldFile, $UPLOAD_PATH . $thumbFile);
            $info['src'] = $thumbFile;
            $info['width'] = intval($old_image_width);
            $info['height'] = intval($old_image_height);
            if(strpos($thumbFile, "Avatar")){
                $new_pic_file = substr($thumbFile,15);
                $param['objectKey'] = $new_pic_file;
                Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadAvatorResource', $param);
            } else {
                $new_pic_file = substr($thumbFile, 16);
                $param['objectKey'] = $new_pic_file;
                Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadForumPicResource', $param);
            }
            if(strpos($info['src'], "Avatar")) {
                $src = substr($info['src'], 15);
                $param["bucketName"] = "hisihi-avator";
                $param['objectKey'] = $src;
                $info['src'] = "http://".C('OSS_AVATAR').C('OSS_ENDPOINT').$src;
            }
            return $info;
        } else {
            if($height != "auto" && $width != "auto") {
                ////////////////////////factor 1/////////////////////////////
                //$factorN = $height / $width;
                //$factorO = $old_image_height / $old_image_width;
                //if( $factorN/$factorO > 2 || $factorO/$factorN > 2){
                //   $height = $height;
                //}else {
                //    if($factorN > $factorO)
                //        $height = "auto";
                //    if($factorN < $factorO)
                //        $width = "auto";
                //}
                ////////////////////////factor 1 end/////////////////////////
                ////////////////////////////factor 2/////////////////////////
                //$factor =  ($height * $width) / ($old_image_height * $old_image_width);
                //if($factor<1) {
                //    $height = $factor * $old_image_height;
                //    $width = $factor * $old_image_width;
                //}
                ////////////////////////////factor 2 end/////////////////////
                ////////////////////////////factor 3/////////////////////////
                if($width == 280 && $height == 160) {
                    //1.缩放到（280，160）的缩放因子
                    $factorw = 280 / $old_image_width;
                    $factorh = 160 / $old_image_height;
                    $factor1 = min($factorw, $factorh);

                    //2.缩放到（160，160）的缩放因子
                    $factorw = 160 / $old_image_width;
                    $factorh = 160 / $old_image_height;
                    $factor2 = max($factorw, $factorh);

                    //取二者最大值
                    $factor = max($factor1, $factor2);
                    //小于1需要缩小
                    if($factor < 1){
                        //进行缩放原尺寸长宽乘以缩放因子
                        $width  = $old_image_width * $factor;
                        $height = $old_image_height * $factor;
                    }
                }
                ////////////////////////////factor 3 end/////////////////////
            }
            if ($height == "auto") $height = $old_image_height * $width / $old_image_width;
            if ($width == "auto") $width = $old_image_width * $height / $old_image_height;
            if (intval($height) == 0 || intval($width) == 0) {
                return 0;
            }

            require_once('ThinkPHP/Library/Vendor/phpthumb/PhpThumbFactory.class.php');
            $thumb = PhpThumbFactory::create($UPLOAD_PATH . $filename);
            if ($type == 0) {
                $thumb->adaptiveResize($width, $height);
            } else {
                $thumb->resize($width, $height);
            }
            $res = $thumb->save($UPLOAD_PATH . $thumbFile);

            if(strpos($thumbFile, "Avatar")){
                $new_pic_file = substr($thumbFile, 15);
                $param['objectKey'] = $new_pic_file;
                Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadAvatorResource', $param);
            } else {
                $new_pic_file = substr($thumbFile, 16);
                $param['objectKey'] = $new_pic_file;
                Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadForumPicResource', $param);
            }
            if(strpos($info['src'], "Avatar")) {
                $src = substr($info['src'], 15);
                $param["bucketName"] = "hisihi-avator";
                $param['objectKey'] = $src;
                $info['src'] = "http://".C('OSS_AVATAR').C('OSS_ENDPOINT').$src;
            }

            $info['src'] = $UPLOAD_PATH . $thumbFile;
            $info['width'] = intval($width);
            $info['height'] = intval($height);
            return $info;

            //内置库缩略
            /*  $image = new \Think\Image();
              $image->open($UPLOAD_PATH . $filename);
              //dump($image);exit;
              $image->thumb($width, $height, $type);
              $image->save($UPLOAD_PATH . $thumbFile);
              //缩图失败
              if (!$image) {
                  $thumbFile = $oldFile;
              }
              $info['width'] = $width;
              $info['height'] = $height;
              $info['src'] = $thumbFile;
              return $info;*/


        }
    }
}

function getRootUrl()
{
    if (__ROOT__ != '') {
        return __ROOT__ . '/';
    }
    if (C('URL_MODEL') == 1 || C('URL_MODEL') == 2)
        return __ROOT__ . '/';
    return __ROOT__;
}


function getThumbImageById($cover_id, $width = 100, $height = 'auto', $type = 0, $replace = false)
{

    $picture = M('Picture')->where(array('status' => 1))->getById($cover_id);
    if (empty($picture)) {
        return getRootUrl() . 'Public/Core/images/nopic.png';
    }
    switch ($picture['type']) {
        case 'qiniu':
            $height=$height=='auto'?100:$height;
            if(stripos($picture['path'],'imageMogr2') !== false){
                $picture['path'] = $picture['path'] . '/thumbnail/' . $width . 'x' . $height;
            }else{
                $picture['path'] = $picture['path'] . '?imageView/1/w/' . $width . '/h/' . $height;
            }
            return $picture['path'];
            break;
        case 'local':
            $attach = getThumbImage($picture['path'], $width, $height, $type, $replace);
            //$attach['src'] = getRootUrl() . $attach['src'];
            return $attach['src'];
        case 'sae':
            $attach = getThumbImage($picture['path'], $width, $height, $type, $replace);
            return $attach['src'];
        default:
            return $picture['path'];
    }

}

/**对于附件来修正其url，兼容urlmodel2,sae
 * @param $url
 * @return string
 * @auth 陈一枭
 */
function fixAttachUrl($url)
{
    if (is_local()) {
        return str_replace('//', '/', getRootUrl() . $url); //防止双斜杠的出现
    } else {
        return $url;
    }

}