/**
 * Created by jimmy on 2016/2/16.
 */
requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        $:'../sharecommon/zepto.min',
        fx:'../sharecommon/fx_v1.1',
        prefixfree:'../sharecommon/prefixfree.min',
        base:'../sharecommon/base-1.1',
        fastclick:'../sharecommon/fastclick',
        lazyloading:'../sharecommon/lazyloading',
        photoswipe:'../sharecommon/photoswipe/photoswipe.min',
        photoswipeui:'../sharecommon/photoswipe/photoswipe-ui-default.min',
        myPhotoSwipe:'../sharecommon/photoswipe/myphotoswipe',
        home:'2.9.5/orgbasicinfo',
    },
    shim: {
        $:{
            output:'$'
        },
        prefixfree:{
            output:'prefixfree'
        },
        fastclick:{
            output:'fastclick'
        },
    }
});


require(['home','prefixfree'],function(orgBasicInfo){

    var url = window.location.href;
    if(url.indexOf('%2F')>0){
        url=url.replace(/\%2F/g,'\/');
    }

    var reg = /organization_id\/[1-9][0-9]*/g,
        id = url.match(reg)[0].toString().replace(/organization_id\//g,'');
    window.orgBasicInfo = new orgBasicInfo($('#wrapper'),id,window.hisihiUrlObj.apiUrl);
});
