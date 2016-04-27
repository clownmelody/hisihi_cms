/**
 * Created by jimmy on 2016/4/27.
 */
define(['$','iscroll'],function() {

    /**基础类**/
    var MyScroll = function ($target,options) {
        this.$target=$target;
        this.options=options;
        this.init();
    };

    MyScroll.prototype = {

        pullDownAction: function () {
            this.options.pullDownAction && this.options.pullDownAction();
            //$.getJSON('test.json', function (data, state) {
            //    if (data && data.state == 1 && state == 'success') {
            //        setTimeout(function () {
            //            $('#news-list').html(data.data);
            //            myScroll.refresh();
            //            $downIcon.removeClass('loading');
            //            $down.find('.pullDownLabel').text('下拉刷新');
            //        }, 600);
            //    }
            //});
        },

        pullUpAction: function () {
            this.options.pullUpAction && this.options.pullUpAction();
            //$.getJSON('test.json', function (data, state) {
            //    if (data && data.state == 1 && state == 'success') {
            //        setTimeout(function () {
            //            $('#news-list').append(data.data);
            //            myScroll.refresh();
            //            $upIcon.removeClass('loading');
            //            $up.find('.pullUpLabel').text('上拉加载更多');
            //        }, 600);
            //    }
            //});
        },

        init: function () {
            this.$down = this.$target.find('.pullDown');
            this.$up = this.$target.find('.pullUp');
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
                    if (!that.$downIcon.hasClass('loading')) {
                        that.$downIcon.addClass('loading');
                        that.$down.find('.pullDownLabel').text('加载中...');
                        pullDownAction();
                    }
                }
            });

            myScroll.on("slideUp", function () {
                if (this.maxScrollY - this.y > 40) {
                    if (!that.$upIcon.hasClass('loading')) {
                        that.$upIcon.addClass('loading');
                        that.$up.find('.pullUpLabel').text('加载中...');
                        that.pullUpAction();
                    }
                }
            });

            myScroll.on("scroll", function () {
                var y = this.y,
                    maxY = this.maxScrollY - y,

                    downHasClass = that.$downIcon.hasClass("flip"),
                    upHasClass = that.$upIcon.hasClass("flip");

                if (y >= 40) {
                    !downHasClass && that.$downIcon.addClass("flip");
                    that.$down.find('.pullDownLabel').text('释放刷新');
                    return;
                } else if (y < 40 && y > 0) {
                    downHasClass && that.$downIcon.removeClass("flip");
                    that.$down.find('.pullDownLabel').text('下拉刷新');
                    return "";
                }

                if (maxY >= 40) {
                    !upHasClass && that.$upIcon.addClass("flip");
                    that.$up.find('.pullUpLabel').text('释放刷新');
                    return;
                } else if (maxY < 40 && maxY >= 0) {
                    upHasClass && that.$upIcon.removeClass("flip");
                    that.$up.find('.pullUpLabel').text('上拉加载更多');
                    return;
                }
            });
            this.myScroll=myScroll;
            return myScroll;
        },
    };
    return MyScroll;
});