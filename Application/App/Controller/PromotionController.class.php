<?php

namespace App\Controller;

use Think\Controller;

class PromotionController extends AppController
{

    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    public function promotion_detail($organization_id=0, $promotion_id=0){
        $this->assign("organization_id", $organization_id);
        $this->assign("promotion_id", $promotion_id);
        $this->display('promotion_detail');
    }

    public function promotion_detail_share($organization_id=0, $promotion_id=0){
        $this->assign("organization_id", $organization_id);
        $this->assign("promotion_id", $promotion_id);
        $this->display('promotion_detail_share');
    }

}