<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-11
 * Time: PM5:41
 */

namespace Admin\Controller;

use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminSortBuilder;
use Admin\Builder\AdminTreeListBuilder;
use Think\Hook;
use Think\Page;

class ForumController extends AdminController
{

    public function index()
    {
        redirect(U('forum'));
    }

    public function config()
    {
        $admin_config = new AdminConfigBuilder();
        $data = $admin_config->handleConfig();
        if (!$data) {
            $data['LIMIT_IMAGE'] = 10;
            $data['FORUM_BLOCK_SIZE'] = 4;
            $data['CACHE_TIME']=300;
        }

        $admin_config->title('论坛基本设置')
            ->keyInteger('LIMIT_IMAGE', '帖子图片解析数量限制', '超过数量限制就不会被解析出来，不填则默认为10张')
            ->keyInteger('FORUM_BLOCK_SIZE', '论坛板块列表板块所占尺寸', '默认为4,，值可填1到12,共12块，数值代表每个板块所占块数，一行放3个板块则为4，一行放4个板块则为3')
            ->keyInteger('CACHE_TIME','板块数据缓存时间','默认300秒')
            ->buttonSubmit('', '保存')->data($data);
        $admin_config->display();
    }

    public function forum($page = 1, $r = 20)
    {
        //读取数据
        $map = array('status' => array('GT', -1));
        $model = M('Forum');
        $list = $model->where($map)->page($page, $r)->order('sort asc')->select();
        $totalCount = $model->where($map)->count();

        foreach ($list as &$v) {
            $v['post_count'] = D('ForumPost')->where(array('forum_id' => $v['id']))->count();
        }

        //显示页面
        $builder = new AdminListBuilder();
        $builder
            ->title('板块管理')
            ->buttonNew(U('Forum/editForum'))
            ->setStatusUrl(U('Forum/setForumStatus'))->buttonEnable()->buttonDisable()->buttonDelete()
            ->buttonSort(U('Forum/sortForum'))
            ->keyId()->keyLink('title', '标题', 'Forum/post?forum_id=###')
            ->keyCreateTime()->keyText('post_count', '主题数量')->keyStatus()->keyDoActionEdit('editForum?id=###')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function type()
    {
        $list = D('Forum/ForumType')->getTree();
        $treeBuilder = new AdminTreeListBuilder();

        $treeBuilder->buttonNew(U('addtype'));
        $treeBuilder->title('论坛分类管理')->setLevel(1)
            ->setModel('type')
            ->disableMerge()
            ->disableMove()
            ->data($list);
        $treeBuilder->display();
    }

    public function setTypeStatus($ids=array(),$status=1){
        if(is_array($ids)){
            $map['id']=array('in',implode(',',$ids));
        }else{
            $map['id']=$ids;
        }
        $result= D('Forum/ForumType')->where($map)->setField('status',$status);
        $this->success('设置成功。'.'影响了'.$result.'条记录。');
    }


    public function addType()
    {
        $aId = I('id', 0, 'intval');
        if (IS_POST) {
            $aPid = I('pid', 0, 'intval');
            $aSort = I('sort', 0, 'intval');
            $aStatus = I('status', -2, 'intval');
            $aTitle = I('title', '', 'op_t');
            if ($aId != 0)
                $type['id'] = $aId;

            $type['sort'] = $aSort;
            $type['pid'] = $aPid;
            if ($aStatus != -2)
                $type['status'] = $aStatus;
            $type['title'] = $aTitle;
            if ($aId != 0) {
                $result = M('ForumType')->save($type);
            } else {
                $result = M('ForumType')->add($type);
            }
            if ($result) {
                $this->success('成功。');
            } else {
                $this->error('出错。');
            }


        }


        $type = M('ForumType')->find($aId);
        if (!$type) {
            $type['status'] = 1;
            $type['sort'] = 1;
        }
        $configBuilder = new AdminConfigBuilder();
        $configBuilder->title('编辑分类');
        $configBuilder->keyId()
            ->keyText('title', '分类名')
            ->keyInteger('sort', '排序')
            ->keyStatus()
            ->buttonSubmit()
            ->buttonBack();


        $configBuilder->data($type);
        $configBuilder->display();

    }

    public function forumTrash($page = 1, $r = 20, $model = '')
    {
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        //读取回收站中的数据
        $map = array('status' => '-1');
        $model = M('Forum');
        $list = $model->where($map)->page($page, $r)->order('sort asc')->select();
        $totalCount = $model->where($map)->count();

        //显示页面

        $builder
            ->title('板块回收站')
            ->setStatusUrl(U('Forum/setForumStatus'))->buttonRestore()->buttonClear('forum')
            ->keyId()->keyLink('title', '标题', 'Forum/post?forum_id=###')
            ->keyCreateTime()->keyText('post_count', '帖子数量')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function sortForum()
    {
        //读取贴吧列表
        $list = M('Forum')->where(array('status' => array('EGT', 0)))->order('sort asc')->select();

        //显示页面
        $builder = new AdminSortBuilder();
        $builder->title('贴吧排序')
            ->data($list)
            ->buttonSubmit(U('doSortForum'))->buttonBack()
            ->display();
    }

    public function setForumStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('Forum', $ids, $status);
    }

    public function doSortForum($ids)
    {
        $builder = new AdminSortBuilder();
        $builder->doSort('Forum', $ids);
    }

    public function editForum($id = null, $title = '', $create_time = 0, $status = 1, $allow_user_group = 0, $logo = 0)
    {
        if (IS_POST) {
            //判断是否为编辑模式
            $isEdit = $id ? true : false;

            //生成数据
            $data = array('title' => $title, 'create_time' => $create_time, 'status' => $status, 'allow_user_group' => $allow_user_group, 'logo' => $logo
            , 'type_id' => I('type_id', 1, 'intval'), 'background' => I('background', 0, 'intval'), 'description' => I('description', '', 'op_t'));

            //写入数据库
            $model = M('Forum');
            if ($isEdit) {
                $data['id'] = $id;
                $data = $model->create($data);
                $result = $model->where(array('id' => $id))->save($data);
                if (!$result) {
                    $this->error('编辑失败');
                }
            } else {
                $data = $model->create($data);
                $result = $model->add($data);
                if (!$result) {
                    $this->error('创建失败');
                }
            }

            S('forum_list', null);
            //返回成功信息
            $this->success($isEdit ? '编辑成功' : '保存成功');

        } else {
            //判断是否为编辑模式
            $isEdit = $id ? true : false;

            //如果是编辑模式，读取贴吧的属性
            if ($isEdit) {
                $forum = M('Forum')->where(array('id' => $id))->find();
            } else {
                $forum = array('create_time' => time(), 'post_count' => 0, 'status' => 1);
            }
            $types = M('ForumType')->where(array('status' => 1))->select();
            foreach ($types as $t) {
                $type_id_array[$t['id']] = $t['title'];
            }


            //显示页面
            $builder = new AdminConfigBuilder();
            $builder
                ->title($isEdit ? '编辑贴吧' : '新增贴吧')
                ->keyId()->keyTitle()
                ->keyTextArea('description', '板块描述', '可用Html语法')
                ->keySelect('type_id', '分类板块', '选择板块所在分类', $type_id_array)
                ->keyMultiUserGroup('allow_user_group', '允许发帖的用户组')
                ->keySingleImage('logo', '板块图标', '用于显示的封面755px*130px')
                ->keySingleImage('background', '板块背景', '板块背景图')
                ->keyStatus()
                ->keyCreateTime()
                ->data($forum)
                ->buttonSubmit(U('editForum'))->buttonBack()
                ->display();
        }

    }


    public function post($page = 1, $forum_id = null, $r = 20, $title = '', $content = '',
                         $showtop=0, $showelite=null)
    {
        //读取帖子数据
        #$map = array('status' => array('EGT', 0));
        if ($title != '') {
            $map['title'] = array('like', '%' . $title . '%');
        }
        if ($content != '') {
            $map['content'] = array('like', '%' . $content . '%');
        }
        if($showtop==1) $map['is_top'] = 1;
        if($showelite==1) $map['is_elite'] = 1;
        if ($forum_id) $map['forum_id'] = $forum_id;
        $model = M('ForumPost');
        $list = $model->where($map)->order('last_reply_time desc')->page($page, $r)->select();
        if(S('admin_forum_forum_post_total_count'.md5($map))){
            $totalCount = S('admin_forum_forum_post_total_count'.md5($map));
        } else {
            $totalCount = $model->where($map)->count();
            S('admin_forum_forum_post_total_count'.md5($map), $totalCount, 600);
        }
        foreach ($list as &$v) {
            if ($v['is_top'] == 1) {
                $v['top'] = '版内置顶';
            } else if ($v['is_top'] == 2) {
                $v['top'] = '全局置顶';
            } else {
                $v['top'] = '不置顶';
            }
            $v['content'] = substr(strip_tags($v['content']), 0,120);
        }
        //读取板块基本信息
        if ($forum_id) {
            $forum = M('Forum')->where(array('id' => $forum_id))->find();
            $forumTitle = ' - ' . $forum['title'];
        } else {
            $forumTitle = '';
        }

        //显示页面
        $builder = new AdminListBuilder();
        $builder->title('帖子管理'.$forumTitle)
            ->setStatusUrl(U('Forum/setPostStatus'))->buttonEnable()->buttonDisable()->buttonDelete()->buttonNew(U('Forum/addTopPost'))
            ->ajaxButton(U('Forum/pushTopPost'),null,'推送')->buttonNew(U('Forum/post?showtop=1'),'显示置顶帖')
            ->buttonNew(U('Forum/post?showelite=1'),'显示精华帖')
            ->ajaxButton(U('Forum/unsetPostElite'),null,'取消精华帖')
            /*->keyId()->keyLink('title','标题','Forum/reply?post_id=###')*/
            ->keyId()->keyLink('content','内容','Forum/reply?post_id=###')
            ->keyCreateTime()
            /*->keyUpdateTime()->keyTime('last_reply_time','最后回复时间')*/
            ->key('top','是否置顶')
            ->keyStatus()->keyDoActionEdit('editPost?id=###')->keyDoActionHide()
            ->setEliteUrl(U('Forum/setPostElite'))->keyDoActionElite()
            ->setSearchPostUrl()
            /*->search('标题','title')*/
            ->search('内容','content')
            ->data($list)
            ->pagination($totalCount,$r)
            ->display();
    }

    /**
     * 显示置顶帖
     * @param int $page
     * @param int $r
     */
    /*public function showTopPost($page = 1, $forum_id = null, $r = 20, $title = '', $content = '')
    {
        //读取帖子数据
        $map = array('status' => array('EGT', 0));
        if ($title != '') {
            $map['title'] = array('like', '%' . $title . '%');
        }
        if ($content != '') {
            $map['content'] = array('like', '%' . $content . '%');
        }
        $map['is_top'] = array('EQ',1);
        if ($forum_id) $map['forum_id'] = $forum_id;
        $model = M('ForumPost');
        $list = $model->where($map)->order('last_reply_time desc')->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        foreach ($list as &$v) {
            if ($v['is_top'] == 1) {
                $v['top'] = '版内置顶';
            } else if ($v['is_top'] == 2) {
                $v['top'] = '全局置顶';
            } else {
                $v['top'] = '不置顶';
            }
        }
        //读取板块基本信息
        if ($forum_id) {
            $forum = M('Forum')->where(array('id' => $forum_id))->find();
            $forumTitle = ' - ' . $forum['title'];
        } else {
            $forumTitle = '';
        }

        //显示页面
        $builder = new AdminListBuilder();
        $builder->title('帖子管理' . $forumTitle)
            ->setStatusUrl(U('Forum/setPostStatus'))->buttonEnable()->buttonDisable()->buttonDelete()->buttonNew(U('Forum/addTopPost'))
            ->ajaxButton(U('Forum/pushTopPost'), null, '推送')
            ->keyId()->keyLink('title', '标题', 'Forum/reply?post_id=###')
            ->keyCreateTime()->keyUpdateTime()->keyTime('last_reply_time', '最后回复时间')->key('top', '是否置顶')->keyStatus()->keyDoActionEdit('editPost?id=###')
            ->setSearchPostUrl()->search('标题', 'title')->search('内容', 'content')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }*/

    public function postTrash($page = 1, $r = 20)
    {
        //显示页面
        $builder = new AdminListBuilder();
        $builder->clearTrash('ForumPost');
        //读取帖子数据
        $map = array('status' => -1);
        $model = M('ForumPost');
        $list = $model->where($map)->order('last_reply_time desc')->page($page, $r)->select();
        $totalCount = $model->where($map)->count();


        $builder->title('帖子回收站')
            ->setStatusUrl(U('Forum/setPostStatus'))->buttonRestore()->buttonClear('ForumPost')
            ->keyId()->keyLink('title', '标题', 'Forum/reply?post_id=###')
            ->keyCreateTime()->keyUpdateTime()->keyTime('last_reply_time', '最后回复时间')->keyBool('is_top', '是否置顶')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function editPost($id = null, $title = '', $content = '', $create_time = 0, $update_time = 0, $last_reply_time = 0,
                             $type = '普通', $is_top = 0, $is_out_link=0, $link_url=null, $is_inner=0, $cover_id=0, $community=1)
    {
        if (IS_POST) {
            //判断是否为编辑模式
            $isEdit = $id ? true : false;

            //写入数据库
            $model = M('ForumPost');
            $data = array('title' => $title, 'content' => $content, 'create_time' => $create_time, 'update_time' => $update_time,
                'last_reply_time' => $last_reply_time, 'type'=>$type, 'is_top' => $is_top, 'is_out_link' => $is_out_link,
                'link_url' => $link_url, 'is_inner' => $is_inner, 'cover_id' => $cover_id, 'community' => $community);
            $this->uploadLogoPicToOSS($cover_id);
            if ($isEdit) {
                $result = $model->where(array('id' => $id))->save($data);
            } else {
                $result = $model->keyDoActionEdit($data);
            }
            //如果写入不成功，则报错
            if (!$result) {
                $this->error($isEdit ? '编辑失败' : '创建成功', U('post'));
            }
            //返回成功信息
            $this->success($isEdit ? '编辑成功' : '创建成功', U('post'));
        } else {
            //判断是否在编辑模式
            $isEdit = $id ? true : false;

            //读取帖子内容
            if ($isEdit) {
                $post = M('ForumPost')->where(array('id' => $id))->find();
            } else {
                $post = array();
            }

            //显示页面
            $community_list = M('ForumConfig')->field('id, name')->where('type=1 and status=1')->select();
            $community_array = array();
            foreach($community_list as $community){
                $community_array[$community['id']] = $community['name'];
            }
            $builder = new AdminConfigBuilder();
            $builder->title($isEdit ? '编辑帖子' : '新建帖子')
                ->keyId()->keyTitle()->keyEditor('content', '内容')->keyRadio('is_top', '置顶', '选择置顶形式', array(0 => '不置顶', 1 => '本版置顶', 2 => '全局置顶'))
                /*->keyText('type', '类型', '填写置顶或加精等')
                ->keyRadio('is_out_link', '外链', '是否是展示外链', array(0 => 0, 1 => 1))
                ->keyText('link_url', '外链链接', '填写跳转的外链链接')*/
                //->keyRadio('is_inner', '嘿设汇新闻', '是否是嘿设汇新闻内页', array(0 => 0, 1 => 1))
                //->keyRadio('community', '所属圈子', '选择所属圈子', array(1 => '学习圈', 2 => '老师圈', 3 => '朋友圈', 4 => '精华圈'))
                ->keyRadio('is_inner', '顶部分类', '选择显示在置顶的哪一栏', array(1 => '新闻', 4 => '小嘿专栏'))
                ->keyRadio('community', '所属圈子', '选择所属圈子', $community_array)
                ->keySingleImage('cover_id','内页帖子封面')
                ->keyCreateTime()->keyUpdateTime()
                ->keyTime('last_reply_time', '最后回复时间')
                ->buttonSubmit(U('editPost'))->buttonBack()
                ->data($post)
                ->display();
        }

    }

    /**
     * 添加置顶帖
     */
    public function addTopPost(){
        $community_list = M('ForumConfig')->field('id, name')->where('type=1 and status=1')->select();
        $community_array = array();
        foreach($community_list as $community){
            $community_array[$community['id']] = $community['name'];
        }
        //显示页面
        $builder = new AdminConfigBuilder();
        $builder->title('添加论坛置顶列表内容')
            ->keyId()->keyTitle()->keyEditor('content', '内容')
            /*->keyText('top_type', '类型', '填写置顶或加精等')
            ->keyRadio('is_out_link', '外链', '是否是展示外链', array(0 => 0, 1 => 1))
            ->keyText('link_url', '外链链接', '填写跳转的外链链接')*/
            ->keyRadio('is_inner', '顶部分类', '选择显示在置顶的哪一栏', array(1 => '新闻', /*2 => '第二栏', 3 => '第三栏',*/ 4 => '小嘿专栏'))
            //->keyRadio('community', '所属圈子', '选择所属圈子', array(1 => '学习圈', 2 => '老师圈', 3 => '朋友圈', 4 => '精华圈'))
            ->keyRadio('community', '所属圈子', '选择所属圈子', $community_array)
            ->keySingleImage('cover_id','内页帖子封面')
            ->buttonSubmit(U('saveTopPost'))->buttonBack()
            ->display();
    }

    /**
     * 置顶帖保存
     * @param string $title
     * @param string $content
     * @param string $top_type
     * @param int $is_top
     * @param int $is_out_link
     * @param null $link_url
     * @param int $is_inner
     * @param int $cover_id
     * @param int $community
     */
    public function saveTopPost($title = '', $content = '', $top_type = '置顶', $is_top = 1, $is_out_link=0,
                                $link_url=null, $is_inner=1, $cover_id=0, $community=1){
        //写入数据库
        $model = D('Forum/ForumPost');
        $random_count = rand(C('HisihiNewsInitMinViewCount'), C('HisihiNewsInitMaxViewCount'));
        $data = array('title' => $title, 'content' => $content, 'type' => $top_type, 'is_top' => $is_top,
            'is_out_link' => $is_out_link, 'link_url' => $link_url, 'is_inner' => $is_inner, 'cover_id' => $cover_id,
            'view_count'=>$random_count, 'community'=>$community);
        if($is_inner==0){
            $data['uid'] = 0;
        }
        $result = $model->createPost($data);
        $this->uploadLogoPicToOSS($cover_id);
        //如果写入不成功，则报错
        if (!$result) {
            $this->error('添加失败');
        }
        //返回成功信息
        $this->success('添加成功', U('post'));
    }

    public function setPostStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('ForumPost', $ids, $status);
        // 改变用户作品表中的数据状态
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        M('UserWorks')->where(array('post_id' => array('in', $ids)))->save(array('status' => $status));
    }

    //设置精华帖
    public function setPostElite($ids, $is_elite)
    {
        $builder = new AdminListBuilder();
        $builder->doSetElite('ForumPost', $ids, $is_elite);
    }

    public function unsetPostElite($ids){
        if(empty($ids)){
            $this->error('请选择要操作的数据');
        }
        if(is_array($ids)){
            $map['id'] = array('in',$ids);
            $result = M('ForumPost')->where($map)->save(array('is_elite'=>0));
            if($result){
                $this->success('取消精华成功', U('Forum/post?showelite=1'));
            }
        }else{
            $result = M('ForumPost')->where('id='.$ids)->save(array('is_elite'=>0));
            if($result){
                $this->success('取消精华成功', U('Forum/post?showelite=1'));
            }
        }
    }

    public function reply($page = 1, $post_id = null, $r = 20)
    {
        $builder = new AdminListBuilder();

        //读取回复列表
        $map = array('status' => array('EGT', 0));
        if ($post_id) $map['post_id'] = $post_id;
        $model = M('ForumPostReply');
        $list = $model->where($map)->order('create_time asc')->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        foreach ($list as &$reply) {
            $reply['content'] = op_t($reply['content']);
        }
        unset($reply);
        //显示页面

        $builder->title('回复管理')
            ->setStatusUrl(U('setReplyStatus'))->buttonEnable()->buttonDisable()->buttonDelete()
            ->keyId()->keyTruncText('content', '内容', 50)->keyCreateTime()->keyUpdateTime()->keyStatus()->keyDoActionEdit('editReply?id=###')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function replyTrash($page = 1, $r = 20, $model = '')
    {
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        //读取回复列表
        $map = array('status' => -1);
        $model = M('ForumPostReply');
        $list = $model->where($map)->order('create_time asc')->page($page, $r)->select();
        foreach ($list as &$reply) {
            $reply['content'] = op_t($reply['content']);
        }
        unset($reply);
        $totalCount = $model->where($map)->count();

        //显示页面

        $builder->title('回复回收站')
            ->setStatusUrl(U('setReplyStatus'))->buttonRestore()->buttonClear('ForumPostReply')
            ->keyId()->keyTruncText('content', '内容', 50)->keyCreateTime()->keyUpdateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function setReplyStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('ForumPostReply', $ids, $status);
    }

    public function editReply($id = null, $content = '', $create_time = 0, $update_time = 0, $status = 1)
    {
        if (IS_POST) {
            //判断是否为编辑模式
            $isEdit = $id ? true : false;

            //写入数据库
            $data = array('content' => $content, 'create_time' => $create_time, 'update_time' => $update_time, 'status' => $status);
            $model = M('ForumPostReply');
            if ($isEdit) {
                $result = $model->where(array('id' => $id))->save($data);
            } else {
                $result = $model->add($data);
            }

            //如果写入出错，则显示错误消息
            if (!$result) {
                $this->error($isEdit ? '编辑失败' : '创建失败');
            }

            //返回成功消息
            $this->success($isEdit ? '编辑成功' : '创建成功', U('reply'));

        } else {
            //判断是否为编辑模式
            $isEdit = $id ? true : false;

            //读取回复内容
            if ($isEdit) {
                $model = M('ForumPostReply');
                $reply = $model->where(array('id' => $id))->find();
            } else {
                $reply = array('status' => 1);
            }

            //显示页面
            $builder = new AdminConfigBuilder();
            $builder->title($isEdit ? '编辑回复' : '创建回复')
                ->keyId()->keyEditor('content', '内容')->keyCreateTime()->keyUpdateTime()->keyStatus()
                ->data($reply)
                ->buttonSubmit(U('editReply'))->buttonBack()
                ->display();
        }

    }

    /**
     * 自动回复内容列表
     */
    public function autoreply(){
        $builder = new AdminListBuilder();
        $model = M('Autoreply');
        $list = $model->order('create_time desc')->page(1, 10)->select();
        $totalCount = $model->count();

        foreach ($list as &$reply) {
            $reply['content'] = op_t($reply['content']);
        }
        unset($reply);
        //显示页面
        $builder->title('自动回复内容管理')
            ->setStatusUrl(U('setAutoReplyStatus'))->buttonNew(U('Forum/add_autoreply'))
            ->keyId()->keyTruncText('content', '回复内容', 70)
            ->keyText('forum_id', '论坛ID')->keyCreateTime()->keyStatus()->keyDoActionEdit('add_autoreply?id=###')
            ->data($list)
            ->pagination($totalCount, 10)
            ->display();
    }

    public function setAutoReplyStatus($ids, $status){
        $builder = new AdminListBuilder();
        $builder->doSetStatus('Autoreply', $ids, $status);
    }

    /**
     * 论坛帖子自动回复
     */
    public function add_autoreply($id = null, $content='', $forum_id=0, $status=1){
        if (IS_POST) {
            //判断是否为编辑模式
            $isEdit = $id ? true : false;
            //写入数据库
            $time = time();
            $data = array('content' => $content, 'forum_id'=>$forum_id, 'create_time'=>$time,'status' => $status);
            $model = M('Autoreply');
            if ($isEdit) {
                $result = $model->where(array('id' => $id))->save($data);
            } else {
                $result = $model->add($data);
            }
            //如果写入出错，则显示错误消息
            if (!$result) {
                $this->error($isEdit ? '编辑失败' : '创建失败');
            }
            //返回成功消息
            $this->success($isEdit ? '编辑成功' : '创建成功', U('autoreply'));
        } else {
            //判断是否为编辑模式
            $isEdit = $id ? true : false;
            //如果是编辑模式，读取贴吧的属性
            if ($isEdit) {
                $forum = M('Autoreply')->where(array('id' => $id))->find();
            } else {
                $forum = array('create_time' => time(), 'post_count' => 0, 'status' => 1);
            }
            $types = M('ForumType')->where(array('status' => 1))->select();
            foreach ($types as $t) {
                $type_id_array[$t['id']] = $t['title'];
            }
            //显示页面
            $builder = new AdminConfigBuilder();
            $builder
                ->title($isEdit?'编辑自动回复内容':'新增自动回复内容')
                ->keyId()->keySelect('forum_id', '分类板块', '选择板块所在分类', $type_id_array)
                ->keyText('content', '回复内容')
                ->data($forum)
                ->buttonSubmit(U('add_autoreply'))->buttonBack()
                ->display();
        }
    }

    /*
     * 推送论坛置顶帖
     */
    public function pushTopPost($ids){
        if(empty($ids)){
            $this->error('请选择要操作的数据');
        }
        if(count($ids)>1){
            $this->error('一次只能推送一条数据');
        }
        $model = M('ForumPost')->field('title')->where('id='.$ids[0])->find();
        if($model){
            $param['alert_info'] = $model['title'];
        } else {
            $param['alert_info'] = "嘿设汇-论坛精华帖推荐";
        }
        $param['type'] = 3;
        $param['id'] = $ids[0];
        $param['production'] = C('APNS_PRODUCTION');
        $result = Hook::exec('Addons\\JPush\\JPushAddon', 'push_video_article', $param);
        $result = true;
        if($result){
            $this->success("推送成功");
        } else {
            $this->error('推送异常，请检查后重试');
        }
    }

    /**
     * 设置广告位
     */
    public function advPositionSetting(){
        $_marks = array(
            array(
                'id'=>1,
                'value'=>'1号位'
            ),
            array(
                'id'=>2,
                'value'=>'2号位'
            ),
            array(
                'id'=>3,
                'value'=>'3号位'
            ),
            array(
                'id'=>4,
                'value'=>'4号位'
            ),
            array(
                'id'=>5,
                'value'=>'5号位'
            ),
            array(
                'id'=>6,
                'value'=>'6号位'
            ),
            array(
                'id'=>7,
                'value'=>'7号位'
            ),
            array(
                'id'=>8,
                'value'=>'8号位'
            ),
            array(
                'id'=>9,
                'value'=>'9号位'
            ),
            array(
                'id'=>10,
                'value'=>'10号位'
            )
        );
        $position = M('CompanyConfig')->where('type=12')->field('id,value')->find();
        $position_array = explode("#",$position['value']);
        $this->assign('position_array',$position_array);
        $this->assign('info',$position);
        $this->assign('_marks',$_marks);
        $this->display();
    }

    /**
     * 更新广告位
     */
    public function advPositionUpdate(){
        if (IS_POST) { //提交表单
            $model = M('CompanyConfig');
            $cid = $_POST["cid"];
            $data["value"] = $_POST["value"];
            if(empty($cid)){
                $this->error('配置id为空', 'index.php?s=/admin/forum/post');
            } else {
                $res = $model->where('id='.$cid)->save($data);
                $this->success('设置成功', 'index.php?s=/admin/forum/post');
            }
        }
    }

    /**
     * 论坛圈子管理
     */
    public function forumcircle(){
        $model = M('ForumConfig');
        $count = $model->where('type=1 and status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where("type=1 and status=1")->order('create_time desc')
            ->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title", "圈子管理");
        $this->display('forumcircle');
    }

    /**
     * 新增圈子
     */
    public function circleAdd(){
        $this->display('circle_add');
    }

    /**
     * 圈子数据更新
     */
    public function circle_update(){
        if (IS_POST) { //提交表单
            $model = M('ForumConfig');
            $cid = $_POST["cid"];
            $data['type'] = 1; // type 1 社区圈子
            $data["name"] = $_POST["name"];
            $data['status']=1;
            if(empty($cid)){
                $data["create_time"] = time();
                $model->data($data)->add();
                $this->success('添加成功', 'index.php?s=/admin/forum/forumcircle');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/forum/forumcircle');
            }
        } else {
            $this->display('circle_add');
        }
    }

    /**
     * 编辑圈子
     * @param $id
     */
    public function circle_edit($id){
        $model = M('ForumConfig');
        $data = $model->where('status=1 and id='.$id)->find();
        $this->assign('info',$data);
        $this->display();
    }

    /**
     * 删除圈子
     * @param $id
     */
    public function circle_delete($id){
        if(!empty($id)){
            $model = M('ForumConfig');
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
                $this->success("删除成功",'index.php?s=/admin/forum/forumcircle');
            }
        }else{
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 论坛置顶管理
     */
    public function forumtoppost(){
        $model = M('ForumTopPost');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where("status=1")->order('create_time desc')
            ->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($list as &$toppost) {
            $community = $toppost['community'];
            $type = $toppost['type'];
            $info = M('ForumConfig')->field('name')->where('type=1 and id='.$community)->find();
            $toppost['community'] = $info['name'];
            switch($type){
                case 1:
                    $toppost['type'] = '新闻列表';
                    break;
                case 2:
                    $toppost['type'] = '内部web帖子';
                    break;
                case 3:
                    $toppost['type'] = '原生帖子';
                    break;
                case 4:
                    $toppost['type'] = '外部url';
                    break;
            }
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title", "置顶管理");
        $this->display('forumtoppost');
    }

    /**
     * 新增置顶
     */
    public function toppostAdd(){
        $community_list = M('ForumConfig')->field('id, name')->where('type=1 and status=1')->select();
        $this->assign('_community', $community_list);
        $this->display('toppost_add');
    }

    /**
     * 置顶数据更新
     */
    public function toppost_update(){
        if (IS_POST) { //提交表单
            $model = M('ForumTopPost');
            $cid = $_POST["cid"];
            $data['tag'] = $_POST['tag'];
            $data["title"] = $_POST["title"];
            $data["community"] = $_POST["community"];
            $data["type"] = $_POST["type"];
            $data["post_id"] = $_POST["post_id"];
            //$data['content'] = $_POST["content"];
            $data["url"] = $_POST["url"];
            $data['status']=1;
            if(empty($cid)){
                $data["create_time"] = time();
                $model->data($data)->add();
                $this->success('添加成功', 'index.php?s=/admin/forum/forumtoppost');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/forum/forumtoppost');
            }
        } else {
            $this->display('toppost_add');
        }
    }

    /**
     * 编辑置顶
     * @param $id
     */
    public function toppost_edit($id){
        $community_list = M('ForumConfig')->field('id, name')->where('type=1 and status=1')->select();
        $info = M('ForumTopPost')->where('id='.$id)->find();
        $this->assign('_community', $community_list);
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 删除置顶
     * @param $id
     */
    public function toppost_delete($id){
        if(!empty($id)){
            $model = M('ForumTopPost');
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
                $this->success("删除成功",'index.php?s=/admin/forum/forumtoppost');
            }
        }else{
            $this->error('未选择要删除的数据');
        }
    }

    /**
     * 上传图片到OSS
     * @param $picID
     */
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
