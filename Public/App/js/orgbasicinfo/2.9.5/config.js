/**
 * Created by jimmy on 2016/2/16.
 */
requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'../sharecommon/zepto.min',
        fx:'../sharecommon/fx_v1.1',
        prefixfree:'../sharecommon/prefixfree.min',
        base:'../sharecommon/base-1.1',
        fastclick:'../sharecommon/fastclick',
        lazyloading:'../sharecommon/lazyloading',
        scale:'../hiworks/scale',
        toucher:'2.9.5/toucher',
        touch:'../sharecommon/zepto.event.touch',
        mysilder:'../sharecommon/myslider',
        home:'2.9.5/orgbasicinfo',
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
        scale:{
            output:'scale'
        },
        toucher:{
            output:'toucher'
        },
    }
});


require(['home','prefixfree'],function(orgBasicInfo){

    var url = window.location.href;
    if(url.indexOf('%2F')>0){
        url=url.replace(/\%2F/g,'\/');
    }

    var reg = /organization_id\/[1-9][0-9]*/g,
        id = url.match(reg)[0].toString().replace(/organization_id\//g,'');
    window.orgBasicInfo = new orgBasicInfo($('#wrapper'),id,window.hisihiUrlObj.apiUrl);
});
