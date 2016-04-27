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