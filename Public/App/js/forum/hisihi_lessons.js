/**
 * Created by jimmy on 2015/11/18.
 */

var hisihiLessons = function ($wrapper,urlObj) {
    this.separateOperation();
    this.$wrapper = $wrapper;
    this.controlLoadingPos();
    this.urlObj=urlObj;
    this.pageIndex = 1;
    this.pageSize = 10;
    this.totalPage=1;
    this.loadData(1);
    var that=this;
    this.$wrapper.scroll($.proxy(this,'scrollContainer'));  //滚动加载更多数据
    this.$wrapper.on('click','.loadError',function(){   //重新加载数据
        $(this).hide();
        that.loadData(that.pageIndex);
    });
};

hisihiLessons.prototype = {

    controlLoadingPos:function(){
       var $loading = this.$wrapper.find('.loadingResultTips'),
           w=$loading.width(),
           h=$loading.height(),
           dw=this.$wrapper.width(),
           dh=this.$wrapper.height();
        $loading.css({'top':(dh-h)/2,'left':(dw-w)/2,'opacity':'1'});
    },


    separateOperation:function(){
        var operation=getDeviceType();
        if(operation.mobile){
            if(operation.android) {
                //如果方法存在
                if (typeof AppFunction != "undefined") {
                    AppFunction.showShareView(false);  //调用安卓的方法，控制分享按钮不可用
                }
            }
            else if(operation.ios){
                //var userInfo = getUser_iOS();
                //alert(JSON.stringify(userInfo));
            }
        }
    },

    /*
     *加载新闻列表数据
     * para:
     * pageIndex - {int} 当前的页码数
     */
    loadData: function (pageIndex,callback) {
        var that=this;
        this.getDataAsync(pageIndex,callback);
    },

    /*
     *向服务器请求新闻列表数据，计算总的页码数，并填充内容
     * para:
     * pageIndex - {int} 当前的页码数
     */
    getDataAsync: function (pageIndex,callback) {
        if(pageIndex>this.totalPage){
            return;
        }

        //等待图片  区分两种情况：第一次加载的时候，等待图时居中的图；加载更多的时候，是在列表的底部
        var $loadinngImgTarget,
            $loadinngMore=this.$wrapper.find('.loadingMoreResultTips'),
            $loadingError,
            $loadingMain;
        if(pageIndex==1){
            $loadinngImgTarget=this.$wrapper.find('.loadingResultTips');
        }else{
            $loadinngImgTarget=$loadinngMore;
        }
        $loadinngImgTarget.addClass('active').show();  //显示加载效果
        $loadingMain=$loadinngImgTarget.find('.loadingMoreResultTipsMain').show();  //加载提示对象
        $loadingError=$loadinngImgTarget.find('.loadError');  //加载失败对象
        var tempObj = {
                page: pageIndex,
                count: this.pageSize
            },
            url = this.urlObj.server_url + '/newsList',
            that = this;

        var ajaxTimeoutTest=$.ajax({
            url: url,  //请求的URL
            timeout: 10000, //超时时间设置，单位毫秒
            type: 'post',  //请求方式，get或post
            data:tempObj,
            dataType: 'json',//返回的数据格式
            success: function (result) { //请求成功的回调函数
                $loadinngImgTarget.removeClass('active');  //去掉active类，防止css3动画
                if(result.success) {
                    $loadinngImgTarget.hide();
                    that.totalPage=Math.ceil(result.totalCount/that.pageSize);
                    that.pageIndex++;
                    $loadinngMore.before(that.getNewsContent(result.data));
                    //控制图片的显示，按比例显示
                    that.$wrapper.find('.newsListContainer img').unbind('load').bind("load",function(){
                        $(this).css('opacity','1');
                    });
                }else{
                    $loadingMain.hide();
                    $loadingError.show();
                }
            },
            complete : function(XMLHttpRequest,status){    //请求完成后最终执行参数
                $loadinngImgTarget.removeClass('active');
                if(status=='timeout' || status=='error'){   //超时,status还有success,error等值的情况
                    ajaxTimeoutTest.abort();
                    $loadingMain.hide();
                    $loadingError.show();
                }
                callback && callback();
            }
        });
    },

    /*
     *填充内容
     * para:
     * data - {array} 结果数据
     * return
     * str - {string} 内容字符串
     */
    getNewsContent:function(data){
        //data=[
        //    {"id":"5472","title":"内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试","create_time":"1447299691","view_count":"89757","is_out_link":"0","link_url":"","url":this.urlObj.server_url+"/toppostdetailv2/post_id/5510","pic_url":"http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-11-12/56440a5ccd24e.jpg"},
        //    {"id":"5471","title":"新闻测试","create_time":"1447295771","view_count":"12043","is_out_link":"0","link_url":"","url":this.urlObj.server_url+"/toppostdetailv2/post_id/5513","pic_url":"http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-11-12/56440a5ccd24e.jpg"}
        //];
        var str = '',title, len = data.length, item,dateStr;
        for (var i = 0; i < len; i++) {
            item = data[i];
            title=this.substrLongStr(item.title,25);
            dateStr=this.getTimeFromTimestamp(item.create_time);
            str += '<li class="newsLiItem">'+
                    '<div class="coverBorderContainer"></div>'+
                    '<a href="'+item.url+'">' +
                    '<div class="left">' +
                    '<img src="' + item.pic_url + '"/>' +
                    '</div>' +
                    '<div class="right">' +
                    '<div class="rightHeader">' +
                    '<p>'+title+'</p>' +
                    '</div>' +
                    '<div class="rightBottom">'+
                    '<div class="rightBottomLeft">'+
                    //'<i class="viewTimesIcon"><img src="'+this.urlObj.img_url+'/viewTimes.png"/></i>'+
                    '<span class="viewTimesIcon">人气：</span>'+
                    '<span>'+item.view_count +'</span>'+
                    '</div>'+
                    '<div class="rightBottomRight">'+ dateStr + '</div>'+
                    '</div>' +
                    '</div>' +
                    '</a>'+
                '</li>';
        }
        return str;
    },

    /*
     *滚动加载更多的数据
     * 通过滚动条是否在底部来确定
     * 同时通过 loadingData 类 来防止连续快速滚动导致的重复加载
     */
    scrollContainer:function(e){
        var target= e.currentTarget,
            height = target.scrollHeight - $(target).height();
        if ($(target).scrollTop() == height && !$(target).hasClass('loadingData')) {  //滚动到底部
            $(target).addClass('loadingData');
            this.loadData(this.pageIndex,function(){
                $(target).removeClass('loadingData');
            });
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

/*
*IOS调用  控制分享按钮的可用性
*/
function canShare(){
    return false;
}