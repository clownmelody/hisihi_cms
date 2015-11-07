<?php
/**
 * Created by PhpStorm.
 * User: shaolei
 * Date: 2015/9/15 0015
 * Time: 12:30
 */

namespace Admin\Controller;

use Think\Exception;
use Think\Hook;
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
        $model = M('Organization');
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
        $Model = M('Organization');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $marks = M('OrganizationConfig')->where(array('status'=>1,'type'=>2))->field('id,value')->select();
        $markarray = explode("#",$data['advantage']);
        $this->assign('_markarray', $markarray);
        $this->assign('_marks', $marks);
        $this->assign('organization', $data);
        $this->meta_title = '编辑机构信息';
        $this->display();
    }

    /**
     * 机构基本信息更新
     */
    public function update(){
        if (IS_POST) { //提交表单
            $model = M('Organization');
            $cid = $_POST["id"];
            $data["name"] = $_POST["name"];
            $data["slogan"] = $_POST["slogan"];
            $data["location"] = $_POST["location"];
//            $data["latitude"] = $_POST["latitude"];
//            $data["longitude"] = $_POST["longitude"];
            $data["phone_num"] = $_POST["phone_num"];
            //$data["advantage"] = implode("#",$_POST["advantage"]);
            $data["advantage"] = $_POST["advantage"];
            $data["introduce"] = $_POST["introduce"];
//            $data["certification"] = $_POST["certification"];
            $data["logo"] = $_POST["picture"];
            if(empty($cid)){
                try {
                    $data["create_time"] = time();
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
                    M('OrganizationRelation')->where(array('organization_id'=>$i))->save($data);
                    M('OrganizationCourse')->where(array('organization_id'=>$i))->save($data);
                    $course = M('OrganizationCourse')->where(array('organization_id'=>$id))->field('id')->select();
                    if($course){
                        $course_array = array();
                        foreach($course as $courseid){
                            $course_array[] = $courseid['id'];
                        }
                        M('OrganizationVideo')->where(array('course_id'=>array('in',$course_array)))->save($data);
                    }
                    M('OrganizationResource')->where(array('organization_id'=>$i))->save($data);
                    M('OrganizationComment')->where(array('organization_id'=>$i))->save($data);
                    M('OrganizationCertification')->where(array('organization_id'=>$i))->save($data);
                }
            } else {
                $id = intval($id);
                $model->updateOrganization($id, $data);
                M('OrganizationRelation')->where(array('organization_id'=>$id))->save($data);
                M('OrganizationCourse')->where(array('organization_id'=>$id))->save($data);
                $course = M('OrganizationCourse')->where(array('organization_id'=>$id))->field('id')->select();
                if($course){
                    $course_array = array();
                    foreach($course as $courseid){
                        $course_array[] = $courseid['id'];
                    }
                    M('OrganizationVideo')->where(array('course_id'=>array('in',$course_array)))->save($data);
                }
                M('OrganizationResource')->where(array('organization_id'=>$id))->save($data);
                M('OrganizationComment')->where(array('organization_id'=>$id))->save($data);
                M('OrganizationCertification')->where(array('organization_id'=>$id))->save($data);
            }
            $this->success('删除成功','index.php?s=/admin/organization');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 机构老师关系列表
     */
    public function relation($organization_id=0){
        $model = M('OrganizationRelation');
        $filter_map['status'] = 1;
        //从机构列表跳转
        if($organization_id){
            $filter_map['organization_id'] = $organization_id;
            $organization_name = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->getField('name');
        }
        $count = $model->where($filter_map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于用户名称搜索
        $name = $_GET["title"];
        if($name){
            $map['nickname'] = array('like','%'.$name.'%');
            $map['status']=1;
            $uid = M('Member')->where($map)->field('uid')->select();
            $userid = array();
            foreach($uid as $user_id){
                $userid[] = $user_id['uid'];
            }
            $filter_map['uid'] = array('in',$userid);
            $list = $model->where($filter_map)->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($filter_map)->limit($Page->firstRow.','.$Page->listRows)->select();
        }

        foreach($list as &$relation){
            $relation['user_name'] = M('Member')->where(array('uid'=>$relation['uid'],'status'=>1))->getField('nickname');
            if($organization_id){
                $relation['organization'] = $organization_name;
            }else{
                $relation['organization'] = M('Organization')->where(array('id'=>$relation['organization_id'],'status'=>1))->getField('name');
            }
            $relation['group_name'] = M('OrganizationConfig')->where(array('id'=>$relation['teacher_group_id'],'status'=>1,'type'=>1001))
                ->getField('value');
        }
        if($organization_id){
            $this->assign('organization_id', $organization_id);
            $this->assign('organization_name', $organization_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构老师");
        $this->display();
    }

    /**
     * 机构师生关系更新
     */
    public  function relation_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationRelation');
            $cid = $_POST["cid"];
            $data["uid"] = $_POST["uid"];
            $data["teacher_group_id"] = $_POST["teacher_group_id"];
            $data['organization_id'] = $_POST["organization_id"];
            $data['group'] = $_POST["group"];
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
                $this->success('添加成功', 'index.php?s=/admin/organization/relation');
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                $this->success('更新成功', 'index.php?s=/admin/organization/relation');
            }
        } else {
            $this->display('relation_add');
        }
    }

    /**
     * 编辑机构师生关系
     * @param $id
     */
    public function relation_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('OrganizationRelation');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $data['user_name'] = M('Member')->where(array('uid'=>$data['uid'],'status'=>1))->getField('nickname');
        $data['organization'] = M('Organization')->where(array('id'=>$data['organization_id'],'status'=>1))->getField('name');
        $teacher_group = M('OrganizationConfig')->where(array('organization_id'=>$data['organization_id'],'status'=>1,'type'=>1001))
            ->field('id,value')->select();
        $this->assign('info', $data);
        $this->assign('teacher_group',$teacher_group);
        $this->display();
    }

    /**
     * 删除机构师生关系
     * @param $id
     */
    public function relation_delete($id){
        if(!empty($id)){
            $model = M('OrganizationRelation');
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
            $this->success('删除成功','index.php?s=/admin/organization/relation');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 学生作品列表
     */
    public function works($organization_id=0){
        $model = D('OrganizationResource');
        $map['type']=2;
        $map['status']=1;
        if($organization_id){
            $map['organization_id'] = $organization_id;
            $organization_name = M('Organization')->where('status=1 and id='.$organization_id)->getField("name");
        }
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于图片描述搜索
        $name = $_GET["title"];
        if($name){
            $map['description'] = array('like','%'.$name.'%');
            $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$environment){
            if($organization_id){
                $environment['organization_name'] = $organization_name;
            }else{
                $environment['organization_name'] = M('Organization')->where('status=1 and id='.$environment['organization_id'])->getField("name");
            }
        }
        if($organization_id){
            $this->assign('organization_id', $organization_id);
            $this->assign('organization_name', $organization_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构环境");
        $this->display();
    }

    /**
     * 学生作品新增
     */
    public function works_add(){
        $this->display();
    }

    /**
     * 学生作品更新
     */
    public  function works_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationResource');
            $cid = $_POST["cid"];
            $data["pic_id"] = $_POST["picture"];
            $data["description"] = $_POST["description"];
            $data['organization_id'] = $_POST["organization_id"];
            $data['type'] = 2;
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
                $this->uploadLogoPicToOSS($data["pic_id"]);
                getThumbImageById($data["pic_id"],280,160);
                $this->success('添加成功', 'index.php?s=/admin/organization/works');
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                $this->uploadLogoPicToOSS($data["pic_id"]);
                getThumbImageById($data["pic_id"],280,160);
                $this->success('更新成功', 'index.php?s=/admin/organization/works');
            }
        } else {
            $this->display('works_add');
        }
    }

    /**
     * 编辑学生作品
     * @param $id
     */
    public function works_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('OrganizationResource');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('info', $data);
        $this->display();
    }

    /**
     * 删除学生作品
     * @param $id
     */
    public function works_delete($id){
        if(!empty($id)){
            $model = M('OrganizationResource');
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
            $this->success('删除成功','index.php?s=/admin/organization/works');
        } else {
            $this->error('未选择要删除的数据');
        }
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

    /**
     * 机构配置编辑
     * @param $id
     */
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
    public function comment($organization_id=0)
    {
        $model = D('OrganizationComment');
        $filter['status'] = 1;
        if($organization_id){
            $filter['organization_id'] = $organization_id;
            $organization_name = M('Organization')->where('status=1 and id='.$organization_id)->getField("name");
        }
        $count = $model->where($filter)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于用户名搜索
        $name = $_GET["title"];
        if($name){
            $map['nickname'] = array('like','%'.$name.'%');
            $map['status']=1;
            $users = M('Member')->where($map)->field('uid')->select();
            $uid_array = array();
            foreach($users as &$user){
                $uid_array[] = $user['uid'];
            }
            $filter['uid'] = array('in',$uid_array);
            $list = $model->where($filter)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($filter)->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$comment){
            if($organization_id){
                $comment['organization_name'] = $organization_name;
            }else{
                $comment['organization_name'] = M('Organization')->where('status=1 and id='.$comment['organization_id'])->getField("name");
            }
            $comment['user_name'] = M('Member')->where(array('uid'=>$comment['uid'],'status'=>1))->getField('nickname');
        }

        if($organization_id){
            $this->assign('organization_id', $organization_id);
            $this->assign('organization_name', $organization_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构评论");
        $this->display();
    }

    /**
     * 机构配置编辑
     * @param $id
     */
    public function comment_edit($id){
        $comment = D('OrganizationComment');
        $info = $comment->where('status=1 and id='.$id)->find();
        $info['organization_name'] = M('Organization')->where(array('id'=>$info['organization_id'],'status'=>1))->getField('name');
        $info['user_name'] = M('Member')->where(array('uid'=>$info['uid'],'status'=>1))->getField('nickname');
        $this->assign('comment', $info);
        $this->meta_title = '编辑机构配置';
        $this->display();
    }

    /**
     * 机构配置添加和修改
     */
    public function comment_update($id=0){
        if(IS_POST){
            $Config = D('OrganizationComment');
            $data = $Config->create();
            if(empty($id)){
                if($data){
                    if($Config->add()){
                        $this->success('新增成功', U('comment'));
                    } else {
                        $this->error('新增失败');
                    }
                } else {
                    $this->error($Config->getError());
                }
            } else {
                $result = $Config->where('id='.$id)->save($data);
                if($result){
                    $this->success('编辑成功', U('comment'));
                } else {
                    $this->error('编辑失败');
                }
            }
        } else {
            $this->meta_title = '新增配置';
            $this->assign('info',null);
            $this->display('comment_add');
        }
    }

    /**
     * 机构评论删除
     *
     */
    public function comment_delete(){
        $id = array_unique((array)I('id',0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $config = D('OrganizationComment');
        $map = array('id' => array('in', $id) );
        $result = $config->where($map)->save(Array('status'=>-1));
        if($result){
            $this->success('删除成功', U('comment'));
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 机构环境图片列表
     */
    public function environment($organization_id=0){
        $model = D('OrganizationResource');
        $map['status'] = 1;
        $map['type'] = 1;
        if($organization_id){
            $map['organization_id'] = $organization_id;
            $organization_name = M('Organization')->where('status=1 and id='.$organization_id)->getField("name");
        }
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于图片描述搜索
        $name = $_GET["title"];
        if($name){
            $map['description'] = array('like','%'.$name.'%');
            $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$environment){
            if($organization_id){
                $environment['organization_name'] = $organization_name;
            }else{
                $environment['organization_name'] = M('Organization')->where('status=1 and id='.$environment['organization_id'])->getField("name");
            }
        }

        if($organization_id){
            $this->assign('organization_id', $organization_id);
            $this->assign('organization_name', $organization_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构环境");
        $this->display();
    }

    /**
     * 环境图片新增
     */
    public function environment_add(){
        $this->display();
    }

    /**
     * 环境图片更新
     */
    public  function environment_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationResource');
            $cid = $_POST["cid"];
            $data["pic_id"] = $_POST["picture"];
            $data["description"] = $_POST["description"];
            $data['organization_id'] = $_POST["organization_id"];
            $data['type'] = 1;
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
                $this->uploadLogoPicToOSS($data["pic_id"]);
                getThumbImageById($data["pic_id"],280,160);
                $this->success('添加成功', 'index.php?s=/admin/organization/environment');
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                $this->uploadLogoPicToOSS($data["pic_id"]);
                getThumbImageById($data["pic_id"],280,160);
                $this->success('更新成功', 'index.php?s=/admin/organization/environment');
            }
        } else {
            $this->display('environment_add');
        }
    }

    /**
     * 编辑环境图片
     * @param $id
     */
    public function environment_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('OrganizationResource');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('info', $data);
        $this->meta_title = '编辑机构环境图片信息';
        $this->display();
    }

    /**
     * 删除环境图片
     * @param $id
     */
    public function environment_delete($id){
        if(!empty($id)){
            $model = M('OrganizationResource');
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
            $this->success('删除成功','index.php?s=/admin/organization/environment');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 机构视频列表
     */
    public function video($id){
        $model = M('OrganizationVideo');
        $map['status'] = 1;
        $map['course_id'] = $id;
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as &$video){
            $video['course_name'] = M('OrganizationCourse')->where(array('id'=>$video['course_id'],'status'=>1))->getField('title');
            $video['duration'] = time_format($video['duration'],'m分s秒');
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构课程视频");
        $this->display();
    }

    /**
     * 课程视频更新
     */
    public  function video_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationVideo');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data['course_id'] = $_POST["course_id"];
            $data['url'] = $_POST["url"];
            $data['duration'] = $_POST["duration"];
            $data['update_time'] = time();
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
                $this->success('添加成功', 'index.php?s=/admin/organization/video');
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                $this->success('更新成功', 'index.php?s=/admin/organization/video/id/'.$cid);
            }
        } else {
            $this->display('video_add');
        }
    }

    /**
     * 编辑课程视频
     * @param $id
     */
    public function video_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('OrganizationVideo');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $data['course_name'] = M('OrganizationCourse')->where(array('status'=>1,'id'=>$data['course_id']))->getField('title');
        $data['duration'] = time_format($data['duration'],'m分s秒');
        $this->assign('info', $data);
        $this->meta_title = '编辑机构课程视频';
        $this->display();
    }

    /**
     * 删除视频
     * @param $id
     */
    public function video_delete($id){
        if(!empty($id)){
            $model = M('OrganizationVideo');
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
            $this->success('删除成功','index.php?s=/admin/organization/video');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 机构课程列表
     */
    public function course($organization_id=0){
        $model = D('OrganizationCourse');
        $map['status']=1;
        if($organization_id){
            $map['organization_id'] = $organization_id;
            $organization_name = M('Organization')->where('status=1 and id='.$organization_id)->getField("name");
        }
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于课程名称搜索
        $name = $_GET["title"];
        if($name){
            $map['title'] = array('like','%'.$name.'%');
            $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$course){
            if($organization_id){
                $course['organization_name'] = $organization_name;
            }else{
                $course['organization_name'] = M('Organization')->where('status=1 and id='.$course['organization_id'])->getField("name");
            }
            $course['category'] = M('OrganizationConfig')->where(array('id'=>$course['category_id'],'type'=>1002,'status'=>1))->getField('value');
            $course['teacher'] = M('Member')->where(array('uid'=>$course['lecturer']))->getField('nickname');
        }

        if($organization_id){
            $this->assign('organization_id', $organization_id);
            $this->assign('organization_name', $organization_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构视频课程");
        $this->display();
    }

    /**
     * 机构课程更新
     */
    public  function course_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationCourse');
            $cid = $_POST["cid"];
            $data["title"] = $_POST["title"];
            $data["content"] = $_POST["content"];
            $data['organization_id'] = $_POST["organization_id"];
            $data['img'] = $_POST["picture"];
            $data['category_id'] = $_POST["category_id"];
            $data['lecturer'] = $_POST["lecturer"];
            $data['auth'] = $_POST["auth"];
            $data['update_time'] = time();
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
                $this->uploadLogoPicToOSS($data['img']);
                $this->success('添加成功', 'index.php?s=/admin/organization/course');
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                $this->uploadLogoPicToOSS($data['img']);
                $this->success('更新成功', 'index.php?s=/admin/organization/course');
            }
        } else {
            $this->display('course_add');
        }
    }

    /**编辑机构课程
     * @param $id
     */
    public function course_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('OrganizationCourse');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $data['organization'] = M('Organization')->where(array('id'=>$data['organization_id'],'status'=>1))->getField('name');
        $data['teacher'] = M('Member')->where(array('uid'=>$data['lecturer'],'status'=>1))->getField('nickname');
        $category = M('OrganizationConfig')->where(array('status'=>1,'type'=>1002))->field('id,value')->select();
        $this->assign('info', $data);
        $this->assign('category', $category);
        $this->meta_title = '编辑机构视频课程';
        $this->display();
    }

    /**删除机构课程
     * @param $id
     */
    public function course_delete($id){
        if(!empty($id)){
            $model = M('OrganizationCourse');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                    M('OrganizationVideo')->where(array('course_id'=>$i))->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
                M('OrganizationVideo')->where(array('course_id'=>$id))->save($data);
            }
            $this->success('删除成功','index.php?s=/admin/organization/course');
        } else {
            $this->error('未选择要删除的数据');
        }
    }


    /**
     * 机构证书
     */
    public function certificate(){
        $name = I('name');
        if(!empty($name)){
            $map['name'] = array('like', '%' . (string)$name . '%');
        }
        $status = I('status');
        if(!empty($status)){
            $map['status'] = $status;
        }else{
            $map['status'] = array('neq',-1);
        }
        $model = D('OrganizationCertificate');
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构认证");
        $this->display();
    }

    /**
     * 认证通过
     */
    public function certificate_pass($id){
        if(!empty($id)){
            $model = M('OrganizationCertificate');
            $data['status'] = 2;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                    $organization_id = $model->where('id='.$i)->getField('organization_id');
                    M('Organization')->where(array('id'=>$organization_id))->save(array('identification'=>2));
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
                $organization_id = $model->where('id='.$id)->getField('organization_id');
                M('Organization')->where(array('id'=>$organization_id))->save(array('identification'=>2));
            }
            $this->success('认证成功','index.php?s=/admin/organization/certificate');
        } else {
            $this->error('未选择要认证的数据');
        }
    }

    /**
     * 认证拒绝
     */
    public function certificate_refuse($id){
        if(!empty($id)){
            $model = M('OrganizationCertificate');
            $data['status'] = -2;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                    $organization_id = $model->where('id='.$i)->getField('organization_id');
                    M('Organization')->where(array('id'=>$organization_id))->save(array('identification'=>-2));
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
                $organization_id = $model->where('id='.$id)->getField('organization_id');
                M('Organization')->where(array('id'=>$organization_id))->save(array('identification'=>-2));
            }
            $this->success('拒绝认证成功','index.php?s=/admin/organization/certificate');
        } else {
            $this->error('未选择要拒绝认证的数据');
        }
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
        $this->assign("total", $count);
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
            $data["update_time"] = time();
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
            \Think\Log::write($isExist);
        }
    }
}