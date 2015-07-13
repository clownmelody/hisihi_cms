<?php

namespace Addons\JPush;
use Addons\JPush\Controller\JPushController;
use Common\Controller\Addon;

/**
 * 极光推送插件
 * @author RFly
 */
//http://localhost:8989/hisihi_web/api.php?s=%2Fuser%2Flogin&password=123456&username=10011234001&reg_id=0813a6507f0&client=5&session_id=c3177a47uaokloicrqfqj85o34
//http://localhost:8989/hisihi_web/api.php?s=%2Fuser%2Flogin&password=123456&username=10011234002&reg_id=0a14e09a866&client=4&session_id=gv5344qhmnsd5l3nifivrcoaf3
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
                $param['user_id'], $param['reg_id'], $param['production']);
        }

        public function push_question_like($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_question_like($param['alert_info'], $param['question_id'],
                $param['fans_id'], $param['user_id'], $param['reg_id'], $param['production']);
        }

        public function push_question_answer($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_question_answer($param['alert_info'], $param['question_id'],
                $param['reply_id'], $param['fans_id'], $param['user_id'], $param['reg_id'], $param['production']);
        }

        public function push_floor_reply($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_floor_reply($param['alert_info'], $param['question_id'],
                $param['lzl_id'], $param['reply_id'], $param['fans_id'], $param['user_id'],
                $param['reg_id'], $param['production']);
        }

        public function push_question_asked($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_question_asked($param['alert_info'], $param['question_id'],
                $param['fans_id'], $param['user_id'], $param['reg_id'], $param['production']);
        }

        public function push_offline_notification($param){
            $this->checkArguments($param);
            $pushService = new JPushController();
            $pushService->push_offline_notification($param['alert_info'], $param['reg_id'],
                $param['user_id'], $param['production']);
        }

        private function checkArguments($param){
            if(!is_array($param)||empty($param)){
                throw new InvalidArgumentException("Invalid arguments exception");
            }
        }

    }