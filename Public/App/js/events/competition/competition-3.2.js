/**
 * Created by hisihi on 2016/10/24.
 */
define(['base','async'],function(Base,Async){

    var Competition=function($wrapper,id,url){
        this.$wrapper= $wrapper;
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
            that.initData();
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

    t.initData = function (){
        var that= this;
        Async.parallel({
            basic:function(callback) {
                that.loadCompetitionInfo(function (result) {
                    if (!result||!result.success) {
                        that.showTips('比赛不存在');
                        that.controlLoadingBox(false);
                        return;
                    }
                    callback(null, result);//返回值是为空还是有值
                    });
                },
            },function (err, results) {
            var val;
            for (var item in results) {
                var fn = null;
                val = results[item]
                switch (item) {
                    case 'basic':
                        fn = that.fillCompetitionInfo;
                        break;
                    default :
                        break;
                }
                fn && fn.call(that, val);
            }
            $('.wrapper').show();//数据加载完毕展示页面内容
            that.controlLoadingBox(false);
        });
    };

    //填充页面大赛信息
    t.fillCompetitionInfo = function(result){
        if(!result||result.id==''){
            return ;
        }
        var t=result.title,
            o=result.organizer,
            a=result.aaddress,
            str='';
        str =  '<div class="content-box head">'+
            '<div class="content-box" id="img-box">'+
                '<img src="__IMG__/event/compretition/sprite.png"/>'+
            '</div>'+
            '<div class="header">'+t+'</div>'+
            <!--活动时间-->
            '<ul class="head-box">'+
            '<li id="time">'+
            '<div class="logo-box">'+
            '<span class="logo"></span>'+
            '</div>'+
            '<div class="text">'+
            '<span class="title">'+
                this.getDiffTime(result.sTime)+
                this.getDiffTime(result.eTime)+
            '</span>'+
            '<span class="status">'+
                //(进行中)时间状态
            '</span>'+
            '</div>'+
            '</li>'+
            '<li id="address">'+
            '<div class="logo-box">'+
            '<span class="logo">'+
            '</span>'+
            '</div>'+
            '<div class="text">'+
            '<span class="title">地址：</span>'+
            '<span class="detail">'+a+'</span>'
            '</div>'+
            '</li>'+
            '<li id="host">'+
            '<div class="logo-box">'+
            '<span class="logo"></span>'+
            '</div>'+
            '<div class="text">'+
            '<span class="title">主办方：</span>'+
            '<span class="detail">'+o+'</span>'+
            '</div>'+
            '</li>'+
            '</div>';
    };




    return Competition;
});