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
        this.usedAppLoginFn = false;  //是否使用app 的登录方法
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
            that.loadCommentInfo(0);
        });


        this.$wrapper.on(eventName, '.bottom-voteCon .leftItem', $.proxy(this, 'execVoteUp'));
        this.$wrapper.on(eventName, '.bottom-voteCon .rightItem', $.proxy(this, 'execVoteDown'));
        this.$wrapper.on(eventName, '.up-comment-box', $.proxy(this, 'execVotUpForComment'));

        /*查看评论，收藏，分享*/
        this.$wrapper.on(eventName, '.up-comment-box', $.proxy(this, 'execVotUpForComment'));
        this.$wrapper.on(eventName, '.comment-collect', $.proxy(this, 'execFavorite'));
        this.$wrapper.on(eventName, '.up-comment-box', $.proxy(this, 'execVotUpForComment'));



        //控制输入框的状态，当有信息输入的时候才可用
        this.$wrapper.on('input', '#comment-area', $.proxy(this, 'controlCommitBtn'));

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
                this.usedAppLoginFn = true;
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
                this.getDataAsync(para);
                //callback && callback.call(that);
            }
        }
        else {
            callback && callback.call(that);
        }

    };

    /*加载前10个评论信息*/
    t.loadCommentInfo=function(index){
        var that = this,
            paraData={id: this.articleId,page:index,count:this.commentListPageCount};
        if(this.userInfo.session_id!==''){
            paraData.session_id=this.userInfo.session_id;
        }
        var para = {
            url: this.baseUrl + 'document/getTopContentComments',
            type: 'get',
            paraData: paraData,
            sCallback: function (data) {
                that.fillInCommentInfo(data);
            },
            eCallback: function (data) {
                that.showTips.call(that,data.txt);
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
        $('#comment-counts').text(count);

        if(result && count>0){
            var dataList=result.data,
                len=dataList.length,
                str='',
                $ul=$('#comment-list-ul'),
                index=Number($ul.attr('data-index'))+1,
                item,

                totalPage=Math.ceil(count/this.commentListPageCount);

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
                        '<div class="up-comment-box" data-id="'+item.id+'">'+
                            '<span class="'+uClass+'"></span>'+
                            '<span class="'+nClass+'">'+upNum+'</span>'+
                        '</div>'+
                    '</li>';
            }
            $ul.next().hide();
            $ul.attr({'data-page-count':totalPage,'data-index':index}).append(str);

            if(count>9999){
                count='10k+';
            }
            $('.comment-red-bubble').text(count).show();
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
                that.showTips.call(that,data.txt);
            },
        };
        this.getDataAsync(para);
    };

    /*通过点赞和 踩的 人数*/
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
        var $target = $(e.currentTarget),
            $thumb=$target.find('.icon-thumb');
        if($target.hasClass('voting') || $thumb.hasClass('active')){
            return;
        }
        //没有登录
        if (this.userInfo.session_id==='') {

            //提示登录框跳转方法
            this.controlModelBox(1,1);
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
        var $target = $(e.currentTarget),
            $thumb=$target.find('.icon-thumb');
        if($target.hasClass('voting') || $thumb.hasClass('active')){
            return;
        }

        //没有登录
        if (this.userInfo.session_id==='') {
            //提示登录框跳转方法
            this.controlModelBox(1,1);
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
        var $target = $(e.currentTarget),
            $thumb=$target.find('.icon-thumb_up');
        if($target.hasClass('voting')){
            this.showTips('操作过于频繁');
            return;
        }
        //没有登录
        if (this.userInfo.session_id==='') {
            //提示登录框跳转方法
            this.controlModelBox(1,1);
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
            this.controlModelBox(1,1);
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

    /*调用app的登录方法*/
    t.execLoginFromApp=function () {
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

    /*显示评论框*/
    t.showCommentBox=function(){
        var index=0;
        if(!this.userInfo.session_id){
            index=1;
        }
        this.controlModelBox(1,index,function(){
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
    *
    */
    t.controlModelBox=function(opacity,index,callback) {
        var $target=$('.model-box'),
            $targetBox=$target.find('.model-box-item').eq(index),
            that=this;
        $target.animate(
            {opacity: opacity},
            500, 'ease-out',
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

    };

    return Topcontent;
});