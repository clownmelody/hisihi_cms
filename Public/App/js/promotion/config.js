/**
 * Created by airbreak on 2016/5/10.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        prefixfree:'sharecommon/prefixfree.min',
        base:'sharecommon/base',
        fastclick:'sharecommon/fastclick',
        lazyloading:'sharecommon/lazyloading',
        home:'promotion/home',
    },
    shim: {
        $:{
            output:'$'
        },
        prefixfree:{
            output:'prefixfree'
        },
        fastclick:{
            output:'fastclick'
        },
    }
});


require(['home','prefixfree'],function(promotion){
    var userAgent = window.location.href,
        reg = /promotion_id\/[1-9][0-9]*/g,
        id = userAgent.match(reg)[0].toString().replace(/promotion_id\//g,'');
    window.promotion = new promotion(id);
});