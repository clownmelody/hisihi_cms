<?php

namespace Addons\OTcaiji\Controller;

use Admin\Controller\AdminController;
use Admin\Model\AuthGroupModel;
use Admin\Model\AuthRuleModel;
use Home\Controller\AddonsController;

/*
  1、获取列表地址
  2、存储到自己的db
  ================
  3、读取列表地址
  4、获取地址内容
  5、存储内容
  6、跳转到下一条继续
 */

class OTcaijiController extends AddonsController {

    //采集列表
    Public function caji() {
        $config = $this->getConfig();
        if (!$config['codelogin']) {
            $this->error('插件未开启，请先开启！');
        }
        $url = $config['URL'];
        $ur = array_filter(preg_split("/[\r\n]/", $url));
        $urls = array(); //url地址
        $yu = array(); //域名
        $urlneme = array(); //网站名称
        $indexPage = array(); //页码首页标识
        foreach ($ur as $value) {
            $temp = explode("@", $value);
            $urls[$temp[0]] = $temp[1];
            $yu[$temp[0]] = $temp[3];
            $urlneme[$temp[0]] = $temp[2];
            if (!empty($temp[4])) {
                $indexPage[$temp[0]] = $temp[4];
            }
        }
        //获取所有网址数组键名
        $arr_key = array_keys($urls);     
        //获取列表正则
        $preg = $config['LIST'];
        $pre = array_filter(preg_split("/[\r\n]/", $preg));
        $pregs = array();
        foreach ($pre as $value) {
            $temp = explode("@", $value);
            $pregs[$temp[0]] = $temp[1];
        }
        //初始化页码
        $page = F('caiji_page');
        if (empty($page)) {
            $page = 1;
            F('caiji_page', $page);
        }
        $index = F("caiji_index");
        if (empty($index)) {
            $index = array_shift($arr_key);
            F('caiji_index', $index);
        }      
        $ul = str_replace('{$page}', $page, $urls[$index]);
        if (empty($ul)) {
            foreach ($arr_key as $k => $v) {
                if ($index == $v) {
                    $index = $arr_key[$k++];
                    F('caiji_index', $index);
                }
            }
            $ul = str_replace('{$page}', $page, $urls[$index]);
        }
        $con[$index] = '';
        if (!empty($indexPage) && $page == 1) {
            $ul = str_replace('1.', $indexPage[1] . '.', $ul);
            $con[$index] = $this->curl($ul);
        } else {
            $con[$index] = $this->curl($ul);
        }     
        if (preg_match_all($pregs[$index], $con[$index], $arr[$index])) {
            $end = false;
            $keyword = $config['KEYWORDS']; //获取关键词，然后根据关键词采集
            $key = explode("|", $keyword); //吧连续的$keyword字符串分割成数组
            foreach ($arr[$index][1] as $id => $v) {

                $title = iconv('GB2312', 'UTF-8', $arr[$index][2][$id]);
                $v = iconv('GB2312', 'UTF-8', $v);
                if (strpos($ul, "http") === false) {//判断是否包含域名
                    $v = substr($yu, 0, strlen($yu) - 1) . $v; //截取掉域名最后的‘/’
                } else {
                    $v = $v;
                }
                $data = '';

                if (!empty($key[0])) {//关键词存在的处理
                    if (strlen(str_replace($key, "", $title)) < strlen($title)) {//关键词对比-》不匹配跳出
                        $datas = array(
                            'title' => $title,
                            'url' => $v,
                            'source' => $urlneme[$index],
                            'sourceurl' => $yu[$index],
                            'dates' => time(),
                            'st' => $index,
                        );
                    } else {
                        $str[] = '没有找到与<font color="red">' . $keyword . '</font>匹配的数据</br>';
                        continue;
                    }
                    $data = $datas;
                } else {
                    $dat = array(
                        'title' => $title,
                        'url' => $v,
                        'source' =>$urlneme[$index],
                        'sourceurl' => $yu[$index],
                        'dates' => time(),
                        'st' => $index,
                    );
                    $data = $dat;
                }
                //排除数据库中已经存在的数据
                $old = M('listtmp')->where(array('title' => $data['title']))->select();
                if ($old[0]) {
                    $str[] = '<font color="blue">' . $title . '</font>已经存在</br>';
                    $end = true;
                    break;
                }
                $str[] = '正在采集....<font color="green">' . $title . '</font></br>';
                if (M('Listtmp')->add($data)) {
                    $str[] = '采集成功!</br>';
                }
            }
            $page++; //执行完一页page加1
            F("caiji_page", $page);
            //=====单网址所有页面执行完毕
            if ($page > 5 || $end) {//执行完一个地址index加1
                $sites_keys_index = array_search($index, $arr_key);
                if ($sites_keys_index == count($arr_key) - 1) {
                    $index = $arr_key[0];
                } else {
                    $index = $sites_keys[$sites_keys_index + 1];
                }
                F("caiji_index", $index);
                F("caiji_page", 1);
            } else {
                F("caiji_page", $page);
            }
        }
        $this->assign('str', $str);
        $this->display(T('Addons://OTcaiji@OTcaiji/index'));
    }
    /**
     * 读取分类
     * @param string $address
     * autor:Marvin9002
     */
    public function getCategory(){
        $id=I('get.id');
        $cate = M('Category')->where(array('status' => 1))->field('id,title,pid,allow_publish')->order('pid,sort')->select();
        $this->assign('cate',$cate);
        $this->assign('id',$id);
        $this->display(T('Addons://OTcaiji@OTcaiji/cate'));
        
    }
    //CURL获取数据
    Public function curl($ul, $index) {
        $curl = curl_init(); //初始化curl
        curl_setopt($curl, CURLOPT_URL, $ul); //设置访问的网址$ul
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //执行后不直接打印
        $con = curl_exec($curl); //执行
        curl_close($curl); //关闭资源
        return $con;
    }

    //获取表前缀
    public function db_prefix() {
        $db_prefix = C('DB_PREFIX');
        return $db_prefix;
    }

    /*
     * 1、点击入库跳转到一个表单页面
     * 2、自动填写表单信息
     * 3、选择分类
     * 4、提交数据库
     */

    //获取标题及内容
    Public function conTmp($id) {
        $w = array('id' => $id);
        $q = M('Listtmp')->where($w)->select();
//        if($q[0]['zt']==0){
//            $data['status'] = 1;
//            $data['content'] = '已经入库';
//            $this->ajaxReturn($data);
//        }
        $q[0]['url'] ? null : exit("执行完毕");
        $con = file_get_contents($q[0][url]);
//        if (mb_detect_encoding($con) == 'UTF-8') {//判断内容编码是否为UTF-8
//            $con = $con;
//        } else {
//            $con = iconv('GB2312', 'UTF-8', $con);
//        }
        $config = $this->getConfig();
        $title = $config['TITLE'];
        $tit = array_filter(preg_split("/[\r\n]/", $title));
        $titles = array();
        foreach ($tit as $value) {
            $temp = explode("@", $value);
            $titles[$temp[0]] = $temp[1];
        }
        preg_match_all($titles[$q[0]['st']], $con, $arr);
        $tt = str_replace('\n\r', '', $arr[1][0]);
        $tt = iconv('GB2312', 'UTF-8', $tt);
        $content = $config['CONTENT'];
        $cont = array_filter(preg_split("/[\r\n]/", $content));
        $contents = array();
        foreach ($cont as $value) {
            $temp = explode("@", $value);
            $contents[$temp[0]] = $temp[1];
        }
        preg_match_all($contents[$q[0]['st']], $con, $arr2);
        $cc = str_replace("'", "\"", $arr2[1][0]);
        $cc = iconv('GB2312', 'UTF-8', $cc);
        $data = array(
            'title' => $tt,
            'cont' => $cc,
            'create_time' => time(),
        );
        //把获取到的信息传输给add()
        return $data;
    }

    //删除不用的文章
    public function delList() {
        $id = $_GET['id'];
        if (M('Listtmp')->where(array('id' => $id))->delete()) {
            $this->success('删除成功');
        }
    }

    /**
     * 获取插件的配置数组
     */
    final public function getConfig() {
        $config = array();
        $map['name'] = "OTcaiji";
        $map['status'] = 1;
        $config = M('Addons')->where($map)->getField('config');
        if ($config) {
            $config = json_decode($config, true);
        } else {
            $this->addon_path = ONETHINK_ADDON_PATH . 'OTcaiji/';
            if (is_file($this->addon_path . 'config.php')) {
                $this->config_file = $this->addon_path . 'config.php';
            }
            $temp_arr = include $this->config_file;
            foreach ($temp_arr as $key => $value) {
                if ($value['type'] == 'group') {
                    foreach ($value['options'] as $gkey => $gvalue) {
                        foreach ($gvalue['options'] as $ikey => $ivalue) {
                            $config[$ikey] = $ivalue['value'];
                        }
                    }
                } else {
                    $config[$key] = $temp_arr[$key]['value'];
                }
            }
        }
        return $config;
    }

    /* 保存允许访问的公共方法 */

    static protected $allow = array('draftbox', 'mydocument');
    private $cate_id = null; //文档分类id

    /**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */

    final protected function checkRule($rule, $type = AuthRuleModel::RULE_URL, $mode = 'url') {
        if (IS_ROOT) {
            return true; //管理员允许访问任何页面
        }
        static $Auth = null;
        if (!$Auth) {
            $Auth = new \Think\Auth();
        }
        if (!$Auth->check($rule, UID, $type, $mode)) {
            return false;
        }
        return true;
    }

    /**
     * 显示左边菜单，进行权限控制
     * @author huajie <banhuajie@163.com>
     */
    protected function getMenu() {
        //获取动态分类
        $cate_auth = AuthGroupModel::getAuthCategories(UID); //获取当前用户所有的内容权限节点
        $cate_auth = $cate_auth == null ? array() : $cate_auth;
        $cate = M('Category')->where(array('status' => 1))->field('id,title,pid,allow_publish')->order('pid,sort')->select();

        //没有权限的分类则不显示
        if (!IS_ROOT) {
            foreach ($cate as $key => $value) {
                if (!in_array($value['id'], $cate_auth)) {
                    unset($cate[$key]);
                }
            }
        }
        $cate = list_to_tree($cate); //生成分类树
        //获取分类id
        $cate_id = I('param.cate_id');
        $this->cate_id = $cate_id;
        //是否展开分类
        $hide_cate = false;
        if (ACTION_NAME != 'recycle' && ACTION_NAME != 'draftbox' && ACTION_NAME != 'mydocument') {
            $hide_cate = true;
        }
        //生成每个分类的url
        foreach ($cate as $key => &$value) {
            $value['url'] = 'Article/index?cate_id=' . $value['id'];
            if ($cate_id == $value['id'] && $hide_cate) {
                $value['current'] = true;
            } else {
                $value['current'] = false;
            }
            if (!empty($value['_child'])) {
                $is_child = false;
                foreach ($value['_child'] as $ka => &$va) {
                    $va['url'] = 'Article/index?cate_id=' . $va['id'];
                    if (!empty($va['_child'])) {
                        foreach ($va['_child'] as $k => &$v) {
                            $v['url'] = 'Article/index?cate_id=' . $v['id'];
                            $v['pid'] = $va['id'];
                            $is_child = $v['id'] == $cate_id ? true : false;
                        }
                    }
                    //展开子分类的父分类
                    if ($va['id'] == $cate_id || $is_child) {
                        $is_child = false;
                        if ($hide_cate) {
                            $value['current'] = true;
                            $va['current'] = true;
                        } else {
                            $value['current'] = false;
                            $va['current'] = false;
                        }
                    } else {
                        $va['current'] = false;
                    }
                }
            }
        }
        $this->assign('nodes', $cate);
        $this->assign('cate_id', $this->cate_id);
        //获取面包屑信息
        $nav = get_parent_category($cate_id);
        $this->assign('rightNav', $nav);
        //获取回收站权限
        $show_recycle = $this->checkRule('Admin/article/recycle');
        $this->assign('show_recycle', IS_ROOT || $show_recycle);
        //获取草稿箱权限
        $this->assign('show_draftbox', C('OPEN_DRAFTBOX'));
    }

    /**
     * 文档新增页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function add() {
        $id = $_GET['id'];
        //获取左边菜单
        $this->getMenu();
        $cate_id = I('get.cate_id', 0);
        $model_id = I('get.model_id', 0);
        empty($cate_id) && $this->error('参数不能为空！');
        empty($model_id) && $this->error('该分类未绑定模型！');
        $cate = M('Category')->where(array('status' => 1))->field('id,title,pid,allow_publish')->order('pid,sort')->select();
        //检查该分类是否允许发布
         $allow_publish = check_category($cate_id);
        !$allow_publish && $this->error('该分类不允许发布内容！');
        /* 获取要编辑的扩展模型模板 */
        $model = get_document_model($model_id);
        //处理结果
        $info['pid'] = $_GET['pid'] ? $_GET['pid'] : 0;
        $info['model_id'] = $model_id;
        $info['category_id'] = $cate_id;
        if ($info['pid']) {
            // 获取上级文档
            $article = M('Document')->field('id,title,type')->find($info['pid']);
            $this->assign('article', $article);
        }
        $res = $this->conTmp($id);
        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('data', $res);
        $this->assign('list_id', $id);
        $this->assign('info', $info);
        $this->assign('fields', $fields);
        $this->assign('type_list', get_type_bycate($cate_id));
        $this->assign('model', $model);
        $this->meta_title = '新增' . $model['title'];
        $this->display(T('Addons://OTcaiji@OTcaiji/add'));
       
    }
    /**
    * 验证分类是否允许发布内容
    * @param  integer $id 分类ID
    * @return boolean     true-允许发布内容，false-不允许发布内容
    */
   function check_category($id){
       if (is_array($id)) {
           $type = get_category($id['category_id'], 'type');
           $type = explode(",", $type);
           return in_array($id['type'], $type);
       } else {
           $publish = get_category($id, 'allow_publish');
           return $publish ? true : false;
       }
   }
    /**
     * 更新一条数据
     * @author huajie <banhuajie@163.com>
     */
    public function update() {
        $id = $_POST['list_id'];
        //var_dump($id);
        $res = D('Document')->update();
        if (!$res) {
            $this->error(D('Document')->getError());
        } else {
            //var_dump($res);die;
             //入库后更改数据状态
            M('Listtmp')->where(array('id' => $id))->save(array('zt'=>0));
            $this->success($res['id'] ? '更新成功' : '新增成功', Cookie('__forward__'));
        }
    }

}
