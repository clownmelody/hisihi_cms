<?php
/**
 * Created by PhpStorm.
 * User: Whispers
 * Date: 2016/4/1
 * Time: 13:58
 */

namespace App\Controller;

require('Application/Common/Lib/RedisCache.class.php');
include('Application/App/Controller/AppController.class.php');

class TestController extends AppController
{
    public function _initialize()
    {
//        \Think\Hook::add('test_begin','Common\\Behavior\\TestBehavior');
//        \Think\Hook::listen('test_begin');
//        tag('cache');
    }

    public function do_test($hhh)
    {
        $cache = new \RedisCache();
        $arr = array('success'=>true, 'error_code'=>0,array('h'=>7));
        $cache->setResCache($this, '获取成功', $arr);
//        $this->apiSuccess($arr);
    }

    public function _before_do_test(){
        $cache = new \RedisCache();
        $cache->getResCache($this);
        return;
    }

    public function hi(){
        echo 'hihihi';
    }

    public function _after_do_test(){
        echo 'after<br/>';
    }
}