/**
 * Created by airbreak on 2016/2/19.
 */


requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        zepto:'../sharecommon/zepto.min',
        zeptofx:'../sharecommon/fx',
        appmin:'../app.min',
        vote:'topcontent_vote'
    },
    shim: {
        zepto:{
            deps:[],
            exports:'Zepto'
        },

        appmin:{
            deps:[],
            exports:'App'
        }
    }
});


require(['zepto','zeptofx','appmin','vote'],function(Zepto,zeptofx,App,Vote) {
    App.controller('home', function (page) {
        // put stuff here
        //rawText      : '点击“确定”下载<br>【嘿设汇App】<br>体验更多功能',
        $(page)
            .find('.app-button')
            .on('click', function () {
                window.location.href = "http://www.hisihi.com/download.php";
                /*App.dialog({
                 title        : '嘿设汇',
                 rawText      : '<img style="width: 50px;" src="__IMG__/icon.png" /><br>火速下载',
                 okButton     : '确定',
                 cancelButton : '取消'
                 }, function (tryAgain) {
                 if (tryAgain) {
                 location.href = 'http://www.hisihi.com/app/downloadapp.html';
                 }
                 });*/
            });
    });
    App.controller('page2', function (page) {
        // put stuff here
    });
    try {
        App.restore();
    } catch (err) {
        App.load('home');
    }

    $("a").removeAttr('href');
    var userAgent = window.location.href;
    if (userAgent.indexOf("hisihi-app") < 0) {
        $(".app-bottombar").show();
        $(".bottomspace").show();
    }
    new Vote($('.bottomVoteCon'), window.hisihiUrlObj.server_url);
});