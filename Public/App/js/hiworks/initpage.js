/**
 * Created by jimmy on 2016/4/27.
 */

var data,
    myScroll,
    pullDownEl,pullDownOffset,
    pullUpEl,pullUpOffset,
    generatedCount=0;
var $down,$up,$downIcon,$upIcon;
function pullDownAction(){
    $.getJSON('test.json',function(data,state){
        if(data && data.state==1 && state=='success'){
            setTimeout(function(){
                $('#news-list').html(data.data);
                myScroll.refresh();
                $downIcon.removeClass('loading');
                $down.find('.pullDownLabel').text('下拉刷新');
            },600);
        }
    });
}

function pullUpAction(){
    $.getJSON('test.json',function(data,state){
        if(data && data.state==1 && state=='success'){
            setTimeout(function(){
                $('#news-list').append(data.data);
                myScroll.refresh();
                $upIcon.removeClass('loading');
                $up.find('.pullUpLabel').text('上拉加载更多');
            },600);
        }
    });
}
//document.addEventListener('touchmove',function(e){e.preventDefault(),false});
document.addEventListener('DOMContentLoaded',loaded,false);

function loaded(){
    $down=$('#pullDown');
    $up=$('#pullUp');
    $downIcon=$down.find('.icon');
    $upIcon=$up.find('.icon');
    pullDownEl=$down[0];
    pullDownOffset=pullDownEl.offsetHeight;
    pullUpEl=$up[0];
    pullUpOffset=pullUpEl.offsetHeight;

    myScroll=new IScroll('#wrapper',{probeType: 3, mouseWheel: true,vScrollbar:false});
    myScroll.on("slideDown",function() {
        if(this.y > 40){
            if(!$downIcon.hasClass('loading')){
                $downIcon.addClass('loading');
                $down.find('.pullDownLabel').text('加载中...');
                pullDownAction();
            }
        }
    });

    myScroll.on("slideUp",function(){
        if(this.maxScrollY - this.y > 40){
            if(!$upIcon.hasClass('loading')){
                $upIcon.addClass('loading');
                $up.find('.pullUpLabel').text('加载中...');
                pullUpAction();
            }
        }
    });

    myScroll.on("scroll",function(){
        var y = this.y,
            maxY = this.maxScrollY - y,

            downHasClass = $downIcon.hasClass("flip"),
            upHasClass = $upIcon.hasClass("flip");

        if(y >= 40){
            !downHasClass && $downIcon.addClass("flip");
            $down.find('.pullDownLabel').text('释放刷新');
            return;
        }else if(y < 40 && y > 0){
            downHasClass && $downIcon.removeClass("flip");
            $down.find('.pullDownLabel').text('下拉刷新');
            return "";
        }

        if(maxY >= 40){
            !upHasClass && $upIcon.addClass("flip");
            $up.find('.pullUpLabel').text('释放刷新');
            return;
        }else if(maxY < 40 && maxY >=0){
            upHasClass && $upIcon.removeClass("flip");
            $up.find('.pullUpLabel').text('上拉加载更多');
            return;
        }
    });
}