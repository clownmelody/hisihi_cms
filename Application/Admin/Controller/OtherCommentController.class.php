<?php

namespace Admin\Controller;

use Exception;
use Think\Page;


class OtherCommentController extends AdminController
{
    public function index(){
        $model = M('OrganizationOtherComment');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","第三方评论列表");
        $this->display();
    }

    public function othercomment_add(){
        $this->display('othercomment_add');
    }

    public function update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationOtherComment');
            $cid = $_POST['cid'];
            $data['name'] = $_POST["name"];
            $data['content'] = $_POST["content"];
            $data['from'] = $_POST["from"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/othercomment/index');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/othercomment/index');
            }
        } else {
            $this->display('othercomment_add');
        }
    }

    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('OrganizationOtherComment');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('comment', $data);
        $this->meta_title = '编辑评论';
        $this->display();
    }

    public function set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('OrganizationOtherComment');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/othercomment/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

}
