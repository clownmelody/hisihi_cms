<?php
/**
 *  author: 杨楚杰
 *  function：嘿设汇推送服务
 *  date: 2015-07-10
 */
namespace Addons\JPush\Controller;
use Home\Controller\AddonsController;

use JPush\JPushClient;
use JPush\JPushLog;
use JPush\Model as M;
use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;
use Think\Log;


class JPushController extends AddonsController{
    protected $push;
    public function _initialize() {
        vendor('Psr.Log.LoggerInterface');
        vendor('Httpful.Httpful');
        vendor('Httpful.Request');
        vendor('Httpful.Http');
        vendor('Httpful.Bootstrap');
        vendor('Monolog.Formatter.FormatterInterface');
        vendor('Monolog.Formatter.NormalizerFormatter');
        vendor('Monolog.Formatter.LineFormatter');
        vendor('Monolog.Registry');
        vendor('Monolog.ErrorHandler');
        vendor('Monolog.Logger');
        vendor('Monolog.Handler.HandlerInterface');
        vendor('Monolog.Handler.AbstractHandler');
        vendor('Monolog.Handler.AbstractProcessingHandler');
        vendor('Monolog.Handler.StreamHandler');
        vendor('JPush.Model.Audience');
        vendor('JPush.Model.PushPayload');
        vendor('JPush.Model.Options');
        vendor('JPush.Model.Platform');
        vendor('JPush.Model.Notification');
        vendor('JPush.Model.Message');
        vendor('JPush.JPushLog');
        vendor('JPush.JPushClient');
        $config=  get_addon_config('JPush');
        $app_key = $config['app_key'];
        $master_secret = $config['master_secret'];
        #JPushLog::setLogHandlers(array(new StreamHandler('jpush.log', Logger::DEBUG)));
        $this->push = new JPushClient($app_key, $master_secret);
    }

    /**课程视频 或 文章 推送
     * @param $alert_info  // 提示文字
     * @param $id          // 视频或者文章ID
     * @param $type        // 1表示视频，2表示文章
     * @param $production  // 是否产品模式，默认为false（调试模式）
     * @return mixed       // 推送结果
     */
    public function push_video_article($alert_info, $id, $type, $production){
        $result = false;
        if(!isset($alert_info)||!isset($id)||!isset($type)){
            Log::write("传入参数未设置或为空", "WARN");
            return false;
        }
        if($type==2){
            $content_type = "article";
        } else if($type==3) {
            $content_type = "forum_post";
        } else  {
            $content_type = "course_video";
        }
        $product = false;
        if($production==true){
            $product = true;
        }
        try{
            $result = $this->push->push()
                ->setPlatform(M\platform('ios', 'android'))
                ->setAudience(M\all)
                ->setNotification(M\notification("文章|视频|论坛推送", M\android($alert_info, '嘿设汇',
                    null, array('type'=>$content_type, 'id'=>$id, 'infos'=>'')),
                    M\ios($alert_info, "default", "+0", false, array('type'=>$content_type, 'id'=>$id,
                        'infos'=>''), null)))
                ->setOptions(M\options(1234, 0, null, $product, 0))
                ->send();
        } catch (APIRequestException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        } catch (APIConnectionException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        }
        return $result;
    }

    /**用户被关注 推送
     * @param $alert_info  // 提示文字
     * @param $fans_id     // 粉丝ID
     * @param $user_id     // 用户ID（被关注者ID）
     * @param $production  // 是否产品模式，默认为false（调试模式）
     * @return mixed       // 推送结果
     */
    public function push_followed($alert_info, $fans_id, $user_id, $reg_id, $production){
        if(!isset($alert_info)||!isset($fans_id)||!isset($user_id)||empty($reg_id)){
            Log::write("传入参数未设置或为空", "WARN");
            return false;
        }
        $product = false;
        if($production==true){
            $product = true;
        }
        try{
            $arr = array ('to_uid'=>$user_id);
            $result = $this->push->push()
                ->setPlatform(M\platform('ios', 'android'))
                ->setAudience(M\audience(M\registration_id(array($reg_id))))  //  需要根据reg_id单独推送
                ->setNotification(M\notification("被关注推送", M\android($alert_info, '嘿设汇',
                    null, array('type'=>"follow_you", 'id'=>$fans_id, 'infos'=>$arr)),
                    M\ios($alert_info, "default", "+1", false, array('type'=>"follow_you", 'id'=>$fans_id,
                        'infos'=>$arr), null)))
                ->setMessage(M\message($alert_info, null, null, array('type'=>"follow_you", 'id'=>$fans_id,
                    'infos'=>$arr)))
                ->setOptions(M\options(1234, 0, null, $product, 0))
                ->send();
        } catch (APIRequestException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        } catch (APIConnectionException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        }
        return $result;
    }

    /**提问被点赞 推送
     * @param $alert_info     // 提示文字，信息需要调用者组装后传入
     * @param $question_id    // 问题ID
     * @param $fans_id        // 粉丝ID
     * @param $user_id        // 用户ID（被关注者ID）
     * @param $production     // 是否产品模式，默认为false（调试模式）
     * @return mixed
     */
    public function push_question_like($alert_info, $question_id, $fans_id, $user_id, $reg_id, $production){
        if(!isset($alert_info)||!isset($question_id)||!isset($fans_id)||!isset($user_id)||empty($reg_id)){
            Log::write("传入参数未设置或为空", "WARN");
            return false;
        }
        $product = false;
        if($production==true){
            $product = true;
        }
        try{
            $arr = array ('to_uid'=>$user_id, 'from_uid'=>$fans_id);
            $result = $this->push->push()
                ->setPlatform(M\platform('ios', 'android'))
                ->setAudience(M\audience(M\registration_id(array($reg_id))))  //  需要根据reg_id单独推送
                ->setNotification(M\notification("被点赞推送", M\android($alert_info, '嘿设汇',
                    null, array('type'=>"support_post", 'id'=>$question_id, 'infos'=>$arr)),
                    M\ios($alert_info, "default", "+1", false, array('type'=>"support_post", 'id'=>$question_id,
                        'infos'=>$arr), null)))
                ->setMessage(M\message($alert_info, null, null, array('type'=>"support_post", 'id'=>$question_id,
                    'infos'=>$arr)))
                ->setOptions(M\options(1234, 0, null, $product, 0))
                ->send();
        } catch (APIRequestException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        } catch (APIConnectionException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        }
        return $result;
    }

    /**提问被回答 推送
     * @param $alert_info     // 提示文字，信息需要调用者组装后传入
     * @param $question_id    // 问题ID
     * @param $reply_id       // 回答ID
     * @param $fans_id        // 粉丝ID
     * @param $user_id        // 用户ID（被关注者ID）
     * @param $production     // 是否产品模式，默认为false（调试模式）
     * @return mixed
     */
    public function push_question_answer($alert_info, $question_id, $reply_id,
                                            $fans_id, $user_id, $reg_id, $production){
        if(!isset($alert_info)||!isset($question_id)||!isset($reply_id)
            ||!isset($fans_id)||!isset($user_id)||empty($reg_id)){
            Log::write("传入参数未设置或为空", "WARN");
            return false;
        }
        $product = false;
        if($production==true){
            $product = true;
        }
        try{
            $arr = array ('reply_id'=>$reply_id, 'to_uid'=>$user_id, 'from_uid'=>$fans_id);
            $result = $this->push->push()
                ->setPlatform(M\platform('ios', 'android'))
                ->setAudience(M\audience(M\registration_id(array($reg_id))))  //  需要根据reg_id单独推送
                ->setNotification(M\notification("提问被回答推送", M\android($alert_info, '嘿设汇',
                    null, array('type'=>"reply_post", 'id'=>$question_id, 'infos'=>$arr)),
                    M\ios($alert_info, "default", "+1", false, array('type'=>"reply_post", 'id'=>$question_id,
                        'infos'=>$arr), null)))
                ->setMessage(M\message($alert_info, null, null, array('type'=>"reply_post", 'id'=>$question_id,
                    'infos'=>$arr)))
                ->setOptions(M\options(1234, 0, null, $product, 0))
                ->send();
        } catch (APIRequestException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        } catch (APIConnectionException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        }
        return $result;
    }

    /**楼中楼回复 推送
     * @param $alert_info    // 提示文字，信息需要调用者组装后传入
     * @param $question_id   // 问题ID
     * @param $lzl_id        // 楼中楼回复的ID
     * @param $reply_id      // 回答ID
     * @param $fans_id       // 粉丝ID
     * @param $user_id       // 用户ID（被关注者ID）
     * @param $production    // 是否产品模式，默认为false（调试模式）
     * @return mixed
     */
    public function push_floor_reply($alert_info, $question_id, $lzl_id, $reply_id,
                                         $fans_id, $user_id, $reg_id, $production){
        if(!isset($alert_info)||!isset($question_id)||!isset($lzl_id)||
            !isset($reply_id)||!isset($fans_id)||!isset($user_id)||empty($reg_id)){
            Log::write("传入参数未设置或为空", "WARN");
            return false;
        }
        $product = false;
        if($production==true){
            $product = true;
        }
        try{
            $arr = array ('reply_id'=>$reply_id, 'lzl_id'=>$lzl_id, 'to_uid'=>$user_id, 'from_uid'=>$fans_id);
            $result = $this->push->push()
                ->setPlatform(M\platform('ios', 'android'))
                ->setAudience(M\audience(M\registration_id(array($reg_id))))  //  需要根据reg_id单独推送
                ->setNotification(M\notification("楼中楼回复推送", M\android($alert_info, '嘿设汇',
                    null, array('type'=>"lzl_reply", 'id'=>$question_id, 'infos'=>$arr)),
                    M\ios($alert_info, "default", "+1", false, array('type'=>"lzl_reply", 'id'=>$question_id,
                        'infos'=>$arr), null)))
                ->setMessage(M\message($alert_info, null, null, array('type'=>"lzl_reply", 'id'=>$question_id,
                    'infos'=>$arr)))
                ->setOptions(M\options(1234, 0, null, $product, 0))
                ->send();
        } catch (APIRequestException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        } catch (APIConnectionException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        }
        return $result;
    }

    /**被提问 推送   0813a6507f0   0a14e09a866
     * @param $alert_info     // 提示文字，信息需要调用者组装后传入
     * @param $question_id    // 问题ID
     * @param $fans_id        // 粉丝ID
     * @param $user_id        // 用户ID（被关注者ID）
     * @param $production     // 是否产品模式，默认为false（调试模式）
     * @return mixed
     */
    public function push_question_asked($alert_info, $question_id, $fans_id, $user_id, $reg_id, $production){
        if(!isset($alert_info)||!isset($question_id)||!isset($fans_id)||!isset($user_id)||empty($reg_id)){
            Log::write("传入参数未设置或为空", "WARN");
            return false;
        }
        $product = false;
        if($production==true){
            $product = true;
        }
        try{
            $arr = array ('to_uid'=>$user_id, 'from_uid'=>$fans_id);
            $result = $this->push->push()
                ->setPlatform(M\platform('ios', 'android'))
                ->setAudience(M\audience(M\registration_id($reg_id)))  //  需要根据reg_id单独推送
                ->setNotification(M\notification("被提问推送", M\android($alert_info, '嘿设汇',
                    null, array('type'=>"ask_you", 'id'=>$question_id, 'infos'=>$arr)),
                    M\ios($alert_info, "default", "+1", false, array('type'=>"ask_you", 'id'=>$question_id,
                        'infos'=>$arr), null)))
                ->setMessage(M\message($alert_info, null, null, array('type'=>"ask_you", 'id'=>$question_id,
                    'infos'=>$arr)))
                ->setOptions(M\options(1234, 0, null, $product, 0))
                ->send();
        } catch (APIRequestException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        } catch (APIConnectionException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        }
        return $result;
    }

    /**用户换设备登陆 通知
     * @param $alert_info
     * @param $reg_id
     * @param $user_id
     * @param $production
     * @return bool
     */
    public function push_offline_notification($alert_info, $reg_id, $user_id, $production){
        if(!isset($alert_info)||empty($reg_id)||!isset($user_id)){
            Log::write("传入参数未设置或为空", "WARN");
            return false;
        }
        $product = false;
        if($production==true){
            $product = true;
        }
        try{
            $timestamp = time();
            $arr = array ('to_uid'=>$user_id, 'timestamp'=>$timestamp);
            $result = $this->push->push()
                ->setPlatform(M\platform('ios', 'android'))
                ->setAudience(M\audience(M\registration_id(array($reg_id))))  //  需要根据reg_id单独推送
                ->setNotification(M\notification("下线通知推送", M\android($alert_info, '嘿设汇',
                    null, array('type'=>"logout", 'id'=>$user_id, 'infos'=>$arr)),
                    M\ios($alert_info, "default", "+1", false, array('type'=>"logout", 'id'=>$user_id,
                        'infos'=>$arr), null)))
                ->setMessage(M\message($alert_info, null, null, array('type'=>"logout", 'id'=>$user_id,
                    'infos'=>$arr)))
                ->setOptions(M\options(1234, 0, null, $product, 0))
                ->send();
        } catch (APIRequestException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        } catch (APIConnectionException $e) {
            Log::write("Push Video or Article Exception: ".$e->getMessage(), "ERROR");
        }
        return $result;
    }

}
