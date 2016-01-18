/**
 * Created by jimmy on 2015/12/28.
 */

define(['zepto','common'],function(){

    function OrgBasicInfo($wrapper,oid) {
        this.$wrapper = $wrapper;
        var that = this;
        this.oid=oid;
        this.pageIndex=1; //评论页码
        this.pageSize=0;
        this.perPageSize=3;

        //样式控制
        this.controlLoadingPos();
        this.videoPreviewBox();
        this.locationMapBox();
        this.initImgPercent();
        this.controlCommentInputStyle();
        this.controlCoverFootStyle();

        /*报名人员列表滚动效果*/
        this.extendJqueryForScroll();

        //初始数据请求
        this.loadBasicInfoData(); //基本信息
        this.loadTopAnnouncement(); //头条
        this.loadSignUpInfo(); /*加载报名信息*/

        /*操作设备信息*/
        this.deviceType=getDeviceType(),
            eventsName='touchend';
        if(!this.deviceType.mobile) {
            eventsName='click';
        }else{
            this.$wrapper.find('.btn').on('touchstart', function () {

            });
        }

        this.$wrapper.find('#videoPreviewBox img').bind('load',$.proxy(this,'controlPlayBtnStyle'));
        this.$wrapper.scroll($.proxy(this,'scrollContainer'));  //滚动加载更多数据

        //重新加载数据
        var fnArr=[this.loadBasicInfoData,this.loadTopAnnouncement,
            this.loadSignUpInfo, this.loadMyVideoInfo,this.loadMyTeachersInfo,
            this.loadMyCompresAsseinfo,this.loadDetailCommentInfo
        ];
        this.$wrapper.on(eventsName,'.loadErrorCon a',function(){
            var index=$(this).data('index')| 0,
                fn=fnArr[index];
            fn&&fn.call(that);
        });
    }

    OrgBasicInfo.prototype={

        /*加载等待框的位置*/
        controlLoadingPos:function(){
            var $loading = $('.loadingResultTips'),
                $body=$('body'),
                w=$loading.width(),
                h=$loading.height(),
                dw=$body.width(),
                dh=$body.height();
            $loading.css({'top':(dh-h)/2,'left':(dw-w)/2,'opacity':'1'});
        },

        /*机构视频预览的图片*/
        videoPreviewBox:function(){

            var $temp=this.$wrapper.find('#videoPreviewBox'),
                w=this.$wrapper.width()-30,
                h=parseInt(w*(9/16)),
                $i=$temp.find('i'),
                ih=$i.height(),
                iw=$i.width();
            this.$wrapper.find('#videoPreviewBox').css('height',h);
            $i.css({'top':(h-ih)/2,'left':(w-iw)/2});
        },

        /*地图预览的图片*/
        locationMapBox:function(){
            var $temp=this.$wrapper.find('.mainItemLocation'),
                w=this.$wrapper.width(),
                h=parseInt(w*(7/16)),
                $i=$temp.find('i'),
                ih=$i.height(),
                iw=$i.width();
            this.$wrapper.find('#locationMap').css('height',h);
        },

        /*播放按钮图片*/
        controlPlayBtnStyle:function(){
            var $temp=this.$wrapper.find('#videoPreviewBox img'),
                w=$temp.width(),
                h=$temp.height(),
                $i=$temp.next(),
                ih=$i.height(),
                iw=$i.width();
            $i.css({'top':(h-ih)/2,'left':(w-iw)/2});
        },

        /*播放我的评论输入框样式*/
        controlCommentInputStyle:function(){
            var $input=this.$wrapper.find('#myComment'),
                w=this.$wrapper.width()-35;
            $input.css('width',w+'px');
        },

        loadData:function(paras){
            if(!paras.type){
                paras.type='get';
            }
            var that=this;
            that.controlLoadingTips(1);
            var loginXhr = $.ajax({
                url: paras.url,
                type: paras.type,
                data: paras.paraData,
                timeOut: 10,
                contentType: 'application/json;charset=utf-8',
                complete: function (xmlRequest, status) {
                    if(status=='success') {
                        var rTxt = xmlRequest.responseText,
                            result = {};
                        if (rTxt) {
                            result = JSON.parse(xmlRequest.responseText)
                        } else {
                            result.status = false;
                        }

                        if (result.success) {
                            that.controlLoadingTips(0);
                            paras.sCallback(JSON.parse(xmlRequest.responseText));
                        } else {

                            var txt=result.message;
                            if(paras.eCallback){
                                paras.eCallback(txt);
                            }
                            that.controlLoadingTips(0);
                        }
                    }
                    //超时
                    else if (status == 'timeout') {
                        loginXhr.abort();
                        that.controlLoadingTips(0);
                        paras.eCallback();
                    }
                    else {
                        that.controlLoadingTips(0);
                        paras.eCallback()
                    }
                }
            });

        },

        /*加载基本信息*/
        loadBasicInfoData:function() {
            var that=this,
                $target=that.$wrapper.find('.logoAndCertInfo'),
                queryPara={
                    url:window.urlObject.apiUrl+'appGetBaseInfo',
                    paraData:{organization_id:this.oid},
                    sCallback: $.proxy(this,'fillInBasicInfoData'),
                    eCallback:function(){
                        $target.css('opacity',1);
                        $target.find('.loadErrorCon').show();

                    }
                };
            this.loadData(queryPara);
        },



        /*显示具体信息*/
        fillInBasicInfoData:function(result){
            var data=result.data;
            var  authen1=data.authenticationInfo[2].status,
                class1=authen1?'certed':'unCerted',
                authen2=data.authenticationInfo[3].status,
                class2=authen2?'certed':'unCerted';
            var url=data.logo;
            if(this.deviceType.android){
                url=window.urlObject.image+'/orgbasicinfo/blur.jpg';
            }

            var str='<div class="head mainContent">'+
                '<div class="filter">'+
                '<img class="logoBg myLogo" src="'+url+'" alt="logo"/>'+
                '<div class="filterUp"></div>'+
                '</div>'+
                '<div class="mainInfo">'+
                '<div class="left">'+
                '<img id="myLogo" class="myLogo" src="'+data.logo+'" />'+
                '</div>'+
                '<div class="right">'+
                '<div id="orgName">'+data.name+'</div>'+
                '<div class="peopleInfo">'+
                '<div class="peopleInfoItem">'+
                '<div class="valInfo" id="viewedVal">'+data.view_count+'</div>'+
                '<div class="filedInfo">查看人数</div>'+
                '</div>'+
                '<div class="peopleInfoItem">'+
                '<div class="valInfo" id="teacherdVal">'+data.teachersCount+'</div>'+
                '<div class="filedInfo">老师</div>'+
                '</div>'+
                '<div class="peopleInfoItem">'+
                '<div class="valInfo" id="fansVal">'+data.followCount+'</div>'+
                '<div class="filedInfo">粉丝</div>'+
                '</div>'+
                '<div class="peopleInfoItem">'+
                '<div class="valInfo" id="groupsVal">'+data.groupCount+'</div>'+
                '<div class="filedInfo">群主</div>'+
                '</div>'+
                '<div style="clear: both;"></div>'+
                '</div>'+
                '</div>'+
                '</div>'+
                '</div>'+
                '<div class="bottom">'+
                '<div class="cerInfoItem '+ class1 +'">'+
                '<span>'+
                '<i class="heiCerIcon spiteBg '+class1+'"></i>'+
                '<span class="cerName '+class1+'">嘿设汇认证</span>'+
                '<div style="clear: both;"></div>'+
                '</span>'+
                '</div>'+
                '<div class="cerInfoItem  '+ class2 +'">'+
                '<span>'+
                '<i class="honestCerIcon spiteBg '+class2+'"></i>'+
                '<span class="cerName '+class1+'">诚信机构认证</span>'+
                '<div style="clear: both;"></div>'+
                '</span>'+
                '</div>'+
                    //'</div>'+
                '</div>';
            this.$wrapper.find('.logoAndCertInfo').html(str).css('opacity',1);
            this.$wrapper.find('#myLogo').setImgBox();
            this.fillInIntroduceInfo(result);


        },

        /*加载头条信息*/
        loadTopAnnouncement:function(){
            var that=this,
                $target=that.$wrapper.find('.mainItemTopNews');
            this.loadData({
                url: window.urlObject.apiUrl + 'topPost',
                paraData: {organization_id: this.oid},
                sCallback: function(result){
                    $target.css('opacity',1);
                    that.fillInTopAnnouncement(result.data);
                },
                eCallback:function(txt){
                    $target.css('opacity',1);
                    $target.find('.loadErrorCon').show().find('a').text('获取头条信息失败，点击重新加载').show();
                }
            });
        },

        /*填充头条信息*/
        fillInTopAnnouncement:function(data){
            var str='',item;
            if(!data || data.length==0){
                str='<li><div class="nonData">暂无头条信息</div></li>';
            }else {
                var len = data.length;
                for (var i = 0; i < len; i++) {
                    item = data[i];
                    str += '<li>' +
                        '<div class="topNewLogo">头条</div>' +
                        '<div class="title">' +
                        '<a href="' + item.detail_url + '">' + item.title + '</a>' +
                        '</div>' +
                        '</li>';
                }
            }
            this.$wrapper.find('.mainItemTopNews .mainContent').html(str);
        },

        /*加载报名信息*/
        loadSignUpInfo:function(){
            var that=this,
                $target=that.$wrapper.find('.mainItemSignUp');
            this.loadData({
                url: window.urlObject.apiUrl + 'enrollList',
                paraData: {organization_id: this.oid,type:'all'},
                sCallback: function(result){
                    $target.css('opacity',1);
                    $target.find('#leftSingUpNum').text(result.available_count);
                    that.fillInSignUpInfo(result.data);  /*填充报名信息*/

                },
                eCallback:function(txt){
                    $target.css('opacity',1);
                    $target.find('.loadErrorCon').show().find('a').text('获取报名信息失败，点击重新加载').show();
                }
            });
        },

        /*填充报名信息*/
        fillInSignUpInfo:function(data){
            var str='',item,
                flag=!data || data.length==0;

            if(flag){
                str='<div class="nonData">暂无人员报名</div>';
            }else {
                var len = data.length,
                    diff=Math.ceil(len/3)*3 - len;
                for (var i = 0; i < len; i++) {
                    item = data[i];
                    var time = new Date(item.create_time * 1000).format('yyyy-MM-dd');
                    str += '<li>' +
                        //'<span class="dot spiteBg"></span>' +
                        '<span>' + item.student_name + '</span>' +
                        '<span>&nbsp;同学于</span>' +
                        '<span>&nbsp;' + time + '</span>' +
                        '<span>&nbsp;成功报名</span>' +
                        '</li>';
                }
                for(var i=0;i<diff;i++){
                    str += '<li></li>';
                }
            }
            this.$wrapper.find('.mainItemSignUp .signUpConUl').html(str);

            // 如果记录人数超过三条，则使用滚动显示的方式
            if(!flag && data.length>3){
                this.$wrapper.find('.signUpCon').Scroll({line:3,speed:2500,timer:2500,up:"btn1",down:"btn2"});
            }
        },

        /*填充简介信息*/
        fillInIntroduceInfo:function(result){
            var $target =this.$wrapper.find('.mainItemBasicInfo'),
                $location=this.$wrapper.find('.mainItemLocation');
            $target.add($location).css('opacity','1');
            if(result &&result.data){
                var data=result.data,
                    introduce=data.introduce,
                    advantage=data.advantage,
                    location=data.location,
                    locationImg=data.location_img;

                /*简介*/
                if(introduce) {
                    $target.find('.introduce').html('<p>'+introduce+'</p>');
                }

                /*优势标签*/
                if(advantage) {
                    var arr=advantage.split('#'),
                        str='';
                    for(var i=0;i<arr.length;i++){
                        str+='<li>'+arr[i]+'</li>';
                    }
                    $target.find('.itemContentDetail').html(str);
                }
                if(!location)
                    $location.find('#myLocation').text(location);
                if(locationImg) {
                    $location.find('.locationMap img').attr('src', locationImg);
                }
                else{
                    $location.find('.noDataInHeader').html('&nbsp;&nbsp;&nbsp;&nbsp;地址信息暂无');
                }
            }
        },


        /*加载我的老师信息*/
        loadMyTeachersInfo:function(callback){
            var that=this,
                $target=that.$wrapper.find('.mainItemTeacherPower');
            this.loadData({
                url: window.urlObject.apiUrl + 'appGetTeacherList',
                paraData: {organization_id: this.oid},
                sCallback: function(result){
                    $target.css('opacity',1);
                    that.fillMyTeachersInfo(result.teacherList);
                    callback&&callback();
                },
                eCallback:function(txt){
                    $target.css('opacity',1);
                    $target.find('.loadErrorCon').show().find('a').text('获得教师信息失败，点击重新加载').show();
                    callback&&callback();
                }
            });
        },

        /*填充我的老师信息*/
        fillMyTeachersInfo:function(data){
            var str='',itemInfo;
            if(!data || data.length==0){
                str='<div class="nonData">暂无老师</div>';
            }
            else {
                var len = data.length,
                    isOdd=len%2== 0,
                    className='border';
                for (var i = 0; i < len; i++) {
                    if(isOdd && i>=len-2){
                        className='unBorder';
                    }
                    if(!isOdd && i>=len-1){
                        className='unBorder';
                    }
                    itemInfo = data[i].info;
                    str += '<li class="'+className +'">' +
                        '<div class="leftPic">' +
                        '<img src="' + itemInfo.avatar128 + '"/>' +
                        '</div>' +
                        '<div class="rightUserInfo">' +
                        '<div class="name">' + itemInfo.nickname + '</div>' +
                        '<div class="desc">' + itemInfo.institution.substrLongStr(12) + '</div>' +
                        '</div>' +
                        '</li>';
                }
            }
            this.$wrapper.find('.mainItemTeacherPower .teacherPowerDetail').prepend(str);
        },

        /*加载我的视频信息*/
        loadMyVideoInfo:function(callback){
            var that=this,
                $target=that.$wrapper.find('.videoPreview');
            this.loadData({
                url: window.urlObject.apiUrl + 'getPropagandaVideo',
                paraData: {organization_id: this.oid},
                sCallback: function(result){
                    if(result.success) {
                        var src=result.data.video_img;
                        if(src) {
                            that.$wrapper.find('#videoPreview').attr('src', result.data.video_img);
                        }else{
                            that.$wrapper.find('.videoCon .itemHeader span').text('视频信息暂无');
                        }
                        callback();
                    }else{
                        that.$wrapper.find('.noDataInHeader').text('视频信息暂无');
                    }
                },
                eCallback:function(txt){
                    $target.css('opacity',1);
                    $target.find('.loadErrorCon').show().find('a').text('获取视频信息失败，，点击重新加载').show();
                    callback();
                }
            });
        },


        /*加载我的评分息*/
        loadMyCompresAsseinfo:function(callback){
            var that=this,
                $target=that.$wrapper.find('.mainItemCompresAsse');
            this.loadData({
                url: window.urlObject.apiUrl + 'fractionalStatistics',
                paraData: {organization_id: this.oid},
                sCallback: function(result){
                    $target.css('opacity',1);
                    that.fillMyCompresAsseInfo(result);
                    callback && callback();
                },
                eCallback:function(txt){
                    $target.css('opacity',1);
                    $target.find('.loadErrorCon:eq(0)').show().find('a').text('获取评价信息失败，点击重新加载').show();
                    callback && callback();
                }
            });
        },

        /*填充我的评分信息*/
        fillMyCompresAsseInfo:function(result){
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
            $basicHeader.find('#myAssessment').text(result.comprehensiveScore);
            $basicHeader.find('#starsConForCompress').prepend(strStar);

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
        },

        /*加载我的评论信息*/
        loadDetailCommentInfo:function(pageIndex,callback){
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
        },

        /*填充我的评论信息*/
        fillDetailCommentInfo:function(result){
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
        },


        /*
         *加载等待,
         *para:
         * status - {num} 状态控制 码
         * 0.显示加载等待;  1 隐藏等待;
         */
        controlLoadingTips:function(status){
            var $target=$('#loadingTip'),
                $img=$target.find('.loadingImg');
            if(status==1){
                $target.css('z-index',1);
                $img.addClass('active');
            } else{
                $target.css('z-index',-1);
                $img.removeClass('active');
            }

        },

        /*
         *滚动加载更多的数据
         * 通过滚动条是否在底部来确定
         * 同时通过 loadingData 类 来防止连续快速滚动导致的重复加载
         */
        scrollContainer:function(e){
            var target= e.currentTarget,
                height = target.scrollHeight - $(target).height(),
                scrollTop=$(target).scrollTop(),
                arrScrollTop=[300,550];

            //加载我的老师
            var $targetTeacher=this.$wrapper.find('.mainItemTeacherPower'),
                $targetCompress=this.$wrapper.find('.mainItemCompresAsse');

            //如果是 300 到500 之间，并且 没有加载过，也 不在加载过程中，则加载新数据
            if(scrollTop>=arrScrollTop[0] &&
                scrollTop<arrScrollTop[1] &&
                $targetTeacher.attr('data-loading')=='false' &&
                $targetTeacher.attr('data-loaded')=='false'){
                var flag=$targetTeacher.attr('data-loaded');
                $targetTeacher.attr('data-loading','true');
                if(flag=='false') {
                    this.loadMyTeachersInfo(function(){
                        $targetTeacher.attr({'data-loaded':'true','data-loading':'false'});
                    });
                    this.loadMyVideoInfo(function(){
                        $targetTeacher.prev().find('.videoCon').attr({'data-loaded':'true','data-loading':'false'});
                    });
                }
                return;
            }

            //加载我的评分
            //如果 大于 500 ，并且 没有加载过，也 不在加载过程中，则加载新数据
            if(scrollTop>=arrScrollTop[1] &&
                $targetCompress.attr('data-loading')=='false' &&
                $targetCompress.attr('data-loaded')=='false'){
                var flag=$targetCompress.attr('data-loaded');
                $targetCompress.attr('data-loading','true');
                if('false'==flag) {
                    this.loadMyCompresAsseinfo(function(){
                        $targetCompress.attr({'data-loaded':'true','data-loading':'false'});
                    });

                    //加载评论内容
                    this.loadDetailCommentInfo(this.pageIndex,function(){
                        $targetCompress.attr({'data-loaded':'true','data-loading':'false'});
                        this.pageIndex++;
                    });
                }
                return;
            }

            //加载更加多评论内容
            //if ($(target).scrollTop() >= height -120 && $targetCompress.attr('data-loading')=='false') {  //滚动到底部
            //    if(this.pageIndex>this.pageSize){
            //        return;
            //    }
            //    $targetCompress.attr('data-loading','true');
            //    this.loadDetailCommentInfo(this.pageIndex,function(){
            //        $targetCompress.attr({'data-loaded':'true','data-loading':'false'});
            //        this.pageIndex++;
            //    });
            //}
        },

        /*根据比例大小 计算图片的大小*/
        initImgPercent:function(){
            $.fn.setImgBox=function() {
                if (this.length == 0) {
                    return;
                }
                var that=this,
                    img = new Image();
                img.src = this[0].src;
                img.onload = function () {
                    var height = img.height,
                        width = img.width,
                        mHeight = that.css('max-height'),
                        mWidth = that.css('max-width');
                    if (!mHeight || mHeight == 'none') {
                        mHeight = that.parent().height();
                    } else {
                        mHeight = mHeight.replace('px', '');
                    }
                    if (!mWidth || mWidth == 'none') {
                        mWidth = that.parent().width();
                    }
                    else {
                        mWidth = mWidth.replace('px', '');
                    }
                    var flag1 = height > mHeight;
                    var flag2 = width > mWidth;
                    var radio = 1;
                    if (flag1 || flag2) {
                        var radio1 = mHeight / height;
                        var radio2 = mWidth / width;
                        if (radio1 < radio2) {
                            height = mHeight;
                            width = width * radio1;
                            radio = radio1;
                        } else {
                            width = mWidth;
                            height = height * radio2;
                            radio = radio2;
                        }
                    }
                    that.css({
                        'width': width + 'px',
                        'height': height + 'px',
                        'margin-top': (that.parent().height() - height) / 2 + 'px'
                    }).attr('data-radio', radio);
                };
                return this;
            };
        },

        /*根据分数情况，得到星星的信息*/
        getStarInfoByScore:function(num){
            if(num.toString().indexOf('.')>0){
                num=this.myRoundNumber(num);
            }
            var str='',
                allNum=Math.floor(num),
                tempNum=Math.ceil(num),
                halfNum=tempNum==allNum? 0:1,
                blankNum=5-tempNum;
            for(var i=0;i<allNum;i++){
                str+='<i class="allStar spiteBgOrigin"></i>';
            }
            if(halfNum==1){
                str+='<i class="halfStar spiteBgOrigin"></i>';
            }
            for(var i=0;i<blankNum;i++){
                str+='<i class="emptyStar spiteBgOrigin"></i>';
            }
            return str;
        },

        /*
         *对评分进行四舍五入
         * 按照以下类似规则：
         * 1：   2.1，2.2  = 2.0
         * 2：   2.3，2.4，2.5，2.6 = 2.5
         * 3：   2.7，2.8，2.9  = 3.0
         */
        myRoundNumber:function(num){
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
        },

        /*根据分数情况，得到色块的信息*/
        getColorBlockInfoByScore:function(score){
            var scores=[
                {min:0,max:2,cName:'greenFillIn'},
                {min:2,max:4,cName:'yellowFillIn'},
                {min:4,max:5.000000001,cName:'redFillIn'}
            ];
            var temp =$.grep(scores,function(n,i){
                return score>= n.min && score<n.max
            })[0];
            return{
                cName:temp.cName,
                width:Math.ceil(score/5*100)
            }
        },

        /*控制底部logo的位置样式*/
        controlCoverFootStyle:function(){
            var $target = $('#downloadCon'),
                $a=$target.find('a'),
                aw=$a.width(),
                ah=aw*0.40,
                bw=$target.width(),
                h= bw*102/750;
            $target.css({'height':h+'px','left':($('body').width()-bw)/2,'opacity':1});
            this.$wrapper.css('bottom',h+'px');
            var fontSize='16px';
            if(bw<375){
                fontSize='14px';
            }
            $a.css({'top':(h-ah)/2,'height':ah+'px','line-height':ah+'px','font-size':fontSize});
        },

        /*
         *根据客户端的时间信息得到发表评论的时间格式
         *多少分钟前，多少小时前，然后是昨天，然后再是月日
         */
        getDiffTime: function (recordTime) {
            if (recordTime) {
                var minute = 1000 * 60;
                var hour = minute * 60;
                var day = hour * 24;
                var diff = new Date() - recordTime;
                var result = '';
                if (diff < 0) {
                    return result;
                }
                var weekR = diff / (7 * day);
                var dayC = diff / day;
                var hourC = diff / hour;
                var minC = diff / minute;
                if (weekR >= 1) {
                    result = recordTime.getFullYear() + '.' + (recordTime.getMonth() + 1) + '.' + recordTime.getDate();
                    return result;
                }
                else if (dayC >= 1) {
                    result = parseInt(dayC) + '天前';
                    return result;
                }
                else if (hourC >= 1) {
                    result = parseInt(hourC) + '小时前';
                    return result;
                }
                else if (minC >= 1) {
                    result = parseInt(minC) + '分钟前';
                    return result;
                } else {
                    result = '刚刚';
                    return result;
                }
            }
            return '';
        },


        /*拓展滚动*/
        extendJqueryForScroll:function(){
            var that=this;
            $.extend($.fn, {
                Scroll:function(opt,callback){
                    //参数初始化
                    if(!opt) var opt={};
                    var timerID;

                    var _this=this.eq(0).find("ul"),
                        $li=_this.find("li"),
                        lineH=$li.eq(0).height(), //获取行高
                        line=opt.line?parseInt(opt.line,10):parseInt(this.height()/lineH,10), //每次滚动的行数，默认为一屏<a href="http://www.codesky.net" class="hden">源码天空</a>，即父容器高度
                        speed=opt.speed?parseInt(opt.speed,10):500, //卷动速度，数值越大，速度越慢（毫秒）
                        timer=opt.timer; //?parseInt(opt.timer,10):3000; //滚动的时间间隔（毫秒）

                    if(line==0) line=1;
                    var upHeight=0-line*lineH;
                    //滚动函数
                    var scrollUp=function(){
                        var style={
                            'margin-top':upHeight,
                        };
                        if(that.deviceType.android){
                             style={
                                'margin-top':upHeight,
                                '-webkit-transform':'translate3d(0,0,0)',
                                '-moz-transform':'translate3d(0,0,0)'
                            };
                        }
                        _this.animate(
                            style,
                            500,'ease-out',
                            function(){
                                for(var i=1;i<=line;i++){
                                    _this.find("li").eq(0).appendTo(_this);
                                }
                                _this.css({marginTop:0});
                            }
                        );
                    }

                    //Shawphy:自动播放
                    var autoPlay = function(){
                        if(timer)timerID = window.setInterval(scrollUp,timer);
                    };
                    autoPlay();
                }
            })
        },
    };

    return OrgBasicInfo;
});
