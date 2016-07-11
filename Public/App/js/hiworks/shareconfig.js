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
        base:'sharecommon/base-1.1',
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
    var url = window.location.href;
    if(url.indexOf('%2F')>0){
        url=url.replace(/\%2F/g,'\/');
    }
    var reg = /hiword_id\/[1-9][0-9]*/g,
        id = url.match(reg)[0].toString().replace(/hiword_id\//g,'');
    window.hiworks = new works(window.hisihiUrlObj.link_url,id);
});