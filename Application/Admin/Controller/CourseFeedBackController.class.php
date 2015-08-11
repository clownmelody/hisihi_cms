<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Page;


class CourseFeedBackController extends AdminController
{
    public function index()
    {
        $model = D('CourseFeedBack');
        $count = $model->where('resolved=0')->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $list = $model->where('resolved=0')->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","课程反馈");
        $this->display();
    }

    public function resolve($id){
        if(empty($id)){
            $this->error('请选择要操作的数据');
        }
        $model = D('CourseFeedBack');
        $result = $model->resolve($id);
        if($result){
            $this->success("成功处理！");
        } else {
            $this->error("写入数据库失败,请重试！");
        }
    }

    public function deleteFeedback($id){
        if(empty($id)){
            $this->error('请选择要操作的数据');
        }
        $model = D('CourseFeedBack');
        $result = $model->deleteFeedback($id);
        if($result){
            $this->success("成功删除！");
        } else {
            $this->error("写入数据库失败,请重试！");
        }
    }


}
