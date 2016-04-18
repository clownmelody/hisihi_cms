/**
 * Created by jimmy on 2016/4/18.
 */


define(['$'],function() {

    /**推荐阅读基础类**/
    var MoreInfoBase = function () {

    };

    MoreInfoBase.prototype = {

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
                timeout: 20000,
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

                            var txt = result.message;
                            that.controlLoadingTips(-1);
                            paras.eCallback && paras.eCallback();
                        }
                    }
                    //超时
                    else if (status == 'timeout') {
                        loginXhr.abort();
                        that.controlLoadingTips(-1);
                        paras.eCallback && paras.eCallback();
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
        }

    };
    return MoreInfoBase;
});