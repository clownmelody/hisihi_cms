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
        if($children==null){
            $this->apiSuccess("该类别下没有子分类", null, null);
        }

        //分割分类
        $children = explode(',', $children);

        $categorylist = array();
        foreach ($children as &$child) {
            $child = D('Hiworks/Category')->info($child);
            $childcategory['id'] = $child['id'];
            $childcategory['name'] = $child['name'];
            //$childcategory['icon'] = "http://www.hisihi.com/images/mobile_".$child['name'].".jpg";
            $childcategory['icon'] = $this->getIconUrl($child['icon']);
            $childcategory['title'] = $child['title'];
            $map = array('category_id' => $child['id']);
            //$childcategory['files'] = D('Document')->where($map)->count('id');
            $t_res = M('Category')->field('fake_hiworks_count')->where('id='.$child['id'])->find();
            if($t_res){
                $childcategory['files'] = $t_res['fake_hiworks_count'];
            }
            $view = D('Document')->where($map)->field('view')->select();
            $childcategory['download'] = 0;
            $childcategory['download'] += array_sum(getSubByKey($view,'view'));
            /* -- */
            $model = D('Hiworks/Category');
            $condition['status'] = 1;
            $condition['allow_publish'] = 1;
            $condition['pid'] = $child['id'];
            $cateResult = $model->where($condition)->field('id')->select();
            foreach($cateResult as &$value){
                $submap = array('category_id' => $value['id']);
                /*$subCount = D('Document')->where($submap)->count('id');
                $childcategory['files'] = $childcategory['files'] + $subCount;*/
                $subview = D('Document')->where($submap)->field('view')->select();
                $childcategory['download'] += array_sum(getSubByKey($subview,'view'));
            }
            /* -- */
            $categorylist[] = $childcategory;
        }
        $allCateTotalDownloadCount = 0;
        foreach($categorylist as $category){
            $allCateTotalDownloadCount += $category['download'];
            S('fake_all_category_hiworks_download', $allCateTotalDownloadCount);
        }
        $this->apiSuccess("获取云作业列表成功", null, array('category' => $categorylist));
    }


    /* 文档分类检测 */
    private function cate($id = 1)
    {
        /* 标识正确性检测 */
        $id = $id ? $id : I('get.category', 0);
        if (empty($id)) {
            $this->apiError(-1, '没有指定文档分类！');
            //$this->error('没有指定文档分类！');
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
            $this->apiError(-1, '分类不存在或被禁用！');
            //$this->error('分类不存在或被禁用！');
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
        if(count($ids)==0){
            return null;
        }
        return implode(',', $ids);
    }

    /**
     * 金榜作业
     * @param null $version
     */
    public function topDownload($version=null){
        $model = M();
        if((float)$version>=2.2){
            $extra['allCount'] = S('fake_all_category_hiworks_download');
            if(!$extra['allCount']){
                $allCount = $model->query('select sum(view) as allCount from hisihi_document');
                $extra['allCount'] = $allCount[0]['allCount'];
            }
        }
        $result = $model->query("select document.id, document.title, document.category_id, document.cover_id, download.download from hisihi_document_download as download,
                                  hisihi_document as document where download.id=document.id and document.cover_id!=0 and document.status=1
                                  order by download.download desc limit 0,3");
        foreach($result as &$value){
            $pic_id = $value['cover_id'];
            $category_id = $value['category_id'];
            $category = $model->query("select title from hisihi_category where id=".$category_id);
            $value['category_name'] = $category[0]['title'];
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
        $extra['data'] = $result;
        $this->apiSuccess("获取金榜作业成功", null, $extra);
    }

    /**
     * 获取云作业制定类别下的子分类
     * @param $category_id
     * @param int $page
     * @param int $count
     */
    public function hiworksChildCategory($category_id, $page=0, $count=10){
        $model = D('Hiworks/Category');
        $condition['status'] = 1;
        $condition['allow_publish'] = 1;
        $condition['pid'] = $category_id;
        $result = $model->where($condition)->field('id, name, title, pid, icon')->order('create_time desc')->page($page, $count)->select();
        foreach($result as &$value){
            $value['icon'] = $this->getIconUrl($value['icon']);
        }
        $extra['data'] = $result;
        $this->apiSuccess('获取云作业子列表成功', null, $extra);
    }

    /**
     * @param $id
     * @return null|string
     */
    private function getIconUrl($id){
        $model = M();
        $pic_info = $model->query("select path from hisihi_picture where id=".$id);
        if($pic_info){
            $path = $pic_info[0]['path'];
            $objKey = substr($path, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $objKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if($isExist){
               return "http://hisihi-other.oss-cn-qingdao.aliyuncs.com/".$objKey;
            } else {
               return null;
            }
        }
    }
}