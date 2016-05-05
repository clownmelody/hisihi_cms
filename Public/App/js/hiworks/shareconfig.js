/**
 * Created by jimmy on 2016/04/26.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
        prefixfree:'sharecommon/prefixfree.min',
        touchslider:'hiworks/touchSlider-lib',
        base:'sharecommon/base',
        home:'hiworks/sharehome',
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
        }
    }
});


require(['home','prefixfree'],function(works){
    var id=$('body').data('id');
    window.hiworks = new works(window.hisihiUrlObj.server_url,id);
});