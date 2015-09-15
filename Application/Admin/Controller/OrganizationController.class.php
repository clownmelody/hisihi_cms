<?php
/**
 * Created by PhpStorm.
 * User: shaolei
 * Date: 2015/9/15 0015
 * Time: 12:30
 */

namespace Admin\Controller;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;

use Think\Model;

class OrganizationController extends AdminController
{
    protected $organizationModel;
    protected $organization_typeModel;
    protected $organization_formModel;
    protected $organization_periodModel;

    function _initialize()
    {
        $this->organizationModel = D('Organization/Organization');
        $this->organization_typeModel = D('Organization/OrganizationType');
        $this->organization_formModel = D('Organization/OrganizationForm');
        $this->organization_periodModel = D('Organization/OrganizationPeriod');
        parent::_initialize();
    }

    /**商品分类
     *
     */
    public function organizationInfo($page=1,$r=10)
    {
        //读取列表
        $map['status'] = array('egt', 0);
        $list = $this->organizationModel->where($map)->order('id desc')->page($page, $r)->select();
        unset($li);
        $totalCount = $this->organizationModel->where($map)->count();
        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';


        $builder->title('机构信息管理')
            ->setStatusUrl(U('setIssueContentStatus'))->buttonDisable('','审核不通过')->buttonDelete()->buttonSetStatus(U('setIssueContentStatus'),2,'推荐',array())->buttonNew(U('editContents'))->button('课程同步',array('href'=>U('SyncCourse')))
            ->keyId()->keyLink('name', '机构名称','editContents?id=###')->keyText('type','机构类型')->keyText('form','形式')
            ->keyText('period','周期')->keyText('location','地址')->keyMap('identification','是否认证',array(0 => '未认证',1 => '已认证'))
            ->keyCreateTime()
            ->keyMap('status','状态',array(1 => '正常', 0 => '未激活'))
            ->keyDoActionEdit( 'Issue/editcontents?id=###','编辑')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }
}