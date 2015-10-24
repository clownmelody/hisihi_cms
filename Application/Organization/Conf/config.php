<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.thinkphp.cn>
// +----------------------------------------------------------------------

/**
 * 前台配置文件
 * 所有除开系统级别的前台配置
 */

return array(
    'TMPL_PARSE_STRING' => array(
        '__IMG__'    => __ROOT__ . '/Application/Organization/Content/images',
        '__CSS__'    => __ROOT__ . '/Application/Organization/Content/css',
        '__JS__'     => __ROOT__ . '/Application/Organization/Javascript',
        '__URL__'    =>'/hisihi-cms/weborg.php',

    ),

    'LAYOUT_ON'=>true,
    'LAYOUT_NAME'=>'layout',
);