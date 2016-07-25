<?php
namespace Admin\Controller;
use Think\Exception;
use Think\Hook;
use Think\Page;


class YellowPagesController extends AdminController {

    protected $yellowPages;
    protected $pageClass;
    protected $pageLabel;

    public function _initialize(){
        parent::_initialize();
        $this->yellowPages = M('YellowPages');
        $this->pageClass = M('PageClass');
        $this->pageLabel = M('YellowPagesLabel');

    }

    /**
     * 显示黄页列表
     */
    public function index(){
        $model = $this->yellowPages;
        $count = $model->where('status>=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        //用于黄页名称搜索
        $name = $_GET["title"];
        if($name){
            $map['website_name'] = array('like','%'.$name.'%');
            $list = $model->where($map)->where("status>=1")->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('status>=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$website){
            $value = $website['class_id'];
            $cmodel = $this->pageClass;
            $category = $cmodel->where('status=1 and id='.$value)->getField("category_name");
            $website['category'] = $category;
            $label_name = $this->pageLabel->where('status=1 and id='.$website['label'])->getField('name');
            if($label_name){
                $website['label_name'] = $label_name;
            }else{
                $website['label_name'] = '无';
            }
        }

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","黄页列表");
        $this->display();
    }

    /**
     * 显示首页推荐
     */
    public function showRecommend(){
        $model = $this->yellowPages;
        $count = $model->where('status=2')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        //用于黄页名称搜索
        $name = $_GET["title"];
        if($name){
            $map['website_name'] = array('like','%'.$name.'%');
            $list = $model->where($map)->where("status=2")->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('status=2')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$website){
            $value = $website['class_id'];
            $cmodel = $this->pageClass;
            $category = $cmodel->where('status=1 and id='.$value)->getField("category_name");
            $website['category'] = $category;
            $label_name = $this->pageLabel->where('status=1 and id='.$website['label'])->getField('name');
            if($label_name){
                $website['label_name'] = $label_name;
            }else{
                $website['label_name'] = '无';
            }
        }

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","黄页列表");
        $this->display('index');
    }

    /**
     * 显示首页推荐
     */
    public function showHot(){
        $model = $this->yellowPages;
        $count = $model->where('label=1 and status>=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        //用于黄页名称搜索
        $name = $_GET["title"];
        if($name){
            $map['website_name'] = array('like','%'.$name.'%');
            $list = $model->where($map)->where("label=1 and status>=1")->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('label=1 and status>=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$website){
            $value = $website['class_id'];
            $cmodel = $this->pageClass;
            $category = $cmodel->where('status=1 and id='.$value)->getField("category_name");
            $website['category'] = $category;
            $label_name = $this->pageLabel->where('status=1 and id='.$website['label'])->getField('name');
            if($label_name){
                $website['label_name'] = $label_name;
            }else{
                $website['label_name'] = '无';
            }
        }

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","黄页列表");
        $this->display('index');
    }

    /**
     * 显示首页推荐
     */
    public function showNew(){
        $model = $this->yellowPages;
        $count = $model->where('label=2 and status>=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        //用于黄页名称搜索
        $name = $_GET["title"];
        if($name){
            $map['website_name'] = array('like','%'.$name.'%');
            $list = $model->where($map)->where("label=2 and status>=1")->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('label=2 and status>=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$website){
            $value = $website['class_id'];
            $cmodel = $this->pageClass;
            $category = $cmodel->where('status=1 and id='.$value)->getField("category_name");
            $website['category'] = $category;
            $label_name = $this->pageLabel->where('status=1 and id='.$website['label'])->getField('name');
            if($label_name){
                $website['label_name'] = $label_name;
            }else{
                $website['label_name'] = '无';
            }
        }

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","黄页列表");
        $this->display('index');
    }

    /**
     * 更新黄页信息
     */
    public function update(){
        if (IS_POST) { //提交表单
            $model = $this->yellowPages;
            $cid = $_POST["cid"];
            $data['class_id'] = $_POST['class_id'];
            $data['website_name'] = $_POST['website_name'];
            $data['url'] = $_POST['url'];
            $pic_id = $_POST['pic_id'];
            $data["label"] = $_POST["label"];
            $data["create_time"] = time();
            $data["fake_view_count"] = $_POST["fake_view_count"];
            $data['status'] = $_POST["status"];
            if(empty($cid)){
                try {
                    //上传图片到OSS
                    $this->uploadLogoPicToOSS($pic_id);
                    $data['icon_url'] = $this->fetchCdnImage($pic_id);
                    $result = $model->data($data)->add();
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/yellow_pages');
            } else {
                if(is_numeric($pic_id)){
                    //上传图片到OSS
                    $this->uploadLogoPicToOSS($pic_id);
                    $data['icon_url'] = $this->fetchCdnImage($pic_id);
                }else{
                    $data['icon_url'] = $pic_id;
                }
                $result = $model->where('id='.$cid)->save($data);
                if($result){
                    $this->success('更新成功', 'index.php?s=/admin/yellow_pages');
                }else{
                    $this->error();
                }
            }
        } else {
            $this->display('add');
        }
    }

    /**
     * 添加黄页信息
     */
    public function add(){
        $model = $this->pageClass;
        $type = $model->where('status=1')->field('id,category_name')->order('id')->select();
        $label = $this->pageLabel->where('status=1')->field('id,name')->order('id')->select();
        $this->assign('_label', $label);
        $this->assign('_type', $type);
        $this->display();
    }

    /**
     * 修改黄页信息
     * @param $id
     */
    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        $yellowPages = $this->yellowPages;
        $data = $yellowPages->where('status>=1 and id='.$id)->find();
        $model = $this->pageClass;
        $type = $model->where('status=1')->order('id')->select();
        $label = $this->pageLabel->where('status=1')->field('id,name')->order('id')->select();
        $this->assign('_label', $label);
        $this->assign('_type', $type);
        $this->assign('info',$data);
        $this->display();
    }

    /**删除黄页信息
     * @param string $id
     */
    public function  delete($id){
        if(!empty($id)){
            $model = $this->yellowPages;
            $data['status'] = -1;
            if(is_array($id)){
                $map['id'] = array('in',$id);
                $model->where($map)->save($data);
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('删除成功','index.php?s=/admin/yellow_pages');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 黄页分类列表
     */
    public function category(){
        $model = $this->pageClass;
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));

        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","黄页分类列表");
        $this->display();
    }


    /**
     * 更新黄页分类
     */
    public function category_update(){
        if (IS_POST) { //提交表单
            $model = $this->pageClass;
            $cid = $_POST["cid"];
            $data["category_name"] = $_POST["category_name"];
            $pic_id = $_POST["pic_id"];
            $data['status']=1;
            if(empty($cid)){
                try {
                    $data["create_time"] = time();
                    //上传图片到OSS
                    $this->uploadLogoPicToOSS($pic_id);
                    $data['icon_url'] = $this->fetchCdnImage($pic_id);
                    $result = $model->data($data)->add();
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/yellow_pages/category');
            } else {
                if(is_numeric($pic_id)){
                    //上传图片到OSS
                    $this->uploadLogoPicToOSS($pic_id);
                    $data['icon_url'] = $this->fetchCdnImage($pic_id);
                }else{
                    $data['icon_url'] = $pic_id;
                }
                $result = $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/yellow_pages/category');
            }
        } else {
            $this->display('add');
        }
    }

    /**删除黄页分类
     * @param $id
     */
    public function category_delete($id){
        if(!empty($id)){
            $model = $this->pageClass;
            $data['status'] = -1;
            if(is_array($id)){
                $map['id'] = array('in',$id);
                $res = $model->where($map)->save($data);
            }else{
                $map['id'] = $id;
                $res = $model->where($map)->save($data);
            }
            if(!$res){
                $this->error("删除数据失败");
            }else{
                $this->success("删除成功",'index.php?s=/admin/yellow_pages/category');
            }
        }else{
            $this->error('未选择要删除的数据');
        }
    }

    public function category_edit($id){
        $model = $this->pageClass;
        $data = $model->where('status=1 and id='.$id)->find();
        $this->assign('info',$data);
        $this->display();
    }

    /**
     * 黄页分类列表
     */
    public function label(){
        $model = $this->pageLabel;
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));

        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","黄页标签列表");
        $this->display();
    }


    /**
     * 更新黄页标签
     */
    public function label_update(){
        if (IS_POST) { //提交表单
            $model = $this->pageLabel;
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $pic_id = $_POST["pic_id"];
            $data['status']=1;
            if(empty($cid)){
                try {
                    $data["create_time"] = time();
                    //上传图片到OSS
                    $this->uploadLogoPicToOSS($pic_id);
                    $data['url'] = $this->fetchCdnImage($pic_id);
                    $result = $model->data($data)->add();
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/yellow_pages/label');
            } else {
                if(is_numeric($pic_id)){
                    //上传图片到OSS
                    $this->uploadLogoPicToOSS($pic_id);
                    $data['url'] = $this->fetchCdnImage($pic_id);
                }else{
                    $data['url'] = $pic_id;
                }
                $result = $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/yellow_pages/label');
            }
        } else {
            $this->display('add');
        }
    }

    /**
     * 删除黄页标签
     * @param $id
     */
    public function label_delete($id){
        if(!empty($id)){
            $model = $this->pageLabel;
            $data['status'] = -1;
            if(is_array($id)){
                $map['id'] = array('in',$id);
                $res = $model->where($map)->save($data);
            }else{
                $map['id'] = $id;
                $res = $model->where($map)->save($data);
            }
            if(!$res){
                $this->error("删除数据失败");
            }else{
                $this->success("删除成功",'index.php?s=/admin/yellow_pages/label');
            }
        }else{
            $this->error('未选择要删除的数据');
        }
    }

    public function label_edit($id){
        $model = $this->pageLabel;
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

    /**
     * 获取cdn oss图片地址
     * @param $pic_id
     * @return null|string
     */
    private function fetchCdnImage($pic_id){
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
                $picUrl = "http://pic.hisihi.com/".$objKey;
            } else {
                $picUrl = null;
            }
        }
        return $picUrl;
    }
}
