/**
 * Created by jimmy on 2015/12/28.
 */
requirejs.config({
    baseUrl: window.urlObject.js,
    paths: {
        zepto:'zepto.min',
        prefixfree:'prefixfree.min',
        common:'../forum/hisihi_news_common',
        stackblur:'stackblur',
        orgbasicinfo:'orgbasicinfo',
    },
    shim: {
        zepto:{
            output:'Zepto'
        },
        common:{
            output:'MyCommon'
        },
        stackblur:{
            output:'StackBlur'
        }
    }
});


require(['zepto','common','orgbasicinfo','prefixfree','stackblur'],function(Zepto,MyCommon,OrgBasicInfo){
    var $target=$('#wrapper');
    new OrgBasicInfo($target,$target.data('oid'));
});