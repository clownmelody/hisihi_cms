/**
 * Created by jimmy on 2016/6/22.
 */


requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        prefixfree:'sharecommon/prefixfree.min',
        base:'sharecommon/base',
        fastclick:'sharecommon/fastclick',
        lazyloading:'sharecommon/lazyloading',
        home:'promotion/home-share',
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
    var url = window.location.href;
    if(url.indexOf('%2F')>0){
        url=url.replace(/\%2F/g,'\/');
    }
    var reg = /promotion_id\/[1-9][0-9]*/g,
        id = url.match(reg)[0].toString().replace(/promotion_id\//g, '');
    window.promotion = new promotion(id);
});