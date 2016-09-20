/**
 * Created by hisihi on 2016/9/19.
 */

define(['base','async','fastclick'],function(Base,async){
    FastClick.attach(document.body);
    var Course=function(id,oid,url){
        this.cid = id;
        this.oid=oid;
        var eventName='click',that=this;
        this.baseUrl = url;
        if(this.isLocal){
            //eventName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        }
        this.controlLoadingBox(true);

        //window.setTimeout(function(){
        //    that.getUserInfo(null,1);  //0，表示不要令牌，1表示 基础令牌，其他表示普通用户令牌
        //    that.getBasicInfo.call(that,function(result){
        //        that.getOrgBasicInfo.call(that,result,function(resultOrg){
        //            that.getPromotionsInfo.call(that,result,resultOrg);
        //        });
        //    });
        //    that.getMoreCourseInfo();
        //},100);
        //加载页面数据
        window.setTimeout(function(){
            that.initData();
        },100);

        //领取优惠券
        $(document).on(eventName,'.coupon-right .coupon-status', $.proxy(this,'operateCoupon'));

        /*模态窗口操作*/
        $(document).on(eventName,'#do-login', $.proxy(this,'doLogin'));
        $(document).on(eventName,'#cancle-login', $.proxy(this,'hideLoginTipBox'));

    };

    Course.prototype=new Base();
    Course.constructor=Course;
    var t=Course.prototype;


    /*同步请求数据
    * 多层嵌套
    * async.js方法
    * */
    t.initData=function(){
        var that=this;
        async.parallel({
            basic: function (callback) {
                that.getBasicInfo(function (result){
                    if(!result) {
                        that.showTips.call(that,'信息加载失败');
                        that.controlLoadingBox(false);
                        return;
                    }
                    callback(null,result);
                });
            },
            orgBasic: function(callback) {
                that.getOrgBasicInfo(function (result){
                    callback(null,result);
                });
            },
            promotions: function(callback) {
                that.getPromotionsInfo(function (result){
                    callback(null,result);
                });
            },
            moreCourse: function(callback) {
                that.getMoreCourseInfo(function (){
                    callback(null.result);
                });
            },
        },function (results) {
            var val;
            for(var item in results) {
                var fn=null;
                val=results[item]
                switch (item){
                    case 'basic':
                        fn=that.getOrgInfoStr;
                        break;
                    case 'orgBasic':
                        fn=that.getBasicIntroduceInfo;
                        break;
                    case 'promotions':
                        fn=that.fillInCourseInfo;
                        break;
                    case 'moreCourse':
                        fn=that.fillInMoreCourseInfo;
                        break;
                };
            };
        });
    };


    //获得当前机构的基本信息
    t.getOrgBasicInfo=function(result,callback){
        //var that = this,
        //    para = {
        //        url: window.hisihiUrlObj.api_url + 'v1/org/'+this.oid+'/base',
        //        type: 'get',
        //        paraData: null,
        //        async:false,
        //        sCallback: function (orgResutl) {
        //            callback && callback(orgResutl);
        //        },
        //        eCallback: function (data) {
        //            var txt=data.txt;
        //            if(data.code=404){
        //                txt='信息加载失败';
        //            }
        //            that.controlLoadingBox(false);
        //            that.showTips.call(that,txt);
        //            $('#current-info .nodata').show();
        //            callback && callback();
        //        },
        //    };
        //this.getDataAsyncPy(para);


        var that=this,
            queryPara={
                url:this.baseUrl+'appGetBaseInfo',
                paraData:{organization_id:this.oid,version:2.95},
                sCallback:function(orgResutl){
                    callback && callback(orgResutl);
                },
                eCallback:function(){
                    ////电话号码
                    //$('.contact a').attr('href','javacript:void(0)').css('opacity','0.3');
                },
                type:'get',
                async:false
            };
        this.getDataAsync(queryPara);
    };

    //获得当前课程的详细信息
    t.getBasicInfo=function(callback){
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
    t.getMoreCourseInfo=function(callback){
        var paraData={
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
                    //var txt=data.txt,
                    //    $nodata=$('#more-info .nodata'),
                    //    $p=$nodata.find('p');
                    //if(data.code==404){
                    //    txt='信息加载失败';
                    //}
                    //if(data.code==1001){
                    //    txt='暂无推荐课程';
                    //}
                    //$p.text(txt);
                    //$nodata.show();
                    //that.controlLoadingBox(false);
                    //callback && callback();
                },
            };
        this.getDataAsyncPy(para);
    };

    //当前课程的详细信息显示
    t.fillInCourseInfo=function(result,orgResult,proResult){
        this.getBasicIntroduceInfo(result);
        this.getOrgInfoStr(orgResult);
        this.getCoupon(proResult);
        this.getIntroduceStr(result),
        this.getSingInStr(result);
    };

    //更多课程信息列表显示
    t.fillInMoreCourseInfo=function(result){
        var str=this.getMoreStr(result);
        if(''==str) {
            return;
        }
        $('#more-info').show();
        $('#more-info .lessons-more').show().find('ul').html(str);
        this.drawArrowColorBlock();
    };

    //课程简介
    t.getBasicIntroduceInfo=function(result){
        if(!result){
            return;
        }
        var money=result.price;
        if(money){
            money='￥'+money;
        }else{
            money='<label class="noprice">暂无报价</label>';
        }
        var str = '<div class="center-content">'+
                    '<div id="current-title">'+
                        result.course_name+
                    '</div>'+
                    '<div id="price" class="price">'+
                        money+
                    '</div>'+
                '</div>';
        $('.basic-info').html(str).show();
    };

    //机构信息
    t.getOrgInfoStr=function(result){
        if(!result || !result.data){
            return;
        }
        var data=result.data,
            name=data.name,
            logo=data.logo,
            str='',
            cerInfo=this.setCertInfo(data.authenticationInfo),
            vStyle='';
        if(cerInfo.v){
            vStyle='display:inline-block;';
        }
        //name=this.substrLongStr(name,10);
        if(!logo){
            logo='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png'
        }
        str = '<a href="hisihi://organization/detailinfo?id='+this.oid+'">'+
                    '<div class="center-content">'+
                        '<div class="left">'+
                            '<img class="group-logo" src="'+logo+'">'+
                            '<img class="v-cert" src="'+hisihiUrlObj.img_url+'/orgbasicinfo/2.9.5/ic_v@3x.png" style="'+vStyle+'">'+
                        '</div>'+
                        '<div class="right">'+
                            '<div class="name-cerbox">'+
                                '<div class="org-name">'+
                                    '<div class="name">'+name+'</div>'+
                                '</div>'+
                                '<span class="cert-box">'+cerInfo.str+'</span>'+

                                '<ul class="nums-info">'+
                                    '<li><span id="view-nums">'+this.transformNums(data.view_count) + '</span><span>查看</span></li>'+
                                    '<li><span id="view-watch">'+this.transformNums(data.followCount) + '</span><span>关注</span></li>'+
                                '</ul>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</a>';
        $('.org-basic-info').html(str).show();
    };

    //认证信息
    t.setCertInfo=function(authen){
        var obj={
            str:'',
            v:false
        };
        if(!authen || authen.length==0){
            return obj;
        }
        var str='',
            url='',
            len=authen.length,
            item;
        for(var i=0;i<len;i++){
            item=authen[i];
            if(item.default_display=='1') {
                //显示加v
                if (item.hisihi_add_v && item.hisihi_add_v == true) {
                    obj.v=true;
                    continue;
                }
                else {
                    //if (item.status) {
                    url = item.tag_pic_url
                    //} else {
                    //    url = item.disable_pic_url;
                    //}
                    str += '<img src="' + url + '">';
                }
            }
        }
        obj.str=str;
        return obj;
    };

    /*优惠券*/
    t.getCoupon=function(result){
        if(!result ||!result.data || result.data.length==0){
            return;
        }
        var str='',
            data=result.data[0],
            couponInfo=data.coupon_info,
            strAndType=this.getCouponState(couponInfo);
        if(strAndType.type===false){
            return'';
        }
        var startTime=this.getTimeFromTimestamp(couponInfo.start_time,'yyyy.MM.dd'),
            endTime=this.getTimeFromTimestamp(couponInfo.end_time,'yyyy.MM.dd'),
            className=strAndType.type;
        str = '<div class="center-content">'+
                '<div class="coupon-middle">'+
                    '<div class="coupon-middle-all">'+
                        '<div class="coupon-box">'+
                            '<div class="coupon-all-box '+className+'">'+
                                '<div class="coupon-main-top">'+
                                    '<span>￥</span>'+
                                    '<span>'+couponInfo.money+'</span>'+
                                '</div>'+
                                '<div class="coupon-main-bottom">'+
                                    '<span>有效期：'+'</span>'+'<span>'+startTime+'-'+endTime+'</span>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="coupon-left">'+
                    '<img id="coupon-icon" src="'+hisihiUrlObj.img_url+'/teaching-course/ic.png">' +
                '</div>'+
                '<div class="coupon-right">'+
                    '<div class="coupon-status '+className+'"></div>'+
                '</div>'+
            '</div>';
        $('.coupon-basic-info').html(str).show()
            .attr({'data-id':couponInfo.id,'data-oid':couponInfo.obtain_id});
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
        var is_obtain=data.is_obtain,
            is_used=data.is_used,
            out_date=data.is_out_of_date;

        if(out_date) {
            temp.type = false;
            return temp;
        }
        //未领取
        if(!is_obtain){
            temp.type='un-take-in';
        }else{
            if(is_used){
                temp.type='used';
            }else {
                temp.type = 'unused';
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
            id=$parent.attr('data-id'),
            that=this;
        //未领取
        if($target.hasClass('un-take-in')){
            this.execTakeInCoupon(id,function(result) {
                if (result !== false) {
                    window.location.href = 'hisihi://coupon/detailinfo?id=' + result.obtain_id;

                    //数据统计平台
                    that.updateStatisticsNum('coupon_obtain');
                    return;
                }
            });
            return;
        }

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
        this.showTipsNoHide('领取中…');
        var $btn=$('.coupon-status'),
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
                    that.hideTips();
                    callback && callback(result);
                },
                eCallback: function (data) {
                    that.hideTips();
                    var txt=data.txt;
                    if(data.code=404){
                        txt='信息加载失败';
                    }
                    that.controlLoadingBox(false);
                    that.showTips.call(that,txt);
                    $('#current-info .nodata').show();
                    callback && callback(false);
                },
            };
        this.getDataAsyncPy(para);

    };

    /*数值大于9999时，转换成万*/
    t.transformNums=function(num){
        num=parseInt(num);
        if(num>9999){
            var lessNum=num%10000;
            num= (num/10000);
            if(lessNum!=0){
                num=num.toString();
                var index=num.indexOf('.');
                num=num.substr(0,index+2);
                var arr=num.split('.');
                if(arr[1]==0){
                    num=arr[0];
                }
            }
            num+='万';
        }
        return num;
    };


    ///*得到认证的图片*/
    t.getCerImg=function(data){
        var str='<div class="img-box">',len=data.length;
        for(var i=0;i<len;i++){
            if(data[i].default_display) {
                //嘿和信认证logo图片
                //str += '<img src="' + data[i].tag_pic_url + '">';
            }
        }
        str +='</div>'
        return str;
    };

    //简介 和 安排信息
    t.getIntroduceStr=function(data){
        if(!data){
            return;
        }
        var sTime='',
            sTime1= this.judgeInfoNullInfo(data.start_course_time),
            sTime2= this.judgeInfoNullInfo(data.end_course_time),
            plan=data.plan,
            intro=data.introduction;
        sTime='<p>'+sTime1+'——'+sTime2+'</p>';
        if(!sTime1 && !sTime2){
            sTime='';
        }
        if(''!=intro) {
            var str1 = '<div class="center-content">' +
                            '<div class="lessons-item">' +
                                '<div class="head-txt">' +
                                    '<label>课程简介</label>' +
                                '</div>' +
                            '</div>' +
                            '<div class="content-txt">' +
                                '<p>' +
                                    intro +
                                '</p>' +
                            '</div>' +
                        '</div>';
            $('.lessons-detail').html(str1).show();
        }
        if(''!=plan) {
            var str2 = '<div class="center-content">' +
                            '<div class="head-txt">' +
                                '<label>课程安排</label>' +
                            '</div>' +
                            '<div class="content-txt">' +
                                sTime +
                                '<p>' +
                                    plan +
                                '</p>' +
                            '</div>' +
                        '</div>';
            $('.lessons-plan').html(str2).show();
        }
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
        if(!result){
            return;
        }
        var enrollArr=result.data,
            str='';
        if(enrollArr) {
            var len = enrollArr.length;
            if(len==0){
                return;
            }
            str ='<div class="head-txt">' +
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
            str += '</ul>';
            $('.lessons-sing-in').html(str).show();
        }

    };



    //更多
    t.getMoreStr=function(result){
        if(!result || !result.courses || result.courses.length == 0) {
            return '';
        }
        var courses=result.courses,
            len = courses.length,
            str='';

        for(var i=0;i<len;i++) {
            var item, courseName='', teacher='', sTeacher='', money='';
            item=courses[i];
            courseName=item.course_name;
            //teacher=this.judgeInfoNullInfo(item.lecture_name);
            //if(teacher!=''){
            //    sTeacher='<span>老师：'+teacher+'</span>';
            //}
            money=this.judgeInfoNullInfo(item.price);
            if(money){
                money='￥'+money;
            }else{
                money='<label class="noprice">暂无报价</label>';
            }
            var limitStr='';
            if(item.student_num!=0){
                limitStr ='<div class="singin-limit-nums">' +
                                '<div><canvas></canvas></div>'+
                                '<span>'+item.already_registered+'/'+item.student_num+'</span>' +
                            '</div>';
            }
            str += '<li class="normal">' +
                        '<a href="hisihi://techcourse/detailinfo?id='+item.id+'">' +
                            '<div class="main-content">'+
                                '<div class="left">' +
                                    '<img src="'+item.cover_pic+'">' +
                                '</div>' +
                                '<div class="right">' +
                                    '<div class="lesson-name">'+courseName+'</div>' +
                                    '<div class="right-item price">'+money+'</div>' +
                                    '<div class="lesson-view-info">' +
                                        this.getMiddleItemStr(item)+
                                    '</div>' +
                                '</div>' +
                                limitStr +
                            '</div>'+
                        '</a>'+
                    '</li>' +
                    '<li class="seperation"></li>';
        }
        return str;
    };

    t.getMiddleItemStr=function(item){
        var period=this.judgeInfoNullInfo(item.lesson_period),
            //num=this.judgeInfoNullInfo(item.student_num),
            teacher=this.judgeInfoNullInfo(item.lecture_name),
            stime=this.judgeInfoNullInfo(item.start_course_time),
            arr=[],
            str='';
        if(period!=''){
            arr.push(period+'次课');
        }
        if(stime!=''){
            arr.push(stime+'开课');
        }
        if(teacher!=''){
            arr.push('老师：'+teacher);
        }
        $.each(arr,function(){
            str+='<span>'+this+'</span>';
        });
        return str;
    };

    //绘制箭头
    t.drawArrowColorBlock=function () {
        var $canvas = $('.singin-limit-nums canvas'),
            lines = ["#000000", "#039BE5"];
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


    /**查看数和关注数人数超过一万的时候
     * 截取前一位，加上W单位
     * 参数说明：
     * 根据长度截取先使用字符串，超长部分追加W
     * str 对象字符串
     * len 目标字节长度
     * 返回值： 处理结果字符串
     */
    t.cutString=function(str,len){
        //length属性读出来的汉字长度为1
        if(str.length*4 <= len) {
            return str;
        }
        var strlen = 0;
        var s = "";
        for(var i = 0;i < str.length; i++) {
            s = s + str.charAt(i);
            if (str.charCodeAt(i) > 128) {
                strlen = strlen + 2;
                if(strlen >= len){
                    return s.substring(0,s.length-1) + "W";
                }
            } else {
                strlen = strlen + 1;
                if(strlen >= len){
                    return s.substring(0,s.length-2) + ".";
                }
            }
        }

        return s;
    }


    /*报名标签
    * 如果报名人数为0，则右侧的报名人数不显示*/
    t.ClassNum=function(info){
        var str=info;
        if(typeof info=='string'){
            str=str.trim();
        }
        if(!info || info==''||info==0){
            str='';
        }
        return str;
    };

    return Course;

});