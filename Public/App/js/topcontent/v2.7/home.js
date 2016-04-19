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

        var eventName='click';
        this.deviceType = this.operationType();
        if(this.deviceType.mobile){
            eventName='touchend';
        }
        //加载投票信息
        this.getUserInfo(this.loadVoteInfo);
        this.$wrapper.on('click', '.bottom-voteCon .leftItem', $.proxy(this, 'execVoteUp'));
        this.$wrapper.on('click', '.bottom-voteCon .rightItem', $.proxy(this, 'execVoteDown'));

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

    /*通过点赞和 踩的 人数，控制颜色条的长度*/
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
                that.fillInfoVoteInfo(data.data,false);
                that.saveCurrentVoteInfo(); //存储当前投票信息
            },
            eCallback: function (str) {

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
    t.fillInfoVoteInfo=function (data,flag) {
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
        var $target = $(e.currentTarget);
        if($target.hasClass('voting') || $target.find('span').hasClass('active')){
            return;
        }
        //没有登录
        if (this.userInfo.session_id==='') {
            //调app的登录框跳转方法
            this.getUserInfo();
            if (!this.usedAppLoginFn) {
                this.execLoginFromApp();
                return;
            }
        }
        //正在投票
        if (this.isVoting()) {
            return;
        }
        var url = this.baseUrl + 'document/doSupport',

            that = this;
        $target.addClass('voting');
        that.finishVote(1);
        var para = {
            url: url,
            type: 'get',
            paraData: {session_id: this.userInfo.session_id, id: this.articleId},
            sCallback: function () {
                //that.finishVote(1);
                $target.removeClass('voting');
                that.saveCurrentVoteInfo.call(that); //存储当前投票信息
            },
            eCallback: function (data) {
                $target.removeClass('voting');
                that.showVoteResult.call(that,data.txt);
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
        var $target = $(e.currentTarget);
        if($target.hasClass('voting') || $target.find('span').hasClass('active')){
            return;
        }

        //没有登录
        if (this.userInfo.session_id==='') {
            //调app的登录框跳转方法
            this.getUserInfo();
            if (!this.usedAppLoginFn) {
                this.execLoginFromApp();
                return;
            }
        }
        //正在投票
        if (this.isVoting()) {
            return;
        }
        var url = this.baseUrl + 'document/doOppose',
            that = this;
        $target.addClass('voting');
        that.finishVote(0);
        var para = {
            url: url,
            type: 'get',
            paraData: {session_id: this.userInfo.session_id, id: this.articleId},
            sCallback: function (data) {
                //that.finishVote(0);
                $target.removeClass('voting');
                that.saveCurrentVoteInfo.call(that); //存储当前投票信息
            },
            eCallback: function (data) {
                $target.removeClass('voting');
                that.showVoteResult.call(that,data.txt);
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


    /*正在招待投票*/
    t.isVoting=function(){
        return $('body .bottom-voteCon>div').hasClass('voting');
    };

    /*
     *显示操作结果
     *para:
     *tip - {string} 内容结果
     */
    t.showVoteResult=function(tip){
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
            this.fillInfoVoteInfo(oldData,false);
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


    return Topcontent;
});