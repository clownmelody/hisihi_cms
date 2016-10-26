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
                sCallback:function(result){
                    if(result.data){
                        that.fillCompetitionInfo(result.data);
                        $('.wrapper').css('opacity','1');
                    }else{
                        that.showTips('比赛详情加载失败');
                    }
                },
                eCallback:function(){
                    that.showTips('比赛详情加载失败');
                },
                type: 'get',
                async: this.async
            };
        this.getDataAsync(queryPara);
    };


    //填充页面大赛信息
    t.fillCompetitionInfo = function(result) {
        if (!result || result.id == '') {
            return;
        }
        var i = result.pic_path,
            t = result.title,
            e = result.explain,
            str = '',
            iStr = '<div class="content-box" id="img-box">' +
                //'<img  src="http://pic.hisihi.com/2016-06-29/57733124ec788.jpg">' +
                '<img  src="' + i + '">' +
                '</div>';
        str =  iStr+
                '<div class="content-box head">'+
                '<div class="header">'+t+'</div>'+
            <!--活动时间-->
                '<ul class="head-box">'+
                    this.getTimeInfo(result)+this.getAddressInfo(result)+this.getHostInfo(result)+
                '</ul>'+
                '</div>'+
                this.getComInfo(result);
        $('.detail-head').html(str);
    };

    /*
    * 日期时间检查
    * 判断比赛是否结束,eTime 是否结束
    * 获取当前时间进行时间差计算
    * 格式为YYYY-MM-DD
    * */
    t.getDaysBetween = function(result,t1,t2){
        var t1= new Date(),
            t2 = result.eTime;
            t1.toLocaleString( );//获取日期与时间
        //判断时间差
        if ((new Date(t1.replace(/-/g,"\/"))) > (new Date(t2.replace(/-/g,"\/")))){
            return true;
            }
        else {
            return false;
            }
        };

    //获取比赛时间
    t.getTimeInfo = function (result){
        if (!result.sTime||!result.eTime){
            return '';
        }
        var begin=this.getDiffTime(result.sTime),
            end=this.getDiffTime(result.eTime),
            str='';
        str= '<li id="time">'+
            '<div class="logo-box">'+
            '<span class="logo"></span>'+
            '</div>'+
            '<div class="text">'+
            '<span class="title">'+
                begin+
                end+
            '</span>'+
            '<span class="status">'+
                //(进行中)时间状态
            '</span>'+
            '</div>'+
            '</li>';
        return str;
    };

    //获取比赛地址
    t.getAddressInfo = function (result){
        var str='',
            a = result.address;
        str= '<li id="address">'+
            '<div class="logo-box">'+
            '<span class="logo">'+
            '</span>'+
            '</div>'+
            '<div class="text">'+
            '<span class="title">地址：</span>'+
            '<span class="detail">'+a+'</span>'
        '</div>'+
        '</li>';
        //判断是否有地址信息，如果没有地址信息则不显示
        if (!result||a==null) {
            return '';
        }else {
            return str;
        }
    };

    //获取比赛主办方
    t.getHostInfo = function(result){
        var str='',
            h=result.organizer;
        if (!result||h==null) {
            return '';
        }
            str='<li id="host">'+
                '<div class="logo-box">'+
                '<span class="logo">'+
                '</span>'+
                '</div>'+
                '<div class="text">'+
                '<span class="title">主办方：</span>'+
                '<span class="detail">'+h+'</span>'+
                '</div>'+
                '</li>';
        return str;
    };

    //获取比赛详情
    t.getComInfo= function (result){
        if (!result||result.explain==null){
            return '';
        };
        return  '<div class="content-box detail">'+
                    result.explain+
                '</div>';
    };

    return Competition;
});