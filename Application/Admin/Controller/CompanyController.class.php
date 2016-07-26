<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Admin\Model\AuthGroupModel;
use Admin\Model\CompanyConfigModel;
use Think\Exception;
use Think\Hook;
use Think\Page;

/**
 * 公司管理模块
 * Class CompanyController
 * @package Admin\Controller
 */
class CompanyController extends AdminController {

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 显示公司列表
     */
    public function index(){
        $model = D('Company');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        //用于公司名称搜索
        $name = $_GET["title"];
        if($name){
            $map['name'] = array('like','%'.$name.'%');
            $list = $model->where($map)->where("status=1")->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$recruit){
            $scale_value = $recruit['scale'];
            $cmodel = D('CompanyConfig');
            $scale = $cmodel->where('type=2 and status=1 and value='.$scale_value)->getField("value_explain");
            $recruit['scale'] = $scale;
        }

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","公司列表");
        $this->display();
    }

    /**
     * 显示company新增页面
     */
    public function add(){
        $model = D('CompanyConfig');
        $marks = $model->where('type=1 and status=1')->select();
        $scale = $model->where('type=2  and status=1')->order('id')->select();
        $filtrate = $model->where('type=8 and status=1')->select();

        $this->assign('_marks', $marks);
        $this->assign('_scale', $scale);
        $this->assign('_filtrate', $filtrate);
        $this->display();
    }

    /**
     * 更新company
     */
    public function update(){
        if (IS_POST) { //提交表单
            $model = D('Company');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["city"] = $_POST["city"];
            $data["slogan"] = $_POST["slogan"];
            $data["filtrate_mark"] = $_POST["filtrate_mark"];
            $data["introduce"] = $_POST["introduce"];
            $data["marks"] = $_POST["marks"];
            $data["scale"] = $_POST["scale"];
            $data["website"] = $_POST["website"];
            $data["fullname"] = $_POST["fullname"];
            $data["location"] = $_POST["location"];
            $data['hr_email'] = $_POST['hr_email'];
            $data["picture"] = $_POST["picture"];
            $data["create_time"] = time();
            if(empty($cid)){
                try {
                    if(!$model->create($data)){
                        $this->error($model->getError());
                    }
                    $res = $model->addNewDate($data);
                    if(!$res){
                        $this->error(D('Company')->getError());
                    }else{
                        $id = $res;
                        //上传图片到OSS
                        $picid = $model->where('id='.$id)->getField('picture');
                        if($picid){
                            $this->uploadLogoPicToOSS($picid);
                        }
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                //$this->success('添加成功', Cookie('__forward__'));
                $this->success('添加成功', 'index.php?s=/admin/company');
            } else {
                $model = D('Company');
                if(!$model->create($data)){
                    $this->error($model->getError());
                }
                $model->updateCompany($cid, $data);
                //上传图片到OSS
                $picid = $model->where('id='.$cid)->getField('picture');
                if($picid){
                    $this->uploadLogoPicToOSS($picid);
                }
                //$this->success('更新成功', Cookie('__forward__'));
                $this->success('更新成功', 'index.php?s=/admin/company');
            }
        } else {
            $this->display('add');
        }
    }

    /**显示company的编辑页面
     * @param $id
     */
    public function edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('Company');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $cmodel = D('CompanyConfig');
        $marks = $cmodel->where('type=1 and status=1')->select();
        $scale = $cmodel->where('type=2  and status=1')->order('id')->select();
        $markarray = explode("#",$data['marks']);
        $filtrateArray =  explode("#",$data['filtrate_mark']);
        $filtrate =  $cmodel->where('type=8 and status=1')->select();

        $this->assign('_filtrate',$filtrate);
        $this->assign('_filtrate_array',$filtrateArray);
        $this->assign('_markarray', $markarray);
        $this->assign('_marks', $marks);
        $this->assign('_scale', $scale);
        $this->assign('info', $data);
        $this->meta_title = '编辑公司';
        $this->display();
    }

    /**company的删除
     * @param string $id
     */
    public function delete($id){
        if(!empty($id)){
            $model = D('Company');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->updateCompany($i, $data);
                }
            } else {
                $id = intval($id);
                $model->updateCompany($id, $data);
            }
            $this->success('删除成功','index.php?s=/admin/company');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 公司列表banner
     */
    public function banner($cate_id = 47)
    {
        if ($cate_id === null) {
            $cate_id = $this->cate_id;
        }

        //获取模型信息
        $model = M('Model')->getByName('topcommend');

        //解析列表规则
        $fields = array();
        $grids = preg_split('/[;\r\n]+/s', $model['list_grid']);
        foreach ($grids as &$value) {
            // 字段:标题:链接
            $val = explode(':', $value);
            // 支持多个字段显示
            $field = explode(',', $val[0]);
            $value = array('field' => $field, 'title' => $val[1]);
            if (isset($val[2])) {
                // 链接信息
                $value['href'] = $val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function ($match) use (&$fields) {
                    $fields[] = $match[1];
                }, $value['href']);
            }
            if (strpos($val[1], '|')) {
                // 显示格式定义
                list($value['title'], $value['format']) = explode('|', $val[1]);
            }
            foreach ($field as $val) {
                $array = explode('|', $val);
                $fields[] = $array[0];
            }
        }

        // 过滤重复字段信息 TODO: 传入到查询方法
        $fields = array_unique($fields);

        //获取对应分类下的模型
        if (!empty($cate_id)) {   //没有权限则不查询数据
            //获取分类绑定的模型
            $models = get_category($cate_id, 'model');
            $allow_reply = get_category($cate_id, 'reply');//分类文档允许回复
            $pid = I('pid');
            if ($pid == 0) {
                //开发者可根据分类绑定的模型,按需定制分类文档列表
                $template = $this->topCommendOfArticle($cate_id); //转入默认文档列表方法
                $this->assign('model', explode(',', $models));
            } else {
                //开发者可根据父文档的模型类型,按需定制子文档列表
                $doc_model = M('Document')->where(array('id' => $pid, 'position' => 5))->find();

                switch ($doc_model['model_id']) {
                    default:
                        if ($doc_model['type'] == 2 && $allow_reply) {
                            $this->assign('model', array(2));
                            $template = $this->indexOfReply($cate_id); //转入子文档列表方法
                        } else {
                            $this->assign('model', explode(',', $models));
                            $template = $this->topCommendOfArticle($cate_id); //转入默认文档列表方法
                        }
                }
            }
            $this->assign('list_grids', $grids);
            $this->assign('model_list', $model);
            // 记录当前列表页的cookie
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
            $this->display($template);
        }
    }

    protected function topCommendOfArticle($cate_id){
        /* 查询条件初始化 */
        $map = array();
        $map['position'] = 5;
        if(isset($_GET['title'])){
            $map['title']  = array('like', '%'.(string)I('title').'%');
        }
        if(isset($_GET['status'])){
            $map['status'] = I('status');
            $status = $map['status'];
        }else{
            $status = null;
            $map['status'] = array('in', '-1,0,1,2');
        }
        if ( !isset($_GET['pid']) ) {
            $map['pid']    = 0;
        }
        if ( isset($_GET['time-start']) ) {
            $map['update_time'][] = array('egt',strtotime(I('time-start')));
        }
        if ( isset($_GET['time-end']) ) {
            $map['update_time'][] = array('elt',24*60*60 + strtotime(I('time-end')));
        }
        if ( isset($_GET['nickname']) ) {
            $map['uid'] = M('Member')->where(array('nickname'=>I('nickname')))->getField('uid');
        }

        // 构建列表数据
        $Document = M('Document');
        $map['category_id'] =   $cate_id;
        $map['pid']         =   I('pid',0);
        if($map['pid']){ // 子文档列表忽略分类
            unset($map['category_id']);
        }

        $list = $this->lists($Document,$map,'level DESC,id DESC');
        int_to_string($list);
        if($map['pid']){
            // 获取上级文档
            $article    =   $Document->field('id,title,type')->find($map['pid']);
            $this->assign('article',$article);
        }
        //检查该分类是否允许发布内容
        $allow_publish  =   get_category($cate_id, 'allow_publish');

        $this->assign('status', $status);
        $this->assign('list',   $list);
        $this->assign('allow',  $allow_publish);
        $this->assign('pid',    $map['pid']);

        $this->meta_title = '公司列表Banner';
        return 'banner';
    }

    /**
     * 顶部文档新增页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function topadd(){
        $cate_id    =   I('get.cate_id',0);
        $model_id   =   I('get.model_id',0);

        empty($cate_id) && $this->error('参数不能为空！');
        empty($model_id) && $this->error('该分类未绑定模型！');

        //检查该分类是否允许发布
        $allow_publish = D('Document')->checkCategory($cate_id);
        !$allow_publish && $this->error('该分类不允许发布内容！');

        /* 获取要编辑的扩展模型模板 */
        $model      =   get_document_model($model_id);

        //处理结果
        $info['pid']            =   $_GET['pid']?$_GET['pid']:0;
        $info['model_id']       =   $model_id;
        $info['category_id']    =   $cate_id;
        if($info['pid']){
            // 获取上级文档
            $article            =   M('Document')->field('id,title,type')->find($info['pid']);
            $this->assign('article',$article);
        }

        //获取表单字段排序
        $fields = get_model_attribute($model['id']);

        $this->assign('info',       $info);
        $this->assign('fields',     $fields);
        $this->assign('type_list',  get_type_bycate($cate_id));
        $this->assign('model',      $model);
        $this->meta_title = '新增'.$model['title'];
        $this->display();
    }
    /**
     * 顶部文档编辑页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function topedit(){

        $id     =   I('get.id','');
        if(empty($id)){
            $this->error('参数不能为空！');
        }

        /*获取一条记录的详细数据*/
        $Document = D('Document');
        $data = $Document->detail($id);
        if(!$data){
            $this->error($Document->getError());
        }

        if($data['pid']){
            // 获取上级文档
            $article        =   M('Document')->field('id,title,type')->find($data['pid']);
            $this->assign('article',$article);
        }
        $this->assign('data', $data);
        $this->assign('model_id', $data['model_id']);

        /* 获取要编辑的扩展模型模板 */
        $model      =   get_document_model($data['model_id']);
        $this->assign('model',      $model);

        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('fields',     $fields);


        //获取当前分类的文档类型
        $this->assign('type_list', get_type_bycate($data['category_id']));

        $this->meta_title   =   '编辑文档';
        $this->display();
    }

    /**
     * 更新顶部banner数据
     * @author huajie <banhuajie@163.com>
     */
    public function topupdate(){
        $res = D('Document')->update();
        if(!$res){
            $this->error(D('Document')->getError());
        }else{
            $id = $res['id'];
            $model = M();
            $result = $model->query('SELECT logo_pic FROM hisihi_document_article WHERE id='.$id);
            if($result){
                $this->uploadLogoPicToOSS($result[0]['logo_pic']);
            }
            $result = $model->query('SELECT cover_id FROM hisihi_document WHERE id='.$id);
            if($result){
                $this->uploadLogoPicToOSS($result[0]['cover_id']);
            }
            $this->success($res['id']?'更新成功':'新增成功', Cookie('__forward__'));
        }
    }

    /*
     * 显示配置信息列表
     */
    public function config(){
        $model = D('CompanyConfig');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        //用于公司名称搜索
        $name = $_GET["title"];
        if($name){
            $map['name'] = array('like','%'.$name.'%');
            $list = $model->where($map)->where("status=1")->order('type')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('status=1')->order('type')->limit($Page->firstRow.','.$Page->listRows)->select();
        }

        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","配置列表");
        $this->display();
    }

    /**
     * 新增配置信息
     */
    public function config_add(){
        $this->display();
    }

    /**编辑配置信息
     * @param $id
     */
    public function config_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('CompanyConfig');
        $data = $Model->where('status=1 and id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('info', $data);
        $this->meta_title = '编辑公司配置';
        $this->display();
    }

    /**
     * 更新配置信息
     */
    public function config_update(){
        if (IS_POST) { //提交表单
            $model = M('CompanyConfig');
            $cid = $_POST["cid"];
            $data["type"] = $_POST["type"];
            $data["type_explain"] = $_POST["type_explain"];
            $data["value"] = $_POST["value"];
            $data["value_explain"] = $_POST["value_explain"];
            $data["create_time"] = time();
            if(empty($cid)){
                try {
                    $res = $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                //$this->success('添加成功', Cookie('__forward__'));
                $this->success('添加成功', 'index.php?s=/admin/company/config');
            } else {
                $model = D('CompanyConfig');
                $model->updateCompanyConfig($cid, $data);
                //$this->success('更新成功', Cookie('__forward__'));
                $this->success('更新成功', 'index.php?s=/admin/company/config');
            }
        } else {
            $this->display('add');
        }
    }

    /**删除配置信息
     * @param $id
     */
    public function config_delete($id){
        if(!empty($id)){
            $model = D('CompanyConfig');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->updateCompanyConfig($i, $data);
                }
            } else {
                $id = intval($id);
                $model->updateCompanyConfig($id, $data);
            }
            $this->success('删除成功','index.php?s=/admin/company/config');
        } else {
            $this->error('未选择要删除的数据');
        }
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
                //getThumbImageById($picID, 280, 160);//上传时生成固定大小图片
            }
        }
    }

    /**
     * 公司招聘信息列表
     */
    public function recruit(){
        $model = D('CompanyRecruit');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        //用于公司名称搜索
        $name = $_GET["title"];
        if($name){
            $map['name'] = array('like','%'.$name.'%');
            $list = $model->where($map)->where("status=1")->order('type')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$recruit){
            $company_id = $recruit['company_id'];
            $companyModel = M('Company');
            $company_info = $companyModel->where('id='.$company_id)->find();
            $recruit['company_name'] = $company_info['name'];

            $salary_value = $recruit['salary'];
            $cmodel = D('CompanyConfig');
            $salary = $cmodel->where('type=4 and status=1 and value='.$salary_value)->getField("value_explain");
            $recruit['salary'] = $salary;
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","招聘职位列表");
        $this->display();
    }

    /**
     * 添加公司招聘信息
     * @param $id
     */
    public function addRecruit($id){
        $cmodel = D('CompanyConfig');
        $marks = $cmodel->where('type=3 and status=1')->select();
        $salary = $cmodel->where('type=4 and status=1')->select();
        $this->assign('_salary', $salary);
        $this->assign('requirement', $marks);
        $this->assign('company_id', $id);
        $this->display('addRecruit');
    }

    /**
     * 招聘信息添加和修改
     */
    public function recruitUpdate(){
        if (IS_POST) { //提交表单
            $model = D('CompanyRecruit');
            $data = $model->create();
            $data['end_time'] = strtotime($_POST["end_time"]);
            $rid = $_POST['rid'];
            if(empty($rid)){
                try {
                    $model = M('CompanyRecruit');
                    $data = $model->create();
                    $data['end_time'] = strtotime($_POST["end_time"]);
                    $data['create_time'] = time();
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/company/recruit');
            } else {
                $model = D('CompanyRecruit');
                $model->updateCompanyRecruit($rid, $data);
                $this->success('更新成功', 'index.php?s=/admin/company/recruit');
            }
        } else {
            $this->display('addRecruit');
        }
    }

    /**
     * 编辑招聘信息
     * @param $id
     */
    public function editRecruit($id){
        $model = M('CompanyRecruit');
        $result = $model->where('id='.$id)->find();
        $cmodel = D('CompanyConfig');
        $marks = $cmodel->where('type=3 and status=1')->select();
        $salary = $cmodel->where('type=4 and status=1')->select();
        $markarray = explode("#",$result['requirement']);
        $this->assign('_markarray', $markarray);
        $this->assign('_salary', $salary);
        $this->assign('requirement', $marks);
        $this->assign('recruit', $result);
        $this->display('editRecruit');
    }

    /**
     * 删除招聘信息
     * @param $id
     */
    public function deleteRecruit($id){
        if(!empty($id)){
            $model = D('CompanyRecruit');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->updateCompanyRecruit($i, $data);
                }
            } else {
                $id = intval($id);
                $model->updateCompanyRecruit($id, $data);
            }
            $this->success('删除成功','index.php?s=/admin/company/recruit');
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 恢复删除的招聘信息数据
     * @param $id
     */
    public function setRecruitStatus($id){
        if(!empty($id)){
            $model = D('CompanyRecruit');
            $data['status'] = 1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->updateCompanyRecruit($i, $data);
                }
            } else {
                $id = intval($id);
                $model->updateCompanyRecruit($id, $data);
            }
            $this->success('启用数据成功','index.php?s=/admin/company/recruit');
        } else {
            $this->error('未选择要启用的数据');
        }
    }

}
