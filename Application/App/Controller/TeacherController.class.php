<?php
namespace App\Controller;
use Common\Controller\BaseController;

class TeacherController extends BaseController
{
    public function __construct(){
        parent::__construct();
    }

    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    public function teacherv3_1(){
        $this->display('teacherv3_1');
    }

}