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
     * @param $organization_id
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
     * @param int $uid
     */
    public function createTeacher($organization_id, $name, $avatar, $title, $tag, $introduce, $student_list=null,
                           $teach_age, $employment_rate, $student_num, $student_work_list=null, $uid=0){
        $model = M('OrganizationTeacher');
        $tsrmodel = M('TeacherStudentRelation');
        $swmodel = M('StudentWorks');
        $data['organization_id'] = $organization_id;
        $data['name'] = $name;
        $data['avatar'] = $avatar;
        $data['title'] = $title;
        $data['tag'] = $tag;
        $data['introduce'] = $introduce;
        $data['teach_age'] = $teach_age;
        $data['employment_rate'] = $employment_rate;
        $data['student_num'] = $student_num;
        $data['uid'] = $uid;
        $data['create_time'] = time();
        $teacher_id = $model->add($data);
        if(!empty($student_list)){
            $student_list = stripslashes($student_list);
            $student_list = json_decode($student_list, true);
            foreach($student_list as $sid){
                $tsrdata['teacher_id'] = $teacher_id;
                $tsrdata['student_id'] = $sid;
                $tsrdata['create_time'] = time();
                $tsrmodel->add($tsrdata);
            }
        }
        if(!empty($student_work_list)){
            $student_work_list = stripslashes($student_work_list);
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

    /**
     * @param $id
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
     * @param int $uid
     */
    public function updateTeacher($id, $name=null, $avatar=null, $title=null, $tag=null, $introduce=null,
                                  $student_list=null, $teach_age=null, $employment_rate=null,
                                  $student_num=null, $student_work_list=null, $uid=0){
        $model = M('OrganizationTeacher');
        $tsrmodel = M('TeacherStudentRelation');
        $swmodel = M('StudentWorks');
        if(!empty($name)){
            $data['name'] = $name;
        }
        if(!empty($avatar)){
            $data['avatar'] = $avatar;
        }
        if(!empty($title)){
            $data['title'] = $title;
        }
        if(!empty($tag)){
            $data['tag'] = $tag;
        }
        if(!empty($introduce)){
            $data['introduce'] = $introduce;
        }
        if(!empty($teach_age)){
            $data['teach_age'] = $teach_age;
        }
        if(!empty($employment_rate)){
            $data['employment_rate'] = $employment_rate;
        }
        if(!empty($student_num)){
            $data['student_num'] = $student_num;
        }
        if(!empty($uid)){
            $data['uid'] = $uid;
        }
        $model->where('id='.$id)->save($data);
        if(!empty($student_list)){
            $student_list = stripslashes($student_list);
            $student_list = json_decode($student_list, true);
            foreach($student_list as $sid){
                $sel_where['teacher_id'] = $id;
                $sel_where['student_id'] = $sid;
                $sel_where['status'] = 1;
                $count = $tsrmodel->where($sel_where)->count();
                if(!$count){
                    $tsrdata['teacher_id'] = $id;
                    $tsrdata['student_id'] = $sid;
                    $tsrdata['create_time'] = time();
                    $tsrmodel->add($tsrdata);
                }
            }
        }
        if(!empty($student_work_list)){
            $student_work_list = stripslashes($student_work_list);
            $student_work_list = json_decode($student_work_list, true);
            foreach($student_work_list as $pic_url){
                $sel_where['teacher_id'] = $id;
                $sel_where['pic_url'] = $pic_url;
                $sel_where['status'] = 1;
                $count = $swmodel->where($sel_where)->count();
                if(!$count){
                    $swdata['teacher_id'] = $id;
                    $swdata['pic_url'] = $pic_url;
                    $swdata['create_time'] = time();
                    $swmodel->add($swdata);
                }
            }
        }
        $this->apiSuccess('更新老师成功', null, array('id'=>$id));
    }

    public function deleteTeacher($id){
        $model = M('OrganizationTeacher');
        $data['status'] = -1;
        $model->where('id='.$id)->save($data);
        $this->apiSuccess('删除老师成功');
    }

    public function deleteStudentWork($work_id=0){
        $model = M('StudentWorks');
        $data['id'] = $work_id;
        $model->where($data)->save(array('status'=>-1));
        $this->apiSuccess('删除学生作品成功');
    }

    public function bindStudentWork($teacher_id=0, $course_id=0, $pic_url_list=null){
        if(empty($pic_url_list)){
            $this->apiError(-1, '图片地址不能为空');
        }
        if($teacher_id==0&&$course_id==0){
            $this->apiError(-1, '老师id和课程id不能同时为空');
        }
        $model = M('StudentWorks');
        if(!empty($pic_url_list)){
            $pic_url_list = stripslashes($pic_url_list);
            $pic_url_list = json_decode($pic_url_list, true);
            foreach($pic_url_list as $pic_url){
                $sel_where['teacher_id'] = $teacher_id;
                $sel_where['course_id'] = $course_id;
                $sel_where['pic_url'] = $pic_url;
                $sel_where['status'] = 1;
                $count = $model->where($sel_where)->count();
                if(!$count){
                    $data['teacher_id'] = $teacher_id;
                    $data['course_id'] = $course_id;
                    $data['pic_url'] = $pic_url;
                    $data['create_time'] = time();
                    $model->add($data);
                }
            }
        }
        $this->apiSuccess('绑定学生作品成功');
    }

    public function deleteStudentEmployInfo($teacher_id=0, $student_id=0){
        $model = M('TeacherStudentRelation');
        $data['teacher_id'] = $teacher_id;
        $data['student_id'] = $student_id;
        $model->where($data)->save(array('status'=>-1));
        $this->apiSuccess('删除学生就业信息成功');
    }

    public function createStudent($name, $avatar, $title=null, $company=null, $salary=null,
                                  $country=null, $school=null, $major=null){
        $model = M('OrganizationStudent');
        $data['name'] = $name;
        $data['avatar'] = $avatar;
        if(!empty($title)){
            $data['title'] = $title;
        }
        if(!empty($company)){
            $data['company'] = $company;
        }
        if(!empty($salary)){
            $data['salary'] = $salary;
        }
        if(!empty($country)){
            $data['country'] = $country;
        }
        if(!empty($school)){
            $data['school'] = $school;
        }
        if(!empty($major)){
            $data['major'] = $major;
        }
        $data['create_time'] = time();
        $id = $model->add($data);
        $this->apiSuccess('创建学生就业信息成功', null, array('id'=>$id));
    }

    public function updateStudent($id, $name=null, $avatar=null, $title=null, $company=null, $salary=null,
                                  $country=null, $school=null, $major=null){
        $model = M('OrganizationStudent');
        if(!empty($name)){
            $data['name'] = $name;
        }
        if(!empty($avatar)){
            $data['avatar'] = $avatar;
        }
        if(!empty($title)){
            $data['title'] = $title;
        }
        if(!empty($company)){
            $data['company'] = $company;
        }
        if(!empty($salary)){
            $data['salary'] = $salary;
        }
        if(!empty($country)){
            $data['country'] = $country;
        }
        if(!empty($school)){
            $data['school'] = $school;
        }
        if(!empty($major)){
            $data['major'] = $major;
        }
        $model->where('id='.$id)->save($data);
        $this->apiSuccess('修改学生就业信息成功');
    }

    public function deleteStudent($id){
        $model = M('OrganizationStudent');
        $data['status'] = 1;
        $model->where('id='.$id)->save($data);
        $this->apiSuccess('删除学生就业信息成功');
    }

    public function getTeacherInfo($teacher_id=0){
        $model = M('OrganizationTeacher');
        $orgModel = M('Organization');
        $teacherInfo = $model->field('id, organization_id, name, avatar, title, tag, introduce, teach_age,
         employment_rate, student_num, uid')->where('id='.$teacher_id)->find();
        $orgInfo = $orgModel->field('type')->where('id='.$teacherInfo['organization_id'])->find();
        $teacherInfo['org_type'] = $orgInfo['type'];
        $teacherInfo['web_url'] = C('HOST_NAME_PREFIX')."api.php?s=/teacher/teacherv3_1/uid/".$teacher_id;
        $this->apiSuccess('获取老师基本信息成功', null, array('data'=>$teacherInfo));
    }

    public function getTeacherStudentWorkList($teacher_id=0, $page=1, $count=8){
        $model = M('StudentWorks');
        $totalCount = $model->where('status=1 and teacher_id='.$teacher_id)->count();
        $list = $model->field('id, pic_url')->where('status=1 and teacher_id='.$teacher_id)
            ->page($page, $count)->select();
        foreach($list as &$item){
            $new_pic_url = $item['pic_url'] . '@info';
            $item['origin_info'] = json_decode(getOssImgSizeInfo($new_pic_url));
        }
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

    public function getTeacherList($organization_id=0){
        $model = M('OrganizationTeacher');
        $list = $model->field('id, name, avatar, title, introduce')
            ->where('status=1 and organization_id='.$organization_id)->select();
        foreach($list as &$item){
            $item['web_url'] = C('HOST_NAME_PREFIX')."api.php?s=/teacher/teacherv3_1/uid/".$item['id'];
        }
        $this->apiSuccess('获取机构下老师列表成功', null, array('data'=>$list));
    }

    /**
     * 创建培训课程
     * @param int $organization_id
     * @param null $course_name
     * @param null $cover_pic
     * @param null $introduction
     * @param int $price
     * @param null $teacher_id_list
     * @param null $student_work_list
     * @param null $outline
     */
    public function createTeachingCourse($organization_id=0, $course_name=null, $cover_pic=null,
                                         $introduction=null, $price=0, $teacher_id_list=null,
                                         $student_work_list=null, $outline=null){
        $courseModel = M('OrganizationTeachingCourse');
        $swmodel = M('StudentWorks');
        $outlineModel = M('TeachingCourseOutline');
        $outlineResModel = M('TeachingCourseOutlineResource');
        $data['organization_id'] = $organization_id;
        $data['course_name'] = $course_name;
        $data['cover_pic'] = $cover_pic;
        $data['introduction'] = $introduction;
        $data['price'] = $price;
        $data['teacher_id_list'] = $teacher_id_list;
        $data['create_time'] = time();
        $course_id = $courseModel->add($data);
        if(!empty($student_work_list)){
            $student_work_list = stripslashes($student_work_list);
            $student_work_list = json_decode($student_work_list, true);
            foreach($student_work_list as $pic_url){
                $swdata['course_id'] = $course_id;
                $swdata['pic_url'] = $pic_url;
                $swdata['create_time'] = time();
                $swmodel->add($swdata);
            }
        }
        if(!empty($outline)){
            $outline = stripslashes($outline);
            $outline = json_decode($outline, true);
            foreach($outline as $item){
                $first_title = $item['title'];
                $outlineData['teaching_course_id'] = $course_id;
                $outlineData['title'] = $first_title;
                $outlineData['create_time'] = time();
                $outlineId = $outlineModel->add($outlineData);
                $contentList = $item['data'];
                foreach($contentList as $content){
                    $outlineResData['outline_id'] = $outlineId;
                    $outlineResData['name'] = $content['name'];
                    $outlineResData['type'] = $content['type'];
                    if($outlineResData['type']==1){
                        $outlineResData['status'] = 0;
                    }
                    $outlineResData['video_id'] = $content['video_id'];
                    $outlineResData['content']  = $content['content'];
                    $outlineResData['cover_pic']  = $content['cover_pic'];
                    if($content['is_top']){
                        $outlineResData['is_top'] = $content['is_top'];
                    } else {
                        $outlineResData['is_top'] = 0;
                    }
                    $outlineResData['create_time'] = time();
                    $outlineResModel->add($outlineResData);
                }
            }
        }
        $this->apiSuccess('创建课程成功', null, array('id'=>$course_id));
    }

    public function getCourseTeacherList($teaching_course_id=0){
        $courseModel = M('OrganizationTeachingCourse');
        $courseInfo = $courseModel->field('teacher_id_list')->where('id='.$teaching_course_id)->find();
        $teacherList = explode('#', $courseInfo['teacher_id_list']);
        $model = M('OrganizationTeacher');
        $list = array();
        foreach($teacherList as $item){
            $info = $model->field('id, name, avatar, title, introduce, uid')
                ->where('id='.$item)->find();
            $info['web_url'] = C('HOST_NAME_PREFIX')."api.php?s=/teacher/teacherv3_1/uid/".$info['id'];
            $list[] = $info;
        }
        $this->apiSuccess('获取课程下老师列表成功', null, array('data'=>$list));
    }

    public function getCourseStudentWorkList($teaching_course_id=0, $page=1, $count=8){
        $model = M('StudentWorks');
        $totalCount = $model->where('status=1 and course_id='.$teaching_course_id)->count();
        $list = $model->field('id, pic_url')->where('status=1 and course_id='.$teaching_course_id)
            ->page($page, $count)->select();
        foreach($list as &$item){
            $new_pic_url = $item['pic_url'] . '@info';
            $item['origin_info'] = json_decode(getOssImgSizeInfo($new_pic_url));
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $list;
        $this->apiSuccess('获取课程下学生作品成功', null, $extra);
    }

    public function getCourseOutline($teacher_course_id=0){
        $outlineModel = M('TeachingCourseOutline');
        $outlineResModel = M('TeachingCourseOutlineResource');
        $outlineList = $outlineModel->field('id, title')
            ->where('status=1 and teaching_course_id='.$teacher_course_id)->select();
        foreach($outlineList as &$item){
            $resList = $outlineResModel->field('id, outline_id, name, type, video_id,
                                                content, cover_pic, is_top')
                ->where('status>=0 and outline_id='.$item['id'])->select();
            foreach($resList as &$res){
                if($res['type']==2){
                    $res['content'] = C('HOST_NAME_PREFIX')."api.php?s=TeachingCourse/outLineLongTextPage/id/".$res['id'];
                }
            }
            $item['data'] = $resList;
        }
        $this->apiSuccess('获取课程大纲成功', null, array('data'=>$outlineList));
    }

    public function searchTeacherByMobile($mobile=null){
        $model = M('UcenterMember');
        $info = $model->field('id, username')->where('mobile='.$mobile)->find();
        $this->apiSuccess('根据手机号查找用户成功', null, array('data'=>$info));
    }

}