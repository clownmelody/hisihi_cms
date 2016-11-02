/**
 * Created by hisihi on 2016/10/31.
 */
define(['base'],function(Base) {


    var Encyclopedia = function (id, url) {

        var that = this;
        this.id = id;
        this.baseUrl = url;
        var eventsName = 'click', that = this;
        if (this.isLocal) {
            //eventsName = 'touchend';
            this.baseUrl = this.baseUrl.replace('api.php', 'hisihi-cms/api.php');
        }

        //是否显示加载等待动画
        this.controlLoadingBox(false);
        //window.setTimeout(function () {
        //    that.loadEncyclopediaInfo();
        //请求数据
        //$.getJSON("data.json",function(result){});
        //}, 100);
    };

    //下载条
    var config = {
        downloadBar: {
            show: true,
            pos: 0
        }
    };


    Encyclopedia.prototype = new Base(config);
    Encyclopedia.constructor = Encyclopedia;
    var t = Encyclopedia.prototype;

    //获取百科基本信息
    $.getJSON("Public/App/js/encyclopedia/data.json",function(result){
        if (result.status == 'success') {
            loadEncyclopediaInfo(result);
        }
    });

    t.loadEncyclopediaInfo = function() {
        alert('成功');
    };


    return Encyclopedia;
});