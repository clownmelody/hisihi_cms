/**
 * Created by airbreak on 2016/5/10.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        prefixfree:'sharecommon/prefixfree.min',
        fastclick:'sharecommon/fastclick',
        base:'sharecommon/base-1.1',
        home:'teaching-course/homev2.9',
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