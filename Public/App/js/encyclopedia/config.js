/**
 * Created by hisihi on 2016/10/31.
 */
requirejs.config({
    //give me the things that I loved when the diamond in the sky
    baseUrl:window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
        base:'sharecommon/base-1.1',
        fastclick:'sharecommon/fastclick',
        prefixfree:'sharecommon/prefixfree.min',
        home:'encyclopedia/encyclopedia',
        //约定不需要具体网络数据获取情况
    },
    shim: {
        $: {
            output: '$'
        },
        fx: {
            steps: ['$'],
            output: 'fx'
        },
        prefixfree: {
            output: 'prefixfree'
        },
    }
});

require(['home','prefixfree'],function(Encyclopedia) {
    var url = window.location.href;
    if (url.indexOf('%2f') > 0) {
        url=url.replace(/\%2F/g,'\/');
    }

    var reg = /id\/[0-9][0-9]*/g,
        id = url.match(reg)[0].toString().replace(/id\//g,'');
    window.Encyclopedia = new Encyclopedia(id,window.hisihiUrlObj.api_url_php);
});