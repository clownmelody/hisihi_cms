/**
 * Created by jimmy on 2015/12/28.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        zepto:'zepto.min',
        common:'../forum/hisihi_news_common',
        sharemain:'main',
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


require(['zepto','common','sharemain'],function(Zepto,MyCommon,sharemain){
    var type=$('.moreRecommend').data('type');
    new sharemain(type);
});