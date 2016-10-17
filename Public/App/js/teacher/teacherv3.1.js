/**
 * Created by hisihi on 2016/10/10.
 */
define(['base','async','myPhotoSwipe','lazyloading'],function(Base,Async,MyPhotoSwipe){

    function Teacher($wrapper,uid,url) {
        this.$wrapper = $wrapper;
        var that = this;
        this.baseUrl = url;
        this.uid=uid;
        var eventName='click',that=this;
        if(this.isLocal){
            //eventName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        }

        this.loadBasicInfoData(function(result){
            if (result){
                alert (result);
                return;
            }
        });
    }

    //下载条
    var config={
        downloadBar:{
            show:true,
            pos:0
        }
    };

    Teacher.prototype=new Base(config);
    Teacher.constructor=Teacher;
    var t=Teacher.prototype;

    //获取老师基本信息
    t.loadBasicInfoData=function(callback) {
        var that=this,
        //$target=that.$wrapper.find('.logoAndCertInfo'),
            queryPara={
                //http://localhost/api.php?s=/teacher/getTeacherInfo
                url:this.baseUrl+'teacher/getTeacherInfo/',
                paraData:{teacher_id:this.uid},
                sCallback:function(result){
                    callback && callback(result);
                },
                eCallback:function(){
                    callback && callback(null);
                },
                type:'get',
                async:this.async
            };
        this.getDataAsync(queryPara);
    };

    //获取老师下学生作品
    t.getTeacherStudentWork=function(callback){

    };

    //填充页面信息
    t.fillTeacherPage=function(result){

    }

    //填充老师头部基本信息
    t.getTeacherBasicInfo=function(result){
        var str='';
        return  str='<div class="info-left">'+
                        '<img src="__IMG__/teacher/LL.jpg"/>'+
                        '</div>'+
                        '<div class="info-right">'+
                        '<ul>'+
                        '<li>'+
                        '<span id="name">'+徐领导+'</span>'+
                        '</li>'+
                        '<li>'+
                        '<span id="title">'+平面设计师，高级讲师+'</span>'+
                        '</li>'+
                        '</ul>'+
                        '</div>';
        $('.info').html(str);
    };


    return Teacher;
});