/**
 * Created by jimmy on 2016/6/13.
 */

define(['base','lazyloading'],function(Base){
    var Topic=function(){
        Base.call(this,true);  //属性继承
        this.tid=$('body').data('id');
        if(this.isLocal){
            window.urlObj.localApi+='/hisihi-cms';
        }
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
        desc=this.substrLongStr(desc,80);
        if(!imgUrl){
            imgUrl='http://pic.hisihi.com/2016-06-15/1465962987445587.png';
        }
        $('.topic-real-name').text(title);
        $('.banner-desc').text(desc);
        $('.img-box img').attr('src',imgUrl);
    }

    /*加载10条帖子*/
    t.loadTenPostsInfo=function(){
        this.controlLoadingBox(true);
        var that =this,
            para={
            url:window.urlObj.localApi+"/api.php?s=/forum/forumFilterByTopic",
            paraData:{
                topicId:this.tid,
                page:1,
                count:10
            },
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
                    that.showTip('帖子信息加载失败');
                }
            },
            eCallback:function(){
                that.controlLoadingBox(false);
                that.showTip('帖子信息加载失败');
            }
        };
        this.getDataAsync(para);
    };

    t.fakeData=function(){
        var data=[
            {
                "content":"放个假好方法,我就是要用CSS实现九宫格图_CSS_网页制作_脚本之家.CSS布局奇淫技巧之-宽度自适应 - 无双 - 博客园",
                "create_time":"1439450761",
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
                "create_time":"1439450761",
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
                "create_time":"1439450761",
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
                "create_time":"1439450761",
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
                "create_time":"1439450761",
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
        return data;
    },

    /*填充帖子内容*/
    t.getPostInfo=function(data){
        data=this.fakeData();
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
            name=this.substrLongStr(name,8);
            type=item.orgStr;
            if(type==6){
                orgStr=this.getOrgStr(item.userInfo.extinfo);
            }
            pic=item.userInfo.avatar128;
            if(!pic){
                pic='http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
            }
            str+='<li>'+
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
                '</li>';
        }
        return str;
    };

    /*如果是老师，则取出其对应的 机构或者学校信息*/
    t.getOrgStr=function(arr){
        var str='';
        $.each(arr,function(){
            str=this.institution;
            if(str){
                return true
            }
        });
        return str;
    },

    /*得到帖子的图片信息*/
    t.getPostImgStr=function(imgList){
        var len=imgList.length,str='',cName='';
        if(len==0){
            return '';
        }
        if(len==1){
            cName='img-size1';
        }
        else if(len==2 || len==4){
            cName='img-size2';
        }
        else{
            cName='img-size3';
        }
        for(var i=0;i<len;i++){
            str+='<li class="'+cName+'">'+
                    '<img  data-original="'+imgList[0]+'">'+
                 '</li>';
        }
        return str;
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

    /*
     *根据客户端的时间信息得到发表评论的时间格式
     *多少分钟前，多少小时前，然后是昨天，然后再是月日
     */
    t.getDiffTime=function (serviceData) {
        if (serviceData.AddTime) {
            var minute = 1000 * 60;
            var hour = minute * 60;
            var day = hour * 24;
            //var recordTimeInt = Date.parse(serviceData.replace(/-/gi, "/"));
            var recordTime = new Date(serviceData.AddTime);
            var diff = new Date() - recordTime;
            var result = '';
            if (diff < 0) {
                return result;
            }
            var weekR = diff / (7 * day);
            var dayC = diff / day;
            var hourC = diff / hour;
            var minC = diff / minute;
            if (weekR >= 1) {
                result = recordTime.getFullYear() + '.' + (recordTime.getMonth() + 1) + '.' + recordTime.getDate();
                return result;
            }
            else if (dayC >= 1) {
                result = parseInt(dayC) + '天前';
                return result;
            }
            else if (hourC >= 1) {
                result = parseInt(hourC) + '小时前';
                return result;
            }
            else if (minC >= 1) {
                result = parseInt(minC) + '分钟前';
                return result;
            } else {
                result = '刚刚';
                return result;
            }
        }
        return '';
    };

    return Topic;
});

