/**
 * Created by airbreak on 2016/5/10.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js+'/sharecommon',
    paths: {
        $:'zepto.min',
        prefixfree:'prefixfree.min',
        fastclick:'fastclick',
        base:'base-1.1',
        home:'../teaching-course/3.0.2/home',
        async:'async',
        lazyloading:'lazyloading',
        photoswipe:'photoswipe/photoswipe.min',
        myPhotoSwipe:'photoswipe/myphotoswipe',
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


require(['home','prefixfree'],function(course){
    var userAgent = window.location.href,
        reg = /course_id\/[1-9][0-9]*/g,
        id = userAgent.match(reg)[0].toString().replace(/course_id\//g,'');
    var oid=$('body').attr('data-oid'),
        url=window.hisihiUrlObj.link_url+'api.php?s=/Organization/';
    window.course = new course(id,oid,url);
});