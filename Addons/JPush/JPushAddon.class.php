<?php

namespace Addons\JPush;
use Addons\JPush\Controller\JPushController;
use Common\Controller\Addon;

/**
 * 极光推送插件
 * @author RFly
 */

    class JPushAddon extends Addon{

        public $info = array(
            'name'=>'JPush',
            'title'=>'极光推送',
            'description'=>'调用极光推送SDK，完成向Android、iOS客户端指定用户推送消息的功能',
            'status'=>1,
            'author'=>'RFly',
            'version'=>'0.1'
        );

        public function install(){
            return true;
        }

        public function uninstall(){
            return true;
        }

        public function push_video_article($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_video_article($param['alert_info'], $param['id'],
                $param['type'], $param['production']);
        }

        public function push_followed($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_followed($param['alert_info'], $param['fans_id'],
                $param['user_id'],$param['production']);
        }

        public function push_question_like($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_question_like($param['alert_info'], $param['question_id'],
                $param['fans_id'], $param['user_id'], $param['production']);
        }

        public function push_question_answer($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_question_answer($param['alert_info'], $param['question_id'],
                $param['reply_id'], $param['fans_id'], $param['user_id'], $param['production']);
        }

        public function push_floor_reply($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_floor_reply($param['alert_info'], $param['question_id'],
                $param['lzl_id'], $param['reply_id'], $param['fans_id'], $param['user_id'], $param['production']);
        }

        public function push_question_asked($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_question_asked($param['alert_info'], $param['question_id'],
                $param['fans_id'], $param['user_id'], $param['production']);
        }

        private function checkArguments($param){
            if(!is_array($param)||empty($param)){
                throw new InvalidArgumentException("Invalid arguments exception");
            }
        }

    }