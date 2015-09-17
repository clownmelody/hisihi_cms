<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Think\Exception;
use Think\Hook;
use Think\Page;

/**
 * 公司管理模块
 * Class CompanyController
 * @package Admin\Controller
 */
class CompanyController extends AdminController {

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 显示公司列表
     */
    public function index(){
        $model = D('Company');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","公司列表");
        $this->display();
    }

    public function add(){
        $this->display();
    }

    public function update(){
        if (IS_POST) { //提交表单
            $model = M('Company');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["content"] = $_POST["content"];
            $data["picture"] = $_POST["picture"];
            $data["create_time"] = time();
            if(empty($cid)){
                try {
                    $res = $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功');
            } else {
                $model = D('Company');
                $model->updateCompany($cid, $data);
                $this->success('更新成功', Cookie('__forward__'));
            }
        } else {
            $this->display('add');
        }
    }

    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('Company');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('info', $data);
        $this->meta_title = '编辑公司';
        $this->display();
    }

    public function delete($id){
        if(!empty($id)){
            $model = D('Company');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->updateCompany($i, $data);
                }
            } else {
                $id = intval($id);
                $model->updateCompany($id, $data);
            }
            $this->success('删除成功',Cookie('__forward__'));
        } else {
            $this->error('未选择要删除的数据');
        }
    }

}
