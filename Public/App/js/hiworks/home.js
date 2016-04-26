/**
 * Created by jimmy on 2016/4/18.
 * version-2.7
 */
define(['fx','base','iscroll'],function(fx,Base) {
    var HiWorks = function (url) {
        this.baseUrl = url;
        this.$wrapper = $('body');
        //访问来源
        var userAgent = window.location.href;
        this.userInfo = {session_id: ''};
        this.isFromApp = userAgent.indexOf("hisihi-app") >= 0;
        if(this.isFromApp){
            $('.bottom-comment').show();
        }
        this.commentListPageCount=10;  //每次加载10条评论

        var eventName='click',that=this;
        this.deviceType = this.operationType();
        if(this.deviceType.mobile){
            eventName='touchend';
        }
        this.initIScroll();

        $(document).on(eventName,'.btn',function(){
            event.stopPropagation();
        });


    };
    HiWorks.prototype =new Base();
    HiWorks.constructor=HiWorks;

    var t=HiWorks.prototype;

    /*
     *获得用户的信息 区分安卓和ios
     *从不同的平台的方法 获得用户的基本信息，进行发表评论时使用
     */
    t.getUserInfo=function (callback) {
        var userStr = '', that = this;
        if (this.deviceType.mobile) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction != "undefined") {
                    userStr = AppFunction.getUser(); //调用app的方法，得到用户的基体信息
                    //AppFunction.showShareView(true);  //调用安卓的方法，控制分享按钮可用
                }
            }
            else if (this.deviceType.ios) {
                //如果方法存在
                if (typeof getUser_iOS != "undefined") {
                    userStr = getUser_iOS();//调用app的方法，得到用户的基体信息
                }
            }
            if (userStr != '') {
                this.userInfo = JSON.parse(userStr);
                callback && callback.call(that);
            } else {
                var para = {
                    url: this.baseUrl + 'user/login',
                    type: 'get',
                    paraData: {username: '13554154325', password: '12345678', type: 1, client: 4},
                    sCallback: function (data) {
                        that.userInfo = data;
                        callback && callback.call(that);
                    }
                };
                //this.getDataAsync(para);
                callback && callback.call(that);
            }
        }
        else {
            callback && callback.call(that);
        }

    };

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
                that.fillInCommentInfo(data);

                /*标记分页信息*/
                var totalPage=Math.ceil(data.totalCount/that.commentListPageCount),
                $ul=$('#comment-list-ul');
                $ul.attr({'data-page-count':totalPage,'data-index':index})
                $loadingMore.removeClass('active').hide();
                callback && callback(data);
            },
            eCallback: function (data) {
                var txt=data.txt;
                if(data.code=404){
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
     * flag - {bool} 是列表添加（true）还是发表之后（false）添加
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
                            '<div>'+item.content +'</div>'+
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

    /*注册上拉加载更多数据*/
    t.initIScroll=function(){
        this.$pullDown=$('#pullDown');
        this.$pullUp=$('#pullUp');
        this.$downIcon=this.$pullDown.find('.icon');
        this.$upIcon=this.$pullUp.find('.icon');
        this.pullDownEl=this.$pullDown[0];
        this.pullDownOffset=this.pullDownEl.offsetHeight;
        this.pullUpEl=this.$pullUp[0];
        this.pullUpOffset=this.pullUpEl.offsetHeight;

        var that=this;



        this.myScroll=new IScroll('#wrapper',{probeType: 3, mouseWheel: true,vScrollbar:false});
        this.myScroll.on("slideDown",function() {
            if(this.y > 40){
                if(!that.$downIcon.hasClass('loading')){
                    that.$downIcon.addClass('loading');
                    that.$pullDown.find('.pullDownLabel').text('加载中...');
                    //that.pullDownAction();
                }
            }
        });

        this.myScroll.on("slideUp",function(){  that.$pullUp.show();
            if(that.maxScrollY - this.y > 40){
                if(!that.$upIcon.hasClass('loading')){

                    that.$upIcon.addClass('loading');
                    that.$pullUp.find('.pullUpLabel').text('加载中...');
                    //that.pullUpAction();
                }
            }
        });

        this.myScroll.on("scroll",function(){
            var y = this.y,
                maxY = this.maxScrollY - y,

                downHasClass = that.$downIcon.hasClass("flip"),
                upHasClass = that.$upIcon.hasClass("flip");
            console.log(y);
            if(y >= 40){
                !downHasClass && that.$downIcon.addClass("flip");
                that.$pullDown.find('.pullDownLabel').text('释放刷新');
                return;
            }else if(y < 40 && y > 0){
                downHasClass && that.$downIcon.removeClass("flip");
                that.$pullDown.find('.pullDownLabel').text('下拉刷新');
                return "";
            }

            if(maxY >= 40){
                !upHasClass && that.$upIcon.addClass("flip");
                that.$pullUp.find('.pullUpLabel').text('释放刷新');
                return;
            }else if(maxY < 40 && maxY >=0){
                upHasClass && that.$upIcon.removeClass("flip");
                that.$pullUp.find('.pullUpLabel').text('上拉加载更多');
                return;
            }

        });

    };

    /*下拉关闭*/
   t.pullDownAction=function(){
       var that=this;
        that.$downIcon.removeClass('loading');
        that.$pullDown.find('.pullDownLabel').text('下拉刷新');
       $('.comment-list-panel').hide();
       //$('.comment-list-panel').css({'z-index':'-1','opacity':'0'});
       //$('#wrapper').css('top','100%');
    };

    /*上拉刷新*/
    t.pullUpAction=function (){
        var that=this;
        $.getJSON('test.json',function(data,state){
            if(data && data.state==1 && state=='success'){
                setTimeout(function(){
                    $('#news-list').append(data.data);
                    that.myScroll.refresh();
                    that.$upIcon.removeClass('loading');
                    that.$up.find('.pullUpLabel').text('上拉加载更多');
                },600);
            }
        });
    };



    /*
    *登录功能的回调方法
    *要做三件事：
    * 1，更新点赞 和点踩的信息
    * 2，收藏更新
    * 3，评论列表对应的点赞更新,将目前已经加载下来的评论重新加载。
    */
    window.loginSuccessCallback=function(){
        var obj=window.topContentObj;
        obj.controlModelBox(0,1);

        //得到用户基本信息
        obj.getUserInfo(function(){
            obj.loadVoteInfo(); //点赞信息
            obj.getFavoriteInfo(); //收藏信息
            window.topContentObj.updateCommentInfo();  //更新评论的点赞信息
        });
    };

    window.getShareInfo=function(){
        return {title:'123',url:'123123',thumb:'',description:''};
    };

    return HiWorks;
});