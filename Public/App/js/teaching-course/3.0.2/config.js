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
        deduction:'../orgbasicinfo/2.9.5/deduction',
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
    var url = window.location.href;
    if(url.indexOf('%2F')>0){
        url=url.replace(/\%2F/g,'\/');
    }
    var reg = /course_id\/[1-9][0-9]*/g,
        id = url.match(reg)[0].toString().replace(/course_id\//g,''),
        oid=$('body').attr('data-oid'),
        url=window.hisihiUrlObj.link_url+'api.php?s=/Organization/';
    window.course = new course(id,oid,url);
});