/**
 * Created by jimmy on 2015/12/28.
 */

define(['base','async','myPhotoSwipe','deduction','lazyloading'],function(Base,Async,MyPhotoSwipe,Deduction){

    function OrgBasicInfo($wrapper,oid,url) {
        this.$wrapper = $wrapper;
        var that = this;
        this.baseUrl = url;
        this.oid=oid;
        var eventName='click',that=this;
        if(this.isLocal){
            //eventName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        }

        this.perPageSize=10;
        this.pageIndex=1;
        this.async=true;  //同步加载所有的数据
        this.showGuaranteeBorder=false; //  是是否显示担保容器的边框
        this.controlLoadingBox(true);
        window.setTimeout(function(){
            that.initData();
        },100);


        //视频信息查看
        $(document).on(eventName,'.pics-preview-box .li-video',$.proxy(this,'showPicsAndVideoDetailInfo'));


        /*优惠券点击*/
        $(document).on(eventName,'.coupon-box li, .t-video-box li,.name-main-box img, .collection',function(){
            that.showTips('<p>请下载嘿设汇App</p>',3000);
            window.setTimeout(function(){
                window.location.href='http://www.hisihi.com/download.php';
            },3000);
        });


        //$(document).on(eventName,'.t-video-box li',$.proxy(this,'showTeachingVideo'));

        //关闭视频
        $(document).on(eventName,'.video-modal', function(){
            event.stopPropagation();
            if(event.target==this){
                $('.modal').removeClass('show');
                that.scrollControl(true);  //恢复滚动
                that.myPlayer.pause();
            }
        });

        $(document).on('input','#user-name, #phone-num', $.proxy(this,'singInBtnControl'));

        //显示预约报名框
        $(document).on(eventName,'.sing-in-box .active', $.proxy(this,'singIn'));

        //预约
        $(document).on(eventName,'.sing-in,.appointment', $.proxy(this,'showSingInModal'));

        //关闭预约
        $(document).on(eventName,'.close-sing-in', $.proxy(this,'closeSingInBox'));

        //显示所有的优惠课程
        $(document).on(eventName,'.show-more-course', $.proxy(this,'showAllLesson'));

        //photoswipe   //学生作品信息查看  相册、视频信息查看
        new MyPhotoSwipe('.works-preview-box',{
            bgFilter:true,
        });
    }

    //下载条
    var config={
        downloadBar:{
            show:true,
            pos:0
        }
    };


    OrgBasicInfo.prototype=new Base(config);
    OrgBasicInfo.constructor=OrgBasicInfo;
    var t=OrgBasicInfo.prototype;

    /*请求数据，多层嵌套，同步请求*/
    t.initData=function() {
        var that = this;
        Async.parallel({
            basic: function (callback) {
                that.loadBasicInfoData(function (result) {
                        if(!result){
                        that.showTips('机构不存在');
                        that.controlLoadingBox(false);
                        return;
                    }
                    callback(null, result);
                });
            },
            announcement: function (callback) {
                that.loadTopAnnouncement(function (result) {
                    callback(null, result);
                });
            },
            signUp:function(callback) {
                that.loadSignUpInfo(function(result){
                    callback(null,result)
                });
            },

            //课程
            course:function(callback){
                that.loadTeachingCourse(function(result){
                    callback(null,result);
                });
            },
            pics:function(callback){
                that.loadPics(function(result){
                    callback(null,result)
                });
            },
            video:function(callback) {
                that.loadVideo(function(result){
                    callback(null,result)
                });
            },
            teacher:function(callback) {
                that.loadMyTeachersInfo(function(result){
                    callback(null,result)
                });
            },
            teachingVideo:function(callback) {
                that.loadTeachingVideoInfo(function(result){
                    callback(null,result)
                });
            },
            works:function(callback) {
                that.loadWorksInfo(function(result){
                    callback(null,result)
                });
            },
            groups:function(callback) {
                that.loadGroupsInfo(function (result) {
                    callback(null, result)
                });
            },
            detailComment:function(callback) {
                that.loadDetailCommentInfo(1,function(result){
                    callback(null,result)
                });
            },
            //otherDetailComment:function(callback) {
            //    that.loadDetailCommentInfo(2,function(result){
            //        callback(null,result)
            //    },4);
            //},
        }, function (err, results) {
            var val;
            for(var item in results){
                var fn=null;
                val=results[item]
                switch (item){
                    case 'basic':
                        fn=that.fillInBasicInfoData;
                        break;
                    case 'announcement':
                        fn=that.fillInTopAnnouncement;
                        break;
                    case 'signUp':
                        fn=that.fillInSignUpInfo;
                        break;
                    case 'coupon':
                        fn=that.fillInCouponInfo;
                        break;
                    case 'course':
                        fn=that.fillInCourseInfo;
                        var aaaa='';
                        break;
                    case 'pics':
                        that.fillInPicsAndVideo([results['video']],val);
                        break;
                    case 'video':
                        //var str = that.getVideoStr([val]);  /*填充报名信息*/
                        //that.loadPics(str);
                        break;
                    case 'teacher':
                        fn = that.fillMyTeachersInfo;  /*填充老师信息*/
                        break;
                    case 'teachingVideo':
                        that.fillInTeachingVideo(val,results['basic']);
                        break;
                    case 'works':
                        fn=that.fillWorksInfo;
                        break;
                    case 'groups':
                        fn=that.filInGroupsInfo;
                        break;
                    case 'detailComment':
                        that.fillDetailCommentInfo(val);
                        break;
                    default :

                        break;
                }
                fn && fn.call(that,val);
            }
            $('#wrapper,#footer').show();
            that.controlLoadingBox(false);
            //that.initVideoPlayer();
            $('.lazy-img').picLazyLoad($(window),{
                threshold:150
            });
        });
    };

    /*播放器控制*/
    t.initVideoPlayer=function(){
        var that=this;
        videojs("video-player").ready(function() {
            that.myPlayer = this;
        });
    };

    /*
    *播放器地址控制
    * url -{string} 视频地址，类似 http://91.16.0.7/video/14/output.m3u8
    *
    * */
    t.resetVideoPlayerUrl=function(url,poster){
        if(!this.myPlayer){
            this.initVideoPlayer();
        }
        this.myPlayer.src({type: 'application/x-mpegURL',src:url});
        if(!poster){
            poster='http://pic.hisihi.com/hisihi_home_pic/video_cover.png';
        }
        this.myPlayer.poster(poster);
        $('.modal').eq(0).addClass('show');
    };

    /*加载基本信息*/
    t.loadBasicInfoData=function(callback) {
        var that=this,
            $target=that.$wrapper.find('.logoAndCertInfo'),
            queryPara={
                url:this.baseUrl+'appGetBaseInfo',
                paraData:{organization_id:this.oid,version:3.02},
                sCallback:function(restult){
                    callback && callback(restult);
                },
                eCallback:function(){
                    //电话号码
                    $('.contact a').attr('href','javacript:void(0)').css('opacity','0.3');
                    callback && callback(null);
                },
                type:'get',
                async:this.async

            };
        this.getDataAsync(queryPara);
    };



    /*显示具体信息*/
    t.fillInBasicInfoData=function(result) {
        $('.logo-box').show();
        var data = result.data, url = data.logo;
        if (this.deviceType.android) {
            url = window.hisihiUrlObj.image + '/orgbasicinfo/blur.jpg';
        }
        var name = data.name;
        if (name.length > 10) {
            name = name.substr(0, 9) + ' …';
        }
        var $box = $('.logo-box');
        //logo
        $box.find('#filter-logo').attr('src', url);
        $box.find('#logo').attr('src', data.logo);

        // 视频、名称、认证
        $box.find('.name-main-box .org-name').text(data.name);
        this.setCertInfo(data.authenticationInfo);

        // 粉丝和观看人数
        var $people = $box.find('.user-number-box label')
        $people.eq(0).text(this.translationCount(data.view_count));
        $people.eq(1).text(this.translationCount(data.followCount));

        //基本信息
        if (data.introduce) {
            $('.my-introduce').text(data.introduce).parent().show();
        }
        if (data.location) {
            $('.my-location').text(data.location).parent().show();
        }
        /*优势标签*/
        if (data.advantage) {
            var arr = data.advantage.split('#'),
                str = '';
            for (var i = 0; i < arr.length; i++) {
                str += '<li>' + arr[i] + '</li>';
            }
            $('.my-tags').html(str).parent().show();
        }

        //电话号码
        $('.contact a').attr('href', 'tel:' + data.phone_num);


        this.fillAppointmentInfo(result);

        // 抵扣券 标签
        this.fillInDeductionTags(result);

    };


    //认证信息
    t.setCertInfo=function(authen){
        if(!authen || authen.length==0){
            return;
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
                    $('.v-cert').show();
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
        $('.cert-box').html(str);
    };


    //抵扣券信息
    t.loadCouponInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + '/v1/org/41/teaching_course',
            paraData: {organization_id: this.oid, version: 2.95},
            sCallback: function(result){
                callback && callback(result);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    t.fillInCouponInfo=function(data){
        if(!data || data.list.length==0){
            return;
        }
        var list=data.list,
            len=list.length,
            str='',coupon;

        for(var i=0;i<len;i++){
            coupon=list[i];
            if(coupon.is_out_of_date){
                continue;
            }
            str+='<li>'+
                    '<div class="coupon-header">￥'+coupon.money+'</div>'+
                    '<div class="coupon-bottom">'+
                        '<p>适用于：'+coupon.course_name+'</p>'+
                    '</div>'+
                 '</li>'
        }
        $('.coupon-box').show().find('ul').html(str);
    };

    /*课程列表*/
    t.fillInCourseInfo=function(data){
        if(!data || !data.courses || data.courses.length==0){
            return;
        }
        var list = data.courses,
            len = list.length,
            str = '',
            rightStr = '',
            count= 0,
            item;



        for(var i=0;i<len;i++){
            item=list[i];
            rightStr=this.getRightStrAndMarginInfo(item.rebate_info);  //抵扣券信息
            count++;
            var money=this.judgeInfoNullInfo(item.price);
            if(money!=''){
                money='￥'+money;
            }else{
                money='<label class="noprice">暂无报价</label>';
            }
            str+='<li data-course-id="'+item.id+'">'+
                    //'<a href="hisihi://techcourse/detailinfo?id='+item.id+'">' +
                    '<a href="' + this.baseUrl + 'teaching_course_main_page_v3_02/course_id/' + item.id + '">'+
                        '<div class="item-main">'+
                            '<div class="left">'+
                                '<div class="img-box">'+
                                    '<img class="lazy-img" data-original="'+item.cover_pic+'@66h_66w_2e">'+
                                '</div>'+
                            '</div>'+
                            '<div class="middle">'+
                                '<p class="title-info hasCoupon">'+item.course_name+'</p>'+
                                '<p class="money-info">'+money+'</p>'+
                            '</div>'+
                            rightStr+
                        '</div>'+
                    '</a>'+
                '</li>';
        }
        if(str) {
            $('.deduction-detail').find('ul').html(str);
            $('.coupon').show();
            var diff=count- 2,
                height=90 + 15,
                $detail=$('.deduction-detail');
            if(diff>0){
                height=2 * 90 + 35;
                var className='slow';
                if(diff>8){
                    className='fast';
                }
                if(diff>3 && diff<=8){
                    className='normal';
                }
                $('.show-more-course').show().find('span').eq(0).text('查看其他'+ diff +'个优惠课程');
            }
            if(diff==0){
                height=2 * 90 + 15;
            }
            $detail.addClass(className).css('height',height).attr({'data-height':height,'data-diff':diff});
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

    /*加载头条信息*/
    t.loadTopAnnouncement=function(callback){
        var that=this;
        this.getDataAsyncPy({
            url:window.hisihiUrlObj.apiUrlPy+'v1/org/' +this.oid +'/news',
            sCallback: function(result){
                callback && callback(result);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async

        });
    };

    /*填充头条信息*/
    t.fillInTopAnnouncement=function(result){
        if(!result || !result.news || result.news.length==0){
            return;
        }
        var str='',
            item,
            data=result.news,
            $target=$('.news-box');
        $target.show();
        var len = data.length;
        for (var i = 0; i < len; i++) {
            if(i<2) {
                item = data[i];
                str += '<a href="' + item.url + '"><p>' + item.title + '</p></a>';
            }
        }
        $target.find('.right-item div').html(str);
    };

    //滚动
    t.scrollSingInInfo=function(){
        var $target=$('.sing-up-detail-box'),
            $item=$target.find('p'),
            h=40,
            len=$item.length,
            ty= -h+'px';
        var style={
            'margin-top':ty,
            '-webkit-transform':'translate3d(0,0,0)',
            '-moz-transform':'translate3d(0,0,0)',
            'opacity':'0.7'
        };
        window.setInterval(function(){
            $target.animate(
                style,
                500,'ease-out',
                function(){
                    $target.find('p').eq(0).appendTo($target);
                    $target.css({marginTop:0,'opacity':'1'});
                }
            );

        },2500);
    };

    /*加载报名信息*/
    t.loadSignUpInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url:this.baseUrl + 'enrollList',
            paraData: {organization_id: this.oid,type:'all'},
            sCallback: function(result){
                callback && callback(result.data);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    /*填充报名信息*/
    t.fillInSignUpInfo=function(data){
        var str='',item,
            flag=!data || data.length==0;
        //没有人报名
        if(flag) {
            return;
        }
        var len = data.length;
        for (var i = 0; i < len; i++) {
            item = data[i];
            var time = new Date(item.create_time * 1000).format('yyyy-MM-dd');
            str += '<p><span>' + item.student_name + '</span>同学 于 ' + time + ' 成功报名</p>';
        }

        $('.sing-up-main').show().find('.sing-up-detail-box').html(str);

        //显示担保边框
        if(this.showGuaranteeBorder){
            $('.sing-up-guarantee').addClass('border');
        }

        // 如果记录人数超过1条，则使用滚动显示的方式
        if (data.length > 1) {
            this.scrollSingInInfo();
        }
    };

    /*获得抵扣券使用流程信息*/
    t.fillInDeductionTags=function(result){
        if(!result || !result.data){
            return;
        }
        var tipsArr=result.data.teaching_course_tag_list;
        if(tipsArr.length>0) {
            var $target=$('.deduction-tip').show();
            new Deduction(tipsArr, $target);
        }

    };

    /*课程信息*/
    t.loadTeachingCourse=function(callback){
        var that=this;
        this.getDataAsyncPy({
            url: window.hisihiUrlObj.apiUrlPy+'/v1/org/'+this.oid+'/teaching_course',
            paraData: {
                page:1,
                per_page:100000,
                except_id:''
            },
            sCallback: function(result){
              callback && callback(result);
            },
            eCallback:function(){
                callback && callback(null);
            },
            type:'get',
            beforeSend:function(xhr){
                xhr.setRequestHeader('version','3.02');  //设置头消息
            }
        });
    },

    /*加载视频*/
    t.loadVideo=function(callback){
        var that=this;
        this.getDataAsync({
            url:this.baseUrl + 'getPropagandaVideo',
            paraData: {organization_id: this.oid,count:8},
            sCallback: function(result){
                callback && callback([result.data]);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    /*加载相册*/
    t.loadPics=function(callback){
        var that=this;
        this.getDataAsync({
            url:this.baseUrl + 'appGetOrganizationEnvironment',
            paraData: {organization_id: this.oid,count:8},
            sCallback: function(result){
                callback && callback(result);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    t.fillInPicsAndVideo=function(data1,data2){
        var str = this.getVideoStr(data1);  /*填充报名信息*/
        var newStr = this.getPicsStr(data2.data,true),
            allStr=str;
        if(''!=newStr) {
            newStr = '<ul class="works-preview-box">'+newStr+'</ul>';
        }
        allStr=str + newStr;
        if(allStr!='') {
            $('.pics-box').show().find('.pics-preview-box ul')
                .html(allStr);
        }
    },

    /*
    * 相册
    * para:
    * data - {arr} 图片信息数组
    * type - {bool} true 表示是  相册 模块调用；false 表示是  作品 模块调用
    */
    t.getPicsStr=function(data,type){
        var $label=$('.pics-number label').eq(1);
        if(!data || data.length==0){
            return '';
        }
        var len=data.length,str='',
            pic,thumb,item;
        type && $label.text(len+'照片').css('display','inline');
        for(var i=0;i<len;i++){
            item=data[i];
            pic=item.picture;
            thumb=item.thumb
            str+='<li class="li-img" data-id="'+item.id+'">'+
                    '<a href="'+pic.url +'" data-size="'+pic.size.width +'x'+pic.size.height+'"></a>'+
                    '<img class="lazy-img works" data-original="'+thumb.url+'">'+
                '</li>';
        }
        return str;
    };

    /*
    * 视频
    * para:
    * data- {arr} 视频数据列表
    * type - {bool} 是否计划视频列表的高度，教学视频时需要计算
    */
    t.getVideoStr=function(data,type){
        if(!data || data.length==0){
            return;
        }
        var len=data.length,
            str='',
            item,
            style='',
            $label=$('.pics-number label').eq(0),
            className='';
        if(type){
            var h=$('body').width()*7/16;
            style='height:'+h+'px;';
            $('.name-main-box img').css('display','inline-block');
        }else{
            if(!data[0].video_url) {
                $label.hide();
                return '';
            }
            $label.text(len+'视频').show();
            className='li-video';
        }

        for(var i=0;i<len;i++) {
            item=data[i];
            item.video_img = item.video_img || item.img || '';
            item.video_url = item.video_url || '';
            //item.video_url = 'http://91.16.0.7/video/14/output.m3u8';
            str+= '<li data-url="' + item.video_url + '" style="'+style+'" class="'+className+'">' +
                '<img class="lazy-img" data-original="' + item.video_img + '">' +
                '<span class="p-btn"><i class="icon-play"></i></span>' +
                '</li>';
        }
        return str;
    };

    /*播放视频详细信息*/
    t.showPicsAndVideoDetailInfo=function(e){
        var $target=$(e.currentTarget),
            url=$target.data('url');
        if(!url){
            this.showTips('视频暂无');
            return;
        }
        this.resetVideoPlayerUrl($target.data('url'),$target.find('img').attr('src'));

        this.scrollControl(false);  //禁止滚动
    };


    /*加载我的老师信息*/
    t.loadMyTeachersInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'appGetTeacherList',
            paraData: {organization_id: this.oid,version:'3.1',page:1,count:10},
            sCallback: function(result){
                //that.fillMyTeachersInfo(result.teacherList);
                callback && callback(result.teacherList);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    /*填充我的老师信息*/
    t.fillMyTeachersInfo=function(data){
        var str='',itemInfo;
        if(!data || data.length==0){
            return;
        }
        else {
            var len = data.length,tempStr='',id;
            for (var i = 0; i < len; i++) {
                itemInfo = data[i];
                id=itemInfo.uid;
                tempStr='<a target="_blank" href="'+this.baseUrl.replace('Organization','Teacher')+'teacherv3_1/uid/'+itemInfo.id+'"><img src="'+itemInfo.avatar+'"><p>' + itemInfo.name + '</p></a>';
                //if(id == null || id=='0'){
                //    tempStr=tempStr.replace(/<a[^<]*>/,'').replace(/<\/a>/,'');
                //}
                str+='<li>'+tempStr+'</li>';
            }
        }
        $('.teachers-box').show();
        $('.teachers-detail').html(str);
    }

    /*加载我的视频教程信息*/
    t.loadTeachingVideoInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'appGetCoursesList',
            paraData: {organization_id: this.oid},
            sCallback: function(result){

                callback && callback(result);
            },
            eCallback:function(txt){
                $target.css('opacity',1);
                $target.find('.loadErrorCon').show().find('a').text('获取视频信息失败，，点击重新加载').show();
                callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    t.fillInTeachingVideo=function(data,basicData){
        if(!data || data.coursesList.length==0){
            return;
        }
        var $box =  $('.t-video-box').show();
        var str = this.getVideoStr(data.coursesList,true);
        $box.find('.basic-header span').html('('+data.coursesList.length+')');
        $box.find('ul').html(str);

    };

    //预约礼,判断是否支持试听，超出长度部分滚动显示
    t.fillAppointmentInfo=function(basicData){
        // false 0 null undefined
        var flag= parseInt(basicData.data.is_listen_preview) && basicData.data.listen_preview_text.length!=0;
        if (flag) {

            var str = '<div class="left-item"></div>' +
                '<div class="middle-item">' +
                '<p>'+
                basicData.data.listen_preview_text +
                '</p>'+
                '</div>' +
                '<div class="right-item"></div>';
            $('.appointment').show().html(str).css('height','44px');
        }
    };

    /*播放教学视频*/
    t.showTeachingVideo=function(e){
        this.showTips('下载App');
    };



    /*加载学生作品*/
    t.loadWorksInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'appGetStudentWorks',
            paraData: {organization_id: this.oid,count:8,version:2.9},
            sCallback: function(result){
                callback && callback(result);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    t.fillWorksInfo=function(result){
        if(!result || !result.data || result.data.length==0){
            return;
        }
        var $box =  $('.works-box').show(),
            data=result.data,
            len=data.length;
        var str = this.getPicsStr(result.data,false);
        $box.find('ul').html(str);
    };

    /*加载群组*/
    t.loadGroupsInfo=function(callback){
        var that=this;
        this.getDataAsyncPy({
            url: window.hisihiUrlObj.apiUrlPy+'/v1/im/org/' +this.oid +'/groups',
            paraData: {per_page:1},
            sCallback: function(result){
                callback && callback(result)
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    t.filInGroupsInfo=function(result){
        if(!result || result.data.length==0){
            return;
        }
        var $box =  $('.groups-box').show(),
            data=result.data[0],
            str='<div class="left">'+
                    '<img src="' + data.group_avatar + '">'+
                '</div>'+
                '<div class="right">'+
                    '<p>' + data.group_name + '</p>'+
                '</div>';
        $box.find('.groups-detail-box').html(str);
    };


    /*加载我的评论信息*/
    t.loadDetailCommentInfo=function(type,callback,perCount){
        if(!perCount){
            perCount=this.perPageSize;
        }
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'commentList',
            paraData: {organization_id: this.oid,page:1,count:perCount,comment_type:type,version:'3.1'},
            sCallback: function(result){
                callback&&callback.call(that,result);
            },
            eCallback:function(txt){
                callback&&callback.call(that,null);
            },
            type:'get',
            async:this.async
        });
    };

    /*填充评论信息,包括app评论、网络评论*/
    t.fillDetailCommentInfo=function(result){
        var totalCount = (result.totalCount | 0) + (result.totalCount | 0);
        $('#commentNum').text(totalCount);
        if(totalCount==0){
            $('.studentCommentDetail .nodata').show();
            return;
        }
        var data=result.data,

            str='',
            len = data == null ? 0 : data.length,
            item,
            userInfo,
            dateTime,
            chooseReasonObj=null,
            commentPicsObj=null,
            commentTxt='';

        //app用户评论
        for (var i = 0; i < len; i++) {
            item = data[i];
            userInfo = item.userInfo;
            dateTime = this.getDiffTime(item.create_time,true);   //得到发表时间距现在的时间差
            if(dateTime.indexOf('-')>=0){
                dateTime=dateTime.split(' ')[0];
            }
            chooseReasonObj=this.getChooseReaons(item);  //选择理由
            commentPicsObj=this.getCommentPics(item.pic_info);  //评论图片
            commentTxt=item.comment || '';
            if(chooseReasonObj.flag && (commentPicsObj.flag || commentTxt!=='')){
                chooseReasonObj.str=chooseReasonObj.str.replace(/no-border/,'border-box');
            }
            str += '<li>' +
                    '<div class="comment-box-head">'+
                        '<div class="user-img-box">' +
                            '<img src="' + userInfo.avatar128 + '"/>' +
                        '</div>' +
                        '<div class="user-name-box">' +
                            '<div class="commentNickname">' + userInfo.nickname + '</div>' +
                            '<div class="publicTime">' + dateTime + '</div>' +
                        '</div>' +
                        '<div class="user-star-box">' +
                            '<span class="rightItem starsCon">' +
                                this.getStarInfoByScore(item.comprehensive_score | 0) +
                            '</span>' +
                            '<div class="total-comment-count">'+this.getChildrenCommentCounts(item.childCommentCount)+'</div>'+
                        '</div>' +
                    '</div>'+
                    '<div class="commentCon">' +
                        chooseReasonObj.str+
                        '<p class="content">' + commentTxt + '</p>' +
                        commentPicsObj.str+
                    '</div>' +
                    '<div class="border-box"></div>'+
                    '</li>';
        }

        $('.studentCommentDetail').append(str);

        //var netStr = this.getNetComment(result2);
        //if(netStr!='') {
        //    $('.net-comment-box').show();
        //    $('.net-comment-box-detail').append(netStr);
        //}
    };

    t.getImgSize=function(src){
        src=src.replace(/@.*/,'')+'@info';
        $.get(src,null,function(data){
            alert(data);
        });
    };

    //子评论条数，超过万则用万作单位
    t.getChildrenCommentCounts=function(num){
        num =num | 0;
        if(num>0){
            num = this.translationCount(num);
            return '共有'+num+'条评论';
        }
        return '';
    };

    /*
    * 评论选择原因，老师质量等
    * para:
    * data - {obj} 信息对象
    * return
    * flag- {bool} 表示 选择原来 和教学质量等信息是否为空
    * str - {string} 内容字符串
    */
    t.getChooseReaons=function(data){
        var tempArr=[
                {key:'选择原因',val:data.choose_reason},
                {key:'教学师资',val:data.teaching_group},
                {key:'教学质量',val:data.teaching_quality},
                {key:'教学环境',val:data.teaching_env},
                {key:'就业服务',val:data.employ_service},
            ],
            str='',
            flag=false,
            item;
        for(var i in tempArr){
            item=tempArr[i];
            if(item.val){
                flag=true;
                str+='<div class="comment-reason-item"><span class="field-name">'+item.key+'</span><span class="field-val">'+item.val+'</span></div>'
            }
        }
        return {
            flag:flag,
            str:'<div class="comment-reason-box">'+str+'<div class="no-border"></div></div>'
        }
    };

    /*
    * 评论图片
    * para:
    * picInfo - {obj} 信息对象
    * return
    * flag- {bool} 表示 picInfo信息是否为空
    * str - {string} 内容字符串
    */
    t.getCommentPics=function(picInfo){
        var str='',
            flag=false;
        if(picInfo){
            var item,
                len=picInfo.length,
                src,
                w=($('body').width() * 0.33) | 0 + 'px',
                style='height:'+w+'px';
            if(len>0){
                flag=true;
            }
            for(var i=0;i<len;i++){
                //item = picInfo[i];
                src = picInfo[i];
                //this.getImgSize(src);
                str+='<li class="li-img" style="' + style + '">'+
                        //'<a href="'+ src +'" data-size="'+item.src_size[0] +'x'+item.src_size[1]+'"></a>'+
                        '<img class="lazy-img works" data-original="'+ src +'">'+
                    '</li>';
            }
            str ='<ul class="comment-img-list works-preview-box">' + str +'</ul>';
        }
        return{
            str:str,
            flag:flag
        };
    };

    /*
     * 互联网评论
     * para:
     * result - {obj} 信息对象
     * return
     * str - {string} 内容字符串
     */
    t.getNetComment=function(result){
        var data=result.data,
            len = data == null ? 0:data.length,
            item,
            userInfo,
            dateTime,
            str='';

        //网络评论
        for (var i = 0; i < len; i++) {
            item = data[i];
            userInfo = item.userInfo;
            dateTime = this.getDiffTime(item.create_time,true);   //得到发表时间距现在的时间差
            if(dateTime.indexOf('-')>=0){
                dateTime=dateTime.split(' ')[0];
            }
            str += '<li>' +
                    '<div class="comment-box-head">' +
                        '<div class="commentNickname">'+item.name + '</div>' +
                    '</div>'+
                    '<div class="commentCon">' +
                        '<p class="content">' + item.content + '</p>' +

                    '</div>' +
                    '<div class="comment-box-bottom">'+
                        '<div class="publicTime">' + dateTime + '</div>' +
                        '<div class="public-location">' + item.from + '</div>' +
                    '</div>'+
                    '<div class="border-box"></div>'+
                '</li>';
        }
        return str;
    };

    /*根据分数情况，得到星星的信息*/
    t.getStarInfoByScore=function(num){
        if(num.toString().indexOf('.')>0){
            num=this.myRoundNumber(num);
        }
        var str='',
            allNum=Math.floor(num),
            tempNum=Math.ceil(num),
            halfNum=tempNum==allNum? 0:1,
            blankNum=5-tempNum;
        for(var i=0;i<allNum;i++){
            str+='<i></i>';
        }
        if(halfNum==1){
            str+='<i class="half"></i>';
        }
        for(var i=0;i<blankNum;i++){
            str+='<i class="none"></i>';
        }
        return str;
    };

    /*
     *对评分进行四舍五入
     * 按照以下类似规则：
     * 1：   2.1，2.2  = 2.0
     * 2：   2.3，2.4，2.5，2.6 = 2.5
     * 3：   2.7，2.8，2.9  = 3.0
     */
    t.myRoundNumber=function(num){
        num=num.toFixed(1);
        var arr=num.split('.'),
            firstNum=arr[0],
            lastNum=arr[1];
        if(lastNum!=0){
            var flag1=lastNum<= 2,
                flag2=lastNum>=7;
            if(flag1){
                return firstNum | 0;
            }else if(flag2){
                return parseInt (firstNum) + 1;
            }
            else{
                return parseInt(firstNum) + 0.5;
            }
        }
    };

    t.showSingInModal=function(){
        $('.sing-in-modal').addClass('show');
        if($('.sing-in-item input').eq(0).val()){
            $('.sing-in-btn').addClass('active');
        }
        this.scrollControl(false);  //禁止滚动
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
        this.scrollControl(true);  //恢复滚动
    };

    //显示所有的 抵扣券信息
    t.showAllLesson=function(e){
        var $target=$(e.currentTarget),
            $span=$target.find('span'),
            $i=$target.find('i'),
            $parent=$target.parent(),
            height=$parent.find('ul').height(),
            i=1;
        if(!$parent.hasClass('show')){
            $parent.css('height',height+35).addClass('show');
            $i.addClass('up');
            this.lesssonScrollTop = $('body').scrollTop();
        }else{
            var h=$parent.data('height'),
                diff=$parent.data('diff');
            if(diff>8) {
                this.initTimer();
            }
            $parent.css('height',h).removeClass('show');
            $i.removeClass('up');
            i=0;
        }
        $span.eq(i).addClass('show').siblings().removeClass('show');
    };

    t.initTimer=function(){
        this.timer=setInterval($.proxy(this,'runToPos'),0.05);
    }
    t.runToPos=function(){
        var currentPosition=$('body').scrollTop();
        currentPosition-=20;
        if(currentPosition>this.lesssonScrollTop)
        {
            window.scrollTo(0,currentPosition);
        }
        else
        {
            clearInterval(this.timer);
        }
    }

    return OrgBasicInfo;
});
