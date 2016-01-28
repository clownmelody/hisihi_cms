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
            $target.css('z-index',1);
            $img.addClass('active');
        } else{
            $target.css('z-index',-1);
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
    //data=[
    //    {"id":"5472","title":"内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试","create_time":"1447299691","view_count":"89757","is_out_link":"0","link_url":"","url":window.hisihiUrlObj.server_url+"/toppostdetailv2/post_id/5510","pic_url":"http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-11-12/56440a5ccd24e.jpg"},
    //    {"id":"5471","title":"新闻测试","create_time":"1447295771","view_count":"12043","is_out_link":"0","link_url":"","url":window.hisihiUrlObj.server_url+"/toppostdetailv2/post_id/5513","pic_url":"http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-11-12/56440a5ccd24e.jpg"}
    //];
    var str = this.getContentStr(data),
        allStr='<div class="basicHeaderWithArrow">'+
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
        var len = data.length;
        for (var i = 0; i < len; i++) {
            item = data[i];
            title = this.substrLongStr(item.title, 25);
            dateStr = this.getTimeFromTimestamp(item.create_time);
            str += '<li class="newsLiItem">' +
                    //'<div class="coverBorderContainer"></div>' +
                    '<a href="' + item.url + '">' +
                        '<div class="left spiteBgOrigin">' +
                            '<img src="' + item.pic_url + '"/>' +
                        '</div>' +
                        '<div class="right">' +
                            '<div class="rightHeader">' +
                                '<p>' + title + '</p>' +
                            '</div>' +
                            '<div class="rightBottom">' +
                                '<div class="rightBottomLeft">' +
                                    //'<i class="viewTimesIcon"><img src="'+this.urlObj.img_url+'/viewTimes.png"/></i>'+
                                    '<span class="viewTimesIcon">人气：</span>' +
                                    '<span>' + item.view_count + '</span>' +
                                '</div>' +
                                '<div class="rightBottomRight">' + dateStr + '</div>' +
                            '</div>' +
                        '</div>' +
                    '</a>' +
                '</li>';
        }
    }
    return str;
};


/***********业务逻辑*************/
var basicLogicClass=function(type){

    this.allContent=[
            {name:'大家都在参加',url:window.hisihiUrlObj.server_url+'/newsList',loadNow:false,className:'activity'},
            {name:'热门头条',url:window.hisihiUrlObj.server_url+'/newsList',loadNow:false,className:'hotTop'},
            {name:'热门教程',url:window.hisihiUrlObj.server_url+'/newsList',loadNow:false,className:'hotLesson'},
            {name:'热门快捷键',url:window.hisihiUrlObj.server_url+'/newsList',loadNow:false,className:'hotFastKey'},
            {name:'嘿设汇新闻',url:window.hisihiUrlObj.server_url+'/newsList',loadNow:false,className:'hisihiNews'}
        ];
    this.names=['活动','头条','视频','快捷键','新闻'];
    this.normalInfoObjArr=[];
    this.resetAllContentArr(type);  //根据当前文章的类型 重新调整内容数组的顺序
    this.$wrapper=$('.headlines-more');
    this.mainContentHeight=this.$wrapper.height();
    $('.headlines-box').scroll($.proxy(this,'scrollContainer'));  //滚动加载更多数据
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
        if (scrollTop >= height -120 &&
            !that.$wrapper.hasClass('loadingData')) {  //滚动到底部
                var index=$Itemtarget.index();
                that.$wrapper.addClass('loadingData');
                this.normalInfoObjArr[index].loadData(function(){
                    that.$wrapper.removeClass('loadingData');
                    $Itemtarget.removeClass('unFilledIn');
                });
        }
    },
};

$(function(){
    var type=$('.headlines-more').data('type');
    new basicLogicClass();
    //var contentOrder
});

