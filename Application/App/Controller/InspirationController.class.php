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
        $data_3['id'] = 3;
        $data_3['value'] = "收藏最多";
        $data_4['id'] = 4;
        $data_4['value'] = "浏览最多";
        $tags = array($data_1, $data_2, $data_3, $data_4);
        $extra['data'] = $tags;
        $this->apiSuccess('获取筛选条件列表成功', null, $extra);
    }

    /**
     * 获取灵感图片列表
     * @param int $category_id
     * @param int $filter_type   1-特别推荐  2-精选作品  3-收藏最多  4-浏览最多
     * @param int $page
     * @param int $count
     */
    public function inspirationImgList($category_id=0, $filter_type=1, $page=1, $count=10){
        $model = D('Admin/Inspiration');
        $map['status'] = 1;
        if($category_id!=0){
            $map['category_id'] = $category_id;
        }
        switch($filter_type){
            case 1:
                $map['special'] = 1;
                $total_count = $model->where($map)->count();
                $list = $model->where($map)->page($page, $count)->select();
                break;
            case 2:
                $map['selection'] = 1;
                $total_count = $model->where($map)->count();
                $list = $model->where($map)->page($page, $count)->select();
                break;
            case 3:
                $total_count = $model->where($map)->count();
                $list = $model->where($map)->order('favorite_count desc')->page($page, $count)->select();
                break;
            case 4:
                $total_count = $model->where($map)->count();
                $list = $model->where($map)->order('view_count desc')->page($page, $count)->select();
                break;
        }
        $list = $this->formatList($list);
        $extra['totalCount'] = $total_count;
        $extra['data'] = $list;
        $this->apiSuccess('获取灵感图片列表成功', null, $extra);
    }

    /**
     * 收藏灵感图片
     * @param int $uid
     * @param int $pic_id
     */
    public function doFavorite($uid=0, $pic_id=0){
        if(empty($pic_id)){
            $this->apiError(-1, '传入图片id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }
        $favorite['appname'] = 'Inspiration';
        $favorite['table'] = 'Inspiration';
        $favorite['row'] = $pic_id;
        $favorite['uid'] = $uid;

        if (D('Favorite')->where($favorite)->count()) {
            $this->apiError(-100,'您已经收藏，不能再收藏了!');
        } else {
            $favorite['create_time'] = time();
            if (D('Favorite')->where($favorite)->add($favorite)) {
                $this->apiSuccess('感谢您的支持');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        }
    }

    /**
     * 判断灵感图片是否收藏
     * @param int $uid
     * @param int $pic_id
     */
    public function isFavorite($uid=0, $pic_id=0){
        if(empty($pic_id)){
            $this->apiError(-1, '传入图片id为空');
        }
        if(empty($uid)){
            $this->requireLogin();
            $uid = $this->getUid();
        }
        $favorite['appname'] = 'Inspiration';
        $favorite['table'] = 'Inspiration';
        $favorite['row'] = $pic_id;
        $favorite['uid'] = $uid;

        if (D('Favorite')->where($favorite)->count()) {
            $extra['isFavorite'] = true;
            $this->apiSuccess('查询成功', null, $extra);
        } else {
            $extra['isFavorite'] = false;
            $this->apiSuccess('查询成功', null, $extra);
        }
    }

    private function formatList($list=null){
        if(!empty($list)){
            foreach($list as &$inspira){
                $pic_id = $inspira['pic_id'];
                $pic_url = $this->fetchImage($pic_id);
                $origin_img_info = getimagesize($pic_url);
                $src_size = Array();
                $src_size['width'] = $origin_img_info[0]; // width
                $src_size['height'] = $origin_img_info[1]; // height
                $inspira['picture'] = array(
                    'url'=>$pic_url,
                    'size'=>$src_size
                );
                $pic_small = getThumbImageById($pic_id, 280, 160);
                $thumb_img_info = getimagesize($pic_small);
                $thumb_size = Array();
                $thumb_size['width'] = $thumb_img_info[0]; // width
                $thumb_size['height'] = $thumb_img_info[1]; // height
                $inspira['thumb'] = array(
                    'url'=>$pic_small,
                    'size'=>$thumb_size
                );
                unset($inspira['id']);
                unset($inspira['status']);
            }
        }
        return $list;
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