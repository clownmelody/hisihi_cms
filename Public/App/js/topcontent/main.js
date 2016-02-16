/**
 * Created by jimmy on 2015/12/28.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        zepto:'zepto.min',
        zeptofx:'fx',
        common:'../forum/hisihi_news_common',
        vote:'topcontent_vote',
        sharemain:'../sharelist/main',
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


require(['zepto','common','zeptofx','vote','sharemain'],function(Zepto,MyCommon,zeptofx,vote,sharemain){
    var aLink=document.getElementsByTagName("a");
    for(var i=0; i<aLink.length; i++){
        aLink[i].href = "javascript:void(0);";
        aLink[i].setAttribute("disabled", "disabled");
    }
    $('#downloadCon a')[0].href='http://www.hisihi.com/download.php';
    $('#loadingTip a')[0].removeAttribute("disabled");
    new Vote($('.bottomVoteCon'),window.hisihiUrlObj.server_url);
    var type=$('.moreRecommend').data('type');
    new sharemain(type);
});