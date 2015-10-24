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
        $this->display('Index/announcement');
    }

    public function basicinfo(){
        $this->display();
    }
    public function teachers(){
        $this->display();
    }
}