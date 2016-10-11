<?php

namespace Admin\Controller;
use Think\Exception;
use Think\Page;


class QuestionController extends AdminController {

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index(){
        $model = M('Questions');
        $count = $model->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","常见问题列表");
        $this->display();
    }

    public function add(){
        $this->display();
    }

    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('Questions');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('info', $data);
        $this->assign('meta_title', '编辑问题');
        $this->display();
    }

    public function update(){
        if (IS_POST) { //提交表单
            $model = M('Questions');
            $cid = $_POST['cid'];
            $data['title'] = $_POST["title"];
            $data['content'] = $_POST['content'];
            if(empty($cid)){
                try {
                    $data['create_time'] = time();
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/question/index');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/question/index');
            }
        } else {
            $this->display('add');
        }
    }

    public function set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('Questions');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/question/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

}
