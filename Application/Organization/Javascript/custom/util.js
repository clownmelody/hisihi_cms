/**
 * Created by Jimmy on 2015/10/21.
 */
define(['jquery'],function () {
    window.Hisihi = {};

    /*********************通用方法*****************************/

    /*
     *拓展Date方法。得到格式化的日期形式 基本是什么格式都支持
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
    }

    Hisihi.getLocalTime = function (nS, format) {
        var daa = new Date(1230999938);
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/, ' ');
    };

    /***********创建模态窗口**************/
    Hisihi.modelBox = function (options) {
        return (this instanceof Hisihi.modelBox) ? this.initialize(options) : new Hisihi.modelBox;
    };

    Hisihi.modelBox.prototype = {
        headLabel: '信息提示',
        btnType: ['关闭'],
        showMsg: '信息填写错误',
        boxWidth: '540px',
        boxHeight: '315px',
        $panel: null,
        showAtFirst: true,
        posInfo: null,

        //显示隐藏的回调
        stateChangeCallback: null,

        //按钮回调方法
        btnsCallback: null,

        //初始化回调方法
        initCallback: null,

        initialize: function (options) {
            $.extend(this, options);
            var str = this.createBoxContent();

            $('body').append(str);

            this.$panel = $('.cornerModelBoxBg:last');

            this.eventsInit(); //事件注册
            this.controlModelBoxW_H(); //窗口大小控制
            this.controModelBoxPosIfo();

            if (this.showAtFirst) {
                this.show();
            }
            else {
                this.hide(false);
            }
            this.initCallback && this.initCallback.call(this);
        },

        /*事件注册*/
        eventsInit: function () {
            var $box = this.$panel;
            var that = this;
            $box.on('click', '.cornerModelBoxClose', function () {
                that.hide();
            });
        },

        createBoxContent: function () {
            var mainConte = this.boxMainContentForAlert();
            var str = '<div class="cornerModelBoxBg">' +
                '<div class="cornerModelBox">' +
                '<div class="cornerModelBoxHead">' + this.headLabel + '</div>' +
                '<div class="cornerModelBoxClose" title="关闭">×</div>' +
                '<div class="cornerModelBoxMain">' + mainConte + '</div>' +
                '</div>';
            return str;
        },

        boxMainContentForAlert: function () {
            var str = '<div class="r2ModuleBoxMain">' +
                '<p>' + this.showMsg + '</p>' +
                '</div>' +
                '<div class="r2ModuleBoxBtns">' +
                '<div class="auditBtns">关闭</div>' +
                '</div>';
            return str;
        },


        /*显示模态窗口*/
        show: function (showMsg, type) {
            var that = this;
            var $container = this.$panel;
            $container.show();
        },

        /*
         *隐藏模态窗口
         *Parameters:
         *flag - {bool} 是否用回调
         */
        hide: function (flag) {
            this.$panel.hide();
            if (typeof flag == 'undefined') {
                flag = true;
            }
            if (flag) {
                this.closeBoxCallback && this.closeBoxCallback.call();
            }
        },

        /*控制模态窗口的大小样式*/
        controlModelBoxW_H: function () {
            var mainBox = this.$panel.find('.cornerModelBox');
            mainBox.css({'height': this.boxHeight, 'width': this.boxWidth});
        },

        /*控制模态窗口的位置样式*/
        controModelBoxPosIfo: function () {
            var mainBox = this.$panel.find('.cornerModelBox');
            if (!this.posInfo) {
                var ph = this.$panel.height(),
                    pw = this.$panel.width(),
                    mh = mainBox.height(),
                    mw = mainBox.width();
                this.posInfo = {
                    'top': (ph - mh) / 2 - 80,
                    'left': (pw - mw) / 2
                };
            }
            mainBox.css(this.posInfo);
        },

        OBJECT_NAME: 'Hisihi.modelBox'
    };

    /*通用异步请求方法*/
    Hisihi.getDataAsync = function (options) {
        var data = options.data,
            cookie = JSON.parse($.cookie('hisihi-org'));
        data.session_id = cookie.session_id;
        if (options.org) {
            data.organization_id = cookie.organization_id;
        }
        if (!options.type || options.type == 'post') {
            $.post(options.url, data, options.callback);
        } else {
            $.get(options.url, data, options.callback);
        }
    };

    /*
     *得到随机的整数
     *para
     * max - {num} 最大值
     * min - {num} 最小值 默认为0
     */
    Hisihi.getRandomNum = function (max, min) {
        if (!min) {
            min = 0;
        }
        var rand = max - min,
            num = (Math.random() * rand) + min;
        return Math.round(num);
    };

    /*
     *从时间戳 得到 时间
     * para
     * dateInfo - {num} 时间戳
     * dateFormat - {string} 时间格式 默认为'yyyy.MM.dd'
     */
    Hisihi.getTimeFromTimestamp = function (dateInfo, dateFormat) {
        if (!dateFormat) {
            dateFormat = 'yyyy.MM.dd';
        }
        return new Date(parseFloat(dateInfo) * 1000).format(dateFormat);
    }

    /*
     *字符串截取
     * para
     * str - {string} 目标字符串
     * len - {int} 最大长度
     */
    Hisihi.substrLongStr = function (str, len) {
        if (str.length > len) {
            str = str.substr(0, parseInt(len - 1)) + '……';
        }
        return str;
    };

    /*
     *文件上传
     */
    Hisihi.initUploadify = function ($object,callback,options) {
        var defaultPara={
            "height": 40,
            "swf": window.urlObject.js + "/libs/uploadify/uploadify.swf",
            "fileObjName": "download",
            "buttonText": "上传图片",
            "uploader": window.urlObject.apiUrl+'/api.php?s=/Organization/uploadLogo',
            "width": 100,
            'removeTimeout': 1,
            'fileTypeExts': '*.jpg; *.png; *.gif;',
            'queueID':'',
            'onFallback': function () {
                alert('未检测到兼容版本的Flash.');
            }
        };
        for(var item in options){
            var val=options[item];
            if(val){
                defaultPara[item]=val;
            }
        }
        defaultPara.onUploadSuccess=function(file, data){
            var data = $.parseJSON(data);
            callback(file,data);
        };
        $object.uploadify(defaultPara);
    };


//提示等待插件
    ;
    (function ($) {
        var CornerLoading = function ($element, options) {
            this.options = options;
            this.$element = $element;
            this.addLoadingToPage();
            this.$loadingBox = $element.find('.cornerLoadingBox');
        };
        CornerLoading.prototype = {

            /*添加等待图片到页面中*/
            addLoadingToPage: function () {
                var postion = this.$element.css('position');
                if (postion == 'static') {
                    this.$element.css('position', 'relative');
                }
                var str = '<div class="cornerLoadingBox">' +
                    '<div class="cornerLoadingItem cornerLoadingImg"></div>' +
                    '<div class="cornerLoadingItem cornerLoadingText">' + this.options.text + '</div>' +
                    '</div>';
                this.$element.append(str);
                this.$cornerLoadingBox = this.$element.find('.cornerLoadingBox');
                var left = (this.$element.width() - this.$cornerLoadingBox.width()) / 2,
                    top = (this.$element.height() - this.$cornerLoadingBox.height()) / 3;
                this.$cornerLoadingBox.css({'left': left, 'top': top});
                this.options.showAtFirst && this.showLoading();
            },

            showLoading: function () {
                this.$cornerLoadingBox.show();
            },
            hideLoading: function () {
                this.$cornerLoadingBox.hide();
            }
        };

        $.fn.cornerLoading = function (option) {
            if (this.length == 0) {
                return;
            }
            var args = Array.prototype.slice.call(arguments, 1);
            var innerReturn,
                defaultParas = {
                    text: '数据加载中，请稍后……',
                    showAtFirst: true,
                };
            this.each(function () {
                var $this = $(this),
                    data = $this.data('cornerLoading'),
                    options = typeof option == 'object' ? option : {};
                if (!data) {
                    $this.data('cornerLoading', data = new CornerLoading($(this), $.extend(defaultParas, options)))
                }
                if (typeof option == 'string' && typeof data[option] == 'function') {
                    innerReturn = data[option].call(data, args);
                }
            });
            if (innerReturn !== undefined) {
                return innerReturn;
            } else {
                return this;
            }
        };

    })(jQuery);
});


