<?php
/**
 * Created by PhpStorm.
 * User: shaolei
 * Date: 2015/9/15 0015
 * Time: 12:15
 */

namespace Organization\Controller;
use Think\Controller;

class IndexController extends Controller
{   
    public function _initialize()
    {
        C('SHOW_PAGE_TRACE', false);
    }
    /* -- 添加自定义function -- */
    public function index(){
        layout(false);
        $this->display('Index/home');
    }
    public function announcement(){
        $this->assign('title','最新公告');
        $this->display();
    }

    public function basicinfo(){
        $this->assign('title','基本信息');
        $this->display();
    }
    public function teachers(){
        $this->assign('title','我的老师');
        $this->display();
    }
    public function video(){
        $this->assign('title','视频教程');
        $this->display();

    }
    public function addnewlesson($id){
        $this->assign('title','添加教程');
        $this->assign('id', $id);
        $this->display();
    }
    public  function  lessondetailinfo($id){
        $this->assign('title','教程信息');
        $this->assign('id', $id);
        $this->display();
    }
    public  function  studentworks(){
        $this->assign('title','学生作品');
        $this->display();
    }
    public  function  teachcondition(){
        $this->assign('title','教学环境');
        $this->display();
    }
    public  function  certification(){
        $this->assign('title','认证管理');
        $this->display();
    }

}