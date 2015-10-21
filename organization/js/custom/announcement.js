/**
 * Created by Jimmy on 2015/10/21.
 */

//今日公告

define(function () {
    var TodayAnnoucement = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.pageIndex=0;  //当前页数
        this.pageSize=0;  //总页数
        this.perPageSize=10; //每页显示的条数
        this.loadData(0);

        //事件定义
        this.$wrapper.on('ul li','click', $.proxy(this.showDetailAnnounceInfo));
        this.$wrapper.on('scroll', $.proxy(this.loadData));

    };

    TodayAnnoucement.prototype={
        loadData:function(){
            this.getDataAsync(function(data){
                data;
            });
        },
        getDataAsync:function(callback){
            var tempObj={
                pageIndex:this.pageIndex,
                count:this.perPageSize
            };
            $.post('../../',tempObj,callback);
        },

        /*
        *显示公告的基本内容，
        * 包括标题和时间
        */
        showDetailAnnounceInfo:function(data){
            var str='';
            $.each(data,function(item){
                str+='<tr><td>item.title</td><td>item.time</td></tr>';
            });
            this.$wrapper.append(str);
        }
    };

    return TodayAnnoucement;

});