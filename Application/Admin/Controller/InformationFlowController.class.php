<?php

namespace Admin\Controller;
use Think\Exception;
use Think\Page;
/**
 * 2.5版本首页资讯流部分
 * Class InformationFlowController
 * @package Admin\Controller
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
            $pic_id = $_POST["picture"];
            A('Organization')->uploadLogoPicToOSS($pic_id);
            $data['pic_url'] = A('Organization')->fetchCdnImage($pic_id);
            $data['pic_id'] = $pic_id;
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/informationflow/banner');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/informationflow/banner');
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
            $this->success('处理成功','index.php?s=/admin/informationflow/banner');
        } else {
            $this->error('未选择要处理的数据');
        }
    }


}