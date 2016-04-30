/**
 * Created by jimmy on 2016/04/26.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
        lazyloading:'hiworks/imglazyload.min',
        prefixfree:'sharecommon/prefixfree.min',
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
        //touchslider:{
        //    steps:['$'],
        //    output:'TouchSlider'
        //},
        lazyloading:{
            steps:['$'],
            output:'lazyloading'
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
    var id=$('body').data('id');
    id=45;
    window.hiworks = new works(window.hisihiUrlObj.server_url,id);
});