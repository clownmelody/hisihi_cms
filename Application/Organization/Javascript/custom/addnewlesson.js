/**
 * Created by Jimmy on 2015/10/27.
 */
//我的老师

define(['jquery','jqueryui'],function () {
    var AddNewLesson = function ($wrapper) {
        this.$wrapper = $wrapper;
    };
    AddNewLesson.prototype= {
        loadData: function () {
            var data = [
                    {
                    id:0,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/video/1.png'
                    },
                {
                    id:1,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/video/2.png'
                },
                {
                    id:2,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/video/2.png'
                },
                {
                    id:3,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/video/3.png'
                },
                {
                    id:4,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/video/4.png'
                },
                {
                    id:5,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/video/5.png'
                },
                {
                    id:6,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/video/6.png'
                },
                {
                    id:7,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/video/7.png'
                },
                {
                    id:8,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/video/8.png'
                },
                {
                    id:9,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/video/9.png'
                }
            ];
            //this.getDataAsync(function(data){
            //    data;
            //});
            this.showLessonInfo(data);
        },
        showLessonInfo:function(data){
            var str='',
                that=this,
                typeNameAndTile='',
                tempTitle='';
            $.each(data,function(){
                typeNameAndTile=this.typeName+' | '+this.title;
                tempTitle=typeNameAndTile;
                if(typeNameAndTile.length>42){
                    tempTitle=typeNameAndTile.substr(0,42)+'…';
                }
                str+='<li class="normal" data-id="'+this.id+'">'+
                        '<div class="videoItemHeader">'+
                            '<img src="'+this.imgSrc+'">'+
                            '<i class="playBtn"></i>'+
                        '</div>'+
                        '<div class="videoItemBottom">'+
                            '<div class="videoItemDesc"><p class="typeNameAndTitle" title="'+typeNameAndTile+'">'+tempTitle+'</p></div>'+
                            '<div class="videoFooter">'+
                                '<div class="videoFooterLeft">'+
                                    '<i class="videoIcon videoClock"></i>'+
                                    '<span>'+this.uploadTime+'</span>'+
                                '</div>'+
                                '<div class="videoFooterRight">'+
                                    '<i class="videoIcon videoViewedTimes"></i>'+
                                    '<span>'+this.viewedTime+'</span>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                        '<div class="delete-item-btn" title="删除"></div>'+
                    '</li>';
            });
            str+='<div style="clear:both;">';
            this.$wrapper.find('#lessonsMainCon').append(str);
        },

    };
    var $wrapper=$('.addNewLessonWrapper');
    if($wrapper.length>0) {
         new AddNewLesson($wrapper);
    }
});