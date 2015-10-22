<?php
namespace App\Controller;

use Think\Controller;
use Think\Exception;
use Think\Model;


class OrganizationWebController extends AppController {

    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    /* -- 添加自定义function -- */
    public function readme(){
        $this->display();
    }

}