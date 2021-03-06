/**
 * Created by jimmy on 2015/12/28.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        zepto:'../sharecommon/zepto.min',
        zeptofx:'../sharecommon/fx',
        prefixfree:'../sharecommon/prefixfree.min',
        //debuggap:'../sharecommon/debuggap',
        common:'../forum/hisihi_news_common',
        vote:'topcontent_vote',
        sharemain:'../sharecommon/main',
    },
    shim: {
        zepto:{
            output:'Zepto'
        },
        common:{
            output:'MyCommon'
        },
        sharemain:{
            output:'Sharemain'
        },
    }
});


require(['zepto','common','zeptofx','vote','sharemain','prefixfree'],function(Zepto,MyCommon,zeptofx,vote,sharemain){
    $('#downloadCon a')[0].href='http://www.hisihi.com/download.php';
    $('#loadingTip a')[0].removeAttribute("disabled");
    //访问来源
    var userAgent = window.location.href;
    if(userAgent.indexOf("hisihi-app")>=0){
        $('#downloadCon').hide();
    }

    new vote($('.bottomVoteCon'),window.hisihiUrlObj.server_url);
    var type=$('.moreRecommend').data('type');
    var cId=$('.bottomVoteCon').data('id');
    new sharemain(type,cId);
});