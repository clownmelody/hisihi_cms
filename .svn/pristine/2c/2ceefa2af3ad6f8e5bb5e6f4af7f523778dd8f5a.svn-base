<?php
/**
 * Created by PhpStorm.
 * User: RFly
 * Date: 2015/2/21 0021
 * Time: 下午 9:07
 */
define('Scan', true);
$lifeTime = 10*60;
session_set_cookie_params($lifeTime);
session_start();
?>

<?php if(!$_SESSION["token"]){//登录前?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <link rel="shortcut icon" href="/images/web-favicon.ico"/>
        <style>
            body {
    font-family: "Microsoft YaHei";
    margin: 0;
    padding: 0;
    text-align: center;
    background: repeat url("/images/hiworks_bg.png")
}

h1 {
    padding-top: 50px;
    color: #e7e7e7;
    font-size: 36px;
    font-weight: normal;
    letter-spacing: 10px
}

h2 {
    color: #8f8f8f;
    font-size: 16px;
    font-weight: normal;
    letter-spacing: 6px
}

#qrcode {
    position: relative;
    display: inline-block;
    width: 250px;
    height: 50%;
    z-index: 1000;
}

#container {
    width: 250px;
    height: 250px;
    line-height: 250px;
    color: #3e3a39;
    background-color: #e8e8e8;
    box-shadow: 0 0 16px rgba(0,0,0,0.5);
    z-index: 900;
}

#container>* {
    display: block
}

#refresh {
    width: 250px;
    height: 250px;
    margin-top: -250px;
    line-height: 250px;
    color: #fff;
    background-color: #000;
    display: none;
    opacity: .78;
    z-index: 1000;
    cursor: pointer;
}

#ft {
    position: relative;
    display: inline-block;
    top:50px;
    bottom: 20px;
    width: 100%;
    padding-bottom: 10px
}

#ft .logo {
    width: 100%;
    height: 55px;
    background: url("/images/bottom_logo.png") no-repeat center
}

#ft .copy {
    height: 30px
}

#ft .copy p {
    width: 100%;
    line-height: 15px;
    font-size: 12px;
    text-align: center;
    color: #a1a1a1
}
        </style>
        <title>嘿云作业—扫描二维码进入</title>
        <script src="/js/jquery-1.8.3.min.js"></script>
        <script src="/js/jquery.qrcode.js"></script>
    </head>
    <body>
    <h1>嘿云作业</h1>
    <div id="qrcode">
        <div id="container">正在生成二维码</div>
        <div id="refresh">二维码已失效，请点击刷新</div>
        <h2>扫描进入云作业库</h2>
    </div>
    
    <div id='ft'>
        <div class="logo"></div>
        <div class="copy">
            <p>Copyright©2015-2016 www.hisihi.com All Rights Reserved. 武汉迅牛科技有限公司</p>
            <p>鄂ICP备15003238号</p>
        </div>
    </div>
    <script>
        var ASK_TIME=<?php echo 5;?>;
        var i=0;
        var guid;

        $(function(){
            $.get('/api.php?s=/qrscan/initdata',
                function(d){
                    if(d){
                        guid=d.message;
                        var options={
                            render:"image",
                            ecLevel:"H",
                            minVersion:3,
                            color:"#3e3a39",
                            bgColor:"#e8e8e8",
                            text:"hisihi://hiworks?guid="+guid,
                            size:250,
                            radius:0.5,
                            quiet:1,
                            mode:0
                        };
                        $("#container").empty().qrcode(options)}},'json');
            setInterval(function(){
                $.get('/api.php?client=pc&s=/qrscan/askstatus/guid/'+guid,
                    function(d){
                        if(d){
                            if(d.success==true){
                                window.location=''
                            }else if(d.error_code==-1){
                                $('#refresh').css('display','block');
                                if($.browser.msie && parseInt($.browser.version) <= 8){
                                    $('#container').css('visibility','hidden');
                                }
                            }}},'json')},ASK_TIME*1000)
        });
        $('#refresh').click(function(){
            $.get('/api.php?s=/qrscan/initdata',
                function(d){
                    if(d){
                        guid=d.message;
                        var options={
                            render:"image",
                            ecLevel:"H",
                            minVersion:3,
                            color:"#3e3a39",
                            bgColor:"#e8e8e8",
                            text:"hisihi://hiworks?guid="+guid,
                            size:250,
                            radius:0.5,
                            quiet:1,
                            mode:0
                        };
                        $("#container").empty().qrcode(options)}},'json');
            $('#refresh').css('display','none');
            if($.browser.msie && parseInt($.browser.version) <= 8){
                $('#container').css('visibility','visible');
            }
        });
    </script>
    </body>
    </html>
<?php }else{//登录后?>
    <?php
    require 'hiworks_list.php';
    ?>
<?php }?>

