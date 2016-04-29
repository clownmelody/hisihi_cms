<?php

namespace Admin\Controller;
use Think\Exception;
use Think\Page;

/**
 * 2.5版本首页资讯流部分
 */
class InformationFlowController extends AdminController {

    /**
     * 资讯流Banner列表
     */
    public function banner(){
        $model = M('InformationFlowBanner');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","资讯流Banner");
        $this->display();
    }

    public function bannerAdd(){
        $this->display();
    }

    public function update(){
        if (IS_POST) { //提交表单
            $model = M('InformationFlowBanner');
            $cid = $_POST['cid'];
            $data['url'] = $_POST["url"];
            $data['show_pos'] = $_POST['show_pos'];
            $data['jump_type'] = $_POST['jump_type'];
            $pic_id = $_POST["picture"];
            if(!empty($pic_id)){
                A('Organization')->uploadLogoPicToOSS($pic_id);
                $data['pic_url'] = A('Organization')->fetchCdnImage($pic_id);
                $data['pic_id'] = $pic_id;
            }
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/informationFlow/banner');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/informationFlow/banner');
            }
        } else {
            $this->display('bannerAdd');
        }
    }

    /**
     * @param $id
     */
    public function editbanner($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('InformationFlowBanner');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('banner', $data);
        $this->meta_title = '编辑Banner';
        $this->display();
    }

    /**
     * Banner 状态修改
     * @param $id
     * @param int $status
     */
    public function setBannerStatus($id, $status=1){
        if(!empty($id)){
            $model = M('InformationFlowBanner');
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
            $this->success('处理成功','index.php?s=/admin/informationFlow/banner');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    /**
     * 资讯流内容列表
     */
    public function content(){
        $model = M('InformationFlowContent');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as &$content){
            $config_type = $content['config_type'];
            $model = M('InformationFlowConfig');
            $config_detail = $model->field('title')->where('id='.$config_type)->find();
            $content['config_type'] = $config_detail['title'];
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","资讯流内容");
        $this->display();
    }

    /**
     * 内容状态修改
     * @param $id
     * @param int $status
     */
    public function setContentStatus($id, $status=1){
        if(!empty($id)){
            $model = M('InformationFlowContent');
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
            $this->success('处理成功','index.php?s=/admin/informationFlow/content');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    /**
     * 资讯流配置列表
     */
    public function config(){
        $model = M('InformationFlowConfig');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","资讯流配置");
        $this->display();
    }

    public function configAdd(){
        $this->display();
    }

    public function configUpdate(){
        if (IS_POST) { //提交表单
            $model = M('InformationFlowConfig');
            $cid = $_POST['cid'];
            $data['title'] = $_POST["title"];
            if(empty($cid)){
                $data["create_time"] = time();
                $data["update_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/informationFlow/config');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/informationFlow/config');
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
            $model = M('InformationFlowConfig');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    if($i == 1 && $status == -1){
                        $this->error('特殊分类不能删除');
                    }
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/informationFlow/config');
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
        $Model = M('InformationFlowConfig');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('banner', $data);
        $this->meta_title = '编辑配置';
        $this->display();
    }

    public function setConfigType($id){
        $model = M('InformationFlowConfig');
        $config_type = $model->where('status>-1')->order('id')->select();
        $this->assign('_config_type', $config_type);
        $this->assign('cid', $id);
        $this->display();
    }


    public function setConfigUpdate($cid, $config_type){
        if(!empty($cid)){
            $model = M('InformationFlowContent');
            $data['config_type'] = $config_type;
            $cid = intval($cid);
            $model->where('id='.$cid)->save($data);
            $this->success('处理成功','index.php?s=/admin/informationFlow/content');
        } else {
            $this->error('未选择要处理的数据');
        }
    }


}