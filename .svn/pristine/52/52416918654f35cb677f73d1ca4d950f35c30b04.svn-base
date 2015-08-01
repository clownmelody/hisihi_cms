/**
 * 气球、云朵等拖动效果
 * Created by Benz on 2014/12/16.
 */

(function ($$, window){

    window.balloonMove = function (dom) {
        dom = $$(dom);
        var isDragging = false,
            domStartPos = {x:0, y:0},
            touchStartPos = {x:0, y:0},
            domNowPos = {x:0, y:0},
            domLastFramePos = {x:0, y:0},
            domLastFrameTime = new Date(),
            speed = {x:0, y:0};

        dom.on('touchstart', function(e){
            console.log('start');
            domStartPos.x = domNowPos.x = domLastFramePos.x = parseInt(dom.css('left'));
            domStartPos.y = domNowPos.y = domLastFramePos.y = parseInt(dom.css('top'));
            touchStartPos.x = e.touches ? e.touches[0].pageX : e.pageX;
            touchStartPos.y = e.touches ? e.touches[0].pageY : e.pageY;
            domLastFrameTime = new Date();
            dom.addClass('moving');
            isDragging = true;
            startMoving();
            $$(document).off('touchmove');
            $$(document).off('touchend');

            $$(document).on('touchmove', function(e){
                var dx = (e.touches ? e.touches[0].pageX : e.pageX) - touchStartPos.x,
                    dy = (e.touches ? e.touches[0].pageY : e.pageY) - touchStartPos.y;
                domNowPos.x = domStartPos.x + dx;
                domNowPos.y = domStartPos.y + dy;
                e.preventDefault();
            });

            $$(document).on('touchend', function(e){
                $$(document).off('touchmove');
                $$(document).off('touchend');
                dom.removeClass('moving');
                isDragging = false;
                domNowPos.x += Math.round(speed.x / 16);
                domNowPos.y += Math.round(speed.y / 16);
                dom.css('left', domNowPos.x);
                dom.css('top', domNowPos.y);
                endMoving();
                e.preventDefault();
            });
            e.preventDefault();
        });


        function startMoving(){
            moving();
            console.log('startMoving', isDragging);
        }

        function moving(){
            var now = new Date(),
                dTime = now.getTime() - domLastFrameTime.getTime(),
                dx = domNowPos.x - domLastFramePos.x,
                dy = domNowPos.y - domLastFramePos.y;
            speed.x = dx * 1000 / dTime;
            speed.y = dy * 1000 / dTime;
            domLastFramePos.x = domNowPos.x;
            domLastFramePos.y = domNowPos.y;
            domLastFrameTime = now;
            console.log(speed, dTime);

            dom.css('left', domNowPos.x + 'px');
            dom.css('top', domNowPos.y + 'px');

            if (isDragging) {
                requestAnimationFrame(moving);
            }
        }

        function endMoving() {
            console.log('endMoving');
        }
    }

})($$, window);