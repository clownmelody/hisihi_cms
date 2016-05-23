<?php

namespace Admin\Controller;

use Think\Page;

/**
 * 优惠券模块
 * Class CouponController
 * @package Admin\Controller
 */
class CouponController extends AdminController
{

    public function index(){
        $model = M('Coupon');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","优惠券列表");
        $this->display();
    }

    public function coupon_add(){
        $this->display('coupon_add');
    }

    public function update(){
        if (IS_POST) { //提交表单
            $model = M('Coupon');
            $cid = $_POST['cid'];
            $data['name'] = $_POST["name"];
            $data['money'] = $_POST["money"];
            $data['start_time'] = strtotime($_POST["start_time"]);
            $data['end_time'] = strtotime($_POST["end_time"]);
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/coupon/index');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/coupon/index');
            }
        } else {
            $this->display('coupon_add');
        }
    }

    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('Coupon');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('coupon', $data);
        $this->meta_title = '编辑活动';
        $this->display();
    }

    public function set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('Coupon');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/coupon/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

}
