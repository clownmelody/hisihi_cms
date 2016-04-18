/**
 * Created by jimmy on 2016/4/18.
 */


define(['$'],function() {

    /**�Ƽ��Ķ�������**/
    var MoreInfoBase = function () {

    };

    MoreInfoBase.prototype = {

        /*��������*/
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
                    //��ʱ
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
         *���صȴ�,
         *para:
         * status - {num} ״̬���� ��
         * 0.��ʾ���صȴ�;  1 ���صȴ�; -1����תȦͼƬ����ʾ����ʧ�ܣ�����ˢ�µİ�ť;
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
         *�ַ�����ȡ
         * para
         * str - {string} Ŀ���ַ���
         * len - {int} ��󳤶�
         */
        substrLongStr: function (str, len) {
            if (str.length > len) {
                str = str.substr(0, parseInt(len - 1)) + '����';
            }
            return str;
        },

        getTimeFromTimestamp: function (dateInfo, dateFormat) {
            return new Date(parseFloat(dateInfo) * 1000).format(dateFormat);
        },

        /*
         *�ж�webview����Դ
         */
        operationType:function() {
            var u = navigator.userAgent, app = navigator.appVersion;
            return { //�ƶ��ն�������汾��Ϣ
                trident: u.indexOf('Trident') > -1, //IE�ں�
                presto: u.indexOf('Presto') > -1, //opera�ں�
                webKit: u.indexOf('AppleWebKit') > -1, //ƻ�����ȸ��ں�
                gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //����ں�
                mobile: !!u.match(/AppleWebKit.*Mobile.*/), //�Ƿ�Ϊ�ƶ��ն�
                ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios�ն�
                android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android�ն˻�uc�����
                iPhone: u.indexOf('iPhone') > -1, //�Ƿ�ΪiPhone����QQHD�����
                iPad: u.indexOf('iPad') > -1, //�Ƿ�iPad
                webApp: u.indexOf('Safari') == -1 //�Ƿ�webӦ�ó���û��ͷ����ײ�
            };
        }

    };
    return MoreInfoBase;
});