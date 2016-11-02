<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 2016/10/31
 * Time: 16:21
 */

namespace App\Controller;
use Common\Controller\BaseController;
use Think\Hook;

class EncyclopediaController extends BaseController {

    public function __construct(){
        parent::__construct();
    }

    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    public function encyclopedia($entry_id=0){
        $this->assign('entry_id', $entry_id);
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

    public function getEntryByCatagory($catagory_id=0, $page=1, $count=10){
        $map['catagory_id'] = $catagory_id;
        $map['status'] = 1;
        $entry_ids = M('EncyclopediaEntryCatagory')->where($map)->field('entry_id')
            ->select();
        $entry_array = array();
        foreach ($entry_ids as &$item){
            $entry_array[] = $item['entry_id'];
        }
        $entry_map['id'] = array('in', $entry_array);
        $entry_map['status'] = 1;
        $list = M('EncyclopediaEntry')->where($entry_map)->field('id, name, cover_id, abstract')
            ->order('sort desc, create_time desc')->page($page, $count)->select();
        foreach ($list as &$item2){
            $item2['cover_url'] = M('Picture')->where('id='.$item2['cover_id'])->getField('path');
            $picKey = substr($item2['cover_url'], 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $picKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            unset($item2['cover_id']);
            if($isExist){
                $cover_pic = "http://hisihi-other".C('OSS_ENDPOINT').$picKey;
                $item2['cover_url'] = $cover_pic;
            }
            $item2['content_url'] = C('HOST_NAME_PREFIX').'app.php/encyclopedia/encyclopedia/entry_id/'.$item2['id'];
        }
        $count = M('EncyclopediaEntry')->where($entry_map)->count();
        $this->apiSuccess("获取词条列表成功", null,
            array('data' => $list, 'total_count' => $count));
    }


}
