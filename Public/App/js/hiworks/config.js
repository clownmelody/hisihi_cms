/**
 * Created by jimmy on 2016/04/26.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
        prefixfree:'sharecommon/prefixfree.min',
        iscroll:'sharecommon/iscroll',
        base:'sharecommon/base',
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
        prefixfree:{
            output:'prefixfree'
        },
        iscroll:{
            steps:['$'],
            output:'IScroll'
        },
        base:{
            output:'Base'
        },
        home:{
            output:'home'
        },
    }
});


require(['home','prefixfree'],function(work){
    var id=$('body').data('id');
    //id=1264;
    window.topContentObj = new work(window.hisihiUrlObj.server_url);
});