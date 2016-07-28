/**
 * Created by jimmy on 16/7/15.
 */

define(['touch','toucher'],function(){
    var MySlider=function($wrapper,htmlArr,setting){
        if(!$wrapper || !htmlArr){
            return;
        }
        if(!htmlArr instanceof Array || !htmlArr.constructor == Array){
            return;
        }
        var that=this;
        this.$wrapper=$wrapper;
        this.htmlArr=htmlArr;
        this.extentSetting(setting);

        this.initContainer();
        if(!this.setting.touchDisabled){
            ////左滑动
            //this.$wrapper.on('swipeLeft','.slider-main .slider-item',$.proxy(this,'swipeLeft'));
            //
            ////右滑动
            //this.$wrapper.on('swipeRight','.slider-main .slider-item', $.proxy(this,'swipeRight'));

            var bodyTouch = util.toucher($wrapper[0]);
            bodyTouch.on('swipeRight','.slider-item',$.proxy(this,'swipeRight'));
            bodyTouch.on('swipeLeft','.slider-item',$.proxy(this,'swipeLeft'));

        };
        var index=this.setting.index;
        if(index!='0'){
            this.transform(
                that.getDiffWidth(index),
                that.$wrapper.find('.slider-item').eq(index),
                function(){
                    that.setting.changeCallback && that.setting.changeCallback('right',index);
                });
        }
        this.playAuto();



    };
    MySlider.prototype={
        /*默认参数设置*/
        extentSetting:function(setting){
            this.setting=defaultSetting;
            if(!setting){
                return;
            }
            for(var item in defaultSetting){
                if(typeof setting[item]!='undefined'){
                    this.setting[item]=setting[item];
                }
            }
        },
        initContainer:function(){
            var len=this.htmlArr.length,
                str='<div class="slider-container"><div class="slider-main" style="width:'+len*100+'%;">',
                navStr='<div class="default-nav"><div class="nav-main">';
            this.itemWidth=1/len;
            var itemWidth=this.itemWidth*100+'%';

            for(var i=0;i<len;i++){
                var className='';
                if(i==0){
                    className='now';
                }
                str+='<div class="slider-item ' + className + '" style="width:'+itemWidth+'">'+
                        this.htmlArr[i]+
                     '</div>';
                navStr+='<span class="'+className+'"></span>';
            }
            str+='</div>';
            if(this.setting.showNav && len>1){
                navStr+='</div></div>';
                str+=navStr;
            }
            str+='</div>';
            this.$wrapper.html(str);
        },

        swipeLeft:function(e){
            //var $target=$(e.currentTarget),
            var target= e.target,$target;
            if(target.nodeName=='IMG'){
                $target=$(target).parent();
            }
            else{
                $target=$(e.target);
            }
            var index=$target.index(),
                that=this;
            if(index==$target.siblings().length){
                return
            }
            this.transform(that.getDiffWidth(index+1),
                $target.next(),
                function(){
                    that.setting.changeCallback && that.setting.changeCallback('right',index+1);
                    that.playAuto();
                    if(that.setting.showNav){

                    }
                });
        },

        swipeRight:function(e){
            //var $target=$(e.currentTarget),
            var target= e.target,$target;
            if(target.nodeName=='IMG'){
                $target=$(target).parent();
            }
            else{
                $target=$(e.target);
            }
            var index=$target.index(),
                that=this;
            if(index==0){
                return
            }
            that.transform(that.getDiffWidth(index-1),
                $target.prev(),
                function(){
                    that.setting.changeCallback && that.setting.changeCallback('left',index-1);
                    that.playAuto();
                });

        },


        playAuto:function(){
            clearInterval(this.timeInterval);
            var that=this;
            if(this.setting.autoPlay){
               this.timeInterval = window.setInterval(function(){
                    var $parent=that.$wrapper.find('.slider-main'),
                        $target=$parent.find('.now'),
                        index=$target.index();
                    if(index==$parent.children().length-1){
                        $target=$parent.children().eq(0);
                    }else{
                        $target=$target.next();
                    }
                    index=$target.index();
                    that.transform(
                        that.getDiffWidth(index),
                        $target,
                        function(){
                            that.setting.changeCallback && that.setting.changeCallback('right',index);
                        });
               },this.setting.interval);
            }
        },

        transform:function(f,$target,callback){
            $target.addClass('now').siblings().removeClass('now');
            this.$wrapper.find('.slider-main').css({
                '-webkit-transform':'translateX(-'+f+') translateZ(0)',
                '-webkit-transition':'500ms ease-out'
            });
            callback && callback();
            var index=$target.index();
            if(this.setting.showNav){
                this.$wrapper.find('.nav-main span').eq(index)
                    .addClass('now').siblings()
                    .removeClass('now');
            }
        },

        getDiffWidth:function(num){
            return this.itemWidth * num * 100+'%';
        },
    };

    var defaultSetting={
        touchDisabled:false,
        interval:2000,
        autoPlay:true,
        showNav:true,
        changeCallback:null,
        index:0
    };

    return MySlider;
});
