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
        $list = $model->field('id,name')->where($data)->order('sort asc')->select();
        $this->apiSuccess("获取百科一级分类列表成功", null,
            array('data' => $list, 'total_count' => count($list)));
    }

    public function getSecondLevelCategory($id=0){
        $model = M('EncyclopediaCategory');
        $data['status'] = 1;
        $data['pid'] = $id;
        $list = $model->field('id,name')->where($data)->order('sort asc')->select();
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

    public function searchEntryMore($name='', $category_id=0, $page=1, $count=10){
        $entry_map['name'] = array('like', '%'.$name.'%');
        $entry_map['status'] = 1;
        $entry_ids = M('EncyclopediaEntry')->where($entry_map)->field('id')
            ->select();
        $entry_id_array = array();
        foreach ($entry_ids as &$item1){
            $entry_id_array[] = $item1['id'];
        }

        $map0['entry_id'] = array('in', $entry_id_array);
        $map0['first_catagory_id'] = $category_id;
        $first_catragory_ids = M('EncyclopediaEntryCatagory')->field('entry_id')->where($map0)
            ->select();
        $first_catragory_array = array();
        foreach ($first_catragory_ids as &$item){
            $first_catragory_array[] = $item['entry_id'];
        }
        $map2['id'] = array('in', $first_catragory_array);
        $limit_index = ($page-1)*$count + 3;
        $entry_in_catagory = M('EncyclopediaEntry')->where($map2)->field('id, name, abstract, cover_url')
            ->order('sort desc, create_time desc')->limit($limit_index,$count)->select();
        foreach ($entry_in_catagory as &$item4){
            $item4['content_url'] = C('HOST_NAME_PREFIX').'app.php/encyclopedia/encyclopedia/entry_id/'.$item4['id'];
            $item4['digest'] = $item4['abstract'];
            unset($item4['abstract']);
        }
        $total_count = M('EncyclopediaEntry')->where($map2)->count();

        $this->apiSuccess("查询词条成功", null,
            array('data' => $entry_in_catagory, 'total_count'=>$total_count));
    }

    public function getEntryUrl($entry_id=0){
        $entry = M('EncyclopediaEntry')->where('id='.$entry_id)->find();
        $url = C('HOST_NAME_PREFIX').'app.php/encyclopedia/encyclopedia/entry_id/'.$entry_id;
        $data['content_url'] = $url;
        $data['id'] = $entry['id'];
        $data['name'] = $entry['name'];
        $data['digest'] = $entry['abstract'];
        $data['cover_url'] = $entry['cover_url'];
        $this->apiSuccess("查询词条成功", null,
            array('data' => $data));
    }

    public function getEntryDetail($entry_id=0){
        $entry_info = M('EncyclopediaEntry')->where('id='.$entry_id)->field('id, name, abstract, relevant_entry')->find();
        $headInfo = array(
            'id'=>$entry_info['id'],
            'title'=>$entry_info['name'],
            'detail'=>$entry_info['abstract']
        );
        $likeKeyWords = null;
        if(!empty($entry_info['relevant_entry'])){
            $relevant_entry_id =  explode('#', $entry_info['relevant_entry']);
            $map['id'] = array('in', $relevant_entry_id);
            $map['status'] = 1;
            $relevant_entry_list = M('EncyclopediaEntry')->where($map)->field('id, name')
                ->order('sort desc , create_time desc')->select();
            $likeKeyWords = $relevant_entry_list;
        }
        $catalog = null;
        $map1['entry_id'] = $entry_id;
        $map1['status'] = 1;
        $map1['pid'] = 0;
        $p_catalog = M('EncyclopediaEntryCatalogue')->where($map1)->field('id, name')->select();
        if(!empty($p_catalog)){
            $p_catalog_arr = array();
            foreach ($p_catalog as $item0){
                $p_catalog_arr[] = $item0['id'];
            }
            $map2['catalogue_id'] = array('in', $p_catalog_arr);
            $map2['entry_id'] = $entry_id;
            $map2['status'] = 1;
            $p_content = M('EncyclopediaEntryContent')->where($map2)->field('catalogue_id, content')->select();
            foreach ($p_catalog as &$item1){
                foreach ($p_content as &$content1){
                    if($content1['catalogue_id'] == $item1['id']){
                        $item1['detail'] = $content1['content'];
                    }
                }
                $item1['level2'] = null;
                $map3['pid'] = $item1['id'];
                $map3['entry_id'] = $entry_id;
                $map3['status'] = 1;
                $c_catalog = M('EncyclopediaEntryCatalogue')->where($map3)->field('id, name')->select();
                if(!empty($c_catalog)){
                    $c_catalog_arr = array();
                    foreach ($c_catalog as $item2){
                        $c_catalog_arr[] = $item2['id'];
                    }
                    $map4['catalogue_id'] = array('in', $c_catalog_arr);
                    $map4['entry_id'] = $entry_id;
                    $map4['status'] = 1;
                    $c_content = M('EncyclopediaEntryContent')->where($map4)->field('catalogue_id, content')->select();
                    foreach ($c_catalog as &$item3){
                        foreach ($c_content as &$content2){
                            if($content2['catalogue_id'] == $item3['id']){
                                $item3['detail'] = $content2['content'];
                            }
                        }
                    }
                    $item1['level2'] = $c_catalog;
                }
            }
            $catalog = $p_catalog;
        }

        $linkInfo = null;
        $map5['entry_id'] = $entry_id;
        $map5['status'] = 1;
        $link_list = M('EncyclopediaEntryLink')->where($map5)->field('id, name, link')->select();
        if(!empty($link_list)){
            $linkInfo = $link_list;
        }
        $data = array(
            'headInfo'=>$headInfo,
            'likeKeyWords'=>$likeKeyWords,
            'catalog'=>$catalog,
            'linkInfo'=>$linkInfo
        );
        $this->apiSuccess("获取词条详情成功", null,
            array('data' => $data));
    }
}
