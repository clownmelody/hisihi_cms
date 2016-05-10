/**
 * Created by jimmy on 2016/04/26.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
        fastclick:'sharecommon/fastclick',
        prefixfree:'sharecommon/prefixfree.min',
        touchslider:'hiworks/touchSlider-lib',
        scale:'hiworks/scale',
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
        scale:{
            output:'scale'
        },
        fastclick:{
            output:'fastclick'
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