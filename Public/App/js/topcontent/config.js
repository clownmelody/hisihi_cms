/**
 * Created by jimmy on 2015/12/28.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        zepto:'../sharecommon/zepto.min',
        zeptofx:'../sharecommon/fx',
        prefixfree:'../sharecommon/prefixfree.min',
        common:'../forum/hisihi_news_common',
        vote:'topcontent_vote',
    },
    shim: {
        zepto:{
            output:'Zepto'
        },
        common:{
            output:'MyCommon'
        },
    }
});


require(['zepto','common','zeptofx','vote','prefixfree'],function(Zepto,MyCommon,zeptofx,vote){
    new vote($('.bottomVoteCon'),window.hisihiUrlObj.server_url);
});