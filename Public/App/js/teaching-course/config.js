/**
 * Created by airbreak on 2016/5/10.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        base:'sharecommon/base',
        home:'university/home',
    },
    shim: {
        $:{
            output:'$'
        }
    }
});


require(['home'],function(university){
    var userAgent = window.location.href,
        reg = /university_id\/[1-9][0-9]*/g,
        id = userAgent.match(reg)[0].toString().replace(/university_id\//g,'');
    window.university = new university(id);
});