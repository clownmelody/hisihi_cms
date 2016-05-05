/**
 * Created by jimmy on 2016/04/26.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
        //lazyloading:'hiworks/imglazyload.min',
        prefixfree:'sharecommon/prefixfree.min',
        fastclick:'sharecommon/fastclick',
        iscroll:'sharecommon/iscroll',
        touchslider:'hiworks/touchSlider-lib',
        base:'sharecommon/base',
        myscroll:'hiworks/myscroll',
        home:'hiworks/home',
    },
    shim: {
        $:{
            output:'$'
        },
        fx:{
            steps:['$'],
            output:'fx'
        },
        fastclick:{
            output:'fastclick'
        },
        //lazyloading:{
        //    steps:['$'],
        //    output:'lazyloading'
        //},
        prefixfree:{
            output:'prefixfree'
        },
        iscroll:{
            output:'iscroll'
        },
    }
});


require(['home','prefixfree'],function(works){
    var userAgent = window.location.href,
        reg = /category\/[1-9][0-9]*/g,
        id = userAgent.match(reg)[0].toString().replace(/category\//g,'');
    window.hiworks = new works(window.hisihiUrlObj.server_url,id);
});