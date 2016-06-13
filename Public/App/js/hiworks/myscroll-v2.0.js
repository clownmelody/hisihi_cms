/**
 * Created by jimmy on 2016/6/13.
 * v2.0
 */
define(['$','iscroll'],function() {

    /**基础类**/
    var MyScroll = function ($target,options) {
        this.$target=$target;
        this.options=options;
        this._init();
    };

    MyScroll.prototype = {

        _pullDownAction: function () {
            var that=this;
            this.options.pullDownAction && this.options.pullDownAction();
        },

        /*还原上拉效果*/
        resetDownStyle:function(){
            this.myScroll.refresh();
            this.$downIcon.removeClass('loading');
            this.$down.find('.t-label').text('上拉加载更多');
            this.controlDownTipsStyle(true);
        },

        //是否 显示 隐藏上拉加载更多提示
        controlDownTipsStyle:function(flag){
            if(flag){
                this.$down.show();
                this.$up.show();
            }else {
                this.$up.hide();
            }
        },

        _pullUpAction: function () {
            var that=this;
            this.options.pullUpAction && this.options.pullUpAction();
        },

        /*还原下拉效果*/
        resetUpStyle:function(){
            this.myScroll.refresh();
            this.$upIcon.removeClass('loading');
            this.$up.find('.t-label').text('下拉刷新');
        },

        _init: function () {
            this.$down = this.$target.find('.pull-down');
            this.$up = this.$target.find('.pull-up');
            this.$downIcon = this.$down.find('.icon');
            this.$upIcon = this.$up.find('.icon');
            this.pullDownEl = this.$down[0];
            this.pullDownOffset = this.pullDownEl.offsetHeight;
            this.pullUpEl = this.$up[0];
            this.pullUpOffset = this.pullUpEl.offsetHeight;
            var that = this;

            var myScroll = new IScroll(this.$target[0], {probeType: 3, mouseWheel: true, vScrollbar: false});
            myScroll.on("slideDown", function () {
                if (this.y > 40) {
                    if (!that.$downIcon.hasClass('loading') && !that.$upIcon.hasClass('loading')) {
                        that.$downIcon.addClass('loading');
                        that.$down.find('.t-label').text('加载中...');
                        that._pullDownAction();
                    }
                }
            });

            myScroll.on("slideUp", function () {
                if (this.maxScrollY - this.y > 40) {
                    if (!that.$upIcon.hasClass('loading') && !that.$downIcon.hasClass('loading') && that.$up.css('display')!='none' ) {
                        that.$upIcon.addClass('loading');
                        that.$up.find('.t-label').text('加载中...');
                        that._pullUpAction();
                    }
                }
            });

            myScroll.on("scroll", function () {
                var y = this.y,
                    maxY = this.maxScrollY - y,
                    downHasClass = that.$downIcon.hasClass("flip"),
                    upHasClass = that.$upIcon.hasClass("flip"),
                    downLoadingClass=that.$downIcon.hasClass("loading"),
                    upLoadingClass=that.$upIcon.hasClass("loading");

                if (y >= 40) {
                    if(downLoadingClass){
                        return;
                    }
                    !downHasClass && that.$downIcon.addClass("flip");
                    that.$down.show().find('.t-label').text('释放刷新');
                    return;
                } else if (y < 40 && y > 0) {
                    if(downLoadingClass){
                        return;
                    }
                    downHasClass && that.$downIcon.removeClass("flip");
                    that.$down.hide().find('.t-label').text('下拉刷新');
                    return "";
                }

                if (maxY >= 60) {
                    if(upLoadingClass){
                        return;
                    }
                    !upHasClass && that.$upIcon.addClass("flip");
                    that.$up.show().find('.t-label').text('释放加载');
                    return;
                } else if (maxY < 60 && maxY >= 0) {
                    if(upLoadingClass){
                        return;
                    }
                    upHasClass && that.$upIcon.removeClass("flip");
                    that.find('.t-label').text('上拉加载更多');
                    return;
                }
            });
            this.myScroll=myScroll;

            return myScroll;

        },
        refresh:function(flag){
            if(flag) {
                this.controlDownTipsStyle(true);
            }
            this.myScroll.refresh();
        },

    };
    return MyScroll;
});