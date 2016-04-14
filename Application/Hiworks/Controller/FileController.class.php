<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Hiworks\Controller;

/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */

class FileController extends HiworksController
{
    /* 下载文件 */
    public function download($id = null)
    {
        $token = $_SESSION["token"];
        if(!$token) {
            redirect('/hiworks.php');
        }

        if (empty($id) || !is_numeric($id)) {
            $this->error('参数错误！');
        }

        $logic = D('Download', 'Logic');
        if (!$logic->download($id)) {
            $this->error($logic->getError());
        }
    }

    public function downloadZip($key){
        $id = $this->caesar_decode($key, 'hisihi_hiworks_downlaod');
        $logic = D('Download', 'Logic');
        if (!$logic->download($id)) {
            $this->error($logic->getError());
        }
    }

    private function caesar_decode($s, $k) {
        $k = "$k";
        for($i=0; $i<strlen($k); $i++) {
            $d = 36 - base_convert($k{$i}, 36, 10);
            $t = '';
            for($j=0; $j<strlen($s); $j++)
                $t .= base_convert((base_convert($s{$j}, 36, 10)+$d)%36, 10, 36);
            $s = $t;
        }
        return $t;
    }

}
