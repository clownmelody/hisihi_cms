/**
 * Created by hisihi on 2016/10/26.
 */
requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'sharecommon/zepto.min',
        fx:'sharecommon/fx_v1.1',
        fastclick:'sharecommon/fastclick',
        lazyloading:'sharecommon/lazyloading',
        prefixfree:'sharecommon/prefixfree.min',
        photoswipe:'sharecommon/photoswipe/photoswipe.min',
        photoswipeui:'sharecommon/photoswipe/photoswipe-ui-default.min',
        myPhotoSwipe:'sharecommon/photoswipe/myphotoswipe',
        home:'events/activity/activity-3.2',
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
        photoswipe:{
            output:'photoswipe'
        },
        photoswipeui:{
            steps:['photoswipe'],
            output:'photoswipeui'
        },
        lazyloading:{
            steps:['$','fx'],
            output:'lazyloading'
        },
        prefixfree:{
            output:'prefixfree'
        },
    }
});

require(['home','prefixfree'],function(Activity){
    var url = window.location.href;
    if(url.indexOf('%2F')>0){
        url=url.replace(/\%2F/g,'\/');
    }

    var reg = /id\/[0-9][0-9]*/g,
        id = url.match(reg)[0].toString().replace(/id\//g,'');
    window.Competition = new Activity(id,window.hisihiUrlObj.api_url_php);
});