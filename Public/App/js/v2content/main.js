/**
 * Created by jimmy on 2016/1/27.
 */

/**推荐阅读基础类**/
var MoreInfoBase=function(){

};

MoreInfoBase.prototype={

    /*请求数据*/
    getDataAsync:function(paras){
        if(!paras.type){
            paras.type='post';
        }
        if(!paras.url){
            return;
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
                        that.controlLoadingTips(-1);
                    }
                }
                //超时
                else if (status == 'timeout') {
                    loginXhr.abort();
                    that.controlLoadingTips(-1);
                    paras.eCallback();
                }
                else {
                    that.controlLoadingTips(-1);
                    paras.eCallback()
                }
            }
        });
    },

    /*
     *加载等待,
     *para:
     * status - {num} 状态控制 码
     * 0.显示加载等待;  1 隐藏等待; -1隐藏转圈图片，显示加载失败，重新刷新的按钮;
     */
    controlLoadingTips:function(status){
        var $target=$('#loadingTip'),
            $img=$target.find('.loadingImg'),
            $a=$target.find('.loadError');
        if(status==1){
            $target.show();
            $img.addClass('active');
        }else if(status==-1){
            $target.show();
            $img.removeClass('active');
            $a.show();
        }
        else{
            $target.hide();
            $img.removeClass('active');
        }
    },

    /*
     *字符串截取
     * para
     * str - {string} 目标字符串
     * len - {int} 最大长度
     */
    substrLongStr: function (str, len) {
        if (str.length > len) {
            str = str.substr(0, parseInt(len - 1)) + '……';
        }
        return str;
    },

    getTimeFromTimestamp:function (dateInfo, dateFormat) {
        return new Date(parseFloat(dateInfo) * 1000).format(dateFormat);
    },

};


/********* 热门头条  热门新闻  热门教程 基本model**********/
var NormalInfo=function($wrapper,paras){
    this.$wrapper=$wrapper;
    this.paras=paras;
    this.init()
};

NormalInfo.prototype=new MoreInfoBase();
NormalInfo.constructor=NormalInfo;
var nPro= NormalInfo.prototype;

nPro.init=function(){
    //添加 unFilledIn 类，方便滚动加载时区分
    var classNames=this.paras.className;
    if(this.paras.loadNow) {
        this.fillInInfo();
    }else{
        classNames+=' unFilledIn'
    }
    this.$wrapper.addClass(classNames);
};

nPro.loadData=function(callback){
    var that=this;
    var paras={
        url:this.paras.listUrl,
        type:'get',
        paraData:{page:1, count:3},
        sCallback:function(data){
            callback && callback();
            that.fillInInfo.call(that,data);
        },
        eCallback:function(){

        },
    };
    this.getDataAsync(paras);
};

nPro.errorInfo=function(){
    //loadingImg
};

//填充内容
nPro.fillInInfo=function(data){
    var str= this.getContentStr(data);
    var allStr='<div class="basicHeaderWithArrow">'+
                    '<span class="titleInfo">'+this.paras.title+'</span>'+
                    '<i class="spiteBgOrigin arrow"></i>'+
                    '<span class="moreTip">更多</span>'+
                '</div>'+
                '<div class="loadErrorCon">'+
                    '<a class="loadError" href="javascript:void(0)" data-index="4"></a>'+
                '</div>'+
                '<ul class="mainContentUl">'+str+'</ul>';
    this.$wrapper.html(allStr);
    //控制图片的显示，按比例显示
    this.$wrapper.find('.newsLiItem .left>img').unbind('load').bind("load",function(){
        $(this).css('opacity','1');
    });
};

nPro.getContentStr=function(result){
    if(this.paras.title=='嘿设汇新闻') {
        return this.getContentStrNews(result);
    }
    if(this.paras.title=='热门头条') {
        return this.getContentStrTop(result);
    }
    if(this.paras.title=='热门快捷键') {
        return this.getContentStrForKey(result);
    }
    if(this.paras.title=='热门教程') {
        return this.getContentStrLesson(result);
    }
    if(this.paras.title=='大家都在参加') {
        return this.getContentStrActivity(result);
    }
};


//新闻 内容字符串
nPro.getContentStrNews=function(result){
    var str = '',title,item,dateStr;
    if(!result || result.data.length==0){
        str='<div class="nonData">暂无内容</div>';
    }
    else {
        data=result.data;
        var len = data.length;
        for (var i = 0; i < len; i++) {
            item = data[i];
            title = this.substrLongStr(item.title, 25);
            dateStr = this.getTimeFromTimestamp(item.create_time);
            str += '<li class="newsLiItem">' +
                    '<a href="' +window.hisihiUrlObj.link_url + item.url + '">' +
                        '<div class="left spiteBgOrigin">' +
                            '<img src="' + item.pic_url + '"/>'+
                        '</div>' +
                        '<div class="right">' +
                            '<div class="rightHeader">' +
                                '<p>' + title + '</p>' +
                            '</div>' +
                            '<div class="rightBottom">' +
                                '<div class="rightBottomLeft">' +
                                    dateStr +
                                '</div>'+
                            '<div class="rightBottomRight">' +
                            '<i class="viewTimesIcon spiteBg"></i>' +
                            '<span class="viewTimesIcon">' + item.view_count + '</span>' +
                            '</div>'+
                            '</div>' +
                        '</div>' +
                    '</a>' +
                '</li>';
        }
    }
    return str;
};

//头条 内容字符串
nPro.getContentStrTop=function(result){
    var str = '',title,item,dateStr;
    if(!result || result.course.length==0){
        str='<div class="nonData">暂无内容</div>';
    }
    else {
        data=result.course;
        var len = data.length;
        for (var i = 0; i < len; i++) {
            item = data[i];
            title = this.substrLongStr(item.title, 25);
            dateStr = this.getTimeFromTimestamp(item.update_time);
            str += '<li class="newsLiItem">' +
                    '<a href="' +window.hisihiUrlObj.link_url+ item.content_url + '">' +
                '<div class="left spiteBgOrigin">' +
                    '<img src="' + item.img + '"/>' +
                '</div>' +
                '<div class="right">' +
                '<div class="rightHeader">' +
                '<p>' + title + '</p>' +
                '</div>' +
                '<div class="rightBottom">' +
                '<div class="rightBottomLeft">' +
                    dateStr +
                '</div>'+
                '<div class="rightBottomRight">' +
                    '<i class="viewTimesIcon spiteBg"></i>' +
                    '<span class="viewTimesIcon">' + item.view + '</span>' +
                '</div>'+
                '</div>' +
                '</div>' +
                '</a>' +
                '</li>';
        }
    }
    return str;
};

//热门教程 内容字符串
nPro.getContentStrLesson=function(result){
    var str = '',title,item,dateStr;
    if(!result || result.coursesList.length==0){
        str='<div class="nonData">暂无内容</div>';
    }
    else {
        data=result.coursesList;
        var len = data.length;
        for (var i = 0; i < len; i++) {
            item = data[i];
            title = this.substrLongStr(item.title, 25);
            dateStr = this.getTimeFromTimestamp(item.update_time);

            str += '<li class="newsLiItem">' +
                '<a href="'+window.hisihiUrlObj.server_url+'/course/courseDetail/type/view/id/' + item.id + '">' +
                '<div class="left spiteBgOrigin">' +
                    '<img src="' + item.img + '"/>' +
                    '<div class="btnPlay spiteBgOrigin"></div>'+
                '</div>' +
                '<div class="right">' +
                '<div class="rightHeader">' +
                '<p>' + title + '</p>' +
                '</div>' +
                '<div class="rightBottom">' +
                    '<div class="rightBottomLeft">' +
                        dateStr +
                    '</div>'+
                    '<div class="rightBottomRight">' +
                        '<i class="viewTimesIcon spiteBg"></i>' +
                        '<span class="viewTimesIcon">' + item.ViewCount + '</span>' +
                    '</div>'+
                '</div>' +
                '</div>' +
                '</a>' +
                '</li>';
        }
    }
    return str;
};

//活动 内容字符串
nPro.getContentStrActivity=function(result){
    var str = '',title,item,dateStr;
    if(!result || result.data.length==0){
        str='<div class="nonData">暂无内容</div>';
    }
    else {
        data=result.data;
        var len = data.length;
        for (var i = 0; i < len; i++) {
            item = data[i];
            title = this.substrLongStr(item.title, 25);
            var isEnd=new Date(parseFloat(item.eTime) * 1000) -new Date()>0;
            dateStr = this.getTimeFromTimestamp(item.eTime);
            var statueStr=isEnd?'(进行中)':'(已结束)';
            var strBottomRight = '<div class="rightBottomLeft">' +
                '截稿时间：'+dateStr + '&nbsp;'+statueStr+
                '</div>';
            str += '<li class="newsLiItem">' +
                '<a href="' +window.hisihiUrlObj.server_url+ '/event/competitioncontent/type/view/id/'+item.id + '">' +
                '<div class="left spiteBgOrigin">' +
                '<img src="' + item.pic_path + '"/>' +
                '</div>' +
                '<div class="right">' +
                '<div class="rightHeader">' +
                '<p>' + title + '</p>' +
                '</div>' +
                '<div class="rightBottom">' +
                    '<div class="rightBottomLeft">' +
                        strBottomRight +
                    '</div>'+
                '</div>' +
                '</div>' +
                '</a>' +
                '</li>';
        }
    }
    return str;
};

//快捷键 内容字符串
nPro.getContentStrForKey=function(result){
    var str = '';
    if(!result || result.data.length==0){
        str='<div class="nonData">暂无内容</div>';
    }
    else {
        data = result.data;
        var len = data.length;
            str = '';
        for (var i = 0; i < len; i++) {
            var item = data[i];
            str += '<li class="shortKeyLiItem">' +
                        '<a href="'+window.hisihiUrlObj.server_url+'/HotKeys/share/type/' + item.text + '">'+
                            '<img src="'+item.icon+'"/>'+
                        '</a>' +
                    '</li>';
        }
        str += '<div style="clear:both;"></div>';
    }
    return str;
};



/***********业务逻辑*************/
var basicLogicClass=function(type){

    this.allContent=[
            {name:'热门头条',url:window.hisihiUrlObj.server_url+'/public/topList1',loadNow:false,className:'hotTop'},
            {name:'热门快捷键',url:window.hisihiUrlObj.server_url+'/HotKeys/sort',loadNow:false,className:'hotShortcutKey'},
            {name:'热门教程',url:window.hisihiUrlObj.server_url+'/Course/recommendcourses',loadNow:false,className:'hotLesson'},
            {name:'大家都在参加',url:window.hisihiUrlObj.server_url+'/event/competitionList',loadNow:false,className:'activity'},
            {name:'嘿设汇新闻',url:window.hisihiUrlObj.server_url+'/forum/newsList',loadNow:false,className:'hisihiNews'}
        ];
    this.names=['头条','快捷键','教程','比赛','新闻'];

    this.normalInfoObjArr=[];
    this.resetAllContentArr(type);  //根据当前文章的类型 重新调整内容数组的顺序
    this.isFromApp=false;
    /*操作设备信息*/
    this.deviceType=getDeviceType();
    this.separateOperation();
    this.$wrapper=$('.headlines-more');
    this.mainContentHeight=this.$wrapper.height();
    $('.headlines-box').scroll($.proxy(this,'scrollContainer'));  //滚动加载更多数据
    var eventName='touched';
    if(!this.deviceType.mobile){
        eventName='click';
    }
    $('.loadError').on(eventName,function(){});   //重新加载
    this.controlCommentBoxStatus();
};

basicLogicClass.prototype={

    /*根据当前文章的类型 重新调整内容数组的顺序*/
    resetAllContentArr:function(type){
        var index= $.inArray(type,this.names);
        var tempItem = this.allContent.splice(index,1)[0];
        this.allContent.splice(0,0,tempItem);
        tempItem=null;
        var $wrapper=$('.moreItem'),
            normalInfoObj=null;
        for(var i=0;i<this.allContent.length;i++){
            var item=this.allContent[i];
            var para={
                listUrl:item.url,
                title:item.name,
                loadNow:item.loadNow,
                className:item.className
            };
            normalInfoObj = new NormalInfo($wrapper.eq(i),para);
            this.normalInfoObjArr.push(normalInfoObj);
        }
    },

    /*
     * 滚动加载更多的数据
     * 通过滚动条是否在底部来确定
     * 同时通过 loadingData 类 来防止连续快速滚动导致的重复加载
     */
    scrollContainer:function(e){
        var target= e.currentTarget,
            height = target.scrollHeight - $(target).height(),
            scrollTop=$(target).scrollTop(),
            that=this,
            arrScrollTop=[this.mainContentHeight - 500,this.mainContentHeight];

        //加载更加多评论内容
        var $target=this.$wrapper.find('.unFilledIn');
        if($target.length==0){
            return;
        }

        var $Itemtarget=$target.eq(0);
        if (scrollTop >= height -170 &&
            !that.$wrapper.hasClass('loadingData')) {  //滚动到底部
                var index=$Itemtarget.index();
                that.$wrapper.addClass('loadingData');
                this.normalInfoObjArr[index].loadData(function(){
                    that.$wrapper.removeClass('loadingData');
                    $Itemtarget.removeClass('unFilledIn');
                });
        }
    },

    /*
     *获得用户的信息 区分安卓和ios
     */
    separateOperation:function(callback){

        var userStr='',that=this;
        if(this.deviceType.mobile){
            if (this.deviceType.android) {
                //如果方法存在
                if(typeof AppFunction !="undefined") {
                    this.isFromApp=true;
                }
            }
            else if (this.deviceType.ios) {
                //如果方法存在
                if (typeof getUser_iOS !="undefined") {
                    this.isFromApp=true;
                }
            }
            if(userStr!=''){
                this.userInfo=JSON.parse(userStr);
                callback&&callback.call(that);
            }else{

            }
        }
        else{
            callback&&callback.call(that);
        }
    },

    /*
     * 控制评论框的显示状态，通过 session_id 是否 为空 来
     * 三种情况：
     * 1.用户已经登录，则直接显示评论框，并且主要容器的高度 不 为100%
     * 2.用户未登录，不显示评论框，主要容器的高度  为 100%
     * 3.用户不来源于app，而是从其他的地方进入，不显示评论框，显示下载条，主要容器的高度  不为 100%
     * 如果用户没有登录，   则不显示;并将内容框控制到最高
     */
    controlCommentBoxStatus:function(){
        var $target=$('.headlines-box');
        //来源于app
        if(this.isFromApp){
            this.$wrapper.hide();
            $target.find('.detailed-main').css('margin-bottom','0');
            return;
        }
        //来源于普通的页面
        else {
            this.controlCoverFootStyle();
        }
    },

    /*控制底部logo的位置样式*/
    controlCoverFootStyle:function(){
        var $target = $('#downloadCon'),
            $a=$target.find('a'),
            aw=$a.width(),
            ah=aw*0.40,
            bw=$target.width(),
            h= bw*120/750;
        $target.css({'height':h+'px','left':($('body').width()-bw)/2,'opacity':1});
        this.$wrapper.parent().css('bottom',h+'px');
        var fontSize='16px';
        if(bw<375){
            fontSize='14px';
        }
        $a.css({'top':(h-ah)/2,'height':ah+'px','line-height':ah+'px','font-size':fontSize});
    },

};

$(function(){
    var type=$('.headlines-more').data('type');
    new basicLogicClass(type);
});

