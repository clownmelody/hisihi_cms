<?php

namespace App\Controller;

use Think\Controller;
use Think\Exception;
use Think\Model;

/* ----------- Fuck The FakeData ----------- */

/* ----------------------------------------- */

class FakeDataController extends AppController {

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
    }

    /**
     * 视频收藏数量每天自动增长
     * @return int
     */
    public function autoIncreaseVideoFavoriteCountByDay(){

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
    }

    /**
     * 快捷键访问数量每天自动增长
     */
    public function autoIncreaseHotKeysViewCountByDay(){
        $hotkeysModel = M('CompanyConfig');
        $random_view_count = rand(C('HotkeysDayIncreaseMinViewCount'), C('HotkeysDayIncreaseMaxViewCount'));
        $hotkeysModel->where('type=10 and status=1')->setInc('value', $random_view_count);
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
    }

    /**
     * 公司人气每天自动增长
     */
    public function autoIncreaseCompanyViewCountByDay(){
        $companyConfigModel = M('CompanyConfig');
        $random_view_count = rand(C('CompanyDayIncreaseMinViewCount'), C('CompanyDayIncreaseMaxViewCount'));
        $companyConfigModel->where('type=9 and status=1')->setInc('value', $random_view_count);
    }




}