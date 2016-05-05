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

    /**
     * @param $id
     */
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
        $countryModel = M('AbroadCountry');
        $country_list = $countryModel->field('id, name')->where('status=1')->select();
        $this->assign('_country', $country_list);
        $this->display('university_add');
    }

}
