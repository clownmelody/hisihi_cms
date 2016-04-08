<?php
/**
 * Created by PhpStorm.
 * User: Whispers
 * Date: 2016/4/1
 * Time: 12:23
 */

namespace Common\Behavior;


use Think\Behavior;

class TestBehavior extends Behavior
{
    protected $options   =  array(
        'TEST_PARAM'        => false,   //  行为参数 会转换成TEST_PARAM配置参数
    );

    public function run(&$content)
    {
        echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $a = C('TEST_PARAM');
    }
}