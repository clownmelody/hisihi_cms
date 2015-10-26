/**
 * Created by Jimmy on 2015/10/21.
 */
window.Hisihi={};

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
    posInfo:null,

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
        mainBox.css({ 'height': this.boxHeight, 'width': this.boxWidth });
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


