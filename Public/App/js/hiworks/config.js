/**
 * Created by jimmy on 2016/04/26.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
        event:'sharecommon/zepto.event',
        touch:'sharecommon/zepto.touch',
        prefixfree:'sharecommon/prefixfree.min',
        fastclick:'sharecommon/fastclick',
        iscroll:'sharecommon/iscroll',
        touchslider:'hiworks/touchSlider-lib',
        scale:'hiworks/scale',
        base:'sharecommon/base-1.1',
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
        scale:{
            output:'scale'
        },
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
    window.hiworks = new works(window.hisihiUrlObj.link_url,id);
});
