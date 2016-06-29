/**
 * Created by jimmy on 2016/5/10.
 */


define(['$'],function() {

    /**基础类**/
    var Base = function (flag) {
        var userAgent = window.location.href;
        this.isFromApp = userAgent.indexOf('hisihi-app') >= 0;
        this.isLocal=userAgent.indexOf('localhost') >= 0 || userAgent.indexOf('192.168.')>=0;
        this.deviceType = this.operationType();
        this.staticUserNameStr='jg2rw2xVjyrgbrZp';
        this.userInfo={
            token:'',
            name:this.staticUserNameStr
        };
        this.timeOutFlag=false; //防止出现在重复快速点击时，计时器混乱添加的回调方法
        this._initTimeFormat();
        this._initStringExtentFn();
        this._addTip();
        this._addLoadingImg();
        if(!flag) {
            this._stopTouchendPropagationAfterScroll();
        }
    };

    Base.prototype = {

        /*
         *获得用户的信息 区分安卓和ios
         *从不同的平台的方法 获得用户的基本信息，进行发表评论时使用
         * @para
         * tokenType-{int} token类型，0 不使用token ，使用session_id的形式，1 基础令牌,  否则为具体用户令牌
         */
        getUserInfo:function (callback,tokenType) {
            var userStr = '', that = this;
            if (this.deviceType.mobile) {
                if (this.deviceType.android) {
                    //如果方法存在
                    if (typeof AppFunction != "undefined") {
                        userStr = AppFunction.getUser(); //调用app的方法，得到用户的基体信息
                    }
                }
                else if (this.deviceType.ios) {
                    //如果方法存在
                    if (typeof getUser_iOS != "undefined") {
                        userStr = getUser_iOS();//调用app的方法，得到用户的基体信息
                    }
                }
                //已经登录
                if (userStr != '') {
                    this.userInfo = JSON.parse(userStr);
                    this.userInfo.token=this.getBase64encode(this.userInfo.token);
                    callback && callback.call(that);
                } else {
                    if(tokenType==0) {
                        var para = {
                            url: this.baseUrl + 'user/login',
                            type: 'get',
                            async: false,
                            paraData: {username: '13554154325', password: '12345678', type: 1, client: 4},
                            sCallback: function (data) {
                                that.userInfo = data;
                                callback && callback.call(that);
                            }
                        };
                        this.getDataAsync(para);
                        callback && callback.call(that);
                        return;
                    }
                    var userInfo={
                        account:'18140662282',
                        secret: '954957945',
                        type: 200
                    };
                    if(tokenType==1){
                        userInfo={
                            account: that.staticUserNameStr,
                            secret: 'VbkzpPlZ6H4OvqJW',
                            type: 100
                        };
                    }
                    that.getBasicToken({account:userInfo.account, secret: userInfo.secret, type: userInfo.type},false,function(token){
                        that.userInfo.token=token;
                        that.userInfo.name=userInfo.account;
                        callback && callback.call(that,that.userInfo);
                    });
                }
            }
            else {
                callback && callback.call(that);
            }

        },

        /*请求数据*/
        getDataAsync: function (paras) {
            if (!paras.type) {
                paras.type = 'post';
            }
            if (paras.async==undefined) {
                paras.async = true;
            }
            if (!paras.url) {
                return;
            }
            if (!paras.url) {
                return;
            }
            var that = this;
            that.controlLoadingTips(1);
            var loginXhr = $.ajax({
                url: paras.url,
                async:paras.async,
                type: paras.type,
                data: paras.paraData,
                //timeout: 2000,
                timeout: 100000,
                contentType: 'application/json',
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
                            var txt = result.message,
                                code=result.error_code;
                            that.controlLoadingTips(-1);
                            paras.eCallback && paras.eCallback({code:code,txt:txt});
                        }
                    }
                    //超时
                    else if (status == 'timeout') {
                        loginXhr.abort();
                        that.controlLoadingTips(-1);
                        paras.eCallback && paras.eCallback({code:'408',txt:'超时'});
                    }
                    else {
                        that.controlLoadingTips(-1);
                        paras.eCallback && paras.eCallback({code:'404',txt:'no found'});
                        loginXhr.abort();
                    }
                }
            });
        },

        /*请求数据 python*/
        getDataAsyncPy: function (paras) {
            if (!paras.type) {
                paras.type = 'post';
            }
            if (paras.async==undefined) {
                paras.async = true;
            }
            var that = this;
            that.controlLoadingTips(1);
            var loginXhr = $.ajax({
                async:paras.async,
                url: paras.url,
                type: paras.type,
                data: paras.paraData,
                //timeout: 20000,
                timeout: 50000,
                contentType: 'application/json',
                beforeSend: function (xhr) {
                    //将token加入到请求的头信息中
                    if (paras.needToken) {
                        xhr.setRequestHeader('Authorization', paras.token);  //设置头消息
                    }
                },
                complete: function (xmlRequest, status) {
                    var rTxt = xmlRequest.responseText,
                    result = {};
                    if (rTxt) {
                        result = JSON.parse(xmlRequest.responseText);

                    } else {
                        result.code = 0;

                    }
                    if (status == 'success') {

                        paras.sCallback(result);

                    }
                    //超时
                    else if (status == 'timeout') {
                        loginXhr.abort();
                        paras.eCallback && paras.eCallback({code:'408',txt:'超时'});
                    }
                    else {
                        if(!result){
                            result={code: '404', txt: 'no found'};
                        }
                        paras.eCallback && paras.eCallback(result);
                    }
                }
            });
        },

        /*获得令牌*/
        getBasicToken:function(userinfo,async,callback){
            var that=this,
                para = {
                    async:async,
                    url: window.hisihiUrlObj.api_url+'/v1/token',
                    type: 'post',
                    paraData: JSON.stringify({account:userinfo.account, secret: userinfo.secret, type: userinfo.type}),
                    sCallback: function (data) {
                        var token =that.getBase64encode(data.token);
                        callback && callback.call(that,token);
                    },eCallback:function(result){
                        that.showTips(result.txt);
                    }
                };
            this.getDataAsyncPy(para);
        },

        /*
         *加载等待,
         *para:
         * status - {num} 状态控制 码
         * 0.显示加载等待;  1 隐藏等待; -1隐藏转圈图片，显示加载失败，重新刷新的按钮;
         */
        controlLoadingTips: function (status) {
            var $target = $('#loadingTip'),
                $img = $target.find('.loadingImg'),
                $a = $target.find('.loadError');
            if (status == 1) {
                $target.show();
                $img.addClass('active');
            } else if (status == -1) {
                $target.show();
                $img.removeClass('active');
                $a.show();
            }
            else {
                $target.hide();
                $img.removeClass('active');
            }
        },

        /*
         *字符串截取
         * para
         * str - {string} 目标字符串
         * len - {int} 最大长度
         */
        substrLongStr: function (str, len) {
            if (str.length > len) {
                str = str.substr(0, parseInt(len - 1)) + '…';
            }
            return str;
        },

        getTimeFromTimestamp: function (dateInfo, dateFormat) {
            if(!dateFormat){
                dateFormat='yyyy-MM-dd';
            }
            return new Date(parseFloat(dateInfo) * 1000).format(dateFormat);
        },

        _initTimeFormat:function(){

            /*
             *拓展Date方法。得到格式化的日期形式
             *date.format('yyyy-MM-dd')，date.format('yyyy/MM/dd'),date.format('yyyy.MM.dd')
             *date.format('dd.MM.yy'), date.format('yyyy.dd.MM'), date.format('yyyy-MM-dd HH:mm')   等等都可以
             *使用方法 如下：
             *                       var date = new Date();
             *                       var todayFormat = date.format('yyyy-MM-dd'); //结果为2015-2-3
             *Parameters:
             *format - {string} 目标格式 类似('yyyy-MM-dd')
             *Returns - {string} 格式化后的日期 2015-2-3
             *
             */
            Date.prototype.format = function (format) {
            var o = {
                "M+": this.getMonth() + 1, //month
                "d+": this.getDate(), //day
                "h+": this.getHours(), //hour
                "m+": this.getMinutes(), //minute
                "s+": this.getSeconds(), //second
                "q+": Math.floor((this.getMonth() + 3) / 3), //quarter
                "S": this.getMilliseconds() //millisecond
            }
            if (/(y+)/.test(format)) format = format.replace(RegExp.$1,
                (this.getFullYear() + "").substr(4 - RegExp.$1.length));
            for (var k in o) if (new RegExp("(" + k + ")").test(format))
                format = format.replace(RegExp.$1,
                    RegExp.$1.length == 1 ? o[k] :
                        ("00" + o[k]).substr(("" + o[k]).length));
            return format;
            };
        },


        _initStringExtentFn:function(){
            String.prototype.substrLongStr=function(){
                var str=this;
                if (this.length > len) {
                    str = this.substr(0, parseInt(len - 1)) + '…';
                }
                return str;
            };
            String.prototype.trim=function(){
                return this.replace(/(^\s*)|(\s*$)/g,'');
            }
        },

        /*
         *根据客户端的时间信息得到发表评论的时间格式
         *多少分钟前，多少小时前，然后是昨天，然后再是月日
         * Para :
         * recordTime - {float} 时间戳
         */
       getDiffTime:function (recordTime) {
           if (recordTime) {
               var ss = new Date(parseFloat(recordTime) * 1000).format('yyyy-MM-dd');
               console.log(ss);
               recordTime=new Date(parseFloat(recordTime)*1000);
               var minute = 1000 * 60,
                   hour = minute * 60,
                   day = hour * 24,
                   now=new Date(),
                   diff = now -recordTime;
               var result = '';
               if (diff < 0) {
                   return result;
               }
               var weekR = diff / (7 * day);
               var dayC = diff / day;
               var hourC = diff / hour;
               var minC = diff / minute;
               if (weekR >= 1) {
                   //result = recordTime.getFullYear() + '.' + (recordTime.getMonth() + 1) + '.' + recordTime.getDate();
                   result=recordTime.format('MM-dd hh:mm');
                   return result;
               }
               else if (dayC == 1 ||(hourC <24 && recordTime.getDate()!=now.getDate())) {
                   result = '昨天'+ recordTime.getHours() + ':'+recordTime.getMinutes();
                   return result;
               }
               else if (dayC > 1) {
                   result=recordTime.format('MM-dd hh:mm');
                   return result;
                   //result = parseInt(dayC) + '天前';
                   //return result;
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
       },

        /*
         *判断webview的来源
         */
        operationType:function() {
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

        /*
         *禁止（允许）滚动
         * para：
         * flag：{bool} 允许还是禁止
         */
        forbidentScroll:function(flag,$target,oldPosStyle){
            if(!$target){
                $target=$('body');
            }
            if(!oldPosStyle){
                oldPosStyle='static';
            }
            if(flag) {
                $target.css({
                    'overflow': 'hidden',
                    'position': 'fixed',
                });
            }else{
                $target.css({
                    'overflow': 'auto',
                    'position': oldPosStyle,
                });
            }
        },

        /*滚动时，禁用touchend*/
        _stopTouchendPropagationAfterScroll:function(){
            var flag = false;
            window.addEventListener('touchmove', function(ev){
                flag || (flag = true, window.addEventListener('touchend', stopTouchendPropagation, true));
            }, false);
            function stopTouchendPropagation(ev){
                ev.stopPropagation();
                setTimeout(function(){
                    window.removeEventListener('touchend', stopTouchendPropagation, true);
                    flag = false;
                }, 50);
            }
        },


        /*
         * 向本地localStorage中写入信息
         * para:
         * dictionary - {object} 键值对信息 {key：val}
         *
         * */
        writeInfoToStorage: function (dictionary) {
            var storage = window.localStorage;
            storage.setItem(dictionary.key, dictionary.val);
        },

        /*
         * 读取本地localStorage中的信息
         * para:
         * keyName - {string} 键值 名称
         *
         * */
        getInfoFromStorage: function (key) {
            var storage = window.localStorage,
                info = storage.getItem(key); //myToken
            if (info) {
                return info;
            } else {
                return false;
            }
        },

        /*添加等待提示框*/
        _addLoadingImg:function(){
            if($('#loading-data').length>0){
                return;
            }
           var str = '<div id="loading-data" style="display: none;"><img class="loding-img"  src="http://pic.hisihi.com/2016-05-11/1462946331132960.png"></div>';
            $('body').append(str);
        },

        /*
         *控制加载等待框
         *@para
         * flag - {bool} 默认隐藏
         */
        controlLoadingBox:function(flag){
            var $target=$('#loading-data'),
                $img=$target.find('img');
            if(flag) {
                $target.addClass('active').show();
            }else{
                $target.removeClass('active').hide();
            }
        },

        /*添加操作结果提示框*/
        _addTip:function(){
            if($('#result-tips').length>0){
                return;
            }
           var str = '<div id="result-tips" class="result-tips" style="display: none;"><p></p></div>';
            $('body').append(str);
        },

        /*
         * 显示操作结果，防止出现在重复快速点击时，计时器混乱添加了  timeOutFlag  进行处理
         * @para:
         * tip - {string} 内容结果
         * strFormat - {bool} 自定义的简单格式
         */
        showTips:function(tip,strFormat){
            if(this.timeOutFlag){
                return;
            }
            this.timeOutFlag=true;
            var $tip=$('body').find('.result-tips'),
                $p=$tip.find('p').text(tip),that=this;
            if(strFormat){
                $tip.html(strFormat);
            }
            $tip.show();
            window.setTimeout(function(){
                $tip.hide();
                $p.text('');
                that.timeOutFlag=false;
            },1500);
        },

        /*
         * 显示操作结果，防止出现在重复快速点击时，计时器混乱添加了  timeOutFlag  进行处理
         * 不会自动隐藏
         * @para:
         * tip - {string} 内容结果
         * strFormat - {bool} 自定义的简单格式
         */
        showTipsNoHide:function(tip,strFormat){
            if(this.timeOutFlag){
                return;
            }
            this.timeOutFlag=true;
            var $tip=$('body').find('.result-tips'),
                $p=$tip.find('p').text(tip),that=this;
            if(strFormat){
                $tip.html(strFormat);
            }
            $tip.show();

        },

        /*隐藏信息提示*/
        hideTips:function(){
            var $tip=$('body').find('.result-tips'),
                $p=$tip.find('p'),
                that=this;
            $tip.hide();
            $p.text('');
            this.timeOutFlag=false;
        },

        /***************64编码的方法****************/
        getBase64encode:function(str) {
            str+= ':'
            var out, i, len, base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
            var c1, c2, c3;
            len = str.length;
            i = 0;
            out = "";
            while (i < len) {
                c1 = str.charCodeAt(i++) & 0xff;
                if (i == len) {
                    out += base64EncodeChars.charAt(c1 >> 2);
                    out += base64EncodeChars.charAt((c1 & 0x3) << 4);
                    out += "==";
                    break;
                }
                c2 = str.charCodeAt(i++);
                if (i == len) {
                    out += base64EncodeChars.charAt(c1 >> 2);
                    out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
                    out += base64EncodeChars.charAt((c2 & 0xF) << 2);
                    out += "=";
                    break;
                }
                c3 = str.charCodeAt(i++);
                out += base64EncodeChars.charAt(c1 >> 2);
                out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
                out += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
                out += base64EncodeChars.charAt(c3 & 0x3F);
            }
            return 'basic '+ out;
        },

        /*调用app登录*/
        doLogin:function(){
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

        /*控制底部logo的位置样式*/
        setFootStyle:function($wrapper) {
            var $target = $('#downloadCon'),
                $a = $target.find('a'),
                aw = $a.width(),
                ah = aw * 0.40,
                bw = $(document).width(),
                h = bw * 120 / 750;
            $target.css({'height': h + 'px', 'opacity': 1});
            $wrapper.css({'bottom': h + 5+'px'});
            return h;
        },

    };
    return Base;
});