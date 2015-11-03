/**
 * Created by Jimmy on 2015/10/27.
 */
//我的老师

define(['jquery','jqueryui','jqueryvalidate','util'],function () {
    var AddNewLesson = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
        this.initUploadify();
        this.validity=this.getFormValidity();
        this.course_id=this.$wrapper.data('lid');
        this.loadAllTeachers();  //加载所有老师
        this.loadAllClass(); //加载所有分类
        if(this.course_id!=0) {
            this.loadLessonBasicInfo();
        }else{
            this.course_id=null;
            this.$wrapper.find('#myLessonCoverImg img').attr('src',window.urlObject.defaultImg.cover);
        }
        //事件注册
        this.$wrapper.on('click','#addNewLessonSubmitBtn', $.proxy(this,'addNewLesson'));
    };
    AddNewLesson.prototype= {

        /*加载所有老师*/
        loadAllTeachers:function(){
            var url=this.basicApiUrl+'/teachersList',
                that=this;
            var strss='<option value="69">Rfly</option> <option value="70">qwert123</option> <option value="72">Leslie</option> <option value="75">hldw123</option> <option value="77">小Y</option> <option value="81">你猜我叫什么</option> <option value="82">阿当</option> <option value="91">good2good</option> <option value="92">15871752300</option> <option value="93">fjlkaejf</option> <option value="104">melodytest</option> <option value="519">Quan</option> <option value="520">聚光设计vx</option> <option value="521">武汉顾美设计有限公司</option> <option value="522">新美工作室</option> <option value="523">思优游戏设计</option> <option value="524">艺创梦续</option> <option value="525">设计师里默默</option> <option value="526">嫣希</option> <option value="527">一文创意</option> <option value="528">曹博雨</option> <option value="529">LEE</option> <option value="530">Teige</option> <option value="531">王广</option> <option value="532">老齐</option> <option value="533">玮蓝刘老师</option> <option value="534">周飞</option> <option value="535">lennie</option> <option value="536">Mouri</option> <option value="537">Herron</option> <option value="538">沉海哥</option> <option value="539">邦克传媒</option> <option value="540">梁煜辉</option> <option value="541">老孙</option> <option value="542">Frankie</option> <option value="543">Use</option> <option value="544">将讯佳</option> <option value="545">锐意设计</option> <option value="546">先创老刘</option> <option value="547">鹿鼎装饰</option> <option value="548">北京创元设计</option> <option value="550">張君雅</option>';
            that.$wrapper.find('#newLessonTeacher').html(strss);
            //Hisihi.getDataAsync({
            //    type: "post",
            //    url: url,
            //    data: {page:1,count:100},
            //    org:true,
            //    callback:function(result){
            //        if(result.success){
            //            var data=result.data,
            //            str='',len=data.length;
            //            for(var i=0;i<len;i++){
            //                str+=' <option value="'+data[i].uid+'">'+data[i].nickname+'</option>';
            //            }
            //            that.$wrapper.find('#newLessonTeacher').html(str);
            //        }
            //
            //
            //    }
            //});
        },

        /*加载所有分类*/
        loadAllClass:function(){
            var url=this.basicApiUrl+'/getVideoCategory',
                that=this;
            //var strss='<option value="69">Rfly</option> <option value="70">qwert123</option> <option value="72">Leslie</option> <option value="75">hldw123</option> <option value="77">小Y</option> <option value="81">你猜我叫什么</option> <option value="82">阿当</option> <option value="91">good2good</option> <option value="92">15871752300</option> <option value="93">fjlkaejf</option> <option value="104">melodytest</option> <option value="519">Quan</option> <option value="520">聚光设计vx</option> <option value="521">武汉顾美设计有限公司</option> <option value="522">新美工作室</option> <option value="523">思优游戏设计</option> <option value="524">艺创梦续</option> <option value="525">设计师里默默</option> <option value="526">嫣希</option> <option value="527">一文创意</option> <option value="528">曹博雨</option> <option value="529">LEE</option> <option value="530">Teige</option> <option value="531">王广</option> <option value="532">老齐</option> <option value="533">玮蓝刘老师</option> <option value="534">周飞</option> <option value="535">lennie</option> <option value="536">Mouri</option> <option value="537">Herron</option> <option value="538">沉海哥</option> <option value="539">邦克传媒</option> <option value="540">梁煜辉</option> <option value="541">老孙</option> <option value="542">Frankie</option> <option value="543">Use</option> <option value="544">将讯佳</option> <option value="545">锐意设计</option> <option value="546">先创老刘</option> <option value="547">鹿鼎装饰</option> <option value="548">北京创元设计</option> <option value="550">張君雅</option>';
            //that.$wrapper.find('#newLessonTeacher').html(strss);
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: {},
                org:false,
                callback:function(result){
                    if(result.success){
                        var data=result.data,
                        str='',len=data.length;
                        for(var i=0;i<len;i++){
                            str+=' <option value="'+data[i].id+'">'+data[i].value+'</option>';
                        }
                        that.$wrapper.find('#newLessonType').html(str);
                    }


                }
            });
        },

        /*
        *加载教程的基本信息
         */
        loadLessonBasicInfo:function(){
            if (this.$wrapper.data('cornerLoading')) {
                this.$wrapper.cornerLoading('showLoading');
            } else {
                this.$wrapper.cornerLoading();
            }
            url=this.basicApiUrl+'/getCourseVideoList',
                that=this;
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: {course_id:this.course_id},
                callback: $.proxy(this,'fillInLessonBasicInfo')
            });
        },

        /*填充教程基本信息*/
        fillInLessonBasicInfo:function(result){
            this.$wrapper.cornerLoading('hideLoading');
            if(result.success) {
                var data=result.data,
                    $form = this.$wrapper.find('#addNewLessonForm'),
                    url=data.img_url;
                url =url || window.urlObject.defaultImg.cover;
                $form.find('#newLessonTitle').val(data.title);
                $form.find('#newLessonContent').val(data.content);

                $form.find('#myLessonCoverImg img').attr({'src':url,'data-lid':data.img});

                //控制下拉框的默认值
                this.setSelectedInfo($('#newLessonTeacher'),data.lecturer);
                this.setSelectedInfo($('#newLessonType'),data.category_id);
                this.setSelectedInfo($('#newLessonAuth'),data.auth);
            }else{
                alert(result.message);
            }
        },

        /*添加新的教程*/
        addNewLesson:function(){
            if(this.validity.form()) {
                var $form = this.$wrapper.find('#addNewLessonForm'),
                    $result=this.$wrapper.find('#addNewLessonSubmitResult label'),
                    that=this,
                    newData= {
                        id:this.course_id,
                        title: $form.find('#newLessonTitle').val(),
                        content: $form.find('#newLessonContent').val(),
                        img: $form.find('#myLessonCoverImg img').attr('data-lid'),
                        lecturer: this.getSelectedInfo($form.find('#newLessonTeacher')).val,
                        category_id:this.getSelectedInfo($form.find('#newLessonType')).val,
                        auth :this.getSelectedInfo($form.find('#newLessonAuth')).val
                    };

                Hisihi.getDataAsync({
                    type: "post",
                    url: this.basicApiUrl + '/addCourse',
                    data: newData,
                    org:true,
                    callback: function(result){
                        var txt='操作失败';
                        if(result.success) {
                            txt='操作成功';
                            that.$wrapper.attr('data-lid',result.courses_id);
                            that.course_id=result.courses_id;
                        }
                        $result.text(txt).parent().show().delay(1000).hide(0);
                    }
                });
            }
        },

        /*
        *获得下拉框的内容
        * para
        * $sel-{jquery对象}下拉框对象
        */
        getSelectedInfo:function($sel){
           var $option= $sel.find('option:selected');
            return {
                txt:$option.text(),
                val:$option.val()
            }
        },

        /*
         *控制下拉框的内容
         * para
         * $sel-{jquery对象}下拉框对象
         * val - {string} 默认值
         */
        setSelectedInfo:function($sel,val){
            $sel.find('option').each(function(){
                if($(this).val()==val){
                    $(this).attr('selected',true);
                    return false;
                }
            });
        },

        //表单验证
        getFormValidity:function(){
            return $("#addNewLessonForm").validate({
                rules: {
                    title: {
                        required: true,
                    },
                    content: {
                        required: true,
                    },
                    lecturer: {
                        required: true,
                    },
                },
                messages: {
                    title: "请输入标题",
                    content: {
                        required: "课程介绍不能为空",
                    },
                    lecturer:{
                        required: "课程讲师不能为空",
                    }
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.next('.basicFormInfoError'));
                }
            });
        },

        /*
         *初始化头像上传插件
         */
        initUploadify:function() {
            var that=this;
            Hisihi.initUploadify($("#uploadLessonPic"),function(file, data){
                var src = '';
                if (data.success) {
                    var $img=that.$wrapper.find('#myLessonCoverImg img');
                    $img.attr({'src':data.logo.path,'data-lid':data.logo.id});
                } else {
                    alert(data.message);
                    //(data.info);
                    //data.info
                    //setTimeout(function () {
                    //
                    //}, 1500);
                }
            });
        },
    };


    var $wrapper=$('.addNewLessonWrapper');
    if($wrapper.length>0) {
         new AddNewLesson($wrapper);
    }
});