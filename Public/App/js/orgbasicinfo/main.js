/**
 * Created by jimmy on 2015/12/28.
 */
requirejs.config({
    baseUrl: window.urlObject.js,
    paths: {
        zepto:'../zepto',
        prefixfree:'prefixfree.min',
        common:'../forum/hisihi_news_common',
        orgbasicinfo:'orgbasicinfo',
    },
    shim: {
        zepto:{
            output:'Zepto'
        },
        common:{
            output:'MyCommon'
        }
    }
});


require(['zepto','common','orgbasicinfo','prefixfree'],function(Zepto,MyCommon,OrgBasicInfo){
    new OrgBasicInfo($('#wrapper'));
});