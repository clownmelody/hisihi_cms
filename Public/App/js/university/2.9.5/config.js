/**
 * Created by airbreak on 2016/5/10.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fastclick:'sharecommon/fastclick',
        prefixfree:'sharecommon/prefixfree.min',
        base:'sharecommon/base-1.1',
        photoswipe:'sharecommon/photoswipe/photoswipe.min',
        photoswipeui:'sharecommon/photoswipe/photoswipe-ui-default.min',
        myPhotoSwipe:'sharecommon/photoswipe/myphotoswipe',
        home:'university/2.9.5/home',
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


require(['home','prefixfree'],function(university){
    var userAgent = window.location.href,
        reg = /university_id\/[1-9][0-9]*/g,
        id = userAgent.match(reg)[0].toString().replace(/university_id\//g,'');
    window.university = new university(id);
});