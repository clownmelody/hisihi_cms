/**
 * Created by jimmy on 2015/11/18.
 */

var hisihiNews = function ($wrapper,urlObj) {
    this.separateOperation();
    this.$wrapper = $wrapper;
    this.urlObj=urlObj;
    this.pageIndex = 1;
    this.pageSize = 20;
    this.totalPage=1;
    this.loadData(1);
    this.$wrapper.scroll($.proxy(this,'scrollContainer'));
};

hisihiNews.prototype = {


    separateOperation:function(){
        var operation=browserType();
        if(operation.mobile){
            if(operation.android){
                AppFunction.showShareView(false);  //调用安卓的方法，控制分享按钮不可用
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
    loadData: function (pageIndex) {
        var that=this;
        this.getDataAsync(pageIndex);
    },

    /*
     *向服务器请求新闻列表数据，计算总的页码数，并填充内容
     * para:
     * pageIndex - {int} 当前的页码数
     */
    getDataAsync: function (pageIndex) {
        if(pageIndex>this.totalPage){
            return;
        }
        $loadinng=this.$wrapper.find('#loadingTip').show();
        var tempObj = {
                pageIndex: pageIndex,
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
                $loadinng.hide();
                if(result.success) {
                    that.totalPage=Math.ceil(result.totalCount/that.pageSize);
                    that.pageIndex++;
                    $loadinng.before(that.getNewsContent(result.data));
                }else{
                    $loadinng.next().text(result.massage).show().delay(2000).hide(0);
                }
            },
            complete : function(XMLHttpRequest,status){    //请求完成后最终执行参数
                $loadinng.hide();
                if(status=='timeout'){   //超时,status还有success,error等值的情况
                    ajaxTimeoutTest.abort();
                    $loadinng.next().text('请求超时').show();
                }
                else if(status=='error'){
                    var tips='网络错误';
                    ajaxTimeoutTest.abort();
                    if(XMLHttpRequest.status=='404') {
                        tips='请求地址错误';
                    }
                    $loadinng.next().text(tips).show();
                }
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
        data=[
            {"id":"5472","title":"内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试内页帖图片测试","create_time":"1447299691","view_count":"89757","is_out_link":"0","link_url":"","url":this.urlObj.server_url+"/toppostdetailv2/post_id/5472","pic_url":"http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-11-12/56440a5ccd24e.jpg"},
            {"id":"5471","title":"新闻测试","create_time":"1447295771","view_count":"12043","is_out_link":"0","link_url":"","url":this.urlObj.server_url+"/toppostdetailv2/post_id/5471","pic_url":"http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-11-12/56440a5ccd24e.jpg"}
        ];
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
                    '<i class="viewTimesIcon"><img src="'+this.urlObj.img_url+'/viewTimes.png"/></i>'+
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
     */
    scrollContainer:function(e){
        var target= e.currentTarget,
            height = target.scrollHeight - $(target).height();
        if ($(target).scrollTop() == height) {  //滚动到底部
            this.loadData(this.pageIndex);
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