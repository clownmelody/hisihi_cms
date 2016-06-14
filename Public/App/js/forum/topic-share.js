/**
 * Created by jimmy on 2016/6/13.
 */

define(['myscroll'],function(MyScroll){
    var Topic=function(){
        this.initStyle();
        var s = new MyScroll($('.wrapper'), {
            //下拉刷新
            pullDownAction:$.proxy(this,'reloadWorksListInfo'),
            //上拉加载更多
            pullUpAction: $.proxy(this,'loadMoreWorksListInfo'),
        });
    };

    var t=Topic.prototype;

    t.initStyle=function(){
        this.controlCoverFootStyle();
        this.setContentBoxMinHeight();
    };

    /*控制底部logo的位置样式*/
    t.controlCoverFootStyle=function() {
        var $target = $('#downloadCon'),
            $a = $target.find('a'),
            aw = $a.width(),
            ah = aw * 0.40,
            bw = $(document).width(),
            h = bw * 120 / 750;
        $target.css({'height': h + 'px', 'opacity': 1});
        $('.wrapper').css({'bottom': h + 'px'});
    };

    /*设置主要容器的最小高度*/
    t.setContentBoxMinHeight=function(){
        var h=$(document).height(),
            h1=$('.banner-box').height(),
            h2=$('#downloadCon').height();
        $('.content-box ul').css('min-height',h-h1-h2);

    };

    t.reloadWorksListInfo=function(){};

    t.loadMoreWorksListInfo=function(){};

    return Topic;
});

