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
        'TEST_PARAM'        => false,   //  ��Ϊ���� ��ת����TEST_PARAM���ò���
    );

    public function run(&$content)
    {
        echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $a = C('TEST_PARAM');
    }
}