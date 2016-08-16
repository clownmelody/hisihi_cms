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
        photoswipe:'sharecommon/photoswipe/photoswipe.min',
        photoswipeui:'sharecommon/photoswipe/photoswipe-ui-default.min',
        myPhotoSwipe:'sharecommon/photoswipe/myphotoswipe',
        home:'forum/topic/topic-share',
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
        photoswipe:{
            output:'photoswipe'
        },
        photoswipeui:{
            steps:['photoswipe'],
            output:'photoswipeui'
        },
        lazyloading:{
            steps:['$','fx'],
            output:'lazyloading'
        },
        prefixfree:{
            output:'prefixfree'
        },
        scale:{
            output:'scale'
        },
    }
});


require(['fx','home','prefixfree'],function(fx,topic){
    //var userAgent = window.location.href,
    //    reg = /category\/[1-9][0-9]*/g,
    //    id = userAgent.match(reg)[0].toString().replace(/category\//g,'');
    window.topic = new topic();
});