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
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys%2FPS.png'
        );
        $data[] = array(
            'text' => 'ai',
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys%2FAI.png'
        );
        $data[] = array(
            'text' => 'cad',
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys%2FCAD.jpg'
        );
        $data[] = array(
            'text' => 'cdr',
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys%2FCDR.png'
        );
        $data[] = array(
            'text' => 'ae',
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys%2FAE.png'
        );
        $extra['data'] = $data;
        $this->apiSuccess('获取快捷键列表成功', null, $extra);
    }
}