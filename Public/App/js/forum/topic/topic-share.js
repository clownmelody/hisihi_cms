/**
 * Created by jimmy on 2016/6/13.
 */

define(['base','myPhotoSwipe','lazyloading'],function(Base,MyPhotoSwipe){
    var Topic=function(){
        this.tid=$('body').data('id');
        this.baseUrl=window.hisihiUrlObj.link_url;
        var eventsName='click';
        if(this.isLocal){
            eventsName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        }

        //查看图片
        //$(document).on(eventsName,'.post-img-box li', $.proxy(this,'viewPics'));
        this.initStyle();
        this.loadData();

        //photoswipe
        new MyPhotoSwipe('.post-img-box',{
            bgFilter:true,
        });

        //点赞和评论弹出下载引导页
         $(document).on(eventsName,'.choseArea', $.proxy(this,'openMask'));
        //$(document).on(eventsName,'.btn-good', $.proxy(this,'openMask'));
        //$(document).on(eventsName,'.btn-discuss', $.proxy(this,'openMask'));
        //$(document).on(eventsName,'.btn-share', $.proxy(this,'openMask'));
        $(document).on(eventsName,'.mask', $.proxy(this,'hideMask'));
    };


    Topic.prototype=new Base();
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
            url:window.hisihiUrlObj.api_url+'/v1/topic/'+ this.tid,
            type:'get',
            sCallback:function(result){
                if(result.data){
                    that.fillInTopicInfo(result.data);
                    $('.wrapper').css('opacity','1');
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
            imgUrl=data.img_url,
        title=this.substrLongStr(title,18);
        if(!imgUrl){
            imgUrl='http://pic.hisihi.com/2016-06-15/1465962987445587.png';
        }
        $('#post-nums').text(data.post_count);
        $('.topic-real-name').text(title);
        $('title').text(title);
        $('.banner-desc').text(desc);
        var h=$(document).width()*9/16+'px';
        $('.img-box img').attr('src',imgUrl).css('height',h);
    }

    /*加载10条帖子*/
    t.loadTenPostsInfo=function(){
        this.controlLoadingBox(true);
        var that =this,
            para={
            url:this.baseUrl+'?s=/forum/forumFilterByTopic/topicId/'+this.tid+'/page/1/count/10',
            type:'get',
            sCallback:function(result){
                that.controlLoadingBox(false);
                if(result.success){
                    var str = that.getPostInfo(result.forumList);
                    $('.content-box ul').append(str);



                    //惰性加载
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

    /*填充帖子内容*/
    t.getPostInfo=function(data){
        var len=data.length;
        if(len==0){
            $('.nodata').show();
            return '';
        }
        var  str='',
             item=null,
             name='',pic='',
             type='',
             orgStr='',title='';
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
            if(!item.topic_info || !item.topic_info.title) {
                title =$('.topic-real-name').text();
            }else{
                title = item.topic_info.title;
            }
            var posStr='';
            //判断定位信息是否存在
            if(item.pos){
                posStr='<div class="location-box">'+
                    //t.getLocationToProvince(item.pos)+
                '<div id="location-img"></div>'+
                '<span class="location">'+item.pos+'</span>'+
                '</div>';
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
                        '<span class="topic-name">#'+title+'#</span>' +item.content+
                    '</p>'+
                    '<ul class="post-img-box">'+
                        t.getPostImgStr(item.img)+
                        '<div style="clear: both"></div>'+
                    '</ul>'+
                    //新增发帖定位和学校信息栏
                        posStr+
                    '<ul class="btn-box">'+
                        '<li><div class="choseArea btn-good"><div id="btn-good"></div></div></li>'+
                        '<li><div class="choseArea btn-discuss"><div id="btn-discuss"></div></div></li>'+
                        '<li><div class="choseArea btn-share"><div id="btn-share"></div></div></li>'+
                    '</ul>'+
                '</div></li>';
        }
        return str;
    };

    t.viewPics=function(e){
        var $target=$(e.currentTarget),
            index=$target.index(),
            imgArr=[],
            $li =$target.parent().find('li'),
            len=$li.length,
            that=this;
        for(var i=0;i<len;i++){
            var url=$li.eq(i).find('img').attr('src');
            imgArr.push(url);
        }
        var slider = new Myslider(imgArr,{
            index:index,
            showOrHideCallback:function(type){
                var flag=type=='show'?false:true;
                that.scrollControl(flag);  //恢复滚动
            }
        });

        slider.show();
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
            style='',
            size='';
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
            var url=imgList[i].src,
                thumb=imgList[i].thumb;
            if(!thumb){
                thumb='http://pic.hisihi.com/2016-06-02/1464833264193150.png';
            }
            if(!url){
                url='http://pic.hisihi.com/2016-06-02/1464867656861374.png';
            }
            size=imgList[i].src_size;
            str+='<li class="'+cName+'" style="'+style+'">'+
                    '<a href="'+url+'" data-size="'+size[0]+'x'+size[1]+'"></a>'+
                    '<img  data-original="' + thumb + '">'+
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



    /*点赞和评论操作*/
    t.openMask=function(){
        this.controlMaskModal(true);
    };

    t.hideMask=function(){
        this.controlMaskModal(false);
    };

    /*下载引导页的显示和隐藏*/
    t.controlMaskModal=function(flag){
        var $target=$('.mask');
        if(flag==true){
            $target.show();
        }
        else{
            $target.hide();
        }
    };

    return  Topic;
});

