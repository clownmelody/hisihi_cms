<?php

namespace Admin\Controller;

use Think\Page;

/**
 * 优惠券模块
 * Class CouponController
 * @package Admin\Controller
 */
class CouponController extends AdminController
{

    public function index(){
        $model = M('Coupon');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","优惠券列表");
        $this->display();
    }

    public function coupon_add(){
        $this->display('coupon_add');
    }

    public function update(){
        if (IS_POST) { //提交表单
            $model = M('Coupon');
            $cid = $_POST['cid'];
            $data['name'] = $_POST["name"];
            $data['money'] = $_POST["money"];
            $data['start_time'] = strtotime($_POST["start_time"]);
            $data['end_time'] = strtotime($_POST["end_time"]);
            $data['service_condition'] = $_POST["service_condition"];
            $data['using_method'] = $_POST['using_method'];
            $data['instructions_for_use'] = $_POST['instructions_for_use'];
            if(empty($data['service_condition'])){
                $data['service_condition'] = "";
            }
            if(empty($data['using_method'])){
                $data['using_method'] = "结算时手机出示此优惠券，请商家扫描二维码或输入号码，待验证成功后，即成功使用";
            }
            if(empty($data['instructions_for_use'])){
                $data['instructions_for_use'] = "本券限现场使用，每次限用一张；本券不可兑换现金";
            }
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/coupon/index');
            } else {
                $model->where('id='.$cid)->save($data);
                $updateData['service_condition'] = $data['service_condition'];
                $updateData['using_method'] = $data['using_method'];
                $updateData['instructions_for_use'] = $data['instructions_for_use'];
                M('TeachingCourseCouponRelation')->where('status=1 and coupon_id='.$cid)->save($updateData);
                $this->success('更新成功', 'index.php?s=/admin/coupon/index');
            }
        } else {
            $this->display('coupon_add');
        }
    }

    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('Coupon');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('coupon', $data);
        $this->meta_title = '编辑活动';
        $this->display();
    }

    public function set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('Coupon');
            $tccr_model = M('TeachingCourseCouponRelation');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                    $tccr_model->where('coupon_id='.$i)->save(array('status'=>-1));
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
                $tccr_model->where('coupon_id='.$id)->save(array('status'=>-1));
            }
            $this->success('处理成功','index.php?s=/admin/coupon/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function set_obtain_gift_status($id, $status=-1){
        if(!empty($id)){
            $model = M('UserGiftPackage');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/coupon/obtain_gift_list');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function obtain_gift_list(){
        $model = M('UserGiftPackage');
        $count = $model->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($list as &$item){
            $obtian_id = $item['obtain_coupon_record_id'];
            $user_coupon = M('UserCoupon')->where('id='.$obtian_id)->find();
            $coupon_name = M('Coupon')->where('id='.$user_coupon['coupon_id'])->getField('name');
            $teaching_course_name = M('OrganizationTeachingCourse')
                ->where('id='.$user_coupon['teaching_course_id'])->getField('course_name');
            $item['coupon_name'] = $coupon_name;
            $item['teaching_course_name'] = $teaching_course_name;
        }

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","领取礼包列表");
        $this->display();
    }


    public function obtain_gift_detail(){
        $obtian_id = I('id');
        $model = M('UserGiftPackage');
        $obtain_gift = $model->where('id='.$obtian_id)->find();
        $obtain_gift['voucher'] = explode(',', $obtain_gift['voucher']);
        $this->assign('obtain_gift', $obtain_gift);
        $this->assign("meta_title","领取礼包详情");
        $this->display();
    }


    public function obtain_coupon_list(){
        $model = M('UserCoupon');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($list as &$item) {
            $item['user_name'] = M('Member')->where('uid='.$item['uid'])->getField('nickname');
            $item['course_name'] = M('OrganizationTeachingCourse')
                ->where('id='.$item['teaching_course_id'])->getField('course_name');
            $item['coupon_name'] = M('Coupon')->where('id='.$item['coupon_id'])->getField('name');
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","领取优惠券列表");
        $this->display();
    }

    public function gift_package(){
        $model = M('OrganizationGiftPackage');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","机构活动礼包列表");
        $this->display();
    }

    public function gift_package_update(){
        if (IS_POST) { //提交表单
            $model = M('OrganizationGiftPackage');
            $cid = $_POST['cid'];
            $data['introduce'] = $_POST["introduce"];
            $data['detail'] = $_POST["detail"];

            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/coupon/gift_package');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/coupon/gift_package');
            }
        } else {
            $this->display('gift_package_add');
        }
    }

    public function gift_package_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('OrganizationGiftPackage');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('gift', $data);
        $this->meta_title = '编辑礼包';
        $this->display();
    }


    public function set_gift_package_status($id, $status=-1){
        if(!empty($id)){
            $model = M('OrganizationGiftPackage');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/coupon/gift_package');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function set_obtain_gift_check_status($id, $check=0){
        if(!empty($id)){
            $model = M('UserGiftPackage');
            $data['check'] = $check;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/coupon/obtain_gift_detail&id='.$id);
        } else {
            $this->error('未选择要处理的数据');
        }
    }
}
