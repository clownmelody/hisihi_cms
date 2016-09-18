<?php

namespace Admin\Controller;

use Think\Page;

/**
 * 机构活动模块
 * Class PromotionController
 * @package Admin\Controller
 */
class PromotionController extends AdminController
{

    public function index(){
        $model = M('Promotion');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","活动列表");
        $this->display();
    }

    public function promotion_add(){
        $this->display('promotion_add');
    }

    public function update(){
        if (IS_POST) { //提交表单
            $model = M('Promotion');
            $cid = $_POST['cid'];
            $data['title'] = $_POST["title"];
            $data['description'] = $_POST["description"];
            $logo_id = $_POST["logo_url"];
            $little_logo_id = $_POST["little_logo_url"];
            $tag_id = $_POST["tag_url"];
            if(!empty($logo_id)){
                A('Organization')->uploadLogoPicToOSS($logo_id);
                $data['logo_url'] = A('Organization')->fetchCdnImage($logo_id);
            }
            if(!empty($little_logo_id)){
                A('Organization')->uploadLogoPicToOSS($little_logo_id);
                $data['little_logo_url'] = A('Organization')->fetchCdnImage($little_logo_id);
            }
            if(!empty($tag_id)){
                A('Organization')->uploadLogoPicToOSS($tag_id);
                $data['tag_url'] = A('Organization')->fetchCdnImage($tag_id);
            }
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/promotion/index');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/promotion/index');
            }
        } else {
            $this->display('promotion_add');
        }
    }

    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('Promotion');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('promotion', $data);
        $this->meta_title = '编辑活动';
        $this->display();
    }

    public function set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('Promotion');
            $tcopr_model = M('TeachingCourseOrganizationPromotionRelation');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                    if($status==-1){
                        $tcopr_model->where('promotion_id='.$i)->save(array('status'=>-1));
                    }
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
                if($status==-1){
                    $tcopr_model->where('promotion_id='.$id)->save(array('status'=>-1));
                }
            }
            $this->success('处理成功','index.php?s=/admin/promotion/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function add_coupon($id){
        $coupon = M('Coupon')->where('status=1')->order('id')->select();
        $this->assign('_coupon', $coupon);
        $this->assign('id', $id);
        $this->display();
    }

    public function update_add_coupon(){
        if (IS_POST) {
            $post_data['promotion_id'] = $_POST["promotion_id"];
            $post_data['coupon_id'] = $_POST["coupon_id"];
            $post_data['status'] = 1;
            if(M('PromotionCouponRelation')->where($post_data)->find()){
                $this->success('已经添加过该优惠券', 'index.php?s=/admin/promotion/index');
            }
            $post_data['create_time'] = time();
            M('PromotionCouponRelation')->add($post_data);
            $this->success('加入优惠券成功', 'index.php?s=/admin/promotion/index');
        } else {
            $this->display('add_coupon');
        }
    }

    public function org_to_promotion(){
        $model = M('TeachingCourseOrganizationPromotionRelation');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as &$info){
            $org = M('Organization')->field('name')->where('id='.$info['organization_id'])->find();
            $info['organization_name'] = $org['name'];
            $pro = M('Promotion')->field('title')->where('id='.$info['promotion_id'])->find();
            $info['promotion_name'] = $pro['title'];
            $course = M('OrganizationTeachingCourse')->field('course_name')->where('id='.$info['teaching_course_id'])->find();
            $info['course_name'] = $course['course_name'];
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构参与活动列表");
        $this->display();
    }

    public function teaching_course_to_coupon(){
        $model = M('TeachingCourseCouponRelation');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as &$info){
            $pro = M('OrganizationTeachingCourse')->field('course_name')->where('id='.$info['teaching_course_id'])->find();
            $info['teaching_course_name'] = $pro['course_name'];
            $org = M('Coupon')->field('name')->where('id='.$info['coupon_id'])->find();
            $info['coupon_name'] = $org['name'];
            $gift = M('OrganizationGiftPackage')->field('introduce')->where('id='.$info['gift_package_id'])->find();
            if($gift){
                $info['gift_package'] = $gift['introduce'];
            }else{
                $info['gift_package'] = '无';
            }
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","课程优惠券列表");
        $this->display();
    }

    public function teaching_course_to_rebate(){
        $model = M('TeachingCourseRebateRelation');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as &$info){
            $pro = M('OrganizationTeachingCourse')->field('course_name')->where('id='.$info['teaching_course_id'])->find();
            $info['teaching_course_name'] = $pro['course_name'];
            $rebate = M('Rebate')->field('name')->where('id='.$info['rebate_id'])->find();
            $info['rebate_name'] = $rebate['name'];
            $gift = M('OrganizationGiftPackage')->field('introduce')->where('id='.$info['gift_package_id'])->find();
            if($gift){
                $info['gift_package'] = $gift['introduce'];
            }else{
                $info['gift_package'] = '无';
            }
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","课程抵扣券列表");
        $this->display();
    }

    public function set_org_promotion_status($id, $status=-1){
        if(!empty($id)){
            $model = M('TeachingCourseOrganizationPromotionRelation');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/promotion/org_to_promotion');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function set_teaching_course_coupon_status($id, $status=-1){
        if(!empty($id)){
            $model = M('TeachingCourseCouponRelation');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/promotion/teaching_course_to_coupon');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function set_teaching_course_rebate_status($id, $status=-1){
        if(!empty($id)){
            $model = M('TeachingCourseRebateRelation');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/promotion/teaching_course_to_rebate');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

}
