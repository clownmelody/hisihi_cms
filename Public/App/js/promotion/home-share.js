/**
 * Created by jimmy on 2016/6/1.
 * 今天儿童节，儿童节快乐，武汉被大水淹了……
 */

define(['base','lazyloading','fastclick'],function(Base){
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
        $(document).on(eventName,'.model-box', $.proxy(this,'hideModel'));



        this.controlStyle();
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

    t.controlStyle=function(){
        var h = this.setFootStyle($('.wrapper'));
        $('.model-box img').css('bottom',h+15+'px');
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
            oid=$('body').data('oid'),
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/org/'+oid+'/promotion/'+this.pid+'/teaching_course',
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
            item,
            money='',
            stime='';
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
            money=this.judgeInfoNullInfo(item.price);
            if(money!=''){
                money='￥'+money;
            }else{
                money='<label class="noprice">暂无报价</label>';
            }
            stime=this.judgeInfoNullInfo(item.start_course_time);
            if(stime!=''){
                stime+='开课';
            }
            str+='<li data-obtain-id="'+obtainId+'" data-coupon-id="'+couponId+'" data-course-id="'+courseId+'">'+
                '<div class="item-main">'+
                '<div class="main-content">'+
                '<div class="middle" style="margin-right:'+marginRight+'">'+
                '<p class="title-info">'+item.course_name+'</p>'+
                '<p class="money-info">'+money+'</p>'+
                '<p class="time-info">'+stime+'</p>'+
                '</div>'+
                '</div>'+
                '<div class="left">'+
                '<div class="img-box">'+
                '<img data-original="'+item.cover_pic+'">'+
                '</div>'+
                '</div>'+
                rightStr+
                '</div>'+
                '</li>';
        }
        $('.list').html(str);
        $('.list img').picLazyLoad($('.wrapper'));
    };

    /*判断字段信息是否为空*/
    t.judgeInfoNullInfo=function(info){
        var str=info;
        if(typeof info=='string'){
            str=str.trim();
        }
        if(!info || info==''||info==0){
            str='';
        }
        return str;
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
    t.operateCoupon=function(){
        this.controlLoginTipModal(true);
    };

    t.hideModel=function(e){
        var $target=$(e.currentTarget);
        if($target.hasClass('btnElement')){
            return;
        }
        this.controlLoginTipModal(false);
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

    return Promotion;
});