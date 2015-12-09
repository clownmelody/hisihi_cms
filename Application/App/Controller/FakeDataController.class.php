<?php

namespace App\Controller;

use Think\Controller;
use Think\Exception;
use Think\Model;

/* ----------- Fuck The FakeData ----------- */

/* ----------------------------------------- */

class FakedataController extends AppController {

    public function _initialize() {
        C('SHOW_PAGE_TRACE', false);
    }

    /**
     * 视频播放数量每天自动增长
     * @return int
     */
    public function autoIncreaseVideoPlayCountByDay(){
        $courseModel = M('OrganizationCourse');
        $course_list = $courseModel->field('id, view_count')->where('status=1')->select();
        $count = count($course_list);
        foreach($course_list as $course) {
            $id = $course['id'];
            $view_count = $course['view_count'];
            if($view_count>100000){
                $random_count = rand(C('VideoDaySlowIncreaseMinPlayCount'), C('VideoDaySlowIncreaseMaxPlayCount'));
                $random_id = rand(1, $count);
                $courseModel->where('id='.$random_id)->setInc('view_count', $random_count);
            } else {
                $random_count = rand(C('VideoDayIncreaseMinPlayCount'), C('VideoDayIncreaseMaxPlayCount'));
                $courseModel->where('id='.$id)->setInc('view_count', $random_count);
            }
        }
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseVideoPlayCountByDay success -- ' . $date);
    }

    /**
     * 视频收藏数量每天自动增长
     * @return int
     */
    public function autoIncreaseVideoFavoriteCountByDay(){
        $courseModel = M('OrganizationCourse');
        $course_list = $courseModel->field('id')->where('status=1')->select();
        foreach($course_list as $course) {
            $id = $course['id'];
            $random_count = rand(C('VideoDayIncreaseMinFavoriteCount'), C('VideoDayIncreaseMaxFavoriteCount'));
            $courseModel->where('id='.$id)->setInc('fake_favorite_count', $random_count);
        }
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseVideoFavoriteCountByDay success -- ' . $date);
    }

    /**
     * 视频点赞数量每天自动增长
     * @return int
     */
    public function autoIncreaseVideoSupportCountByDay(){
        $courseModel = M('OrganizationCourse');
        $course_list = $courseModel->field('id')->where('status=1')->select();
        foreach($course_list as $course) {
            $id = $course['id'];
            $random_count = rand(C('VideoDayIncreaseMinSupportCount'), C('VideoDayIncreaseMaxSupportCount'));
            $courseModel->where('id='.$id)->setInc('fake_support_count', $random_count);
        }
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseVideoSupportCountByDay success -- ' . $date);
    }

    /**
     * 头条浏览数量每天自动增长
     * @return int
     */
    public function autoIncreaseArticleViewCountByDay(){
        $documentModel = M('Document');
        $article_list = $documentModel->field('id, view')->where('status=1')->select();
        $count = count($article_list);
        foreach($article_list as $article) {
            $id = $article['id'];
            $view_count = $article['view'];
            if($view_count>100000){
                $min = (int)C('VideoDaySlowIncreaseMinPlayCount') * 0.6;
                $max = (int)C('VideoDaySlowIncreaseMaxPlayCount') * 0.6;
                $random_count = rand($min, $max);
                $random_id = rand(1, $count);
                $documentModel->where('id='.$random_id)->setInc('view', $random_count);
            } else {
                $min = (int)C('VideoDayIncreaseMinPlayCount') * 0.6;
                $max = (int)C('VideoDayIncreaseMaxPlayCount') * 0.6;
                $random_count = rand($min, $max);
                $documentModel->where('id='.$id)->setInc('view', $random_count);
            }
        }
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseArticleViewCountByDay success -- ' . $date);
    }

    /**
     * 头条点赞数量每天自动增长
     * @return int
     */
    public function autoIncreaseArticleSupportCountByDay(){
        $documentModel = M('DocumentArticle');
        $article_list = $documentModel->field('id')->select();
        foreach($article_list as $article) {
            $id = $article['id'];
            $min = (int)C('VideoDayIncreaseMinSupportCount') * 0.6;
            $max = (int)C('VideoDayIncreaseMaxSupportCount') * 0.6;
            $random_count = rand($min, $max);
            $documentModel->where('id='.$id)->setInc('fake_support_count', $random_count);
        }
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseArticleSupportCountByDay success -- ' . $date);
    }

    /**
     * 嘿设汇新闻访问数量每天自动增长
     */
    public function autoIncreaseHisihiNewsViewCountByDay(){
        $hisihi_news_model = M('ForumPost');
        $list = $hisihi_news_model->field('id, view_count')->where('forum_id=0 and is_top=1 and status=1 and is_inner=1')->select();
        $count = count($list);
        foreach($list as $course) {
            $id = $course['id'];
            $view_count = $course['view_count'];
            if($view_count>13000){
                $random_count = rand(C('HisihiNewsDaySlowIncreaseMinViewCount'), C('HisihiNewsDaySlowIncreaseMaxViewCount'));
                $random_id = rand(1, $count);
                $hisihi_news_model->where('id='.$random_id)->setInc('view_count', $random_count);
            } else {
                $random_count = rand(C('HisihiNewsDayIncreaseMinViewCount'), C('HisihiNewsDayIncreaseMaxViewCount'));
                $hisihi_news_model->where('id='.$id)->setInc('view_count', $random_count);
            }
        }
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseHisihiNewsViewCountByDay success -- ' . $date);
    }

    /**
     * 灵感访问数量和收藏量每天自动增长
     */
    public function autoIncreaseInspirationViewCountAndFavoriteCountByDay(){
        $hisihi_news_model = M('Inspiration');
        $list = $hisihi_news_model->field('id, view_count')->where('status=1')->select();
        $count = count($list);
        foreach($list as $course) {
            $id = $course['id'];
            $view_count = $course['view_count'];
            if($view_count>13000){
                $random_view_count = rand(C('InspirationDayIncreaseMinViewCount'), C('InspirationDayIncreaseMaxViewCount'));
                $random_id = rand(1, $count);
                $hisihi_news_model->where('id='.$random_id)->setInc('view_count', $random_view_count);
                $random_favorite_count = rand(C('InspirationDayIncreaseMinFavoriteCount'), C('InspirationDayIncreaseMaxFavoriteCount'));
                $hisihi_news_model->where('id='.$random_id)->setInc('favorite_count', $random_favorite_count);
            } else {
                $random_view_count = rand(C('InspirationDayIncreaseMinViewCount'), C('InspirationDayIncreaseMaxViewCount'));
                $hisihi_news_model->where('id='.$id)->setInc('view_count', $random_view_count);
                $random_favorite_count = rand(C('InspirationDayIncreaseMinFavoriteCount'), C('InspirationDayIncreaseMaxFavoriteCount'));
                $hisihi_news_model->where('id='.$random_id)->setInc('favorite_count', $random_favorite_count);
            }
        }
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseInspirationViewCountAndFavoriteCountByDay success -- ' . $date);
    }

    /**
     * 比赛访问数量每天自动增长
     */
    public function autoIncreaseCompetitionViewCountByDay(){
        $eventModel = M('Event');
        $count = $eventModel->where('status=1 and type_id=2')->count();
        $list = $eventModel->field('id')->where('status=1 and type_id=2')->order('create_time desc')->page(1, 10)->select();
        foreach($list as $event){
            $id = $event['id'];
            $random_view_count = rand(C('CompetitionDayIncreaseMinViewCount'), C('CompetitionDayIncreaseMaxViewCount'));
            $eventModel->where('id='.$id)->setInc('view_count', $random_view_count);
        }
        $list = $eventModel->field('id')->where('status=1 and type_id=2')->order('create_time desc')->limit(10, $count-10)->select();
        foreach($list as $event){
            $id = $event['id'];
            $random_view_count = rand(C('CompetitionDaySlowIncreaseMinViewCount'), C('CompetitionDaySlowIncreaseMaxViewCount'));
            $eventModel->where('id='.$id)->setInc('view_count', $random_view_count);
        }
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseCompetitionViewCountByDay success -- ' . $date);
    }

    /**
     * 快捷键访问数量每天自动增长
     */
    public function autoIncreaseHotKeysViewCountByDay(){
        $hotkeysModel = M('CompanyConfig');
        $random_view_count = rand(C('HotkeysDayIncreaseMinViewCount'), C('HotkeysDayIncreaseMaxViewCount'));
        $hotkeysModel->where('type=10 and status=1')->setInc('value', $random_view_count);
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseHotKeysViewCountByDay success -- ' . $date);
    }

    /**
     * 分类云作业下载数量每天自动增长
     */
    public function autoIncreaseCateHiworksViewCountByDay(){
        $docModel = M('Document');
        $doc_list = $docModel->where('status=1')->field('id')->select();
        foreach($doc_list as $doc){
            $id = $doc['id'];
            $random_view_count = rand(20, 40);
            $docModel->where('id='.$id)->setInc('view', $random_view_count);
        }
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseCateHiworksViewCountByDay success -- ' . $date);
    }

    /**
     * 公司人气每天自动增长
     */
    public function autoIncreaseCompanyViewCountByDay(){
        $companyConfigModel = M('CompanyConfig');
        $random_view_count = rand(C('CompanyDayIncreaseMinViewCount'), C('CompanyDayIncreaseMaxViewCount'));
        $companyConfigModel->where('type=9 and status=1')->setInc('value', $random_view_count);
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoIncreaseCompanyViewCountByDay success -- ' . $date);
    }

    /**
     * 解决问题数每天自动增长
     */
    public function autoQuestionResolvedCountByDay(){
        $random_view_count = rand(C('QuestionsResolvedDayIncreaseMinViewCount'), C('QuestionsResolvedDayIncreaseMaxViewCount'));
        M('CompanyConfig')->where('status=1 and type=11')->setInc('value', $random_view_count);
        $time = time();
        $date = date("y-m-d",$time);
        $this->apiSuccess('autoQuestionResolvedCountByDay success -- ' . $date);
    }

    /**
     * 修改嘿设汇置顶帖中的数据内容
     */
    public function autoModifyHisihiTopPostDataContent()
    {
        $postModel = M('ForumPost');
        $configCount = M('CompanyConfig')->field('value')->where('status=1 and type=11')->find();
        if($configCount){
            $questions_resolved = $configCount['value'] + A('Forum')->getAutoIncreseCount();
        }
        $allStudentsCount = A('Forum')->getAllColleageStudentsCount();
        $allHiworksCount = A('Forum')->getHiworksTotalCount();
        $where = 'auth_group_access.uid = member.uid and auth_group_access.group_id = ';
        $statInfo['designers'] = M("table")->table(array(
            'hisihi_auth_group_access'=>'auth_group_access',
            'hisihi_member'=>'member',))->where($where.'6')->field('member.uid')->count();
        $allTeachersCount = $statInfo['designers'] + C('TEACHER_COUNT_BASE') + A('User')->getAutoIncreseCount();
        $data['content'] = '<p>嘿设汇致力于为广大设计学习者、爱好者和从业者提供交流互动平台，自2015年8月正式运营以来，当前：
已经解决问题：'.$questions_resolved.'个'.'
已入驻设计师：'.$allTeachersCount.'人'.'
设计大学生：'.$allStudentsCount.'人'.'
设计作业源文件：'.$allHiworksCount.'份'.'
嘿设汇一直在努力！</p>
 <p><img src="http://forum-pic.oss-cn-qingdao.aliyuncs.com/2015-12-03/jerqwersfd.jpg" _src="http://forum-pic.oss-cn-qingdao.aliyuncs.com/2015-12-03/jerqwersfd.jpg" style=""/></p>';
        $data['update_time'] = time();
        $res = $postModel->where('id=5346')->save($data);
        //$res = $postModel->where('id=67282')->save($data);
        $time = time();
        $date = date("y-m-d",$time);
        if($res){
            $this->apiSuccess('修改置顶帖中数据内容 success -- ' . $date);
        } else {
            $this->apiSuccess('修改置顶帖中数据内容 Failture -- ' . $date);
        }
    }

}