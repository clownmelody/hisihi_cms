/**
 * Created by Jimmy on 2015/10/21.
 */

//今天公告

define(['jquery','util'],function () {
    var TodayAnnoucement = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.pageIndex=0;  //当前页
        this.pageSize=0;  //总页数
        this.perPageSize=10; //每次加载数目
        this.loadData(0);
        this.controlContainerHeight();
        //事件注册
        var that=this;
        this.$wrapper.on('#announcesContainer ul li','click', $.proxy(this,'showDetailAnnounceInfo'));
        this.$wrapper.parent().scroll(function(){
            that.scrollContainer.call(that,this);
        });

    };

    TodayAnnoucement.prototype={
        loadData:function(){
            /*data: Array[3]
            error_code: 0
            message: "获取公告信息列表成功"
            success: true
            totalCount: "3"
            */
            if (this.$wrapper.data('cornerLoading')) {
                this.$wrapper.cornerLoading('showLoading');
            } else {
                this.$wrapper.cornerLoading();
            }
            var that=this;
            this.getDataAsync(function(data){
                that.$wrapper.cornerLoading('hideLoading');
                if(data.success) {
                    data = data.data;
                    that.showShortAnnounceInfo(data);
                }else{
                    alert(data.message);
                }
            });
        },

        getDataAsync:function(callback){
            var tempObj={
                pageIndex:this.pageIndex,
                count:this.perPageSize
                },
            url=window.urlObject.apiUrl+'/api.php?s=/Organization/getNotice',
                that=this;
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: {page:1,count:50},
                callback:function(data){
                    callback.call(that,data)
                }
            });
        },

        /*
        *滚动加载更多的数据
        * @para:
        * target - {object} javascript 对象
        */
        scrollContainer:function(target){
            var height = target.scrollHeight - $(target).height();
            if ($(target).scrollTop() == height) {  //滚动到底部
                this.loadData();
                this.$wrapper.find('.loadingData').show().delay(2000).hide(0);
            }
        },


        /*
        *显示简要的公告信息
        * @para
        * data -{array} 公告数组
        */
        showShortAnnounceInfo:function(data){
            var str='',
                date;
            if(data) {
                $.each(data, function () {
                    date = new Date(parseFloat(this.update_time) * 1000).format('yyyy.MM.dd');
                    str += '<li class="anListItem" data-id="' + this.id + '"><a href="' + this.detail_url + '" target="_blank"> <span>' + this.title + '</span><span>' + date + '</span></a></li>';
                });
            }else{
                str='<p class="noDataForQuery">暂时没有公告信息</p>';
            }
            this.$wrapper.find('#announcesContainer .clearDiv').before(str);
        },

        /*
         控制容器的高度
         */
        controlContainerHeight:function(){

        },

    };
    var $wrapper=$('#announcesWrapper');
    if($wrapper.length>0) {
        new TodayAnnoucement($wrapper);
    }
    //var todayAnnouncement=new TodayAnnoucement($('.anWrapper'));
    //return todayAnnouncement;

});