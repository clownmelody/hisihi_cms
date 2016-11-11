<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 2016/10/31
 * Time: 16:21
 */
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Exception;
use Think\Hook;
use Think\Page;
use Admin\Builder\AdminTreeListBuilder;
use Admin\Builder\AdminConfigBuilder;

class EncyclopediaController extends AdminController {

    public function _initialize(){
        parent::_initialize();
    }

/*    public function category($pid=0){
        $model = M('EncyclopediaCategory');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        if($pid==0){
            $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        } else {
            $list = $model->where('status=1 and pid='.$pid)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$item){
            $info = $model->field('name')->where('id='.$item['pid'])->find();
            $item['pid_name'] = $info['name'];
        }
        $first_level_list = $model->field('id,name')->where('status=1 and pid=0')->select();
        $this->assign('first_level_list', $first_level_list);
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","分类列表");
        $this->display();
    }*/

    public function category($pid=0){
        //显示页面
        $builder = new AdminTreeListBuilder();
        $tree = $this->getTree(0, 'id,name,sort,pid,status');
        foreach ($tree as &$item1){
            $item1['title'] = $item1['name'];
            if(is_array($item1['_'])){
                foreach ($item1['_'] as &$item2){
                    $item2['title'] = $item2['name'];
                }
            }
        }
        $builder->title('分类管理')
            ->buttonNew(U('Encyclopedia/add'))
            ->data($tree)
            ->highlight_subnav(U('Encyclopedia/category'))
            ->display();
    }

    /**获得分类树
     * @param int  $id
     * @param bool $field
     * @return array
     * @auth 陈一枭
     */
    public function getTree($id = 0, $field = true){
        $model = M('EncyclopediaCategory');
        /* 获取当前分类信息 */
        if($id){
            $info = $this->info($id);
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $map  = array('status' => array('gt', -1));
        $list = $model->field($field)->where($map)->order('sort desc, create_time desc ')->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);

        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }

    public function add($id = 0, $pid = 0)
    {
        $model = D('EncyclopediaCategory');
        if (IS_POST) {
            if ($id != 0) {
                $issue = $model->create();
                $issue['name'] = $_POST['title'];
                if ($model->save($issue)) {
                    $this->success('编辑成功', 'index.php?s=Admin/Encyclopedia/category');
                } else {
                    $this->error('编辑失败', 'index.php?s=Admin/Encyclopedia/category');
                }
            } else {
                $issue = $model->create();
                $issue['name'] = $_POST['title'];
                $issue['create_time'] = time();
                $max_sort = $model->where('pid='.$_POST['pid'])->max('sort');
                if(empty($max_sort)){
                    $max_sort = 0;
                }
                $issue['sort'] = $max_sort + 1;
                if ($model->add($issue)) {
                    $this->success('新增成功', 'index.php?s=Admin/Encyclopedia/category');
                } else {
                    $this->error('新增失败', 'index.php?s=Admin/Encyclopedia/category');
                }
            }
        } else {
            $builder = new AdminConfigBuilder();
            $opt = array();
            if($pid != 0){
                $issues = $model->where('pid=0')->select();
                foreach ($issues as $issue) {
                    $opt[$issue['id']] = $issue['name'];
                }
            }
            if ($id != 0) {
                $issue = $model->find($id);
                $issue['title'] = $issue['name'];
            } else {
                $issue = array('pid' => $pid, 'status' => 1);
            }
            $builder->title('新增分类')->keyId()->keyText('title', '标题')->keySelect('pid', '父分类', '选择父级分类', array('0' => '顶级分类')+$opt)
                ->keyStatus()->keyCreateTime()
                ->data($issue)
                ->buttonSubmit(U('Encyclopedia/add'))->buttonBack()->display2();
        }

    }

    public function setstatus(){
        $model = D('EncyclopediaCategory');
        $id = I('ids');
        $status = I('status');
        if(intval($status) == -1){
            $count = M('EncyclopediaEntryCatagory')
                ->where('`status`=1 and (catagory_id='.$id.' or first_catagory_id='.$id.')')->count();
            if($count > 0){
                $this->error('该分类中包含词条，不可删除', 'index.php?s=Admin/Encyclopedia/category', 2);
            }
        }
        $res = $model->where('id='.$id)->save(array('status'=>$status));
        if($res === false){
            $this->error('设置失败', 'index.php?s=Admin/Encyclopedia/category', 2);
        }else{
            $this->success('设置成功', 'index.php?s=Admin/Encyclopedia/category', 2);
        }
    }

    /**
     * 获取分类详细信息
     * @param  milit   $id 分类ID或标识
     * @param  boolean $field 查询字段
     * @return array     分类信息
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function info($id, $field = true){
        $model = M('EncyclopediaCategory');
        /* 获取分类信息 */
        $map = array();
        if(is_numeric($id)){ //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['name'] = $id;
        }
        return $model->field($field)->where($map)->find();
    }

    public function category_add($id=0){
        $this->assign('pid', $id);
        $this->display();
    }

    public function category_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        $Model = M('EncyclopediaCategory');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('category', $data);
        $this->assign("meta_title", '编辑分类');
        $this->display();
    }

    public function category_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaCategory');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["sort"] = $_POST["sort"];
            $data["pid"] = $_POST["pid"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/encyclopedia/category');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/encyclopedia/category');
            }
        } else {
            $this->display('category_add');
        }
    }

    public function entry_add(){
        $id = I('id');
        if(!empty($id)){
            $entry = M('EncyclopediaEntry')->where('id='.$id)->find();
            $entry_tags = explode('#', $entry['relevant_entry']);
            $map['id'] = array('in', $entry_tags);
            $entry_tags_list = M('EncyclopediaEntry')->where($map)->field('id,name')->select();
            $this->assign('entry_tags_list', $entry_tags_list);
            $this->assign('info', $entry);
        }else{
            $sort = M('EncyclopediaEntry')->where('`status`=1')->Max('sort');
            $info['sort'] = $sort + 1;
            $this->assign('info', $info);
        }
        $this->display('entry_add');
    }

    public function entry_delete(){
        $id = I('id');
        if(!empty($id)){
            if(is_array($id)){
                $map1['id'] = array('in', $id);
                $res = M('EncyclopediaEntry')->where($map1)->delete();
                $map2['entry_id'] = array('in', $id);
                $res = M('EncyclopediaEntryCatagory')->where($map2)->delete();
                $res = M('EncyclopediaEntryCatalogue')->where($map2)->delete();
                $res = M('EncyclopediaEntryContent')->where($map2)->delete();
                $res = M('EncyclopediaEntryLink')->where($map2)->delete();
            }else{
                $map1['id'] = $id;
                $res = M('EncyclopediaEntry')->where($map1)->delete();
                $map2['entry_id'] = $id;
                $res = M('EncyclopediaEntryCatagory')->where($map2)->delete();
                $res = M('EncyclopediaEntryCatalogue')->where($map2)->delete();
                $res = M('EncyclopediaEntryContent')->where($map2)->delete();
                $res = M('EncyclopediaEntryLink')->where($map2)->delete();
            }
            $this->success('删除成功','index.php?s=/admin/encyclopedia/item');
        }
        $this->error('未选择要删除的数据');
    }

    public function entry_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaEntry');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["sort"] = $_POST["sort"];
            $data["cover_id"] = $_POST["cover_id"];
            $data["abstract"] = $_POST["abstract"];
            $data["relevant_entry"] = $_POST["relevant_entry"];
            if(intval($data["cover_id"]) > 0){
                $this->uploadLogoPicToOSS($data["cover_id"]);
                $data['cover_url'] = $this->fetchCdnImage($data["cover_id"]);
            }
            unset($data['cover_id']);
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $entry_id = $model->add($data);
                    if($entry_id === false){
                        $this->error("新增失败");
                    }else{
                        $this->success('添加成功', 'index.php?s=/admin/encyclopedia/catalogue_add/id/'.$entry_id);
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if ($res === false){
                    $this->error("编辑失败");
                }else{
                    $this->success('更新成功', 'index.php?s=/admin/encyclopedia/item');
                }
            }
        } else {
            $this->display('entry_add');
        }
    }

    public function searchEntry(){
        $name = I('name');
        $map['name'] = array('like', '%'.$name.'%');
        $map['status'] = 1;
        $res = M('EncyclopediaEntry')->where($map)->field('id, name')->order('sort desc, create_time desc')->select();
        if(empty($res)){
            $rdata['status'] = 1;
            $rdata['msg'] = '获取成功';
            $rdata['data'] = null;
            $this->ajaxReturn($rdata, 'JSON');
        }else{
            $rdata['status'] = 1;
            $rdata['msg'] = '获取成功';
            $rdata['data'] = $res;
            $this->ajaxReturn($rdata, 'JSON');
        }
    }

    public function edit_catalogue_name(){
        $id = I('catalogue_id');
        $name = I('name');
        $data['name'] = $name;
        $res = M('EncyclopediaEntryCatalogue')->where('id='.$id)->save($data);
        if($res === false){
            $rdata['status'] = -1;
            $rdata['msg'] = '修改失败';
            $this->ajaxReturn($rdata, 'JSON');
        }else{
            $rdata['status'] = 1;
            $rdata['msg'] = '修改成功';
            $this->ajaxReturn($rdata, 'JSON');
        }
    }

    public function catalogue_delete(){
        $id = I('catalogue_id');
        $entry_id = I('entry_id');
        $child_ids = M('EncyclopediaEntryCatalogue')->where('pid='.$id)->field('id')->select();
        if(!empty($child_ids)){
            $child_ids_arr = array();
            foreach ($child_ids as &$item){
                $child_ids_arr[] = $item['id'];
            }
            $map1['id'] = array('in', $child_ids_arr);
            $map1['entry_id'] = $entry_id;
            $res = M('EncyclopediaEntryCatalogue')->where($map1)->delete();
            $map2['catalogue_id'] = array('in', $child_ids_arr);
            $map2['entry_id'] = $entry_id;
            $res1 = M('EncyclopediaEntryContent')->where($map2)->delete();
        }
        $res = M('EncyclopediaEntryCatalogue')->where('id='.$id)->delete();
        $res1 = M('EncyclopediaEntryContent')->where('catalogue_id='.$id.' and entry_id='.$entry_id)->delete();
    }

    public function catalogue_add(){
        $entry_id = I('id');
        if(empty($entry_id)){
            $this->error('请先添加词条基本信息', 'index.php?s=/admin/encyclopedia/entry_add/');
        }
        $entry = M('EncyclopediaEntry')->where('id='.$entry_id)->find();
        $catalogue_list = M('EncyclopediaEntryCatalogue')->where('`status`=1 and entry_id='.$entry_id)
            ->field('id, entry_id, pid, name')->select();
        $catalogue_str_arr = array();
        foreach ($catalogue_list as &$item){
            $node_id = 100 + $item['id'];
            if($item['pid'] == 0){
                $item['pid'] = 1;
            }else{
                $item['pid'] += 100;
            }
            $name2 = $this->op_t($item['name']);
            $catalogue_str = '{id: '.$node_id.', pId: '.$item['pid'].', name: "'.$name2.'", catalogue_id: '.$item['id'].', open:true}';
            $catalogue_str_arr[] = $catalogue_str;
        }
        $str = implode(",", $catalogue_str_arr);
        $this->assign('catalogue_str', $str);
        $this->assign('entry', $entry);
        $this->display('catalogue_add');
    }

    public function catalogue_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaEntryCatalogue');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            if(empty($cid)){
                $data["entry_id"] = $_POST["entry_id"];
                $data["pid"] = $_POST["pid"];
                $data["create_time"] = time();
                try {
                    $catalogue_id = $model->add($data);
                    if($catalogue_id === false){
                        $rdata['status'] = -1;
                        $rdata['msg'] = '新增失败';
                        $this->ajaxReturn($rdata, 'JSON');
                    }else{
                        $rdata['catalogue_id'] = $catalogue_id;
                        $rdata['status'] = 1;
                        $rdata['msg'] = '新增成功';
                        $this->ajaxReturn($rdata, 'JSON');
                    }
                } catch (Exception $e) {
                    $rdata['status'] = -1;
                    $rdata['msg'] = '新增失败';
                    $this->ajaxReturn($rdata, 'JSON');
                }
            } else {
                $res = $model->where('id='.$cid)->save($data);
                if ($res === false){
                    $rdata['status'] = -1;
                    $rdata['msg'] = '修改失败';
                    $this->ajaxReturn($rdata, 'JSON');
                }else{
                    $rdata['status'] = 1;
                    $rdata['msg'] = '修改成功';
                    $this->ajaxReturn($rdata, 'JSON');
                }
            }
        } else {
            $this->display('entry_add');
        }
    }

    public function entry_catagory_add(){
        $id = I('id');
        $entry_category_ids = M('EncyclopediaEntryCatagory')->where('`status`=1 and entry_id='.$id)
            ->field('catagory_id')->select();
        $model = M('EncyclopediaCategory');
        $plist = $model->where('pid = 0 and status=1')->order('create_time desc')->select();
        $list = $model->where('status=1 and pid > 0')->order('create_time desc')->select();
        foreach ($list as &$item){
            $has_add = false;
            foreach ($entry_category_ids as $item2){
                if($item['id'] == $item2['catagory_id']){
                    $has_add = true;
                }
            }
            if($has_add){
                $item['checked'] = 1;
            }else{
                $item['checked'] = 0;
            }
        }
        $this->assign('cid', $id);
        $this->assign('pcatagory', $plist);
        $this->assign('catagory', $list);
        $this->display('entry_catagory_add');
    }

    public function entry_catagory_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaEntryCatagory');
            $cid = $_POST["cid"];
            $category_ids = $_POST["category_id"];
            $category_arr = array();
            foreach ($category_ids as &$item){
                $category_arr[] = $item;
            }
            $map['id'] = array('in', $category_arr);
            $category_list = M('EncyclopediaCategory')->where($map)->select();
            $checked_category = array();
            foreach ($category_list as &$item2){
                $checked_category[] = array(
                    'entry_id'=>$cid,
                    'catagory_id'=>$item2['id'],
                    'first_catagory_id'=>$item2['pid'],
                    'create_time'=>time()
                );
            }
            $model->where('entry_id='.$cid)->delete();
            $model->addAll($checked_category);
            $this->success('更新成功', 'index.php?s=/admin/encyclopedia/item');
        } else {
            $this->display('item');
        }
    }

    public function entry_link_add(){
        $entry_id = I('id');
        $model = M('EncyclopediaEntryLink');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $map['entry_id'] = $entry_id;
        $map['status'] = 1;
        $list = $model->where($map)->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('entry_id', $entry_id);
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->display('entry_link_add');
    }

    /**
     * 编辑外链
     */
    public function entry_link_edit(){
        $id = I('id');
        $entry_id = I('entry_id');
        $this->assign('entry_id', $entry_id);
        if(empty($id)){
            $this->display();
        }else{
            $model = M('EncyclopediaEntryLink');
            $link = $model->where('status=1 and id='.$id)->find();
            $this->assign('info', $link);
            $this->display();
        }
    }

    public function entry_link_delete(){
        $id = I('id');
        $entry_id = I('entry_id');
        if(!empty($id)){
            if(is_array($id)){
                $map['id'] = array('in', $id);
                $res = M('EncyclopediaEntryLink')->where($map)->delete();
            }else{
                $res = M('EncyclopediaEntryLink')->where('id='.$id)->delete();
            }
            $this->success('删除成功','index.php?s=/admin/encyclopedia/entry_link_add/id/'.$entry_id);
        } else {
            $this->error('未选择要删除的数据');
        }
    }

    public function entry_link_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaEntryLink');
            $cid = $_POST["cid"];
            $data["name"] = $_POST["name"];
            $data["link"] = $_POST["link"];
            $data["entry_id"] = $_POST["entry_id"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/encyclopedia/entry_link_add/id/'.$data['entry_id']);
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/encyclopedia/entry_link_add/id/'.$data['entry_id']);
            }
        } else {
            $this->display('item');
        }
    }

    /**
     * 添加站内链接
     */
    public function ajaxAddLink(){
        $param = I('post.');
        $link_arr = array();
        $entry_id = $param['entry_id'];
        $param_arr = json_decode($param['data'], true);
        $map['entry_id'] = $entry_id;
        $map['link_id'] = array('gt', 0);
        $has_add_link = M('EncyclopediaEntryLink')->where($map)->field('link_id')->select();
        foreach ($param_arr as &$item){
            $had_add = false;
            foreach ($has_add_link as &$item2){
                if($item['id'] == $item2['link_id']){
                    $had_add = true;
                }
            }
            if(!$had_add){
                $cur_time = time();
                $data['create_time'] = $cur_time;
                $data['entry_id'] = $entry_id;
                $data['name'] = $item['title'];
                $data['link'] = $item['url'];
                $data['link_id'] = $item['id'];
                $link_arr[] = $data;
            }
        }
        if(empty($link_arr) && !empty($param_arr)){
            $rdata['status'] = 1;
            $rdata['msg'] = '添加成功';
            $this->ajaxReturn($rdata, 'JSON');
        }
        $res = M('EncyclopediaEntryLink')->addAll($link_arr);
        if(empty($res)){
            $rdata['status'] = -1;
            $rdata['msg'] = '添加失败';
            $this->ajaxReturn($rdata, 'JSON');
        }else{
            $rdata['status'] = 1;
            $rdata['msg'] = '添加成功';
            $this->ajaxReturn($rdata, 'JSON');
        }
    }
    /**
     * 获取资讯流类型
     */
    public function getArticleTypeList(){
        $map['status'] = array('gt', -1);
        $res = M('InformationFlowConfig')->where($map)->field('id, title')->order('create_time desc')->select();
        if(empty($res)){
            $rdata['status'] = 1;
            $rdata['msg'] = '获取成功';
            $rdata['data'] = null;
            $this->ajaxReturn($rdata, 'JSON');
        }else{
            $rdata['status'] = 1;
            $rdata['msg'] = '获取成功';
            $rdata['data'] = $res;
            $this->ajaxReturn($rdata, 'JSON');
        }
    }

    /**
     * 搜索资讯流文章
     */
    public function searchArticle(){
        $name = I('name');
        $type = I('type');
        if(!empty($name)){
            $map['content_name'] = array('like', '%'.$name.'%');
        }
        if(!empty($type) && intval($type) > 0){
            $map['config_type'] = $type;
        }
        $map['status'] = 1;
        $map['content_type'] = 1;
        $res = M('InformationFlowContent')->where($map)->field('id, content_id, content_name')->group('content_id')
            ->limit(20)->order('sort desc, create_time desc')->select();
        foreach ($res as &$item){
            $item['content_url'] = C('HOST_NAME_PREFIX').'app.php/public/topcontent/version/3.3/type/view/id/'.$item['content_id'];
        }
        if(empty($res)){
            $rdata['status'] = 1;
            $rdata['msg'] = '获取成功';
            $rdata['data'] = null;
            $this->ajaxReturn($rdata, 'JSON');
        }else{
            $rdata['status'] = 1;
            $rdata['msg'] = '获取成功';
            $rdata['data'] = $res;
            $this->ajaxReturn($rdata, 'JSON');
        }
    }

    public function content_add(){
        $catalogue_id = I('catalogue_id');
        $entry_id = I('entry_id');
        $content = M('EncyclopediaEntryContent')
            ->where('`status`=1 and catalogue_id='.$catalogue_id.' and entry_id='.$entry_id)->find();
        if(empty($content)){
            $this->assign('content', '');
        }else{
            $this->assign('content', $content['content']);
            $this->assign('id', $content['id']);
        }
        $this->assign('entry_id', $entry_id);
        $this->assign('catalogue_id', $catalogue_id);
        $this->display('content_add');
    }

    public function content_update(){
        if (IS_POST) { //提交表单
            $model = M('EncyclopediaEntryContent');
            $cid = $_POST["cid"];
            $data["content"] = $_POST["content"];
            $data["catalogue_id"] = $_POST["catalogue_id"];
            $data["entry_id"] = $_POST["entry_id"];
            if(empty($cid)){
                $map['catalogue_id'] = $data["catalogue_id"];
                $map['entry_id'] = $data["entry_id"];
                $re = $model->where($map)->find();
                if(!empty($re)){
                    $model->where($map)->save($data);
                    $this->success('更新成功', '', true);
                }else{
                    $data["create_time"] = time();
                    try {
                        $model->add($data);
                    } catch (Exception $e) {
                        $this->error($e->getMessage());
                    }
                    $this->success('添加成功', '', true);
                }
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', '', true);
            }
        } else {
            $this->display('content_add');
        }
    }

    /**
     * 上传图片到OSS
     * @param $picID
     */
    public function uploadLogoPicToOSS($picID){
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
    public function fetchCdnImage($pic_id){
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

    public function item(){
        $name = I('name');
        if(!empty($name)){
            $map['name'] = array('like', '%'.$name.'%');
        }
        $cid = I('cid');
        if(!empty($cid) && intval($cid) > 0){
            $entry_ids = M('EncyclopediaEntryCatagory')->where('`status`=1 and catagory_id='.$cid)
                    ->field('entry_id')->select();
            $entry_array = array();
            foreach ($entry_ids as &$item){
                $entry_array[] = $item['entry_id'];
            }
            $map['id'] = array('in', $entry_array);
        }
        $map['status'] = 1;
        $list = M('EncyclopediaEntry')->where($map)->order('sort desc , create_time desc')->select();
        $first_category = M('EncyclopediaCategory')->where('`status`=1 and pid=0')->select();
        $pid = I('pid');
        if(!empty($pid) && intval($pid) > 0){
            $cur_list = M('EncyclopediaCategory')->where('`status`=1 and id in ('.$cid.', '.$pid.')')
                ->field('id, name')->select();
            $cur_category1 = array();
            $cur_category2 = array();
            foreach ($cur_list as $item){
                if($item['id'] == $cid){
                    $cur_category2['id'] = $cid;
                    $cur_category2['name'] = $item['name'];
                }
                if($item['id'] == $pid){
                    $cur_category1['id'] = $pid;
                    $cur_category1['name'] = $item['name'];
                }
            }
            $second_category = M('EncyclopediaCategory')->where('`status`=1 and pid='.$pid)->select();
            $this->assign('second_level_list', $second_category);
            $this->assign('cur_category1', $cur_category1);
            $this->assign('cur_category2', $cur_category2);
        }
        $this->assign('first_level_list', $first_category);
        $this->assign('_list', $list);
        $this->display('item');
    }

    /**
     * t函数用于过滤标签，输出没有html的干净的文本
     * @param string text 文本内容
     * @return string 处理后内容
     */
    function op_t($text)
    {
        $text = nl2br($text);
        $text = real_strip_tags($text);
        $text = addslashes($text);
        $text = trim($text);
        return $text;
    }

    public function getSecondCategory(){
        $pid = I('pid');
        $map['status'] = 1;
        $map['pid'] = $pid;
        $list = M('EncyclopediaCategory')->where($map)->field('id, name')
            ->order('sort desc , create_time desc')->select();
        if(empty($list)){
            $rdata['status'] = 1;
            $rdata['msg'] = '获取成功';
            $rdata['data'] = null;
            $this->ajaxReturn($rdata, 'JSON');
        }else{
            $rdata['status'] = 1;
            $rdata['msg'] = '获取成功';
            $rdata['data'] = $list;
            $this->ajaxReturn($rdata, 'JSON');
        }
    }
}
