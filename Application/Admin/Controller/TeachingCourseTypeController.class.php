<?php

namespace Admin\Controller;
use Think\Exception;


class TeachingCourseTypeController extends AdminController {

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index(){
        $model = D('App/OrganizationTeachingCourseType', 'Model');
        $list = $model->where('status=1')->select();
        $this->assign('_list', $list);
        $this->display();
    }

    public function add(){
        $this->display();
    }

    public function edit($id){
        $model = D('App/OrganizationTeachingCourseType', 'Model');
        $info = $model->where('id='.$id)->find();
        $this->assign('info', $info);
        $this->display();
    }

    public function update(){
        if (IS_POST) { //提交表单
            $model = D('App/OrganizationTeachingCourseType', 'Model');
            $cid = $_POST['cid'];
            $data['name'] = $_POST["name"];
            $data['pic_id'] = $_POST['pic_id'];
            $data['special_type'] = $_POST['special_type'];
            $data['sort'] = $_POST['sort'];
            if(!empty($data['pic_id'])){
                A('Organization')->uploadLogoPicToOSS($data['pic_id']);
                $data['pic_url'] = A('Organization')->fetchCdnImage($data['pic_id']);
            }
            if(empty($cid)){
                try {
                    $data = $model->create($data);
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/teaching_course_type/index');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/teaching_course_type/index');
            }
        } else {
            $this->display('add');
        }
    }

    public function delete($id){
        if(!empty($id)){
            $model = D('App/OrganizationTeachingCourseType', 'Model');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->updateType($i, $data);
                }
            } else {
                $id = intval($id);
                $model->updateType($id, $data);
            }
            $this->success('删除成功','index.php?s=/admin/teaching_course_type/index');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

}
