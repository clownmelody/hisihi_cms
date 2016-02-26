/**
 * Created by jimmy on 2016/2/16.
 */
//define(['zepto','zeptofx'],function() {
define(['zepto'],function() {
    var Vote = function ($target, url) {
        this.baseUrl = url;
        this.$wrapper = $target;
        //访问来源
        var userAgent = window.location.href;
        this.articleId = this.$wrapper.data('id') | 0;
        this.userInfo = {session_id: ''};
        this.isFromApp = userAgent.indexOf("hisihi-app") >= 0;
        this.usedAppLoginFn = false;  //是否使用app 的登录方法

        //加载投票信息
        this.separateOperation(this.loadVoteInfo);
        this.$wrapper.on('click', '.mainVoteBtnCon .upBtnAble', $.proxy(this, 'execVoteUp'));
        this.$wrapper.on('click', '.mainVoteBtnCon .downBtnAble', $.proxy(this, 'execVoteDown'));
    };

    Vote.prototype = {

        /*通过点赞和 踩的 人数，控制颜色条的长度*/
        loadVoteInfo: function () {
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
            this.loadDataAsync(para);
        },

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
        fillInfoVoteInfo: function (data,flag) {
            if (!data) {
                return;
            }
            var upCount = data.supportCount | 0,
                downCount = data.opposeCount | 0,
                isUp = data.isSupported == '1',
                isDown = data.isOpposed == '1';
            var total = upCount + downCount | 0;

            var $voteCon = this.$wrapper,
                $left = $voteCon.find('.left span'),
                $right = $voteCon.find('.right span');
            $left.text(upCount);
            $right.text(downCount);

            $voteCon.find('#totalVoteNum').text(total);


            var upClass = 'upBtnAble',
                upClass1 = 'upBtnDisabled',
                downClass = 'downBtnAble',
                downClass1 = 'downBtnDisabled',
                upTargetClass = upClass,
                downTargetClass = downClass;

            //控制长度和 记录是否已经点过赞、踩
            if (isUp) {
                upTargetClass = upClass1;
            }
            $voteCon.find('.upBtn').removeClass(upClass + ' ' + upClass1).addClass(upTargetClass);
            if (isDown) {
                downTargetClass = downClass1;
            }
            $voteCon.find('.downBtn').removeClass(downClass + ' ' + downClass1).addClass(downTargetClass);
            if(flag){
               return;
            }
            this.updateColorBar(upCount, downCount);  //控制色块的长度
            //this.drawArrowColorBlock();  // 绘制三角形
            this.controlVSTitleStyle();  //vs文字样式
            if (!this.isFromApp) {
                $voteCon.find('.mainVoteBtnCon').hide();
            }
            $voteCon.find('.bottomVoteConBox').css('opacity', 1);
        },

        //绘制箭头
        drawArrowColorBlock: function () {
            var canvas = document.getElementById('arrowColor');
            var ctx = canvas.getContext('2d'),
                w = canvas.width,
                h = canvas.height;
            var lines = ["#FF5A00", "#039BE5"];
            ctx.fillStyle = lines[0];
            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.lineTo(w, 0);
            ctx.lineTo(0, h);
            ctx.closePath();
            ctx.fill();

            ctx.fillStyle = lines[1];
            ctx.beginPath();
            ctx.moveTo(w, 0);
            ctx.lineTo(0, h);
            ctx.lineTo(w, h);
            ctx.closePath();
            ctx.fill();
        },

        loadDataAsync: function (paras) {
            var that = this;
            if (!paras.type) {
                paras.type = 'post';
            }
            var loginXhr = $.ajax({
                url: paras.url,
                type: paras.type,
                data: paras.paraData,
                timeOut: 10,
                contentType: 'application/json;charset=utf-8',
                complete: function (xmlRequest, status) {
                    if (status == 'success') {
                        var rTxt = xmlRequest.responseText,
                            result = {};
                        if (rTxt) {
                            result = JSON.parse(xmlRequest.responseText)
                        } else {
                            result.status = false;
                        }

                        if (result.success) {
                            that.controlLoadingTips(0);
                            paras.sCallback(JSON.parse(xmlRequest.responseText));
                        } else {
                            if (paras.eCallback) {
                                var str='操作失败',
                                    resultObj=JSON.parse(xmlRequest.responseText);
                                if(resultObj.error_code==-100){
                                    str=resultObj.message;
                                }
                                paras.eCallback(resultObj.error_code,str);
                            }
                            that.controlLoadingTips(0);
                        }
                    }
                    //超时
                    else if (status == 'timeout') {
                        loginXhr.abort();
                        that.controlLoadingTips(0);
                        paras.eCallback(408,'加载超时');
                    }
                    else {
                        that.controlLoadingTips(0);
                        var str='操作失败';
                        if(xmlRequest.status==-100){
                            str=xmlRequest.statusText;
                        }
                        paras.eCallback(xmlRequest.status,str);
                    }

                }
            });
        },

        /**加载等待**/
        controlLoadingTips: function (status) {
            var $target = $('#loadingTipForVote'),
                $img = $target.find('.loadingImg');
            if (status == 1) {
                $target.css('z-index', 1);
                $img.addClass('active');
            } else {
                $target.css('z-index', -1);
                $img.removeClass('active');
            }

        },

        /*控制VS的样式*/
        controlVSTitleStyle: function () {
            var $parent = $('.mainVoteBarCon'),
                $target = $parent.find('.title'),
                w = $parent.width(),
                tw = $target.width();
            $target.css('left', (w - tw) / 2);
        },

        /*
         *获得用户的信息 区分安卓和ios
         *从不同的平台的方法 获得用户的基本信息，进行发表评论时使用
         */
        separateOperation: function (callback) {
            /*操作设备信息*/
            this.deviceType = this.getDeviceType();
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
                    //this.loadDataAsync(para);
                    callback && callback.call(that);
                }
            }
            else {
                callback && callback.call(that);
            }

        },

        /*赞同投票*/
        execVoteUp: function (e) {
            //没有登录
            if (this.userInfo.session_id==='') {
                //调app的登录框跳转方法
                this.separateOperation();
                if (!this.usedAppLoginFn) {
                    this.execLoginFromApp();
                    return;
                }
            }
            //正在投票
            if (this.isVoting()) {
                return;
            }
            var url = '', $target = $(e.currentTarget), that = this;
            if ($target.hasClass('upBtnAble')) {
                url = this.baseUrl + 'document/doSupport';
            } else {
                url = this.baseUrl + 'document/unDoSupport';
            }
            $target.addClass('voting');
            that.finishVote(1);
            var para = {
                url: url,
                type: 'get',
                paraData: {session_id: this.userInfo.session_id, id: this.articleId},
                sCallback: function () {
                    $target.removeClass('voting');
                    that.saveCurrentVoteInfo.call(that); //存储当前投票信息
                },
                eCallback: function (code,txt) {
                    $target.removeClass('voting');
                    that.showVoteResult.call(that,txt);
                    var typeNum=-1;
                    //已经操作
                    if(code==-100){
                        typeNum=1;
                    }
                    that.finishVote.call(that, -1,typeNum);

                },
            };
            this.loadDataAsync(para);
        },

        /*踩投票*/
        execVoteDown: function (e) {
            //没有登录
            if (this.userInfo.session_id==='') {
                //调app的登录框跳转方法
                this.separateOperation();
                if (!this.usedAppLoginFn) {
                    this.execLoginFromApp();
                    return;
                }
            }
            //正在投票
            if (this.isVoting()) {
                return;
            }
            var url = '', that = this, $target = $(e.currentTarget);
            if ($target.hasClass('downBtnAble')) {
                url = this.baseUrl + 'document/doOppose';
            } else {
                url = this.baseUrl + 'document/undoOppose';
            }
            $target.addClass('voting');
            that.finishVote(0);
            var para = {
                url: url,
                type: 'get',
                paraData: {session_id: this.userInfo.session_id, id: this.articleId},
                sCallback: function () {
                    $target.removeClass('voting');
                    that.saveCurrentVoteInfo.call(that); //存储当前投票信息
                },
                eCallback: function (code,txt) {
                    $target.removeClass('voting');
                    that.showVoteResult.call(that,txt);
                    var typeNum=-1;
                    //已经操作
                    if(code==-100){
                        typeNum=0;
                    }
                    that.finishVote.call(that, -1,typeNum);
                },
            };
            this.loadDataAsync(para);
        },

        /*
         *显示操作结果
         *para:
         *tip - {string} 内容结果
         */
        showVoteResult:function(tip){
            var $tip=this.$wrapper.find('#voteResult').text(tip);
            $tip.text(tip).css('opacity',1);
            window.setTimeout(function(){
                $tip.text('').css('opacity',0);
            },1500);
        },

        //正在投票中，防止重复操作
        isVoting: function () {
            var $up = this.$wrapper.find('.upBtn'),
                $down = this.$wrapper.find('.downBtn');
            if (!($down.hasClass('voting') || $up.hasClass('voting'))) {
                return false;
            }
            return true;
        },

        /*
         * 投票回调
         * para：
         * flag - {num} 操作类型 0 表示踩; 1表示赞; -1表示投票失败，信息回滚
         * type - {num} 在flag 为-1时使用 0 表示踩; 1表示赞;
         */
        finishVote: function (flag,type) {

            var $up = this.$wrapper.find('.upBtn'),
                $down = this.$wrapper.find('.downBtn'),
                $left = this.$wrapper.find('.left span'),
                $right = this.$wrapper.find('.right span'),
                $total = this.$wrapper.find('#totalVoteNum');
            /*正常操作*/
            if (flag !== -1) {
                var havedVote=this.havedVote(); //是否参与过投票

                //总数是否加1
                if (!havedVote) {
                    this.updateNum($total,1);//加1
                }
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
                    $up.removeClass('upBtnAble').addClass('upBtnDisabled');
                    $down.removeClass('downBtnDisabled').addClass('downBtnAble');
                } else {
                    $up.removeClass('upBtnDisabled').addClass('upBtnAble');
                    $down.removeClass('downBtnAble').addClass('downBtnDisabled');
                }
                this.updateColorBar($left.text() | 0, $right.text() | 0);

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
        },

        /*
         *是否参与过投票
         */
        havedVote: function () {
            var $up = this.$wrapper.find('.upBtn'),
                $down = this.$wrapper.find('.downBtn');
            if ($down.hasClass('downBtnDisabled') || $up.hasClass('upBtnDisabled')) {
                return true;
            }
            return false;
        },

        /*当点踩的时候， 判断 是否对自己的赞消除。对于点赞时，也一样进行判断*/
        clearMyOldVote:function(){
            var infoStr=this.$wrapper.attr('data-oldinfo');
            //是否操作过
            if(infoStr!='') {
                var myOldVote = JSON.parse(this.$wrapper.attr('data-oldinfo'));
            }else{
                return true;
            }


        },

        /*更新目标数据*/
        //updateNum: function ($target, flag) {
        //    if (!$target) {
        //        return;
        //    }
        //    var diff = 1, num = $target.text() | 0;
        //    if (flag === false) {
        //        if (num > 0) {
        //            diff = -1;
        //        } else {
        //            diff = 0;
        //        }
        //    }
        //    num += diff;
        //    $target.text(num);
        //},

        /*更新目标数据*/
        updateNum: function ($target, diff) {
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
        },

        /*更新颜色块长度信息*/
        updateColorBar: function (upCount, downCount) {
            var percent1 = 50,
                total = upCount + downCount;
            if (total > 0) {
                percent1 = Math.round(upCount / total * 100);
            }
            var $voteBar = this.$wrapper.find('.mainVoteBarCon'),
                $up = $voteBar.find('.up'),
                $down = $voteBar.find('.down'),
                w = $voteBar.width() - $voteBar.find('.arrowCon').width(),  //总长度要减去中间三角形的宽度
                wUp = Math.round(w * percent1 / 100);
            if (wUp < 10) {
                $up.css('padding-left', '0');
            }
            if (w - wUp < 10) {
                $down.css('padding-right', '0');
            }
            var style1 = {'width': wUp};
            var style2 = {'width': w - wUp};

            $up.animate(style1, 500, 'ease-out', function () {
            });
            $down.animate(style2, 500, 'ease-out', function () {
            });
        },

        /*记录当前的信息状态，为回滚备用*/
        saveCurrentVoteInfo: function () {
            var iso = this.$wrapper.find('.downBtn ').hasClass('downBtnDisabled') ? '1' : '0',
                iss = this.$wrapper.find('.upBtn ').hasClass('upBtnDisabled') ? '1' : '0',
                sc = this.$wrapper.find('.left>span ').text(),
                oc = this.$wrapper.find('.right>span ').text(),
                oldData = {
                    isOpposed: iso,
                    isSupported: iss,
                    opposeCount: oc,
                    supportCount: sc
                };
            this.$wrapper.attr('data-oldinfo', JSON.stringify(oldData));
        },


        /*
         *判断webview的来源
         */
        getDeviceType: function () {
            var u = navigator.userAgent, app = navigator.appVersion;
            return { //移动终端浏览器版本信息
                trident: u.indexOf('Trident') > -1, //IE内核
                presto: u.indexOf('Presto') > -1, //opera内核
                webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
                gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
                mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
                ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
                android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器
                iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器
                iPad: u.indexOf('iPad') > -1, //是否iPad
                webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
            };
        },

        /*得到安卓的版本信息*/
        androidVersionType: function () {
            var u = navigator.userAgent;
            return Number(u.substr(u.indexOf('Android') + 8, 3));
        },

        /*调用app的登录方法*/
        execLoginFromApp: function () {
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
        },
    };

    return Vote;
});
