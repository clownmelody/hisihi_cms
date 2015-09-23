<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Controller;
use Addons\Avatar\AvatarAddon;

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


    private function fetchImage($pic_id)
    {
        if($pic_id == null)
            return null;
        $pic_small = getThumbImageById($pic_id, 280, 160);
        $pathArray = explode("_",$pic_small);
        $pic_small = $pathArray[0].'.jpg';
        return $pic_small;
    }

}