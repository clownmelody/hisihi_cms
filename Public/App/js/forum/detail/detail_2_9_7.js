/**
 * Created by hisihi on 2016/8/31.
 */
define(['base','myPhotoSwipe'],function(Base,myPhotoSwipe) {
    var Detail = function (id) {
        this.tid = id;
        this.baseUrl = window.hisihiUrlObj.link_url;
        var eventName = 'click';
        if (this.deviceType.mobile && this.isLocal) {
            eventName = 'touchend';
            this.baseUrl = this.baseUrl.replace('api.php', 'hisihi-cms/api.php');
        }

        //提问详情 detail
        //请求地址    http://115.29.44.35/api.php?s=/forum/getPostDetail

        //获取数据
        this.loadData();

        //查看相册
        new myPhotoSwipe('.post-img', {
            bgFilter: true,
        });
    };

    //下载条
    var config = {
        downloadBar: {
            show: true,
            pos: 0
        }
    };

    Detail.prototype = new Base(config);
    Detail.constructor = Detail;
    var t = Detail.prototype;

    /*加载数据信息*/
    t.loadData = function () {
        this.loadDetailInfo();
    };


    /*获取话题帖基本详情帖*/
    t.loadDetailInfo = function () {
        var that = this,
            para = {
                url: this.baseUrl + '?s=/forum/getPostDetail/version/2.97/post_id/' + this.tid,
                type: 'get',
                sCallback: function (result) {
                    //预加载遮罩
                    if (result.data) {
                        that.fillDetailInfo(result.data);
                        $('.wrapper').css('opacity', '1');
                    } else {
                        that.showTips('帖子基本信息加载失败');
                    }
                },
                eCallback: function () {
                    that.showTips('帖子基本信息加载失败');
                }
            }
        this.getDataAsyncPy(para);
    }

    t.fillDetailInfo = function (data) {
        var title = data.title,
            desc = data.description,
        //imgUrl=data.img_url,
            title = this.substrLongStr(title, 18);
        $('title').text(title);
        //$('.info-post p').text(desc);
        this.getPostInfo(data);
    }

    /*填充帖子内容*/
    t.getPostInfo=function(data) {
        var len = data.length;
        if (len == 0) {
            $('.nodata').show();
            return '';
        }
        this.getPostBasicInfo(data);
        this.getDiscussInfo(data);
        };
        
    //贴子基本信息
    t.getPostBasicInfo=function(data){
        var str ='';
        for(var i=0;i<len;i++) {
            item = data[i];
            pic=item.userInfo.avatar128;
            name = item.userInfo.nickname;
            major = item.userInfo.extinfo[1].field_content;
            location = item.pos;
            type = item.userInfo.group;
            title = item.topic_info.title;
            //if(!pic){
            //    pic='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            //}
            //if(!item.topic_info || !item.topic_info.title) {
            //    title =$('.topic-real-name').text();
            //}else{
            //    title = item.topic_info.title;
            //}
            ////判断定位信息是否存在
            //var posStr='';
            //if(item.pos){
            //    posStr= '<div class="location-box">'+
            //            '<div id="location-img"></div>'+
            //                '<span class="location">'+item.pos+'</span>'+
            //            '</div>';
            //}
            ////判断专业信息是否存在
            //var majorStr='';
            //if(item.userInfo.extinfo[1].field_content){
            //    majorStr='<span>'+item.userInfo.extinfo[1].field_content+'</span>';
            //}
            ////判断用户是否为老师，是老师则红名显示
            //var teacherClassName='';
            //if(item.userInfo.group==6){
            //    teacherClassName='teacher-name';
            //}
            str = '<div class="user-info">' +
                '<div class="user-img">' +
                '<img src="' + pic + '">' +
                '</div>' +
                '<div class="user-txt">' +
                '<p class="name ' + teacherClassName + '">' + item.userInfo.nickname + '</p>' +
                '<p class="type">' +
                '<span>' + this.getDiffTime(item.create_time) + '</span>' +
                majorStr +
                '</p>' +
                '</div>' +
                '</div>' +

                '<div class="info-post">' +
                '<p class="post-txt">' +
                '<span class="topic-name">#' + title + '#</span>' + item.content +
                '</p>' +
                '</div>' +
                '<ul class="post-img">' +
                t.getPostImgStr(item.img) +
                '<div class="clear"></div>' +
                '</ul>' +
                posStr +
                '<div class="footer">' +
                '<div class="footer-triangle"></div>' +
                '<ul class="footer-info">' +
                '<li class="circle like-people"><img class="like-img" src="__IMG__/forum/detail/ico.jpg"></li>' +
                '<li class="circle like-people"><img class="like-img" src="__IMG__/forum/detail/ico.jpg"></li>' +
                '<li class="circle like-people"><img class="like-img" src="__IMG__/forum/detail/ico.jpg"></li>' +
                '<li class="circle like-people"><img class="like-img" src="__IMG__/forum/detail/ico.jpg"></li>' +
                '<li class="circle like-people"><img class="like-img" src="__IMG__/forum/detail/ico.jpg"></li>' +
                '<li class="circle like-people"><img class="like-img" src="__IMG__/forum/detail/ico.jpg"></li>' +
                '<li class="circle like-num"><span class="number">999</span></li>' +
                '<li class="like-btn"><div class="like-btn-img"></div></li>' +
                '</ul>' +
                '</div>';
        }
        $('.user-info-box').html(str);
    };

    t.getDiscussInfo=function(data){
        var str='<div class="discuss">'+
            '<div class="teacher">'+
            '<div class="discuss-header">'+
            '<span class="discuss-img"></span>'+
            '<p class="discuss-title"><span>名师</span><span>（32）</span></p>'+
        '</div>'+
        '<ul>'+
        '<li class="discuss-li">'+
            '<div class="discuss-user-img">'+
            '<img src="__IMG__/forum/detail/ico.jpg">'+
            '</div>'+
            '<div class="user-info">'+
            '<div class="discuss-user-info">'+
            '<div class="user-txt">'+
            '<p class="name">小野妹子爱哲学</p>'+
            '<p class="type">'+
            '<span class="time">06-17 17:06</span>'+
        '<span class="major">母猪产后护理饲养专业</span>'+
            '</p>'+
            '</div>'+
            '<div class="discuss-btn"></div>'+
            '</div>'+
            '<div class="discuss-user-txt"><p>The hard part isn’t making the decision. It’s living with it.Everything is going on, but dont give up trying.</p></div>'+
        '</div>'+
        '</li>'+
        '</ul>'+
        '</div>'+
        '</div>';
        $('.t-discuss-info-box').html(str);
    };


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

    /*设置循环数组，展示全部点赞头像*/
    t.getLikeStr=function(like){
        var like='';
        var len = data.length;
        for (var i = 0; i < len; i++) {
            var url=item.img;
            like +='<li class="circle like-people">'+
                    '<img class="like-img" src="'+url+'">'+
                '</li>';
        }
    };

    return Detail;

});
