/**
 * Created by jimmy on 2016/5/10.
 */

define(['base','fastclick'],function(Base){
    FastClick.attach(document.body);
    var Course=function(id,oid){
        this.cid = id;
        this.oid=oid;
        var eventName='click',that=this;
        if(this.deviceType.mobile && this.isLocal){
            eventName='touchend';
        }
        this.getUserInfo(null,1);  //0，表示不要令牌，1表示 基础令牌，其他表示普通用户令牌
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

    Course.prototype=new Base();
    Course.constructor=Course;
    var t=Course.prototype;


    //获得当前课程的详细信息
    t.getBasicInfo=function(callback){
        this.controlLoadingBox(true);
        var that = this,
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/org/teaching_course/'+this.cid+'/detail',
                type: 'get',
                async:false,
                paraData: null,
                needToken:true,
                sCallback: function (resutl) {
                    callback && callback(resutl);
                },
                eCallback: function (data) {
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

    //获得当前机构的基本信息
    t.getOrgBasicInfo=function(result,callback){
        var that = this,
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/org/'+this.oid+'/base',
                type: 'get',
                paraData: null,
                async:false,
                sCallback: function (orgResutl) {
                    callback && callback(orgResutl);
                },
                eCallback: function (data) {
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

    //获得当前课程的优惠券详细信息
    t.getPromotionsInfo=function(result1,resultOrg,callback){
        this.controlLoadingBox(true);
        var token=this.userInfo.token;
        if(!token){
            this.getBasicToken({account:'jg2rw2xVjyrgbrZp', secret: 'VbkzpPlZ6H4OvqJW',type:100},false,function(result){
                token=result;
            });
        }
        var that = this,
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/org/teaching_course/'+this.cid+'/promotions',
                type: 'get',
                paraData: null,
                //async:false,
                needToken:true,
                token:token,
                sCallback: function (resutlPro) {
                    that.controlLoadingBox(false);
                    that.fillInCourseInfo(result1,resultOrg,resutlPro);
                    callback && callback(resutlPro);
                },
                eCallback: function (data) {
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


    //获得更多课程的详细信息
    t.geMoreCourseInfo=function(callback){
        //this.controlLoadingBox(true);
        var paraData={
            //oid: this.oid,
            except_id: this.cid | 0,
            page: 1,
            per_page: 100000
        };
        var that = this,
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/org/'+this.oid+'/teaching_course',
                type: 'get',
                paraData: paraData,
                sCallback: function (resutl) {
                    that.controlLoadingBox(false);
                    that.fillInMoreCourseInfo(resutl);
                    callback && callback(data);
                },
                eCallback: function (data) {
                    var txt=data.txt,
                        $nodata=$('#more-info .nodata'),
                        $p=$nodata.find('p');
                    if(data.code==404){
                        txt='信息加载失败';
                    }
                    if(data.code==1001){
                        txt='暂无推荐课程';
                    }
                    $p.text(txt);
                    $nodata.show();
                    that.controlLoadingBox(false);
                    callback && callback();
                },
            };
        this.getDataAsyncPy(para);
    };

    //当前课程的详细信息显示
    t.fillInCourseInfo=function(result,orgResult,proResult){
        var strBasic=this.getBasicIntroduceInfo(result),
            strOrg=this.getOrgInfoStr(orgResult),
            strCoupon=this.getCoupon(proResult),
            strIntroduce=this.getIntroduceStr(result),
            strSingIn=this.getSingInStr(result);
        var str=strBasic+
            strOrg+
            strCoupon+
            strIntroduce+
            strSingIn;
        $('#current-info').html(str);
    };

    //更多课程信息列表显示
    t.fillInMoreCourseInfo=function(result){
        var str=this.getMoreStr(result);
        $('#more-info').html(str);
        this.drawArrowColorBlock();
    };



    //课程简介
    t.getBasicIntroduceInfo=function(result){
        return '<div class="main-item basic-info">'+
            '<div class="center-content">'+
            '<div class="head-txt" id="current-title">'+
            result.course_name+
            '</div>'+
            '<div id="price" class="price">￥'+
            result.price+
            '</div>'+
            '<ul class="otherinfo">'+
            '<li><i class="cer"></i><span>认证机构</span></li>'+
            '<li><i class="nums"></i><span><span id="singin-nums">'+result.already_registered+'</span>人报名</span></li>'+
            '</ul>'+
            '</div>'+
            '</div>';
    };

    //机构信息
    t.getOrgInfoStr=function(data){
        var name=data.name,logo=data.logo;
        name=this.substrLongStr(name,10);
        if(!logo){
            logo='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png'
        }
        return '<div class="main-item org-basic-info">'+
            '<a href="hisihi://organization/detailinfo?id='+this.oid+'">'+
            '<div class="center-content">'+
            '<div class="left">'+
            '<img src="'+logo+'">'+
            '</div>'+
            '<div class="right">'+
            '<div class="org-name">'+
            '<div class="name">'+name+'</div>'+
            this.getCerImg(data.auth)+
            '<div style="clear: both;"></div>'+
            '</div>'+
            '<ul class="nums-info">'+
            '<li><span id="view-nums">'+this.transformNums(data.view_count) + '</span><span>人查看</span></li>'+
            '<li><span id="singin-nums－org">'+this.transformNums(data.enroll_count) + '</span><span>人报名</span></li>'+
            '<li><span id="view-watch">'+this.transformNums(data.follow_count) + '</span><span>人关注</span></li>'+
            '</ul>'+
            '</div>'+
            '</a>'+
            '</div>'+
            '</div>';
    };

    /*优惠券*/
    t.getCoupon=function(result){
        var str='';
        if(!result ||!result.data || result.data.length==0){
            return str;
        }
        var data=result.data[0],
            couponInfo=data.coupon_info,
            promotionInfo=data.promotion_info,
            strAndType=this.getCouponState(couponInfo);
        if(strAndType.type===false){
            return'';
        }
        var startTime=this.getTimeFromTimestamp(couponInfo.start_time,'yyyy.MM.dd'),
            endTime=this.getTimeFromTimestamp(couponInfo.end_time,'yyyy.MM.dd'),
            className=strAndType.type;
        return '<div class="main-item coupon-basic-info" data-id="'+couponInfo.id+'" data-oid="'+couponInfo.obtain_id+'">'+
                   '<div class="center-content">'+
                    '<div class="coupon-middle">'+
                        '<div class="coupon-middle-all">'+
                            '<div class="coupon-box">'+
                                '<div class="coupon-all-box '+className+'">'+
                                    '<div class="coupon-main-top">'+
                                        '<span>￥</span>'+
                                        '<span>'+couponInfo.money+'</span>'+
                                    '</div>'+
                                    '<div class="coupon-main-bottom">'+
                                        '<span>有效期：'+'</span>'+
                                        '<span>'+startTime+'-'+endTime+'</span>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="sawtooth-left '+className+'"></div>'+
                            '<div class="sawtooth-right '+className+'"></div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="coupon-left">'+
                        '<img src="'+promotionInfo.little_logo_url+'">'+
                    '</div>'+
                    '<div class="coupon-right">'+
                        '<div class="sawtooth-right-main '+className+'">' +
                            strAndType.str+
                        '</div>'+
                        '<i class="'+className+'"></i>'+
                    '</div>'+
                '</div>'+
            '</div>';
    };

    /*通过优惠券的状态，得到相应的样式*/
    t.getCouponState=function(data){
        var temp={
            type:false,
            str:'<div class="do-take-in"><p>立即</p><p>领取</p></div><div class="postmark"></div><div class="to-used-btn">去使用</div>'
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
            //this.controlLoginTipModal(true);
            this.doLogin();
            return;
        }
        var $target=$(e.currentTarget),
            $parent=$target.parents('.coupon-basic-info'),
            id=$parent.attr('data-id');
        //未领取
        if($target.hasClass('un-take-in')){
            this.execTakeInCoupon(id);
            return;
        }

        //已经使用
        //if($target.hasClass('used')){
        //    return;
        //}
        //未使用
        if($target.hasClass('unused') || $target.hasClass('used')){
            var oid=$parent.attr('data-oid');
            if(oid=='undefined'){
                that.showTips('您尚未领取该优惠券');
                return;
            }
            window.location.href='hisihi://coupon/detailinfo?id='+oid;
            return;
        }
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

    /*
    * 领取优惠券
    * @para：
    * id - {string} 优惠券id
    * callback - {fn} 回调方法
    * */
    t.execTakeInCoupon=function(id,callback){
        var $btn=$('.sawtooth-right-main'),
            that = this,
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/user/coupons',
                type: 'post',
                paraData: JSON.stringify({teaching_course_id:this.cid ,coupon_id: id}),
                needToken:true,
                token:this.userInfo.token,
                sCallback: function (result) {
                    if(result.has_obtained){
                        that.showTips.call(that,'您已经领过该优惠券');
                    }
                    $btn.parents('.coupon-basic-info').attr('data-oid',result.obtain_id);
                    $btn.removeClass('un-take-in').addClass('unused');
                    callback && callback(result);
                },
                eCallback: function (data) {
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

    t.transformNums=function(num){
        num =Number(num);
        if(num){
            if(num>10000){
                num=num/10000 +'万'
                return num;
            }
        }else{
            num=0;
        }
        return num;
    };

    /*得到认证的图片*/
    t.getCerImg=function(data){
        var str='<div class="img-box">',len=data.length;
        for(var i=0;i<len;i++){
            if(data[i].default_display) {
                str += '<img src="' + data[i].tag_pic_url + '">';
            }
        }
        str +='</div>'
        return str;
    };

    //简介 和 安排信息
    t.getIntroduceStr=function(data){
        var sTime='',
            sTime1= this.judgeInfoNullInfo(data.start_course_time),
            sTime2= this.judgeInfoNullInfo(data.end_course_time),
            plan=data.plan,
            intro=data.introduction;
        if(sTime1 && sTime2){
            sTime='<p>'+sTime1+'——'+sTime2+'</p>';
        }
        if(plan==''){
            plan='<label class="no-result-data">暂无课程安排</label>';
        }
        if(intro==''){
            intro='<label class="no-result-data">暂无课程简介</label>';
        }
        return '<div class="main-item lessons-detail">'+
            '<div class="lessons-item">'+
            '<div class="head-txt">'+
            '<div class="center-content">课程简介</div>'+
            '</div>'+
            '<div class="content-txt center-content">'+
            '<p>'+
            intro+
            '</p>'+
            '</div>'+
            '</div>'+
            '</div>'+

            '<div class="main-item lessons-detail">'+
            '<div class="head-txt">'+
            '<div class="center-content">课程安排</div>'+
            '</div>'+
            '<div class="content-txt center-content">'+
                sTime+
            '<p>'+
                plan+
            '</p>'+
            '</div>'+
            '</div>';
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

    //报名信息
    t.getSingInStr=function(result){
        var enrollArr=result.data,
            str='';
        if(enrollArr) {
            var len = enrollArr.length;
            if(len==0){
                return str;
            }
            str = '<div class="main-item lessons-singin">' +
                '<div class="head-txt">' +
                '<div class="center-content"><span class="singin-nums">'+len+'</span>人报名</div>' +
                '</div>' +
                '<ul class="center-content">';
            for (var i = 0; i < len; i++) {
                var avatar = enrollArr[i].avatar;
                if(!avatar){
                    avatar='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
                }
                str += '<li><a href="hisihi://user/detailinfo?uid='+enrollArr[i].uid+'"><img src="'+avatar+'"></a></li>';
            }
            str += '</ul></div>';
        }
        return str;
    };

    //更多
    t.getMoreStr=function(result){
        var courses=result.courses,
            str='';
        if(courses) {
            var len = courses.length;
            if (len == 0) {
                return str;
            }
            var str = '<div class="main-item lessons-more">' +
                '<div class="head-txt">' +
                '<div class="center-content">' +
                '<span>机构其他套餐</span>' +
                '<i></i>' +
                '</div>' +
                '</div>' +
                '<ul>';
            for(var i=0;i<len;i++) {
                var item, courseName='', teacher='', sTeacher='', money='';
                item=courses[i];
                courseName=item.course_name;
                teacher=this.judgeInfoNullInfo(item.lecture_name);
                if(teacher!=''){
                    sTeacher='<span>老师：'+teacher+'</span>';
                }
                money=this.judgeInfoNullInfo(item.price);
                if(money!=''){
                    money='￥'+money;
                }else{
                    money='<label class="noprice">暂无报价</label>';
                }
                str += '<li class="normal">' +
                    '<a href="hisihi://techcourse/detailinfo?id='+item.id+'">' +
                    '<div class="main-content">'+
                    '<div class="left">' +
                    '<img src="'+item.cover_pic+'">' +
                    '</div>' +
                    '<div class="right">' +
                    '<div class="lesson-name">'+courseName+'</div>' +
                    '<div class="lesson-view-info">' +
                        this.getMiddleItemStr(item)+
                    '</div>' +
                    '<div class="teacher-info">' +
                    '<div class="left-item">' +
                    sTeacher+
                    '</div>' +
                    '<div class="right-item price">'+money+'</div>' +
                    '<div style="clear: both;"></div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="singin-limit-nums">' +
                    '<div><canvas></canvas></div>'+
                    '<span>'+item.already_registered+'/'+item.student_num+'</span>' +
                    '</div>' +
                    '</div>'+
                    '</a>'+
                    '</li>' +
                    '<li class="seperation"></li>';
            }
            str+='</ul></div>';
        }
        return str;
    };

    t.getMiddleItemStr=function(item){
        var period=this.judgeInfoNullInfo(item.lesson_period),
            num=this.judgeInfoNullInfo(item.student_num),
            stime=this.judgeInfoNullInfo(item.start_course_time),
            arr=[],
            str='';
        if(period!=''){
            arr.push(period+'次课');
        }
        if(num!=''){
            arr.push(num+'人班');
        }
        if(stime!=''){
            arr.push(stime+'开课');
        }
        $.each(arr,function(){
            str+='<span>'+this+'</span>';
        });
        return str;
    };

    //绘制箭头
    t.drawArrowColorBlock=function () {
        var $canvas = $('.singin-limit-nums canvas'),
            lines = ["#FF5A00", "#039BE5"];
        $canvas.each(function(){
            var canvas = $(this)[0];
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = lines[0];
            ctx.beginPath();
            ctx.moveTo(28, 0);
            ctx.lineTo(170, 0);
            ctx.lineTo(170, 65);
            ctx.lineTo(0, 65);
            ctx.closePath();
            ctx.fill();
        });
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

    /*
     *登录功能的回调方法
     *要做三件事：
     * 1，更新点赞 和点踩的信息
     * 2，收藏更新
     * 3，评论列表对应的点赞更新,将目前已经加载下来的评论重新加载。
     */
    window.loginSuccessCallback=function(){
        var obj=window.course;
        //得到用户基本信息
        obj.getUserInfo(null,1);
    };

    return Course;
});