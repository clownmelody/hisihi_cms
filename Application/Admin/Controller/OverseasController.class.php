<?php

namespace Admin\Controller;

use Think\Page;

/**
 * 留学模块
 * Class OverseasController
 * @package Admin\Controller
 */
class OverseasController extends AdminController
{

    public function index(){
        $model = M('AbroadCountry');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","海外国家列表");
        $this->display();
    }

    public function country_add(){
        $this->display('country_add');
    }

    public function country_update(){
        if (IS_POST) { //提交表单
            $model = M('AbroadCountry');
            $cid = $_POST['cid'];
            $data['name'] = $_POST["name"];
            $pic_id = $_POST["logo_url"];
            if(!empty($pic_id)){
                A('Organization')->uploadLogoPicToOSS($pic_id);
                $data['logo_url'] = A('Organization')->fetchCdnImage($pic_id);
            }
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/overseas/index');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/overseas/index');
            }
        } else {
            $this->display('country_add');
        }
    }

    public function country_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('AbroadCountry');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('country', $data);
        $this->meta_title = '编辑留学国家';
        $this->display();
    }

    public function majors(){
        $model = M('AbroadUniversityMajors');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","专业列表");
        $this->display();
    }

    public function majors_update(){
        if (IS_POST) { //提交表单
            $model = M('AbroadUniversityMajors');
            $mid = $_POST['mid'];
            $data['name'] = $_POST["name"];
            $data['type'] = $_POST["type"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/overseas/majors');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/overseas/majors');
            }
        } else {
            $this->display('majors_add');
        }
    }

    public function majors_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('AbroadUniversityMajors');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('majors', $data);
        $this->meta_title = '编辑专业';
        $this->display();
    }

    public function majors_set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('AbroadUniversityMajors');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/overseas/majors');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function cancle_hot($id, $is_hot=0){
        if(!empty($id)){
            $model = M('AbroadCountry');
            $data['is_hot'] = $is_hot;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/overseas/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function country_set_hot($id){
        if(!empty($id)){
            $model = M('AbroadCountry');
            $data['is_hot'] = 1;
            $model->where('id='.$id)->save($data);
            $this->success('处理成功','index.php?s=/admin/overseas/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function country_set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('AbroadCountry');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/overseas/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function university(){
        $model = M('AbroadUniversity');
        $countryModel = M('AbroadCountry');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as &$university){
            $country_id = $university['country_id'];
            $country_info = $countryModel->field('name')->where('id='.$country_id)->find();
            $university['country'] = $country_info['name'];
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title", "海外大学列表");
        $this->display();
    }

    public function setHot($id){
        if(!empty($id)){
            $model = M('Organization');
            $data['is_hot'] = 1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }

            $this->success('设置成功','index.php?s=/admin/overseas/org_list');
        } else {
            $this->error('未选择要操作的数据');
        }
    }

    /**留学机构列表
     * @param string $type
     */
    public function org_list($type='留学'){
        $map['value'] = $type;
        $map['status'] = 1;
        $type_id = M('OrganizationTag')->where($map)->getField('id');
        $model = M('Organization');
        $where_map['status'] = 1;
        $where_map['type'] = $type_id;
        $count = $model->where($where_map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        //用于公司名称搜索
        $name = $_GET["title"];
        if($name){
            $where_map['name'] = array('like','%'.$name.'%');
            $list = $model->where($where_map)->order('sort asc, create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($where_map)->order('sort asc, create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$org){
            $has_admin = M('OrganizationAdmin')->where('status=1 and id='.$org['uid'])->count();
            if($has_admin){
                $org['has_admin'] = 1;
            }else{
                $org['has_admin'] = 0;
            }
            $org['type'] = M('OrganizationTag')->where('type=7 and status=1 and id='.$org['type'])->getField('value');
        }
        $major = M('OrganizationTag')->field('id, value')->where('type=8 and status>0')->select();
        $type = M('OrganizationTag')->field('id, value')->where('type=7 and status=1')->select();
        $this->assign('type', $type);
        $this->assign('major', $major);
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","机构列表");
        $this->display();
    }

    public function university_set_status($id, $status=-1)
    {
        if (!empty($id)) {
            $model = M('AbroadUniversity');
            $data['status'] = $status;
            if (is_array($id)) {
                foreach ($id as $i) {
                    $model->where('id=' . $i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id=' . $id)->save($data);
            }
            $this->success('处理成功', 'index.php?s=/admin/overseas/university');
        } else {
                $this->error('未选择要处理的数据');
        }
    }

    public function university_update(){
        if (IS_POST) { //提交表单
            $model = M('AbroadUniversity');
            $uid = $_POST['uid'];
            $data['name'] = $_POST["name"];
            $data['website'] = $_POST["website"];
            $data['introduction'] = $_POST["introduction"];
            $data['sia_recommend_level'] = $_POST["sia_recommend_level"];
            $data['sia_student_enrollment_rate'] = $_POST["sia_student_enrollment_rate"];
            $data['difficulty_of_application'] = $_POST["difficulty_of_application"];
            $data['tuition_fees'] = $_POST["tuition_fees"];
            $data['toefl'] = $_POST["toefl"];
            $data['ielts'] = $_POST["ielts"];
            $data['proportion_of_undergraduates'] = $_POST["proportion_of_undergraduates"];
            $data['scholarship'] = $_POST["scholarship"];
            $data['deadline_for_applications'] = $_POST["deadline_for_applications"];
            $data['application_requirements'] = $_POST["application_requirements"];
            $data['school_environment'] = $_POST["school_environment"];
            $data['country_id'] = $_POST["country_id"];
            $data['undergraduate_majors'] = $_POST["undergraduate_majors"];
            $data['graduate_majors'] = $_POST["graduate_majors"];
            $pic_id = $_POST["logo_url"];
            if(!empty($pic_id)){
                A('Organization')->uploadLogoPicToOSS($pic_id);
                $data['logo_url'] = A('Organization')->fetchCdnImage($pic_id);
            }
            if(empty($uid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/overseas/university');
            } else {
                $model->where('id='.$uid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/overseas/university');
            }
        } else {
            $this->display('university_add');
        }
    }

    public function university_add(){
        $majorModel = M('AbroadUniversityMajors');
        $countryModel = M('AbroadCountry');
        $country_list = $countryModel->field('id, name')->where('status=1')->select();
        $undergraduate_majors = $majorModel->field('id, name')->where('status=1 and type=1')->select();
        $graduate_majors = $majorModel->field('id, name')->where('status=1 and type=2')->select();
        $this->assign('_country', $country_list);
        $this->assign('_undergraduate_majors', $undergraduate_majors);
        $this->assign('_graduate_majors', $graduate_majors);
        $this->display('university_add');
    }

    public function university_set_hot($id){
        if(!empty($id)){
            $model = M('AbroadUniversity');
            $data['is_hot'] = 1;
            $model->where('id='.$id)->save($data);
            $this->success('处理成功','index.php?s=/admin/overseas/university');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function cancle_university_hot($id, $is_hot=0)
    {
        if (!empty($id)) {
            $model = M('AbroadUniversity');
            $data['is_hot'] = $is_hot;
            if (is_array($id)) {
                foreach ($id as $i) {
                    $model->where('id=' . $i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id=' . $id)->save($data);
            }
            $this->success('处理成功', 'index.php?s=/admin/overseas/university');
        }
        $this->error('未选择要处理的数据');
    }

    /**
     * @param $id
     */
    public function undoSetHot($id){
        if(!empty($id)){
            $model = M('Organization');
            $data['is_hot'] = 0;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('取消成功','index.php?s=/admin/overseas/org_list');
        } else {
            $this->error('未选择要操作的数据');
        }
    }

    public function university_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('AbroadUniversity');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $majorModel = M('AbroadUniversityMajors');
        $undergraduate_majors_list = $majorModel->field('id, name')->where('status=1 and type=1')->select();
        $graduate_majors_list = $majorModel->field('id, name')->where('status=1 and type=2')->select();
        $undergraduate_majors = explode("#",$data['undergraduate_majors']);
        $graduate_majors = explode("#",$data['graduate_majors']);
        foreach($undergraduate_majors_list as &$all_major){
            $is_exist = false;
            if(in_array($all_major['id'], $undergraduate_majors)){
                $is_exist = true;
            }
            if(!$is_exist){
                $all_major['ischecked'] = 0;
            }else{
                $all_major['ischecked'] = 1;
            }
        }
        foreach($graduate_majors_list as &$all_major){
            $is_exist = false;
            if(in_array($all_major['id'], $graduate_majors)){
                $is_exist = true;
            }
            if(!$is_exist){
                $all_major['ischecked'] = 0;
            }else{
                $all_major['ischecked'] = 1;
            }
        }
        $countryModel = M('AbroadCountry');
        $country_list = $countryModel->field('id, name')->where('status=1')->select();
        $this->assign('_country', $country_list);
        $this->assign('_undergraduate_majors', $undergraduate_majors_list);
        $this->assign('_graduate_majors', $graduate_majors_list);
        $this->assign('university', $data);
        $this->meta_title = '编辑大学';
        $this->display();
    }

    public function photo(){
        $model = M('AbroadUniversity');
        $photoModel = M('AbroadUniversityPhotos');
        $count = $photoModel->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $photoModel->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as &$photo){
            $university_id = $photo['university_id'];
            $university_info = $model->field('name')->where('id='.$university_id)->find();
            $photo['university'] = $university_info['name'];
        }
        $university_id = I('university_id');
        $university_name = I('university_name');
        if($university_id){
            $this->assign('university_id', $university_id);
            $this->assign('university_name', $university_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title", "大学相册列表");
        $this->display();
    }

    public function photo_add(){
        if(I('university_id')){
            $this->assign('university_id', I('university_id'));
            $this->assign('university_name', I('university_name'));
        }

        $this->display();
    }

    public function photo_edit($id){
        if(I('university_id')){
            $this->assign('university_id', I('university_id'));
            $this->assign('university_name', I('university_name'));
        }
        $photo = M('AbroadUniversityPhotos')->where('id='.$id)->find();
        $this->assign('info', $photo);
        $this->display();
    }

    public function photo_update(){
        if (IS_POST) { //提交表单
            $model = M('AbroadUniversityPhotos');
            $uid = $_POST['pid'];
            $university_name = $_POST["university_name"];
            $data['descript'] = $_POST["descript"];
            $data['university_id'] = $_POST["university_id"];
            $pic_id = $_POST["pic_url"];
            if(is_numeric($pic_id)){
                A('Organization')->uploadLogoPicToOSS($pic_id);
                $data['pic_url'] = A('Organization')->fetchCdnImage($pic_id);
            }else{
                $data['pic_url'] = $pic_id;
            }
            if(empty($uid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/overseas/photo&university_id='.$data['university_id'].'&university_name='.$university_name);
            } else {
                $model->where('id='.$uid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/overseas/photo&university_id='.$data['university_id'].'&university_name='.$university_name);
            }
        } else {
            $this->display('photo_add');
        }
    }

    public function photo_set_status($id, $status=-1)
    {
        if (!empty($id)) {
            $model = M('AbroadUniversityPhotos');
            $data['status'] = $status;
            if (is_array($id)) {
                foreach ($id as $i) {
                    $model->where('id=' . $i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id=' . $id)->save($data);
            }
            $this->success('处理成功', 'index.php?s=/admin/overseas/photo');
        } else {
            $this->error('未选择要处理的数据');
        }
    }
}
