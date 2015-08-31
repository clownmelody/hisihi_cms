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
use Think\Hook;

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

    /** hiworks_list.php/file/download/id/3578
     * 金榜作业
     */
    public function topDownload(){
        $model = M();
        $result = $model->query("select document.id, document.title, document.cover_id, download.download from hisihi_document_download as download,
                                  hisihi_document as document where download.id=document.id and document.cover_id!=0 and document.status=1
                                  order by download.download desc limit 0,3");
        foreach($result as &$value){
            $pic_id = $value['cover_id'];
            $value['pic'] = null;
            $pic_info = $model->query("select path from hisihi_picture where id=".$pic_id);
            if($pic_info){
                $path = $pic_info[0]['path'];
                $objKey = substr($path, 17);
                $param["bucketName"] = "hisihi-other";
                $param['objectKey'] = $objKey;
                $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
                if($isExist){
                    $picUrl = "http://hisihi-other.oss-cn-qingdao.aliyuncs.com/".$objKey;
                    $value['pic'] = $picUrl;
                }
            }
            unset($value['cover_id']);
        }
        $this->apiSuccess($result);
    }
}