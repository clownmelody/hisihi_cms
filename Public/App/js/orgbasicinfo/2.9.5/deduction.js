/**
 * 抵扣券使用说明
 * Created by jimmy-jiang on 2016/9/20.
 */
define(['base'],function(Base){
    var Deduction=function(data,$target,options){
        if(!data || !(data instanceof Array)){
            alert('请正确传入数据内容');
            return;
        }
        if(!$target || $target.length==0){
            alert('数据容器为空');
            return;
        }
        this.data=data;
        this.minHeight='30px';
        this.extentSetting(options);

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

    /*默认参数设置*/
    t.extentSetting=function(config){
        this.config=defaultConfig;
        if(!config){
            return;
        }
        for(var item in defaultConfig){
            var val=config[item];
            if(val!='undefined'){
                this.config[item] = val;
            }

        }
    };


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
                    '<img src="http://pic.hisihi.com/2016-09-24/1474707998563641.png"/>'+
                    '<span>'+item.value+'</span>'+
                '</li>'
        }
        str+='</ul><div class="deduction-tip-right" style="background: url(http://pic.hisihi.com/2016-09-24/1474708134912749.png);"></div>';
        $target.html(str).css('height',this.minHeight);
    };

    //显示所有的 抵扣券信息
    t.showDeductionTags=function(e){
        var $target=$(e.currentTarget).closest('ul'),
            $parent=$target.parent(),
            height=$parent.height();
        if(!$parent.hasClass('show')){
            $parent.css('height',$target.height()-4).addClass('show');
        }else{
            $parent.css('height',this.minHeight).removeClass('show');
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
                                '<img src="http://pic.hisihi.com/2016-09-24/1474707998563641.png"/>' +
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
        if(this.config.showDeductionTagsCallBack){
            this.config.showDeductionTagsCallBack.call();
            return;
        }
        var $target=$('.deduction-modal');
        if(flag) {
            $target.removeClass('hide').addClass('show');
            this.scrollControl(false);  //禁止滚动
        } else{
            $target.removeClass('show').addClass('hide');
            this.scrollControl(true);  //禁止滚动
        }
    };

    var defaultConfig={
        showDeductionTagsCallBack:null
    };

    return Deduction;

});