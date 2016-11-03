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
            if(intval($data["cover_id"]) > 0){
                $this->uploadLogoPicToOSS($data["cover_id"]);
                $data['cover_url'] = $this->fetchCdnImage($data["cover_id"]);
            }
            unset($data['cover_id']);
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $entry_id = $model->add($data);
                    if($entry_id === false){
                        $this->error("新增失败");
                    }else{
                        $this->success('添加成功', 'index.php?s=/admin/encyclopedia/catalogue_add/entry_id/'.$entry_id);
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if ($res === false){
                    $this->error("编辑失败");
                }else{
                    $this->success('更新成功', 'index.php?s=/admin/encyclopedia/catalogue_add/entry_id/'.$cid);
                }
            }
        } else {
            $this->display('entry_add');
        }
    }

    public function catalogue_add(){
        $entry_id = I('entry_id');
        if(empty($entry_id)){
            $this->error('请先添加词条基本信息', 'index.php?s=/admin/encyclopedia/entry_add/');
        }
        $entry = M('EncyclopediaEntry')->where('id='.$entry_id)->find();
        $this->assign('entry', $entry);
        $this->display('catalogue_add');
    }

    public function catalogue_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaEntryCatalogue');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            if(empty($cid)){
                $data["entry_id"] = $_POST["entry_id"];
                $data["pid"] = $_POST["pid"];
                $data["create_time"] = time();
                try {
                    $catalogue_id = $model->add($data);
                    if($catalogue_id === false){
                        $rdata['status'] = -1;
                        $rdata['msg'] = '新增失败';
                        $this->ajaxReturn($rdata, 'JSON');
                    }else{
                        $rdata['catalogue_id'] = $catalogue_id;
                        $rdata['status'] = 1;
                        $rdata['msg'] = '新增成功';
                        $this->ajaxReturn($rdata, 'JSON');
                    }
                } catch (Exception $e) {
                    $rdata['status'] = -1;
                    $rdata['msg'] = '新增失败';
                    $this->ajaxReturn($rdata, 'JSON');
                }
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if ($res === false){
                    $rdata['status'] = -1;
                    $rdata['msg'] = '修改失败';
                    $this->ajaxReturn($rdata, 'JSON');
                }else{
                    $rdata['status'] = 1;
                    $rdata['msg'] = '修改成功';
                    $this->ajaxReturn($rdata, 'JSON');
                }
            }
        } else {
            $this->display('entry_add');
        }
    }

    public function entry_catagory_add(){
        $model = M('EncyclopediaCategory');
        $plist = $model->where('pid = 0 and status=1')->order('create_time desc')->select();
        $list = $model->where('status=1 and pid > 0')->order('create_time desc')->select();
        $this->assign('pcatagory', $plist);
        $this->assign('catagory', $list);
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
        $model = M('EncyclopediaEntryLink');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as &$item){
            $info = M('InformationFlowContent')->field('name')->where('id='.$item['link_id'])->find();
            $item['name'] = $info['name'];
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->display('entry_link_add');
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

    public function content_add(){
        $this->display('content_add');
    }

    public function content_update(){
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
            $this->display('content_add');
        }
    }

    /**
     * 上传图片到OSS
     * @param $picID
     */
    public function uploadLogoPicToOSS($picID){
        $model = M();
        $result = $model->query("select path from hisihi_picture where id=".$picID);
        if($result){
            $picLocalPath = $result[0]['path'];
            $picKey = substr($picLocalPath, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $picKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if(!$isExist){
                Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadOtherResource', $param);
            }
        }
    }

    /**
     * 获取cdn oss图片地址
     * @param $pic_id
     * @return null|string
     */
    public function fetchCdnImage($pic_id){
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
                $picUrl = "http://pic.hisihi.com/".$objKey;
            } else {
                $picUrl = null;
            }
        }
        return $picUrl;
    }

}
