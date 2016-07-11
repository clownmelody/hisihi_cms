/**
 * Created by jimmy on 2016/04/26.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
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
    var url = window.location.href;
    if(url.indexOf('%2F')>0){
        url=url.replace(/\%2F/g,'\/');
    }
    var reg = /category\/[1-9][0-9]*/g,
        id = url.match(reg)[0].toString().replace(/category\//g,'');
    window.hiworks = new works(window.hisihiUrlObj.link_url,id);
});
