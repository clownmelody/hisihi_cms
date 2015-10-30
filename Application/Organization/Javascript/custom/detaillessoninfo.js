/**
 * Created by Jimmy on 2015/10/21.
 */

//今天公告

define(['jquery','util','jqueryuploadify'],function () {
    var LessonDetailInfo = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization/';
        this.course_id=this.$wrapper.data('id');
        this.loadData();
        //事件注册
        var that=this;

    };
    //
    LessonDetailInfo.prototype={
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
                if(data.success) {
                    data = data.data;
                    that.$wrapper.cornerLoading('hideLoading');
                    var str = that.showAllVideosInfo(data);
                    this.$wrapper.find('#allLessonVideoCon').html(str);
                }else{
                    alert('数据加载失败！');
                }
            });


        },

        /*获得当前教程的所有视频*/
        getDataAsync:function(callback){
            url=this.basicApiUrl+'/getCourseVideoList',
                that=this;
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: {course_id:this.course_id},
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
         *得到视频信息 html字符串
         * @para
         * data -{array} 视频数组
         */
        showAllVideosInfo:function(data){
            var str='',
                date;
            $.each(data,function(index){
                date=new Date(parseFloat(this.update_time)* 1000).format('yyyy.MM.dd');
                str+='<tr>'+
                        '<th>'+index+'</th>'+
                        '<td class="title">Photoshop 大圣归来手绘板原稿</td>'+
                        '<td class="time">2015-03-24-18:24</td>'+
                        '<td>'+
                        '<span class="icon icon-look"></span>'+
                        '</td>'+
                    '</tr>';
            });
            return str;
        },


    };
    var $wrapper=$('#lessonDetailInfoWrapper');
    if($wrapper.length>0) {
        new LessonDetailInfo($wrapper);
    }

});