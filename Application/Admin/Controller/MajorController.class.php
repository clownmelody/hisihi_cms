<?php
namespace Admin\Controller;
use Think\Exception;
use Think\Hook;
use Think\Page;


class MajorController extends AdminController {

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 显示推荐专业列表
     */
    public function index(){
        $model = D('RecomendMajors');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where("status=1")->order('sort desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","推荐专业列表");
        $this->display();
    }

    public function major_add(){
        $this->display('major_add');
    }

    public function major_update(){
        if (IS_POST) { //提交表单
            $model = M('RecomendMajors');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["sort"] = $_POST["sort"];
            $data["create_time"] = time();
            if(empty($cid)){
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/major/index');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/major/index');
            }
        } else {
            $this->display('major_add');
        }
    }

    public function major_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('RecomendMajors');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data) {
            $this->error($Model->getError());
        }
        $this->assign('info', $data);
        $this->meta_title = '编辑推荐专业';
        $this->display();
    }

    public function major_delete($id){
        if(!empty($id)){
            $model = D('RecomendMajors');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('删除成功','index.php?s=/admin/major/index');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

}
