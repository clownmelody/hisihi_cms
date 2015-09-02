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
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/PS.png'
        );
        $data[] = array(
            'text' => 'ai',
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/AI.png'
        );
        $data[] = array(
            'text' => 'cad',
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/CAD.jpg'
        );
        $data[] = array(
            'text' => 'cdr',
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/CDR.png'
        );
        $data[] = array(
            'text' => 'ae',
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys%2FAE.png'
        );
        $extra['data'] = $data;
        $this->apiSuccess('获取快捷键列表成功', null, $extra);
    }
}