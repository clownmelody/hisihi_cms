/**
 * Created by jimmy on 2016/6/13.
 */

define(['myscroll'],function(MyScroll){
    var Topic=function(){
        this.controlCoverFootStyle();

        var s = new MyScroll($('.wrapper'), {
            //下拉刷新
            pullDownAction:$.proxy(this,'reloadWorksListInfo'),
            //上拉加载更多
            pullUpAction: $.proxy(this,'loadMoreWorksListInfo'),
        });
    };

    var t=Topic.prototype;

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

    t.reloadWorksListInfo=function(){};

    t.loadMoreWorksListInfo=function(){};

    return Topic;
});

