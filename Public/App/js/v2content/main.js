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
            $target.show();
            $img.addClass('active');
        } else{
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
        eCallback:function(){},
    };
    this.getDataAsync(paras);
};

//填充内容
nPro.fillInInfo=function(data){
    var str='';
    if(this.paras.className!='hotShortcutKey') {
         str = this.getContentStr(data);
    }else{
        str = this.getContentStrForKey(data);
    }
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

//内容字符串
nPro.getContentStr=function(result){
    var str = '',title,item,dateStr;
    if(!result || result.data.length==0){
        str='<div class="nonData">暂无内容</div>';
    }
    else {
        data=result.data;
        var len = data.length,
            btnStr='',
            strBottomRight='',
            isLesson=this.paras.className=='hotLesson';
        if(this.paras.title=='热门教程'){
            btnStr='<div class="btnPlay spiteBgOrigin"></div>';
        }
        for (var i = 0; i < len; i++) {
            item = data[i];
            title = this.substrLongStr(item.title, 25);
            dateStr = this.getTimeFromTimestamp(item.create_time);
            if(!isLesson) {
                strBottomRight = '<div class="rightBottomLeft">' +
                                    dateStr +
                                 '</div>'+
                                 '<div class="rightBottomRight">' +
                                    '<i class="viewTimesIcon spiteBg"></i>' +
                                    '<span class="viewTimesIcon">' + item.view_count + '</span>' +
                                 '</div>';
            }else{
                var statueStr=item.end?'(进行中)':'(已结束)';
                strBottomRight = '<div class="rightBottomLeft">' +
                                    '截稿时间：'+dateStr + '&nbsp;'+statueStr+
                                 '</div>';
            }
            str += '<li class="newsLiItem">' +
                    '<a href="' + item.url + '">' +
                        '<div class="left spiteBgOrigin">' +
                            '<img src="' + item.pic_url + '"/>' +
                            btnStr+
                        '</div>' +
                        '<div class="right">' +
                            '<div class="rightHeader">' +
                                '<p>' + title + '</p>' +
                            '</div>' +
                            '<div class="rightBottom">' +
                                strBottomRight+
                            '</div>' +
                        '</div>' +
                    '</a>' +
                '</li>';
        }
    }
    return str;
};

nPro.getContentStrForKey=function(){
    var str = '',
        data=[
                {name:'ps',x:0,y:0,url:''},
                {name:'ai',x:0,y:-1,url:''},
                {name:'cad',x:-1,y:-1,url:''},
                {name:'maya',x:-2,y:0,url:''},
                {name:'ae',x:-1,y:0,url:''},
        ],
        size=65;
    var len = data.length;
    for (var i = 0; i < len; i++) {
        var item=data[i];
        var style='background-position:'+item.x*size +'px '+item.y*size+'px';
        str+='<li class="shortKeyLiItem">'+
                '<a href="' + item.url + '" class="spiteBgOrigin" style="'+style+'"></a>'+
             '</li>';
    }
    str+='<div style="clear:both;"></div>';
    return str;
};



/***********业务逻辑*************/
var basicLogicClass=function(type){

    this.allContent=[
            {name:'热门头条',url:window.hisihiUrlObj.server_url+'/newsList',loadNow:false,className:'hotTop'},
            {name:'热门快捷键',url:window.hisihiUrlObj.server_url+'/newsList',loadNow:false,className:'hotShortcutKey'},
            {name:'热门教程',url:window.hisihiUrlObj.server_url+'/newsList',loadNow:false,className:'hotLesson'},
            {name:'大家都在参加',url:window.hisihiUrlObj.server_url+'/newsList',loadNow:false,className:'activity'},
            {name:'嘿设汇新闻',url:window.hisihiUrlObj.server_url+'/newsList',loadNow:false,className:'hisihiNews'}
        ];
    this.names=['头条','快捷键','教程','比赛','新闻'];

    this.normalInfoObjArr=[];
    this.resetAllContentArr(type);  //根据当前文章的类型 重新调整内容数组的顺序
    this.isFromApp=false;
    this.separateOperation();
    this.$wrapper=$('.headlines-more');
    this.mainContentHeight=this.$wrapper.height();
    $('.headlines-box').scroll($.proxy(this,'scrollContainer'));  //滚动加载更多数据
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
        /*操作设备信息*/
        this.deviceType=getDeviceType();
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

