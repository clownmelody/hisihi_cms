<?php

namespace Admin\Controller;

use Think\Page;

/**
 * 机构活动模块
 * Class PromotionController
 * @package Admin\Controller
 */
class PromotionController extends AdminController
{

    public function index(){
        $model = M('Promotion');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","活动列表");
        $this->display();
    }

    public function promotion_add(){
        $this->display('promotion_add');
    }

    public function update(){
        if (IS_POST) { //提交表单
            $model = M('Promotion');
            $cid = $_POST['cid'];
            $data['title'] = $_POST["title"];
            $data['description'] = $_POST["description"];
            $logo_id = $_POST["logo_url"];
            $little_logo_id = $_POST["little_logo_url"];
            $tag_id = $_POST["tag_url"];
            if(!empty($logo_id)){
                A('Organization')->uploadLogoPicToOSS($logo_id);
                $data['logo_url'] = A('Organization')->fetchCdnImage($logo_id);
            }
            if(!empty($little_logo_id)){
                A('Organization')->uploadLogoPicToOSS($little_logo_id);
                $data['little_logo_url'] = A('Organization')->fetchCdnImage($little_logo_id);
            }
            if(!empty($tag_id)){
                A('Organization')->uploadLogoPicToOSS($tag_id);
                $data['tag_url'] = A('Organization')->fetchCdnImage($tag_id);
            }
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/promotion/index');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/promotion/index');
            }
        } else {
            $this->display('promotion_add');
        }
    }

    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('Promotion');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('promotion', $data);
        $this->meta_title = '编辑活动';
        $this->display();
    }

    public function set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('Promotion');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/promotion/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

}
