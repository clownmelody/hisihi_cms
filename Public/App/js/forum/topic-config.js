/**
 * Created by jimmy on 2016/6/13.
 */

requirejs.config({
    baseUrl: window.urlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        //lazyloading:'hiworks/imglazyload.min',
        prefixfree:'sharecommon/prefixfree.min',
        iscroll:'sharecommon/iscroll',
        myscroll:'sharecommon/myscroll-v2.0',
        home:'forum/topic-share',
        base:'sharecommon/base'
    },
    shim: {
        $:{
            output:'$'
        },
        //lazyloading:{
        //    steps:['$'],
        //    output:'lazyloading'
        //},
        prefixfree:{
            output:'prefixfree'
        },
        iscroll:{
            output:'iscroll'
        },
    }
});


require(['home','prefixfree'],function(topic){
    //var userAgent = window.location.href,
    //    reg = /category\/[1-9][0-9]*/g,
    //    id = userAgent.match(reg)[0].toString().replace(/category\//g,'');
    window.topic = new topic();
});