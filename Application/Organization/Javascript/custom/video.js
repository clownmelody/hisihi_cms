/**
 * Created by Jimmy on 2015/10/27.
 */
//我的老师

define(['jquery','jqueryui'],function () {
    var MyLesson = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.loadData();
    };
    MyLesson.prototype= {
        loadData: function () {
            var data = [
                    {
                    id:0,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/userImg/app1.png'
                    },
                {
                    id:1,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/userImg/app1.png'
                },
                {
                    id:2,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/userImg/app1.png'
                },
                {
                    id:3,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/userImg/app1.png'
                },
                {
                    id:4,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/userImg/app1.png'
                },
                {
                    id:5,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/userImg/app1.png'
                }
            ];
            //this.getDataAsync(function(data){
            //    data;
            //});
            this.showLessonInfo(data);
        },
        showLessonInfo:function(data){
            var str='',
                that=this;
            $.each(data,function(){
                str+='<li class="normal" data-id="'+this.id+'">'+
                        '<div class=""><img src="'+this.imgSrc+'"></div>'+
                        '<div class=""><p class="tname" title="'+this.typeName+'">'+this.typeName+'</p><p class="trole">'+this.role+'</p></div>'+
                        '<div class="delete-item-btn" title="删除"></div>'+
                    '</li>';
            });
            str+='<div style="clear:both;">';
            this.$wrapper.find('#lessonsMainCon').append(str);
        },
    };

    var myLesson=new MyLesson($('.normalPageWrapper'));
});