<?php
namespace Hiworks\Controller;

use Think\Controller;
use \Think\Hook;
use Think\Page;
use Think\Log;

class IndexController extends HiworksController
{
    public function index($cate = 1, $page = 1, $download=0, $sub=0, $base=0)
    {
        $token = $_SESSION["token"];
        if(!defined('Scan') /*|| !$token*/) {
            redirect('/hiworks.php');
        }

        $map = array(
            'guid' => $token,
            'status' => -2
        );

        $modal = M('qr_scan');

        $data = $modal->where($map)->field('scan_uid')->find();
        /*if(empty($data)){
            $this->apiError(-1,"非法请求！");
        }*/

        if(!D('Home/Member')->login($data['scan_uid'])){
            $this->apiError(401,"需要登录！");
        }
        $user = query_user(array('nickname','avatar128'));

        /* --- 获取主分类  --- */
        $category = $this->category(1);
        $Category = D('Category');
        $top_children = $Category->getChildrenId($category['id']);
        if(!empty($top_children)){
            $top_children = explode(',', $top_children);
            foreach ($top_children as &$child) {
                $child = $Category->info($child);
            }
            unset($child);
            $this->setCurrent($cate);
        }
        /* ---------------- */

        /* ----获取子分类信息---- */
        $category = $this->category($cate);
        /* 获取当前分类列表 */
        $Document = D('Document');
        $Category = D('Category');
        $children = $Category->getChildrenId($category['id']);
        if(!empty($children)){
            $children = explode(',', $children);
            //将当前分类的文章和子分类的文章混合到一起
            $cates = $children;
            array_push($cates, $category['id']);
            //$list = $Document->page($page, $category['list_row'])->lists(implode(',', $cates));
            $list = $Document->page($page, 12)->lists(implode(',', $cates));
            //得到子分类的目录
            foreach ($children as &$child) {
                $child = $Category->info($child);
            }
            unset($child);
            if($cate==1){
                $this->setCurrent($children[0]['id']);
                $this->assign('detail_cate', $children[0]['id']);
            } else {
                $this->setCurrent($category['id']);
                $this->assign('detail_cate', $category['id']);
            }
        }

        if($sub==1){
            /* 分类信息 */
            $category = $this->category($cate);
            $this->setCurrent($base);
            $this->setSubCurrent($cate);
            $this->assign('detail_cate', $cate);
            /* 获取当前分类列表 */
            $Document = D('Document');
            $Category = D('Category');

            /*$children = $Category->getChildrenId($category['id']);
            if(!empty($children)){
                $children = explode(',', $children);
                //将当前分类的文章和子分类的文章混合到一起
                $cates = $children;
                array_push($cates, $category['id']);
                //$list = $Document->page($page, $category['list_row'])->lists(implode(',', $cates));
                $list = $Document->page($page, 12)->lists(implode(',', $cates));
                //得到子分类的目录
                foreach ($children as &$child) {
                    $child = $Category->info($child);
                }
                unset($child);
            } else {
                $tmp_cates = array();
                array_push($tmp_cates, $category['id']);
                //$list = $Document->page($page, $category['list_row'])->lists(implode(',', $tmp_cates));
                $list = $Document->page($page, 12)->lists(implode(',', $tmp_cates));
            }*/
            $children = $Category->getChildrenId($base);
            if(!empty($children)){
                $children = explode(',', $children);
                //将当前分类的文章和子分类的文章混合到一起
                $tmp_cates = array();
                array_push($tmp_cates, $category['id']);
                $list = $Document->page($page, 12)->lists(implode(',', $tmp_cates));
                //得到子分类的目录
                foreach ($children as &$child) {
                    $child = $Category->info($child);
                }
                unset($child);
            } else {
                $tmp_cates = array();
                array_push($tmp_cates, $category['id']);
                //$list = $Document->page($page, $category['list_row'])->lists(implode(',', $tmp_cates));
                $list = $Document->page($page, 12)->lists(implode(',', $tmp_cates));
            }
        }

        $countAll = 0;
        foreach ($children as &$child) {
            $map = array('category_id' => $child['id']);
            //////////////////////统计数字 特殊处理/////////////////////////////
            $count = D('Document')->where($map)->count('id') + 2000;
            if(($count+10) != S('Hiworks_count_'.$child['id'])) {
                $count = $count + 10;
                S('Hiworks_count_'.$child['id'],$count);
            }
            $child['files'] = S('Hiworks_count_'.$child['id']);
            ///////////////////////////////////////////////////////////////////
            $view = D('Document')->where($map)->field('view')->select();
            $child['download'] = 0;
            $child['download'] += array_sum(getSubByKey($view,'view'));
            if($category['id'] == $child['id']) {
                $child['class'] = $child['name'] . ' current';
            } else {
                $child['class'] = $child['name'];
            }
            $countAll += S('Hiworks_count_'.$child['id']);
        }
        S('Hiworks_count_all',$countAll);

        $this->assign('children_cates', $children);

        if (false === $list) {
            $this->error('获取列表数据失败！');
        }
        foreach ($list as &$info) {
            $detail = $Document->detail($info['id']);
            $cover_id = $info['cover_id'];
            $model = M();
            $result = $model->query("select path from hisihi_picture where id=".$cover_id);
            if($result){
                $path = $result[0]['path'];
                $objKey = substr($path, 17);
                $param["bucketName"] = "hisihi-other";
                $param['objectKey'] = $objKey;
                $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
                if($isExist){
                    $picUrl = "http://hisihi-other.oss-cn-qingdao.aliyuncs.com/".$objKey;
                    $data['showAdv'] = true;
                    $data['pic'] = $picUrl;
                    $info['pic_url'] = $picUrl;
                } else {
                    srand(microtime(true) * 1000);
                    $index =  rand(1, 120);
                    $info['pic_url'] = "http://hiworks.oss-cn-qingdao.aliyuncs.com/".$index.".jpg";
                }
            } else {
                srand(microtime(true) * 1000);
                $index =  rand(1, 120);
                $info['pic_url'] = "http://hiworks.oss-cn-qingdao.aliyuncs.com/".$index.".jpg";
            }

            $info['size'] = $this->conversion($detail['size']);
            $info['download'] = $detail['download'];
        }
        /* 模板赋值并渲染模板 */
        $this->assign('user',$user);
        $this->assign('category', $top_children);
        $this->setTitle('{$category.title|op_t} — 嘿云作业');
        $this->assign('list', $list);
        $count = 20;
        $Page  = new Page($count, 12);
        $show  = $Page->show();
        $this->assign('page', $show);

        if(!empty($download)){
            $this->assign('download', $download);
        }

        $this->display();
    }

    /**
     * 获取云作业分类列表
     * @param int $cate
     * @param int $base
     * @param int $page
     * @param int $count
     */
    public function getHiworksListByCate($cate=1, $base=0, $page=1, $count=12)
    {
        /* 分类信息 */
        $category = $this->category($cate);
        /* 获取当前分类列表 */
        $Document = M('Document');
        $Category = D('Category');
        $tmp_cates = array();
        if($base!=0){
            $children = $Category->getChildrenId($base);
        } else {
            $children = $Category->getChildrenId($cate);
        }
        if(!empty($children)){
            $children = explode(',', $children);
            //将当前分类的文章和子分类的文章混合到一起
            array_push($tmp_cates, $category['id']);
            if($base==0){
                foreach ($children as &$child) {
                    array_push($tmp_cates, $child);
                }
            }
            $where_array['category_id'] = array('in', implode(',', $tmp_cates));
            $where_array['status'] = 1;
            $list = $Document->field('id, category_id, title, cover_id')->order('id desc')->page($page, $count)->where($where_array)->select();
            $totalCount = D('Document')->listCount(implode(',', $tmp_cates));
        } else {
            $tmp_cates = array();
            array_push($tmp_cates, $category['id']);
            $where_array['category_id'] = array('in', implode(',', $tmp_cates));
            $where_array['status'] = 1;
            $list = $Document->field('id, category_id, title, cover_id')->order('id desc')->page($page, $count)->where($where_array)->select();
            $totalCount = D('Document')->listCount(implode(',', $tmp_cates));
        }

        foreach ($list as &$info) {
            $detail = D('Document')->detail($info['id']);
            $cover_id = $info['cover_id'];
            $model = M();
            $result = $model->query("select path from hisihi_picture where id=".$cover_id);
            if($result){
                $path = $result[0]['path'];
                $objKey = substr($path, 17);
                $param["bucketName"] = "hisihi-other";
                $param['objectKey'] = $objKey;
                $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
                if($isExist){
                    $picUrl = "http://pic.hisihi.com/".$objKey."@50p";
                    $data['showAdv'] = true;
                    $data['pic'] = $picUrl;
                    $info['pic_url'] = $picUrl;
                } else {
                    srand(microtime(true) * 1000);
                    $index =  rand(1, 120);
                    $info['pic_url'] = "http://hiworks.oss-cn-qingdao.aliyuncs.com/".$index.".jpg";
                }
            } else {
                srand(microtime(true) * 1000);
                $index =  rand(1, 120);
                $info['pic_url'] = "http://hiworks.oss-cn-qingdao.aliyuncs.com/".$index.".jpg";
            }
            unset($info['cover_id']);
            $muliti_cover_pic = $this->getMultiCoverPicURLByHiworkId($info['id']);
            $info['multi_cover_info'] = array('count'=>count($muliti_cover_pic), 'data'=>$muliti_cover_pic);
            $info['download_url'] = C('HOST_NAME_PREFIX').'/hiworks_list.php/file/downloadZip/key/'.$this->caesar_encode($info['id'], 'hisihi_hiworks_downlaod');
            $info['size'] = $this->conversion($detail['size']);
            $info['download'] = $detail['download'];
        }
        $this->apiSuccess('获取分类下云作业列表成功', null, array('data'=>$list, 'totalCount'=>(int)$totalCount));
    }

    /**
     * 根据id获取作业详情
     * @param int $hiwork_id
     * @param int $version
     */
    public function getHiworkDetailById($hiwork_id=0, $version=0){
        $Document = M('Document');
        $doc_info = $Document->field('id, category_id, title, cover_id')->where('id='.$hiwork_id)->find();
        $detail = D('Document')->detail($doc_info['id']);
        $cover_id = $doc_info['cover_id'];
        $model = M();
        $result = $model->query("select path from hisihi_picture where id=".$cover_id);
        if($result){
            $path = $result[0]['path'];
            $objKey = substr($path, 17);
            $param["bucketName"] = "hisihi-other";
            $param['objectKey'] = $objKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if($isExist){
                $picUrl = "http://pic.hisihi.com/".$objKey."@50p";
                $data['showAdv'] = true;
                $data['pic'] = $picUrl;
                $doc_info['pic_url'] = $picUrl;
            } else {
                srand(microtime(true) * 1000);
                $index =  rand(1, 120);
                $doc_info['pic_url'] = "http://hiworks.oss-cn-qingdao.aliyuncs.com/".$index.".jpg";
            }
        } else {
            srand(microtime(true) * 1000);
            $index =  rand(1, 120);
            $doc_info['pic_url'] = "http://hiworks.oss-cn-qingdao.aliyuncs.com/".$index.".jpg";
        }
        unset($doc_info['cover_id']);
        $muliti_cover_pic = $this->getMultiCoverPicURLByHiworkId($doc_info['id']);
        $doc_info['multi_cover_info'] = array('count'=>count($muliti_cover_pic), 'data'=>$muliti_cover_pic);
        $doc_info['download_url'] = C('HOST_NAME_PREFIX').'/hiworks_list.php/file/downloadZip/key/'.$this->caesar_encode($doc_info['id'], 'hisihi_hiworks_downlaod');
        $doc_info['size'] = $this->conversion($detail['size']);
        $doc_info['download'] = $detail['download'];
        if((float)$version>=2.95){
            $doc_info['next_hiwork_id'] = $this->getNextHiworkId($hiwork_id);
            $doc_info['pre_hiwork_id'] = $this->getPreHiworkId($hiwork_id);
        }
        $this->apiSuccess('获取云作业详情成功', null, array('data'=>$doc_info));

    }

    /**
     * @param int $hiwork_id
     * @return int
     */
    private function getNextHiworkId($hiwork_id=0){
        $model = M();
        $next_id = 0;
        $result = $model->query("select document.id, document.title, document.category_id, document.cover_id, download.download from hisihi_document_download as download,
                                  hisihi_document as document where download.id=document.id and document.id<".$hiwork_id." and document.cover_id!=0 and document.status=1
                                  order by document.create_time desc limit 0,1");
        foreach($result as $value){
            $next_id = $value['id'];
        }
        return $next_id;
    }

    /**
     * @param int $hiwork_id
     * @return int
     */
    private function getPreHiworkId($hiwork_id=0){
        $model = M();
        $next_id = 0;
        $result = $model->query("select document.id, document.title, document.category_id, document.cover_id, download.download from hisihi_document_download as download,
                                  hisihi_document as document where download.id=document.id and document.id>".$hiwork_id." and document.cover_id!=0 and document.status=1
                                  order by document.create_time limit 0,1");
        foreach($result as $value){
            $next_id = $value['id'];
        }
        return $next_id;
    }


    /**
     * 根据关键词搜索云作业
     * @param null $keyword
     * @param int $page
     * @param int $count
     */
    public function searchHiworksByKeyWords($keyword=null, $page=1, $count=12){
        if($keyword==null){
            $this->apiError(-1, '请输入关键词');
        }
        /* 获取当前分类列表 */
        $Document = M('Document');
        $where_array['status'] = 1;
        $where_array['category_id'] = array('neq', 47);
        $where_array['title'] = array('like', '%'.$keyword.'%');
        $list = $Document->field('id, category_id, title, cover_id')->order('id desc')->page($page, $count)->where($where_array)->select();
        $totalCount = D('Document')->where($where_array)->count();
        foreach ($list as &$info) {
            $detail = D('Document')->detail($info['id']);
            $cover_id = $info['cover_id'];
            $model = M();
            $result = $model->query("select path from hisihi_picture where id=".$cover_id);
            if($result){
                $path = $result[0]['path'];
                $objKey = substr($path, 17);
                $param["bucketName"] = "hisihi-other";
                $param['objectKey'] = $objKey;
                $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
                if($isExist){
                    $picUrl = "http://pic.hisihi.com/".$objKey."@50p";
                    $data['showAdv'] = true;
                    $data['pic'] = $picUrl;
                    $info['pic_url'] = $picUrl;
                } else {
                    srand(microtime(true) * 1000);
                    $index =  rand(1, 120);
                    $info['pic_url'] = "http://hiworks.oss-cn-qingdao.aliyuncs.com/".$index.".jpg";
                }
            } else {
                srand(microtime(true) * 1000);
                $index =  rand(1, 120);
                $info['pic_url'] = "http://hiworks.oss-cn-qingdao.aliyuncs.com/".$index.".jpg";
            }
            unset($info['cover_id']);
            $muliti_cover_pic = $this->getMultiCoverPicURLByHiworkId($info['id']);
            $info['multi_cover_info'] = array('count'=>count($muliti_cover_pic), 'data'=>$muliti_cover_pic);
            $info['download_url'] = C('HOST_NAME_PREFIX').'hiworks_list.php/file/downloadZip/key/'.$this->caesar_encode($info['id'], 'hisihi_hiworks_downlaod');
            $info['size'] = $this->conversion($detail['size']);
            $info['download'] = $detail['download'];
        }
        $this->apiSuccess('搜索云作业成功', null, array('data'=>$list, 'totalCount'=>(int)$totalCount));
    }

    /**
     * 获取云作业的预览图列表
     * @param int $hiwork_id
     * @return array
     */
    private function getMultiCoverPicURLByHiworkId($hiwork_id=0){
        $url_array = Array();
        $Document = M('Document');
        $model = M();
        $where_array['id'] = $hiwork_id;
        $info = $Document->field('multi_cover_pic')->where($where_array)->find();
        if($info){
            $result_array = explode(',',$info['multi_cover_pic']);
            foreach($result_array as $pic_id){
                $result = $model->query("select path from hisihi_picture where id=".$pic_id);
                if($result){
                    $path = $result[0]['path'];
                    $objKey = substr($path, 17);
                    $param["bucketName"] = "hisihi-other";
                    $param['objectKey'] = $objKey;
                    $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
                    if($isExist){
                        $picUrl = "http://pic.hisihi.com/".$objKey;
                        $url_array[] = $picUrl;
                    }
                }
            }
        }
        return $url_array;
    }

    private function caesar_encode($s, $k) {
        $k = "$k";
        for($i=0; $i<strlen($k); $i++) {
            $d = base_convert($k{$i}, 36, 10);
            $t = '';
            for($j=0; $j<strlen($s); $j++)
                $t .= base_convert((base_convert($s{$j}, 36, 10)+$d)%36, 10, 36);
            $s = $t;
        }
        return $t;
    }

    public function preview($cate = 1, $page = 1)
    {
        //if(session('is_scanned') != 1 || session('scan_uid') != get_uid())
        //    redirect('hiworks.php');

        $user = query_user(array('nickname','avatar128'),69);

        /* 分类信息 */
        $category = $this->category($cate);

        /* 获取当前分类列表 */
        $Document = D('Document');
        $Category = D('Category');

        $children = $Category->getChildrenId($category['id']);
        if ($children == '') {
            //获取当前分类下的文章
            $list = $Document->page($page, $category['list_row'])->lists($category['id']);
            $is_top_category = ($category['pid'] == 0);
            if (!$is_top_category) {//判断是否是顶级分类，如果是顶级，就算没有子分类，也不获取同级
                //如果是不是顶级分类
                $children = $Category->getSameLevel($category['id']);
                $this->setCurrent($category['pid']);
                //$this->assign('children_cates', $children);
            } else {
                //如果是顶级分类
                $this->setCurrent($category['id']);
            }

        } else {
            //如果还有子分类
            //分割分类
            $children = explode(',', $children);
            //将当前分类的文章和子分类的文章混合到一起
            $cates = $children;
            array_push($cates, $category['id']);
            $list = $Document->page($page, $category['list_row'])->lists(implode(',', $cates));
            //dump($children);exit;
            //得到子分类的目录
            foreach ($children as &$child) {
                $child = $Category->info($child);
            }
            unset($child);
            $this->setCurrent($category['id']);
            $category['id'] = implode(',', $cates);
            //$this->assign('children_cates', $children);
        }

        if (false === $list) {
            $this->error('获取列表数据失败！');
        }
        $countAll = 0;
        foreach ($children as &$child) {
            $map = array('category_id' => $child['id']);
            //////////////////////统计数字 特殊处理/////////////////////////////
            $count = D('Document')->where($map)->count('id') + 2000;
            if(($count+10) != S('Hiworks_count_'.$child['id'])) {
                $count = $count + 10;
                S('Hiworks_count_'.$child['id'],$count);
            }
            $child['files'] = S('Hiworks_count_'.$child['id']);
            ///////////////////////////////////////////////////////////////////
            $view = D('Document')->where($map)->field('view')->select();
            $child['download'] = 0;
            $child['download'] += array_sum(getSubByKey($view,'view'));
            if($category['id'] == $child['id']) {
                $child['class'] = $child['name'] . ' current';
            } else {
                $child['class'] = $child['name'];
            }
        }
        S('Hiworks_count_all',$countAll);
        $this->assign('children_cates', $children);

        foreach ($list as &$info) {
            $detail = $Document->detail($info['id']);
            $info['size'] = $this->conversion($detail['size']);
            $info['download'] = $detail['download'];
        }
        /* 模板赋值并渲染模板 */
        $this->assign('user',$user);
        $this->assign('category', $category);
        $this->setTitle('{$category.title|op_t} — 嘿云作业');
        $this->assign('list', $list);
        $this->assign('page', D('Document')->page); //分页
        $this->display();
    }

    /* 文档分类检测 */
    private function category($id = 1)
    {
        /* 标识正确性检测 */
        $id = $id ? $id : I('get.category', 0);
        if (empty($id)) {
            $this->error('没有指定文档分类！');
        }
        /* 获取分类信息 */
        $category = D('Category')->info($id);
        if ($category && 1 == $category['status']) {
            switch ($category['display']) {
                case 0:
                    $this->error('该分类禁止显示！');
                    break;
                //TODO: 更多分类显示状态判断
                default:
                    return $category;
            }
        } else {
            $this->error('分类不存在或被禁用！');
        }
    }

    private function setCurrent($category_id)
    {
        $this->assign('current', $category_id);
    }

    private function setSubCurrent($category_id)
    {
        $this->assign('subCurrent', $category_id);
    }

    protected function apiReturn($success, $error_code=0, $message=null, $redirect=null, $extra=null) {
        //生成返回信息
        $result = array();
        $result['success'] = $success;
        $result['error_code'] = $error_code;
        if($message !== null) {
            $result['message'] = $message;
        }
        if($redirect !== null) {
            $result['redirect'] = $redirect;
        }
        foreach($extra as $key=>$value) {
            $result[$key] = $value;
        }
        //将返回信息进行编码
        $format = $_REQUEST['format'] ? $_REQUEST['format'] : 'json';//返回值格式，默认json
        if($this->isInternalCall) {
            throw new ReturnException($result);
        } else if($format == 'json') {
            echo json_encode($result);
            exit;
        } else if($format == 'xml') {
            echo xml_encode($result);
            exit;
        } else {
            $_GET['format'] = 'json';
            $_REQUEST['format'] = 'json';
            return $this->apiError(400, "format参数错误");
        }
    }

    protected function apiSuccess($message, $redirect=null, $extra=null) {
        return $this->apiReturn(true, 0, $message, $redirect, $extra);
    }

    protected function apiError($error_code, $message, $redirect=null, $extra=null) {
        return $this->apiReturn(false, $error_code, $message, $redirect, $extra);
    }

    //字节换算
    private function conversion($size,$digits = 1)
    {
        $kb = 1024; // 1KB（Kibibyte，千字节）=1024B，
        $mb = 1024 * $kb; //1MB（Mebibyte，兆字节，简称“兆”）=1024KB，
        $gb = 1024 * $mb; // 1GB（Gigabyte，吉字节，又称“千兆”）=1024MB，
        $tb = 1024 * $gb; // 1TB（Terabyte，万亿字节，太字节）=1024GB，
        $pb = 1024 * $tb; //1PB（Petabyte，千万亿字节，拍字节）=1024TB，
        $fb = 1024 * $pb; //1EB（Exabyte，百亿亿字节，艾字节）=1024PB，
        $zb = 1024 * $fb; //1ZB（Zettabyte，十万亿亿字节，泽字节）= 1024EB，
        $yb = 1024 * $zb; //1YB（Yottabyte，一亿亿亿字节，尧字节）= 1024ZB，
        $bb = 1024 * $yb; //1BB（Brontobyte，一千亿亿亿字节）= 1024YB

        if ($size < $kb) {
            return $size . " B";
        } else if ($size < $mb) {
            return round($size / $kb, $digits) . " KB";
        } else if ($size < $gb) {
            return round($size / $mb, $digits) . " MB";
        } else if ($size < $tb) {
            return round($size / $gb, $digits) . " GB";
        } else if ($size < $pb) {
            return round($size / $tb, $digits) . " TB";
        } else if ($size < $fb) {
            return round($size / $pb, $digits) . " PB";
        } else if ($size < $zb) {
            return round($size / $fb, $digits) . " EB";
        } else if ($size < $yb) {
            return round($size / $zb, $digits) . " ZB";
        } else {
            return round($size / $bb, 2) . " YB";
        }
    }
}