/**
 * Created by hisihi on 2016/10/24.
 */
define(['base','lazyloading','async'],function(Base){

    var Competition=function($wrapper,url,id){
        this.$wrapper = $wrapper;
        var that = this;
        this.id = id;
        this.baseUrl = url;
        var eventsName='click',that=this;
        if(this.isLocal){
            eventsName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        };
        if(!this.isFromApp){ //判断是不是来自于app
            $('#img-box').show();
        }

        this.controlLoadingBox(false);//是否显示加载等待动画
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

    Competition.prototype=new Base(config);
    Competition.constructor=Competition;
    var t=Competition.prototype;


    //获取比赛活动详情信息
    //localhost/api.php?s=/event/competitionDetail
    t.loadCompetitionInfo = function(){
        var that = this,
            queryPara = {
                url: this.baseUrl + 'event/competitionDetail',
                paraData: {competition_id: this.id},
                sCallback: function (result) {
                    callback && callback(result);
                },
                eCallback: function () {
                    callback && callback(null);
                },
                type: 'get',
                async: this.async
            };
        this.getDataAsync(queryPara);
    };



    return Competition;
});