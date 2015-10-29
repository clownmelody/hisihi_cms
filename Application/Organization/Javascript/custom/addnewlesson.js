/**
 * Created by Jimmy on 2015/10/27.
 */
//我的老师

define(['jquery','jqueryui'],function () {
    var AddNewLesson = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.validate=this.getFormValidity();
    };
    AddNewLesson.prototype= {

        /*添加新的教程*/
        addNewLesson:function(){


        },

        //表单验证
        getFormValidity:function(){

            //$organization_id    机构 id
            //$title              课程视频
            //$content            课程介绍
            //$img                课程封面id
            //$lecturer           课程讲师id
            //$auth               课程权限
            return $("#basicForm").validate({
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


    };
    var $wrapper=$('.addNewLessonWrapper');
    if($wrapper.length>0) {
         new AddNewLesson($wrapper);
    }
});