<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 2016/10/31
 * Time: 16:21
 */

namespace App\Controller;
use Common\Controller\BaseController;


class EncyclopediaController extends BaseController {

    public function __construct(){
        parent::__construct();
    }

    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    public function encyclopedia(){
        $this->display('encyclopedia');
    }

    public function getFirstLevelCategory(){
        $model = M('EncyclopediaCategory');
        $data['status'] = 1;
        $data['pid'] = 0;
        $list = $model->field('id,name')->where($data)->order('sort desc')->select();
        $this->apiSuccess("获取百科一级分类列表成功", null,
            array('data' => $list, 'total_count' => count($list)));
    }

    public function getSecondLevelCategory($id=0){
        $model = M('EncyclopediaCategory');
        $data['status'] = 1;
        $data['pid'] = $id;
        $list = $model->field('id,name')->where($data)->order('sort desc')->select();
        $this->apiSuccess("获取百科二级分类列表成功", null,
            array('data' => $list, 'total_count' => count($list)));
    }

}
