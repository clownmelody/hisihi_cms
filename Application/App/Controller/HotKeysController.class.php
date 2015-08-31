<?php

namespace App\Controller;

use Think\Controller;

class HotKeysController extends AppController
{

    public function _initialize()
    {

    }

    // ps  ai  cad   CDR   AE
    public function sort(){
        $data = array();
        $data[] = array(
            'text' => 'ps',
            'icon' => 'http://1.com'
        );
        $data[] = array(
            'text' => 'ai',
            'icon' => 'http://2.com'
        );
        $data[] = array(
            'text' => 'cad',
            'icon' => 'http://3.com'
        );
        $data[] = array(
            'text' => 'cdr',
            'icon' => 'http://4.com'
        );
        $data[] = array(
            'text' => 'ae',
            'icon' => 'http://5.com'
        );
        $this->apiSuccess($data);
    }
}