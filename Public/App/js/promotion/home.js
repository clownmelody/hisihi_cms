/**
 * Created by jimmy on 2016/6/1.
 * 今天儿童节，儿童节快乐，武汉被大水淹了……
 */

define(['base','fastclick'],function(Base){
    FastClick.attach(document.body);
    var Promotion =function(id){
        this.pid = id;
        var eventName='click',that=this;
        if(this.deviceType.mobile && this.isLocal){
            eventName='touchend';
        }
        this.getUserInfo(null,1);  // 0 不使用token ，使用session_id的形式，1 基础令牌,  否则为具体用户令牌

        //领取优惠券
        $(document).on(eventName,'.list .coupon', $.proxy(this,'operateCoupon'));

        //显示课程详情
        $(document).on(eventName,'.list .middle', $.proxy(this,'goToCoursePage'));
        $(document).on(eventName,'.list .left', $.proxy(this,'goToCoursePage'));

        /*模态窗口操作*/
        $(document).on(eventName,'#do-login', $.proxy(this,'doLogin'));
        $(document).on(eventName,'#cancle-login', $.proxy(this,'hideLoginTipBox'));

        this.init();
    };

    Promotion.prototype=new Base();
    Promotion.constructor=Promotion;
    var t=Promotion.prototype;

    t.init=function(){
        this.controlLoadingBox(true);
        var that=this;
        window.setTimeout(function(){
            that.loadPromotionBasicInfo();
            that.loadPromotionCource();
        },100);
    };

    //获得当前活动的基本信息
    t.loadPromotionBasicInfo=function(callback){
        this.controlLoadingBox(true);
        var that = this,
            $box=$('.promotion-box'),
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/org/promotion/'+this.pid,
                type: 'get',
                async:false,
                paraData: null,
                needToken:true,
                sCallback: function (resutl) {
                    $box.css('opacity','1');
                    that.fillInPromotionBasicInfo(resutl);
                    callback && callback(resutl);
                },
                eCallback: function (data) {
                    $box.css('opacity','1');
                    var txt=data.txt;
                    if(data.code=404){
                        txt='信息加载失败';
                    }
                    that.controlLoadingBox(false);
                    that.showTips.call(that,txt);
                    $('#current-info .nodata').show();
                    callback && callback();
                },
            };
        this.getDataAsyncPy(para);
    };

    /*显示活动基本信息*/
    t.fillInPromotionBasicInfo=function(result){
        var $pBox=$('.promotion-box'),
            imgUrl=result.logo_url,
            desc=result.description;
        if(!imgUrl){
            imgUrl=window.urlObj.img+'/hiworks/hisihi.png'
        }
        $pBox.find('img').attr('src',imgUrl);
        desc=this.substrLongStr(desc,200);
        $pBox.find('.content-box p').text(desc);
        $('title').text(result.title);
    }

    /*获得参与活动的课程列表*/
    t.loadPromotionCource=function(callback){
        var token=this.userInfo.token,
            $box= $('.course-box');
        if(!token) {
            this.getBasicToken({
                account: 'jg2rw2xVjyrgbrZp',
                secret: 'VbkzpPlZ6H4OvqJW',
                type: 100
            }, false, function (result) {
                token = result;
            });
        }
        var that = this,
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/org/promotion/'+this.pid+'/teaching_course',
                type: 'get',
                async:false,
                paraData: null,
                needToken:true,
                token:this.userInfo.token,
                sCallback: function (resutl) {
                    $box.css('opacity','1');
                    that.fillInCourceList(resutl);
                    that.controlLoadingBox(false);
                    callback && callback(resutl);
                },
                eCallback: function (data) {
                    $box.css('opacity','1');
                    var txt=data.txt;
                    if(data.code=404){
                        txt='信息加载失败';
                    }
                    that.controlLoadingBox(false);
                    that.showTips.call(that,txt);
                    $('#current-info .nodata').show();
                    callback && callback();
                },
            };
        this.getDataAsyncPy(para);
    };

    /*显示课程列表*/
    t.fillInCourceList=function(result){
        if(!result){
            this.showTips('参与活动课程加载失败');
            return;
        }
        if(result.total_count==0){
            $('.nodata').show();
            return;
        }
        var str='',
            data=result.data,
            len=data.length,
            item;
        for(var i=0;i<len;i++){
            item=data[i];
            var coupon=item.coupon_info;
            var obtainId=0,
                couponId= 0,
                courseId=item.course_id,
                rightStr='',
                marginRight=0;

            //有优惠券
            if(coupon) {
                if (coupon.is_obtain) {
                    obtainId = coupon.obtain_id;
                }
                couponId = coupon.id;
                rightStr=this.getRightStrAndMarginInfo(coupon);
                marginRight='80px';
            }
            str+='<li data-obtain-id="'+obtainId+'" data-coupon-id="'+couponId+'" data-course-id="'+courseId+'">'+
                        '<div class="item-main">'+
                            '<div class="main-content">'+
                                '<div class="middle" style="margin-right:'+marginRight+'">'+
                                    '<p class="title-info">'+item.course_name+'</p>'+
                                    '<p class="money-info">￥'+item.price+'</p>'+
                                    '<p class="time-info">'+this.getTimeFromTimestamp(item.start_course_time)+'开课</p>'+
                                '</div>'+
                            '</div>'+
                            '<div class="left">'+
                                '<div class="img-box">'+
                                    '<img src="'+item.cover_pic+'">'+
                                '</div>'+
                            '</div>'+
                            rightStr+
                        '</div>'+
                '</li>';
        }
        $('.list').html(str);
    };

    /*得到优惠券右边的信息*/
    t.getRightStrAndMarginInfo=function(coupon){
        var couponStatue=this.getCouponStatus(coupon),
            str='';
        if(couponStatue.type) {
           str ='<div class="right btn coupon '+couponStatue.type+'">' +
                '<div class="coupon-money">' +
                '<span>￥</span><span>' + coupon.money + '</span>' +
                '</div>' +
                '<div class="seperation"></div>' +
                '<div class="coupon-state">' +
                '<p>点击领取</p>' +
                '<p>已领取</p>' +
                '<p>已使用</p>' +
                '</div>' +
                '</div>';
        }
        return str;
    };

    /*通过优惠券的状态，得到相应的样式*/
    t.getCouponStatus=function(data){
        var temp={
            type:false,
            str:''
        };
        if(!data){
            return temp;
        }
        //data.is_obtain=true;
        //data.is_used=true;
        //data.is_out_of_date=false;
        var is_obtain=data.is_obtain,
            is_used=data.is_used,
            out_date=data.is_out_of_date;
        //未领取
        if(!is_obtain){
            temp.type='un-take-in';
        }else{
            if(out_date){
                temp.type=false;
            }else{
                if(is_used){
                    temp.type='used';
                }else {
                    temp.type = 'unused';
                }
            }
        }
        return temp;
    };

    /*优惠券操作*/
    t.operateCoupon=function(e){
        if(!this.userInfo.token || this.userInfo.name==this.staticUserNameStr){
            this.controlLoginTipModal(true);
            return;
        }
        var $target=$(e.currentTarget),
            $parent=$target.parents('li'),
            couponId=$parent.attr('data-coupon-id'),  //券id
            courceId=$parent.attr('data-course-id');  //课程id
        //未领取
        if($target.hasClass('un-take-in')){
            this.execTakeInCoupon($target,couponId,courceId);
            return;
        }

        //已经使用
        //未使用
        if($target.hasClass('unused') || $target.hasClass('used')){
            var obtainId=$parent.attr('data-obtain-id');  //领取的id
            if(!obtainId){
                this.showTips('您尚未领取该优惠券');
                return;
            }
            window.location.href='hisihi://coupon/detailinfo?id='+obtainId;
            return;
        }
    };

    /*
     * 领取优惠券
     * @para：
     * id - {string} 优惠券id
     * courceId - {string} 课程id
     * callback - {fn} 回调方法
     * */
    t.execTakeInCoupon=function($btn,id,courceId,callback){
        var $li = $btn.closest('li'),
            that = this,
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/user/coupons',
                type: 'post',
                paraData: JSON.stringify({teaching_course_id:courceId ,coupon_id: id}),
                needToken:true,
                token:this.userInfo.token,
                sCallback: function (result) {
                    if(result.has_obtained){
                        that.showTips.call(that,'您已经领过该优惠券');
                    }
                    $btn.removeClass('un-take-in').addClass('unused');
                    $li.attr('data-obtain-id',result.obtain_id);
                    callback && callback(result);
                },
                eCallback: function (data) {
                    var txt=data.txt;
                    //token  权限不足
                    if(data.code==1004){
                        that.controlLoginTipModal(true);
                        return;
                    }
                    //token  过期
                    if(data.code==1003){
                        that.loginAgainForOutDateToken();
                        return;
                    }
                    if(data.code==404){
                        txt='信息加载失败';
                    }
                    that.controlLoadingBox(false);
                    that.showTips.call(that,txt);
                    $('#current-info .nodata').show();
                    callback && callback();
                },
            };
        this.getDataAsyncPy(para);

    };

    /*显示详细课程信息*/
    t.goToCoursePage=function(e){
        var $target=$(e.currentTarget).closest('li'),
            id=$target.data('course-id');
        window.location.href='hisihi://techcourse/detailinfo?id='+id;
    };


    /*控制模态窗口的显示和隐藏*/
    t.controlLoginTipModal=function(flag){
        var $target=$('.model-box');
        if(flag==true){
            $target.show();
        }
        else{
            $target.hide();
        }
    };

    //隐藏登录提示框
    t.hideLoginTipBox=function(){
        this.controlLoginTipModal(false);
    };

    //登录
    t.doLogin=function(){
        if (this.isFromApp) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction != "undefined") {
                    AppFunction.login(); //显示app的登录方法，得到用户的基体信息
                }
            } else {
                //如果方法存在
                if (typeof showLoginView != "undefined") {
                    showLoginView();//调用app的方法，得到用户的基体信息
                }
            }
        }
        this.controlLoginTipModal(false);
    };

    /*token 过期时，重新调用app的方法，获得新的token*/
    t.loginAgainForOutDateToken=function(){
        if (this.isFromApp) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction != "undefined") {
                    AppFunction.login(); //显示app的登录方法，得到用户的基体信息
                }
            } else {
                //如果方法存在
                if (typeof userAuthInvaild != "undefined") {
                    userAuthInvaild();//调用app的方法，得到用户的基体信息
                }
            }
        }
    };


    /*
     *登录功能的回调方法
     *要做三件事：
     * 1，更新点赞 和点踩的信息
     * 2，收藏更新
     * 3，评论列表对应的点赞更新,将目前已经加载下来的评论重新加载。
     */
    window.loginSuccessCallback=function(){
        var obj=window.promotion;
        //得到用户基本信息
        obj.getUserInfo(null,1);
    };

    return Promotion;
});