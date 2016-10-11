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

}