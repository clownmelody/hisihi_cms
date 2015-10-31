/**
 * Created by Jimmy on 2015/10/21.
 */

//今天公告

define(['jquery','util','jqueryuploadify'],function () {
    var LessonDetailInfo = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
        this.course_id=this.$wrapper.data('cid');
        this.loadData();
        //事件注册
        var that=this;
        this.initUploadify();

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
                    this.$wrapper.find('#allLessonVideoCon').html(str);   //填充视频列表
                    that.fillInBottomBoxInfo(data); //填充基本信息
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

        /*填充下方展示框的内容*/
        fillInBottomBoxInfo:function(data){
            var title=Hisihi.substrLongStr(data.title,26),
                content=Hisihi.substrLongStr(data.content,56),
                category=Hisihi.substrLongStr(data.category_name,20),
                teacherName=Hisihi.substrLongStr(data.teacher_name,20),
                authType=data.auth=='1'?'公开':'私有';
           this.$wrapper.find('#lessonDetailTitleBox').text(title);
           this.$wrapper.find('#lessonDetailContentBox').text(content);
           this.$wrapper.find('#lessonDetailCategoryBox').text(category);
           this.$wrapper.find('#teacherBox').text(teacherName);
           this.$wrapper.find('#authType').text(authType);
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
                date,title='',
                allTitle=data.category_name + ' '+data.title;
            if(allTitle.length>25){
                allTitle=allTitle.substr(0,24)+'……';
            }
            this.$wrapper.find('#lessonDetailTitle').text(allTitle);
            if(!data.video){
                return '';
            }
            $.each(data.video,function(index){
                date=Hisihi.getTimeFromTimestamp(this.create_time);
                title=Hisihi.substrLongStr(this.name,26);
                str+='<tr>'+
                        '<th>'+ parseInt(index+1)+'</th>'+
                        '<td class="title" title="'+this.name+'">'+title+'</td>'+
                        '<td class="time">'+date+'</td>'+
                        '<td>'+
                            '<span class="icon icon-look"></span>' + this.view_count +
                        '</td>'+
                    '</tr>';
            });
            return str;
        },

        /*
         *初始化头像上传插件
         */
        initUploadify:function() {
            var that=this;
            url='http://121.42.44.208:8082/MIS/addOrganizationVideo',
                options={
                    height:34,
                    width:82,
                    buttonText:'上传视频',
                    queueID:'uploadProConForVideo',
                    uploader:url,
                    formData:{course_id:this.course_id},
                    fileSizeLimit: '100MB',//上传文件大小限制
                    fileTypeDesc: '视频文件',
                    fileTypeExts: '*.avi; *.mp4; *.mov; *.wmv'//文件类型过滤
                };
            Hisihi.initUploadify($("#uploadVideoBtn"),function(file, data){
                var src = '';
                if (data.success) {
                    //var logo=data.logo;
                    //that.execAddStudentWorks.call(that,logo);
                } else {
                    alert('作品上传失败');
                }
            },options);
        },


    };
    var $wrapper=$('#lessonDetailInfoWrapper');
    if($wrapper.length>0) {
        new LessonDetailInfo($wrapper);
    }

});