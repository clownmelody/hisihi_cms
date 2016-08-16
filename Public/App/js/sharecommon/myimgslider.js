/**
 * Created by jimmy on 16/7/15.
 * 图片查看 可以滑动，可以放大查看
 */

define(['toucher'],function(){
    var MyImgSlider=function(imgArr,setting){
        if(!imgArr){
            return;
        }
        if(!imgArr instanceof Array || !imgArr.constructor == Array){
            return;
        }
        var that=this;
        this.infoArr=imgArr;
        this.extentSetting(setting);

        this.initContainer();
        this.$wrapper=$('#my-pic-view-modal');

        var bodyTouch = util.toucher(this.$wrapper[0]);
        bodyTouch.on('swipeRight','.slider-item',$.proxy(this,'swipeRight'));
        bodyTouch.on('swipeLeft','.slider-item',$.proxy(this,'swipeLeft'));

        //关闭相册信息
        $(document).on('click','.view-pics-box', function(){
            event.stopPropagation();
            if(event.target==this){
                that.hide();
            }
        });

        var index=this.setting.index;
        if(index!='0'){
            this.transform(
                that.getDiffWidth(index),
                that.$wrapper.find('.slider-item').eq(index),
                function(){
                    that.setting.changeCallback && that.setting.changeCallback('right',index);
                });
        }

    };

    MyImgSlider.prototype={
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
            var len=this.infoArr.length,
                str='<div class="slider-container"><div class="slider-main" style="width:'+len*100+'%;">';

            this.itemWidth=1/len;
            var filterStr='';
            if(this.setting.filterBg){
                filterStr='<div class="filter">' +
                        '<img class="filter-img" src="'+ this.infoArr[this.setting.index]+'" alt="logo">' +
                    '</div>';
            }
            var itemWidth=this.itemWidth*100+'%',
                mStr = '<div class="pic-modal" id="my-pic-view-modal">' +
                    '<div class="pics-nav"><span>'+(this.setting.index+1)+'/'+this.infoArr.length+'</span><label>点击查看大图</label></div>' +
                    '<div class="pic-main">' +
                    filterStr+
                    '<div class="view-pics-box">AAAAA</div>' +
                    '</div>' +
                    '<section class="imgzoom_pack">' +
                    '<div class="imgzoom_x btn"><span class="icon-cancel"></span></div>' +
                    '<div class="imgzoom_img"><img class="img-target" src="" /></div>' +
                    '</section>' +
                    '</div>',
                strScale = '';

            if (this.setting.scale) {
                strScale = '<div class="show-origin-pic">查看大图</div>';
            }
            for (var i = 0; i < len; i++) {
                str += '<div class="slider-item" style="width:' + itemWidth + '">' +
                    '<img  src="' + this.infoArr[i] + '">' +
                    strScale +
                    '</div>';
            }
            str += '</div>';
            var $target=$('#my-pic-view-modal');
            if ($target.length > 0) {
                $target.find('.view-pics-box').html(str);
                $target.find('.pics-nav span').text((this.setting.index+1)+'/'+this.infoArr.length);

            } else {
                mStr = mStr.replace('AAAAA', str);
                $('body').append(mStr);
            }

        },

        /*向左滑动， 即下一张*/
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

                });
        },

        /*向右滑动， 即上一张*/
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
                });

        },


        /*滚动图片*/
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
            if(this.setting.filterBg){
                this.$wrapper.find('.filter-img').attr('src',this.infoArr[index]);
            }
            this.$wrapper.find('.pics-nav span').text((index+1)+'/'+this.infoArr.length);
        },

        /*得到滑动距离*/
        getDiffWidth:function(num){
            return this.itemWidth * num * 100+'%';
        },

        /*显示*/
        show:function(){
            this.$wrapper.addClass('show');
            this.setting.showOrHideCallback && this.setting.showOrHideCallback('show');
        },

        hide:function(){
            this.$wrapper.removeClass('show');
            this.setting.showOrHideCallback && this.setting.showOrHideCallback('hide');
        },

        initScale:function(){
            var btnList=document.querySelectorAll('.view-pics-box .show-origin-pic');
            //实例化缩放
            ImagesZoom.init({
                elem: ".view-pics-box",  //容器dom
                btnsList:btnList,  //查看按钮
                initCallback:function(dom){
                    $(dom).hide().parent().find('img').hide();
                    $('.pics-nav label').hide();
                },
                closeCallback:function(){
                    $('.view-pics-box .now img').show();
                    $('.pics-nav label').show();
                    for(var len=btnList.length,i=0;i<len;i++) {
                        $(btnList[i]).show();
                    }
                }
            });
        },
    };

    var defaultSetting={
        changeCallback:null,
        showOrHideCallback:null,
        index:0,//单纯的图片查看 默认展示第一张
        scale:true,//查看大图
        filterBg:true
    };

    return MyImgSlider;
});
