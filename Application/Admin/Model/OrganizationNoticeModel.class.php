<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;


class OrganizationNoticeModel extends Model{

    protected $_validate = array(
        array('title', 'require', '标题不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('content', 'require', '内容不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_BOTH),
    );


}
