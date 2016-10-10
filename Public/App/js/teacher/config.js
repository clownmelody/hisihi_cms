/**
 * Created by hisihi on 2016/10/10.
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
        home:'forum/teacher/teacherv3.1',
        base:'sharecommon/base-1.1',
        async:'sharecommon/async',
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
    }
});


require(['fx','home','prefixfree'],function(fx,Detail){
    var userAgent = window.location.href,
        reg = /post_id\/[1-9][0-9]*/g,
        id = userAgent.match(reg)[0].toString().replace(/post_id\//g,'');
    window.topic = new Detail(id);
});