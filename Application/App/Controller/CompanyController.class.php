<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Controller;
use Addons\Avatar\AvatarAddon;
use Think\Hook;

/**
 * 公司相关接口
 * Class CompanyController
 * @package App\Controller
 */
class CompanyController extends AppController {

    /**
     * 公司列表
     */
    public function alllist(){
        $model = D('Admin/Company');
        $result = $model->where('status<>-1')->order('scale desc')->select();
        $cmodel = D('CompanyConfig');
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
            }
        }
        $extra['totalCount'] = count($result);
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