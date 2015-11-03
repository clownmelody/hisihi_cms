/**
 * Created by Jimmy on 2015/10/27.
 */
//学生作品

define(['jquery','jqueryui','util'],function () {
    var StudentWorks = function ($wrapper) {
        var that=this;
        this.$wrapper = $wrapper;
        this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
        this.loadData();
        this.initUploadify();
        this.$wrapper.on('click','.worksItemBottom', $.proxy(this,'worksItemDescEdit'));
        this.$wrapper.on('focusout','.worksItemBottom textarea', function(){
            that.hideWorksItemDescEdit.call(that,$(this).closest('li'));
        });
        this.$wrapper.on('keydown','.worksItemBottom textarea', $.proxy(this,'dealWithWorksItemDescEdit'));
        this.$wrapper.on('click','.worksItemBottom p', function(e){e.stopPropagation();});
        //this.$wrapper.on('keyDown','.worksItemBottom textarea', $.proxy(this,'hideWorksItemDescEdit'));
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
                data: {page:1,count:50},
                org:true,
                callback:function(result) {
                    that.$wrapper.cornerLoading('hideLoading');
                    if(result.success) {
                        that.getStudentWorksInfoStr(result.data);
                    }else{
                        alert(result.message);
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
            if(data) {
                $.each(data, function () {
                    title = this.description;
                    str += '<li class="normal" data-id="' + this.id + '">' +
                        '<div class="worksItemHeader">' +
                        '<img src="' + this.url + '">' +
                        '</div>' +
                        '<div class="worksItemBottom">' +
                        '<p title="' + title + '">' + Hisihi.substrLongStr(title, 33) + '</p>' +
                        '<textarea>' + title + '</textarea>' +
                        '</div>' +
                        '<div class="delete-item-btn deleteStudentWorks" title="删除"></div>' +
                        '</li>';
                });
            }else{
                str='<p class="noDataForQuery">学生作品暂无，快点上传吧。</p>';
            }
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

        /*隐藏编辑框*/
        hideWorksItemDescEdit:function($li){

            $li.find('p').show();
            $li.find('textarea').hide();
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
                    alert(data.message);
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
                        that.getStudentWorksInfoStr.call(that, [{id: result.works_id, url: logo.path, description: ''}]);
                    } else {
                        alert(result.message);
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
                            alert(data.message);
                        }
                    }
                });
            }
        },

        dealWithWorksItemDescEdit:function(e){
            var $li=$(e.currentTarget).closest('li');
            if(e.keyCode==13){
                event.returnValue = false;
                this.updateStudentWorks($li);
            }
            if(e.keyCode==27){
                this.hideWorksItemDescEdit($li);
            }
        },

        /*更新学生作品描述信息*/
        updateStudentWorks:function($li){
            var url=this.basicApiUrl+'/updatePictureDescription',
                that=this,
                newVal=$li.find('textarea').val();
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: {id:$li.data('id'),description:newVal},
                org: false,
                callback: function (result) {
                    if (result.success) {
                        //更新图片描述
                        $li.find('p').text(newVal);
                        that.hideWorksItemDescEdit($li);
                    } else {
                        alert(result.message);
                    }
                }
            });
        },

    };

    //var studentWorks=new StudentWorks($('.studentWorksWrapper'));
    var $wrapper=$('.studentWorksWrapper');
    if($wrapper.length>0) {
        new StudentWorks($wrapper);
    }
});