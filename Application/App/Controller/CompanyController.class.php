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
     * @param int $page
     * @param int $count
     *
     */
    public function alllist($uid=0, $page=1, $count=5, $id=0, $name=''){
        if (!$uid) {
            //$this->requireLogin();
            $uid = $this->getUid();
        }
        $model = D('Admin/Company');
        if($id == 0){
            $totalCount = $model->where("status<>-1 and (`name` like '%".$name."%' or fullname like '%".$name."%')")->count();
            $result = $model->where("status<>-1 and (`name` like '%".$name."%' or fullname like '%".$name."%')")
                ->order('scale desc')->page($page, $count)->select();
        }else{
            $totalCount = $model->where("status<>-1 and (filtrate_mark like '".$id."' or filtrate_mark like '".$id."#%'
                or filtrate_mark like '%#".$id."' or filtrate_mark like '%#".$id."#%')
                and (`name` like '%".$name."%' or fullname like '%".$name."%')")
                ->count();
            $result = $model->where("status<>-1 and (filtrate_mark like '".$id."' or filtrate_mark like '".$id."#%'
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
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $result;
        $this->apiSuccess('获取公司列表成功', null, $extra);
    }

    /**
     * 获取公司详细信息
     * @param $id
     */
    public function info($id){
        if(empty($id)){
            $this->apiError(-1, "传入参数不能为空");
        }
        $model = D('Admin/Company');
        $result = $model->where('status<>-1 and id='.$id)->find();
        $cmodel = D('CompanyConfig');
        if($result){
            $result['picture'] = $this->fetchImage($result['picture']);

            $scale = $cmodel->where('type=2 and status=1 and value='.$result['scale'])->getField("value_explain");
            $result['scale'] = $scale;

            $markarray = explode('#',$result['marks']);
            $map['id'] = array('in',$markarray);
            $marks = $cmodel->where($map)->where('type=1 and status=1')->getField("value",true);
            $result['marks'] = $marks;

            $filtrate_array = explode('#',$result['filtrate_mark']);
            $fmap['id'] = array('in',$filtrate_array);
            $fmarks = $cmodel->where($fmap)->where('type=8 and status=1')->getField("value",true);
            $result['filtrate_mark'] = $fmarks;
        }
        $extra['data'] = $result;
        $this->apiSuccess('获取公司信息成功', null, $extra);
    }

    /**
     * 获取公司招聘信息
     * @param $id
     */
    public function recruits($id){
        if(empty($id)){
            $this->apiError(-1, "传入参数不能为空");
        }
        $model = D('CompanyRecruit');
        $result = $model->field('company_id, job, salary, requirement, skills,create_time,end_time')->where('status<>-1 and company_id='.$id)
            ->order('create_time desc')->select();
        $cmodel = D('CompanyConfig');
        foreach($result as &$recruit){
            $salary = $cmodel->where('type=4 and status=1 and value='.$recruit['salary'])->getField("value_explain");
            $recruit['salary'] = $salary;

            $markarray = explode('#',$recruit['requirement']);
            $map['id'] = array('in',$markarray);
            $marks = $cmodel->where($map)->where('type=3 and status=1')->getField("value",true);
            $recruit['requirement'] = $marks;
        }

        $extra['totalCount'] = count($result);
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
            $this->apiError(-1, '传入公司id为空');
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
        $pdfUtils = new PdfUtils();
        $path = $pdfUtils->init($uid);
        $emailUtils = new EmailUtils();
        $model = M();
        $result = $model->query("select hr_email from hisihi_company where status=1 and id=".$companyId.' limit 1');
        if(empty($result[0]['hr_email'])){
            $this->apiError(-1, "该公司未填写HR邮箱");
        }
        $email = $result[0]['hr_email'];
        if($emailUtils->sendMail($email, $path)){
            $model = D('User/ResumeDelivery');
            $data['uid'] = $uid;
            $data['company_id'] = $companyId;
            $data['job_id'] = 0;
            $data['create_time'] = time();
            $model->save($data);
            $this->apiSuccess("简历投递成功");
        } else {
            $this->apiError(-1, "简历投递失败");
        }
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

}