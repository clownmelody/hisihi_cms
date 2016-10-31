<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 2016/10/31
 * Time: 16:21
 */

namespace App\Controller;
use Common\Controller\BaseController;


class Encyclopedia extends BaseController {

    public function __construct(){
        parent::__construct();
    }

    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    public function encyclopedia(){
        $this->display('encyclopedia');
    }

}
