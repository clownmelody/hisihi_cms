/**
 * Created by jimmy on 2016/6/13.
 */

define(['base','lazyloading'],function(Base){
    var Topic=function(){
        Base.call(this,true);  //属性继承
        this.tid=$('body').data('id');
        //if(this.isLocal){
            window.urlObj.localApi+='hisihi-cms/';
        //}
        this.initStyle();
        this.loadData();
    };


    Topic.prototype=new Base(true);
    Topic.constructor=Topic;
    var t=Topic.prototype;

    /*样式基本控制，包括底部下载条和容器的最小高度*/
    t.initStyle=function(){
        this.setFootStyle();
        //this.setContentBoxMinHeight();
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
        $('.wrapper').css({'bottom': h + 5+'px'});
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
                    that.showTips('话题基本信息加载失败');
                }
            },
            eCallback:function(){
                that.showTips('话题基本信息加载失败');
            }
        }
        this.getDataAsyncPy(para);
    };

    t.fillInTopicInfo=function(data){
        var title=data.title,
            desc=data.description,
            imgUrl=data.img_url;
        title=this.substrLongStr(title,18);
        desc=this.substrLongStr(desc,70);
        if(!imgUrl){
            imgUrl='http://pic.hisihi.com/2016-06-15/1465962987445587.png';
        }
        $('#post-nums').text(data.post_count);
        $('.topic-real-name').text(title);
        $('title').text(title);
        $('.banner-desc').text(desc);
        $('.img-box img').attr('src',imgUrl);
    }

    /*加载10条帖子*/
    t.loadTenPostsInfo=function(){
        this.controlLoadingBox(true);
        var that =this,
            para={
            url:window.urlObj.localApi+'api.php?s=/forum/forumFilterByTopic/topicId/'+this.tid+'/page/1/count/10',
            type:'get',
            sCallback:function(result){
                that.controlLoadingBox(false);
                if(result.success){
                    var str = that.getPostInfo(result.forumList);
                    $('.content-box ul').append(str);
                    $('.content-box img').picLazyLoad($('.wrapper'),{
                        settings:10,
                        placeholder:'http://pic.hisihi.com/2016-06-15/1465987988057181.png'
                    });
                }else{
                    that.showTips('帖子信息加载失败');
                }
            },
            eCallback:function(){
                that.controlLoadingBox(false);
                that.showTips('帖子信息加载失败');
            }
        };
        this.getDataAsync(para);
    };

    t.fakeData=function(){
        var str='{"success":true,"error_code":0,"message":"获取话题下提问列表成功","total_count":"15","forumList":[{"content":"测试咯","create_time":"1465888652","last_reply_time":"1465987653","view_count":"2","reply_count":"1","type":"","is_inner":"1","cover_id":"0","community":null,"post_id":"5769","forumTitle":"设计吐槽\/吐槽专区","userInfo":{"uid":72,"avatar256":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2015-12-22\/56792a426d0b5-05505543_256_256.jpg","avatar128":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2015-12-22\/56792a426d0b5-05505543_128_128.jpg","group":6,"nickname":"Leslie","extinfo":[{"id":"36","field_name":"college","field_title":"所在大学","field_content":"中国地质大学(武汉)"},{"id":"37","field_name":"major","field_title":"所学专业","field_content":"信息工程呵呵复发辜负"},{"id":"39","field_name":"institution","field_title":"任职公司","field_content":"星光绘画培训机构1"},{"id":"44","field_name":"expected_position","field_title":"期望职位","field_content":"Java开发工程师"},{"id":"46","field_name":"my_strengths","field_title":"我的优势","field_content":"我的优势就是没有优势～"}]},"first_teacher":null,"topic_info":{"title":"端午加班了吗","description":"在确定软件开发可行的情况下，对软件需要实现的各个功能进行详细分析。需求分析阶段是一个很重要的阶段，这一阶段做得好，将为整个软件开发项目的成功打下良好的基础。\"唯一不变的是变化本身。\"，同样需求也是在整个软件开发过程中不断变化和深入的，因此我们必须制定需求变更计划来应付这种变化，以保护整个项目的顺利进行","img_url":"http:\/\/pic.hisihi.com\/2016-06-15\/5760eb8a4794f.png","is_hot":"1"},"img":[{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-13\/575e56077286e.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-13\/575e56077286e_280_160.jpg","src_size":[1000,1334],"size":[160,212]}],"sound":null,"supportCount":"0","isSupportd":"0","pos":"武汉","shareUrl":"app.php\/forum\/detail\/type\/view\/post_id\/5769"},{"content":"iOS手机，标签插入文字描述测试","create_time":"1465979469","last_reply_time":"1465979469","view_count":"1","reply_count":"0","type":"","is_inner":"1","cover_id":"0","community":null,"post_id":"5779","forumTitle":"创意设计\/广告设计","userInfo":{"uid":574,"avatar256":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2016-03-24\/56f35da522682-05505543_256_256.jpg","avatar128":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2016-03-24\/56f35da522682-05505543_128_128.jpg","group":6,"nickname":13554154325,"extinfo":[{"id":"36","field_name":"college","field_title":"所在大学","field_content":null},{"id":"37","field_name":"major","field_title":"所学专业","field_content":null},{"id":"39","field_name":"institution","field_title":"任职公司","field_content":"英雄联盟开黑组"},{"id":"44","field_name":"expected_position","field_title":"期望职位","field_content":null},{"id":"46","field_name":"my_strengths","field_title":"我的优势","field_content":null}]},"first_teacher":null,"topic_info":{"title":"端午加班了吗","description":"在确定软件开发可行的情况下，对软件需要实现的各个功能进行详细分析。需求分析阶段是一个很重要的阶段，这一阶段做得好，将为整个软件开发项目的成功打下良好的基础。\"唯一不变的是变化本身。\"，同样需求也是在整个软件开发过程中不断变化和深入的，因此我们必须制定需求变更计划来应付这种变化，以保护整个项目的顺利进行","img_url":"http:\/\/pic.hisihi.com\/2016-06-15\/5760eb8a4794f.png","is_hot":"1"},"img":[{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-15\/57611247cd600.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-15\/57611247cd600_280_160.jpg","src_size":[852,1136],"size":[160,212]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-15\/5761124a1aac8.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-15\/5761124a1aac8_280_160.jpg","src_size":[852,1136],"size":[160,212]}],"sound":null,"supportCount":"0","isSupportd":"0","pos":null,"shareUrl":"app.php\/forum\/detail\/type\/view\/post_id\/5779"},{"content":"Android手机，标签插入文字描述测试","create_time":"1465979381","last_reply_time":"1465979381","view_count":"2","reply_count":"0","type":"","is_inner":"1","cover_id":"0","community":null,"post_id":"5778","forumTitle":"3DMax\/景观设计","userInfo":{"uid":621,"avatar256":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/default\/default_256_256.jpg","avatar128":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/default\/default_128_128.jpg","group":5,"nickname":"memeda","extinfo":[{"id":"36","field_name":"college","field_title":"所在大学","field_content":"华中农业大学"},{"id":"37","field_name":"major","field_title":"所学专业","field_content":null},{"id":"44","field_name":"expected_position","field_title":"期望职位","field_content":null},{"id":"46","field_name":"my_strengths","field_title":"我的优势","field_content":null}]},"first_teacher":null,"topic_info":{"title":"端午加班了吗","description":"在确定软件开发可行的情况下，对软件需要实现的各个功能进行详细分析。需求分析阶段是一个很重要的阶段，这一阶段做得好，将为整个软件开发项目的成功打下良好的基础。\"唯一不变的是变化本身。\"，同样需求也是在整个软件开发过程中不断变化和深入的，因此我们必须制定需求变更计划来应付这种变化，以保护整个项目的顺利进行","img_url":"http:\/\/pic.hisihi.com\/2016-06-15\/5760eb8a4794f.png","is_hot":"1"},"img":[{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-15\/576111f5bdebb.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-15\/576111f5bdebb_280_160.jpg","src_size":[664,374],"size":[284,160]}],"sound":null,"supportCount":"0","isSupportd":"0","pos":"武汉市","shareUrl":"app.php\/forum\/detail\/type\/view\/post_id\/5778"},{"content":"很多很多经济的基督教基督教","create_time":"1465963158","last_reply_time":"1465963158","view_count":"1","reply_count":"0","type":"","is_inner":"1","cover_id":"0","community":null,"post_id":"5773","forumTitle":"3DMax\/景观设计","userInfo":{"uid":610,"avatar256":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/default\/default_256_256.jpg","avatar128":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/default\/default_128_128.jpg","group":5,"nickname":"mnmn","extinfo":[{"id":"36","field_name":"college","field_title":"所在大学","field_content":"北京语言大学"},{"id":"37","field_name":"major","field_title":"所学专业","field_content":"跌"},{"id":"44","field_name":"expected_position","field_title":"期望职位","field_content":"行政专员"},{"id":"46","field_name":"my_strengths","field_title":"我的优势","field_content":"滴滴"}]},"first_teacher":null,"topic_info":{"title":"端午加班了吗","description":"在确定软件开发可行的情况下，对软件需要实现的各个功能进行详细分析。需求分析阶段是一个很重要的阶段，这一阶段做得好，将为整个软件开发项目的成功打下良好的基础。\"唯一不变的是变化本身。\"，同样需求也是在整个软件开发过程中不断变化和深入的，因此我们必须制定需求变更计划来应付这种变化，以保护整个项目的顺利进行","img_url":"http:\/\/pic.hisihi.com\/2016-06-15\/5760eb8a4794f.png","is_hot":"1"},"img":null,"sound":null,"supportCount":"0","isSupportd":"0","pos":"武汉市","shareUrl":"app.php\/forum\/detail\/type\/view\/post_id\/5773"},{"content":"测试咯","create_time":"1465888792","last_reply_time":"1465888792","view_count":"1","reply_count":"0","type":"","is_inner":"1","cover_id":"0","community":null,"post_id":"5771","forumTitle":"设计吐槽\/吐槽专区","userInfo":{"uid":72,"avatar256":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2015-12-22\/56792a426d0b5-05505543_256_256.jpg","avatar128":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2015-12-22\/56792a426d0b5-05505543_128_128.jpg","group":6,"nickname":"Leslie","extinfo":[{"id":"36","field_name":"college","field_title":"所在大学","field_content":"中国地质大学(武汉)"},{"id":"37","field_name":"major","field_title":"所学专业","field_content":"信息工程呵呵复发辜负"},{"id":"39","field_name":"institution","field_title":"任职公司","field_content":"星光绘画培训机构1"},{"id":"44","field_name":"expected_position","field_title":"期望职位","field_content":"Java开发工程师"},{"id":"46","field_name":"my_strengths","field_title":"我的优势","field_content":"我的优势就是没有优势～"}]},"first_teacher":null,"topic_info":{"title":"端午加班了吗","description":"在确定软件开发可行的情况下，对软件需要实现的各个功能进行详细分析。需求分析阶段是一个很重要的阶段，这一阶段做得好，将为整个软件开发项目的成功打下良好的基础。\"唯一不变的是变化本身。\"，同样需求也是在整个软件开发过程中不断变化和深入的，因此我们必须制定需求变更计划来应付这种变化，以保护整个项目的顺利进行","img_url":"http:\/\/pic.hisihi.com\/2016-06-15\/5760eb8a4794f.png","is_hot":"1"},"img":[{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377e11293d.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377e11293d_280_160.jpg","src_size":[1334,1000],"size":[213,160]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377da23890.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377da23890_280_160.jpg","src_size":[1000,1334],"size":[160,212]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafdc474c2.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafdc474c2_280_160.jpg","src_size":[1334,1000],"size":[213,160]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafe24cfee.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafe24cfee_280_160.jpg","src_size":[1334,1000],"size":[213,160]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafea75c55.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafea75c55_280_160.jpg","src_size":[1334,1000],"size":[213,160]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377dd84c37.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377dd84c37_280_160.jpg","src_size":[1000,1334],"size":[160,212]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafe5dcec5.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafe5dcec5_280_160.jpg","src_size":[1334,1000],"size":[213,160]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafdedaa33.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafdedaa33_280_160.jpg","src_size":[1334,1000],"size":[213,160]}],"sound":null,"supportCount":"0","isSupportd":"0","pos":null,"shareUrl":"app.php\/forum\/detail\/type\/view\/post_id\/5771"},{"content":"测试咯","create_time":"1465888750","last_reply_time":"1465888750","view_count":"1","reply_count":"0","type":"","is_inner":"1","cover_id":"0","community":null,"post_id":"5770","forumTitle":"设计吐槽\/吐槽专区","userInfo":{"uid":72,"avatar256":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2015-12-22\/56792a426d0b5-05505543_256_256.jpg","avatar128":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2015-12-22\/56792a426d0b5-05505543_128_128.jpg","group":6,"nickname":"Leslie","extinfo":[{"id":"36","field_name":"college","field_title":"所在大学","field_content":"中国地质大学(武汉)"},{"id":"37","field_name":"major","field_title":"所学专业","field_content":"信息工程呵呵复发辜负"},{"id":"39","field_name":"institution","field_title":"任职公司","field_content":"星光绘画培训机构1"},{"id":"44","field_name":"expected_position","field_title":"期望职位","field_content":"Java开发工程师"},{"id":"46","field_name":"my_strengths","field_title":"我的优势","field_content":"我的优势就是没有优势～"}]},"first_teacher":null,"topic_info":{"title":"端午加班了吗","description":"在确定软件开发可行的情况下，对软件需要实现的各个功能进行详细分析。需求分析阶段是一个很重要的阶段，这一阶段做得好，将为整个软件开发项目的成功打下良好的基础。\"唯一不变的是变化本身。\"，同样需求也是在整个软件开发过程中不断变化和深入的，因此我们必须制定需求变更计划来应付这种变化，以保护整个项目的顺利进行","img_url":"http:\/\/pic.hisihi.com\/2016-06-15\/5760eb8a4794f.png","is_hot":"1"},"img":[{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377e11293d.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377e11293d_280_160.jpg","src_size":[1334,1000],"size":[213,160]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377da23890.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377da23890_280_160.jpg","src_size":[1000,1334],"size":[160,212]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafdc474c2.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafdc474c2_280_160.jpg","src_size":[1334,1000],"size":[213,160]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafe24cfee.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafe24cfee_280_160.jpg","src_size":[1334,1000],"size":[213,160]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafea75c55.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafea75c55_280_160.jpg","src_size":[1334,1000],"size":[213,160]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377dd84c37.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2015-11-12\/564377dd84c37_280_160.jpg","src_size":[1000,1334],"size":[160,212]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafe5dcec5.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafe5dcec5_280_160.jpg","src_size":[1334,1000],"size":[213,160]},{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafdedaa33.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575fafdedaa33_280_160.jpg","src_size":[1334,1000],"size":[213,160]}],"sound":null,"supportCount":"0","isSupportd":"0","pos":null,"shareUrl":"app.php\/forum\/detail\/type\/view\/post_id\/5770"},{"content":"端午節不加班","create_time":"1465877407","last_reply_time":"1465888235","view_count":"1","reply_count":"1","type":"","is_inner":"1","cover_id":"0","community":null,"post_id":"5767","forumTitle":"新生入口\/学生专栏","userInfo":{"uid":598,"avatar256":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/default\/default_256_256.jpg","avatar128":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/default\/default_128_128.jpg","group":5,"nickname":"老船长","extinfo":[{"id":"36","field_name":"college","field_title":"所在大学","field_content":"福建对外经济贸易职业技术学院"},{"id":"37","field_name":"major","field_title":"所学专业","field_content":null},{"id":"44","field_name":"expected_position","field_title":"期望职位","field_content":null},{"id":"46","field_name":"my_strengths","field_title":"我的优势","field_content":null}]},"first_teacher":null,"topic_info":{"title":"端午加班了吗","description":"在确定软件开发可行的情况下，对软件需要实现的各个功能进行详细分析。需求分析阶段是一个很重要的阶段，这一阶段做得好，将为整个软件开发项目的成功打下良好的基础。\"唯一不变的是变化本身。\"，同样需求也是在整个软件开发过程中不断变化和深入的，因此我们必须制定需求变更计划来应付这种变化，以保护整个项目的顺利进行","img_url":"http:\/\/pic.hisihi.com\/2016-06-15\/5760eb8a4794f.png","is_hot":"1"},"img":[{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575f839c2ab4e.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-14\/575f839c2ab4e_280_160.jpg","src_size":[1000,1334],"size":[160,212]}],"sound":null,"supportCount":1,"isSupportd":"0","pos":null,"shareUrl":"app.php\/forum\/detail\/type\/view\/post_id\/5767"},{"content":"但他、他不知道為什麼就學著","create_time":"1465815369","last_reply_time":"1465815369","view_count":"1","reply_count":"0","type":"","is_inner":"1","cover_id":"0","community":null,"post_id":"5766","forumTitle":"设计吐槽\/吐槽专区","userInfo":{"uid":598,"avatar256":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/default\/default_256_256.jpg","avatar128":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/default\/default_128_128.jpg","group":5,"nickname":"老船长","extinfo":[{"id":"36","field_name":"college","field_title":"所在大学","field_content":"福建对外经济贸易职业技术学院"},{"id":"37","field_name":"major","field_title":"所学专业","field_content":null},{"id":"44","field_name":"expected_position","field_title":"期望职位","field_content":null},{"id":"46","field_name":"my_strengths","field_title":"我的优势","field_content":null}]},"first_teacher":null,"topic_info":{"title":"端午加班了吗","description":"在确定软件开发可行的情况下，对软件需要实现的各个功能进行详细分析。需求分析阶段是一个很重要的阶段，这一阶段做得好，将为整个软件开发项目的成功打下良好的基础。\"唯一不变的是变化本身。\"，同样需求也是在整个软件开发过程中不断变化和深入的，因此我们必须制定需求变更计划来应付这种变化，以保护整个项目的顺利进行","img_url":"http:\/\/pic.hisihi.com\/2016-06-15\/5760eb8a4794f.png","is_hot":"1"},"img":null,"sound":null,"supportCount":"0","isSupportd":"0","pos":null,"shareUrl":"app.php\/forum\/detail\/type\/view\/post_id\/5766"},{"content":"","create_time":"1465814969","last_reply_time":"1465814969","view_count":"1","reply_count":"0","type":"","is_inner":"1","cover_id":"0","community":null,"post_id":"5765","forumTitle":"设计吐槽\/吐槽专区","userInfo":{"uid":72,"avatar256":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2015-12-22\/56792a426d0b5-05505543_256_256.jpg","avatar128":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2015-12-22\/56792a426d0b5-05505543_128_128.jpg","group":6,"nickname":"Leslie","extinfo":[{"id":"36","field_name":"college","field_title":"所在大学","field_content":"中国地质大学(武汉)"},{"id":"37","field_name":"major","field_title":"所学专业","field_content":"信息工程呵呵复发辜负"},{"id":"39","field_name":"institution","field_title":"任职公司","field_content":"星光绘画培训机构1"},{"id":"44","field_name":"expected_position","field_title":"期望职位","field_content":"Java开发工程师"},{"id":"46","field_name":"my_strengths","field_title":"我的优势","field_content":"我的优势就是没有优势～"}]},"first_teacher":null,"topic_info":{"title":"端午加班了吗","description":"在确定软件开发可行的情况下，对软件需要实现的各个功能进行详细分析。需求分析阶段是一个很重要的阶段，这一阶段做得好，将为整个软件开发项目的成功打下良好的基础。\"唯一不变的是变化本身。\"，同样需求也是在整个软件开发过程中不断变化和深入的，因此我们必须制定需求变更计划来应付这种变化，以保护整个项目的顺利进行","img_url":"http:\/\/pic.hisihi.com\/2016-06-15\/5760eb8a4794f.png","is_hot":"1"},"img":[{"src":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-13\/575e8fb68fbac.jpg","thumb":"http:\/\/forum-pic.oss-cn-qingdao.aliyuncs.com\/2016-06-13\/575e8fb68fbac_280_160.jpg","src_size":[1334,1000],"size":[213,160]}],"sound":{"url":"http:\/\/forum-sound.oss-cn-qingdao.aliyuncs.com\/2016-06-13\/575e8fb5853a7.amr","duration":"1"},"supportCount":"0","isSupportd":"0","pos":null,"shareUrl":"app.php\/forum\/detail\/type\/view\/post_id\/5765"},{"content":"哈喽，你好","create_time":"1465814892","last_reply_time":"1465814892","view_count":"2","reply_count":"0","type":"","is_inner":"1","cover_id":"0","community":null,"post_id":"5764","forumTitle":"3DMax\/景观设计","userInfo":{"uid":566,"avatar256":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2015-11-13\/5645b325d2dd7-05505543_256_256.jpg","avatar128":"http:\/\/hisihi-avator.oss-cn-qingdao.aliyuncs.com\/2015-11-13\/5645b325d2dd7-05505543_128_128.jpg","group":5,"nickname":"皇帝的新衣jj","extinfo":[{"id":"36","field_name":"college","field_title":"所在大学","field_content":"中国地质大学(武汉)"},{"id":"37","field_name":"major","field_title":"所学专业","field_content":"设计师"},{"id":"44","field_name":"expected_position","field_title":"期望职位","field_content":"平面设计师"},{"id":"46","field_name":"my_strengths","field_title":"我的优势","field_content":"全国一等奖"}]},"first_teacher":null,"topic_info":{"title":"端午加班了吗","description":"在确定软件开发可行的情况下，对软件需要实现的各个功能进行详细分析。需求分析阶段是一个很重要的阶段，这一阶段做得好，将为整个软件开发项目的成功打下良好的基础。\"唯一不变的是变化本身。\"，同样需求也是在整个软件开发过程中不断变化和深入的，因此我们必须制定需求变更计划来应付这种变化，以保护整个项目的顺利进行","img_url":"http:\/\/pic.hisihi.com\/2016-06-15\/5760eb8a4794f.png","is_hot":"1"},"img":null,"sound":null,"supportCount":1,"isSupportd":"0","pos":"武汉市","shareUrl":"app.php\/forum\/detail\/type\/view\/post_id\/5764"}]}';
        var data=[
            {
                "content":"放个假好方法,我就是要用CSS实现九宫格图_CSS_网页制作_脚本之家.CSS布局奇淫技巧之-宽度自适应 - 无双 - 博客园",
                "create_time":"1466043712",
                "last_reply_time":"1439455644",
                "view_count":"5",
                "reply_count":"1",
                "type":"",
                "is_inner":"1",
                "cover_id":"0",
                "community":null,
                "post_id":"5210",
                "forumTitle":"设计吐槽/吐槽专区",
                "userInfo":{
                    "uid":"102",
                    "avatar256":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_256_256.jpg",
                    "avatar128":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_128_128.jpg",
                    "group":"5",
                    "nickname":"15934133729",
                    "extinfo":[
                        {
                            "id":"36",
                            "field_name":"college",
                            "field_title":"所在大学",
                            "field_content":null
                        },
                        {
                            "id":"37",
                            "field_name":"major",
                            "field_title":"所学专业",
                            "field_content":null
                        },
                        {
                            "id":"44",
                            "field_name":"expected_position",
                            "field_title":"期望职位",
                            "field_content":null
                        },
                        {
                            "id":"46",
                            "field_name":"my_strengths",
                            "field_title":"我的优势",
                            "field_content":null
                        }
                    ]
                },
                "first_teacher":"Leslie",
                "topic_info":{
                    "title":"端午加班了吗",
                    "description":"中国网财经6月14日讯 据农业部网站消息，农业部办公厅日前发布关于组织开展深化农垦改革专项试点工作的通知，通知决定在全国各垦区组织开展深化农垦改革专项试点工作，通过试点先行，探索改革路径，积累改革经验，全面推进农垦各项改革。",
                    "img_url":"http://pic.hisihi.com/2016-05-13/5735761750b1d.png",
                    "is_hot":"1"
                },
                "img":['http://pic.hisihi.com/2016-05-19/1463654404426501.png'],
                "sound":null,
                "supportCount":"2",
                "isSupportd":"0",
                "pos":null,
                "shareUrl":"app.php/forum/detail/type/view/post_id/5210"
            },
            {
                "content":"改革试点名单的集团旗下迄今为止尚未有实质性改革进展的公司，这类企业往往存在较强的改革预期；五是存在入选第二批央企改革试点名单可能的企业。 中信证券认为，目前",
                "create_time":"1465963932",
                "last_reply_time":"1439455644",
                "view_count":"5",
                "reply_count":"1",
                "type":"",
                "is_inner":"1",
                "cover_id":"0",
                "community":null,
                "post_id":"5211",
                "forumTitle":"设计吐槽/吐槽专区",
                "userInfo":{
                    "uid":"102",
                    "avatar256":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_256_256.jpg",
                    "avatar128":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_128_128.jpg",
                    "group":"5",
                    "nickname":"15934133729",
                    "extinfo":[
                        {
                            "id":"36",
                            "field_name":"college",
                            "field_title":"所在大学",
                            "field_content":null
                        },
                        {
                            "id":"37",
                            "field_name":"major",
                            "field_title":"所学专业",
                            "field_content":null
                        },
                        {
                            "id":"44",
                            "field_name":"expected_position",
                            "field_title":"期望职位",
                            "field_content":null
                        },
                        {
                            "id":"46",
                            "field_name":"my_strengths",
                            "field_title":"我的优势",
                            "field_content":null
                        }
                    ]
                },
                "first_teacher":"Leslie",
                "topic_info":{
                    "title":"端午加班了吗",
                    "description":"中国网财经6月14日讯 据农业部网站消息，农业部办公厅日前发布关于组织开展深化农垦改革专项试点工作的通知，通知决定在全国各垦区组织开展深化农垦改革专项试点工作，通过试点先行，探索改革路径，积累改革经验，全面推进农垦各项改革。",
                    "img_url":"http://pic.hisihi.com/2016-05-13/5735761750b1d.png",
                    "is_hot":"1"
                },
                "img":['http://pic.hisihi.com/2016-05-19/1463654404426501.png'],
                "sound":null,
                "supportCount":"2",
                "isSupportd":"0",
                "pos":null,
                "shareUrl":"app.php/forum/detail/type/view/post_id/5210"
            },
            {
                "content":"浙江、安徽、广西、甘肃等明确要求优化国有资本重点投资方向和领域，引导其更多投向战略性新兴产业等关键领域。并购、重组、上市是国有企业资产证券化的主要途径，多省将大力推动地方国企改制上市。",
                "create_time":"1465618332",
                "last_reply_time":"1439455644",
                "view_count":"5",
                "reply_count":"1",
                "type":"",
                "is_inner":"1",
                "cover_id":"0",
                "community":null,
                "post_id":"5212",
                "forumTitle":"设计吐槽/吐槽专区",
                "userInfo":{
                    "uid":"102",
                    "avatar256":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_256_256.jpg",
                    "avatar128":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_128_128.jpg",
                    "group":"5",
                    "nickname":"15934133729",
                    "extinfo":[
                        {
                            "id":"36",
                            "field_name":"college",
                            "field_title":"所在大学",
                            "field_content":null
                        },
                        {
                            "id":"37",
                            "field_name":"major",
                            "field_title":"所学专业",
                            "field_content":null
                        },
                        {
                            "id":"44",
                            "field_name":"expected_position",
                            "field_title":"期望职位",
                            "field_content":null
                        },
                        {
                            "id":"46",
                            "field_name":"my_strengths",
                            "field_title":"我的优势",
                            "field_content":null
                        }
                    ]
                },
                "first_teacher":"Leslie",
                "topic_info":{
                    "title":"端午加班了吗",
                    "description":"中国网财经6月14日讯 据农业部网站消息，农业部办公厅日前发布关于组织开展深化农垦改革专项试点工作的通知，通知决定在全国各垦区组织开展深化农垦改革专项试点工作，通过试点先行，探索改革路径，积累改革经验，全面推进农垦各项改革。",
                    "img_url":"http://pic.hisihi.com/2016-05-13/5735761750b1d.png",
                    "is_hot":"1"
                },
                "img":['http://pic.hisihi.com/2016-05-19/1463654402085592.png','http://pic.hisihi.com/2016-05-19/1463654400267875.png'],
                "sound":null,
                "supportCount":"2",
                "isSupportd":"0",
                "pos":null,
                "shareUrl":"app.php/forum/detail/type/view/post_id/5210"
            },
            {
                "content":"推进儿童医疗卫生服务改革 记者注意到，此次深改组会议不仅仅聚焦儿科医务人员培养问题，还提出要完善儿童医疗卫生服务体系、推进儿童医疗卫生服务领域改革",
                "create_time":"1465531932",
                "last_reply_time":"1439455644",
                "view_count":"5",
                "reply_count":"1",
                "type":"",
                "is_inner":"1",
                "cover_id":"0",
                "community":null,
                "post_id":"5210",
                "forumTitle":"设计吐槽/吐槽专区",
                "userInfo":{
                    "uid":"102",
                    "avatar256":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_256_256.jpg",
                    "avatar128":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_128_128.jpg",
                    "group":"5",
                    "nickname":"15934133729",
                    "extinfo":[
                        {
                            "id":"36",
                            "field_name":"college",
                            "field_title":"所在大学",
                            "field_content":null
                        },
                        {
                            "id":"37",
                            "field_name":"major",
                            "field_title":"所学专业",
                            "field_content":null
                        },
                        {
                            "id":"44",
                            "field_name":"expected_position",
                            "field_title":"期望职位",
                            "field_content":null
                        },
                        {
                            "id":"46",
                            "field_name":"my_strengths",
                            "field_title":"我的优势",
                            "field_content":null
                        }
                    ]
                },
                "first_teacher":"Leslie",
                "topic_info":{
                    "title":"端午加班了吗",
                    "description":"中国网财经6月14日讯 据农业部网站消息，农业部办公厅日前发布关于组织开展深化农垦改革专项试点工作的通知，通知决定在全国各垦区组织开展深化农垦改革专项试点工作，通过试点先行，探索改革路径，积累改革经验，全面推进农垦各项改革。",
                    "img_url":"http://pic.hisihi.com/2016-05-13/5735761750b1d.png",
                    "is_hot":"1"
                },
                "img":['http://pic.hisihi.com/2016-05-19/1463654397503936.png','http://pic.hisihi.com/2016-05-19/1463654398206341.jpg','http://pic.hisihi.com/2016-05-19/1463654396285620.png'],
                "sound":null,
                "supportCount":"2",
                "isSupportd":"0",
                "pos":null,
                "shareUrl":"app.php/forum/detail/type/view/post_id/5210"
            },
            {
                "content":"文仅代表作者个人观点，与凤凰网无关。其原创性以及文中陈述文字和内容未经本站证实，对本文以及其中全部或者部分内容、文字的真实性、完整性、及时性本站不作任何保证或承诺，请读者仅作参考，并请自行核实相关内容。",
                "create_time":"1466032332",
                "last_reply_time":"1439455644",
                "view_count":"5",
                "reply_count":"1",
                "type":"",
                "is_inner":"1",
                "cover_id":"0",
                "community":null,
                "post_id":"5213",
                "forumTitle":"设计吐槽/吐槽专区",
                "userInfo":{
                    "uid":"102",
                    "avatar256":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_256_256.jpg",
                    "avatar128":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_128_128.jpg",
                    "group":"5",
                    "nickname":"15934133729",
                    "extinfo":[
                        {
                            "id":"36",
                            "field_name":"college",
                            "field_title":"所在大学",
                            "field_content":null
                        },
                        {
                            "id":"37",
                            "field_name":"major",
                            "field_title":"所学专业",
                            "field_content":null
                        },
                        {
                            "id":"44",
                            "field_name":"expected_position",
                            "field_title":"期望职位",
                            "field_content":null
                        },
                        {
                            "id":"46",
                            "field_name":"my_strengths",
                            "field_title":"我的优势",
                            "field_content":null
                        }
                    ]
                },
                "first_teacher":"Leslie",
                "topic_info":{
                    "title":"端午加班了吗",
                    "description":"中国网财经6月14日讯 据农业部网站消息，农业部办公厅日前发布关于组织开展深化农垦改革专项试点工作的通知，通知决定在全国各垦区组织开展深化农垦改革专项试点工作，通过试点先行，探索改革路径，积累改革经验，全面推进农垦各项改革。",
                    "img_url":"http://pic.hisihi.com/2016-05-13/5735761750b1d.png",
                    "is_hot":"1"
                },
                "img":['http://pic.hisihi.com/2016-05-19/1463654395617301.png','http://pic.hisihi.com/2016-05-19/1463654393079826.jpg','http://pic.hisihi.com/2016-05-19/1463654391995651.png','http://pic.hisihi.com/2016-05-19/1463654390470330.png','http://pic.hisihi.com/2016-05-19/1463654388561165.png'],
                "sound":null,
                "supportCount":"2",
                "isSupportd":"0",
                "pos":null,
                "shareUrl":"app.php/forum/detail/type/view/post_id/5210"
            },
            {
                "content":"文仅代表作者个人观点，与凤凰网无关。其原创性以及文中陈述文字和内容未经本站证实，对本文以及其中全部或者部分内容、文字的真实性、完整性、及时性本站不作任何保证或承诺，请读者仅作参考，并请自行核实相关内容。",
                "create_time":"1466032332",
                "last_reply_time":"1439455644",
                "view_count":"5",
                "reply_count":"1",
                "type":"",
                "is_inner":"1",
                "cover_id":"0",
                "community":null,
                "post_id":"5213",
                "forumTitle":"设计吐槽/吐槽专区",
                "userInfo":{
                    "uid":"102",
                    "avatar256":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_256_256.jpg",
                    "avatar128":"http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/default/default_128_128.jpg",
                    "group":"5",
                    "nickname":"15934133729",
                    "extinfo":[
                        {
                            "id":"36",
                            "field_name":"college",
                            "field_title":"所在大学",
                            "field_content":null
                        },
                        {
                            "id":"37",
                            "field_name":"major",
                            "field_title":"所学专业",
                            "field_content":null
                        },
                        {
                            "id":"44",
                            "field_name":"expected_position",
                            "field_title":"期望职位",
                            "field_content":null
                        },
                        {
                            "id":"46",
                            "field_name":"my_strengths",
                            "field_title":"我的优势",
                            "field_content":null
                        }
                    ]
                },
                "first_teacher":"Leslie",
                "topic_info":{
                    "title":"端午加班了吗",
                    "description":"中国网财经6月14日讯 据农业部网站消息，农业部办公厅日前发布关于组织开展深化农垦改革专项试点工作的通知，通知决定在全国各垦区组织开展深化农垦改革专项试点工作，通过试点先行，探索改革路径，积累改革经验，全面推进农垦各项改革。",
                    "img_url":"http://pic.hisihi.com/2016-05-13/5735761750b1d.png",
                    "is_hot":"1"
                },
                "img":['http://pic.hisihi.com/2016-05-19/1463654395617301.png','http://pic.hisihi.com/2016-05-19/1463654393079826.jpg','http://pic.hisihi.com/2016-05-19/1463654391995651.png','http://pic.hisihi.com/2016-05-19/1463654390470330.png','http://pic.hisihi.com/2016-05-19/1463654388561165.png'],
                "sound":null,
                "supportCount":"2",
                "isSupportd":"0",
                "pos":null,
                "shareUrl":"app.php/forum/detail/type/view/post_id/5210"
            },
        ];
        data=JSON.parse(str).forumList;
        return data;
    },

    /*填充帖子内容*/
    t.getPostInfo=function(data){
        //data=this.fakeData();
        var len=data.length;
        if(len==0){
            $('.nodata').show();
            return '';
        }
        var  str='',
             item=null,
             name='',pic='',
             type='',
             orgStr='';
        for(var i=0;i<len;i++){
            item=data[i];
            name=item.userInfo.nickname;
            type=item.userInfo.group;
            if(type==6){
                orgStr=this.getOrgStr(item.userInfo.extinfo);
            }
            pic=item.userInfo.avatar128;
            if(!pic){
                pic='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            str+='<li><div class="li-main">'+
                    '<div class="user-info">'+
                        '<div class="left">'+
                            '<div class="left-img">'+
                                '<img src="'+pic+'">'+
                            '</div>'+
                            '<div class="right-txt">'+
                                '<p class="name">'+name+'</p>'+
                                '<p class="type">'+
                                    '<span>'+this.getDiffTime(item.create_time)+'</span>'+
                                    '<span>'+item.forumTitle+'</span>'+
                                '</p>'+
                            '</div>'+
                        '</div>'+
                        '<div class="right">'+orgStr+'</div>'+
                        '<div style="clear: both;"></div>'+
                    '</div>'+
                    '<p class="post-word">'+
                        '<span class="topic-name">#'+item.topic_info.title+'#</span>' +item.content+
                    '</p>'+
                    '<ul class="post-img-box">'+
                        t.getPostImgStr(item.img)+
                        '<div style="clear: both"></div>'+
                    '</ul>'+
                '</div></li>';
        }
        return str;
    };

    /*如果是老师，则取出其对应的 机构或者学校信息*/
    t.getOrgStr=function(arr){
        var str='',
            title='',
            con='';
        $.each(arr,function(){
            title=this.field_title;
            if(title && title=='任职公司') {
                con = this.field_content;
                if (con) {
                    str = con
                }
                return true;
            }
        });
        return str;
    },

    /*得到帖子的图片信息*/
    t.getPostImgStr=function(imgList){
        if(!imgList){
            return '';
        }
        var len=imgList.length;
        if(len==0){
            return '';
        }
        var str='',
            cName='',
            h='',
            style='';
        if(len==1){
            cName='img-size1';
        }
        else if(len==2 || len==4){
            cName='img-size2';
            h=this.getImgWidthByNums(2);
            style='width:'+h+';height:'+h;
        }
        else{
            cName='img-size3';
            h=this.getImgWidthByNums(3);
            style='width:'+h+';height:'+h;
        }
        for(var i=0;i<len;i++){
            var url=imgList[i].thumb;
            if(!url){
                url='http://pic.hisihi.com/2016-06-02/1464833264193150.png';
            }

            str+='<li class="'+cName+'" style="'+style+'">'+
                    '<img  data-original="' + url + '">'+
                 '</li>';
        }
        return str;
    };

    t.getImgWidthByNums=function(num){
        var radio = 0.3;
        if(num==2) {
            radio=0.4;
        }
        var width = $('body').width() - 23,
            lw = width * radio;
        return lw + 'px';
    };

    /*根据图片的数量，得到图片的宽度*/
    t.getImgSizeClassByLen=function(num){
        var cName='img-class1';
        switch (num){
            case 1:

                break;
            case 2:
                cName='img-class2';
                break;
            case 3:
                cName='img-class3';
                break;
            case 4:
                break;
        }
    };


    return Topic;
});

