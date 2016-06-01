<?php

namespace App\Controller;

use Think\Controller;

class PromotionController extends AppController
{

    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    public function promotion_detail($promotion_id=0){
        $this->assign("promotion_id", $promotion_id);
        $this->display('promotion_detail');
    }

}