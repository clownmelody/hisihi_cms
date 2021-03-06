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
use Think\Page;


class EventController extends AdminController
{
    protected $eventModel;

    function _initialize()
    {
        $this->eventModel = D('Event/Event');
        parent::_initialize();
    }
    public function config()
    {
        $admin_config = new AdminConfigBuilder();
        $data = $admin_config->handleConfig();

        $admin_config->title('活动基本设置')
            ->keyBool('NEED_VERIFY', '创建活动是否需要审核','默认无需审核')
            ->buttonSubmit('', '保存')->data($data);
        $admin_config->display();
    }

    public function event($page = 1, $r = 10, $title=''){
        $r = C('LIST_ROWS');
        if(!empty($title)){
            $map['title'] = array('like', '%' . $title . '%');
        }
        //读取列表
        $map['status'] = 1;
        $map['type_id'] = 2;
        $model = $this->eventModel;
        $list = $model->where($map)->page($page, $r)->order('create_time desc')->select();
        unset($li);
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder = new AdminListBuilder();

        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';

        $builder->title('设计大赛管理')
            ->setStatusUrl(U('setEventContentStatus'))->buttonDisable('', '审核不通过')->buttonDelete()->button('设为推荐', array_merge($attr, array('url' => U('doRecommend', array('tip' => 1)))))
            ->button('取消推荐', array_merge($attr, array('url' => U('doRecommend', array('tip' => 0)))))
            ->buttonNew(U('Event/add'))
            ->keyId()->keyLink('title', '标题', 'Event/add?id=###')->keyUid()->keyCreateTime()->keyStatus()->keyMap('is_recommend', '是否推荐', array(0 => '否', 1 => '是'))
            ->keyDoActionEdit( 'Event/add?id=###','编辑')
            ->keyDoActionEdit( 'Event/works_list?id=###','作品管理')
            ->data($list)
            ->search('标题', 'title')
            ->pagination($totalCount, $r)
            ->display();
    }

    public function offlineevent($page = 1, $r = 10, $title=''){
        $r = C('LIST_ROWS');
        if(!empty($title)){
            $map['title'] = array('like', '%' . $title . '%');
        }
        //读取列表
        $map['status'] = 1;
        $map['type_id'] = 5;
        $model = $this->eventModel;
        $list = $model->where($map)->page($page, $r)->order('create_time desc')->select();
        unset($li);
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder = new AdminListBuilder();

        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';

        $builder->title('线下活动管理')
            ->setStatusUrl(U('setEventContentStatus'))->buttonDisable('', '审核不通过')->buttonDelete()->button('设为推荐', array_merge($attr, array('url' => U('doRecommend', array('tip' => 1)))))
            ->button('取消推荐', array_merge($attr, array('url' => U('doRecommend', array('tip' => 0)))))
            ->buttonNew(U('Event/activity_add'))
            ->keyId()->keyLink('title', '标题', 'Event/add?id=###')->keyUid()->keyCreateTime()->keyStatus()->keyMap('is_recommend', '是否推荐', array(0 => '否', 1 => '是'))
            ->keyDoActionEdit( 'Event/activity_add?id=###','编辑')
            ->data($list)
            ->search('标题', 'title')
            ->pagination($totalCount, $r)
            ->display();
    }

    public function add($id=0)
    {
        $event_add = new AdminConfigBuilder();

        $types = D('Event/EventType')->getTree(0, 'id,title,sort,pid,status');
        $event_types = array();
        foreach($types as $key => $value){
            if($value['id']==2){
                $event_types[$value['id']] = $value['title'];
            }
        }

        $event_add->title('发布活动')
            ->keyText('title', '标题')->keySelect('type_id',"选择类型",'',$event_types)
            /*->keyTime('deadline', '报名结束时间')*/
            ->keyTime('sTime', '课程开始时间')
            ->keyTime('eTime', '课程结束时间')
            ->keyText('address', '地点','线下培训地点')
            ->keyText('organizer', '主办方')
            ->keyInteger('limitCount','人数','课程人数上线')
            ->keyTextArea('explain','介绍')
            ->keyEditor('detail_content', '详细内容')
            ->keySingleImage('cover_id','封面')
            ->buttonSubmit(U('Event/addEvent'), '保存')
            ->buttonBack();

        if($id){
            $event_add->keyHidden('id','编号');
            $event_content = D('Event')->where(array('status' => 1, 'id' => $id))->find();
            $event_add->data($event_content);
        }
        $this->assign('meta_title', '活动');
        $event_add->display();
    }

    public function activity_add($id=0)
    {
        $event_add = new AdminConfigBuilder();

        $types = D('Event/EventType')->getTree(0, 'id,title,sort,pid,status');
        $event_types = array();
        foreach($types as $key => $value){
            if($value['id']==5){
                $event_types[$value['id']] = $value['title'];
            }
        }

        $event_add->title('发布活动')
            ->keyText('title', '标题')->keySelect('type_id',"选择类型",'',$event_types)
            /*->keyTime('deadline', '报名结束时间')*/
            ->keyTime('sTime', '课程开始时间')
            ->keyTime('eTime', '课程结束时间')
            ->keyText('address', '地点','线下培训地点')
            ->keyText('organizer', '主办方')
            ->keyInteger('limitCount','人数','课程人数上线')
            ->keyTextArea('explain','介绍')
            ->keyEditor('detail_content', '详细内容')
            ->keySingleImage('cover_id','封面')
            ->buttonSubmit(U('Event/addEvent'), '保存')
            ->buttonBack();

        if($id){
            $event_add->keyHidden('id','编号');
            $event_content = D('Event')->where(array('status' => 1, 'id' => $id))->find();
            $event_add->data($event_content);
        }
        $this->assign('meta_title', '活动');
        $event_add->display();
    }

    public function addEvent($type_id, $cover_id, $id=0, $title='', $sTime='', $eTime='',
                             $deadline='',$explain='',$address='',$organizer='', $limitCount=1)
    {
        if (!$cover_id) {
            $this->error('请上传封面。');
        }
        if (trim(op_t($title)) == '') {
            $this->error('请输入标题。');
        }
        if ($type_id == 0) {
            $this->error('请选择分类。');
        }
        if (trim(op_h($explain)) == '') {
            $this->error('请输入内容。');
        }
        if (trim(op_h($address)) == '') {
            $this->error('请输入地点。');
        }
        /*if ($sTime < $deadline) {
            $this->error('报名截止不能大于活动开始时间');
        }
        if ($deadline == '') {
            $this->error('请输入截止日期');
        }*/
        if ($sTime > $eTime) {
            $this->error('活动开始时间不能大于活动结束时间');
        }

        $content = D('Event')->create();
        $content['explain'] = op_h($content['explain']);
        $content['title'] = op_t($content['title']);
        $content['type_id'] = intval($type_id);
        $content['sTime'] = $sTime;
        $content['eTime'] = $eTime;
        $content['organizer'] = $organizer;
        $content['address'] = $address;
        $content['status'] = 1;
        if ($id) {
            $rs = D('Event')->save($content);
            if ($rs) {
                $this->uploadEventPicToOSS($content['cover_id']);
                if($content['type_id']==2){
                    $this->success('编辑成功', 'index.php?s=/admin/event/event');
                } else {
                    $this->success('编辑成功', 'index.php?s=/admin/event/offlineevent');
                }
            } else {
                $this->success('编辑失败', '');
            }
        }else{
            $content['create_time'] = time();
            $content['view_count'] = rand(C('CompetitionInitMinViewCount'), C('CompetitionInitMaxViewCount'));
            $rs = D('Event')->add($content);
            if ($rs) {
                if($content['type_id']==2){
                    $this->success('发布成功', 'index.php?s=/admin/event/event');
                } else {
                    $this->success('发布成功', 'index.php?s=/admin/event/offlineevent');
                }
            } else {
                $this->success('发布失败', '');
            }
        }
    }

    private function uploadEventPicToOSS($picID){
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
     * 设置推荐or取消推荐
     * @param $ids
     * @param $tip
     * autor:xjw129xjt
     */
    public function doRecommend($ids, $tip)
    {
        D('Event')->where(array('id' => array('in', $ids)))->setField('is_recommend', $tip);
        $this->success('设置成功', $_SERVER['HTTP_REFERER']);
    }

    /**
     * 审核页面
     * @param int $page
     * @param int $r
     * autor:xjw129xjt
     */
    public function verify($page = 1, $r = 10)
    {
        //读取列表
        $map = array('status' => 0);
        $model = $this->eventModel;
        $list = $model->where($map)->page($page, $r)->select();
        unset($li);
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder = new AdminListBuilder();
        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';
        $builder->title('审核内容')
            ->setStatusUrl(U('setEventContentStatus'))->buttonEnable('', '审核通过')->buttonDelete()
            ->keyId()->keyLink('title', '标题', 'Event/Index/detail?id=###')->keyUid()->keyCreateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    /**
     * 设置状态
     * @param $ids
     * @param $status
     * autor:xjw129xjt
     */
    public function setEventContentStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        if ($status == 1) {
            foreach ($ids as $id) {
                $content = D('Event')->find($id);
                D('Common/Message')->sendMessage($content['uid'], "管理员审核通过了您发布的内容。现在可以在列表看到该内容了。", $title = '专辑内容审核通知', U('Event/Index/detail', array('id' => $id)), is_login(), 2);
                /*同步微博*/
                $user = query_user(array('username', 'space_link'), $content['uid']);
                $weibo_content = '管理员审核通过了@' . $user['username'] . ' 的内容：【' . $content['title'] . '】，快去看看吧：' . "http://$_SERVER[HTTP_HOST]" . U('Event/Index/detail', array('id' => $content['id']));
                $model = D('Weibo/Weibo');
                $model->addWeibo(is_login(), $weibo_content);
                /*同步微博end*/
            }

        }
        $builder->doSetStatus('Event', $ids, $status);

    }

    public function contentTrash($page = 1, $r = 10)
    {
        //读取微博列表
        $map = array('status' => -1);
        $model = D('Event');
        $list = $model->where($map)->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder = new AdminListBuilder();
        $builder->title('内容回收站')
            ->setStatusUrl(U('setEventContentStatus'))->buttonRestore()
            ->keyId()->keyLink('title', '标题', 'Event/Index/detail?id=###')->keyUid()->keyCreateTime()->keyStatus()
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function enroll(){
        $model = M('EventAttend');
        $eventModel = M('Event');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, C('LIST_ROWS'));
        $show = $Page->show();
        $list = $model->field('id, event_id, username, mobile, create_time')->where('status=1')
            ->order('create_time desc')
            ->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as &$item){
            $info = $eventModel->field('title')->where('id='.$item['event_id'])->find();
            $item['name'] = $info['title'];
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title", "预约报名列表");
        $this->display();
    }

    public function enroll_delete($id){
        if(!empty($id)){
            $model = M('EventAttend');
            $data['status'] = -1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/event/enroll');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function works_list($page = 1, $r = 10){
        $r = C('LIST_ROWS');
        //读取列表
        $map['status'] = 1;
        $map['competition_id'] = I('id');
        $model = M('EventWorks');
        $list = $model->where($map)->page($page, $r)->order('create_time desc')->select();
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder = new AdminListBuilder();

        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';

        $builder->title('比赛作品管理')->setStatusUrl(U('setWorksStatus'))
            ->buttonNew(U('Event/works_add?competition_id='.I('id')))
            ->buttonDelete()
            ->keyId()->keyText('name', '名称')->keyText('author', '作者')->keyCreateTime()->keyStatus()
            ->keyDoActionEdit( 'Event/works_add?id=###','编辑')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function setWorksStatus($ids, $status)
    {
        $builder = new AdminListBuilder();
        $builder->doSetStatus('EventWorks', $ids, $status);
    }

    public function works_add($id=0)
    {
        $competition_id = I('competition_id');
        $event_add = new AdminConfigBuilder();

        $event_add->title('上传作品')
            ->keyText('name', '作品名称')
            ->keyText('author', '作者')
            ->keyEditor('content', '详细内容')
            ->keySingleImage('cover_id','封面')
            ->keyHidden('competition_id')
            ->buttonSubmit(U('Event/addWorks'), '保存')
            ->buttonBack();
        $event_add->data(array('competition_id'=>$competition_id));
        if($id){
            $event_add->keyHidden('id');
            $event_content = D('EventWorks')->where(array('status' => 1, 'id' => $id))->find();
            $event_add->data($event_content);
        }
        $this->assign('meta_title', '比赛作品');
        $event_add->display();
    }

    public function addWorks($competition_id, $cover_id, $id=0, $name='', $author='')
    {
        if (!$cover_id) {
            $this->error('请上传封面。');
        }
        if (trim(op_t($name)) == '') {
            $this->error('请输入作品名称');
        }
        if (trim(op_h($author)) == '') {
            $this->error('请输入作者');
        }

        $content = D('EventWorks')->create();
        $content['author'] = op_h($content['author']);
        $content['name'] = op_t($content['name']);
        $content['status'] = 1;
        if ($id) {
            $content_temp = D('EventWorks')->find($id);
            if (!is_administrator(is_login())) { //不是管理员则进行检测
                if ($content_temp['uid'] != is_login()) {
                    $this->error('小样儿，可别学坏。别以为改一下页面元素就能越权操作。');
                }
            }
            $rs = D('EventWorks')->save($content);

            if ($rs ===false) {
                $this->error('编辑失败。', '');
            }else{
                $this->uploadEventPicToOSS($content['cover_id']);
                $this->success('编辑成功。', U('works_list?id='.$content['competition_id']));
            }
        }else{
            $content['create_time'] = time();
            $rs = D('EventWorks')->add($content);
            if ($rs) {
                $this->success('发布成功。' , U('works_list?id='.$content['competition_id']));
            } else {
                $this->error('发布失败。', '');
            }
        }
    }
}
