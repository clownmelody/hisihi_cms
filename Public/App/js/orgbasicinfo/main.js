/**
 * Created by jimmy on 2015/12/28.
 */
requirejs.config({
    baseUrl: window.urlObject.js,
    paths: {
        zepto:'zepto.min',
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
        },
        debuggap:{
            output:'debuggap'
        }
    }
});


require(['zepto','common','orgbasicinfo','prefixfree'],function(Zepto,MyCommon,OrgBasicInfo){
    var $target=$('#wrapper');
    new OrgBasicInfo($target,$target.data('oid'));
});