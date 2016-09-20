/**
 * 抵扣券使用说明
 * Created by jimmy-jiang on 2016/9/20.
 */
define(['base'],function(Base){
    var Deduction=function(data,$target){
        if(!data || !(data instanceof Array)){
            this.showTips('请正确传入数据内容');
            return;
        }
        if(!$target || $target.length==0){
            this.showTips('数据容器为空');
            return;
        }
        this.data=data;
        this.initData($target);
        var eventName='click',that=this;
        if(this.isLocal) {
            eventName = 'touchend';
        }

        //查看抵扣券 全部信息
        $target.on(eventName,'.deduction-tip-left', $.proxy(this,'showDeductionTags'));

        $target.on(eventName,'.deduction-tip-right', $.proxy(this,'createModal'));

        $(document).on(eventName,'.deduction-footer',function(){
            that.controlDeductionTagsTips(false);
        });
    };

    Deduction.prototype=new Base();
    Deduction.constructor=Deduction;
    var t=Deduction.prototype;

    t.initData=function($target){
        this.fillInTags($target);
    };

    t.fillInTags=function($target){
        var len=this.data.length,
            str='<ul class="deduction-tip-left hide">',
            item;
        for(var i=0;i<len;i++){
            item=this.data[i];
            str+='<li>'+
                    '<img src="'+window.hisihiUrlObj.image+'/orgbasicinfo/3.0.2/ic_lable@2x.png"/>'+
                    '<span>'+item.value+'</span>'+
                '</li>'
        }
        str+='</ul><div class="deduction-tip-right"></div>';
        $target.html(str);
    };

    //显示所有的 抵扣券信息
    t.showDeductionTags=function(e){
        var $target=$(e.currentTarget).closest('ul'),
            $parent=$target.parent(),
            height=$parent.height();
        if(!$parent.hasClass('show')){
            $parent.css('height',$target.height()-4).addClass('show');
        }else{
            $parent.css('height','22px').removeClass('show');
        }
    };

    t.createModal=function(){
        if($('.deduction-modal').length==0){
            var $modal = $('<div class="deduction-modal">' +
                                '<div class="deduction-main">' +
                                    '<div class="deduction-head">' +
                                        '嘿设汇为您提供' +
                                    '</div>' +
                                    '<div class="deduction-content"><ul class="deduction-ul"></ul></div>' +
                                    '<div class="deduction-footer"><div>我知道了</div></div>' +
                                '</div>' +
                            '</div>'),
                str = '',
                item,
                len = this.data.length;
            for (var i = 0; i < len; i++) {
                item = this.data[i];
                str += '<li>' +
                            '<div class="li-left">'+
                                '<img src="' + window.hisihiUrlObj.image + '/orgbasicinfo/3.0.2/ic_lable@2x.png"/>' +
                            '</div>'+
                            '<div class="li-right">'+
                                '<span>' + item.value + '</span>' +
                                '<p>'+item.extra+'</p>'+
                            '</div>'+
                        '</li>';
            }
            str += '<div class="deduction-tip-right"></div>';
            $modal.find('.deduction-ul').html(str);
            $('body').append($modal);
        }
        this.controlDeductionTagsTips(true);
    };


    //显示 使用说明 页面
    t.controlDeductionTagsTips=function(flag){
        var $target=$('.deduction-modal');
        if(flag) {
            $target.removeClass('hide').addClass('show');
            this.scrollControl(false);  //禁止滚动
        } else{
            $target.removeClass('show').addClass('hide');
            this.scrollControl(true);  //禁止滚动
        }
    };

    return Deduction;

});