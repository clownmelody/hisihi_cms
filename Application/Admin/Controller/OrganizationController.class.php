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
        $this->organizationModel = D('Admin/Organization');
        $this->organization_relationModel = D('Admin/OrganizationRelation');
        $this->organization_worksModel = D('Admin/OrganizationWorks');
        $this->organization_resourceModel = D('Admin/OrganizationResource');
        $this->organization_commentModel = D('Admin/OrganizationComment');
        $this->organization_configModel = D('Admin/OrganizationConfig');
        parent::_initialize();
    }

    /**
     * 显示公司列表
     */
    public function index(){
        $model = $this->organizationModel;
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于公司名称搜索
        $name = $_GET["title"];
        if($name){
            $map['name'] = array('like','%'.$name.'%');
            $list = $model->where($map)->where("status=1")->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('status=1')->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        }

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","机构列表");
        $this->display();
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
    /**
     * 机构基本信息编辑
     */
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
                    if(!$res){
                        $this->error($model->getError());
                    }else{
                        $id = $res;
                        //上传图片到OSS
                        $picid = $model->where('id='.$id)->getField('logo');
                        if($picid){
                            $this->uploadLogoPicToOSS($picid);
                        }
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/organization/index');
            } else {
                //$model = D('Organization');
                $model = $this->organizationModel;
                $model->updateOrganization($cid, $data);
                //上传图片到OSS
                $picid = $model->where('id='.$cid)->getField('logo');
                if($picid){
                    $this->uploadLogoPicToOSS($picid);
                }
                $this->success('更新成功', 'index.php?s=/admin/organization/index');
            }
        } else {
            $this->display('add');
        }
    }
    /**
     * 机构基本信息删除
     */
    public function delete($id){
        if(!empty($id)){
            $model = $this->organizationModel;
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->updateOrganization($i, $data);
                }
            } else {
                $id = intval($id);
                $model->updateOrganization($id, $data);
            }
            $this->success('删除成功','index.php?s=/admin/organization');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 机构学生老师关系管理
     */
    public function relation(){
        $model = D('OrganizationRelation');
        $count = $model->count();
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
        $model = D('OrganizationWorks');
        $count = $model->count();
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
     * 机构配置列表
     */
    public function config()
    {
        $configvalue = I('configvalue');
        if(!empty($configvalue)){
            $map['value'] = array('like', '%' . (string)$configvalue . '%');
        }
        $model = D('OrganizationConfig');
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where($map)->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
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
        $count = $model->count();
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
     * 机构环境图片列表
     */
    public function environment(){
        $model = D('OrganizationResource');
        $count = $model->where('type=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构环境");
        $this->display();
    }

    /**
     * 机构视频列表
     */
    public function video(){
        $model = D('OrganizationResource');
        $count = $model->where('type=2')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构环境");
        $this->display();
    }

    /**
     * 机构证书
     */
    public function certificate(){
        $name = I('name');
        if(!empty($name)){
            $map['value'] = array('like', '%' . (string)$name . '%');
        }
        $model = D('OrganizationCertificate');
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where($map)->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构环境");
        $this->display();
    }

    /**
     * 新增证书或修改
     */
    public function certificateUpdate(){

    }

    /**
     * 机构公告列表
     */
    public  function notice(){
        $model = D('OrganizationNotice');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于公司名称搜索
        $name = $_GET["title"];
        if($name){
            $map['title'] = array('like','%'.$name.'%');
            $map['status'] = 1;
            $list = $model->where($map)->order('update_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('status=1')->order('update_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","机构公告列表");
        $this->display();
    }

    /**
     * 机构公告新增
     */
    public function notice_add(){
        $this->display();
    }

    /**
     * 机构公告更新
     */
    public  function notice_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationNotice');
            $cid = $_POST["cid"];
            $data["title"] = $_POST["title"];
            $data["content"] = $_POST["content"];
            if(empty($cid)){
                try {
                    $data["create_time"] = time();
                    $res = $model->add($data);
                    if(!$res){
                        $this->error($model->getError());
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/organization/notice');
            } else {
                $data["update_time"] = time();
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                $this->success('更新成功', 'index.php?s=/admin/organization/notice');
            }
        } else {
            $this->display('notice_add');
        }
    }

    /**编辑公告信息
     * @param $id
     */
    public function notice_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('OrganizationNotice');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('info', $data);
        $this->meta_title = '编辑机构公告信息';
        $this->display();
    }

    /**删除公告信息
     * @param $id
     */
    public function notice_delete($id){
        if(!empty($id)){
            $model = M('OrganizationNotice');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('删除成功','index.php?s=/admin/organization/notice');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**上传图片到OSS
     * @param $picID
     */
    private function uploadLogoPicToOSS($picID){
        $model = M();
        $result = $model->query("select path from hisihi_picture where id=".$picID);
        if($result){
            $picLocalPath = $result[0]['path'];
            $picKey = substr($picLocalPath, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $picKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if(!$isExist){
                Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadOtherResource', $param);
            }
        }
    }
}