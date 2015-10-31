/**
 * Created by Jimmy on 2015/10/27.
 */
//学生作品

define(['jquery','jqueryui','util'],function () {
    var StudentWorks = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
        this.loadData();
        this.initUploadify();
        this.$wrapper.on('click','.worksItemBottom', $.proxy(this,'worksItemDescEdit'));
        this.$wrapper.on('focusout','.worksItemBottom textarea', $.proxy(this,'hideWorksItemDescEdit'));
        this.$wrapper.on('keyDown','.worksItemBottom textarea', $.proxy(this,'hideWorksItemDescEdit'));
        this.$wrapper.on('click','.editStudentWorks', $.proxy(this,'showEditVideoBox'));
        this.$wrapper.on('click','.deleteStudentWorks', $.proxy(this,'deleteStudentWorks'));
    };
    StudentWorks.prototype= {
        loadData: function () {
            if (this.$wrapper.data('cornerLoading')) {
                this.$wrapper.cornerLoading('showLoading');
            } else {
                this.$wrapper.cornerLoading();
            }
            var url=this.basicApiUrl+'/getStudentWorks',
            that=this;
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: {},
                org:true,
                callback:function(result) {
                    that.$wrapper.cornerLoading('hideLoading');
                    if(result.success) {
                        that.getStudentWorksInfoStr(result.data);
                    }else{
                        alert('学生作品加载失败');
                    }
                }
            });
        },

        /*
        *展示学生作品
        */
        getStudentWorksInfoStr:function(data){
            var str='',
                that=this,
                title='';
            $.each(data,function(){
                title=this.description;
                str+='<li class="normal" data-id="'+this.id+'">'+
                        '<div class="worksItemHeader">'+
                            '<img src="'+this.url+'">'+
                        '</div>'+
                        '<div class="worksItemBottom">'+
                            '<p title="'+title+'">'+Hisihi.substrLongStr(title,33)+'</p>'+
                            '<textarea>'+title+'</textarea>'+
                        '</div>'+
                        '<div class="delete-item-btn deleteStudentWorks" title="删除"></div>'+
                    '</li>';
            });
            that.$wrapper.find('#studentWorksMainCon>div').before(str);
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

        /*
         *初始化头像上传插件
         */
        initUploadify:function() {
            var that=this;
            Hisihi.initUploadify($("#uploadStudentWorks"),function(file, data){
                var src = '';
                if (data.success) {
                    var logo=data.logo;
                    that.execAddStudentWorks.call(that,logo);
                } else {
                    alert('作品上传失败');
                }
            },{height:34,width:82,'queueID':'uploadProConForSWorkd'});
        },

        /*添加学生作品*/
        execAddStudentWorks:function(logo){
            var url=this.basicApiUrl+'/studentWorks',
                that=this;
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: {pic_id: logo.id,description:''},
                org: true,
                callback: function (result) {
                    if (result.success) {
                        //添加到列表中
                        that.getStudentWorksInfoStr.call(that, [{url: logo.path, description: '',id:result.works_id}]);
                    } else {
                        alert('作品上传失败');
                    }
                }
            });
        },

        /*删除学生作品*/
        deleteStudentWorks:function(e){
            e.stopPropagation();
            if(window.confirm('确定删除该作品么？')) {
                var $parent = $(e.currentTarget).closest('li'),
                    url = this.basicApiUrl + '/studentWorks',
                    that = this;
                Hisihi.getDataAsync({
                    url: url,
                    data: {id: $parent.data('id'),type:'delete'},
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

    };

    //var studentWorks=new StudentWorks($('.studentWorksWrapper'));
    var $wrapper=$('.studentWorksWrapper');
    if($wrapper.length>0) {
        new StudentWorks($wrapper);
    }
});