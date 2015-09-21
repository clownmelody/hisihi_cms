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
    protected $organization_relationModel;
    protected $organization_worksModel;
    protected $organization_resourceModel;
    protected $organization_commentModel;
    protected $organization_configModel;

    function _initialize()
    {
        $this->organizationModel = D('Organization/Organization');
        $this->organization_relationModel = D('Organization/OrganizationRelation');
        $this->organization_worksModel = D('Organization/OrganizationWorks');
        $this->organization_resourceModel = D('Organization/OrganizationResource');
        $this->organization_commentModel = D('Organization/OrganizationComment');
        $this->organization_configModel = D('Organization/OrganizationConfig');
        parent::_initialize();
    }

    /**查看机构信息
     * @param int $page
     * @param int $r
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
            ->setStatusUrl(U('setOrganizationIdentification'))->buttonDisable('','审核不通过')->buttonDelete()->buttonSetStatus(U('setIssueContentStatus'),2,'推荐',array())->buttonNew(U('add'))
            ->keyId()->keyLink('name', '机构名称','editContents?id=###')->keyText('type','机构类型')->keyText('form','形式')
            ->keyText('period','周期')->keyText('location','地址')->keyMap('identification','是否认证',array(0 => '未认证',1 => '已认证'))
            ->keyCreateTime()
            ->keyMap('status','状态',array(1 => '正常', 0 => '未激活'))
            ->keyDoActionEdit( 'Issue/editcontents?id=###','编辑')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function setOrganizationIdentification($ids,$status){
        $builder = new AdminListBuilder();
        if($status==1){
            foreach($ids as $id){
                $content=D('Organization')->find($id);
                D('Common/Message')->sendMessage($content['uid'],"管理员审核通过了您发布的内容。现在可以在列表看到该内容了。" , $title = '机构认证通知', U('Issue/Index/issueContentDetail',array('id'=>$id)), is_login(), 2);
                /*同步微博*/
                $user = query_user(array('nickname', 'space_link'), $content['uid']);
                $weibo_content = '管理员审核通过了@' . $user['nickname'] . ' 的内容：【' . $content['title'] . '】，快去看看吧：' ."http://$_SERVER[HTTP_HOST]" .U('Issue/Index/issueContentDetail',array('id'=>$content['id']));
                $model = D('Weibo/Weibo');
                $model->addWeibo(is_login(), $weibo_content);
                /*同步微博end*/
            }
        }
        $builder->doSetStatus('IssueContent', $ids, $status);
    }

    /**
     * 机构基本信息增加
     */
    public function add(){
        $this->display();
    }

    /**
     * 机构学生老师关系管理
     */
    public function relation($page=1,$r=10){
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
            ->setStatusUrl(U('setOrganizationIdentification'))->buttonDisable('','审核不通过')->buttonDelete()->buttonSetStatus(U('setIssueContentStatus'),2,'推荐',array())->buttonNew(U('add'))
            ->keyId()->keyLink('name', '机构名称','editContents?id=###')->keyText('type','机构类型')->keyText('form','形式')
            ->keyText('period','周期')->keyText('location','地址')->keyMap('identification','是否认证',array(0 => '未认证',1 => '已认证'))
            ->keyCreateTime()
            ->keyMap('status','状态',array(1 => '正常', 0 => '未激活'))
            ->keyDoActionEdit( 'Issue/editcontents?id=###','编辑')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    /**
     * 学生作品管理
     */
    public function works(){
        $this->display();
    }


}