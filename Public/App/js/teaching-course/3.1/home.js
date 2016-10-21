/**
 * Created by hisihi on 2016/9/19.
 */

define(['base','async','deduction','myPhotoSwipe','lazyloading','fastclick'],function(Base,async,Deduction,MyPhotoSwipe){
    var Course=function(id,oid,url){
        this.cid = id;
        this.oid=oid;
        var eventName='click',that=this;
        this.baseUrl = url;
        //判断是否是外链还是app内打开链接
        if(this.isLocal){
            //eventName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        }
        this.controlLoadingBox(true);
        //加载页面数据
        window.setTimeout(function(){
            that.initData();
        },100);

        $(document).on(eventName,'.sing-in-box .active', $.proxy(this,'singIn'));
        //预约
        $(document).on(eventName,'.sing-in,.appointment', $.proxy(this,'showSingInModal'));
        //关闭预约
        $(document).on(eventName,'.close-sing-in', $.proxy(this,'closeSingInBox'));
        //
        $(document).on('input','#user-name, #phone-num', $.proxy(this,'singInBtnControl'));

        /*模态窗口操作*/
        $(document).on(eventName,'#do-login', $.proxy(this,'doLogin'));
        $(document).on(eventName,'#cancle-login', $.proxy(this,'hideLoginTipBox'));

        $(document).on(eventName,'.deduction-main-info .btn', $.proxy(this,'buyNow'));

        $(document).on(eventName,'.download-app-modal', $.proxy(this,'cotrolDownloadAppModalStatus'));

        /*显示所有的学生作品*/
        $(document).on(eventName,'.show-all-works', $.proxy(this,'showAllWorks'));

        /*跳转老师详情页面*/
        $(document).on(eventName,'.teacher-info-main li', $.proxy(this,'showTeacherInfo'));

        this.initPhotoSwipe();
    };

    var tempFlag =window.location.href.indexOf('hisihi-app') < 0,  //是否来源于app
        config=null;

    if(tempFlag) {
        //下载条
        config = {
            downloadBar: {
                show: true,
                pos: 1
            }
        };
    }

    Course.prototype=new Base(config);
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
                that.getMoreCourseInfo(function (result){
                    callback(null,result);
                });
            },
            deduction:function(callback){
                that.getDecutionInfo(function(result){
                    callback(null,result);
                });
            },
            teacher:function(callback){
                that.getTeachersInfo(function(result){
                    callback(null,result);
                });
            },
            studentWorks: function(callback) {
                that.geStudentWorksInfo(function (result){
                    callback(null,result);
                });
            }
        },function (err,results) {
            var val;

            //此处做了两次循环，目的是确保basic的方法在deduction之前执行。因为basic异步方法执行时间最长，
            //在网速比较慢的情况下，会在deduction之后才执行，导致价格下的标签说明没有办法加载
            for(var item in results) {
                if ('basic'==item) {
                    that.getBasicIntroduceInfo(results[item]);
                    break;
                }
            }

            for(var item in results) {
                var fn = null;
                val = results[item];
                switch (item){
                    case 'orgBasic':
                        fn=that.getOrgInfoStr;
                        break;
                    case 'promotions':
                        fn=that.getCoupon;
                        break;
                    case 'moreCourse':
                        fn=that.fillInMoreCourseInfo;
                        break;
                    case 'deduction':
                        fn=that.fillInDedutionInfo;
                        break;
                    case 'studentWorks':
                        fn=that.fillInStudentWorksInfo;
                        break;
                    case 'teacher':
                        fn=that.fillInTeachersInfo;
                        break;
                    default :
                        fn=that.fillDetailCommentInfo;
                        break;
                }
                fn && fn.call(that,val);
            }
            $('#wrapper,#footer').show();
            that.controlLoadingBox(false);
            $('.lazy-img').picLazyLoad($(window),{
                threshold:0
            });
        });
    };

    /*初始化图片查看*/
    t.initPhotoSwipe=function(){
        //app 内部，由于安卓的滑动原因，调用原生来实现全图查看
        if(this.isFromApp && this.deviceType.android){
            this.arrWorksForAndroid=[];  //前8张作品地址
            $(document).on('click','.works-preview-ul li', $.proxy(this,'showOriginalWorks'));
            return;
        }
        new MyPhotoSwipe('.works-preview-ul');
    };


    //获得当前机构的基本信息
    t.getOrgBasicInfo=function(callback){
        var that=this,
            queryPara={
                url:this.baseUrl+'appGetBaseInfo',
                paraData:{organization_id:this.oid,version:2.95},
                sCallback:function(orgResutl){
                    callback && callback(orgResutl);
                },
                eCallback:function(){
                    ////电话号码
                    $('.contact a').attr('href','javacript:void(0)').css('opacity','0.3');
                    callback && callback(null);
                },
                type:'get',
            };
        this.getDataAsync(queryPara);
    };

    //获得当前课程的详细信息
    t.getBasicInfo=function(callback){
        var that = this,
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/org/teaching_course/'+this.cid+'/detail',
                type: 'get',
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
    t.getPromotionsInfo=function(callback){
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
                    callback && callback(resutl);
                },
                eCallback: function (data) {
                    callback && callback(null);
                },
                beforeSend:function(xhr){
                    xhr.setRequestHeader('version','3.02');  //设置头消息
                }
            };
        this.getDataAsyncPy(para);
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
            money='<span>￥</span><span>'+money+'</span>';
        }else{
            money='<label class="noprice">暂无报价</label>';
        }
        var str ='<div class="center-content">'+
                    '<div id="current-title">'+
                        result.course_name+
                    '</div>'+
                    '<div id="price" class="price">'+
                        money+
                    '</div>'+
                    '<div class="deduction-head-tips-box"></div>';
                '</div>';
        $('.basic-info').html(str).show();
        this.getIntroduceStr(result);

        //app 内不显示 banner
        if(!this.isFromApp) {
            var $banner=$('#banner').show(),
                width=$banner.width(),
                height= parseInt(width*7/16),
                url =result.cover_pic+'@'+width+'w';
            $banner.css('max-height',height).find('img').attr('src',url);
        }
    };

    //机构信息
    t.getOrgInfoStr=function(result){
        if(!result || !result.data) {
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


        //展示预约报名信息
        this.fillAppointmentInfo(result);
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


    //预约礼,判断是否支持试听，超出长度部分滚动显示
    t.fillAppointmentInfo=function(basicData){
        // false 0 null undefined
        var flag= parseInt(basicData.data.is_listen_preview) && basicData.data.listen_preview_text.length!=0;
        if (flag) {

            var str ='<div class="left-item"></div>' +
                        '<div class="middle-item">' +
                        '<p>'+
                        basicData.data.listen_preview_text +
                        '</p>'+
                        '</div>' +
                        '<div class="right-item"></div>';
            $('.appointment').show().html(str).css('height','44px');
        }
    };

    /*抵扣券*/
    t.getDecutionInfo=function(callback){
        var that=this,
            queryPara={
                url:this.baseUrl.replace('Organization','teaching_course')+'rebateAndTagInfo',
                paraData:{teaching_course_id:this.cid | 0},
                sCallback:function(result){
                    callback && callback(result);
                },
                eCallback:function(){
                    callback && callback(null);
                },
                type:'get',
            };
        this.getDataAsync(queryPara);
    };

    t.fillInDedutionInfo=function(result){
        if(!result || !result.data){
            return;
        }

        var data = result.data,
            ducutionInfo=data.rebate_info,
            packageId=data.gift_package_id;

        if(!ducutionInfo || !ducutionInfo.id || ducutionInfo.id=='') {
            return;
        }

        var  val= 0,rVal=0;
        if(ducutionInfo.value) {
            val = this.translationCount(ducutionInfo.value | 0 );
        }if(ducutionInfo.rebate_value) {
            rVal = this.translationCount(ducutionInfo.rebate_value | 0);
        }

        //如果有抵扣券信息，则在课程的 标题下 显示：线上支付xxx定金，抵扣xxxx元学费
        //如果有礼包信息，则显示：报名成功再送超值大礼包！
        var deductionTipsStr = '<p>线上支付'+val+'元定金，抵扣'+rVal+'元学费。';
        if(packageId && packageId!="0"){
            deductionTipsStr+='报名成功再送超值大礼包！';
        }
        deductionTipsStr+='</p>';
        $('.deduction-head-tips-box').html(deductionTipsStr).show();


        //抵扣券具体信息部分
        var $span=$('.deduction-main-info .val span');
        $span.eq(2).text(val).next().text(rVal);

        $('.deduction-basic-info').show();
        $('.deduction-main-info .diff-tips').text('学费直减'+rVal+'元！');

        //换购按钮和时间倒计时
        this.getDiffTimeForBuy(ducutionInfo.buy_end_time);


        // 使用须知
        var $buyItem=$('.buy-note-item'),
            timeInfo=this.getStimeAndEtime(ducutionInfo.use_start_time,ducutionInfo.use_end_time,true),
            useCondition=ducutionInfo.use_condition,
            useMethon=ducutionInfo.use_method;

        // 标签
        this.initTagsForDeduction(data);

        //有效期
        if(timeInfo!='') {
            $buyItem.eq(0).show().find('span').eq(1).text(timeInfo);
        }
        //使用条件
        if(useCondition!='') {
            $buyItem.eq(1).show().find('p').text(useCondition);
        }
        //使用方法
        if(useMethon!='') {
            $buyItem.eq(2).show().find('p').text(useMethon);
        }

    };

    t.getDiffTimeForBuy=function(endTime){
        endTime=this.getTimeFromTimestamp(endTime);
        console.log(endTime);
        endTime=new Date(endTime);


        var $right = $('.deduction-main-info .right-item'),
            $btn= $right.find('.btn'),
            $span= $right.find('span'),
            that=this;
        this.setTime(endTime,$btn,$span);
        this.myTime=window.setInterval(function(){
            that.setTime(endTime,$btn,$span);
        },1000);
    };

    //如果数字小于10 在前面添加 0
    t.addZeroToBefore=function(num){
        if(typeof num =='string'){
            num =num |0;
        }
        if(num<10){
            num='0'+num;
        }
        return num;
    };

    //倒数计时器
    t.setTime=function(eTime,$btn,$span){

        var now=new Date(),
            diff=eTime - now;

        //已经过期
        if(diff<0){
            window.clearInterval(this.myTime);
            $btn.addClass('disabled').text('抢购结束');
            $span.html('');
            return;
        }
        var minutes=60*1000,
            hours=60*minutes,
            days=24*hours,
            diffDay=diff/(days) | 0,  //天
            diffDay1=diff%(days),

            diffHours=diffDay1/hours | 0,
            diffHours1=diffDay1%hours,

            diffMinuetes=diffHours1/minutes | 0,
            diffMinuetes1=diffHours1%minutes,

            diffSeconds = diffMinuetes1/1000 | 0;

        var str='<span>'+diffDay + '天</span>'+
            '<span>'+this.addZeroToBefore(diffHours)+':'+this.addZeroToBefore(diffMinuetes)+':'+this.addZeroToBefore(diffSeconds)+'</span>';
        $span.html(str);
    };

    //抵扣券标签
    t.initTagsForDeduction=function(data){
        var tipsArr=data.course_tag_list;
        if(tipsArr && tipsArr.length>0){
            var $target=$('.deduction-tip').show(),
                options={
                    showDeductionTagsCallBack:null
                };

            if(this.isFromApp){
                if(this.deviceType.android){
                    //如果方法存在
                    if (typeof AppFunction != "undefined" && typeof AppFunction.showRebateInfoModal !='undefined') {
                        options.showDeductionTagsCallBack =function(){
                            AppFunction.showRebateInfoModal(JSON.stringify(tipsArr));
                        }
                    }
                }
                if(this.deviceType.ios && typeof  showRebateInfoModal !='undefined'){
                    options.showDeductionTagsCallBack = showRebateInfoModal;
                }

            }else{
                options.showDeductionTagsCallBack=null;
            }
            new Deduction(tipsArr, $target,options);
        }
    };


    /*主讲老师*/
    t.getTeachersInfo=function(callback){
        var that=this,
            queryPara={
                url:this.baseUrl.replace('Organization','teacher')+'getCourseTeacherList',
                paraData:{teaching_course_id:this.cid | 0},
                sCallback:function(result){
                    callback && callback(result);
                },
                eCallback:function(){
                    callback && callback(null);
                },
                type:'get',
            };
        this.getDataAsync(queryPara);
    };

    /*显示主讲老师信息*/
    t.fillInTeachersInfo=function(result){
        if(!result || !result.data || result.data.length==0){
            return;
        }
        var str1 = '<div class="center-content">' +
                        '<div class="lessons-item">' +
                            '<div class="head-txt">' +
                                '<label>主讲老师</label>' +
                            '</div>' +
                        '</div>' +
                        '<div class="teacher-info-main">' +
                            '<ul>'+this.getTeachersHtmlStr(result.data)+'</ul>'+
                        '</div>' +
                   '</div>';
        $('.lessons-teachers－box').html(str1).show();
    };

    /*拼接同，内容字符串*/
    t.getTeachersHtmlStr=function(data){
        var len=data.length,
            str='',
            pic,item,url='hisihi://orgteacher/detailinfo?id=';
        for(var i=0;i<len;i++){
            item=data[i];
            pic=item.avatar;
            str+='<li data-id="'+item.id+'" data-url="'+item.web_url+'" data-name="'+item.name+'">'+
                    '<div class="li-item-main">'+
                        '<div class="left-item">'+
                            '<img class="lazy-img works" data-original="'+pic+'">'+
                        '</div>'+
                        '<div class="right-item">'+
                            '<div class="name-info">'+
                                '<span>'+item.name+'</span>'+
                                '<span>'+item.title+'</span>'+
                            '</div>'+
                            '<div class="teacher-introduce">'+item.introduce+'</div>'+
                        '</div>'+
                    '</div>'+
                '</li>';
        }
        return str;
    };


    /*获取学生作品*/
    t.geStudentWorksInfo=function(callback){
        var that=this,
            queryPara={
                url:this.baseUrl.replace('Organization','teacher')+'getCourseStudentWorkList',
                paraData:{teaching_course_id:this.cid | 0,page:1,count:8},
                sCallback:function(result){
                    callback && callback(result);
                },
                eCallback:function(){
                    callback && callback(null);
                },
                type:'get',
            };
        this.getDataAsync(queryPara);
    };

    t.fillInStudentWorksInfo=function(result){
        //showStudentWorksList()
        //teacherDetailInfoPage(url,name)
        if(!result || !result.data){
            return;
        }
        var str1 = '<div class="center-content">' +
                        '<div class="lessons-item">' +
                            '<div class="head-txt">' +
                                '<label>学生作品</label>' +
                                '<div class="show-all-works"><i></i></div>'+
                            '</div>' +
                        '</div>' +
                        '<div class="works-preview-box">' +
                           '<ul class="works-preview-ul">'+this.getWorksHtmlStr(result.data)+'</ul>'+
                        '</div>' +
                '</div>';
        $('.works-box').html(str1).show();

    }

    t.getWorksHtmlStr=function(data){
        var len=data.length,
            str='',
            pic,item;
        for(var i=0;i<len;i++){
            item=data[i];
            pic=item.pic_url;
            str+='<li class="li-img" data-id="'+item.id+'">'+
                '<a href="'+pic +'" data-size="'+item.origin_info.width +'x'+item.origin_info.height+'"></a>'+
                '<img class="lazy-img works" data-original="'+pic+'@315w">'+
                '</li>';
        }
        return str;
    };

    /*调用android的大图片查看功能*/
    t.showOriginalWorks=function(e){
        var $li=$(e.currentTarget),
            index=$li.index(),
            $allLi=$li.parent().find('li'),
            that=this;
        if(this.arrWorksForAndroid.length==0) {
            $allLi.each(function () {
                that.arrWorksForAndroid.push($(this).find('a')[0].href);
            });
        }
        //如果方法存在
        if (typeof AppFunction != 'undefined' && typeof AppFunction.viewWorkInOriginSize != 'undefined') {
            AppFunction.viewWorkInOriginSize(that.arrWorksForAndroid,index); //显示学生作品 大图
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

    //显示预约试听
    t.showSingInModal=function(){
        if(this.isFromApp){
            if(this.deviceType.android){
                //如果方法存在
                if (typeof AppFunction != "undefined" && typeof AppFunction.showSingUpBox !='undefined') {
                    AppFunction.showSingUpBox();
                }
            }
            if(this.deviceType.ios && typeof  showSingUpBox !='undefined'){
                showSingUpBox();
            }
        }
    };

    t.singInBtnControl=function(e){
        var $target=$('.sing-in-item input'),
            txt1=$target.eq(0).val().trim(),
            $btn=$('.sing-in-btn'),
            nc='active';
        if(txt1){
            $btn.addClass(nc);
        }else{
            $btn.removeClass(nc);
        }
    };

    //预约报名
    t.singIn=function() {
        var $input =$('.sing-in-item input'),
            that=this,
            number = $input.eq(0).val().trim(),
            name = $input.eq(1).val().trim(),
            reg=/^1\d{10}$/;
        if (!reg.test(number)) {
            this.showTips('请正确输入手机号码');
            return;
        }
        this.controlLoadingBox(true);
        this.getDataAsync({
            url: this.baseUrl + 'yuyue/organization_id/'+this.oid+'/mobile/'+number+'/username/'+name,
            sCallback: function(result){
                that.controlLoadingBox(false);
                if(result.success){
                    $('.sing-in-modal .tips').css('opacity', '1');
                    that.showTips('预约成功');
                }else{
                    that.showTips('预约失败');
                }

            },
            eCallback:function(resutl){
                that.controlLoadingBox(false);
                var txt='预约失败';
                if(resutl.code==-2){
                    txt='不能重复预约';
                }
                that.showTips(txt);
            },
            type:'get'
        });
    };

    //取消预约
    t.closeSingInBox=function(){
        $('.sing-in-modal').removeClass('show');
        this.scrollControl(true);
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


    /*得到认证的图片*/
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
        var sTime=this.getStimeAndEtime(data.start_course_time,data.end_course_time),
            plan=data.plan,
            intro=data.introduction;

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
    };


    t.getStimeAndEtime=function(sTime,eTime,flag){
        if(flag==true) {
            if (sTime) {
                sTime = this.getTimeFromTimestamp(sTime, 'yyyy.MM.dd');
            }
            if (eTime) {
                eTime = this.getTimeFromTimestamp(eTime, 'yyyy.MM.dd');
            }
        }
       var sTime1= this.judgeInfoNullInfo(sTime),
           sTime2= this.judgeInfoNullInfo(eTime);
        sTime=sTime1+'——'+sTime2;
        if(!sTime1 && !sTime2){
            sTime='';
        }
        return sTime;
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

        //获取报名信息，数据类型为enroll_info
        var enrollArr=result.enroll_info.data,
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
            this.fillAppointmentInfo(basicData);
        }
    };


    /*课程列表*/
    t.getMoreStr=function(data){
        if(!data || !data.courses || data.courses.length==0){
            return '';
        }
        var list = data.courses,
            len = list.length,
            str = '',
            rightStr = '',
            count= 0,
            item;



        for(var i=0;i<len;i++){
            item=list[i];
            if(!item.rebate_info){
                continue;
            }
            rightStr=this.getRightStrAndMarginInfo(item.rebate_info);  //抵扣券信息

            count++;
            var money=this.judgeInfoNullInfo(item.price);
            if(money!=''){
                money='￥'+money;
            }else{
                money='<label class="noprice">暂无报价</label>';
            }
            var url='hisihi://techcourse/detailinfo?id='+item.id,
                attrType='data-original';
            if(!this.isFromApp){
                url=this.baseUrl + 'teaching_course_main_page_v3_02/course_id/' + item.id
            }
            //安卓滚动机制不一样，导致图片 懒惰加载有问题
            if(this.deviceType.android){
                attrType='src';
            }
            str+='<li data-course-id="'+item.id+'" class="normal">'+
                    '<a href="'+url+'">' +
                    '<div class="item-main">'+
                        '<div class="left">'+
                        '<div class="img-box">'+
                        '<img class="lazy-img" '+ attrType +'="'+item.cover_pic+'@66h_66w_2e">'+
                        '</div>'+
                        '</div>'+
                        '<div class="middle">'+
                        '<p class="title-info hasCoupon">'+item.course_name+'</p>'+
                        '<p class="money-info">'+money+'</p>'+
                        '</div>'+
                        rightStr+
                    '</div>'+
                    '</a>'+
                '</li>'+
                '<li class="seperation"></li>';
        }
        return str;
    };

    /*得到优惠券右边的信息*/
    t.getRightStrAndMarginInfo=function(deduction){
        var str='';
        if(deduction) {
            var rValue=this.translationCount(deduction.rebate_value),
                val=this.translationCount(deduction.value);
            str ='<div class="right">' +
                '<div class="right-main">'+
                '<div class="money">￥' + val +'</div>' +
                '<div class="deduction-money">抵￥'+ rValue +'</div>' +
                '</div>' +
                '</div>';
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

    //立即抢购
    t.buyNow=function(){
        if (this.isFromApp) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction !='undefined' &&  typeof AppFunction.buyRebate !='undefined') {
                    AppFunction.buyRebate(this.cid); //显示app的登录方法，得到用户的基体信息
                }
            } else {
                //如果方法存在
                if (typeof buyRebate != "undefined") {
                    buyRebate();//调用app的方法，得到用户的基体信息
                }
            }
        }else{
            this.cotrolDownloadAppModalStatus(true);
        }
    };

    t.cotrolDownloadAppModalStatus=function(flag){
        var $target=$('.download-app-modal');
        if(flag===true){
            $target.show();
        }else{
            $target.hide();
        }
    };

    /*显示所有的学生作品*/
    t.showAllWorks=function(){
        if (this.isFromApp) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction !='undefined' &&  typeof AppFunction.showStudentWorkList !='undefined') {
                    AppFunction.showStudentWorkList(); //显示所有的学生作品列表
                }
            } else {
                //如果方法存在
                if (typeof showStudentWorkList != "undefined") {
                    showStudentWorkList();//调用app的方法，得到用户的基体信息
                }
            }
        }else{
            this.showTips.call(this,'下载嘿设汇App，查看更多！');
        }
    };

    /*显示老师的详细信息页面*/
    t.showTeacherInfo=function(e){
        var $li=$(e.currentTarget),
            id=$li.attr('data-id');
        if (this.isFromApp) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction !='undefined' &&  typeof AppFunction.teacherDetailInfoPage !='undefined') {
                    AppFunction.teacherDetailInfoPage($li.attr('data-url'),$li.attr('data-name'));
                }
            } else {
                //如果方法存在
                if (typeof teacherDetailInfoPage != "undefined") {
                    teacherDetailInfoPage(id);//调用app的方法，得到用户的基体信息
                }
            }
        }else{
               window.location.href = this.baseUrl.replace('Organization','teacher')+'teacherv3_1/uid/'+id;
        }
    };

    return Course;

});