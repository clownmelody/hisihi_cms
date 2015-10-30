/**
 * Created by Jimmy on 2015/10/27.
 */
//我的老师

define(['jquery','jqueryui','jqueryvalidate',],function () {
    var AddNewLesson = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
        this.initUploadify();
        this.validate=this.getFormValidity();
        if(this.$wrapper.data('lid')!=0) {
            this.loadLessonBasicInfo();
        }else{

        }
        this.loadAllTeachers();  //加载所有老师

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

        /*
        *加载教程的基本信息
         */
        loadLessonBasicInfo:function(){
            if (this.$wrapper.data('cornerLoading')) {
                this.$wrapper.cornerLoading('showLoading');
            } else {
                this.$wrapper.cornerLoading();
            }


            var url=this.basicApiUrl+'/getBaseInfo',
                that=this;
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: {},
                org:true,
                callback: $.proxy(this,'fillInLessonBasicInfo')
            });
        },

        /*填充机构基本信息*/
        fillInLessonBasicInfo:function(result){
            this.$wrapper.cornerLoading('hideLoading');
            if(result.success) {
                var data=result.data,
                    $form = this.$wrapper.find('#basicForm');
                $form.find('#name').val(data.name);
                $form.find('#Signature').val(data.slogan);
                $form.find('#Address').val(data.location);
                $form.find('#orgBasicIntroduce').val(data.introduce);
                $newImg = this.$wrapper.find('#myNewPicture');
                $form.find('#basicInfoLogo').add($newImg).attr({'src':data.logo.url,'data-lid':data.logo.id});

                //加载优势标签
                var str =this.loadAdvantage(data.advantage);
                this.$wrapper.find('#myAdvantage').html(str);
                $form.find('#Contact').val(data.phone_num);
                $form.find('#organization_id').attr('data-org-id',this.organization_id);
            }else{
                alert('数据加载失败');
            }
        },

        /*添加新的教程*/
        addNewLesson:function(){
            if(this.validate.form()) {
                var $form = this.$wrapper.find('#addNewLessonForm');
                var newData= {
                    title: $form.find('#newLessonTitle').val(),
                    content: $form.find('#newLessonContent').val(),
                    img: $form.find('#myLessonCoverImg img').attr('data-lid'),
                    lecturer: this.getSelectedInfo($form.find('#newLessonTeacher')).val,
                    auth :this.getSelectedInfo($form.find('#newLessonAuth')).val
                };

                Hisihi.getDataAsync({
                    type: "post",
                    url: this.basicApiUrl + '/addCourse',
                    data: newData,
                    org:true,
                    callback: function(e){
                        if(e.success) {
                            alert('更新成功');
                        }else{
                            alert('更新失败');
                        }
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

        //表单验证
        getFormValidity:function(){

            //$organization_id    机构 id
            //$title              课程视频
            //$content            课程介绍
            //$img                课程封面id
            //$lecturer           课程讲师id
            //$auth               课程权限
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
            this.$wrapper.find('#lessonCoverForm').css('opacity',1);
            $("#uploadLessonPic").uploadify({
                "height": 30,
                "swf":window.urlObject.js+"/libs/uploadify/uploadify.swf",
                "fileObjName": "download",
                "buttonText": "上传图片",
                "uploader":that.basicApiUrl+'/uploadLogo' ,
                "width": 120,
                'removeTimeout': 1,
                'fileTypeExts': '*.jpg; *.png; *.gif;',
                "onUploadSuccess": uploadPictureCompany,
                'onFallback': function () {
                    alert('未检测到兼容版本的Flash.');
                }
            });
            function uploadPictureCompany(file, data) {
                var data = $.parseJSON(data);
                var src = '';
                if (data.success) {
                    var $img=that.$wrapper.find('#myLessonCoverImg img');
                    $img.attr({'src':data.logo.path,'data-lid':data.logo.id});
                } else {
                    //(data.info);
                    data.info
                    setTimeout(function () {

                    }, 1500);
                }
            }
        },


    };
    var $wrapper=$('.addNewLessonWrapper');
    if($wrapper.length>0) {
         new AddNewLesson($wrapper);
    }
});