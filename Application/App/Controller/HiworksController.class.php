<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 * =======================================================
 */

namespace App\Controller;

use Think\Controller;

class HiworksController extends AppController
{
    public function _initialize()
    {

    }
    /**
     * 分类信息
     */
    public function category($cate = 1)
    {
        /* 分类信息 */
        $category = $this->cate($cate);

        $children = $this->getChildrenId($category['id']);
        //分割分类
        $children = explode(',', $children);

        $categorylist = array();
        foreach ($children as &$child) {
            $child = D('Hiworks/Category')->info($child);
            $childcategory['id'] = $child['id'];
            $childcategory['name'] = $child['name'];
            $childcategory['icon'] = "http://www.hisihi.com/images/mobile_".$child['name'].".jpg";
            $childcategory['title'] = $child['title'];
            $map = array('category_id' => $child['id']);
            $childcategory['files'] = D('Document')->where($map)->count('id');
            $view = D('Document')->where($map)->field('view')->select();
            $childcategory['download'] = 0;
            $childcategory['download'] += array_sum(getSubByKey($view,'view'));
            $categorylist[] = $childcategory;
        }

        $this->apiSuccess("获取云作业列表成功", null, array('category' => $categorylist));
    }

    /* 文档分类检测 */
    private function cate($id = 1)
    {
        /* 标识正确性检测 */
        $id = $id ? $id : I('get.category', 0);
        if (empty($id)) {
            $this->error('没有指定文档分类！');
        }
        /* 获取分类信息 */
        $category = D('Hiworks/Category')->info($id);
        if ($category && 1 == $category['status']) {
            switch ($category['display']) {
                case 0:
                    $this->error('该分类禁止显示！');
                    break;
                //TODO: 更多分类显示状态判断
                default:
                    return $category;
            }
        } else {
            $this->error('分类不存在或被禁用！');
        }
    }

    /**
     * 获取指定分类子分类ID
     * @param  string $cate 分类ID
     * @return string       id列表
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    private function getChildrenId($cate){
        $field = 'id,name,pid,title,link_id';
        $category = D('Hiworks/Category')->getTree($cate, $field);
        $ids = array();
        foreach ($category['_'] as $key => $value) {
            $ids[] = $value['id'];
        }
        return implode(',', $ids);
    }
}