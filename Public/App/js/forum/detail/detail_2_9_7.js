/**
 * Created by hisihi on 2016/8/31.
 */
define(['base','myPhotoSwipe','async','lazyloading'],function(Base,myPhotoSwipe,async) {
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

        //同步/异步加载数据
        this.async=true;

        //获取数据
        this.loadData();

        //查看相册
        new myPhotoSwipe('.post-img', {
            bgFilter: true,
        });

        $(document).on(eventName,'.discuss-btn', $.proxy(this,'openMask'));
        $(document).on(eventName,'.mask', $.proxy(this,'hideMask'));
    };

    //下载条，1在底部，0在顶部
    var config = {
        downloadBar: {
            show: true,
            pos: 1
        }
    };

    Detail.prototype = new Base(config);
    Detail.constructor = Detail;
    var t = Detail.prototype;

    /*加载数据信息
     * 通过回调方法
     * 获取到接口信息
     * 全部数据加载完毕后停止加载动画，显示数据内容*/
    t.loadData = function () {
        this.controlLoadingBox(true);
        var that = this;
        var asynca =  async.parallel({
            basic: function (callback) {
                that.loadDetailInfo(function (result) {
                    callback(null, result);
                });
            },
            teacher: function (callback) {
                that.loadTeacherInfo(function (result) {
                    callback(null, result);
                });
            },
            student:function(callback) {
                that.loadStudentInfo(function (result) {
                    callback(null,result);
                });
            }
        }, function (err, results) {
            var val;
            for(var item in results){
                val=results[item];
                if(!val){
                    break;
                }
                switch (item) {
                    case 'basic':
                        that.fillDetailInfo(val);
                        break;
                    case 'teacher':
                        that.getTeacherPostInfo(val);
                        break;
                    case 'student':
                        that.getStudentPostInfo(val);
                        break;
                }
            }
            $('.wrapper').css('opacity', '1');
            that.controlLoadingBox(false);
            $('.lazy-img').picLazyLoad($(window),{
                threshold:100
            });
        });
    };

    /*重新加载*/
    t.reloadWorksListInfo=function(){};

    /*加载更新帖子*/
    t.loadMoreWorksListInfo=function(){};

    /*获取话题帖基本详情帖*/
    t.loadDetailInfo = function (callback) {
        var that = this,
            para = {
                url: this.baseUrl + '?s=/forum/getPostDetail/version/2.96/post_id/' + this.tid,
                type: 'get',
                async:this.async,
                sCallback: function (result) {
                    //预加载遮罩
                    if (result.data) {
                        callback(result.data);
                    } else {
                        callback(null);
                        that.showTips('帖子基本信息加载失败');
                    }
                },
                eCallback: function () {
                    callback(null);
                    that.showTips('帖子基本信息加载失败');
                }
            }
        this.getDataAsyncPy(para);
    };

    /*填充帖子信息*/
    t.fillDetailInfo = function (data) {
        var title = data.title,
            desc = data.description,
            title = this.substrLongStr(title, 18);
        $('title').text(title);
        this.getPostInfo(data);
    }

    /*获取帖子点赞详情*/

    /*获取老师回复详情*/
    t.loadTeacherInfo = function(callback){
        var that=this,
            tpara={
                url:this.baseUrl+'?s=/forum/teacherReplyList/version/2.96/post_id/'+ this.tid,
                async:this.async,
                sCallback: function (result) {
                    //预加载遮罩
                    if (result.replyList) {
                        callback(result);
                    } else {
                        callback(null);
                        that.showTips('老师回复加载失败');
                    }
                },
                eCallback: function () {
                    callback(null);
                    that.showTips('老师回复加载失败');
                }
            }
        this.getDataAsyncPy(tpara);
    };

    /*获取学生回复详情*/
    t.loadStudentInfo = function(callback){
        var that=this,
            spara={
                url:this.baseUrl+'?s=/forum/studentReplyList/version/2.96/post_id/'+ this.tid,
                async:this.async,
                sCallback: function (result) {
                    //预加载遮罩
                    if (result.replyList) {
                        callback(result);
                    } else {
                        callback(null);
                        that.showTips('讨论加载失败');
                    }
                },
                eCallback: function () {
                    callback(null);
                    that.showTips('讨论加载失败');
                }
            }
        this.getDataAsyncPy(spara);
    };

    /*填充帖子内容*/
    t.getPostInfo=function(data) {
        var len = data.length;
        if (len == 0) {
            $('.nodata').show();
            return '';
        }
        this.getPostBasicInfo(data);
    };

    /*贴子基本信息*/
    t.getPostBasicInfo=function(data){
        var str ='';
        //判断定位信息是否存在
        var posStr='';
        if(data.pos){
            posStr= '<div class="location-box">'+
                '<div id="location-img"></div>'+
                '<span class="location">'+data.pos+'</span>'+
                '</div>';
        }
        //判断专业信息是否存在
        var majorStr='';
        if(data.userInfo.extinfo[1].field_content){
            majorStr='<span id="major">'+data.userInfo.extinfo[1].field_content+'</span>';
        }
        //判断用户是否为老师，是老师则红名显示
        var teacherClassName='';
        if(data.userInfo.group==6){
            teacherClassName='teacher-name';
        }

        //帖子话题,多个话题帖分享同显示标题蓝色
        var tempstrHtml='',
            content=data.content;
        if(content) {
            var reg=/<.*\/>/g;
            if(reg.test(content)){
                tempstrHtml = this.getTopicTitleAndContent(content);
            }else {
                tempstrHtml = this.getTopicTitleAndContent1(content);
            }
        }

        str = '<div class="user-info">' +
            '<div class="user-img">' +
            '<img src="' +data.userInfo.avatar128+ '">' +
            '</div>' +
            '<div class="user-txt">' +
            '<p class="name ' + teacherClassName + '">' + data.userInfo.nickname + '</p>' +
            '<p class="type">' +
            '<span>' + this.getDiffTime(data.create_time) + '</span>' +
            majorStr +
            '</p>' +
            '</div>' +
            '</div>' +
            '<div class="info-post">' +
            '<p class="post-txt">' +
            tempstrHtml+
            '</p>' +
            '</div>' +
            '<ul class="post-img">' +
            t.getPostImgStr(data.img) +
            '<div class="clear"></div>' +
            '</ul>' +
            posStr ;
        //注释全部底部点赞区域代码
        //'<div class="footer">' +
        //'<div class="footer-triangle"></div>' +
        //'<ul class="footer-info">' +
        //    //'<li class="circle like-people"><img class="like-img" src="../../images/forum/detail/ico.jpg"></li>' +
        //    //    t.getLikeStr(data.img) +
        ////'<li class="circle like-num">'+'<span class="number">'+data.supportCount+'</span>'+'</li>' +
        ////'<li class="like-btn"><div class="like-btn-img"></div></li>' +
        //'</ul>' +
        //'</div>';

        $('.user-info-box').html(str);
        //惰性加载
        $('.post-img img').picLazyLoad($('.wrapper'),{
            settings:10,
            placeholder:'http://pic.hisihi.com/2016-06-15/1465987988057181.png'
        });
    };

    /*
    * 正则表达式提取话题内容,将<topic id=""/>进行替换  新版本数据
    */
    t.getTopicTitleAndContent=function(str){
       // <topic id='17' title='请于废柴的我谈恋爱'/><topic id='17' title='请于废柴的我谈恋爱'/><topic id='17' title='请于废柴的我谈恋爱'/><topic id='6' title='如何提高自己的写作水平？'/>"
        str=str.replace(/<topic id='[0-9]*[1-9][0-9]*' title='/g,'<span class="topic-name">');
        str=str.replace(/<user id='[0-9]*[1-9][0-9]*' nickname='/g,'<span class="user-name">');
        str=str.replace(/'\/>/g,'</span>');
        return str;
    }

    /*
    *老版本数据
     */
    t.getTopicTitleAndContent1=function(str){
        var strHtml='';
        var arr=str.split(''),
            len=arr.length,
            j=0;
        for(var i=0;i<len;i++){
            var tempWord=arr[i];
            if(tempWord=='#'){
                if(j%2==0) {
                    tempWord = '<span class="topic-name">';
                    j=1;
                }else{
                    tempWord = '</span>';
                    j=0;
                }
            }
            strHtml+=tempWord;
        }
        return strHtml;
    }

    /*判断帖子是否有老师回复*/
    t.getTeacherPostInfo=function(result) {
        var len = result.replyList.length;
        if (len == 0) {
            //$('.nodata').show();
            return '';
        }
        this.loadTeacherPos(result,result.replyList);
    };

    /*填充老师回复内容*/
    t.loadTeacherPos=function(result,replyList) {
        var teaPosStr =  '<div class="discuss">' +
            '<div class="teacher">' +
            '<div class="discuss-header">' +
            '<span class="discuss-img"></span>' +
            '<p class="discuss-title"><span>名师</span><span class="pos-num">('+result.replyTotalCount+')</span></p>' +
            '</div>' +
            '<div class="underline"></div>'+
            '<ul>' +
            t.getTeacherDiscussInfo(replyList)+
            '</ul>' +
            '</div>' +
            '</div>' ;
        $('.t-discuss-info-box').html(teaPosStr);
    };

    /*老师回复基本信息,判断回复信息是否为语音*/
    t.getTeacherDiscussInfo=function(result){
        var len=result.length,
            str='';
        for(var i=0;i<len;i++) {
            var item=result[i];
            if (!item.content) {
                item.content = '语音回复请下载嘿设汇app查看';
            }
            str += '<li class="discuss-li">' +
                '<div class="discuss-user-img">' +
                '<img src="' +item.userInfo.avatar128+ '">' +
                '</div>' +
                '<div class="user-info">' +
                '<div class="discuss-user-info">' +
                '<div class="user-txt">' +
                '<p class="name">' + item.userInfo.nickname + '</p>' +
                '<p class="type">' +
                '<span class="time">' + this.getDiffTime(item.create_time) + '</span>' +
                '</p>' +
                '</div>' +
                '<div class="chose-area">'+
                '<div class="discuss-btn"></div>' +
                '</div>'+
                '</div>' +
                '<div class="discuss-user-txt"><p>'+item.content+'</p></div>' +
                '</div>' +
                '</li>' ;
        }
        return str;
    };

    /*学生回复基本信息,判断学生回复是否存在*/
    t.getStudentPostInfo=function(result){
        var len = result.replyList.length;
        if (len == 0) {
            //$('.nodata').show();
            return '';
        }
        this.loadStudentPos(result,result.replyList);
    };

    /*填充学生回复内容*/
    t.loadStudentPos=function(result,replyList){
        var Str =  '<div class="discuss">' +
            '<div class="teacher">' +
            '<div class="discuss-header">' +
            '<span class="discuss-img"></span>' +
            '<p class="discuss-title"><span>讨论</span><span class="pos-num">('+result.replyTotalCount+')</span></p>' +
            '</div>' +
            '<div class="underline"></div>'+
            '<ul>' +
            t.getStudentDiscussInfo(replyList)+
            '</ul>' +
            '</div>' +
            '</div>' ;
        $('.s-discuss-info-box').html(Str);
    };

    t.getStudentDiscussInfo=function(result){
        var len=result.length,
            str='';
        for(var i=0;i<len;i++) {
            var item=result[i];
            if (!item.content) {
                item.content = '语音回复请下载app查看';
            }
            str += '<li class="discuss-li">' +
                '<div class="discuss-user-img">' +
                '<img src="' + item.userInfo.avatar128 + '">' +
                '</div>' +
                '<div class="user-info">' +
                '<div class="discuss-user-info">' +
                '<div class="user-txt">' +
                '<p class="name">' + item.userInfo.nickname + '</p>' +
                '<p class="type">' +
                '<span class="time">' + this.getDiffTime(item.create_time) + '</span>' +
                '</p>' +
                '</div>' +
                '<div class="discuss-btn"></div>' +
                '</div>' +
                '<div class="discuss-user-txt"><p>' + item.content + '</p></div>' +
                '</div>' +
                '</li>';
        }
        return str;
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
            h=this.getImgWidthByNum(2);
            style='width:'+h+';height:'+h;
        }
        else{
            cName='img-size3';
            h=this.getImgWidthByNum(3);
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

    t.getImgWidthByNum=function(num){
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
    //t.getLikeStr=function(like){
    //    var like='',
    //        len = data.length;
    //    for (var i = 0; i < len; i++) {
    //        var url=item.img;
    //        like +='<li class="circle like-people">'+
    //                '<img class="like-img" src="'+url+'">'+
    //                '</li>';
    //    }
    //};

    /*点赞操作*/
    t.openMask=function(){
        this.controlMaskModal(true);
    };

    t.hideMask=function(){
        this.controlMaskModal(false);
    };

    /*下载引导页的显示和隐藏*/
    t.controlMaskModal=function(flag){
        var $target=$('.download');
        if(flag==true){
            $target.show();
        }
        else{
            $target.hide();
        }
    };

    return Detail
});
