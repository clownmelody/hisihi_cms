<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 2016/10/31
 * Time: 16:21
 */

namespace Admin\Controller;
use Common\Controller\BaseController;


class EncyclopediaController extends AdminController {


    public function entry_add(){
        $this->display('entry_add');
    }
}
