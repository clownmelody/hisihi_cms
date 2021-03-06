/**
 * Created by hisihi on 2016/10/24.
 */
requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
        fastclick:'sharecommon/fastclick',
        prefixfree:'sharecommon/prefixfree.min',
        home:'events/competition/competition-3.2',
        base:'sharecommon/base-1.1',
        async:'sharecommon/async',
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
    }
});

require(['home','prefixfree'],function(Competition){
    var url = window.location.href;
    if(url.indexOf('%2F')>0){
        url=url.replace(/\%2F/g,'\/');
    }

    var reg = /id\/[0-9][0-9]*/g,
        id = url.match(reg)[0].toString().replace(/id\//g,'');
    window.Competition = new Competition(id,window.hisihiUrlObj.api_url_php);
});