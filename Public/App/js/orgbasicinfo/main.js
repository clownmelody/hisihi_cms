/**
 * Created by jimmy on 2016/2/16.
 */
requirejs.config({
    baseUrl: window.urlObject.js,
    paths: {
        zepto:'zepto.min',
        zeptofx:'fx',
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


require(['zepto','common','zeptofx','orgbasicinfo','prefixfree'],function(Zepto,MyCommon,zeptofx,OrgBasicInfo){
    var $target=$('#wrapper');
    new OrgBasicInfo($target,$target.data('oid'));
});
