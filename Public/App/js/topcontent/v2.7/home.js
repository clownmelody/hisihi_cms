/**
 * Created by jimmy on 2016/4/18.
 * version-2.7
 */
define(['fx','base'],function(fx,Base) {
    var Topcontent = function (id, url) {
        this.baseUrl = url;
        this.$wrapper = $('body');
        //访问来源
        var userAgent = window.location.href;
        this.articleId = id;
        this.userInfo = {session_id: ''};
        this.isFromApp = userAgent.indexOf("hisihi-app") >= 0;
        this.usedAppLoginFn = false;  //是否使用app 的登录方法

        var eventName='click',
            webview = this.operationType();
        if(webview.mobile){
            eventName='touchend';
        }
        //加载投票信息
        //this.separateOperation(this.loadVoteInfo);
        //this.$wrapper.on('click', '.mainVoteBtnCon .upBtnAble', $.proxy(this, 'execVoteUp'));
        //this.$wrapper.on('click', '.mainVoteBtnCon .downBtnAble', $.proxy(this, 'execVoteDown'));

        $(document).on(eventName,'.btn',function(){});


    };
    Topcontent.prototype =new Base();
    Topcontent.constructor=Topcontent;

    var t=Topcontent.prototype;
    return Topcontent;
});