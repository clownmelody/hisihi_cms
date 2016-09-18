<?php

namespace Admin\Controller;

use Think\Exception;
use Think\Page;

/**
 * 抵扣券模块
 * Class RebateController
 * @package Admin\Controller
 */
class RebateController extends AdminController
{

    public function index(){
        $model = M('Rebate');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","抵扣券列表");
        $this->display();
    }

    public function rebate_add(){
        $this->display('rebate_add');
    }

    public function update(){
        if (IS_POST) { //提交表单
            $model = M('Rebate');
            $cid = $_POST['cid'];
            $data['name'] = $_POST["name"];
            $data['value'] = $_POST["value"];
            $data['rebate_value'] = $_POST["rebate_value"];
            $data['buy_end_time'] = strtotime($_POST["buy_end_time"]);
            $data['use_start_time'] = strtotime($_POST["use_start_time"]);
            $data['use_end_time'] = strtotime($_POST["use_end_time"]);
            $data['use_condition'] = $_POST["use_condition"];
            $data['use_method'] = $_POST['use_method'];
            $data['use_instruction'] = $_POST['use_instruction'];
            if(empty($data['use_condition'])){
                $data['use_condition'] = "";
            }
            if(empty($data['use_method'])){
                $data['use_method'] = "";
            }
            if(empty($data['use_instruction'])){
                $data['use_instruction'] = "";
            }
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/rebate/index');
            } else {
                $model->where('id='.$cid)->save($data);
                /*$updateData['service_condition'] = $data['service_condition'];
                $updateData['using_method'] = $data['using_method'];
                $updateData['instructions_for_use'] = $data['instructions_for_use'];
                M('TeachingCourseCouponRelation')->where('status=1 and coupon_id='.$cid)->save($updateData);*/
                $this->success('更新成功', 'index.php?s=/admin/rebate/index');
            }
        } else {
            $this->display('rebate_add');
        }
    }

    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        $Model = M('Rebate');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('rebate', $data);
        $this->meta_title = '编辑抵扣券';
        $this->display();
    }

    public function set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('Rebate');
            /*$tccr_model = M('TeachingCourseCouponRelation');*/
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                    /*$tccr_model->where('coupon_id='.$i)->save(array('status'=>-1));*/
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
                /*$tccr_model->where('coupon_id='.$id)->save(array('status'=>-1));*/
            }
            $this->success('处理成功','index.php?s=/admin/rebate/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

}
