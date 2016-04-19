<?php

namespace Admin\Controller;
use Think\Exception;
use Think\Page;

/**
 * 2.5版本首页资讯流部分
 */
class FrontPageController extends AdminController {

    /**
     * 头条内容列表
     */
    public function content(){
        $model = M('FrontPage');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as &$content){
            $config_type = $content['category'];
            $model = M('FrontPageCategory');
            $config_detail = $model->field('name')->where('id='.$config_type)->find();
            $content['category'] = $config_detail['name'];
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","头条内容");
        $this->display();
    }

    /**
     * 内容状态修改
     * @param $id
     * @param int $status
     */
    public function setContentStatus($id, $status=1){
        if(!empty($id)){
            $model = M('FrontPage');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/FrontPage/content');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    /**
     * 头条分类列表
     */
    public function config(){
        $model = M('FrontPageCategory');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","头条分类配置");
        $this->display();
    }

    public function configAdd(){
        $this->display();
    }

    public function configUpdate(){
        if (IS_POST) { //提交表单
            $model = M('FrontPageCategory');
            $cid = $_POST['cid'];
            $data['name'] = $_POST["title"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/FrontPage/config');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/FrontPage/config');
            }
        } else {
            $this->display('configAdd');
        }
    }

    /**
     * 内容状态修改
     * @param $id
     * @param int $status
     */
    public function setConfigStatus($id, $status=1){
        if(!empty($id)){
            $model = M('FrontPageCategory');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/FrontPage/config');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    /**
     * @param $id
     */
    public function editconfig($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('FrontPageCategory');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('banner', $data);
        $this->meta_title = '编辑分类';
        $this->display();
    }

    public function setConfigType($id){
        $model = M('FrontPageCategory');
        $config_type = $model->where('status=1')->order('id')->select();
        $article_id = M('FrontPage')->where('id='.$id)->getField('article_id');
        $this->assign('_config_type', $config_type);
        $this->assign('cid', $id);
        $this->assign('article_id', $article_id);
        $this->display();
    }


    public function setConfigUpdate($cid, $config_type, $article_id){
        if(!empty($cid)){
            $model = M('FrontPage');
            $data['category'] = $config_type;
            $cid = intval($cid);
            $result = $model->where('category='.$config_type.' and article_id='.$article_id)->select();
            if($result){
                $this->success('该文章已经存在于此分类下了','index.php?s=/admin/FrontPage/content');
            }
            $model->where('id='.$cid)->save($data);
            $this->success('处理成功','index.php?s=/admin/FrontPage/content');
        } else {
            $this->error('未选择要处理的数据');
        }
    }


}