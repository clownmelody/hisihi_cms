/**
 * Created by Jimmy on 2015/10/27.
 */
//我的老师

define(['jquery','jqueryui','util'],function () {
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
                        var str = that.getStudentWorksInfoStr(result.data);
                        that.$wrapper.find('#studentWorksMainCon>div').before(str);
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
                        '<div class="delete-item-btn" title="删除"></div>'+
                    '</li>';
            });
            return str;
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