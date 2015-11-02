<?php
namespace Admin\Controller;
use Think\Exception;
use Think\Hook;
use Think\Page;


class InspirationController extends AdminController {

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 显示灵感图片列表
     */
    public function index(){
        $model = D('Inspiration');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        //用于公司名称搜索
        $name = $_GET["title"];
        if($name){
            $map['description'] = array('like','%'.$name.'%');
            $list = $model->where($map)->where("status=1")->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$inspiration){
            $value = $inspiration['category_id'];
            $cmodel = D('InspirationConfig');
            $category = $cmodel->where('type=1 and status=1 and id='.$value)->getField("value");
            $inspiration['category'] = $category;
            $inspiration['special'] = $inspiration['special'] ? '是' : '否';
            $inspiration['selection'] = $inspiration['selection'] ? '是' : '否';
        }

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","找灵感图片列表");
        $this->display();
    }

    /**
     * 更新灵感信息
     */
    public function update(){
        if (IS_POST) { //提交表单
            $model = D('Inspiration');
            $cid = $_POST["cid"];
            $data['description'] = $_POST['description'];
            $data['category_id'] = $_POST['category_id'];
            $data['special'] = $_POST['special'];
            $data['selection'] = $_POST['selection'];
            $data["pic_id"] = $_POST["pic_id"];
            $data["create_time"] = time();
            $data["view_count"] = rand(1000, 3000);
            $data["favorite_count"] = rand(100, 300);
            if(empty($cid)){
                try {
                    if(!$model->create($data)){
                        $this->error($model->getError());
                    }
                    $res = $model->saveData($data);
                    if(!$res){
                        $this->error(D('Inspiration')->getError());
                    }else{
                        $id = $res;
                        //上传图片到OSS
                        $picid = $model->where('id='.$id)->getField('pic_id');
                        if($picid){
                            $this->uploadLogoPicToOSS($picid);
                            getThumbImageById($picid, 280, 160);
                        }
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/inspiration');
            } else {
                $model = D('Inspiration');
                if(!$model->create($data)){
                    $this->error($model->getError());
                }
                $model->updateData($cid, $data);
                //上传图片到OSS
                $picid = $model->where('id='.$cid)->getField('pic_id');
                if($picid){
                    $this->uploadLogoPicToOSS($picid);
                }
                $this->success('更新成功', 'index.php?s=/admin/inspiration');
            }
        } else {
            $this->display('add');
        }
    }

    /**
     * 添加灵感图片
     */
    public function add(){
        $model = D('InspirationConfig');
        $type = $model->where('type=1 and status=1')->order('id')->select();
        $this->assign('_type', $type);
        $this->display();
    }

    /**修改灵感图片
     * @param $id
     */
    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        $inspiration = D('Inspiration');
        $data = $inspiration->where('status=1 and id='.$id)->find();
        $model = D('InspirationConfig');
        $type = $model->where('type=1 and status=1')->order('id')->select();
        $this->assign('_type', $type);
        $this->assign('info',$data);
        $this->display();
    }

    /**删除灵感图片
     * @param string $id
     */
    public function  delete($id){
        if(!empty($id)){
            $model = D('Inspiration');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->updateData($i, $data);
                }
            } else {
                $id = intval($id);
                $model->updateData($id, $data);
            }
            $this->success('删除成功','index.php?s=/admin/inspiration');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 显示配置信息列表
     */
    public function config(){
        $model = D('InspirationConfig');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where('status=1')->order('type')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","配置列表");
        $this->display();
    }


    /**
     * 更新配置信息
     */
    public function config_update(){
        if (IS_POST) { //提交表单
            $model = M('InspirationConfig');
            $cid = $_POST["cid"];
            $data["type"] = $_POST["type"];
            $data["value"] = $_POST["value"];
            $data["create_time"] = time();
            if(empty($cid)){
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/inspiration/config');
            } else {
                $model = D('InspirationConfig');
                $model->updateInspirationConfig($cid, $data);
                $this->success('更新成功', 'index.php?s=/admin/inspiration/config');
            }
        } else {
            $this->display('add');
        }
    }

    /**删除灵感配置
     * @param $id
     */
    public function config_delete($id){
        if(!empty($id)){
            $model = D('InspirationConfig');
            $data['status'] = -1;
            if(is_array($id)){
                foreach($id as $val){
                    $map['id'] = $val;
                    $res = $model->where($map)->save($data);
                }
            }else{
                $map['id'] = $id;
                $res = $model->where($map)->save($data);
            }
            if(!$res){
                $this->error("删除数据失败");
            }else{
                $this->success("删除成功",'index.php?s=/admin/inspiration/config');
            }
        }else{
            $this->error('未选择要删除的数据');
        }
    }

    public function config_edit($id){
        $model = D('InspirationConfig');
        $data = $model->where('status=1 and id='.$id)->find();
        $this->assign('info',$data);
        $this->display();
    }

    private function uploadLogoPicToOSS($picID){
        $model = M();
        $result = $model->query("select path from hisihi_picture where id=".$picID);
        if($result){
            $picLocalPath = $result[0]['path'];
            $picKey = substr($picLocalPath, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $picKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if(!$isExist){
                Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadOtherResource', $param);
            }
        }
    }

}
