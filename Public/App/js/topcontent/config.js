/**
 * Created by jimmy on 2015/12/28.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        zepto:'../sharecommon/zepto.min',
        zeptofx:'../sharecommon/fx',
        prefixfree:'../sharecommon/prefixfree.min',
        debuggap:'../sharecommon/debuggap',
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


require(['zepto','common','zeptofx','vote','sharemain','prefixfree','debuggap'],function(Zepto,MyCommon,zeptofx,vote,sharemain){
    $('#downloadCon a')[0].href='http://www.hisihi.com/download.php';
    $('#loadingTip a')[0].removeAttribute("disabled");
    new vote($('.bottomVoteCon'),window.hisihiUrlObj.server_url);
    var type=$('.moreRecommend').data('type');
    new sharemain(type);
});