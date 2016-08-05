/**
 * Created by jimmy on 2015/12/28.
 */

define(['base','mysilder','lazyloading','scale'],function(Base,Myslider){

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
        this.async=false;  //同步加载所有的数据
        this.controlLoadingBox(true);
        window.setTimeout(function(){
            that.initData();
        },100);


        //相册、视频信息查看
        $(document).on(eventName,'.pics-preview-box li',$.proxy(this,'showPicsAndVideoDetailInfo'));

        //学生作品信息查看
        $(document).on(eventName,'.works-preview-box li',$.proxy(this,'showWorksPicsDetailInfo'));

        //关闭相册信息
        $(document).on(eventName,'.view-pics-box', function(){
            event.stopPropagation();
            if(event.target==this){
                $('.modal').removeClass('show');
                that.scrollControl(true);  //恢复滚动
            }
        });

        /*优惠券点击*/
        $(document).on(eventName,'.coupon-box li, .t-video-box li',function(){
            window.location.href='http://www.hisihi.com/download.php';
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

        $(window).on('scroll',$.proxy(this,'scrollContainer'));

        $(document).on('input','#user-name, #phone-num', $.proxy(this,'singInBtnControl'));

        //显示预约报名框
        $(document).on(eventName,'.sing-in-box .active', $.proxy(this,'singIn'));
        //预约
        $(document).on(eventName,'.sing-in', $.proxy(this,'showSingInModal'));

        //关闭预约
        $(document).on(eventName,'.close-sing-in', $.proxy(this,'closeSingInBox'));

    }

    var config={
        downloadBar:{
            show:true,
            pos:0
        }
    };
    OrgBasicInfo.prototype=new Base(config);
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
        this.loadDetailCommentInfo(1);

        $('#wrapper,#footer').show();
        this.controlLoadingBox(false);
        this.initVideoPlayer();
        $('.lazy-img').picLazyLoad($(window),{
            threshold:150
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
                    //电话号码
                    $('.contact a').attr('href','javacript:void(0)').css('opacity','0.3');
                },
                type:'get',
                async:this.async

            };
        this.getDataAsync(queryPara);
    };



    /*显示具体信息*/
    t.fillInBasicInfoData=function(result){
        $('.logo-box').show();
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
        if(data.is_listen_preview =='1'){
            $('.name-main-box img').css('display','inline-block');
        }
        this.setCertInfo(data.authenticationInfo);

        // 粉丝和观看人数
        var $people=$box.find('.user-number-box label')
        $people.eq(0).text(this.translationCount(data.view_count));
        $people.eq(1).text(this.translationCount(data.followCount));

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

        //电话号码
        $('.contact a').attr('href','tel:'+data.phone_num);
    };

    /*数值大于9999时，转换成万*/
    t.translationCount=function(num){
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
            num+='w';
        }
        return num;
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


    /*加载头条信息*/
    t.loadTopAnnouncement=function(){
        var that=this;
        this.getDataAsyncPy({
            url:window.hisihiUrlObj.apiUrlPy+'v1/org/' +this.oid +'/news',
            sCallback: function(result){
                that.fillInTopAnnouncement(result);
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
                str += '<a href="' + item.detail_url + '"><p>' + item.title + '</p></a>';
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
               var str = that.getVideoStr([result.data]);
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
        var $label=$('.pics-number label').eq(1);
        if(!data || data.length==0){
            $label.text('0照片')
            return '';
        }
        var len=data.length,str='';
        type && $label.text(len+'照片');
        for(var i=0;i<len;i++){
            str+='<li class="li-img" data-id="'+data[i].id+'">'+
                    '<div class="img-box">'+
                        '<div class="img-main-box">'+
                            '<img class="lazy-img" data-original="'+data[i].url+'">'+
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
        }else{
            if(!data[0].video_url) {
                return '';
            }
        }

        for(var i=0;i<len;i++) {
            item=data[i];
            item.video_img = item.video_img || item.img || '';
            item.video_url = item.video_url || '';
            //item.video_url = 'http://91.16.0.7/video/14/output.m3u8';
            str+= '<li data-url="' + item.video_url + '" style='+style+'>' +
                '<img class="lazy-img" data-original="' + item.video_img + '">' +
                '<span class="p-btn"><i class="icon-play"></i></span>' +
                '</li>';
        }
        return str;
    };

    /*查看图片或者视频详细信息*/
    t.showPicsAndVideoDetailInfo=function(e){
        var $target=$(e.currentTarget);

        //图片
        if($target.hasClass('li-img')){
            var index= $('.pics-preview-box .li-img').index($target);
            var arr=t.getAllPics($target.parent());
            $('.modal').eq(1).addClass('show').find('.pics-nav span').text(index+1+'/'+arr.length);
            this.initPicsScroll(arr,index);

        }else{
            var url=$target.data('url'),
                that=this;
            if(!url){
                this.showTips('视频暂无');
                return;
            }
            this.resetVideoPlayerUrl($target.data('url'));
        }
        this.scrollControl(false);  //禁止滚动
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
            return;
        }
        else {
            var len = data.length;
            for (var i = 0; i < len; i++) {
                itemInfo = data[i].info;
                str+='<li><img src="'+itemInfo.avatar128+'"><p>' + itemInfo.nickname + '</p></li>';
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
        if(!result || !result.data || result.data.length==0){
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

        //图片
        if ($target.hasClass('li-img')) {
            var index = $('.works-preview-box .li-img').index($target);
            var arr = t.getAllPics($target.parent());
            $('.modal').eq(1).addClass('show').find('.pics-nav span').text(index + 1 + '/' + arr.length);
            this.scrollControl(false);  //禁止滚动
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
                    '<p>' + data.group_name + '</p>'+
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
            $li=$('.assessmentDetail li');

        /*添加星星*/
        var strStar= this.getStarInfoByScore(result.comprehensiveScore);
        var scores=result.comprehensiveScore;
        if(scores.toString().indexOf('.')<0  && scores>0){
            scores=parseInt(scores)+'.0';
        }
        $('#myAssessment').text(scores);
        $('#starsConForCompress').prepend(strStar);

        /*色块评分*/
        for(var i=0;i<data.length;i++){
            item=data[i];
            $li.each(function(){
                var $this=$(this),
                    result=that.getStarInfoByScore(item.score);
                if($this.find('.title').text()==item.value){
                    $this.find('.stars-block').html(result);
                    return false;
                }
            });
        }
    };

    /*加载我的评论信息*/
    t.loadDetailCommentInfo=function(pageIndex,callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'commentList',
            paraData: {organization_id: this.oid,page:pageIndex,count:that.perPageSize},
            sCallback: function(result){
                that.pageSize=Math.ceil((result.totalCount|0)/that.perPageSize);
                $('#commentNum').text(result.totalCount);
                that.fillDetailCommentInfo(result);
                callback&&callback.call(that);
            },
            eCallback:function(txt){},
            type:'get',
            async:this.async
        });
    };

        /*填充我的评论信息*/
    t.fillDetailCommentInfo=function(result){
        var data=result.data,
            str='';
        if(!data || data.length==0){
            $('.studentCommentDetail .nodata').show();
            return;
        }

        /*具体的评论信息*/
        var len = data.length,
            item, userInfo, dateTime;
        for (var i = 0; i < len; i++) {
            item = data[i];
            userInfo = item.userInfo;
            dateTime = this.getDiffTime(item.create_time,true);   //得到发表时间距现在的时间差
            if(dateTime.indexOf('-')>=0){
                dateTime=dateTime.split(' ')[0];
            }
            str += '<li>' +
                '<div class="imgCon">' +
                    '<div><img src="' + userInfo.avatar128 + '"/></div>' +
                '</div>' +
                '<div class="commentCon">' +
                '<div class="commentHead">' +
                '<span class="commentNickname">' + userInfo.nickname + '</span>' +
                '<span class="rightItem starsCon">' +
                this.getStarInfoByScore(item.comprehensive_score | 0) +
                '</span>' +
                '</div>' +
                '<p class="content">' + item.comment + '</p>' +
                '<div class="publicTime">' + dateTime + '</div>' +
                '</div>' +
                '</li>';
        }
        $('.studentCommentDetail').append(str);
    };

    /*
     *滚动加载更多的数据
     * 通过滚动条是否在底部来确定
     * 同时通过 loadingData 类 来防止连续快速滚动导致的重复加载
     */
    t.scrollContainer=function(){
        var $target= $('body'),
            height = $target[0].scrollHeight -$(window).height(),
            scrollTop=$target.scrollTop();
        //加载更加多评论内容
        if (scrollTop >= height -320 && !$target.hasClass('loading')) {  //滚动到底部
            var tempIndex=this.pageIndex+1;
            if(tempIndex>this.pageSize){
                return;
            }
            $target.addClass('loading');
            this.loadDetailCommentInfo(tempIndex,function(){
                $target.removeClass('loading');
                this.pageIndex++;
            });
        }
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
        //$('.sing-in-modal')[0].addEventListener('touchmove', forbiddenScroll, false);
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
        var $input = $target=$('.sing-in-item input'),
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
            //paraData:JSON.stringify({organization_id: this.oid,mobile:number,username:name}),
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


    return OrgBasicInfo;
});
