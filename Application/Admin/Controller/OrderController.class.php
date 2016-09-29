<?php

namespace Admin\Controller;

use Think\Exception;
use Think\Page;

class OrderController extends AdminController
{

    public function index(){
        $model = M('Order');
        $memModel = M('Member');
        $courseModel = M('OrganizationTeachingCourse');
        $rebateModel = M('Rebate');
        $count = $model->where('status>=0')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status>=0')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        foreach($list as &$item){
            $user_info = $memModel->field('nickname')->where('uid='.$item['uid'])->find();
            $item['nickname'] = $user_info['nickname'];
            $courseInfo = $courseModel->field('course_name')->where('id='.$item['courses_id'])->find();
            $item['course_name'] = $courseInfo['course_name'];
            $rebateInfo = $rebateModel->field('value, rebate_value')
                ->where('id='.$item['rebate_id'])->find();
            $item['yf_money'] = (int)$item['rebate_num'] * (int)$rebateInfo['value'];
            $item['dk_money'] = (int)$item['rebate_num'] * (int)$rebateInfo['rebate_value'];
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","抵扣券订单列表");
        $this->display();
    }

    public function detail($id){
        $model = M('Order');
        $memModel = M('Member');
        $courseModel = M('OrganizationTeachingCourse');
        $rebateModel = M('Rebate');
        $orgToCourseModel = M('OrganizationTeachingCourse');
        $orgModel = M('Organization');
        $item = $model->where('id='.$id)->find();
        $user_info = $memModel->field('nickname')->where('uid='.$item['uid'])->find();
        $item['nickname'] = $user_info['nickname'];
        $courseInfo = $courseModel->field('course_name')->where('id='.$item['courses_id'])->find();
        $item['course_name'] = $courseInfo['course_name'];
        $rebateInfo = $rebateModel->field('value, rebate_value, use_start_time, use_end_time')
            ->where('id='.$item['rebate_id'])->find();
        $item['yf_money'] = (int)$item['rebate_num'] * (int)$rebateInfo['value'];
        $item['dk_money'] = (int)$item['rebate_num'] * (int)$rebateInfo['rebate_value'];
        $orgCourseInfo = $orgToCourseModel->field('organization_id')
            ->where('id='.$item['courses_id'])->find();
        $orgInfo = $orgModel->field('name')->where('id='.$orgCourseInfo['organization_id'])->find();
        $item['organization_name'] = $orgInfo['name'];
        $this->assign("order", $item);
        $this->assign("meta_title","抵扣券订单详情");
        $this->display();
    }

    public function confirm_enroll($id){
        $model = M('Order');
        $data['enroll_status'] = 1;
        $model->where('id='.$id)->save($data);
        $this->success('更新成功', 'index.php?s=/admin/order/index');
    }

    public function confirm_gift_package($id){
        $model = M('Order');
        $data['gift_package_status'] = 1;
        $model->where('id='.$id)->save($data);
        $this->success('更新成功', 'index.php?s=/admin/order/index');
    }

    public function set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('Order');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/order/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

}
