<?php
/**
 * Created by PhpStorm.
 * User: shaolei
 * Date: 2015/9/15 0015
 * Time: 15:00
 */

namespace Admin\Model;
use Think\Model;
use Think\Page;

class OrganizationModel extends Model
{
    /* 自动验证规则 */
    protected $_validate = array(
        array('name', 'require', '机构名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('picture', 'require', '图片不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * 获取机构详细信息
     * @param $id
     * @param bool $field
     * @return mixed
     *
     */
    public function info($id, $field = true)
    {
        /* 获取分类信息 */
        $map = array();
        if (is_numeric($id)) { //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['name'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }

    /* 自动完成规则 */
    protected $_auto = array(
        array('create_time', 'getCreateTime', Model:: MODEL_INSERT, 'callback'),
    );

    public function delete($id)
    {
        return $this->where('id=' . $id)->delete();
    }

//    public function add($data)
//    {
//        return $this->add($data);
//    }

    public function updateOrganization($id, $data)
    {
        return $this->where('id=' . $id)->save($data);
    }

    protected function getCreateTime()
    {
        return time();
    }
}