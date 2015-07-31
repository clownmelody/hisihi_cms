<?php

function echoDownLoadInfo(){
    echo "<h3>请点击右上角在浏览器中打开下载</h3>";
}

$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
$is_weixin = strpos($agent, 'micromessenger') ? true : false ;
if($is_weixin){
    echoDownLoadInfo();
} else {
    header("Content-type:text/html; charset=utf-8");
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if(stristr($_SERVER['HTTP_USER_AGENT'],'Android')) {
        //header('Location: http://hisihi-apk.oss-cn-qingdao.aliyuncs.com/hisihi.apk');
        header('Location: http://pan.baidu.com/s/1bnbgnQB');
    }else if(stristr($_SERVER['HTTP_USER_AGENT'],'iPhone')){
        header('Location: https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewSoftware?id=983468515');
    }else{
        //header('Location: http://hisihi-apk.oss-cn-qingdao.aliyuncs.com/hisihi.apk');
        header('Location: http://pan.baidu.com/s/1bnbgnQB');
    }
}


