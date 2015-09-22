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
use Think\Page;

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
            ->setStatusUrl(U('setOrganizationStatus'))->buttonNew(U('add'))->buttonDelete()
            ->keyId()->keyLink('name', '机构名称','edit?id=###')->keyText('type','机构类型')->keyText('form','形式')
            ->keyText('period','周期')->keyText('location','地址')->keyMap('identification','是否认证',array(0 => '未认证',1 => '已认证'))
            ->keyCreateTime()
            ->keyMap('status','状态',array(1 => '正常', 0 => '未激活'))
            ->keyDoActionEdit( 'edit?id=###','编辑')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    /**设置机构信息的状态
     * @param $ids
     * @param $status
     */
    public function setOrganizationStatus($ids,$status){
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
        $builder->doSetStatus('Organization', $ids, $status);
    }

    /**
     * 机构基本信息增加
     */
    public function add(){
        $model = $this->organization_configModel;
        $list = $model->where("type=2")->order("create_time")->select();
        $this->assign('_list', $list);
        $this->display();
    }

    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        //$Model = M('Company');
        $Model = $this->organizationModel;
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('organization', $data);
        $this->meta_title = '编辑机构信息';
        $this->display();
    }

    /**
     * 机构基本信息更新
     */
    public function update(){
        if (IS_POST) { //提交表单
            //$model = M('Organization');
            $model = $this->organizationModel;
            $cid = $_POST["id"];
            $data["name"] = $_POST["name"];
            $data["slogan"] = $_POST["slogan"];
            $data["location"] = $_POST["location"];
            $data["latitude"] = $_POST["latitude"];
            $data["longitude"] = $_POST["longitude"];
            $data["phone_num"] = $_POST["phone_num"];
            //$data["advantage"] = implode("#",$_POST["advantage"]);
            $data["advantage"] = $_POST["advantage"];
            $data["introduce"] = $_POST["introduce"];
            $data["certification"] = $_POST["certification"];
            $data["logo"] = $_POST["picture"];
            $data["create_time"] = time();
            if(empty($cid)){
                try {
                    $res = $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                //$this->success('添加成功', Cookie('__forward__'));
                $this->success('添加成功', 'index.php?s=/admin/organization/organizationinfo');
            } else {
                //$model = D('Organization');
                $model = $this->organizationModel;
                $model->updateOrganization($cid, $data);
                //$this->success('更新成功', Cookie('__forward__'));
                $this->success('更新成功', 'index.php?s=/admin/organization/organizationinfo');
            }
        } else {
            $this->display('add');
        }
    }

    public function delete($id){
        if(!empty($id)){
            $model = D('Company');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->updateCompany($i, $data);
                }
            } else {
                $id = intval($id);
                $model->updateCompany($id, $data);
            }
            $this->success('删除成功','index.php?s=/admin/company');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 机构学生老师关系管理
     */
    public function relation(){
        $model = D('OrganizationRelation');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构评论");
        $this->display();
    }

    /**
     * 学生作品管理
     */
    public function works(){
        $this->display();
    }

    /**
     * 机构配置列表
     */
    public function config()
    {
        $model = D('OrganizationConfig');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构配置");
        $this->display();
    }


    public function config_edit($id){
        $config = D('OrganizationConfig');
        $info = $config->where('status=1 and id='.$id)->find();
        $this->assign('config', $info);
        $this->meta_title = '编辑机构配置';
        $this->display();
    }

    /**
     * 机构配置添加和修改
     */
    public function configUpdate($id=0){
        if(IS_POST){
            $Config = D('OrganizationConfig');
            $data = $Config->create();
            if(empty($id)){
                if($data){
                    if($Config->add()){
                        $this->success('新增成功', U('config'));
                    } else {
                        $this->error('新增失败');
                    }
                } else {
                    $this->error($Config->getError());
                }
            } else {
                $result = $Config->where('id='.$id)->save($data);
                if($result){
                    $this->success('编辑成功', U('config'));
                } else {
                    $this->error('编辑失败');
                }
            }
        } else {
            $this->meta_title = '新增配置';
            $this->assign('info',null);
            $this->display('config_add');
        }
    }

    /**
     * 机构配置删除
     * @param $ids
     */
    public function config_delete(){
        $id = array_unique((array)I('id',0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $config = D('OrganizationConfig');
        $map = array('id' => array('in', $id) );
        $result = $config->where($map)->save(Array('status'=>-1));
        if($result){
            $this->success('删除成功', U('config'));
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 机构配置信息从删除中恢复
     */
    public function config_restore(){
        $id = array_unique((array)I('id',0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $config = D('OrganizationConfig');
        $map = array('id' => array('in', $id) );
        $result = $config->where($map)->save(Array('status'=>1));
        if($result){
            $this->success('启用成功', U('config'));
        } else {
            $this->error('启用失败');
        }
    }

    /**
     * 机构评论列表
     */
    public function comment()
    {
        $model = D('OrganizationComment');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构评论");
        $this->display();
    }

}