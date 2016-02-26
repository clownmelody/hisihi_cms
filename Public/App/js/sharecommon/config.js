/**
 * Created by jimmy on 2015/12/28.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        zepto:'zepto.min',
        common:'../forum/hisihi_news_common',
        prefixfree:'../sharecommon/prefixfree.min',
        sharemain:'main',
    },
    shim: {
        zepto:{
            deps:[],
            exports:'Zepto'
        },
        common:{
            deps:[],
            exports:'MyCommon'
        },
        sharemain:{
            deps:[],
            exports:'Sharemain'
        },
    }
});


require(['zepto','common','sharemain','prefixfree'],function(Zepto,MyCommon,sharemain){
    var $recommend=$('.moreRecommend'),
        type=$recommend.data('type'),
        cid=$recommend.data('id');
    new sharemain(type,cid);
});