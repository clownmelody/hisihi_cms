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
    /* -- ����Զ���function -- */
    public function index(){
        $this->name = 'thinkphp111'; // ����ģ�������ֵ
        $this->display();
    }

    public function user(){
        $this->name = 'thinkphp2'; // ����ģ�������ֵ
        $this->display();
    }
    public function person(){
        $this->name = 'thinkphp3'; // ����ģ�������ֵ
        $this->display();
    }
}