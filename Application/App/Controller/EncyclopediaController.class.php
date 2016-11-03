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
        //$entry_map['status'] = 1;
        $list = M('EncyclopediaEntry')->where($entry_map)->field('id, name, abstract, cover_url')
            ->order('sort desc, create_time desc')->page($page, $count)->select();
        foreach ($list as &$item2){
            $item2['content_url'] = C('HOST_NAME_PREFIX').'app.php/encyclopedia/encyclopedia/entry_id/'.$item2['id'];
            $item2['digest'] = $item2['abstract'];
            unset($item2['abstract']);
        }
        $count = M('EncyclopediaEntry')->where($entry_map)->count();
        if(empty($list)){
            $list = null;
        }
        if(empty($count)){
            $count = 0;
        }
        $this->apiSuccess("获取词条列表成功", null,
            array('data' => $list, 'total_count' => $count));
    }

    public function searchEntry($name=''){
        $entry_map['name'] = array('like', '%'.$name.'%');
        $entry_map['status'] = 1;
        $entry_ids = M('EncyclopediaEntry')->where($entry_map)->field('id')
            ->select();
        $entry_id_array = array();
        foreach ($entry_ids as &$item1){
            $entry_id_array[] = $item1['id'];
        }

        $map0['entry_id'] = array('in', $entry_id_array);
        $first_catragory_ids = M('EncyclopediaEntryCatagory')->field('first_catagory_id')->where($map0)
            ->group('first_catagory_id')->select();
        $first_catragory_array = array();
        foreach ($first_catragory_ids as &$item){
            $first_catragory_array[] = $item['first_catagory_id'];
        }
        $first_catragory_map['status'] = 1;
        $first_catragory_map['id'] = array('in', $first_catragory_array);
        $first_catragory_list = M('EncyclopediaCategory')->where($first_catragory_map)->field('id, name')
            ->order('sort desc, create_time desc')->select();
        $all_catagory_list = array();
        foreach ($first_catragory_list as &$item2){
            $map1['first_catagory_id'] = $item2['id'];
            $map1['status'] = 1;
            $map1['entry_id'] = array('in', $entry_id_array);
            $ids_in_first_catagory = M('EncyclopediaEntryCatagory')->where($map1)->field('entry_id')->select();
            $entry_id_array2 = array();
            foreach ($ids_in_first_catagory as &$item3){
                $entry_id_array2[] = $item3['entry_id'];
            }

            $map2['id'] = array('in', $entry_id_array2);
            $entry_in_catagory = M('EncyclopediaEntry')->where($map2)->field('id, name, abstract, cover_url')
                ->order('sort desc, create_time desc')->page(1,3)->select();
            foreach ($entry_in_catagory as &$item4){
                $item4['content_url'] = C('HOST_NAME_PREFIX').'app.php/encyclopedia/encyclopedia/entry_id/'.$item4['id'];
                $item4['digest'] = $item4['abstract'];
                unset($item4['abstract']);
            }
            $count = M('EncyclopediaEntry')->where($map2)->count();
            if(!empty($entry_in_catagory)){
                $all_catagory_list[] = array(
                    'catagory_id'=>$item2['id'],
                    'catagory_name'=>$item2['name'],
                    'data'=>$entry_in_catagory,
                    'total_count'=>$count
                );
            }
        }
        $this->apiSuccess("查询词条成功", null,
            array('data' => $all_catagory_list));
    }

}
