/**
 * Created by jimmy on 2016/4/18.
 * version-2.7
 */
define(['fx','base'],function(fx,Base) {
    var Topcontent = function (id, url) {
        this.baseUrl = url;
        this.$wrapper = $('body');
        //访问来源
        var userAgent = window.location.href;
        this.articleId = id;
        this.userInfo = {session_id: ''};
        this.isFromApp = userAgent.indexOf("hisihi-app") >= 0;
        if(!this.isFromApp){
            $('.headlines-head').show();
        }else{
            $('.bottom-comment').show();
        }
        this.commentListPageCount=10;  //每次加载20条评论

        var eventName='click',that=this;
        this.deviceType = this.operationType();
        if(this.deviceType.mobile){
            eventName='touchend';
        }
        //加载投票信息
        this.getUserInfo(function(){
            that.getFavoriteInfo();
            that.loadVoteInfo();
            that.loadCommentInfo(1,this.commentListPageCount);
        });


        this.$wrapper.on(eventName, '.bottom-voteCon .leftItem', $.proxy(this, 'execVoteUp'));
        this.$wrapper.on(eventName, '.bottom-voteCon .rightItem', $.proxy(this, 'execVoteDown'));
        this.$wrapper.on(eventName, '.up-comment-box', $.proxy(this, 'execVotUpForComment'));

        /*查看评论，收藏，分享*/
        this.$wrapper.on(eventName, '.comment-bubble', $.proxy(this, 'scrollToComment'));
        this.$wrapper.on(eventName, '.comment-collect', $.proxy(this, 'execFavorite'));
        this.$wrapper.on(eventName, '.comment-share', $.proxy(this, 'execShare'));



        //控制输入框的状态，当有信息输入的时候才可用
        this.$wrapper.on('input', '#comment-area', $.proxy(this, 'controlCommitBtn'));


        this.$wrapper.on(eventName,'.loadCommentAgain', $.proxy(this, 'loadCommentAgain'));



        /*显示评论框*/
        this.$wrapper.on(eventName, '#comment-input', function(){
            that.showCommentBox();
        });

        /*关闭评论框*/
        this.$wrapper.on(eventName, '#left-box', $.proxy(this, 'closeCommentBox'));

        /*发表评论*/
        this.$wrapper.on(eventName, '#right-box', $.proxy(this, 'commitComment'));

        /*关闭登录提示框*/
        this.$wrapper.on(eventName, '#cancle-login', $.proxy(this, 'closeLoginBox'));
        this.$wrapper.on(eventName, '#do-login', $.proxy(this, 'doLogin'));

        /*滚动加载更多评论*/
        $(document).on('touchend','body',function(e){
            that.scrollContainer(e);
        });

        $(document).on(eventName,'.btn',function(){});


    };
    Topcontent.prototype =new Base();
    Topcontent.constructor=Topcontent;

    var t=Topcontent.prototype;

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
     */
    t.fillInCommentInfo=function(result){
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
                str+='<li>'+
                        '<div class="list-main-left">'+
                            '<img src="'+item.user_info.avatar_url+'">'+
                            '</div>'+
                            '<div class="list-main-right">'+
                            '<div>'+item.user_info.username+'</div>'+
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
            $ul.append(str);
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

    /*是否收藏了该文章*/
    t.getFavoriteInfo=function(){
        var that = this,
            paraData={id: this.articleId};
        if(this.userInfo.session_id!==''){
            paraData.session_id=this.userInfo.session_id;
        }
        var para = {
            url: this.baseUrl + '/document/isFavorited',
            type: 'get',
            paraData: paraData,
            sCallback: function (data) {
                if(data.status==1 && data.success){
                    $('.comment-collect').find('span').addClass('active');
                }
            },
            eCallback: function (data) {
                //that.showTips.call(that,data.txt);
            },
        };
        this.getDataAsync(para);
    };

    /*
    *通过点赞和 踩的 人数
    */
    t.loadVoteInfo=function () {
        var that = this,
            paraData={id: this.articleId};
        if(this.userInfo.session_id!==''){
            paraData.session_id=this.userInfo.session_id;
        }
        var para = {
            url: this.baseUrl + 'public/topContentInfo',
            type: 'get',
            paraData: paraData,
            sCallback: function (data) {
                that.fillInVoteInfo(data.data,false);
                that.saveCurrentVoteInfo(); //存储当前投票信息
            },
            eCallback: function (data) {
                if(data.code=='404'){
                    data.txt='投票信息加载失败';
                }
                that.showTips.call(that,data.txt);
            },
        };
        this.getDataAsync(para);
    };

    /*
     *展示投票信息
     * para：
     * data - {array} 查询结果数据 格式为：
     *   {
     *       isOpposed: "0"
     *       isSupported: "0"
     *       opposeCount: "0"
     *       supportCount: "0"
     *    }
     */
    t.fillInVoteInfo=function (data,flag) {
        if (!data) {
            return;
        }
        var upCount = data.supportCount | 0,
            downCount = data.opposeCount | 0,
            isUp = data.isSupported == '1',
            isDown = data.isOpposed == '1';

        var $voteItem = $('body').find('.icon-container'),
            $ups = $voteItem.eq(0),
            $downs = $voteItem.eq(1);
        $ups.find('.num').text(upCount);
        $downs.find('.num').text(downCount);
        $voteItem.find('.active').removeClass('active');

        //控制长度和 记录是否已经点过赞、踩
        if (isUp) {
            $ups.find('span').addClass('active');
        }
        if (isDown) {
            $downs.find('span').addClass('active');
        }
        if(flag){
            return;
        }
    };


    /*赞同投票*/
    t.execVoteUp=function (e) {
        if(!this.isFromApp){
            return;
        }
        var $target = $(e.currentTarget),
            $thumb=$target.find('.icon-thumb');
        if($target.hasClass('voting') || $thumb.hasClass('active')){
            return;
        }
        //没有登录
        if (this.userInfo.session_id==='') {

            //提示登录框跳转方法
            this.controlModelBox(1,1,'请先登录后再点赞');
            return;
        }
        //正在投票
        if (this.isVoting()) {
            this.showTips('操作过于频繁');
            return;
        }

        $thumb.addClass('active animate');
        $target.addClass('voting');
        this.finishVote(1);

        var url = this.baseUrl + 'document/doSupport',
            that = this;
        var para = {
            url: url,
            type: 'get',
            paraData: {session_id: this.userInfo.session_id, id: this.articleId},
            sCallback: function () {
                //that.finishVote(1);
                $target.removeClass('voting');
                $thumb.removeClass('animate');
                that.saveCurrentVoteInfo.call(that); //存储当前投票信息
            },
            eCallback: function (data) {
                $target.removeClass('voting');
                $thumb.removeClass('animate');
                that.showTips.call(that,data.txt);
                var typeNum=-1;
                //已经操作
                if(data.code==-100){
                    typeNum=1;
                }
                that.finishVote.call(that, -1,typeNum);

            },
        };
        this.getDataAsync(para);
    };

    /*踩投票*/
    t.execVoteDown=function (e) {
        if(!this.isFromApp){
            return;
        }
        var $target = $(e.currentTarget),
            $thumb=$target.find('.icon-thumb');
        if($target.hasClass('voting') || $thumb.hasClass('active')){
            return;
        }

        //没有登录
        if (this.userInfo.session_id==='') {
            //提示登录框跳转方法
            this.controlModelBox(1,1,'请先登录后再点踩');
            return;
        }
        //正在投票
        if (this.isVoting()) {
            this.showTips('操作过于频繁');
            return;
        }
        $thumb.addClass('active animate');
        $target.addClass('voting');
        this.finishVote(0);
        var url = this.baseUrl + 'document/doOppose',
            that = this;
        var para = {
            url: url,
            type: 'get',
            paraData: {session_id: this.userInfo.session_id, id: this.articleId},
            sCallback: function (data) {
                //that.finishVote(0);
                $target.removeClass('voting');
                $thumb.removeClass('animate');
                that.saveCurrentVoteInfo.call(that); //存储当前投票信息
            },
            eCallback: function (data) {
                $target.removeClass('voting');
                $thumb.removeClass('animate');
                that.showTips.call(that,data.txt);
                var typeNum=-1;
                //已经操作
                if(data.code==-100){
                    typeNum=0;
                }
                that.finishVote.call(that, -1,typeNum);
            },
        };
        this.getDataAsync(para);
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
            this.controlModelBox(1,1,'请先登录后再点赞');
            return;
        }
        //不能取消点赞 和 重复点赞
        if($thumb.hasClass('active')){
            this.showTips('你已经点过赞了');
            return;
        }else{
            $thumb.addClass('active')
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
                $thumb.removeClass('animate');
                if(data.success){
                    var $num=$target.find('.num'),
                        num=$num.text() | 0;
                    num++;
                    $num.addClass('active').text(num);
                }
            },
            eCallback: function (data) {
                $target.removeClass('voting');
                $thumb.removeClass('animate');
                that.showTips.call(that,data.txt);
            },
        };
        this.getDataAsync(para);
    };

    /*正在投票*/
    t.isVoting=function(){
        return $('body .bottom-voteCon>div').hasClass('voting');
    };

    /*收藏文章*/
    t.execFavorite=function(e){
        var $target = $(e.currentTarget),
            $star=$target.find('.icon-star_border'),
            url=this.baseUrl +'document/doFavorite';
        if($target.hasClass('voting')){
            this.showTips('操作过于频繁');
            return;
        }
        //没有登录
        if (this.userInfo.session_id==='') {
            //提示登录框跳转方法
            this.controlModelBox(1,1,'请先登录后再收藏');
            return;
        }
        if($star.hasClass('active')){
            $star.removeClass('active');
            url=this.baseUrl + 'document/deleteFavorite';
        }else{
            $star.addClass('active');
        }
        $star.addClass('animate');
        $target.addClass('voting');
        //alert($star.attr('class'));
        var that = this,
            para = {
            url: url,
            type: 'get',
            paraData: {session_id: this.userInfo.session_id, id: this.articleId},
            sCallback: function () {
                $target.removeClass('voting');
                $star.removeClass('animate');
            },
            eCallback: function (data) {
                $target.removeClass('voting');
                $star.removeClass('animate');
                if(data.code==-102){
                    data.txt='您还没有收藏过';
                }
                that.showTips.call(that,data.txt);
            },
        };
        this.getDataAsync(para);
    };

    /*分享文章*/
    t.execShare=function(){
        if (this.deviceType.android) {
            if (typeof AppFunction.share != "undefined") {
                AppFunction.share();//调用app的方法，得到用户的基体信息
            }

        }
        else if(this.deviceType.ios){
            //如果方法存在
            if (typeof beginShare != "undefined") {
                beginShare();//调用app的方法，得到用户的基体信息
            }
        }
    };


    /*
     *显示操作结果
     *para:
     *tip - {string} 内容结果
     */
    t.showTips=function(tip){
        if(tip.length>8){
            tip=tip.substr(0,7)+'…';
        }
        var $tip=$('body').find('.result-tips'),
            $p=$tip.find('p').text(tip);
        $tip.show();
        window.setTimeout(function(){
            $tip.hide();
            $p.text('');
        },1500);
    };

    /*
     * 投票回调
     * para：
     * flag - {num} 操作类型 0 表示踩; 1表示赞; -1表示投票失败，信息回滚
     * type - {num} 在flag 为-1时使用 0 表示踩; 1表示赞;
     */
    t.finishVote=function (flag,type) {
        var $voteItem = $('body').find('.icon-container'),
            $ups = $voteItem.eq(0),
            $downs = $voteItem.eq(1),
            $up=$ups.find('.icon-thumb'),
            $down=$downs.find('.icon-thumb'),
            $left=$ups.find('.num'),
            $right=$downs.find('.num');

        /*正常操作*/
        if (flag !== -1) {
            var havedVote=this.havedVote(); //是否参与过投票
            //具体赞和踩的数据更新
            if (flag == 1) {
                this.updateNum($left,1);
                var tempNum=0;
                if(havedVote){
                    tempNum=-1;
                }
                this.updateNum($right, tempNum);//对反方向处理 加(减)1
            } else {
                this.updateNum($right,1);
                var tempNum=0;
                if(havedVote){
                    tempNum=-1;
                }
                this.updateNum($left, tempNum);//对反方向处理 加(减)1
            }

            //按钮样式
            if (flag == 1) {
                $up.add($left).addClass('active');
                $down.add($right).removeClass('active');
            } else {
                $up.add($left).removeClass('active');
                $down.add($right).addClass('active');
            }
        }

        //信息回滚 操作
        else {
            var oldData = this.$wrapper.attr('data-oldinfo');
            if (oldData) {
                oldData = JSON.parse(oldData);
            }
            if(type==0){
                oldData.isOpposed=1;
                oldData.isSupported=0;
            }else if(type==1){
                oldData.isOpposed=0;
                oldData.isSupported=1;
            }
            this.fillInVoteInfo(oldData,false);
            this.saveCurrentVoteInfo();
        }
    };

    /*更新目标数据*/
    t.updateNum= function ($target, diff) {
        if (!$target) {
            return;
        }
        var num = $target.text() | 0;
        if(diff == -1){
            if (num > 0) {
                diff = -1;
            } else {
                diff = 0;
            }
        }
        num += diff;
        $target.text(num);
    };

    /*
     *是否参与过投票
     */
    t.havedVote=function(){
        var $up = $('body').find('.icon-thumb');
        if ($up.hasClass('active')) {
            return true;
        }
        return false;
    };

    /*记录当前的信息状态，为回滚备用*/
    t.saveCurrentVoteInfo=function () {
        var $voteItem = $('body').find('.icon-container'),
            $ups = $voteItem.eq(0),
            $downs = $voteItem.eq(1),
            $up=$ups.find('.icon-thumb'),
            $down=$downs.find('.icon-thumb');

        var up = $up.hasClass('active') ? '1' : '0',
            down = $down.hasClass('active') ? '1' : '0',
            uc = $ups.find('.num').text(),
            dc = $downs.find('.num').text(),
            oldData = {
                isOpposed: down,
                isSupported: up,
                opposeCount: dc,
                supportCount: uc
            };
       $('body').attr('data-oldinfo', JSON.stringify(oldData));
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

    /*两次再次加载*/
    t.loadCommentAgain=function(e){
        $(e.currentTarget).hide();
        var index=$('.list-main').attr('data-index') | 0;
        this.loadCommentInfo(index,this.commentListPageCount);
    },

    /*显示评论框*/
    t.showCommentBox=function(){
        var index=0;
        if(!this.userInfo.session_id){
            index=1;
        }
        this.controlModelBox(1,index,'请先登录后再发表评论',function(){
            $('#comment-area')[0].focus();
        });
    };

    /*关闭评论框*/
    t.closeCommentBox=function(){
      this.controlModelBox(0,0);
    };

    /*
    *控模态窗口的显示 和 隐藏
    * Para:
    * opacity - {int} 透明度，1 表示显示，0表示隐藏
    * index - {int} 控制的对象，1 登录提示框，0评论框
    * title - {string} 提示标题
    * callback - {string} 回调方法
     */
    t.controlModelBox=function(opacity,index,title,callback) {
        var $target=$('.model-box'),
            $targetBox=$target.find('.model-box-item').eq(index),
            that=this;
        if(index==1){
            if(!title){
                title='请登录';
            }
            $('.login-header').text(title);
        }
        $target.animate(
            {opacity: opacity},
            10, 'ease-out',
            function () {
                if(opacity==0) {
                    $(this).hide();
                    //that.forbidentScroll(false);
                    callback && callback();
                }else{
                    $(this).show();
                    $targetBox.show().siblings().hide();
                    //that.forbidentScroll(true);
                    callback && callback();
                }
            });
    };

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
                        avatar_url: that.userInfo.avatar_url,
                        username: that.userInfo.name
                    }
                };
                that.fillInCommentInfo({data:[info],totalCount:c});

            },
            eCallback: function (data) {
                that.showTips.call(that,data.txt);
            },
        };
        this.getDataAsync(para);
    };

    /*关闭登录提示框*/
    t.closeLoginBox=function(){
        this.controlModelBox(0,1);
    };

    /*调用app登录*/
    t.doLogin=function(){
        if (this.isFromApp) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction != "undefined") {
                    AppFunction.login(); //显示app的登录方法，得到用户的基体信息
                }
            } else {
                //如果方法存在
                if (typeof showLoginView != "undefined") {
                    showLoginView();//调用app的方法，得到用户的基体信息
                }
            }
        }
        this.controlModelBox(0,1);
    };


    /*滚动到评论列表*/
    t.scrollToComment=function(){
        var h=$('.headlines-body').height(),
            top=$('body').scrollTop(),
            viewH = $('html')[0].clientHeight+200;//可见高度
        //if(top>h-viewH){
            window.scrollTo(0, h);
        //window.loginSuccessCallback();
        //}
        //else{
        //    this.showCommentListPanel();
        //}
    };

    t.showCommentListPanel=function(){
        $('.comment-list-panel').show();
        //that.initIScroll();
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
                    that.pullDownAction();
                }
            }
        });

        this.myScroll.on("slideUp",function(){
            if(that.maxScrollY - this.y > 40){
                if(!that.$upIcon.hasClass('loading')){
                    that.$upIcon.addClass('loading');
                    that.$pullUp.find('.pullUpLabel').text('加载中...');
                    that.pullUpAction();
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

        var target= e.currentTarget,
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

   function goTop(h,acceleration, time) {
        acceleration = acceleration || 0.1;
        time = time || 16;
        var x1 = 0;
        var y1 = 0;
        var x2 = 0;
        var y2 = 0;
        var x3 = 0;
        var y3 = 0;
        if (document.documentElement) {
            x1 = document.documentElement.scrollLeft || 0;
            y1 = document.documentElement.scrollTop || 0;
        }
        if (document.body) {
            x2 = document.body.scrollLeft || 0;
            y2 = document.body.scrollTop || 0;
        }
        var x3 = window.scrollX || 0;
        var y3 = window.scrollY || 0;
        // 滚动条到页面顶部的水平距离
        var x = Math.max(x1, Math.max(x2, x3));
        // 滚动条到页面顶部的垂直距离
        var y = Math.max(y1, Math.max(y2, y3));
        // 滚动距离 = 目前距离 / 速度, 因为距离原来越小, 速度是大于 1 的数, 所以滚动距离会越来越小
        var speed = 1 + acceleration;
        window.scrollTo(Math.floor(x / speed), h);
        // 如果距离不为零, 继续调用迭代本函数
        if (x > 0 || y > 0) {
            var invokeFunction = "goTop(" + acceleration + ", " + time + ")";
            window.setTimeout(invokeFunction, time);
        }
    }


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
    return Topcontent;
});