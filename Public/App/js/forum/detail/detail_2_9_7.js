/**
 * Created by hisihi on 2016/8/31.
 */
define(['base','myPhotoSwipe','lazyloading'],function(Base,MyPhotoSwipe) {
    var Detail = function () {
        this.tid = $('body').data('id');
        this.baseUrl = window.hisihiUrlObj.link_url;
        var eventName = 'click';
        if (this.isLocal) {
            eventName = 'touchend';
            this.baseUrl = this.baseUrl.replace('api.php', 'hisihi-cms/api.php');
        }

        //查看图片
        //$(document).on(eventsName,'.post-img-box li', $.proxy(this,'viewPics'));
        this.initStyle();
        this.loadData();

        //photoswipe
        new MyPhotoSwipe('.post-img-box', {
            bgFilter: true,
        });
    };

    Detail.prototype = new Base();
    Detail.constructor = Detail;
    var t = Detail.prototype;

    /*样式基本控制，包括底部下载条和容器的最小高度*/
    t.initStyle=function(){
        this.setFootStyle();
    };

    /*加载数据信息*/
    t.loadData=function(){
        this.loadDetailInfo();
    };

    /*控制底部logo的位置样式*/
    t.setFootStyle=function() {
        var $target = $('#downloadCon'),
            $a = $target.find('a'),
            aw = $a.width(),
            ah = aw * 0.40,
            bw = $(document).width(),
            h = bw * 120 / 750;
        $target.css({'height': h + 'px', 'opacity': 1});
        $('.wrapper').css({'bottom': h + 5+'px'});
    };

    /*获取话题帖基本详情帖*/
    t.loadDetailInfo=function () {
        var that=this,
            para={
                url:window.hisihiUrlObj.api_url+'/getPostDetail/'+ this.tid,
                type:'get',
                sCallback:function(result){
                    if(result.data){
                        that.fillInTopicInfo(result.data);
                        $('.wrapper').css('opacity','1');
                    }else{
                        that.showTips('帖子基本信息加载失败');
                    }
                },
                eCallback:function(){
                    that.showTips('帖子基本信息加载失败');
                }
            }
        this.getDataAsyncPy(para);
    }

    return Detail;
});