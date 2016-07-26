<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-11
 * Time: PM5:41
 */

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;
use Think\Hook;


class IssueController extends AdminController
{
    protected $issueModel;

    function _initialize()
    {
        $this->issueModel = D('Issue/Issue');
        parent::_initialize();
    }

    public function config()
    {
        $admin_config = new AdminConfigBuilder();
        $data = $admin_config->handleConfig();

        $admin_config->title('专辑基本设置')
            ->keyBool('NEED_VERIFY', '投稿是否需要审核','默认无需审核')
            ->buttonSubmit('', '保存')->data($data);
        $admin_config->display();
    }
    public function issue()
    {


        //显示页面
        $builder = new AdminTreeListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';
        $attr1 = $attr;
        $attr1['url'] = $builder->addUrlParam(U('setWeiboTop'), array('top' => 1));
        $attr0 = $attr;
        $attr0['url'] = $builder->addUrlParam(U('setWeiboTop'), array('top' => 0));

        $tree = D('Issue/Issue')->getTree(0, 'id,title,sort,pid,status');


        $builder->title('专辑管理')
            ->buttonNew(U('Issue/add'))
            ->data($tree)

            ->display();
    }

    public function add($id = 0, $pid = 0)
    {
        if (IS_POST) {
            if ($id != 0) {
                $issue = $this->issueModel->create();
                if ($this->issueModel->save($issue)) {

                    $this->success('编辑成功。');
                } else {
                    $this->error('编辑失败。');
                }
            } else {
                $issue = $this->issueModel->create();
                if ($this->issueModel->add($issue)) {

                    $this->success('新增成功。');
                } else {
                    $this->error('新增失败。');
                }
            }


        } else {
            $builder = new AdminConfigBuilder();
            $issues = $this->issueModel->select();
            $opt = array();
            foreach ($issues as $issue) {
                $opt[$issue['id']] = $issue['title'];
            }
            if ($id != 0) {
                $issue = $this->issueModel->find($id);
            } else {
                $issue = array('pid' => $pid, 'status' => 1);
            }


            $builder->title('新增分类')->keyId()->keyText('title', '标题')->keySelect('pid', '父分类', '选择父级分类', array('0' => '顶级分类')+$opt)
                ->keyStatus()->keyCreateTime()->keyUpdateTime()
                ->data($issue)
                ->buttonSubmit(U('Issue/add'))->buttonBack()->display();
        }

    }

    public function issueTrash($page = 1, $r = 20,$model='')
    {
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        //读取微博列表
        $map = array('status' => -1);
        $model = $this->issueModel;
        $list = $model->where($map)->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        //显示页面

        $builder->title('专辑回收站')
            ->setStatusUrl(U('setStatus'))->buttonRestore()->buttonClear('Issue/Issue')
            ->keyId()->keyText('title', '标题')->keyStatus()->keyCreateTime()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function operate($type = 'move', $from = 0)
    {
        $builder = new AdminConfigBuilder();
        $from = D('Issue')->find($from);

        $opt = array();
        $issues = $this->issueModel->select();
        foreach ($issues as $issue) {
            $opt[$issue['id']] = $issue['title'];
        }
        if ($type === 'move') {

            $builder->title('移动分类')->keyId()->keySelect('pid', '父分类', '选择父分类', $opt)->buttonSubmit(U('Issue/add'))->buttonBack()->data($from)->display();
        } else {

            $builder->title('合并分类')->keyId()->keySelect('toid', '合并至的分类', '选择合并至的分类', $opt)->buttonSubmit(U('Issue/doMerge'))->buttonBack()->data($from)->display();
        }

    }

    public function doMerge($id, $toid)
    {
        $effect_count=D('IssueContent')->where(array('issue_id'=>$id))->setField('issue_id',$toid);
        D('Issue')->where(array('id'=>$id))->setField('status',-1);
        $this->success('合并分类成功。共影响了'.$effect_count.'个内容。',U('issue'));
        //TODO 实现合并功能 issue
    }

    public function editContents($id = 0){
        if (!check_auth('addIssueContent') && !check_auth('editIssueContent')) {
            $this->error('抱歉，您不具备投稿权限。');
        }

        $content_edit = new AdminConfigBuilder();

        $types = D('Issue/Issue')->getTree(0, 'id,title,sort,pid,status');
        $issue_types = array(0 => '课程分类');
        foreach($types as $key => $value){
            $issue_types[$value['id']] = $value['title'];
            foreach ($value['_'] as $key1 => $value1) {
                $issue_types[$value1['id']] = '---' . $value1['title'];
            }
        }

        $attr['class'] = 'btn';

        $content_edit->title('编辑课程')
            ->keyText('title', '标题')
            ->keyTime('create_time','创建时间')
            ->keyText('duration','时长（秒）')
            ->keyText('lecturer','讲师')
            ->keySelect('issue_id',"课程分类",'选择课程分类',$issue_types)
            ->keyTextArea('content','介绍')
            ->keyText('url','视频链接')
            ->keySingleImage('cover_id','封面');

        if($id){
            $content_edit->keyInteger('view_count','浏览次数')
                ->keyTime('update_time','最近更新')
                ->keyHidden('id','');
            $issue_content = D('IssueContent')->find($id);
            $issue_content['content'] = op_t($issue_content['content']);
            $content_edit->data($issue_content);
        }
        $content_edit->buttonSubmit(U('Issue/postContent'), '保存')
            ->buttonBack();
        $content_edit->display();
    }

    public function SyncCourse($page=1,$r=10,$type=0){
        $r = C('LIST_ROWS');
        //读取列表
        $Videos = M('videos','tbl_','mysqli://hisihi_root:027hsh_db@rdse1jrbrxqx7x18irl5m.mysql.rds.aliyuncs.com:3306/video');
        $map['status'] = array('egt', 0);
        if($type != 0){
            $map['status'] = array('eq', $type);
        }
        $videolist = $Videos->where($map)->page($page, $r)->select();
        $totalCount = $Videos->where($map)->count();

        foreach ($videolist as &$video) {
            $video['id'] = $video['vid'];
            unset($video['vid']);
        }

        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';
        $attr['href'] = U('doSync');

        $filter = '（全部）<同步完成后需要在课程管理中完善分组、讲师、封面等信息后才可发布>';
        if($type == 1){
            $filter = '（未同步）<同步完成后需要在课程管理中完善分组、讲师、封面等信息后才可发布>';
        } else if($type == 2){
            $filter = '（已同步）';
        }
        $builder->title('课程同步'.$filter)
            ->button('全部',array('href'=>U('SyncCourse')))->button('已同步',array('href'=>U('admin/issue/SyncCourse/type/2')))->button('未同步',array('href'=>U('admin/issue/SyncCourse/type/1')))->button('批量同步',$attr)
            ->keyId()->keyText('name', '标题')->keyText('channelName','频道')->keyText('localAddress','资源地址')->keyText('createTime','创建时间')->keyText('duration','时长（秒）')->keyMap('status','状态',array(1 => '未同步', 2 => '已同步', 0 => '已删除'))
            ->keyDoActionEdit( 'Issue/doSync?ids=###','同步')
            ->data($videolist)
            ->pagination($totalCount, $r)
            ->display();

    }

    public function doSync($ids)
    {
        $Videos = M('videos','tbl_','mysqli://hisihi_root:027hsh_db@rdse1jrbrxqx7x18irl5m.mysql.rds.aliyuncs.com:3306/video');

        if(is_array($ids)){
            $map['vid'] = array('in', $ids);
        }elseif (is_numeric($ids)){
            $map['vid'] = $ids;
        }
        $courseVideoList = $Videos->where($map)->select();
        $successCount = 0;
        $faildCount = 0;
        foreach ($courseVideoList as $coursevideo) {
            if($coursevideo['status'] == 2) {
                $faildCount++;
                continue;
            }
            preg_match('@/uploadVideo/(.*?)\.@',$coursevideo['localAddress'],$urlKey);

            $content = D('IssueContent')->create();
            $content['lecturer'] = '嘿设汇认证';
            $content['content'] = op_h($coursevideo['description']);
            $content['title'] = op_t($coursevideo['name']);
            $content['url'] = op_t($urlKey[1]);
            $content['duration'] = op_t($coursevideo['duration']); //新增链接框
            $content['img'] = op_t($coursevideo['image']);
            $content['issue_id'] = 0;
            $content['cover_id'] = -1;
            $content['create_time'] = time();
            $content['status'] = 0;

            $rs = D('IssueContent')->add($content);

            if($rs){
                /* ---------- 同步数据到新表中，支持新版本app对该视频可见 ----------- */
                // 同步课程数据
                $organizationCourseModel = M('OrganizationCourse');
                $organizationVideoModel = M('OrganizationVideo');
                $courseData['organization_id'] = 1575;
                $courseData['title'] = op_t($coursevideo['name']);
                $courseData['content'] = op_h($coursevideo['description']);
                $courseData['create_time'] = time();
                $courseData['update_time'] = time();
                $courseData['status'] = 1;
                $courseData['view_count'] = rand(C('VideoInitMinPlayCount'), C('VideoInitMaxPlayCount'));
                $courseData['fake_support_count'] = rand(C('VideoInitMinSupportCount'), C('VideoInitMaxSupportCount'));
                $courseData['fake_favorite_count'] = rand(C('VideoInitMinFavoriteCount'), C('VideoInitMaxFavoriteCount'));
                $courseData['is_old_hisihi_data'] = 1;
                $courseData['issue_content_id'] = $rs;
                $courseData['img_str'] = op_t($coursevideo['image']);
                $course_id = $organizationCourseModel->add($courseData);
                // 同步视频数据
                if($course_id){
                    $videoData['course_id'] = $course_id;
                    $videoData['name'] = $courseData['title'];
                    $videoData['url'] = op_t($urlKey[1]);
                    $videoData['duration'] = op_t($coursevideo['duration']);
                    $videoData['create_time'] = time();
                    $videoData['update_time'] = time();
                    $videoData['status'] = 1;
                    $organizationVideoModel->add($videoData);
                }
                /* ------------------------------------------------------------- */
            }

            if ($rs) {
                $coursevideo['status'] = 2;
                $Videos->save($coursevideo);
                $successCount++;
            } else {
                $faildCount++;
            }
        }
        if($faildCount > 0) {
            $this->error('共同步成功'.$successCount.'条。失败'.$faildCount.'条，失败原因（重复同步）',U('SyncCourse'));
        } else {
            $this->success('同步成功。',U('SyncCourse'));
        }
        
    }

    public function postContent($id = 0, $cover_id = 0, $title = '', $content = '', $lecturer = '', $issue_id = 0, $url = '', $duration = 0)
    {
        if (!check_auth('addIssueContent')) {
            $this->error('抱歉，您不具备投稿权限。');
        }
        $cover_id = intval($cover_id);
        $issue_id = intval($issue_id);

        if (!is_login()) {
            $this->error('请登陆后再投稿。');
        }
        if ($cover_id == 0) {
            $this->error('请上传封面。');
        }
        if (trim(op_t($title)) == '') {
            $this->error('请输入标题。');
        }
        if (trim(op_h($content)) == '') {
            $this->error('请输入内容。');
        }
        if ($issue_id == 0) {
            $this->error('请选择分类。');
        }
        if (trim(op_h($url)) == '') {
            $this->error('请输入地址。');
        }
        if (trim(op_h($duration)) == '') {
            $this->error('请输入时长。');
        }
        $content = D('IssueContent')->create();
        $content['lecturer'] = op_h($content['lecturer']);
        $content['content'] = op_h($content['content']);
        $content['title'] = op_t($content['title']);
        $content['url'] = op_t($content['url']); //新增链接框
        $content['duration'] = op_t($content['duration']); //新增链接框
        $content['issue_id'] = $issue_id;
        $content['create_time'] = time();
        $content['status'] = 1;

        if ($id) {
            $content_temp = D('IssueContent')->find($id);
            if (!check_auth('editIssueContent')) { //不是管理员则进行检测
                if ($content_temp['uid'] != is_login()) {
                    $this->error('不可操作他人的内容。');
                }
            }
            $content['uid'] = $content_temp['uid']; //权限矫正，防止被改为管理员
            $content['update_time'] = time();

            /* ----------------------- 更新同步后的课程数据 ------------------- */

            $organizationCourseModel = M('OrganizationCourse');
            $courseData['category_id'] = $issue_id;
            $courseData['status'] = 1;
            $organizationCourseModel->where('is_old_hisihi_data=1 and issue_content_id='.$id)->save($courseData);

            /* -------------------------------------------------------------- */

            $rs = D('IssueContent')->save($content);
            if ($rs) {
                $this->success('编辑成功。', U('contents'));
            } else {
                $this->success('编辑失败。', '');
            }
        } else {
            $rs = D('IssueContent')->add($content);
            if ($rs) {
                $this->success('发布成功。',U('contents'));
            } else {
                $this->success('发布失败。', '');
            }
        }


    }

    public function contents($page=1,$r=10){
        $r = C('LIST_ROWS');

        $title = I('title');
        if(!empty($title)){
            $map['title'] = array('like', '%' . $title . '%');
        }
        //读取列表
        $map['status'] = array('egt', 0);
        $model = M('IssueContent');
        $list = $model->where($map)->order('id desc')->page($page, $r)->select();
        unset($li);
        $totalCount = $model->where($map)->count();
        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';


        $builder->title('内容管理')
            ->setStatusUrl(U('setIssueContentStatus'))->buttonDisable('','审核不通过')->buttonSetStatus(U('deleteIssueContent'),-1,'删除',array())->buttonSetStatus(U('setIssueContentStatus'),2,'推荐',array())->buttonNew(U('editContents'))->button('课程同步',array('href'=>U('SyncCourse')))->buttonSetStatus(U('PushVideo'),2,'推送',array())
            ->keyId()->keyLink('title', '标题','editContents?id=###')->keyText('lecturer','讲师')->keyText('duration','时长（秒）')->keyCreateTime()->keyMap('status','状态',array(1 => '未推荐', 2 => '已推荐', 0 => '未激活'))
            ->keyDoActionEdit( 'Issue/editcontents?id=###','编辑')
            ->data($list)
            ->search('标题', 'title')
            ->pagination($totalCount, $r)
            ->display();
    }
    public function verify($page=1,$r=10){
        $r = C('LIST_ROWS');
        //读取列表
        $map = array('status' => 0);
        $model = M('IssueContent');
        $list = $model->where($map)->page($page, $r)->select();
        unset($li);
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';


        $builder->title('审核内容')
            ->setStatusUrl(U('setIssueContentStatus'))->buttonEnable('','审核通过')->buttonDelete()
            ->keyId()->keyLink('title', '标题','Issue/Index/issueContentDetail?id=###')->keyUid()->keyCreateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function setIssueContentStatus($ids,$status){
        $builder = new AdminListBuilder();
        if($status==1){
            foreach($ids as $id){
                $content=D('IssueContent')->find($id);
                D('Common/Message')->sendMessage($content['uid'],"管理员审核通过了您发布的内容。现在可以在列表看到该内容了。" , $title = '专辑内容审核通知', U('Issue/Index/issueContentDetail',array('id'=>$id)), is_login(), 2);
               /*同步微博*/
                $user = query_user(array('nickname', 'space_link'), $content['uid']);
                $weibo_content = '管理员审核通过了@' . $user['nickname'] . ' 的内容：【' . $content['title'] . '】，快去看看吧：' ."http://$_SERVER[HTTP_HOST]" .U('Issue/Index/issueContentDetail',array('id'=>$content['id']));
                $model = D('Weibo/Weibo');
                $model->addWeibo(is_login(), $weibo_content);
                /*同步微博end*/
            }

        }
        $builder->doSetStatus('IssueContent', $ids, $status);

    }

    public function deleteIssueContent($ids,$status){
        $builder = new AdminListBuilder();
        if($status==-1){
            if(is_array($ids)){
                $map['issue_content_id'] = array('in',$ids);
                $courses_ids = M('OrganizationCourse')->where($map)->field('id')->select();
                if($courses_ids){
                    $cid = array();
                    foreach($courses_ids as &$item){
                        $cid[] = $item['id'];
                    }
                    $result = M('OrganizationCourse')->where($map)->save(array('status'=>-1));
                    $map_['course_id'] = array('in',$cid);
                    $res = M('OrganizationVideo')->where($map_)->save(array('status'=>-1));
                }
            }else{
                $courses_id = M('OrganizationCourse')->where('issue_content_id='.$ids)->getField('id');
                if($courses_id){
                    $result = M('OrganizationCourse')->where('id='.$courses_id)->save(array('status'=>-1));
                    $res = M('OrganizationVideo')->where('course_id='.$courses_id)->save(array('status'=>-1));
                }
            }
        }
        $builder->doSetStatus('IssueContent', $ids, $status);
    }

    public function contentTrash($page=1, $r=10,$model=''){
        //读取微博列表
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        $map = array('status' => -1);
        $model = D('IssueContent');
        $list = $model->where($map)->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        //显示页面

        $builder->title('内容回收站')
            ->setStatusUrl(U('setIssueContentStatus'))->buttonRestore()->buttonClear('IssueContent')
            ->keyId()->keyLink('title', '标题','Issue/Index/issueContentDetail?id=###')->keyUid()->keyCreateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function PushVideo($ids){
        if(empty($ids)){
            $this->error('请选择要操作的数据');
        }
        if(count($ids)>1){
            $this->error('一次只能推送一条数据');
        }

        $issue_content_model = D('IssueContent');
        $issue_content_detail = $issue_content_model->where(array('id'=>$ids[0]))->find();
        $title = $issue_content_detail['title'];
        $param['alert_info'] = $title;
        $param['id'] = $ids[0];
        $param['type'] = 1;
        $param['production'] = C('APNS_PRODUCTION');
        $result = Hook::exec('Addons\\JPush\\JPushAddon', 'push_video_article', $param);
        if($result){
            $this->success("推送成功");
        } else {
            $this->error('推送异常，请检查后重试');
        }
    }
}
