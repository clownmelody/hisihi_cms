<?php

namespace App\Controller;

use Think\Controller;

class HotKeysController extends AppController
{

    public function _initialize()
    {
        C('SHOW_PAGE_TRACE', false);
    }

    /**
     * 客户端快捷键列表
     */
    public function sort($version=null){
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
            'icon' => 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/AE.png'
        );
        if((float)$version>=2.2){
            $visit_count = M('CompanyConfig')->where('type=10 and status=1')->getField('value');
            $extra['allCount'] = $visit_count;
        }
        $extra['data'] = $data;
        $this->apiSuccess('获取快捷键列表成功', null, $extra);
    }

    /**
     * 快捷键分享
     * @param string $type
     */
    public function share($type='ps'){
        switch ($type){
            case '3dmax':
                $this->assign('url', 'download.php');
                $this->display('3dmax');
                break;
            case 'ae':
                $this->assign('url', 'download.php');
                $this->display('ae');
                break;
            case 'ai':
                $this->assign('url', 'download.php');
                $this->display('ai');
                break;
            case 'cad':
                $this->assign('url', 'download.php');
                $this->display('cad');
                break;
            case 'cdr':
                $this->assign('url', 'download.php');
                $this->display('cdr');
                break;
            case 'dw':
                $this->assign('url', 'download.php');
                $this->display('dw');
                break;
            case 'flash':
                $this->assign('url', 'download.php');
                $this->display('flash');
                break;
            case 'id':
                $this->assign('url', 'download.php');
                $this->display('id');
                break;
            case 'keyshot':
                $this->assign('url', 'download.php');
                $this->display('keyshot');
                break;
            case 'maya':
                $this->assign('url', 'download.php');
                $this->display('maya');
                break;
            case 'pr':
                $this->assign('url', 'download.php');
                $this->display('pr');
                break;
            case 'proe':
                $this->assign('url', 'download.php');
                $this->display('proe');
                break;
            case 'ps':
                $this->assign('url', 'download.php');
                $this->display('ps');
                break;
            case 'rhino':
                $this->assign('url', 'download.php');
                $this->display('rhino');
                break;
            case 'su':
                $this->assign('url', 'download.php');
                $this->display('su');
                break;
        }

    }

    //点击进入快捷键更新浏览量
    public function clickHotKeys(){
        $result = M("CompanyConfig")->where('type=10')->setInc('value');
        if($result){
            $this->apiSuccess('快捷键浏览数更新');
        }
    }

    /**
     * web 快捷键列表
     */
    public function shareHotKeysList(){
        $this->display('sharehotkeyslist');
    }

}