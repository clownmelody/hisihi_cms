/**
 * modified by jimmy on 2016/07/04.
 */


define(['$','fastclick'],function() {
    FastClick.attach(document.body);
    /**基础类**/
    var Base = function (config) {
        var href= window.location.href,
            reg = /(\d+)\.(\d+)\.(\d+)\.(\d+)/; // 匹配ip地址  http://91.16.0.1/hisihi-cms/api.php?s=/public/topContentV2_9/id/1263

        this.isFromApp = href.indexOf('hisihi-app') >= 0;  //是否来源于app
        this.isLocal=false; //是否是本地调试来源于app  ，由于fastClick浏览器调试时，事件不方便，添加只是方便浏览器调式，以及本地取测试数据

        if(reg.test(href) || href.indexOf('localhost') >= 0){
            this.isLocal=true;
        }
        this.deviceType = this.operationType();
        this.staticUserNameStr='jg2rw2xVjyrgbrZp';
        this.userInfo={
            token:'',
            session_id:null,
            name:this.staticUserNameStr
        };
        this.statisticToken;  //统计平台使用的 公用token

        this.timeOutFlag=false; //防止出现在重复快速点击时，计时器混乱添加的回调方法

        this.extentSetting(config);

        this._initTimeFormat();
        this._initStringExtentFn();
        this._addTip();
        this._addLoadingImg();
        this._addDownloadBar(config);

    };

    Base.prototype = {

        /*默认参数设置*/
        extentSetting:function(config){
            this.config=defaultConfig;
            if(!config){
                return;
            }
            for(var item in defaultConfig){
                var val=config[item];
                if(val!='undefined'){
                    if(typeof val =='object'){
                        this.config[item]=this.extentSettingSub(this.config[item],val);
                    }else {
                        this.config[item] = val;
                    }
                }
            }
        },

        extentSettingSub:function(defaultObj,obj){
            for(var item in defaultObj){
                if(typeof obj[item]!='undefined'){
                    defaultObj[item]=obj[item];
                }
            }
            return defaultObj;
        },


        /*
         *获得用户的信息 区分安卓和ios
         *从不同的平台的方法 获得用户的基本信息，进行发表评论时使用
         * @para
         * tokenType-{int} token类型，0 不使用token ，使用session_id的形式; 1 基础令牌,  否则为具体用户令牌
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
                    if(tokenType==0 || tokenType===undefined) {
                        var para = {
                            url: this.baseUrl + 'user/login',
                            type: 'get',
                            async: false,
                            paraData: {username: '13554154325', password: '12345678', type: 3, client: 4},
                            sCallback: function (data) {
                                that.userInfo = data;
                                callback && callback.call(that);
                            },
                            eCallback:function(){
                                callback && callback.call(that);
                            }
                        };
                        if(that.isLocal) {
                            this.getDataAsync(para);
                        }else{
                            callback && callback.call(that);
                        }
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

                        //统计平台使用的 token
                        if(userInfo.account==that.staticUserNameStr) {
                            that.statisticToken = token;
                        }
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
                            result = JSON.parse(rTxt);
                        } else {
                            result.status = false;
                        }

                        if (result.success) {
                            paras.sCallback(JSON.parse(xmlRequest.responseText));
                        } else {
                            var txt = result.message,
                                code=result.error_code;
                            if(txt.indexOf('已在其他位置登录')>=0){
                                txt='需要登录';
                            }
                            paras.eCallback && paras.eCallback({code:code,txt:txt});
                        }
                    }
                    //超时
                    else if (status == 'timeout') {
                        loginXhr.abort();
                        paras.eCallback && paras.eCallback({code:'408',txt:'超时'});
                    }
                    else {
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

        /*统计平台数据更新*/
        updateStatisticsNum:function(statisticsType){
            this.getDataAsyncPy({
                    url:hisihiUrlObj.athena_api_url+'/bootstrap',
                    paras:{type:statisticsType},
                    needToken:true,
                    token:this.statisticToken
                });
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
         * yearsFlag -{bool} 是否要年份
         */
       getDiffTime:function (recordTime,yearsFlag) {
           if (recordTime) {
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
                   var formate='MM-dd hh:mm';
                   if(yearsFlag){
                       formate='yyyy-MM-dd hh:mm';
                   }
                   return recordTime.format(formate);
               }
               else if (dayC == 1 ||(hourC <24 && recordTime.getDate()!=now.getDate())) {
                   result = '昨天'+ recordTime.getHours() + ':'+recordTime.getMinutes();
                   return result;
               }
               else if (dayC > 1) {
                   var formate='MM-dd hh:mm';
                   if(yearsFlag){
                       formate='yyyy-MM-dd hh:mm';
                   }
                   return recordTime.format(formate);
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
         * timeOut - {number} 显示时间
         */
        showTips:function(tip,timeOut){
            if(this.timeOutFlag){
                return;
            }
            timeOut= timeOut || 1500;
            if(tip.indexOf('<')<0){
                tip='<p>'+tip+'</p>';
            }
            this.timeOutFlag=true;
            var $tip=$('body').find('.result-tips'),
                that=this;
            $tip.html(tip).show();
            window.setTimeout(function(){
                $tip.hide().html('');
                that.timeOutFlag=false;
            },timeOut);
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
            if(!$wrapper){
                $wrapper=$('.wrapper');
            }
            var $target = $('#downloadCon'),
                bw = $(document).width(),
                h = bw * 120 / 750;
            if(bw>750){
                h=113;
            }
            $target.css({'height': h + 'px', 'opacity': 1});
            $wrapper.css({'margin-bottom': h +'px'});
            return h;
        },

        /*添加下载条*/
        _addDownloadBar:function(){
            var downloadBar=this.config.downloadBar;
            if(downloadBar.show){
                var style='bottom:0',style1={'padding-bottom': downloadBar.height};
                if(!downloadBar.pos){
                    style='top:0';
                    style1={'padding-top': downloadBar.height};
                }

                var str='<div id="downloadCon-new" style="'+style +';background-color:'+downloadBar.backgroundColor+'">'+
                            '<img src="'+downloadBar.logo+'" class="allDownImg" />'+
                            '<div class="words">'+
                                '<div class="title">'+
                                    '<label>'+downloadBar.title+'</label>'+
                                    '<span class="stars">'+
                                        '<img src="'+downloadBar.starImg+'">'+
                                        '<img src="'+downloadBar.starImg+'">'+
                                        '<img src="'+downloadBar.starImg+'">'+
                                        '<img src="'+downloadBar.starImg+'">'+
                                        '<img src="'+downloadBar.starImg+'">'+
                                    '</span>'+
                                '</div>'+
                                '<p>'+downloadBar.description+'</p>'+
                            '</div>'+
                            '<a id="download" style="background-color:'+downloadBar.btnBgColor+'" href="http://www.hisihi.com/download.php">'+downloadBar.downloadBtnWord+'</a>'+
                        '</div>';
                $('body').append(str).css(style1);
            }
        },

        /*
        * 禁止(恢复)滚动
        * para：
        * flag - {bool} 允许（true）或者禁止（false）滚动
        * $target - {jquery obj} 滚动对象
        */
        scrollControl:function(flag,$target){
            if(!flag) {
                $target = $target || $('body');
                this.scrollTop = $target.scrollTop();
                $('html,body').addClass('noscroll');
            }else{
                $('html,body').removeClass('noscroll');
                window.scrollTo(0, this.scrollTop);
            }
        },

    };

    var defaultConfig={
        downloadBar:{
            show:false,
            pos:1,//1 表示在底部，0表示在顶部
            title:'嘿设汇',
            description:'设计师必备，百万设计师都在用',
            logo:'http://pic.hisihi.com/2016-08-04/1470283879133258.png',
            starImg:'http://pic.hisihi.com/2016-08-04/1470283927495292.png',
            downloadBtnWord:'立即下载',
            btnBgColor:'#FF5A00',
            backgroundColor:'#9E9A98',
            height:'56px'
        }
    };

    return Base;
});