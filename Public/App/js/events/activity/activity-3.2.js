/**
 * Created by hisihi on 2016/10/26.
 */
define(['base'],function(Base) {

    var Activity = function (id, url) {
        var that = this;
        this.id = id;
        this.baseUrl = url;
        var eventsName = 'click', that = this;
        if (this.isLocal) {
            //eventsName = 'touchend';
            this.baseUrl = this.baseUrl.replace('api.php', 'hisihi-cms/api.php');
        }

        this.controlLoadingBox(true);//是否显示加载等待动画
        window.setTimeout(function () {
            that.loadCompetitionInfo();
        }, 100);
    };

    //下载条
    var config = {
        downloadBar: {
            show: true,
            pos: 1
        }
    };

    Activity.prototype = new Base(config);
    Activity.constructor = Activity;
    var t = Activity.prototype;

    //获取比赛活动详情信息
    //localhost/api.php?s=/event/competitionDetail
    t.loadCompetitionInfo = function () {
        var that = this,
            queryPara = {
                url: this.baseUrl + 'event/competitionDetail',
                paraData: {competition_id: this.id},
                sCallback: function (result) {
                    if (result.data) {
                        that.controlLoadingBox(false);
                        that.fillCompetitionInfo(result.data);
                        $('.wrapper').css('opacity', '1');
                    } else {
                        that.controlLoadingBox(false);
                        that.showTips('比赛详情加载失败');
                    }
                },
                eCallback: function () {
                    that.controlLoadingBox(false);
                    that.showTips('比赛详情加载失败');
                },
                type: 'get',
                async: this.async
            };
        this.getDataAsync(queryPara);
    };


    //填充页面大赛信息
    t.fillCompetitionInfo = function (result) {
        if (!result || result.id == '') {
            return;
        }
        var t = result.title,
            str = '',
            i = result.pic_path,
            imgStr = '<div class="content-box" id="img-box">' +
                '<img  src="' + i + '">' +
                '</div>';
        //$选择器length 为0

        if (!result || i == null) {
            imgStr = '';
        }
        //判断是不是来自于app,如果是，则不显示封面
        if (this.isFromApp) {
            imgStr = '';
        }
        str = imgStr +
            '<div class="content-box head">' +
            '<div class="header">' + t + '</div>' +
            '<ul class="head-box">' +
            this.getTimeInfo(result) + this.getAddressInfo(result) + this.getHostInfo(result) +
            '</ul>' +
            '</div>';
        $('.detail-head').html(str);
    };

    /*
     * 日期时间检查
     * 判断比赛是否结束,eTime 是否结束
     * 获取当前时间进行时间差计算
     * 格式为YYYY-MM-DD
     * */
    t.getDaysBetween = function (result) {
        var now = new Date(),
            t2 = result.eTime,
            recordTime=new Date(parseFloat(t2)*1000),
            diff = now -recordTime;
        if (diff > 0) {
            return '已结束';
        }
        return '进行中';
    };


    //获取比赛时间
    t.getTimeInfo = function (result) {
        if (result.sTime == 0 || result.eTime == 0) {
            return '';
        }
        var begin = this.getTimeFromTimestamp(result.sTime,'yyyy.MM.dd'),
            end = this.getTimeFromTimestamp(result.eTime,'yyyy.MM.dd'),
            str = '';
        str = '<li id="time">' +
            '<div class="logo-box">' +
            '<span class="logo"></span>' +
            '</div>' +
            '<div class="text">' +
            '<span class="title">' +
            begin +
            ' - ' +
            end +
            '</span>' +
            '<span class="status">(' +this.getDaysBetween(result)+')</span>'+
            '</div>' +
            '</li>';
        return str;
    };

    //获取比赛地址
    t.getAddressInfo = function (result) {
        var str = '',
            a = result.address;
        //判断是否有地址信息，如果没有地址信息则不显示
        if (!result || a == null) {
            return '';
        }
        str = '<li id="address">' +
            '<div class="logo-box">' +
            '<span class="logo">' +
            '</span>' +
            '</div>' +
            '<div class="text">' +
            '<span class="title">地址：</span>' + '<span class="detail">' + a + '</span>'
        '</div>' +
        '</li>';
        return str;
    };

    //获取比赛主办方
    t.getHostInfo = function (result) {
        var str = '',
            h = result.organizer;
        if (!result || h == '') {
            return '';
        }
        if (h == null) {
            return '';
        }
        str = '<li id="host">' +
            '<div class="logo-box">' +
            '<span class="logo">' +
            '</span>' +
            '</div>' +
            '<div class="text">' +
            '<span class="title">主办方：</span>' +
            '<span class="detail">' + h + '</span>' +
            '</div>' +
            '</li>';
        return str;
    };

    return Activity;
});