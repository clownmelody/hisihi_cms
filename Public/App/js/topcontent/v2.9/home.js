/**
 * modified by jimmy on 2016/07/04.
 * version-2.9
 */
define(['fx','base'],function(fx,Base) {
    var Topcontent = function (id) {
        //Base.call(this);

        var eventName='click',
            that=this;
        this.baseUrl=window.hisihiUrlObj.link_url;
        this.$wrapper = $(document);
        this.articleId = id;
        this.commentListPageCount=10;  //每次加载10条评论
        if(this.isLocal){
            eventName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        }
        if(!this.isFromApp){ //访问来源
            this.setFootStyle();
            $('#downloadCon').show();
        }


        this.$wrapper.on(eventName, '.up-comment-box', $.proxy(this, 'execVotUpForComment'));

        /*重新加载评论*/
        this.$wrapper.on(eventName, '.loadCommentAgain',function(){that.loadCommentInfo(1,that.commentListPageCount);});

        /*滚动加载更多评论*/
        $(window).on('scroll',function(e){
            that.scrollContainer(e);
        });

        $(document).on(eventName,'.btn',function(){
            event.stopPropagation();
        });
        //加载评论信息
        this.getUserInfo(function(){
            that.loadCommentInfo(1,that.commentListPageCount);
        },0);
    };

    Topcontent.prototype =new Base();
    Topcontent.constructor=Topcontent;

    var t=Topcontent.prototype;


    /*加载前10个评论信息*/
    t.loadCommentInfo=function(index,pCount,callback){
        var that = this,
            paraData={id: this.articleId,page:index,count:pCount};
        if(this.userInfo.session_id!==''){
            paraData.session_id=this.userInfo.session_id;
        }

        //显示加载效果
        var $loadingMore=$('.loading-more-tips'),
            $loadingMoreMain=$loadingMore.find('.loadingMoreResultTipsMain');
        $loadingMoreMain.show();
        $loadingMore.addClass('active').show();

        var para = {
            url: this.baseUrl + 'document/getTopContentComments',
            type: 'get',
            paraData: paraData,
            sCallback: function (data) {
                that.fillInCommentInfo(data,true);

                /*标记分页信息*/
                var totalPage=Math.ceil(data.totalCount/that.commentListPageCount),
                $ul=$('#comment-list-ul');
                $ul.attr({'data-page-count':totalPage,'data-index':index});
                $loadingMore.removeClass('active').hide();
                callback && callback(data);
            },
            eCallback: function (data) {
                var txt=data.txt;
                if(data.code==404){
                    txt='评论信息加载失败';
                }
                that.showTips.call(that,txt);
                $('.no-comment-info').hide();

                $loadingMore.removeClass('active');
                var $loadingError=$loadingMore.find('.loadError');  //加载失败对象
                $loadingMoreMain.hide();
                $loadingError.show();

                callback && callback();
            },
        };
        this.getDataAsync(para);
    },

    /*
     *展示评论信息
     * para：
     * result - {array} 查询结果数据 格式为：
     *   {
     *       isOpposed: "0"
     *       isSupported: "0"
     *       opposeCount: "0"
     *       supportCount: "0"
     *    }
     *
     * flag - {bool} 是列表添加（true）,  发表之后（false）添加
     */
    t.fillInCommentInfo=function(result,flag){
            var count=result.totalCount;
            if(result && count>0){
                var dataList=result.data,
                    len=dataList.length,
                    str='',
                    $ul=$('#comment-list-ul'),
                    item;

                for(var i=0;i<len;i++){
                    item=dataList[i];
                    var upNum= item.support_count | 0,
                        nClass='num',
                        uClass='icon-thumb_up icon-font-a';
                    if(upNum>0){
                        if(upNum>9999){
                            upNum='10k+';
                        }
                    }else{
                        upNum='';
                    }
                    if(item.isSupported){
                        nClass+=' active';
                        uClass +=' active';
                    }
                    var name=item.user_info.username;
                    name=this.substrLongStr(name,12);
                    str+='<li>'+
                        '<div class="list-main-left">'+
                        '<img src="'+item.user_info.avatar_url+'">'+
                        '</div>'+
                        '<div class="list-main-right">'+
                        '<div>'+name+'</div>'+
                        '<div>'+this.getTimeFromTimestamp(item.create_time,'yyyy-MM-dd hh:mm')+'</div>'+
                        '<div><p>'+item.content +'</p></div>'+
                        '</div>'+
                        '<div class="up-comment-box" data-id="'+item.id+'" id="up-comment-'+item.id +'">'+
                        '<span class="'+uClass+'"></span>'+
                        '<span class="'+nClass+'">'+upNum+'</span>'+
                        '</div>'+
                        '</li>';
                }
                $ul.next().hide();
                if(flag) {
                    $ul.append(str);
                }else{
                    $ul.prepend(str);
                }
                $('#comment-counts').text(count);
                if(count>9999){
                    count='10k+';
                }
                $('.comment-red-bubble').text(count).show();
                $('.no-comment-info').hide();
            }
            else{
                $('.no-comment-info').show();
            }

            if($('body').attr('data-loaded')=='false'){
                //$('body').attr('data-loaded','true');
                //this.initIScroll();
            }
        };


    /*赞同评论*/
    t.execVotUpForComment=function(e){
        if(!this.isFromApp){
            return;
        }
        var $target = $(e.currentTarget),
            $thumb=$target.find('.icon-thumb_up');
        if($target.hasClass('voting')){
            this.showTips('操作过于频繁');
            return;
        }
        //没有登录

        if (this.userInfo.session_id==='') {
            //提示登录框跳转方法
            this.doLogin();
            return;
        }
        //不能取消点赞 和 重复点赞
        if($thumb.hasClass('active')){
            this.showTips('你已经点过赞了');
            return;
        }else{
            $thumb.addClass('active');
        }
        $thumb.addClass('animate');
        $target.addClass('voting');

        var url = this.baseUrl + '/document/doTopContentCommentSupport',
            that = this;
        var para = {
            url: url,
            type: 'get',
            paraData: {session_id: this.userInfo.session_id, id: $target.attr('data-id')},
            sCallback: function (data) {
                $target.removeClass('voting');
                if(data.success){
                    var $num=$target.find('.num'),
                        num=$num.text() | 0;
                    num++;
                    $num.addClass('active').text(num);
                }
            },
            eCallback: function (data) {
                $target.removeClass('voting');
                that.showTips.call(that,data.txt);
            },
        };
        //this.getDataAsync(para);
    };

    /*正在投票*/
    t.isVoting=function(){
        return $('body .bottom-voteCon>div').hasClass('voting');
    };


    /*滚动到评论列表*/
    t.scrollToComment=function(){
        var $target=$('body'),h=0;
        if(!$target.hasClass('toComment')){
            $target.addClass('toComment');
            h=$('.headlines-body').height();
        }else{
            $target.removeClass('toComment');
        }
        window.scrollTo(0, h);
    };


    /*控制按钮的可用性*/
    t.controlCommitBtn=function(e){
        var $this=$(e.currentTarget);
        var txt=$this.val().trim(),
            $btn=$('#right-box'),
            nc='abled  btn';
        if(txt){
            $btn.addClass(nc);
        }else{
            $btn.removeClass(nc);
        }
    };

    /*再次加载*/
    t.loadCommentAgain=function(e){
        $(e.currentTarget).hide();
        var index=$('.list-main').attr('data-index') | 0;
        this.loadCommentInfo(index,this.commentListPageCount);
    },


    /*发表评论*/
    t.commitComment=function(e){
        var $textarea=$('#comment-area'),
            str=$textarea.val().replace(/(^\s*)|(\s*$)/g,''),
            that=this,
            $target=$(e.currentTarget);
        if(str==''){
            that.showTips.call(that,'内容为空');
            return;
        }
        if(!this.userInfo.session_id){
            that.showTips.call(that,'请登录');
            return;
        }
        $target.addClass('disabled').removeClass('abled');
        var para = {
            url: this.baseUrl+'document/doCommentOnTopContent',
            type: 'get',
            paraData: {"id": this.articleId,"content":str,"session_id": this.userInfo.session_id},
            sCallback: function (data) {
                that.closeCommentBox();
                that.showTips.call(that,'评论成功');
                $textarea.val('');
                var c= Number($('#comment-counts').text());
                c++;
                var info={
                    content: str,
                    create_time: data.create_time,
                    id: data.comment_id,
                    isSupported: 0,
                    support_count: "0",
                    uid: that.userInfo.uid,
                    user_info: {
                        uid: that.userInfo.uid,
                        avatar_url: that.userInfo.pic,
                        username: that.userInfo.name
                    }

                };
                that.fillInCommentInfo({data:[info],totalCount:c});

            },
            eCallback: function (data) {
                $target.removeClass('disabled').addClass('abled');
                that.showTips.call(that,data.txt);
            },
        };
        this.getDataAsync(para);
    };


    /*
     *滚动加载更多的数据
     * 通过滚动条是否在底部来确定
     * 同时通过 loadingData 类 来防止连续快速滚动导致的重复加载
     */
    t.scrollContainer=function(e){
        var $ul=$('#comment-list-ul');
        var pageIndex=Number($ul.attr('data-index')),
            page= $ul.attr('data-page-count');
        if(pageIndex==page || page=='0'){
            //this.showTips('没有更多评论了');
            return;
        }

        var target= $('body')[0],
            viewH = $('html')[0].clientHeight,//可见高度
            contentH=target.scrollHeight,//内容高度
            scrollTop=$(target).scrollTop(),//滚动高度
            diff=contentH - viewH - scrollTop;
        if (diff<400 && !$(target).hasClass('loadingData')) {  //滚动到底部
            $(target).addClass('loadingData');
            pageIndex++;

            this.loadCommentInfo(pageIndex,this.commentListPageCount,function(){
                $(target).removeClass('loadingData');
            });
        }
    };

    //登录后更新评论的点赞信息
    t.updateCommentInfo=function(){
        var that = this,
            $li=$('#comment-list-ul li'),
            paraData={id: this.articleId,page:0,count:$li.length};
        if(this.userInfo.session_id!==''){
            paraData.session_id=this.userInfo.session_id;
        }
        var para = {
            url: this.baseUrl + 'document/getTopContentComments',
            type: 'get',
            paraData: paraData,
            sCallback: $.proxy(this,'updateUpsInfoForComment')
        };
        this.getDataAsync(para);
    };

    /*更新相对应的点赞信息*/
    t.updateUpsInfoForComment=function(result){
        if(result && result.data){
            var data=result.data,
                len=data.length;
            for(var i=0;i<len;i++){
                var item=data[i],
                    id=item.id;
                if(item.isSupported){
                    var $up=$('#up-comment-'+id);
                    if($up.length>0) {
                        $up.find('span').eq(0).addClass('active');
                    }
                }
            }
        }
    };



    /***********************以下方法为app调用************************/
    /*
    *登录功能的回调方法
    *要做三件事：
    * 1，更新点赞 和点踩的信息
    * 2，收藏更新
    * 3，评论列表对应的点赞更新,将目前已经加载下来的评论重新加载。
    */
    window.loginSuccessCallback=function(){
        var obj=window.topContentObj;

        //得到用户基本信息
        obj.getUserInfo(function(){
            window.topContentObj.updateCommentInfo();  //更新评论的点赞信息
        });
    };

    window.getShareInfo=function(){
        return {title:'123',url:'123123',thumb:'',description:''};
    };

    /*
    * 发表成功后，传入评论信息，添加到列表中
    */
    window.sendCommentInfoToWebView=function(id,str){
        var c = parseInt($('#comment-counts').text())+1,
            obj=window.topContentObj,
            userInfo=obj.userInfo;
        var info={
            content: str,
            create_time: new Date()/1000,
            id: id,
            isSupported: 0,
            support_count: "0",
            uid: userInfo.uid,
            user_info: {
                uid: userInfo.uid,
                avatar_url: userInfo.pic,
                username: userInfo.name
            }
        };
        obj.fillInCommentInfo({data:[info],totalCount:c},false);

    };

    /*
     * 滑动到评论列表区
     */
    window.scrollToComment=function() {
        window.topContentObj.scrollToComment();

    };
    return Topcontent;

});