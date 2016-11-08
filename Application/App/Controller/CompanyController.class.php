<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Controller;
use Addons\Avatar\AvatarAddon;
use Addons\Email\EmailUtils;
use Addons\Pdf\PdfUtils;
use Think\Hook;

/**
 * 公司相关接口
 * Class CompanyController
 * @package App\Controller
 */
class CompanyController extends AppController {

    /**
     * 公司列表
     * @param int $uid
     * @param int $page
     * @param int $count
     * @param int $id
     * @param string $name
     * @param null $version
     */
    public function alllist($uid=0, $page=1, $count=5, $id=0, $name='',$version=null){
        if (!$uid) {
            //$this->requireLogin();
            $uid = $this->getUid();
        }
        $model = D('Admin/Company');
        if($id == 0){
            $totalCount = $model->where("status<>-1 and (`name` like '%".$name."%' or fullname like '%".$name."%')")->count();
            if($page==1&&$count==4){
                $result = $model->field("id,name,city,slogan,introduce,filtrate_mark,marks,scale,website,fullname,location,picture,hr_email")
                    ->where("status=1")
                    ->order('scale desc')->page($page, 30)->select();
                $dyna_result = array();
                $ccount = count($result);
                $numbers = range(0, $ccount);
                shuffle ($numbers);
                $tem_array = array_slice($numbers,0, 4);
                for($i=0; $i<4; $i++){
                    $random_id = $tem_array[$i];
                    if($random_id==30){
                        $random_id = 29;
                    }
                    $dyna_result[] = $result[$random_id];
                }
                $result = $dyna_result;
            } else {
                $result = $model->field("id,name,city,slogan,introduce,filtrate_mark,marks,scale,website,fullname,location,picture,hr_email")
                    ->where("status<>-1 and (`name` like '%".$name."%' or fullname like '%".$name."%')")
                    ->order('scale desc')->page($page, $count)->select();
            }
        }else{
            $totalCount = $model->where("status<>-1 and (filtrate_mark like '".$id."' or filtrate_mark like '".$id."#%'
                or filtrate_mark like '%#".$id."' or filtrate_mark like '%#".$id."#%')
                and (`name` like '%".$name."%' or fullname like '%".$name."%')")
                ->count();
            $result = $model->field("id,name,city,slogan,introduce,filtrate_mark,marks,scale,website,fullname,location,picture,hr_email")
                ->where("status<>-1 and (filtrate_mark like '".$id."' or filtrate_mark like '".$id."#%'
                or filtrate_mark like '%#".$id."' or filtrate_mark like '%#".$id."#%')
                and (`name` like '%".$name."%' or fullname like '%".$name."%')")
                ->order('scale desc')->page($page, $count)->select();
        }
        $cmodel = D('CompanyConfig');
        $rmodel = D('User/ResumeDelivery');
        if($result){
            foreach($result as &$company){
                $company['picture'] = $this->fetchImage($company['picture']);
                $scale = $cmodel->where('type=2 and status=1 and value='.$company['scale'])->getField("value_explain");
                $company['scale'] = $scale;

                $mark = explode("#",$company['marks']);
                $markarray = array();
                foreach($mark as &$markid){
                    $markarr = $cmodel->field('id,value')->where('status=1 and id='.$markid)->select();
                    $markobj = array();
                    $markobj = (object)$markobj;
                    $markobj->id = $markarr['0']['id'];
                    $markobj->value = $markarr['0']['value'];
                    array_push($markarray,$markobj);
                }
                $company['marks'] = $markarray;

                $filtrate_mark = explode("#",$company['filtrate_mark']);
                $filtrate_array = array();
                foreach($filtrate_mark as &$markid){
                    $markarr = $cmodel->field('id,value')->where('status=1 and id='.$markid)->select();
                    $markobj = array();
                    $markobj = (object)$markobj;
                    $markobj->id = $markarr['0']['id'];
                    $markobj->value = $markarr['0']['value'];
                    array_push($filtrate_array,$markobj);
                }
                $company['filtrate_mark'] = $filtrate_array;
                $company['is_delivery'] = false;
                $isdelivery = $rmodel->where('status=1 and uid='.$uid.' and company_id='.$company['id'])->select();
                if($isdelivery){
                    $company['is_delivery'] = true;
                }
            }
        }
        if((float)$version>=2.2){
            $visit_count = M('CompanyConfig')->where('type=9 and status=1')->getField('value');
            $extra['allCount'] = $visit_count;
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $result;
        $this->apiSuccess('获取公司列表成功', null, $extra);
    }

    /**
     * 获取公司详细信息
     * @param $uid
     * @param $id
     */
    public function info($uid=0,$id=0){
        if (!$uid) {
            $uid = $this->getUid();
        }
        if(!$id){
            $this->apiError(-1, "传入参数不能为空");
        }
        $model = D('Admin/Company');
        $result = $model->field("id,name,city,slogan,introduce,filtrate_mark,marks,scale,website,fullname,location,picture,hr_email")
            ->where('status<>-1 and id='.$id)->find();
        $cmodel = D('CompanyConfig');
        if($result){
            $result['picture'] = $this->fetchImage($result['picture']);

            $scale = $cmodel->where('type=2 and status=1 and value='.$result['scale'])->getField("value_explain");
            $result['scale'] = $scale;

            $mark = explode('#',$result['marks']);
            $markarray = array();
            foreach($mark as &$markid){
                $markarr = $cmodel->field('id,value')->where('status=1 and id='.$markid)->select();
                $markobj = array();
                $markobj = (object)$markobj;
                $markobj->id = $markarr['0']['id'];
                $markobj->value = $markarr['0']['value'];
                array_push($markarray,$markobj);
            }
            $result['marks'] = $markarray;

            $filtrate_mark = explode("#",$result['filtrate_mark']);
            $filtrate_array = array();
            foreach($filtrate_mark as &$markid){
                $markarr = $cmodel->field('id,value')->where('status=1 and id='.$markid)->select();
                $markobj = array();
                $markobj = (object)$markobj;
                $markobj->id = $markarr['0']['id'];
                $markobj->value = $markarr['0']['value'];
                array_push($filtrate_array,$markobj);
            }
            $result['filtrate_mark'] = $filtrate_array;
            $rmodel = D('User/ResumeDelivery');
            $result['is_delivery'] = false;
            $isdelivery = $rmodel->where('status=1 and uid='.$uid.' and company_id='.$id)->select();
            if($isdelivery){
                $result['is_delivery'] = true;
            }
        }else{
            $this->apiError(-1, "该公司不存在");
        }
        $cmodel->where('status=1 and type=9')->setInc('value', 1);
        $extra['data'] = $result;
        $this->apiSuccess('获取公司信息成功', null, $extra);
    }

    /**
     * @param $id
     * @param float $version
     * @param int $page
     * @param int $count
     */
    public function recruits($id, $version=1.0, $page=1, $count=5){
        if(empty($id)){
            $this->apiError(-1, "传入参数不能为空");
        }
        $model = D('CompanyRecruit');
        $totalCount = $model->where(array('company_id'=>$id,'status'=>1))->count();
        $result = $model->field('id, job, salary, requirement, skills,work_city,create_time,
        end_time,education,work_experience')
            ->where('status<>-1 and company_id='.$id)
            ->order('create_time desc')->page($page, $count)->select();
        $cmodel = D('CompanyConfig');
        foreach($result as &$recruit){
            $salary = $cmodel->where('type=4 and status=1 and value='.$recruit['salary'])->getField("value_explain");
            $recruit['salary'] = $salary;
            $mark = explode('#',$recruit['requirement']);
            $markarray = array();
            foreach($mark as &$markid){
                $markarr = $cmodel->field('id,value')->where('status=1 and id='.$markid)->select();
                $markobj = array();
                $markobj = (object)$markobj;
                $markobj->id = $markarr['0']['id'];
                $markobj->value = $markarr['0']['value'];
                array_push($markarray,$markobj);
            }
            $recruit['requirement'] = $markarray;
            if((float)$version<3.3){
                unset($recruit["education"]);
                unset($recruit["work_experience"]);
            } else {
                unset($recruit["requirement"]);
            }
        }
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $result;
        $this->apiSuccess('获取公司招聘信息成功', null, $extra);
    }

    /**
     * 获取用户资料中的职位名称
     */
    public function jobNameList(){
        $cmodel = D('Admin/CompanyConfig');
        $jobList = $cmodel->field('id, value')->where('type=5 and status=1')->select();
        $extra['data'] = $jobList;
        $this->apiSuccess('获取职位名称列表成功', null, $extra);
    }

    /**
     * 获取软件技能列表
     */
    public function softwareSkillsList(){
        $cmodel = D('Admin/CompanyConfig');
        $jobList = $cmodel->field('id, value')->where('type=6 and status=1')->select();
        $extra['data'] = $jobList;
        $this->apiSuccess('获取软件技能列表成功', null, $extra);
    }

    /**
     * 获取亮点列表
     */
    public function highlightsList(){
        $cmodel = D('Admin/CompanyConfig');
        $jobList = $cmodel->field('id, value')->where('type=7 and status=1')->select();
        $extra['data'] = $jobList;
        $this->apiSuccess('获取个人亮点列表成功', null, $extra);
    }

    /**
     * 获取公司筛选标签列表
     */
    public function companyFiltrateList(){
        $cmodel = D('Admin/CompanyConfig');
        $jobList = $cmodel->field('id, value')->where('type=8 and status=1')->select();
        $extra['data'] = $jobList;
        $this->apiSuccess('获取公司筛选标签成功', null, $extra);
    }

    /**
     * 用户向公司投递简历
     * @param int $uid
     * @param int $companyId
     */
    public function sendResume($uid=0, $companyId=0){
        if(!$companyId){
            $this->apiError(-3, '传入公司id为空');
        }
        if (!$uid) {
            $this->requireLogin();
            $uid = $this->getUid();
        }

        $resumeModel = D('User/ResumeDelivery');
        $is_delivery = $resumeModel->where('status=1 and uid='.$uid.' and company_id='.$companyId)->select();
        if($is_delivery){
            $this->apiError(-1, '该公司已经投递过了');
        }

        //判断简历信息是否完整
        $this->isResumeComplete($uid);

        $pdfUtils = new PdfUtils();
        $returnData = $pdfUtils->init($uid);
        $emailUtils = new EmailUtils();
        $model = M();
        $result = $model->query("select hr_email, name from hisihi_company where status=1 and id=".$companyId.' limit 1');
        if(empty($result[0]['hr_email'])){
            $this->apiError(-4, "该公司未填写HR邮箱");
        }
        $email = $result[0]['hr_email'];
        $returnData['company_name'] = $result[0]['name'];
        if($emailUtils->sendMail(null, $returnData)){//简历发送至嘿设汇指定邮箱
            $model = D('User/ResumeDelivery');
            $data['uid'] = $uid;
            $data['company_id'] = $companyId;
            $data['job_id'] = 0;
            $data['create_time'] = time();
            $model->save($data);
            $this->apiSuccess("简历投递成功");
        } else {
            $this->apiError(-5, "简历投递失败");
        }
    }

    public function isUserInfoComplete($uid=0){
        if (!$uid) {
            $this->requireLogin();
            $uid = $this->getUid();
        }
        //个人信息
        $user1 = D('Home/Member')->where(array('uid'=>$uid))->field('nickname,sex,birthday')->find();
        foreach($user1 as $key=>$value){
            $extra['isComplete'] = false;
            if($key == 'nickname' && !$value){
                $this->apiSuccess("您的信息不完整，'姓名'未填写",null,$extra);
            }
            if($key == 'sex' && !$value){
                $this->apiSuccess("您的信息不完整，'性别'未填写",null,$extra);
            }
            if($key == 'birthday' && (!$value || $value == '0000-00-00')){
                $this->apiSuccess("您的信息不完整，'生日'未填写",null,$extra);
            }
        }
        $user2 = D('User/UcenterMember')->where(array('id' => $uid))->field('email,mobile')->find();
        foreach($user2 as $key=>$value){
            $extra['isComplete'] = false;
            if($key == 'mobile'&& !$value){
                $this->apiSuccess("您的信息不完整，'手机号'未填写",null,$extra);
            }
            if($key == 'email' && !$value){
                $this->apiSuccess("您的信息不完整，'邮箱'未填写",null,$extra);
            }
        }
        // 扩展信息
        $info_list =  D('field')->where('uid='.$uid)->field('field_id')->select();
        $map['status'] = 1;
        $map['visiable'] = 1;
        $map['id'] = array('in',array('36','37','43','44','45','46'));
        $field_setting_list = D('field_setting')->field('id, input_tips')
            ->where($map)->order('sort asc')->select();
        foreach ($field_setting_list as $val) {
            $hasvalue = false;
            $valid = $val['id'];
            foreach ($info_list as $key){
                $keyid = $key['field_id'];
                if($keyid == $valid){
                    $hasvalue = true;
                }
            }
            if(!$hasvalue){
                $extra['isComplete'] = false;
                $this->apiSuccess("您的信息不完整，'".$val['input_tips']."'未填写",null,$extra);
            }
        }
        $extra['isComplete'] = true;
        $this->apiSuccess("信息已填写完整",null,$extra);
    }

    private function fetchImage($pic_id)
    {
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
                $picUrl = "http://hisihi-other.oss-cn-qingdao.aliyuncs.com/".$objKey;
            }
        }
        return $picUrl;
    }

    /**判断简历信息是否完整
     * @param $uid
     */
    private function isResumeComplete($uid){
        //个人信息
        $user1 = D('Home/Member')->where(array('uid'=>$uid))->field('nickname,sex,birthday')->find();
        foreach($user1 as $key=>$value){
            $extra['isComplete'] = false;
            if($key == 'nickname' && !$value){
                $this->apiSuccess("您的信息不完整，'姓名'未填写",null,$extra);
            }
            if($key == 'sex' && !$value){
                $this->apiSuccess("您的信息不完整，'性别'未填写",null,$extra);
            }
            if($key == 'birthday' && (!$value || $value == '0000-00-00')){
                $this->apiSuccess("您的信息不完整，'生日'未填写",null,$extra);
            }
        }
        $user2 = D('User/UcenterMember')->where(array('id' => $uid))->field('email,mobile')->find();
        foreach($user2 as $key=>$value){
            $extra['isComplete'] = false;
            if($key == 'mobile'&& !$value){
                $this->apiSuccess("您的信息不完整，'手机号'未填写",null,$extra);
            }
            if($key == 'email' && !$value){
                $this->apiSuccess("您的信息不完整，'邮箱'未填写",null,$extra);
            }
        }
        // 扩展信息
        $info_list =  D('field')->where('uid='.$uid)->field('field_id')->select();
        $map['status'] = 1;
        $map['visiable'] = 1;
        $map['id'] = array('in',array('36','37','43','44','45','46'));
        $field_setting_list = D('field_setting')->field('id, input_tips')
            ->where($map)->order('sort asc')->select();
        foreach ($field_setting_list as $val) {
            $hasvalue = false;
            $valid = $val['id'];
            foreach ($info_list as $key){
                $keyid = $key['field_id'];
                if($keyid == $valid){
                    $hasvalue = true;
                }
            }
            if(!$hasvalue){
                $this->apiError(-2, "您的简历不完整，".$val['input_tips']."未填写");
            }
        }
    }

    public function getJobTitleList(){
        $model = M("JobTitleList");
        $visual_design_list = $model->field("name")->where("type=1 and status=1")->select();
        $top_title_list = $model->field("name")->where("type=2 and status=1")->select();
        $extra["visual_design_list"] = $visual_design_list;
        $extra["top_title_list"] = $top_title_list;
        $this->apiSuccess("获取列表成功",null, $extra);
    }

    /**
     * @param null $city
     * @param null $job_name
     * @param string $education
     * @param string $type_of_job
     * @param string $work_experience
     * @param string $salary
     * @param string $industry
     * @param string $scale
     * @param int $page
     * @param int $count
     */
    public function jobList($city=null, $job_name=null, $education='全部', $type_of_job='全部', $work_experience='全部',
                            $salary='全部', $industry='全部', $scale='全部', $page=1, $count=10){
        $model = M("CompanyRecruit");
        $companyModel = M("Company");
        $data["status"] = 1;
        if(empty($job_name)){
            $data["job"] = array("like", '%'.$job_name.'%');
        }
        if(!empty($city)){
            $data["work_city"] = array("like", '%'.$city.'%');
        }
        if($education!='全部'){
            $data["education"] = array("like", '%'.$education.'%');
        }
        if($type_of_job!='全部'){
            $data["type_of_job"] = array("like", '%'.$type_of_job.'%');
        }
        if($work_experience!='全部'){
            $data["work_experience"] = array("like", '%'.$work_experience.'%');
        }
        if($salary!='全部'){
            $salaryList =$this->getSalaryIdList($salary);
            $data["salary"] = array("in", $salaryList);
        }
        if($scale!='全部'){
            $list = $this->getCompanyIdList($scale);
            $data["company_id"] = array("in", $list);
        }
        if($industry!='全部'){
            $data["industry"] = array("like", '%'.$industry.'%');
        }
        $total_count = $model->where($data)->count();
        $list = $model->field("id, company_id, job, salary, work_experience, work_city, education, type_of_job")
            ->where($data)
            ->page($page, $count)
            ->order("create_time desc")
            ->select();
        foreach($list as &$item){
            $companyInfo = $companyModel->field("name, picture")->where("id=".$item["company_id"])->find();
            $companyInfo["picture"] = $this->fetchImage($companyInfo["picture"]);
            $companyInfo["id"] = $item["company_id"];
            $item["companyInfo"] = $companyInfo;
            unset($item["company_id"]);
        }
        $extra["total_count"] = $total_count;
        $extra["data"] = $list;
        $this->apiSuccess("获取职位列表成功",null, $extra);
    }

    private function getSalaryIdList($salary){
        $cmodel = M('CompanyConfig');
        $salaryList = $cmodel->field("value")->where('type=4 and status=1 and value_explain like %'.$salary.'%')->select();
        if(!empty($salaryList)) {
            foreach ($salaryList as $salary) {
                $idList[] = $salary['value'];
            }
            return $idList;
        } else {
            return null;
        }
    }

    private function getCompanyIdList($scale){
        $cmodel = M('CompanyConfig');
        $model = M('Company');
        $scaleList = $cmodel->field("value")->where('type=2 and status=1 and value_explain like %'.$scale.'%')->select();
        if(!empty($scaleList)){
            foreach($scaleList as $scale){
                $idList[] = $scale['value'];
            }
            $data["scale"] = array("in", $idList);
            $idList = $model->field('id')->where($data)->select();
            if(!empty($idList)){
                foreach($idList as $item){
                    $result[] = $item["id"];
                }
                return $result;
            }
        } else {
            return null;
        }
    }


    public function jobDetail($id=0){
        $model = M("CompanyRecruit");
        $companyModel = M("Company");
        $cmodel = M("CompanyConfig");
        $info = $model->field("id, company_id, job, salary, requirement, work_city, skills, create_time, end_time,
        education, type_of_job, work_experience, description, work_location")
            ->where("id=".$id)->find();
        $companyInfo = $companyModel->field("name, picture, website, industry, scale")->where("id=".$info["company_id"])->find();
        $companyInfo["picture"] = $this->fetchImage($companyInfo["picture"]);
        $companyInfo["scale"] = $cmodel->where('type=2 and status=1 and value='.$companyInfo['scale'])->getField("value_explain");
        $info["companyInfo"] = $companyInfo;
        unset($info["company_id"]);
        $extra["data"] = $info;
        $this->apiSuccess("获取职位详情成功",null, $extra);
    }

    public function companyList($uid=0, $page=1, $count=10){
        if (!$uid) {
            $uid = $this->getUid();
        }
        $userJobIntentionModel = M("UserJobIntension");
        $companyModel = M("Company");
        $cmodel = M("CompanyConfig");
        $recruitModel = M("CompanyRecruit");
        $info = $userJobIntentionModel->field("position_applied")->where("uid=".$uid)->find();
        if(!empty($info)){
            $user_position_applied = $info["position_applied"];
            $sel_data["job"] = array("like", '%'.$user_position_applied.'%');
            $companyList = $recruitModel->field("company_id")->where($sel_data)->select();
            foreach($companyList as $company){
                $companyIdList[] = $company["company_id"];
            }
            if(!empty($companyIdList)){
                $data['id'] = array("in", $companyIdList);
            }
        }
        $data["status"] = 1;
        $total_count = $companyModel->where($data)->count();
        $list = $companyModel->field("id, name, city, scale, picture, industry")
            ->where($data)
            ->page($page, $count)
            ->order("create_time desc")
            ->select();
        foreach($list as &$item){
            $item["scale"] = $cmodel->where('type=2 and status=1 and value='.$item['scale'])
                ->getField("value_explain");
            $item["picture"] = $this->fetchImage($item["picture"]);
            if(!empty($info)){
                $user_position_applied = $info["position_applied"];
                $item["hot_job"] = $this->getHotJobListByUser($user_position_applied, $item['id']);
            } else {
                $item["hot_job"] = null;
            }
        }
        $extra["total_count"] = $total_count;
        $extra["data"] = $list;
        $this->apiSuccess("获取公司列表成功",null, $extra);
    }

    private function getHotJobListByUser($job_applied=null, $company_id=0){
        if(empty($job_applied)||empty($company_id)){
            return null;
        }
        $recruitModel = M("CompanyRecruit");
        $sel_data["job"] = array("like", '%'.$job_applied.'%');
        $sel_data["company_id"] = $company_id;
        $list = $recruitModel->field("job")->where($sel_data)->select();
        $data = array();
        foreach($list as $item){
            $data[] = $item['job'];
        }
        return $data;
    }

    public function companyDetail($id=0){
        $companyModel = M("Company");
        $cmodel = M("CompanyConfig");
        $companyInfo = $companyModel->field("id, name, city, slogan, introduce, filtrate_mark, marks, scale, website,
        fullname, location, picture, industry, product_description")->where("id=".$id)->find();
        $companyInfo["scale"] = $cmodel->where('type=2 and status=1 and value='.$companyInfo['scale'])
            ->getField("value_explain");
        $companyInfo["picture"] = $this->fetchImage($companyInfo["picture"]);
        $extra["data"] = $companyInfo;
        $this->apiSuccess("获取公司详情成功",null, $extra);

    }

    public function search($type=1, $key_word=null){
        return null;
    }

}