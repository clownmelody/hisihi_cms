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


/*热门头条  热门新闻  热门教程*/
var NormalInfo=function($wrapper,paras){
    this.$wrapper=$wrapper;
    this.paras=paras;  //'http://hisihi.com/api.php?s=/forum/newsList',
};

NormalInfo.prototype=new MoreInfoBase();
NormalInfo.constructor=NormalInfo;
var nPro= NormalInfo.prototype;

nPro.init=function(){
    var paras={
        url:this.paras.listUrl,
        type:'get',
        sCallback: $.proxy(this,'fillInInfo'),
        eCallback:function(){},
    };
    this.getDataAsync(paras);
};

//填充内容
nPro.fillInInfo=function(data){
    var str = this.getContentStr(data);
    this.$wrapper.find('mainContentUl').html(str);
};

//内容字符串
nPro.getContentStr=function(data){
    var str = '',title, len = data.length, item,dateStr;
    if(!data || data.length==0){
        str='<div class="nonData">暂无内容</div>';
    }
    else {
        for (var i = 0; i < len; i++) {
            item = data[i];
            title = this.substrLongStr(item.title, 25);
            dateStr = this.getTimeFromTimestamp(item.create_time);
            str += '<li class="newsLiItem">' +
                '<div class="coverBorderContainer"></div>' +
                '<a href="' + item.url + '">' +
                '<div class="left">' +
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


$(function(){
    var type=$('.headlines-more').data('type');
    //var contentOrder
});

