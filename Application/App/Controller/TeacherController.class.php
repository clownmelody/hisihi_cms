<?php
namespace App\Controller;
use Common\Controller\BaseController;

class TeacherController extends BaseController
{
    public function __construct(){
        parent::__construct();
    }

    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    public function teacherv3_1(){
        $this->display('teacherv3_1');
    }

    public function tagList(){
        $model = M('OrganizationTag');
        $map['status'] = 1;
        $map['type'] = 10;
        $list = $model->field('id, value')->where($map)->select();
        $extra['data'] = $list;
        $this->apiSuccess('获取老师标签列表成功', null, $extra);
    }

    /**
     * 创建老师
     * @param $name
     * @param $avatar
     * @param $title
     * @param $tag
     * @param $introduce
     * @param null $student_list
     * @param $teach_age
     * @param $employment_rate
     * @param $student_num
     * @param null $student_work_list
     */
    public function createTeacher($name, $avatar, $title, $tag, $introduce, $student_list=null,
                           $teach_age, $employment_rate, $student_num, $student_work_list=null){
        $model = M('OrganizationTeacher');
        $tsrmodel = M('TeacherStudentRelation');
        $swmodel = M('StudentWorks');
        $data['name'] = $name;
        $data['avatar'] = $avatar;
        $data['title'] = $title;
        $data['tag'] = $tag;
        $data['introduce'] = $introduce;
        $data['teach_age'] = $teach_age;
        $data['employment_rate'] = $employment_rate;
        $data['student_num'] = $student_num;
        $data['create_time'] = time();
        $teacher_id = $model->add($data);
        if(!empty($student_list)){
            $student_list = json_decode($student_list, true);
            foreach($student_list as $sid){
                $tsrdata['teacher_id'] = $teacher_id;
                $tsrdata['student_id'] = $sid;
                $tsrdata['create_time'] = time();
                $tsrmodel->add($tsrdata);
            }
        }
        if(!empty($student_work_list)){
            $student_work_list = json_decode($student_work_list, true);
            foreach($student_work_list as $pic_url){
                $swdata['teacher_id'] = $teacher_id;
                $swdata['pic_url'] = $pic_url;
                $swdata['create_time'] = time();
                $swmodel->add($swdata);
            }
        }
        $this->apiSuccess('创建老师成功', null, array('id'=>$teacher_id));
    }

    public function deleteStudentWork($work_id=0){
        $model = M('StudentWorks');
        $data['id'] = $work_id;
        $model->where($data)->save(array('status'=>-1));
        $this->apiSuccess('删除学生作品成功');
    }

    public function deleteStudentEmployInfo($teacher_id=0, $student_id=0){
        $model = M('TeacherStudentRelation');
        $data['teacher_id'] = $teacher_id;
        $data['student_id'] = $student_id;
        $model->where($data)->save(array('status'=>-1));
        $this->apiSuccess('删除学生就业信息成功');
    }

    public function createStudent($name, $avatar, $title, $company, $salary){
        $model = M('OrganizationStudent');
        $data['name'] = $name;
        $data['avatar'] = $avatar;
        $data['title'] = $title;
        $data['company'] = $company;
        $data['salary'] = $salary;
        $data['create_time'] = time();
        $id = $model->add($data);
        $this->apiSuccess('创建学生成功', null, array('id'=>$id));
    }

    public function getTeacherInfo($teacher_id=0){
        $model = M('OrganizationTeacher');
        $orgModel = M('Organization');
        $teacherInfo = $model->field('id, organization_id, name, avatar, title, tag, introduce, teach_age,
         employment_rate, student_num')->where('id='.$teacher_id)->find();
        $orgInfo = $orgModel->field('type')->where('id='.$teacherInfo['organization_id'])->find();
        $teacherInfo['org_type'] = $orgInfo['type'];
        $this->apiSuccess('获取老师基本信息成功', null, array('data'=>$teacherInfo));
    }

    public function getTeacherStudentWorkList($teacher_id=0, $page=1, $count=8){
        $model = M('StudentWorks');
        $totalCount = $model->where('status=1 and teacher_id='.$teacher_id)->count();
        $list = $model->field('id, pic_url')->where('status=1 and teacher_id='.$teacher_id)
            ->page($page, $count)->select();
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $list;
        $this->apiSuccess('获取老师下学生作品成功', null, $extra);
    }

    public function getStudentEmployList($teacher_id=0){
        $model = M('TeacherStudentRelation');
        $stuModel = M('OrganizationStudent');
        $stuList = array();
        $totalCount = $model->where('status=1 and teacher_id='.$teacher_id)->count();
        $list = $model->field('student_id')->where('status=1 and teacher_id='.$teacher_id)
            ->order('create_time desc')->select();
        foreach($list as $item){
            $info = $stuModel->field('id, name, company, title, avatar, salary')
                ->where('id='.$item['student_id'])->find();
            $stuList[] = $info;
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $stuList;
        $this->apiSuccess('获取老师下学生就业信息列表成功', null, $extra);
    }

}