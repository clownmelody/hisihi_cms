/**
 * Created by jimmy on 2016/6/1.
 * 今天儿童节，儿童节快乐，武汉被大水淹了……
 */

define(['base'],function(Base){
    var Promotion =function(id){
        this.pid = id;
        var eventName='click',that=this;
        if(this.deviceType.mobile && this.isLocal){
            eventName='touchend';
        }
        this.getUserInfo(1);
        this.getBasicInfo.call(that,function(result){;
            that.getOrgBasicInfo.call(that,result,function(resultOrg){
                that.getPromotionsInfo.call(that,result,resultOrg);
            });
        });

        //领取优惠券
        $(document).on(eventName,'.sawtooth-right-main', $.proxy(this,'operateCoupon'));

        /*模态窗口操作*/
        $(document).on(eventName,'#do-login', $.proxy(this,'doLogin'));
        $(document).on(eventName,'#cancle-login', $.proxy(this,'hideLoginTipBox'));

        this.geMoreCourseInfo();
    };

    Promotion.prototype=new Base();
    Promotion.constructor=Promotion;
    var t=Promotion.prototype;

    //隐藏登录提示框
    t.hideLoginTipBox=function(){
        this.controlLoginTipModal(false);
    };

    //登录
    t.doLogin=function(){
        this.controlLoginTipModal(false);
    };

    return Promotion;
});