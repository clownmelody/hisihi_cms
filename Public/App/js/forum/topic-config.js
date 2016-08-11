/**
 * Created by jimmy on 2016/6/13.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
        fastclick:'sharecommon/fastclick',
        lazyloading:'sharecommon/lazyloading',
        prefixfree:'sharecommon/prefixfree.min',
        iscroll:'sharecommon/iscroll',
        myscroll:'sharecommon/myscroll-v2.0',
        home:'forum/topic-share',
        base:'sharecommon/base-1.1'
    },
    shim: {
        $:{
            output:'$'
        },
        fx:{
            steps:['$'],
            output:'fx'
        },
        lazyloading:{
            steps:['$','fx'],
            output:'lazyloading'
        },
        prefixfree:{
            output:'prefixfree'
        },
        iscroll:{
            output:'iscroll'
        },
    }
});


require(['fx','home','prefixfree'],function(fx,topic){
    //var userAgent = window.location.href,
    //    reg = /category\/[1-9][0-9]*/g,
    //    id = userAgent.match(reg)[0].toString().replace(/category\//g,'');
    window.topic = new topic();
});