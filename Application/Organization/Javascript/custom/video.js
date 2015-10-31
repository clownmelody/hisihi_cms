/**
 * Created by Jimmy on 2015/10/27.
 */
//我的老师

define(['jquery','jqueryui','util'],function () {
    var MyLesson = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
        var cookie=JSON.parse($.cookie('hisihi-org'));
        this.organization_id=cookie.organization_id;
        this.loadData();
        this.initSortEvents();
        //编辑
        this.$wrapper.on('click','.editVideo',$.proxy(this,'showEditLessonBox'));
        //删除
        this.$wrapper.on('click','.deleteLesson',$.proxy(this,'deleteLesson'));
        //添加教程
        this.$wrapper.on('click','#addLessons',$.proxy(this,'addLessons'));
        //教程详情
        this.$wrapper.on('click','#lessonsMainCon>li', function () {
            var id=$(this).data('id');
            window.location.href = window.urlObject.ctl + "/Index/lessondetailinfo/id/"+id;
        });
    };
    MyLesson.prototype= {

        //数据加载
        loadData: function () {
            if (this.$wrapper.data('cornerLoading')) {
                this.$wrapper.cornerLoading('showLoading');
            } else {
                this.$wrapper.cornerLoading();
            }
            var url=this.basicApiUrl+'/getCourses',
                that=this;
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: {},
                org:true,
                callback:function(result) {
                    that.$wrapper.cornerLoading('hideLoading');
                    if(result.success) {
                        that.showLessonInfo.call(that, result.data);
                    }else{
                        alert('数据加载失败');
                    }
                }
            });
        },

        //展示部分教程数据
        showLessonInfo:function(data){
            var str='',
                that=this,
                typeNameAndTile='',
                tempTitle='',
                date=null;

            $.each(data,function(){
                typeNameAndTile=this.category_name+' | '+this.title;
                tempTitle=typeNameAndTile;
                if(typeNameAndTile.length>42){
                    tempTitle=typeNameAndTile.substr(0,42)+'…';
                }
                date=Hisihi.getTimeFromTimestamp(this.create_time);
                str+='<li class="normal" data-id="'+this.id+'">'+
                    '<div class="videoItemHeader">'+
                    '<img src="'+this.url+'">'+
                    '<i class="playBtn"></i>'+
                    '</div>'+
                    '<div class="videoItemBottom">'+
                    '<div class="videoItemDesc"><p class="typeNameAndTitle" title="'+typeNameAndTile+'">'+tempTitle+'</p></div>'+
                    '<div class="videoFooter">'+
                    '<div class="videoFooterLeft">'+
                    '<i class="videoIcon videoClock"></i>'+
                    '<span>'+date+'</span>'+
                    '</div>'+
                    '<div class="videoFooterRight">'+
                    '<i class="videoIcon videoViewedTimes"></i>'+
                    '<span>'+ Hisihi.getRandomNum(1024*5)+'</span>'+
                    '</div>'+
                    '</div>'+
                    '</div>'+
                    '<div class="delete-item-btn deleteLesson" title="删除"></div>'+
                    '</li>';
            });
            str+='<div style="clear:both;">';
            this.$wrapper.find('#lessonsMainCon').append(str);
        },

        //显示编辑框
        showEditLessonBox:function(e){
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

        //添加教程
        addLessons:function(){
            window.location.href = window.urlObject.ctl + "/Index/addnewlesson/id/"+0;
        },

        /*删除教程*/
        deleteLesson:function(e){
            e.stopPropagation();
            if(window.confirm('确定删除该教程么？')) {
                var $parent = $(e.currentTarget).closest('li'),
                    url = this.basicApiUrl + '/deleteCourses',
                    that = this;
                Hisihi.getDataAsync({
                    url: url,
                    data: {id: $parent.data('id')},
                    org: false,
                    callback: function (data) {
                        if (data.success) {
                            $parent.remove();
                        } else {
                            alert('删除失败');
                        }
                    }
                });
            }
        },

        /*
         *教程移动事件注册
         *
         */
        initSortEvents:function(){
            $target = this.$wrapper.find('#lessonsMainCon');

            //任务拖动
            $target.sortable({
                items: ">li",
                helper: 'clone',
                delay: 300,
                cursor: 'move',
                scroll: true,
                placeholder: "sortableplaceholder",
                connectWith: '.memberItemUl',
                start: function (event, ui) {

                },

                stop: function (event, ui) {

                }
            });
        },
    };
    var $wrapper=$('.vedioWrapper');
    if($wrapper.length>0) {
        new MyLesson($wrapper);
    }
});