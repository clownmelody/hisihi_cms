/**
 * Created by jimmy on 2015/12/28.
 */

define(['zepto','common'],function(){
    function OrgBasicInfo($wrapper,oid) {
        this.$wrapper = $wrapper;
        var that = this;
        this.oid=oid;
        this.controlLoadingPos();
        //控制视频预览框的高度
        this.videoPreviewBox();
        this.locationMapBox();
        this.initImgPercent();
        this.controlCoverFootStyle();

        /*加载基本信息*/
        var queryPara={
            url:window.urlObject.apiUrl+'appGetBaseInfo',
            paraData:{organization_id:this.oid},
            sCallback: $.proxy(this,'fillInData'),
            eCallback:null
        };
        this.loadData(queryPara);

        this.$wrapper.find('#videoPreviewBox img').bind('load',$.proxy(this,'controlPlayBtnStyle'));
        this.$wrapper.scroll($.proxy(this,'scrollContainer'));  //滚动加载更多数据
        this.$wrapper.on('click','#basicInfoLoadEorror',function(){   //重新加载数据
            that.loadData(queryPara);
        });
        this.pageIndex=1; //评论页码
        this.pageSize=0;
        this.perPageSize=10;
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


        loadData:function(paras) {
            if(!paras.type){
                paras.type='get';
            }
            var that=this;
            that.controlLoadingTips(0);
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
                            that.controlLoadingTips(1);
                            paras.sCallback(JSON.parse(xmlRequest.responseText));
                        } else {

                            var txt=result.message;
                            if(paras.eCallback){
                                paras.eCallback(txt);
                            }
                            that.controlLoadingTips(-1, txt);
                            //else {
                            //    that.controlLoadingTips(-1, txt);
                            //}
                        }
                    }
                    //超时
                    else if (status == 'timeout') {
                        loginXhr.abort();
                        that.controlLoadingTips(-1,'加载失败，点击重新加载');
                    }
                    else {
                        that.controlLoadingTips(-1,'加载失败，点击重新加载');
                    }
                }
            });

        },

        /*显示具体信息*/
        fillInData:function(result){
            var data=result.data;
            var  authen1=data.authenticationInfo[2].status,
                class1=authen1?'certed':'unCerted',
                authen2=data.authenticationInfo[3].status,
                class2=authen2?'certed':'unCerted';

           //var str= '<div class="mainItem logoAndCertInfo">'+
            var str='<div class="head mainContent">'+
                            '<div class="filter">'+
                                '<img class="logoBg myLogo" src="'+data.logo+'" alt="logo"/>'+
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
                            '<div class="cerInfoItem">'+
                                '<span>'+
                                    '<i class="heiCerIcon spiteBg '+class1+'"></i>'+
                                    '<span class="cerName '+class1+'">嘿设汇认证</span>'+
                                    '<div style="clear: both;"></div>'+
                                '</span>'+
                            '</div>'+
                            '<div class="cerInfoItem">'+
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

            this.loadTopAnnouncement();
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
                    that.loadSignUpInfo(); /*加载报名信息*/
                },
                eCallback:function(txt){
                    $target.css('opacity',1);
                    $target.find('.loadErrorCon').show().find('a').text('获得头条信息失败，'+txt).show();
                    that.loadSignUpInfo(); /*加载报名信息*/
                }
            });
        },

        /*填充头条信息*/
        fillInTopAnnouncement:function(data){
            var str='',item;
            if(!data || data.length==0){
                return;
            }
            var len=data.length;
            for(var i=0;i<len;i++){
                item=data[i];
                str += '<li>'+
                            '<div class="topNewLogo">头条</div>'+
                            '<div class="title">'+
                                '<a href="'+window.urlObject.webRoot+item.detail_url+'">' + item.title + '</a>'+
                            '</div>'+
                        '</li>';
            }
            this.$wrapper.find('.mainItemTopNews .mainContent').html(str);

        },

        /*加载报名信息*/
        loadSignUpInfo:function(){
            var that=this,
            $target=that.$wrapper.find('.mainItemSignUp');
            this.loadData({
                url: window.urlObject.apiUrl + 'enrollList',
                paraData: {organization_id: this.oid},
                sCallback: function(result){
                    $target.css('opacity',1);
                    $target.find('#leftSingUpNum').text(result.available_count);
                    that.fillInSignUpInfo(result.data);  /*填充报名信息*/
                },
                eCallback:function(txt){
                    $target.css('opacity',1);
                    $target.find('.loadErrorCon').show().find('a').text('获得报名信息失败，'+txt).show();
                }
            });
        },

        /*填充报名信息*/
        fillInSignUpInfo:function(data){
            var str='',item;
            if(!data || data.length==0){
                return;
            }
            var len=data.length;
            for(var i=0;i<len;i++){
                item=data[i];
                var time=new Date(item.create_time*1000).format('yyyy-MM-dd');
                str += '<li>'+
                            '<span class="dot">&middot;</span>'+
                            '<span>'+item.student_name+'</span>'+
                            '<span>&nbsp;&nbsp;同学于</span>'+
                            '<span>&nbsp;&nbsp;'+ time+'</span>'+
                            '<span>&nbsp;&nbsp;成功报名</span>'+
                        '</li>';
            }
            this.$wrapper.find('.mainItemSignUp .signUpCon').html(str);
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
                $location.find('#myLocation').text(location);
                if(!locationImg){
                    locationImg=window.urlObject.image+'/orgbasicinfo/map.png';
                }
                $location.find('.locationMap img').attr('src',locationImg);
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
                    callback();
                },
                eCallback:function(txt){
                    $target.css('opacity',1);
                    $target.find('.loadErrorCon').show().find('a').text('获得头条信息失败，'+txt).show();
                    callback();
                }
            });
        },

        /*填充我的老师信息*/
        fillMyTeachersInfo:function(data){
            var str='',itemInfo;
            if(!data || data.length==0){
                return;
            }
            var len=data.length;
            for(var i=0;i<len;i++){
                itemInfo=data[i].info;
                str +=  '<li>'+
                            '<div class="leftPic">'+
                                '<img src="'+itemInfo.avatar128+'"/>'+
                                '</div>'+
                                '<div class="rightUserInfo">'+
                                '<div class="name">'+itemInfo.nickname+'</div>'+
                                '<div class="desc">'+itemInfo.institution.substrLongStr(12)+'</div>'+
                            '</div>'+
                        '</li>';
            }
            var $ul=this.$wrapper.find('.mainItemTeacherPower .teacherPowerDetail');
            $ul.find('.nonData').remove();
            $ul.prepend(str);
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
                        that.$wrapper.find('#videoPreview').attr('src', result.data.video_img);
                        callback();
                    }else{
                        that.$wrapper.find('.videoCon .itemHeader span').text('视频信息暂无');
                    }
                },
                eCallback:function(txt){
                    $target.css('opacity',1);
                    $target.find('.loadErrorCon').show().find('a').text('获得视频信息失败，'+txt).show();
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
                    callback();
                },
                eCallback:function(txt){
                    $target.css('opacity',1);
                    $target.find('>.loadErrorCon').show().find('a').text('获得头条信息失败，'+txt).show();
                    callback();
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
                    that.fillDetailCommentInfo(result);
                    callback&&callback.call(that);
                },
                eCallback:function(txt){
                    $target.find('>.loadErrorCon').show().find('a').text('获得头条信息失败，'+txt).show();
                    callback&&callback.call(that)();
                }
            });
        },

        /*填充我的评论信息*/
        fillDetailCommentInfo:function(result){
            var data=result.data,$totoalNum=this.$wrapper.find('#commentNum');
            if(!data || data.length==0){
                $totoalNum.text(0);
                return;
            }
            $totoalNum.text(data.length);

            /*具体的评论信息*/
            var len=data.length,
                str='',item,userInfo,dateTime;
            for(var i=0;i<len;i++){
                item=data[i];
                userInfo=item.userInfo;
                dateTime=this.getDiffTime(new Date(item.create_time*1000));   //得到发表时间距现在的时间差
                str+='<li>'+
                        '<div class="imgCon">'+
                            '<img src="'+userInfo.avatar128+'"/>'+
                        '</div>'+
                        '<div class="commentCon">'+
                            '<div class="commentHead">'+
                                '<span class="commentNickname">'+userInfo.nickname+'</span>'+
                                '<span class="rightItem starsCon">'+
                                    this.getStarInfoByScore(item.comprehensive_score | 0)+
                                '<div style="clear: both;"></div>'+
                                '</span>'+
                            '</div>'+
                            '<div class="content">'+item.comment+'</div>'+
                            '<div class="publicTime">发表于'+dateTime+'</div>'+
                        '</div>'+
                     '</li>';
            }
            this.$wrapper.find('.studentCommentDetail').html(str);
        },


        /*
        *加载等待,
        *para:
        * status - {num} 状态控制 码
        * 0.显示加载等待;  1 隐藏等待; -1加载失败，重新加载
        */
        controlLoadingTips:function(status,txt){
            var $target=$('#loadingTip'),
                $img=$target.find('.loadingImg'),
                $error=$img.next().hide();
            if(status==0){
                $target.css('z-index',1);
                $img.addClass('active');
            }
            else if(status==1){
                $target.css('z-index',-1);
                $img.removeClass('active');
            }else{
                $img.removeClass('active');
                $error.text(txt).show();
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
            var $target=this.$wrapper.find('.mainItemTeacherPower');
            if(scrollTop>=arrScrollTop[0] &&
                scrollTop<arrScrollTop[1] &&
                $target.attr('data-loading')=='false'){
                    var flag=$target.attr('data-loaded');
                    $target.attr('data-loading','true');
                    if(flag=='false') {
                        this.loadMyTeachersInfo(function(){
                            $target.attr({'data-loaded':'true','data-loading':'false'});
                        });
                        this.loadMyVideoInfo(function(){
                            $target.attr({'data-loaded':'true','data-loading':'false'});
                        });
                    }
                    return;
            }

            //加载我的评分
            var $target=this.$wrapper.find('.mainItemCompresAsse');
            if(scrollTop>=arrScrollTop[1] && $target.attr('data-loading')=='false'){
                var flag=$target.attr('data-loaded');
                $target.attr('data-loading','true');
                if('false'==flag) {
                    this.loadMyCompresAsseinfo(function(){
                        $target.attr({'data-loaded':'true','data-loading':'false'});
                    });

                    //加载评论内容
                    this.loadDetailCommentInfo(this.pageIndex,function(){
                        $target.attr({'data-loaded':'true','data-loading':'false'});
                    });
                }


                return;
            }

            //加载更加多评论内容
            if ($(target).scrollTop() == height && !$(target).hasClass('loadingData')) {  //滚动到底部
                if(this.pageIndex>=this.pageSize){
                    return;
                }
                $(target).addClass('loadingData');
                this.loadDetailCommentInfo(this.pageIndex,function(){
                    this.pageIndex++;
                    $target.attr({'data-loaded':'true','data-loading':'false'});
                });
            }
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
            $target.css({'height':h+'px','left':($('body').width()-bw)/2});
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

    };

    return OrgBasicInfo;
});