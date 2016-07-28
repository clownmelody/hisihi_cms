/**
 * Created by jimmy on 2015/12/28.
 */

define(['base','mysilder','scale'],function(Base,Myslider){

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

        this.async=false;  //同步加载所有的数据
        this.controlLoadingBox(true);
        window.setTimeout(function(){
            that.initData();
        },100);


        $(document).on(eventName,'.pics-preview-box li',$.proxy(this,'showPicsAndVideoDetailInfo'));
        $(document).on(eventName,'.works-preview-box li',$.proxy(this,'showWorksPicsDetailInfo'));

        $(document).on(eventName,'.view-pics-box', function(){
            event.stopPropagation();
            if(event.target==this){
                $('.modal').removeClass('show');
                $('html,body').removeClass('ovfHidden');
            }
        });

        $(document).on('click','.t-video-box li',$.proxy(this,'showTeachingVideo'));

        $(document).on(eventName,'.video-modal', function(){
            event.stopPropagation();
            if(event.target==this){
                $('.modal').removeClass('show');
                $('html,body').removeClass('ovfHidden');
                that.myPlayer.pause();
            }
        });


    }

    OrgBasicInfo.prototype=new Base();
    OrgBasicInfo.constructor=OrgBasicInfo;
    var t=OrgBasicInfo.prototype;

    t.initData=function(){
        this.loadBasicInfoData();
        this.loadTopAnnouncement();
        this.loadSignUpInfo();
        this.loadCouponInfo();
        this.loadVideo();
        this.loadMyTeachersInfo();
        this.loadTeachingVideoInfo();
        this.loadWorksInfo();
        this.loadGroupsInfo();
        this.loadMyCompresAsseinfo();

        $('#wrapper').show();
        this.controlLoadingBox(false);
        this.initVideoPlayer();
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
    t.resetVideoPlayerUrl=function(url){
        if(!this.myPlayer){
            this.initVideoPlayer();
        }
        this.myPlayer.src({type: 'application/x-mpegURL',src:url});
        $('.modal').eq(0).addClass('show');
    };

    /*加载基本信息*/
    t.loadBasicInfoData=function() {
        var that=this,
            $target=that.$wrapper.find('.logoAndCertInfo'),
            queryPara={
                url:this.baseUrl+'appGetBaseInfo',
                paraData:{organization_id:this.oid,version:2.95},
                sCallback: $.proxy(this,'fillInBasicInfoData'),
                eCallback:function(){
                    //$target.css('opacity',1);
                    //$target.find('.loadErrorCon').show();
                },
                type:'get',
                async:this.async

            };
        this.getDataAsync(queryPara);
    };



    /*显示具体信息*/
    t.fillInBasicInfoData=function(result){
        var data=result.data, url=data.logo;
        if(this.deviceType.android){
            url=window.hisihiUrlObj.image+'/orgbasicinfo/blur.jpg';
        }
        var name=data.name;
        if(name.length>10){
            name=name.substr(0,9)+' …';
        }
        var $box=$('.logo-box');
        //logo
        $box.find('#filter-logo').attr('src',url);
        $box.find('#logo').attr('src',data.logo);

        // 视频、名称、认证
        $box.find('.name-main-box label').text(data.name);
        if(data.is_listen_preview =='0'){
            $('.name-main-box i').css('display','inline-block');
        }
        this.setCertInfo(data.authenticationInfo);

        // 粉丝和观看人数
        var $people=$box.find('.user-number-box label');
        $people.eq(0).text(data.view_count);
        $people.eq(1).text(data.followCount);

        //基本信息
        if(data.introduce){
            $('.my-introduce').text(data.introduce).parent().show();
        }
        if(data.location){
            $('.my-location').text(data.location).parent().show();
        }
        /*优势标签*/
        if(data.advantage) {
            var arr=data.advantage.split('#'),
                str='';
            for(var i=0;i<arr.length;i++){
                str+='<li>'+arr[i]+'</li>';
            }
            $('.my-tags').html(str).parent().show();
        }
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
                if (item.name == '嘿设汇认证') {
                    if (item.status && item.hisihi_add_v && item.hisihi_add_v == true) {
                        $('.v-cert').show();
                        continue;
                    }
                } else {
                    if (item.status) {
                        url = item.pic_url
                    } else {
                        url = item.disable_pic_url;
                    }
                    str += '<img src="' + url + '">';
                }
            }
        }
        $('.cert-box').html(str);
    };

    //优惠券信息
    t.loadCouponInfo=function(){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'getPromotionCouponList',
            paraData: {organization_id: this.oid, version: 2.95},
            sCallback: function(result){
                that.fillInCouponInfo(result);
            },
            eCallback:function(txt){
                //$target.css('opacity',1);
                //$target.find('.loadErrorCon').show().find('a').text('获取头条信息失败，点击重新加载').show();
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
        for(var i=0;i<5;i++){
            coupon=list[i];
            str+='<li>'+
                    '<div class="coupon-header">￥'+coupon.money+'</div>'+
                    '<div class="coupon-bottom">'+
                        '<p>适用于：'+coupon.course_name+'</p>'+
                    '</div>'+
                 '</li>'
        }
        $('.coupon-box ul').html(str);
    };


    /*加载头条信息*/
    t.loadTopAnnouncement=function(){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'topPost',
            paraData: {organization_id: this.oid},
            sCallback: function(result){
                that.fillInTopAnnouncement(result.data);
            },
            eCallback:function(txt){
                //$target.css('opacity',1);
                //$target.find('.loadErrorCon').show().find('a').text('获取头条信息失败，点击重新加载').show();
            },
            type:'get',
            async:this.async
        });
    };

    /*填充头条信息*/
    t.fillInTopAnnouncement=function(data){
        var str='',item,
            $target=$('.news-box');
        if(data || data.length!=0){
            $target.show();
            var len = data.length;
            for (var i = 0; i < len; i++) {
                if(i<2) {
                    item = data[i];
                    str += '<a href="' + item.detail_url + '"><p>' + item.title + '</p></a>';
                }
            }
        }
        $target.find('.right-item div').html(str);
    };

    //滚动
    t.scrollSingInInfo=function(){
        var $target=$('.sing-in-detail-box'),
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
    t.loadSignUpInfo=function(){
        var that=this;
        this.getDataAsync({
            url:this.baseUrl + 'enrollList',
            paraData: {organization_id: this.oid,type:'all'},
            sCallback: function(result){
                that.fillInSignUpInfo(result.data);  /*填充报名信息*/
            },
            eCallback:function(txt){
                //$target.css('opacity',1);
                //$target.find('.loadErrorCon').show().find('a').text('获取报名信息失败，点击重新加载').show();
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

        $('.sing-in-box').show().find('.sing-in-detail-box').html(str);

        // 如果记录人数超过1条，则使用滚动显示的方式
        if (data.length > 1) {
            this.scrollSingInInfo();
        }
    };

    /*加载视频*/
    t.loadVideo=function(){
        var that=this;
        this.getDataAsync({
            url:this.baseUrl + 'getPropagandaVideo',
            paraData: {organization_id: this.oid,count:8},
            sCallback: function(result){
               var str = that.getVideoStr([result.data]);  /*填充报名信息*/
                that.loadPics(str);
            },
            eCallback:function(txt){
                //$target.css('opacity',1);
                //$target.find('.loadErrorCon').show().find('a').text('获取报名信息失败，点击重新加载').show();
            },
            type:'get',
            async:this.async
        });
    };

    /*加载相册*/
    t.loadPics=function(str){
        var that=this;
        this.getDataAsync({
            url:this.baseUrl + 'appGetOrganizationEnvironment',
            paraData: {organization_id: this.oid,count:8},
            sCallback: function(result){
                var newStr = that.getPicsStr(result.data,true),
                    allStr=str+newStr;
                if(allStr!='') {
                    $('.pics-box').show().find('.pics-preview-box ul')
                        .html(str + newStr);
                }
            },
            eCallback:function(txt){
                //$target.css('opacity',1);
                //$target.find('.loadErrorCon').show().find('a').text('获取报名信息失败，点击重新加载').show();
            },
            type:'get',
            async:this.async
        });
    };

    /*
    * 相册
    * para:
    * data - {arr} 图片信息数组
    * type - {bool} true 表示是  相册 模块调用；false 表示是  作品 模块调用
    */
    t.getPicsStr=function(data,type){
        if(!data || data.length==0){
            return;
        }
        var len=data.length,str='';
        type && $('.pics-number label').eq(1).text(len+'照片');
        for(var i=0;i<len;i++){
            str+='<li class="li-img" data-id="'+data[i].id+'">'+
                    '<div class="img-box">'+
                        '<div class="img-main-box">'+
                            '<img src="'+data[i].url+'">'+
                        '</div>'+
                    '</div>'+
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
            style='';
        if(type){
            var h=$('body').width()*7/16;
            style='height:'+h+'px;';
        }

        for(var i=0;i<len;i++) {
            item=data[i];
            item.video_img = item.video_img || item.img;
            //item.video_url = 'http://91.16.0.7/video/14/output.m3u8';
            str+= '<li data-url="' + item.video_url + '" style='+style+'>' +
                '<img src="' + item.video_img + '">' +
                '<span class="p-btn"><i class="icon-play"></i></span>' +
                '</li>';
        }
        return str;
    };

    /*查看图片或者视频详细信息*/
    t.showPicsAndVideoDetailInfo=function(e){
        var $target=$(e.currentTarget);
        $('html,body').addClass('ovfHidden');
        //图片
        if($target.hasClass('li-img')){
            var index= $('.pics-preview-box .li-img').index($target);
            var arr=t.getAllPics($target.parent());
            $('.modal').eq(1).addClass('show').find('.pics-nav span').text(index+1+'/'+arr.length);
            this.initPicsScroll(arr,index);

        }else{
            var url=$target.data('url'),
                that=this;
            if(url=='null' || url=='undefined'){
                this.showTips('视频暂无');
                return;
            }
            this.resetVideoPlayerUrl($target.data('url'));
        }
    };


    /*得到所有图片地址*/
    t.getAllPics=function($box){
        var arr=[];
        $box.find('.li-img').each(function(){
            arr.push($(this).find('img').attr('src'));
        });
        return arr;
    };

    /*图片列表展示*/
    t.initPicsScroll=function(imgArr,index){
        if(index=='undefined'){
            index==0;
        }
        var arr=this.getItemStr(imgArr),
            $span=$('.pics-nav span'),
            num=$span.text().split('/')[1];
        $('#filter-img').attr('src',imgArr[index]);
        new Myslider($('.view-pics-box'),arr,{
                autoPlay:false,
                showNav:false,
                index:index,
                changeCallback:function(type,picIndex){
                    $span.text((picIndex+1)+'/'+num);
                    $('#filter-img').attr('src',imgArr[picIndex]);
            }
        });

        var btnList=document.querySelectorAll('.view-pics-box .show-origin-pic');
        //实例化缩放
        ImagesZoom.init({
            elem: ".view-pics-box",  //容器dom
            btnsList:btnList,  //查看按钮
            initCallback:function(dom){
                $(dom).hide().parent().find('img').hide();
                $('.pics-nav label').hide();
            },
            closeCallback:function(){
                $('.view-pics-box .now img').show();
                $('.pics-nav label').show();
                for(var len=btnList.length,i=0;i<len;i++) {
                    $(btnList[i]).show();
                }
            }
        });
    };


    t.getItemStr=function(data){
        var len=data.length,arr=[];
        for(var i=0;i<len;i++){
            var item=data[i];
            arr.push('<img  src="'+item+'"><div class="show-origin-pic">查看大图</div>');
        }
        return arr;
    };


    /*加载我的老师信息*/
    t.loadMyTeachersInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'appGetTeacherList',
            paraData: {organization_id: this.oid},
            sCallback: function(result){
                that.fillMyTeachersInfo(result.teacherList);
            },
            eCallback:function(txt){

            },
            type:'get',
            async:this.async
        });
    };

    /*填充我的老师信息*/
    t.fillMyTeachersInfo=function(data){
        var str='',itemInfo;
        if(!data || data.length==0){
            str='<div class="nonData">暂无老师</div>';
        }
        else {
            var len = data.length;
            for (var i = 0; i < len; i++) {
                itemInfo = data[i].info;
                str+='<li><img src="'+itemInfo.avatar128+'"><p>' + itemInfo.nickname + '</p></li>';
            }
        }
        $('.teachers-detail').html(str);
    }

    /*加载我的视频教程信息*/
    t.loadTeachingVideoInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'appGetCoursesList',
            paraData: {organization_id: this.oid},
            sCallback: function(result){
                that.fillInTeachingVideo(result);
            },
            eCallback:function(txt){
                $target.css('opacity',1);
                $target.find('.loadErrorCon').show().find('a').text('获取视频信息失败，，点击重新加载').show();
                callback();
            },
            type:'get',
            async:this.async
        });
    };

    t.fillInTeachingVideo=function(data){
        if(!data || data.coursesList.length==0){
            return;
        }
        var $box =  $('.t-video-box').show();
        var str = this.getVideoStr(data.coursesList,true);
        $box.find('.basic-header span').html('('+data.coursesList.length+')');
        $box.find('ul').html(str);
    };


    /*播放教学视频*/
    t.showTeachingVideo=function(e){
        //var $target=$(e.currentTarget),
        //    url=$target.data('url');
        //if(url=='null' || url=='undefined'){
        //    this.showTips('视频暂无');
        //    return;
        //}
        //this.resetVideoPlayerUrl(url);
        this.showTips('下载App');
    };



    /*加载学生作品*/
    t.loadWorksInfo=function(){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'appGetStudentWorks',
            paraData: {organization_id: this.oid,count:8,version:2.9},
            sCallback: function(result){
                that.fillWorksInfo(result);
            },
            eCallback:function(txt){

            },
            type:'get',
            async:this.async
        });
    };

    t.fillWorksInfo=function(result){
        if(!result || result.data.length==0){
            return;
        }
        var $box =  $('.works-box').show(),
            data=result.data,
            len=data.length;
        var str = this.getPicsStr(result.data,false);
        $box.find('ul').html(str);
    };

    /*查看图片或者视频详细信息*/
    t.showWorksPicsDetailInfo=function(e) {
        var $target = $(e.currentTarget);
        $('html,body').addClass('ovfHidden');
        //图片
        if ($target.hasClass('li-img')) {
            var index = $('.works-preview-box .li-img').index($target);
            var arr = t.getAllPics($target.parent());
            $('.modal').eq(1).addClass('show').find('.pics-nav span').text(index + 1 + '/' + arr.length);
            this.initPicsScroll(arr, index);

        }
    };


    /*加载群组*/
    t.loadGroupsInfo=function(){
        var that=this;
        this.getDataAsyncPy({
            url: window.hisihiUrlObj.apiUrlPy+'/v1/im/org/' +this.oid +'/groups',
            paraData: {per_page:1},
            sCallback: function(result){
                that.filInGroupsInfo(result);
            },
            eCallback:function(txt){

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
                    '<p>' + data.description + '</p>'+
                '</div>';
        $box.find('.groups-detail-box').html(str);
    };


    /*加载我的评分息*/
    t.loadMyCompresAsseinfo=function(callback){
        var that=this;
        this.getDataAsync({
            url:this.baseUrl+ 'fractionalStatistics',
            paraData: {organization_id: this.oid},
            sCallback: function(result){
                that.fillMyCompresAsseInfo(result);
            },
            eCallback:function(txt){},
            type:'get',
            async:this.async
        });
    };

        /*填充我的评分信息*/
    t.fillMyCompresAsseInfo=function(result){
        var data=result.data;
        if(!data || data.length==0){
            return;
        }
        var str='',
            that=this,
            item,
            $target=this.$wrapper.find('.mainItemCompresAsse'),
            $basicHeader=$target.find('.basicHeader'),
            $li=$target.find('.assessmentDetail li');

        /*添加星星*/
        var strStar= this.getStarInfoByScore(result.comprehensiveScore);
        $('#myAssessment').text(result.comprehensiveScore);
        $('#starsConForCompress').prepend(strStar);

        /*色块评分*/
        for(var i=0;i<data.length;i++){
            item=data[i];
            $li.each(function(){
                var $this=$(this),
                    result=that.getColorBlockInfoByScore(item.score);
                if($this.find('.title').text()==item.value){
                    $this.find('.score').text(item.score);
                    $this.find('.fillIn').addClass(result.cName)
                        .css('width',result.width+'%')
                        .next().css('width',100-result.width+'%');
                    return false;
                }
            });
        }
    };

        /*加载我的评论信息*/
    t.loadDetailCommentInfo=function(pageIndex,callback){
        var that=this,
            $target=that.$wrapper.find('.studentCommentCon');
        this.loadData({
            url: window.urlObject.apiUrl + 'commentList',
            paraData: {organization_id: this.oid,page:pageIndex,count:that.perPageSize},
            sCallback: function(result){
                that.pageSize=Math.ceil((result.totalCount|0)/that.perPageSize);
                that.$wrapper.find('#commentNum').text(result.totalCount);
                that.fillDetailCommentInfo(result);
                callback&&callback.call(that);
            },
            eCallback:function(txt){
                $target.find('.loadErrorCon:eq(1)').show().find('a').text('获取评论信息失败，点击重新加载').show();
                callback&&callback.call(that);
            }
        });
    };

        /*填充我的评论信息*/
    t.fillDetailCommentInfo=function(result){
        var data=result.data,
            str='';
        if(!data || data.length==0){
            str='<li><div class="nonData">暂无评论</div></li>';
            this.$wrapper.find('.studentCommentDetail li').remove();
        }else {
            /*具体的评论信息*/
            var len = data.length,
                item, userInfo, dateTime;
            for (var i = 0; i < len; i++) {
                item = data[i];
                userInfo = item.userInfo;
                dateTime = this.getDiffTime(new Date(item.create_time * 1000));   //得到发表时间距现在的时间差
                str += '<li>' +
                    '<div class="imgCon">' +
                        '<div><img src="' + userInfo.avatar128 + '"/></div>' +
                    '</div>' +
                    '<div class="commentCon">' +
                    '<div class="commentHead">' +
                    '<span class="commentNickname">' + userInfo.nickname + '</span>' +
                    '<span class="rightItem starsCon">' +
                    this.getStarInfoByScore(item.comprehensive_score | 0) +
                    '<div style="clear: both;"></div>' +
                    '</span>' +
                    '</div>' +
                    '<div class="content">' + item.comment + '</div>' +
                    '<div class="publicTime">发表于' + dateTime + '</div>' +
                    '</div>' +
                    '</li>';
            }
        }
        this.$wrapper.find('.studentCommentDetail').append(str);
    };


    //
    //    /*
    //     *滚动加载更多的数据
    //     * 通过滚动条是否在底部来确定
    //     * 同时通过 loadingData 类 来防止连续快速滚动导致的重复加载
    //     */
    //    scrollContainer:function(e){
    //        var target= e.currentTarget,
    //            height = target.scrollHeight - $(target).height(),
    //            scrollTop=$(target).scrollTop(),
    //            arrScrollTop=[300,550];
    //
    //        //加载我的老师
    //        var $targetTeacher=this.$wrapper.find('.mainItemTeacherPower'),
    //            $targetCompress=this.$wrapper.find('.mainItemCompresAsse');
    //
    //        //如果是 300 到500 之间，并且 没有加载过，也 不在加载过程中，则加载新数据
    //        if(scrollTop>=arrScrollTop[0] &&
    //            scrollTop<arrScrollTop[1] &&
    //            $targetTeacher.attr('data-loading')=='false' &&
    //            $targetTeacher.attr('data-loaded')=='false'){
    //            var flag=$targetTeacher.attr('data-loaded');
    //            $targetTeacher.attr('data-loading','true');
    //            if(flag=='false') {
    //                this.loadMyTeachersInfo(function(){
    //                    $targetTeacher.attr({'data-loaded':'true','data-loading':'false'});
    //                });
    //                this.loadMyVideoInfo(function(){
    //                    $targetTeacher.prev().find('.videoCon').attr({'data-loaded':'true','data-loading':'false'});
    //                });
    //            }
    //            return;
    //        }
    //
    //        //加载我的评分
    //        //如果 大于 500 ，并且 没有加载过，也 不在加载过程中，则加载新数据
    //        if(scrollTop>=arrScrollTop[1] &&
    //            $targetCompress.attr('data-loading')=='false' &&
    //            $targetCompress.attr('data-loaded')=='false'){
    //            var flag=$targetCompress.attr('data-loaded');
    //            $targetCompress.attr('data-loading','true');
    //            if('false'==flag) {
    //                this.loadMyCompresAsseinfo(function(){
    //                    $targetCompress.attr({'data-loaded':'true','data-loading':'false'});
    //                });
    //
    //                //加载评论内容
    //                this.loadDetailCommentInfo(this.pageIndex,function(){
    //                    $targetCompress.attr({'data-loaded':'true','data-loading':'false'});
    //                    this.pageIndex++;
    //                });
    //            }
    //            return;
    //        }
    //
    //        //加载更加多评论内容
    //        //if ($(target).scrollTop() >= height -120 && $targetCompress.attr('data-loading')=='false') {  //滚动到底部
    //        //    if(this.pageIndex>this.pageSize){
    //        //        return;
    //        //    }
    //        //    $targetCompress.attr('data-loading','true');
    //        //    this.loadDetailCommentInfo(this.pageIndex,function(){
    //        //        $targetCompress.attr({'data-loaded':'true','data-loading':'false'});
    //        //        this.pageIndex++;
    //        //    });
    //        //}
    //    },

    //
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
            str+='<i class="full"></i>';
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
                return firstNum | 0 + 1;
            }
            else{
                return parseInt(firstNum) + 0.5;
            }
        }
    };
    //
    //    /*根据分数情况，得到色块的信息*/
    //    getColorBlockInfoByScore:function(score){
    //        var scores=[
    //            {min:0,max:2,cName:'greenFillIn'},
    //            {min:2,max:4,cName:'yellowFillIn'},
    //            {min:4,max:5.000000001,cName:'redFillIn'}
    //        ];
    //        var temp =$.grep(scores,function(n,i){
    //            return score>= n.min && score<n.max
    //        })[0];
    //        return{
    //            cName:temp.cName,
    //            width:Math.ceil(score/5*100)
    //        }
    //    },
    //
    //    /*控制底部logo的位置样式*/
    //    controlCoverFootStyle:function(){
    //        var $target = $('#downloadCon'),
    //            $a=$target.find('a'),
    //            aw=$a.width(),
    //            ah=aw*0.40,
    //            bw=$target.width(),
    //            h= bw*102/750;
    //        $target.css({'height':h+'px','left':($('body').width()-bw)/2,'opacity':1});
    //        this.$wrapper.css('bottom',h+'px');
    //        var fontSize='16px';
    //        if(bw<375){
    //            fontSize='14px';
    //        }
    //        $a.css({'top':(h-ah)/2,'height':ah+'px','line-height':ah+'px','font-size':fontSize});
    //    },
    //
    //    /*
    //     *根据客户端的时间信息得到发表评论的时间格式
    //     *多少分钟前，多少小时前，然后是昨天，然后再是月日
    //     */
    //    getDiffTime: function (recordTime) {
    //        if (recordTime) {
    //            var minute = 1000 * 60;
    //            var hour = minute * 60;
    //            var day = hour * 24;
    //            var diff = new Date() - recordTime;
    //            var result = '';
    //            if (diff < 0) {
    //                return result;
    //            }
    //            var weekR = diff / (7 * day);
    //            var dayC = diff / day;
    //            var hourC = diff / hour;
    //            var minC = diff / minute;
    //            if (weekR >= 1) {
    //                result = recordTime.getFullYear() + '.' + (recordTime.getMonth() + 1) + '.' + recordTime.getDate();
    //                return result;
    //            }
    //            else if (dayC >= 1) {
    //                result = parseInt(dayC) + '天前';
    //                return result;
    //            }
    //            else if (hourC >= 1) {
    //                result = parseInt(hourC) + '小时前';
    //                return result;
    //            }
    //            else if (minC >= 1) {
    //                result = parseInt(minC) + '分钟前';
    //                return result;
    //            } else {
    //                result = '刚刚';
    //                return result;
    //            }
    //        }
    //        return '';
    //    },
    //
    //
    //    /*拓展滚动*/
    //    extendJqueryForScroll:function(){
    //        var that=this;
    //        $.extend($.fn, {
    //            Scroll:function(opt,callback){
    //                //参数初始化
    //                if(!opt) var opt={};
    //                var timerID;
    //
    //                var _this=this.eq(0).find("ul"),
    //                    $li=_this.find("li"),
    //                    lineH=$li.eq(0).height(), //获取行高
    //                    line=opt.line?parseInt(opt.line,10):parseInt(this.height()/lineH,10), //每次滚动的行数，默认为一屏<a href="http://www.codesky.net" class="hden">源码天空</a>，即父容器高度
    //                    speed=opt.speed?parseInt(opt.speed,10):500, //卷动速度，数值越大，速度越慢（毫秒）
    //                    timer=opt.timer; //?parseInt(opt.timer,10):3000; //滚动的时间间隔（毫秒）
    //
    //                if(line==0) line=1;
    //                var upHeight=0-line*lineH;
    //                //滚动函数
    //                var scrollUp=function(){
    //                    var style={
    //                        'margin-top':upHeight,
    //                    };
    //                    if(that.deviceType.android){
    //                         style={
    //                            'margin-top':upHeight,
    //                            '-webkit-transform':'translate3d(0,0,0)',
    //                            '-moz-transform':'translate3d(0,0,0)'
    //                        };
    //                    }
    //                    _this.animate(
    //                        style,
    //                        500,'ease-out',
    //                        function(){
    //                            for(var i=1;i<=line;i++){
    //                                _this.find("li").eq(0).appendTo(_this);
    //                            }
    //                            _this.css({marginTop:0});
    //                        }
    //                    );
    //                }
    //
    //                //Shawphy:自动播放
    //                var autoPlay = function(){
    //                    if(timer)timerID = window.setInterval(scrollUp,timer);
    //                };
    //                autoPlay();
    //            }
    //        })
    //    },
    //};

    return OrgBasicInfo;
});
