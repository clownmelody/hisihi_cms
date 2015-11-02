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
        $this->display();
    }

    public function basicinfo(){
        $this->display();
    }
    public function teachers(){
        $this->display();
    }
    public function video(){
        $this->display();

    }
    public function addnewlesson($id){
        $this->assign('id', $id);
        $this->display();
    }
    public  function  lessondetailinfo($id){
        $this->assign('id', $id);
        $this->display();
    }
    public  function  studentworks(){
        $this->display();
    }
    public  function  teachcondition(){
        $this->display();
    }
    public  function  certification(){
        $this->display();
    }

}