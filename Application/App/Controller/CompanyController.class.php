<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Controller;
use Addons\Avatar\AvatarAddon;
use Think\Hook;

/**
 * 公司相关接口
 * Class CompanyController
 * @package App\Controller
 */
class CompanyController extends AppController {

    /**
     * 公司列表
     */
    public function alllist(){
        $model = D('Admin/Company');
        $result = $model->where('status<>-1')->select();
        if($result){
            foreach($result as &$company){
                $company['picture'] = $this->fetchImage($company['picture']);
            }
        }
        $extra['totalCount'] = count($result);
        $extra['data'] = $result;
        $this->apiSuccess('获取公司列表成功', null, $extra);
    }

    /**
     * 获取公司详细信息
     * @param $id
     */
    public function info($id){
        if(empty($id)){
            $this->apiError(-1, "传入参数不能为空");
        }
        $model = D('Admin/Company');
        $result = $model->where('status<>-1 and id='.$id)->find();
        if($result){
            $result['picture'] = $this->fetchImage($result['picture']);
        }
        $extra['data'] = $result;
        $this->apiSuccess('获取公司信息成功', null, $extra);
    }

    /**
     * 获取公司招聘信息
     * @param $id
     */
    public function recruits($id){
        if(empty($id)){
            $this->apiError(-1, "传入参数不能为空");
        }
        $model = D('CompanyRecruit');
        $result = $model->field('company_id, job, salary, requirement, skills, end_time')->where('status<>-1 and company_id='.$id)->select();
        $extra['totalCount'] = count($result);
        $extra['data'] = $result;
        $this->apiSuccess('获取公司信息成功', null, $extra);
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