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

    public function do_test($get, $post)
    {

//        $value2 = 'World';
//        $cache = new \RedisCache();
//        $value_cache = $cache->getPartResCache('part1');
//        if(!$value_cache){
//            $value1 = 'Hello';
//            $cache->setPartResCache('part1', $value1);
//            $arr['value1'] = $value1;
//        }
//        else{
//            $arr['value1'] = $value_cache;
//        }
//        $arr['value2'] = $value2;
//        $this->apiSuccess('succccccc', null, $arr);
        echo('ssssssssssssssssssssssssssssssss');
    }

    public function _before_do_test(){

    }

    public function hi(){
        echo 'hihihi';
    }

    public function _after_do_test(){
//        echo 'after<br/>';
    }
}