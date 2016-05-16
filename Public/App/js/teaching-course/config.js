/**
 * Created by airbreak on 2016/5/10.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        prefixfree:'sharecommon/prefixfree.min',
        base:'sharecommon/base',
        home:'teaching-course/home',
    },
    shim: {
        $:{
            output:'$'
        },
        prefixfree:{
            output:'prefixfree'
        },
    }
});


require(['home','prefixfree'],function(course){
    var userAgent = window.location.href,
        reg = /course_id\/[1-9][0-9]*/g,
        id = userAgent.match(reg)[0].toString().replace(/course_id\//g,'');
    var oid=$('body').attr('data-oid');
    window.course = new course(id,oid);
});