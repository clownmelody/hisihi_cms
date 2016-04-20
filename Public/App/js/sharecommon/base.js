/**
 * Created by jimmy on 2016/4/18.
 */


define(['$'],function() {

    /**基础类**/
    var Base = function () {
        this._initTimeFormat();
    };

    Base.prototype = {

        /*请求数据*/
        getDataAsync: function (paras) {
            if (!paras.type) {
                paras.type = 'post';
            }
            if (!paras.url) {
                return;
            }
            var that = this;
            that.controlLoadingTips(1);
            var loginXhr = $.ajax({
                url: paras.url,
                type: paras.type,
                data: paras.paraData,
                //timeout: 20000,
                timeout: 5000,
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
                        paras.eCallback && paras.eCallback()
                    }
                }
            });
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
                str = str.substr(0, parseInt(len - 1)) + '……';
            }
            return str;
        },

        getTimeFromTimestamp: function (dateInfo, dateFormat) {
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

    };
    return Base;
});