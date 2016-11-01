/**
 * Created by hisihi on 2016/10/31.
 */
encyclopedia
define(['base'],function(Encyclopedia) {

    var Encyclopedia = function (id, url) {
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
            //that.loadCompetitionInfo();
        }, 100);
    };

    //下载条
    var config = {
        downloadBar: {
            show: true,
            pos: 1
        }
    };

    Encyclopedia.prototype = new Base(config);
    Encyclopedia.constructor = Encyclopedia;
    var t = Encyclopedia.prototype;

    return Encyclopedia;
});