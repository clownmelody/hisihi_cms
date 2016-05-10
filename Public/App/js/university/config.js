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


require(['home'],function(works){
    var userAgent = window.location.href,
        reg = /category\/[1-9][0-9]*/g,
        id = userAgent.match(reg)[0].toString().replace(/category\//g,'');
    window.hiworks = new works(window.hisihiUrlObj.server_url,id);
});