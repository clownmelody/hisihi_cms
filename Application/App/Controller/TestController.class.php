<?php
/**
 * Created by PhpStorm.
 * User: Whispers
 * Date: 2016/4/1
 * Time: 13:58
 */

namespace App\Controller;

require('Application/Common/Lib/RedisCache.class.php');

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
        $cache->setResCache('获取成功',$arr, 600);
        $this->apiSuccess($arr);
    }

    public function _before_do_test(){
//        $c = $_SERVER['HTTP_HOST'];
//        $x = $_SERVER['PHP_SELF'];
//        $f = $_SERVER['QUERY_STRING'];

//        $m = md5($q);
//        print($c.'<br>'.$x.'<br>'.$f.'<br>'.$q.'<br>'.$m.'<br>');
        $cache = new \RedisCache();
        $value = $cache->getResCache();
        if(!$value){
            return;
        }
        else{
            $msg = $value->msg;
            $arr = $value->content;
            $this->apiSuccess($msg, null, $arr);
        }
//        $this->apiSuccess($hhh);
    }

    public function _after_do_test(){
        echo 'after<br/>';
    }
}