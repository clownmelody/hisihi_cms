<?php

namespace Addons\JPush\Controller;
use Home\Controller\AddonsController;

use JPush\Model as M;
use JPush\JPushClient;
use JPush\JPushLog;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;

class JPushController extends AddonsController{
    protected $push;
    public function _initialize() {
        Vendor('Psr.Log.LoggerInterface');
        Vendor('Httpful.Httpful');
        Vendor('Httpful.Request');
        Vendor('Httpful.Http');
        Vendor('Httpful.Bootstrap');
        Vendor('Monolog.Formatter.FormatterInterface');
        Vendor('Monolog.Formatter.NormalizerFormatter');
        Vendor('Monolog.Formatter.LineFormatter');
        Vendor('Monolog.Registry');
        Vendor('Monolog.ErrorHandler');
        Vendor('Monolog.Logger');
        Vendor('Monolog.Handler.HandlerInterface');
        Vendor('Monolog.Handler.AbstractHandler');
        Vendor('Monolog.Handler.AbstractProcessingHandler');
        Vendor('Monolog.Handler.StreamHandler');
        Vendor('JPush.Model.Audience');
        Vendor('JPush.Model.PushPayload');
        Vendor('JPush.Model.Options');
        Vendor('JPush.Model.Platform');
        Vendor('JPush.Model.Notification');
        Vendor('JPush.JPushLog');
        Vendor('JPush.JPushClient');

        $config=  get_addon_config('JPush');
        $app_key = $config['app_key'];
        $master_secret = $config['master_secret'];                //改成自己的key
        JPushLog::setLogHandlers(array(new StreamHandler('jpush.log', Logger::DEBUG)));
        $this -> push = new JPushClient($app_key, $master_secret);
    }
    public function pushtouser($user,$alert,$title,$extras){               //user可以是多个用户，用逗号隔开              //alert为推送内容，title为标题，extras为自定义参数
        $userarray=explode(',',$user);
        try{
            $result = $this -> push -> push()
                ->setPlatform(M\all)
                ->setAudience(M\audience(M\alias($userarray)))
                ->setNotification(M\notification(null, M\android($alert,$title,null,$extras)))
                ->send();

        }catch (APIRequestException $e) {

        }
    }
}
