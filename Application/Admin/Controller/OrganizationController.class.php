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
        $Page = new Page($count, 5);
        $show = $Page->show();
        //用于公司名称搜索
        $name = $_GET["title"];
        if($name){
            $map['name'] = array('like','%'.$name.'%');
            $list = $model->where($map)->where("status=1")->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
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
        $list = $model->where("type=2 and status=1")->order("create_time")->select();
        $type = $model->where("type=3 and status=1")->order("create_time")->select();
        $this->assign('_list', $list);
        $this->assign('_type', $type);
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
        $type_array = M('OrganizationConfig')->where(array('status'=>1,'type'=>3))->field('id,value')->select();
        $_type_array = explode("#",$data['type']);

        $marks = M('OrganizationConfig')->where(array('status'=>1,'type'=>2))->field('id,value')->select();
        //解析json格式的标签
        $advantage = $data['advantage'];
        $advantage = stripslashes($advantage);
        $advantage = json_decode($advantage,true);
        $advantage_array = array();
        $cmodel = M('OrganizationConfig');
        foreach($advantage as &$markid){
            $advantageid = $markid['id'];
            if(0 == $advantageid){
                $markobj = array(
                    'id'=>(string)$markid['id'],
                    'value'=>$markid['value'],
                    'ischecked'=>1
                );
                $advantage_array[] = $markobj;
            }else{
                $markarr = $cmodel->field('id,value')->where('type=2 and status=1 and id='.$advantageid)->find();
                if($markarr){
                    $markobj = array(
                        'id'=>$markarr['id'],
                        'value'=>$markarr['value'],
                        'ischecked'=>1
                    );
                    $advantage_array[] = $markobj;
                }
            }
        }
        //组合公用标签和自定义标签，用ischecked判断
        $all_marks = $advantage_array;
        foreach($marks as $mark){
            $is_exist = false;
            foreach($advantage_array as $advantage_mark){
                if($mark['id'] == $advantage_mark['id']){
                    $is_exist = true;
                }
            }
            if(!$is_exist){
                $mark['ischecked'] = 0;
                $all_marks[] = $mark;
            }
        }

        $this->assign('_type', $type_array);
        $this->assign('type_array', $_type_array);
        $this->assign('_marks', $all_marks);
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
            $data["city"] = $_POST["city"];
//            $data["latitude"] = $_POST["latitude"];
//            $data["longitude"] = $_POST["longitude"];
            $data["phone_num"] = $_POST["phone_num"];
            $data["type"] = $_POST["type"];
            $data["advantage"] = $_POST["advantage"];
            $data["introduce"] = $_POST["introduce"];
            $data["guarantee_num"] = $_POST["guarantee_num"];
            $data["view_count"] = $_POST["view_count"];
            $data["logo"] = $_POST["picture"];
            $data["video_img"] = $_POST["video_img"];
            $data["video"] = $_POST["video"];
            if(empty($cid)){
                try {
                    $data["create_time"] = time();
                    $res = $model->add($data);
                    if(!$res){
                        $this->error($model->getError());
                    }else{
                        $id = $res;
                        //添加到机构创建申请
                        $application = array(
                            'organization_id'=>$id,
                            'name'=>$data['name'],
                            'create_time'=>time(),
                            'update_time'=>time()
                        );
                        M('OrganizationApplication')->add($application);

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
                    M('OrganizationConfig')->where(array('organization_id'=>$id))->save($data);
                    M('OrganizationNotice')->where(array('organization_id'=>$id))->save($data);
                    M('OrganizationApplication')->where(array('organization_id'=>$id))->save($data);
                    M('OrganizationAuthentication')->where(array('organization_id'=>$id))->save($data);
                    M('OrganizationEnroll')->where(array('organization_id'=>$id))->save($data);
                    M('OrganizationCommentStar')->where(array('organization_id'=>$id))->save($data);
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
                M('OrganizationConfig')->where(array('organization_id'=>$id))->save($data);
                M('OrganizationNotice')->where(array('organization_id'=>$id))->save($data);
                M('OrganizationApplication')->where(array('organization_id'=>$id))->save($data);
                M('OrganizationAuthentication')->where(array('organization_id'=>$id))->save($data);
                M('OrganizationEnroll')->where(array('organization_id'=>$id))->save($data);
                M('OrganizationCommentStar')->where(array('organization_id'=>$id))->save($data);
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
            $list = $model->where($filter_map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($filter_map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }

        foreach($list as &$relation){
            $relation['user_name'] = M('Member')->where(array('uid'=>$relation['uid'],'status'=>1))->getField('nickname');
            if($organization_id){
                $relation['organization'] = $organization_name;
            }else{
                $relation['organization'] = M('Organization')->where(array('id'=>$relation['organization_id'],'status'=>1))->getField('name');
            }
            /*$relation['group_name'] = M('OrganizationConfig')->where(array('id'=>$relation['teacher_group_id'],'status'=>1,'type'=>1001))
                ->getField('value');*/
            $relation['group_name'] = M('OrganizationLectureGroup')->where(array('id'=>$relation['teacher_group_id'],'status'=>1))
                ->getField('title');
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
                if($_POST['from_org']){
                    $this->success('添加成功', 'index.php?s=/admin/organization/relation&organization_id='.$data['organization_id']);
                }else{
                    $this->success('添加成功', 'index.php?s=/admin/organization/relation');
                }
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                if($_POST['from_org']){
                    $this->success('更新成功', 'index.php?s=/admin/organization/relation&organization_id='.$data['organization_id']);
                }else{
                    $this->success('更新成功', 'index.php?s=/admin/organization/relation');
                }
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
        /*$teacher_group = M('OrganizationConfig')->where(array('organization_id'=>$data['organization_id'],'status'=>1,'type'=>1001))
            ->field('id,value')->select();*/
        $teacher_group = M('OrganizationLectureGroup')->where(array('organization_id'=>$data['organization_id'],'status'=>1))
            ->field('id, title')->select();
        if($_GET['from_org']){//判断是否从机构跳转过来
            $this->assign('from_org', $_GET['from_org']);
        }
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
            if(I('from_org')){
                $this->success('删除成功', 'index.php?s=/admin/organization/relation&organization_id='.I('organization_id'));
            }else{
                $this->success('删除成功', 'index.php?s=/admin/organization/relation');
            }
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 学生作品列表
     */
    public function works($organization_id=0){
        $model = D('OrganizationResource');
        $map['type']=1;
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
    public function works_add($organization_id=0){
        if($organization_id){
            $organization_name = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->getField('name');
            $this->assign('organization_id',$organization_id);
            $this->assign('organization_name',$organization_name);
            $this->assign('from_org',I('from_org'));
        }
        $this->display();
    }
    /**
     * 学生作品更新
     */
    public  function works_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationResource');
            $cid = $_POST["cid"];
            $pic_id = $_POST["picture"];
            $this->uploadLogoPicToOSS($pic_id);
            $data['url'] = $this->fetchCdnImage($pic_id);
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
                if(I('from_org')){
                    $this->success('添加成功', 'index.php?s=/admin/organization/works&organization_id='.$data['organization_id']);
                }else{
                    $this->success('添加成功', 'index.php?s=/admin/organization/works');
                }
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                if(I('from_org')){
                    $this->success('更新成功', 'index.php?s=/admin/organization/works&organization_id='.$data['organization_id']);
                }else{
                    $this->success('更新成功', 'index.php?s=/admin/organization/works');
                }
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
        if(I('from_org')){
            $this->assign('from_org', I('from_org'));
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
            if(I('from_org')){
                $this->success('删除成功','index.php?s=/admin/organization/works&organization_id='.I('organization_id'));
            }else{
                $this->success('删除成功','index.php?s=/admin/organization/works');
            }
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 机构报名信息列表
     * @param int $organization_id
     */
    public function enroll($organization_id=0){
        $model = M('OrganizationEnroll');
        //根据状态查询
        $status = I('status');
        if(!empty($status)){
            $filter_map['status'] = $status;
        }else{
            $filter_map['status'] = array('neq',-1);
        }
        //从机构列表跳转
        if($organization_id){
            $filter_map['organization_id'] = $organization_id;
            $organization_name = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->getField('name');
        }
        $count = $model->where($filter_map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于机构名称搜索
        $name = $_GET["title"];
        if(!$organization_id && $name){
            $map['name'] = array('like','%'.$name.'%');
            $map['status']=1;
            $oid = M('Organization')->where($map)->field('id')->select();
            $organization_id = array();
            foreach($oid as $org_id){
                $organization_id[] = $org_id['id'];
            }
            $filter_map['organization_id'] = array('in',$organization_id);
            $list = $model->where($filter_map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($filter_map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }

        foreach($list as &$enroll){
            if($organization_id){
                $enroll['organization'] = $organization_name;
            }else{
                $enroll['organization'] = M('Organization')->where(array('id'=>$enroll['organization_id'],'status'=>1))->getField('name');
            }
            $enroll['course_name'] = M('OrganizationConfig')
                ->where(array('id'=>$enroll['course_id'],'status'=>1,'type'=>1003))->getField('value');
        }
        if($organization_id){
            $this->assign('organization_id', $organization_id);
            $this->assign('organization_name', $organization_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构报名信息");
        $this->display();
    }

    /**
     * 添加报名
     * @param int $organization_id
     */
    public function enroll_add($organization_id=0){
        if($organization_id){
            $organization = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->field('name,application_status')->find();
            if($organization['application_status'] != 2){
                $this->error("该机构未通过审核机构",'index.php?s=/admin/organization/index',1);
            }
            $course_list = M('OrganizationConfig')->where('status=1 and type=1003 and organization_id='.$organization_id)->select();
            $this->assign('organization_id',$organization_id);
            $this->assign('organization_name',$organization['name']);
            $this->assign('course_list',$course_list);
            $this->display();
        }else{
            $this->error("未选择机构");
        }
    }

    /**
     * 机构报名信息更新
     */
    public  function enroll_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationEnroll');
            $cid = $_POST["id"];
            $data["organization_id"] = $_POST["organization_id"];
            $data['course_id'] = $_POST["course_id"];
            $data['student_uid'] = $_POST["student_uid"];
            $data['student_name'] = $_POST["student_name"];
            $data['phone_num'] = $_POST["phone_num"];
            $data['student_university'] = $_POST["student_university"];
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
                $this->success('报名成功', 'index.php?s=/admin/organization/enroll');
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                $this->success('修改成功', 'index.php?s=/admin/organization/enroll');
            }
        } else {
            $this->display('enroll_add');
        }
    }

    /**
     * 编辑报名信息
     * @param $id
     */
    public function enroll_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('OrganizationEnroll');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $data['organization'] = M('Organization')->where(array('id'=>$data['organization_id'],'status'=>1))->getField('name');
        $course_list = M('OrganizationConfig')->where('status=1 and type=1003 and organization_id='.$data['organization_id'])->select();
        $this->assign('info', $data);
        $this->assign('course_list', $course_list);
        $this->display();
    }

    /**
     * 删除报名信息
     * @param $id
     */
    public function enroll_delete($id){
        if(!empty($id)){
            $model = M('OrganizationEnroll');
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
            $this->success('删除成功','index.php?s=/admin/organization/enroll');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 报名通过
     */
    public function enroll_pass($id){
        if(!empty($id)){
            $model = M('OrganizationEnroll');
            $data['status'] = 2;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $res = $model->where('id='.$i)->save($data);
                    if($res){
                        $_student = $model->where('id='.$i)->field('organization_id,student_uid')->find();
                        $student['organization_id'] = $_student['organization_id'];
                        $student['uid'] = $_student['student_uid'];
                        $student['group'] = 5;
                        $students[] = $student;
                    }
                }
            } else {
                $id = intval($id);
                $res = $model->where('id='.$id)->save($data);
                if($res){
                    $_student = $model->where('id='.$id)->field('organization_id,student_uid')->find();
                    $student['organization_id'] = $_student['organization_id'];
                    $student['uid'] = $_student['student_uid'];
                    $student['group'] = 5;
                    $students[] = $student;
                }
            }
            if(empty($students)){
                $this->error('所选用户已确认报名');
            }
            $result = M('OrganizationRelation')->addAll($students);
            if($result){
                $this->success('认证通过','index.php?s=/admin/organization/enroll');
            }else{
                $this->error('认证失败');
            }
        } else {
            $this->error('未选择要认证的数据');
        }
    }

    /**
     * 拒绝报名
     */
    public function enroll_refuse($id){
        if(!empty($id)){
            $model = M('OrganizationEnroll');
            $data['status'] = -2;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('拒绝认证成功','index.php?s=/admin/organization/enroll');
        } else {
            $this->error('未选择要拒绝的数据');
        }
    }


    /**
     * 机构认证信息列表
     */
    public function authentication($organization_id=0){
        $model = M('OrganizationAuthentication');
        //根据状态查询
        $status = I('status');
        if(!empty($status)){
            $filter_map['status'] = $status;
        }else{
            $filter_map['status'] = array('neq',-1);
        }
        //从机构列表跳转
        if($organization_id){
            $filter_map['organization_id'] = $organization_id;
            $organization_name = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->getField('name');
        }
        $count = $model->where($filter_map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于机构名称搜索
        $name = $_GET["title"];
        if(!$organization_id && $name){
            $map['name'] = array('like','%'.$name.'%');
            $map['status']=1;
            $oid = M('Organization')->where($map)->field('id')->select();
            $organization_id = array();
            foreach($oid as $org_id){
                $organization_id[] = $org_id['id'];
            }
            $filter_map['organization_id'] = array('in',$organization_id);
            $list = $model->where($filter_map)->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($filter_map)->limit($Page->firstRow.','.$Page->listRows)->select();
        }

        foreach($list as &$authentication){
            if($organization_id){
                $authentication['organization'] = $organization_name;
            }else{
                $authentication['organization'] = M('Organization')->where(array('id'=>$authentication['organization_id'],'status'=>1))->getField('name');
            }
            $authentication['authentication_name'] = M('OrganizationAuthenticationConfig')
                ->where(array('id'=>$authentication['authentication_id'],'status'=>1))->getField('name');
        }
        if($organization_id){
            $this->assign('organization_id', $organization_id);
            $this->assign('organization_name', $organization_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构认证信息");
        $this->display();
    }

    /**
     * 添加机构认证
     * @param int $organization_id
     */
    public function authentication_add($organization_id=0){
        if($organization_id){
            $organization = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->field('name,application_status')->find();
            if($organization['application_status'] != 2){
                $this->error("该机构未通过审核机构",'index.php?s=/admin/organization/index',1);
            }
            $authentication_list = M('OrganizationAuthenticationConfig')->where('status=1')->select();
            $this->assign('organization_id',$organization_id);
            $this->assign('organization_name',$organization['name']);
            $this->assign('authentication_list',$authentication_list);
            $this->assign('from_org',I('from_org'));
            $this->display();
        }else{
            $this->error("未选择机构");
        }
    }

    /**
     * 机构认证信息更新
     */
    public  function authentication_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationAuthentication');
            $cid = $_POST["id"];
            $data["authentication_id"] = $_POST["authentication_id"];
            $data['organization_id'] = $_POST["organization_id"];

            $result = M('OrganizationAuthentication')
                ->where(array('organization_id'=>$data['organization_id'],'authentication_id'=>$data["authentication_id"],'status'=>1))
                ->count();
            if($result){
                $this->error("贵公司已经通过该认证，请选择其他认证");
            }
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
                if(I('from_org')){
                    $this->success('添加成功', 'index.php?s=/admin/organization/authentication&organization_id='.$data['organization_id']);
                }else{
                    $this->success('添加成功', 'index.php?s=/admin/organization/authentication');
                }
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                if(I('from_org')){
                    $this->success('更新成功', 'index.php?s=/admin/organization/authentication&organization_id='.$data['organization_id']);
                }else{
                    $this->success('更新成功', 'index.php?s=/admin/organization/authentication');
                }
            }
        } else {
            $this->display('authentication_add');
        }
    }

    /**
     * 编辑认证信息
     * @param $id
     */
    public function authentication_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('OrganizationAuthentication');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $data['organization'] = M('Organization')->where(array('id'=>$data['organization_id'],'status'=>1))->getField('name');
        $authentication_list = M('OrganizationAuthenticationConfig')->where('status=1')->select();
        if(I('from_org')){
            $this->assign('from_org', I('from_org'));
        }
        $this->assign('authentication_list',$authentication_list);
        $this->assign('info', $data);
        $this->display();
    }

    /**
     * 删除机构认证信息
     * @param $id
     */
    public function authentication_delete($id){
        if(!empty($id)){
            $model = M('OrganizationAuthentication');
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
            if(I('from_org')){
                $this->success('删除成功','index.php?s=/admin/organization/authentication&organization_id='.I('organization_id'));
            }
            $this->success('删除成功','index.php?s=/admin/organization/authentication');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 认证拒绝
     */
    public function authentication_restore($id){
        if(!empty($id)){
            $model = M('OrganizationAuthentication');
            $data['status'] = 1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('启用认证成功','index.php?s=/admin/organization/authentication');
        } else {
            $this->error('未选择要启用的数据');
        }
    }

    /**
     *认证默认展示
     *
     */
    public function authentication_config_display(){
        $id = array_unique((array)I('id',0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $filter_map['status'] = 1;
        $filter_map['default_display'] = 1;
        $display_count = M('OrganizationAuthenticationConfig')->where($filter_map)->count();
        if(!$display_count){
            $display_count = 0;
        }
        if(count($id) > (2-$display_count)){
            $this->error('默认展示不能超过两个');
        }
        $config = D('OrganizationAuthenticationConfig');
        $map = array('id' => array('in', $id) );
        $result = $config->where($map)->save(Array('default_display'=>1));
        if($result){
            $this->success('设置成功', U('authentication_config'));
        } else {
            $this->error('设置失败');
        }
    }

    /**
     * 认证默认隐藏
     *
     */
    public function authentication_config_hide(){
        $id = array_unique((array)I('id',0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $config = D('OrganizationAuthenticationConfig');
        $map = array('id' => array('in', $id) );
        $result = $config->where($map)->save(Array('default_display'=>0));
        if($result){
            $this->success('设置成功', U('authentication_config'));
        } else {
            $this->error('设置失败');
        }
    }

    /**
     * 认证配置列表
     */
    public function authentication_config()
    {
        $configvalue = I('name');
        if(!empty($configvalue)){
            $map['name'] = array('like', '%' . (string)$configvalue . '%');
        }
        $map['status'] = 1;
        $model = M('OrganizationAuthenticationConfig');
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构配置");
        $this->display();
    }

    /**
     * 机构认证配置编辑
     *
     */
    public function authentication_config_add(){
        $this->display();
    }

    /**
     * 机构认证配置编辑
     * @param $id
     */
    public function authentication_config_edit($id){
        $config = D('OrganizationAuthenticationConfig');
        $info = $config->where('status=1 and id='.$id)->find();
        $this->assign('info', $info);
        $this->meta_title = '编辑机构认证配置';
        $this->display();
    }

    /**
     * 机构认证配置添加和修改
     */
    public function authentication_config_update(){
        if(IS_POST){
            $Config = M('OrganizationAuthenticationConfig');
            $id = $_POST['id'];
            $data['name'] = $_POST['name'];
            $data['pic_id'] = $_POST['picture'];
            if(empty($id)){
                if($data){
                    //将最先添加的两个认证默认展示
                    $filter_map['status'] = 1;
                    $filter_map['default_display'] = 1;
                    $display_count = M('OrganizationAuthenticationConfig')->where($filter_map)->count();
                    if(!$display_count){
                        $display_count = 0;
                    }
                    if($display_count < 3){
                        $data['default_display'] = 1;
                    }
                    $data['create_time'] = time();
                    $res = $Config->add($data);
                    if($res){
                        $this->uploadLogoPicToOSS($res);
                        $this->success('新增成功', U('authentication_config'));
                    } else {
                        $this->error('新增失败');
                    }
                } else {
                    $this->error($Config->getError());
                }
            } else {
                $result = $Config->where('id='.$id)->save($data);
                if($result){
                    $this->uploadLogoPicToOSS($data['pic_id']);
                    $this->success('编辑成功', U('authentication_config'));
                } else {
                    $this->error('编辑失败');
                }
            }
        } else {
            $this->meta_title = '新增配置';
            $this->assign('info',null);
            $this->display('authentication_config_add');
        }
    }

    /**
     * 机构认证配置删除
     * @param $ids
     */
    public function authentication_config_delete(){
        $id = array_unique((array)I('id',0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $config = D('OrganizationAuthenticationConfig');
        $map = array('id' => array('in', $id) );
        $result = $config->where($map)->save(Array('status'=>-1));

        $filter_map['authentication_id'] = array('in',$id);
        $res = M('OrganizationAuthentication')->where($filter_map)->save(array('status'=>-1));
        if($result && $res){
            $this->success('删除成功', U('authentication_config'));
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 机构配置信息从删除中恢复
     */
    public function authentication_config_restore(){
        $id = array_unique((array)I('id',0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $config = D('OrganizationAuthenticationConfig');
        $map = array('id' => array('in', $id) );
        $result = $config->where($map)->save(Array('status'=>1));
        if($result){
            $this->success('启用成功', U('authentication_config'));
        } else {
            $this->error('启用失败');
        }
    }

    /**
     * 机构配置列表
     * @param int $organization_id
     */
    public function config($organization_id=0)
    {
        $configvalue = I('configvalue');
        if(!empty($configvalue)){
            $map['value'] = array('like', '%' . (string)$configvalue . '%');
        }
        $model = M('OrganizationConfig');
        if($organization_id){
            $map['organization_id'] = $organization_id;
            $organization_name = M('Organization')->where(array('id'=>$organization_id))->getField('name');
        }
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as &$config){
            if($organization_id){
                $config['organization_name'] = $organization_name;
            }else{
                if($config['organization_id'] > 0){
                    $config['organization_name'] = M('Organization')
                        ->where(array('id'=>$config['organization_id'],'status'=>1))->getField('name');
                }else{
                    $config['organization_name'] = '公用';
                }
            }
        }

        if($organization_id){
            $this->assign('organization_id', $organization_id);
            $this->assign('organization_name', $organization_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构配置");
        $this->display();
    }

    /**
     * 机构配置编辑
     * @param $organization_id
     */
    public function config_add($organization_id=0){
        if($organization_id){
            $model = M('Organization');
            $organization_name = $model->where('status=1 and id='.$organization_id)->getField('name');
            $this->assign('organization_name', $organization_name);
            $this->assign('organization_id', $organization_id);
            $this->assign('from_org', I('from_org'));
        }

        $this->meta_title = '新增机构配置';
        $this->display();
    }

    /**
     * 机构配置编辑
     * @param $id
     */
    public function config_edit($id){
        $config = D('OrganizationConfig');
        $info = $config->where('status=1 and id='.$id)->find();
        if(I('from_org')){
            $this->assign('from_org', I('from_org'));
            $this->assign('organization_id', I('organization_id'));
        }
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
                        if(I('from_org')){
                            $this->success('新增成功', U('config&organization_id='.$_POST['organization_id']));
                        }else{
                            $this->success('新增成功', U('config'));
                        }
                    } else {
                        $this->error('新增失败');
                    }
                } else {
                    $this->error($Config->getError());
                }
            } else {
                $result = $Config->where('id='.$id)->save($data);
                if($result){
                    if(I('from_org')){
                        $this->success('编辑成功', U('config&organization_id='.I('organization_id')));
                    }else{
                        $this->success('编辑成功', U('config'));
                    }
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
     */
    public function config_delete(){
        $id = array_unique((array)I('id',0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $config = D('OrganizationConfig');
        $map = array('id' => array('in', $id) );
        $result = $config->where($map)->save(Array('status'=>-1));
        foreach($id as $config_id){
            $organization_id[] = $config->where(array('id'=>$config_id))->getField('organization_id');
        }
        $organization_id = array_unique($organization_id);
        $filter_map['organization_id'] = array('in',$organization_id);
        $filter_map['teacher_group_id'] = array('in',$id);
        $res = M('OrganizationRelation')->where($filter_map)->save(array('status'=>-1));
        if($result){
            if(I('from_org')){
                $this->success('删除成功', U('config&organization_id='.I('organization_id')));
            }else{
                $this->success('删除成功', U('config'));
            }
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
     * @param int $organization_id
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
            $list = $model->where($filter)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
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
     * 机构新增评论
     * @param int $organization_id
     */
    public function comment_add($organization_id=0){
        if($organization_id){
            $organization_name = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->getField('name');
            $this->assign('organization_id',$organization_id);
            $this->assign('organization_name',$organization_name);
            $students = M('OrganizationRelation')
                ->where(array('organization_id'=>$organization_id,'group'=>5,'status'=>1))->field('uid')->select();
            foreach($students as &$student_id){
                $student['name'] = M('Member')->where(array('uid'=>$student_id['uid']))->getField('nickname');
                $student['id'] = $student_id['uid'];
                $student_list[] = $student;
            }
            $this->assign('student_list',$student_list);
            $this->assign('from_org',I('from_org'));
        }

        $this->meta_title = '新增机构评论';
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
        if(I('from_org')){
            $this->assign('from_org', I('from_org'));
        }
        $this->assign('comment', $info);
        $this->meta_title = '编辑机构评论';
        $this->display();
    }

    /**
     * 机构配置添加和修改
     */
    public function comment_update($id=0){
        if(IS_POST){
            $model = D('OrganizationComment');
            $id = $_POST['id'];
            $data['organization_id'] = $_POST['organization_id'];
            $data['uid'] = $_POST['uid'];
            $data['comprehensive_score'] = $_POST['comprehensive_score'];
            $data['comment'] = $_POST['comment'];
            if(empty($id)){
                if($data){
                    $data['create_time'] = time();
                    if($model->add($data)){
                        if(I('from_org')){
                            $this->success('新增成功', U('comment&organization_id='.$data['organization_id']));
                        }else{
                            $this->success('新增成功', U('comment'));
                        }
                    } else {
                        $this->error('新增失败');
                    }
                } else {
                    $this->error($model->getError());
                }
            } else {
                $result = $model->where('id='.$id)->save($data);
                if($result){
                    if(I('from_org')){
                        $this->success('编辑成功', U('comment&organization_id='.$data['organization_id']));
                    }else{
                        $this->success('编辑成功', U('comment'));
                    }
                } else {
                    $this->error('编辑失败');
                }
            }
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
            if(I('from_org')){
                $this->success('删除成功', U('comment&organization_id='.I('organization_id')));
            }else{
                $this->success('删除成功', U('comment'));
            }
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 机构分类评分列表
     */
    public function comment_star($organization_id=0)
    {
        $model = D('OrganizationCommentStar');
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
            $comment['star_type'] = M('OrganizationConfig')->where(array('id'=>$comment['comment_type'],'status'=>1))->getField('value');
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
     * 机构分类评分编辑
     * @param $id
     */
    public function comment_star_edit($id){
        $comment = D('OrganizationCommentStar');
        $info = $comment->where('status=1 and id='.$id)->find();
        $info['organization_name'] = M('Organization')->where(array('id'=>$info['organization_id'],'status'=>1))->getField('name');
        $info['user_name'] = M('Member')->where(array('uid'=>$info['uid'],'status'=>1))->getField('nickname');
        $star_type = M('OrganizationConfig')->where(array('type'=>5,'status'=>1))->select();

        $this->assign('star_type', $star_type);
        $this->assign('comment', $info);
        $this->meta_title = '编辑机构评论';
        $this->display();
    }

    /**
     * 机构分类评分添加和修改
     */
    public function comment_star_update($id=0){
        if(IS_POST){
            $model = D('OrganizationCommentStar');
            $data = $model->create();
            if(empty($id)){
                if($data){
                    if($model->add()){
                        $this->success('新增成功', U('comment_star'));
                    } else {
                        $this->error('新增失败');
                    }
                } else {
                    $this->error($model->getError());
                }
            } else {
                $result = $model->where('id='.$id)->save($data);
                if($result){
                    $this->success('编辑成功', U('comment_star'));
                } else {
                    $this->error('编辑失败');
                }
            }
        }
    }

    /**
     * 机构分类评分删除
     *
     */
    public function comment_star_delete(){
        $id = array_unique((array)I('id',0));
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        $config = D('OrganizationCommentStar');
        $map = array('id' => array('in', $id) );
        $result = $config->where($map)->save(Array('status'=>-1));
        if($result){
            $this->success('删除成功', U('comment_star'));
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
        $map['type'] = 2;
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
    public function environment_add($organization_id=0){
        if($organization_id){
            $organization_name = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->getField('name');
            $this->assign('organization_id',$organization_id);
            $this->assign('organization_name',$organization_name);
            $this->assign('from_org',I('from_org'));
        }
        $this->display();
    }

    /**
     * 环境图片更新
     */
    public  function environment_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationResource');
            $cid = $_POST["cid"];
            $pic_id = $_POST["picture"];
            $this->uploadLogoPicToOSS($pic_id);
            $data["url"] = $this->fetchCdnImage($pic_id);
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
                if(I('from_org')){
                    $this->success('添加成功', 'index.php?s=/admin/organization/environment&organization_id='.$data['organization_id']);
                }else{
                    $this->success('添加成功', 'index.php?s=/admin/organization/environment');
                }
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                if(I('from_org')){
                    $this->success('编辑成功', 'index.php?s=/admin/organization/environment&organization_id='.$data['organization_id']);
                }else{
                    $this->success('编辑成功', 'index.php?s=/admin/organization/environment');
                }
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
        if(I('from_org')){
            $this->assign('from_org', I('from_org'));
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
            if(I('from_org')){
                $this->success('删除成功','index.php?s=/admin/organization/environment&organization_id='.I('organization_id'));
            }else{
                $this->success('删除成功','index.php?s=/admin/organization/environment');
            }
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
     * 机构课程新增
     */
    public function course_add($organization_id=0){
        if($organization_id){
            $organization_name = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->getField('name');
            $teacher_ids = M(OrganizationRelation)->field('uid')
                ->where(array('organization_id'=>$organization_id,'group'=>6,'status'=>1))->select();
            foreach($teacher_ids as &$teacher_id){
                $teacher['name'] = M('Member')->where(array('uid'=>$teacher_id['uid'],'status'=>1))->getField('nickname');
                $teacher['id'] = $teacher_id['uid'];
                $teacher_list[] = $teacher;
            }
            $this->assign('teacher_list', $teacher_list);
            $this->assign('organization_name',$organization_name);
            $this->assign('organization_id',$organization_id);
        }
        $category = M('OrganizationConfig')->where(array('status'=>1,'type'=>1002))->field('id,value')->select();
        if(I('from_org')){
            $this->assign('from_org', I('from_org'));
        }
        $this->assign('category', $category);
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
                if(I('from_org')){
                    $this->success('添加成功', 'index.php?s=/admin/organization/course&organization_id='.$data['organization_id']);
                }else{
                    $this->success('添加成功', 'index.php?s=/admin/organization/course');
                }

            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                $this->uploadLogoPicToOSS($data['img']);
                if(I('from_org')){
                    $this->success('更新成功', 'index.php?s=/admin/organization/course&organization_id='.$data['organization_id']);
                }else{
                    $this->success('更新成功', 'index.php?s=/admin/organization/course');
                }
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
        $teacher_ids = M(OrganizationRelation)->field('uid')
            ->where(array('organization_id'=>$data['organization_id'],'group'=>6,'status'=>1))->select();
        foreach($teacher_ids as &$teacher_id){
            $teacher['name'] = M('Member')->where(array('uid'=>$teacher_id['uid'],'status'=>1))->getField('nickname');
            $teacher['id'] = $teacher_id['uid'];
            $teacher_list[] = $teacher;
        }
        if(I('from_org')){
            $this->assign('from_org', I('from_org'));
        }
        $this->assign('teacher_list', $teacher_list);
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
            if(I('from_org')){
                $this->success('删除成功','index.php?s=/admin/organization/course&organization_id='.I('organization_id'));
            }else{
                $this->success('删除成功','index.php?s=/admin/organization/course');
            }
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 机构证书
     */
    public function application(){
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
        $model = D('OrganizationApplication');
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构创建申请");
        $this->display();
    }

    /**
     * 审核通过
     */
    public function application_pass($id){
        if(!empty($id)){
            $model = M('OrganizationApplication');
            $data['status'] = 2;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                    $organization_id = $model->where('id='.$i)->getField('organization_id');
                    M('Organization')->where(array('id'=>$organization_id))->save(array('application_status'=>2));
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
                $organization_id = $model->where('id='.$id)->getField('organization_id');
                M('Organization')->where(array('id'=>$organization_id))->save(array('application_status'=>2));
            }
            $this->success('审核通过','index.php?s=/admin/organization/application');
        } else {
            $this->error('未选择要审核的数据');
        }
    }

    /**
     * 认证拒绝
     */
    public function application_refuse($id){
        if(!empty($id)){
            $model = M('OrganizationApplication');
            $data['status'] = -2;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                    $organization_id = $model->where('id='.$i)->getField('organization_id');
                    M('Organization')->where(array('id'=>$organization_id))->save(array('application_status'=>-2));
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
                $organization_id = $model->where('id='.$id)->getField('organization_id');
                M('Organization')->where(array('id'=>$organization_id))->save(array('application_status'=>-2));
            }
            $this->success('拒绝申请成功','index.php?s=/admin/organization/application');
        } else {
            $this->error('未选择要拒绝的数据');
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
    public  function notice($organization_id=0){
        $model = D('OrganizationNotice');
        $map['status'] = 1;
        if($organization_id){
            $map['organization_id'] = $organization_id;
            $organization_name = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->getField('name');
        }
        $count = $model->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于公告名称搜索
        $name = $_GET["title"];
        if($name){
            $map['title'] = array('like','%'.$name.'%');
            $map['status'] = 1;
            $list = $model->where($map)->order('update_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($map)->order('update_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$notice){
            if($organization_id){
                $notice['organization_name'] = $organization_name;
            }else{
                if($notice['organization_id'] > 0){
                    $notice['organization_name'] = M('Organization')
                        ->where(array('id'=>$notice['organization_id'],'status'=>1))->getField('name');
                }else{
                    $notice['organization_name'] = '公用';
                }
            }
        }

        if($organization_id){
            $this->assign('organization_id',$organization_id);
            $this->assign('organization_name',$organization_name);
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
    public function notice_add($organization_id=0){
        if($organization_id){
            $organization_name = M('Organization')->where(array('id'=>$organization_id,'status'=>1))->getField('name');
            $this->assign('organization_name',$organization_name);
            $this->assign('organization_id',$organization_id);
            $this->assign('from_org',I('from_org'));
        }
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
            $data["organization_id"] = $_POST["organization_id"];
            $data["push_to_organization"] = $_POST["push_to_organization"];
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
                if(I('from_org')){
                    $this->success('添加成功', 'index.php?s=/admin/organization/notice&organization_id='.$data["organization_id"]);
                }{
                    $this->success('添加成功', 'index.php?s=/admin/organization/notice');
                }
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if(!$res){
                    $this->error($model->getError());
                }
                if(I('from_org')){
                    $this->success('添加成功', 'index.php?s=/admin/organization/notice&organization_id='.$data["organization_id"]);
                }{
                    $this->success('添加成功', 'index.php?s=/admin/organization/notice');
                }
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
        if(I('from_org')){
            $this->assign('from_org', I('from_org'));
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
            if(I('from_org')){
                $this->success('删除成功','index.php?s=/admin/organization/notice&organization_id='.I("organization_id"));
            }else{

            }
            $this->success('删除成功','index.php?s=/admin/organization/notice');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 上传图片到OSS
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

    /**
     * 获取cdn oss图片地址
     * @param $pic_id
     * @return null|string
     */
    private function fetchCdnImage($pic_id){
        if($pic_id == null)
            return null;
        $model = M();
        $pic_info = $model->query("select path from hisihi_picture where id=".$pic_id);
        if($pic_info){
            $path = $pic_info[0]['path'];
            $objKey = substr($path, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $objKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if($isExist){
                $picUrl = "http://pic.hisihi.com/".$objKey;
            } else {
                $picUrl = null;
            }
        }
        return $picUrl;
    }

}