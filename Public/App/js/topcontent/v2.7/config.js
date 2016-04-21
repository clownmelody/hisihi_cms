/**
 * Created by jimmy on 2015/12/28.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'../sharecommon/zepto.min',
        fx:'../sharecommon/fx_v1.1',
        prefixfree:'../sharecommon/prefixfree.min',
        iscroll:'../sharecommon/iscroll',
        base:'../sharecommon/base',
        home:'v2.7/home',
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


require(['home','prefixfree'],function(topContent){
    var id=$('body').data('id');
    id=1264;
    new topContent(id,window.hisihiUrlObj.server_url);
});