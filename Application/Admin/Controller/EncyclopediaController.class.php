<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 2016/10/31
 * Time: 16:21
 */
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Admin\Model\CompanyConfigModel;
use Think\Exception;
use Think\Hook;
use Think\Page;

class EncyclopediaController extends AdminController {

    public function _initialize(){
        parent::_initialize();
    }

    public function category($pid=0){
        $model = M('EncyclopediaCategory');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        if($pid==0){
            $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        } else {
            $list = $model->where('status=1 and pid='.$pid)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$item){
            $info = $model->field('name')->where('id='.$item['pid'])->find();
            $item['pid_name'] = $info['name'];
        }
        $first_level_list = $model->field('id,name')->where('status=1 and pid=0')->select();
        $this->assign('first_level_list', $first_level_list);
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","分类列表");
        $this->display();
    }

    public function category_add($id){
        $this->assign('pid', $id);
        $this->display();
    }

    public function category_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        $Model = M('EncyclopediaCategory');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('category', $data);
        $this->assign("meta_title", '编辑分类');
        $this->display();
    }

    public function category_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaCategory');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["sort"] = $_POST["sort"];
            $data["pid"] = $_POST["pid"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/encyclopedia/category');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/encyclopedia/category');
            }
        } else {
            $this->display('category_add');
        }
    }

    public function entry_add(){
        $this->display('entry_add');
    }

    public function entry_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaEntry');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["sort"] = $_POST["sort"];
            $data["cover_id"] = $_POST["cover_id"];
            $data["abstract"] = $_POST["abstract"];
            $data["relevant_entry"] = $_POST["relevant_entry"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/encyclopedia/category');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/encyclopedia/category');
            }
        } else {
            $this->display('entry_add');
        }
    }

    public function catalogue_add(){
        $this->display('catalogue_add');
    }

    public function catalogue_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaEntryCatalogue');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["sort"] = $_POST["sort"];
            $data["cover_id"] = $_POST["cover_id"];
            $data["abstract"] = $_POST["abstract"];
            $data["relevant_entry"] = $_POST["relevant_entry"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/encyclopedia/category');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/encyclopedia/category');
            }
        } else {
            $this->display('entry_add');
        }
    }

    public function entry_catagory_add(){
        $this->display('entry_catagory_add');
    }

    public function entry_catagory_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaEntryCatalogue');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["sort"] = $_POST["sort"];
            $data["cover_id"] = $_POST["cover_id"];
            $data["abstract"] = $_POST["abstract"];
            $data["relevant_entry"] = $_POST["relevant_entry"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/encyclopedia/category');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/encyclopedia/category');
            }
        } else {
            $this->display('entry_add');
        }
    }

    public function entry_link_add(){
        $this->display('entry_catagory_add');
    }

    public function entry_link_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaEntryCatalogue');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["sort"] = $_POST["sort"];
            $data["cover_id"] = $_POST["cover_id"];
            $data["abstract"] = $_POST["abstract"];
            $data["relevant_entry"] = $_POST["relevant_entry"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/encyclopedia/category');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/encyclopedia/category');
            }
        } else {
            $this->display('entry_add');
        }
    }
}
