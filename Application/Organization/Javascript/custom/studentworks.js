/**
 * Created by Jimmy on 2015/10/27.
 */
//我的老师

define(['jquery','jqueryui'],function () {
    var StudentWorks = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
        this.loadData();
        this.$wrapper.on('click','.worksItemBottom', $.proxy(this,'worksItemDescEdit'));
        this.$wrapper.on('blur','.worksItemBottom textarea', $.proxy(this,'hideWorksItemDescEdit'));
        this.$wrapper.on('keyDown','.worksItemBottom textarea', $.proxy(this,'hideWorksItemDescEdit'));
        this.$wrapper.on('click','.editStudentWorks', $.proxy(this,'showEditVideoBox'));
    };
    StudentWorks.prototype= {
        loadData: function () {
            var data = [
                    {
                    id:0,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/studentworks/1.png'
                    },
                {
                    id:1,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/studentworks/10.png'
                },
                {
                    id:2,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/studentworks/2.png'
                },
                {
                    id:3,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/studentworks/3.png'
                },
                {
                    id:4,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/studentworks/4.png'
                },
                {
                    id:5,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/studentworks/5.png'
                },
                {
                    id:6,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/studentworks/6.png'
                },
                {
                    id:7,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/studentworks/7.png'
                },
                {
                    id:8,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/studentworks/8.png'
                },
                {
                    id:9,
                    typeName: 'Photoshop',
                    title: '大圣归来手绘原稿',
                    uploadTime: '2015.02.14 12:00',
                    viewedTime:12154546,
                    imgSrc: window.urlObject.image + '/studentworks/9.png'
                }
            ];

            var url=this.basicApiUrl+'/getStudentWorks',
                that=this;
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: {},
                org:true,
                callback:function(data) {
                    that.showStudentWorksInfo(data);
                }
            });
        },

        showStudentWorksInfo:function(data){
            var str='',
                that=this,
                typeNameAndTile='',
                tempTitle='';
            $.each(data,function(){
                typeNameAndTile=this.typeName+' | '+this.title;
                tempTitle=typeNameAndTile;
                if(typeNameAndTile.length>33){
                    tempTitle=typeNameAndTile.substr(0,33)+'…';
                }
                str+='<li class="normal" data-id="'+this.id+'">'+
                        '<div class="worksItemHeader">'+
                            '<img src="'+this.imgSrc+'">'+
                        '</div>'+
                        '<div class="worksItemBottom">'+
                            '<p title="'+typeNameAndTile+'">'+tempTitle+'</p>'+
                            '<textarea>'+typeNameAndTile+'</textarea>'+
                        '</div>'+
                        '<div class="delete-item-btn" title="删除"></div>'+
                    '</li>';
            });
            str+='<div style="clear:both;">';
            this.$wrapper.find('#studentWorksMainCon').append(str);
        },

        /*显示编辑框*/
        worksItemDescEdit:function(e){
           var $target=$(e.currentTarget),
               $p=$target.find('p'),
               $textArea=$target.find('textarea');
            $p.hide();
            $textArea.show();
        },

        hideWorksItemDescEdit:function(e){
            var $target=$(e.currentTarget),
                $p=$target.prev();
            $p.show();
            $target.hide();
        },

        /*
        *显示、隐藏删除按钮
        */
        showEditVideoBox:function(e){
            var $target=$(e.currentTarget),
                flag=$target.text()=='编辑',
                $li = this.$wrapper.find('.list-data-ul li');
            if(flag) {
                $target.text('关闭编辑');
                $li.removeClass('normal').addClass('edit');
            }else{
                $target.text('编辑');
                $li.removeClass('edit').addClass('normal');
            }
        },

    };

    //var studentWorks=new StudentWorks($('.studentWorksWrapper'));
    var $wrapper=$('.studentWorksWrapper');
    if($wrapper.length>0) {
        new StudentWorks($wrapper);
    }
});