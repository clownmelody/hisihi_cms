<?php

namespace App\Controller;
use Addons\Avatar\AvatarAddon;
use Addons\Email\EmailUtils;
use Addons\Pdf\PdfUtils;
use Think\Hook;

/**
 * 找灵感相关接口
 * Class InspirationController
 * @package App\Controller
 */
class InspirationController extends AppController {

    /**
     * 获取作品分类标签
     */
    public function inspirationCategoryList(){
        $cmodel = D('Admin/InspirationConfig');
        $jobList = $cmodel->field('id, value')->where('type=1 and status=1')->select();
        $extra['data'] = $jobList;
        $this->apiSuccess('获取找灵感分类列表成功', null, $extra);
    }

    /**
     * 获取筛选条件列表
     */
    public function fileterTagList(){
        $data_1['id'] = 1;
        $data_1['value'] = "特别推荐";
        $data_2['id'] = 2;
        $data_2['value'] = "精选作品";
        $tags = array($data_1, $data_2);
        $extra['data'] = $tags;
        $this->apiSuccess('获取筛选条件列表成功', null, $extra);
    }


    private function fetchImage($pic_id)
    {
        if($pic_id == null)
            return null;
        $model = M();
        $pic_info = $model->query("select path from hisihi_picture where id=".$pic_id);
        if($pic_info){
            $path = $pic_info[0]['path'];
            $objKey = substr($path, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $objKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if($isExist){
                $picUrl = "http://hisihi-other.oss-cn-qingdao.aliyuncs.com/".$objKey;
            }
        }
        return $picUrl;
    }


}