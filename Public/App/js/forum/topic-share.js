/**
 * Created by jimmy on 2016/6/13.
 */

define(['myscroll','base'],function(MyScroll,Base){
    var Topic=function(){
        Base.call(this,true);  //属性继承
        this.tid=$('body').data('id');
        if(this.isLocal){
            window.urlObj.localApi+='/hisihi-cms';
        }
        this.initStyle();
        this.loadData();
        var s = new MyScroll($('.wrapper'), {
            //下拉刷新
            pullDownAction:$.proxy(this,'reloadWorksListInfo'),
            //上拉加载更多
            pullUpAction: $.proxy(this,'loadMoreWorksListInfo'),
        });
    };


    Topic.prototype=new Base(true);
    Topic.constructor=Topic;
    var t=Topic.prototype;

    /*样式基本控制，包括底部下载条和容器的最小高度*/
    t.initStyle=function(){
        this.setFootStyle();
        this.setContentBoxMinHeight();
    };

    /*加载数据信息*/
    t.loadData=function(){
        this.loadTopicInfo();
        this.loadTenPostsInfo();
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
        $('.wrapper').css({'bottom': h + 'px'});
    };

    /*设置主要容器的最小高度*/
    t.setContentBoxMinHeight=function(){
        var h=$('.wrapper').height(),
            h1=$('.banner-box').height();
        $('.content-box>ul').css('min-height',h-h1);

    };

    /*重新加载*/
    t.reloadWorksListInfo=function(){};

    /*加载更新帖子*/
    t.loadMoreWorksListInfo=function(){};

    /*获得topic 的基本信息*/
    t.loadTopicInfo=function(){
        var that=this,
            para={
            url:window.urlObj.api+'topic/'+ this.tid,
            type:'get',
            sCallback:function(result){
                if(result.data){
                    that.fillInTopicInfo(result.data);
                }else{
                    that.showTip('话题基本信息加载失败');
                }
            },
            eCallback:function(){
                that.showTip('话题基本信息加载失败');
            }
        }
        this.getDataAsyncPy(para);
    };

    t.fillInTopicInfo=function(data){
        var title=data.title,
            desc=data.description,
            imgUrl=data.img_url;
        title=this.substrLongStr(title,20);
        desc=this.substrLongStr(desc,200);
        if(!imgUrl){
            imgUrl='http://pic.hisihi.com/2016-06-15/1465962987445587.png';
        }
        $('.topic-real-name').text(title);
        $('.banner-desc').text(desc);
        $('.img-box img').attr('src',imgUrl);
    }

    /*加载10条帖子*/
    t.loadTenPostsInfo=function(){
        var that =this,
            para={
            url:window.urlObj.localApi+"/api.php?s=/forum/forumFilterByTopic",
            paraData:{
                topicId:this.tid,
                page:1,
                count:10
            },
            sCallback:function(result){
                if(result.success){

                }else{
                    that.showTip('话题基本信息加载失败');
                }
            },
            eCallback:function(){}
        };
        this.getDataAsync(para);
    };

    return Topic;
});

