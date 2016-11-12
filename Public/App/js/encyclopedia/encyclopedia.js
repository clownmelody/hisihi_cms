/**
 * Created by hisihi on 2016/10/31.
 */
define(['base','fx','async'],function(Base) {

    var Encyclopedia = function (id, url) {
        var that = this;
        this.id = id;
        this.baseUrl = url;
        var eventsName = 'click', that = this;
        if (this.isLocal) {
            eventsName = 'touchend';
            this.baseUrl = this.baseUrl.replace('api.php', 'hisihi-cms/api.php');
        }

        //是否显示加载等待动画
        this.controlLoadingBox(true);
        //this.getScroll();
        window.setTimeout(function () {
            that.loadEncyclopediaInfo();
        //请求数据
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



    //获取比赛活动详情信息
    //http://localhost/hisihi-cms/api.php?s=Encyclopedia/getEntryDetail
    t.loadEncyclopediaInfo = function(){
        var that = this,
            queryPara = {
                url: this.baseUrl + 'Encyclopedia/getEntryDetail',
                paraData: {entry_id: this.id},
                sCallback:function(result){
                    if(!result.data||result.data.headInfo.id==null){
                        that.controlLoadingBox(false);
                        that.showTips('词条详情加载失败');
                    }else{
                        that.controlLoadingBox(false);
                        that.loadAllInfo(result);
                        that.loadTopBtn();
                    }
                },
                eCallback:function(){
                    that.controlLoadingBox(false);
                    that.showTips('词条详情加载失败');
                },
                type: 'get',
                async: this.async
            };
        this.getDataAsync(queryPara);
    };

    //加载页面全部信息
    t.loadAllInfo =  function (result) {
        this.loadHeadInfo(result),
        this.loadContentInfo(result),
        this.loadReadAboutInfo(result),
        this.loadIndexInfo(result);
        //this.loadTopBtn(result);
    };


    //加载头部信息
    t.loadHeadInfo = function(result) {
        //判断数据是否存在
        if (!result||result.data.headInfo.title==null) {
            return '';
        }
        var str='',
            title=result.data.headInfo.title,
            detail=result.data.headInfo.detail;
        str ='<div class="head-main-title">'+title+'</div>'+
            '<div class="head-detail">'+detail+'</div>'+ this.loadAboutTips(result);
        $('.head').removeClass('hide');
        $('.head').html(str);
    };

    //加载相关标签
    t.loadAboutTips = function(result){
        if (!result||result.data.likeKeyWords==null) {
            return '';
        }
        var len=result.data.likeKeyWords.length,
            linkHref='',
            strTag='',
            item;
        if(this.isFromApp){
            linkHref='hisihi://encyclopedia/detailinfo?id=';
        }else{
            linkHref=this.baseUrl+'/Encyclopedia/encyclopedia/id/';
        }
        for(var i=0;i<len;i++){
            item=result.data.likeKeyWords[i];
            strTag += '<li class="head-title-tag"><a href="'+linkHref+item.id+'" target="_blank"><span>'+ item.name +'</span></a></li>';
        }

        var str = '<ul class="head-tag">'+
            '<li class="head-title">相关词条：</li>'+
                strTag+
            '<div class="clear"></div>'+
            '</ul>';
        return str;
    };

    //加载目录
    t.loadIndexInfo = function(result){
        //判断数据是否存在或者目录长度是否为空
        if(!result.data||result.data.catalog==null){
            return '';
        }
        var arr=this.setUpArr(result);
        this.getHtmlStr(arr);
    };

    t.setUpArr=function(result){
        var arrdata = result.data.catalog;
        var len = arrdata.length,
            tempArr = [],
            arr1 = [],
            arr2 = [],
            arr3 = [];
        for (var i = 0; i < len; i++) {
            var item = arrdata[i];
            var num1=i+1;
            tempArr.push({
                txt: item.name,
                level: 1,
                num: num1,
                hashTarget:'hash_'+num1
            });
            if (item.level2) {
                var len2 = item.level2.length;
                for (var j = 0; j < len2; j++) {
                    var num2=j+1;
                    tempArr.push({
                        txt: item.level2[j].name,
                        level: 2,
                        hashTarget:'hash_'+num1+'_'+num2
                    });
                }
            }
        }

        var len3 = tempArr.length,
            firstBoxNum = this.getFirstBoxNum(len3);


        for (var i = 0; i < len3; i++) {
            var item = tempArr[i];
            if (i <= firstBoxNum - 1) {
                arr1.push(item);
            } else if (i >= firstBoxNum && i <= 2 * firstBoxNum - 1) {
                arr2.push(item);
            } else {
                arr3.push(item);
            }
        }
        return {
            arr1: arr1,
            arr2: arr2,
            arr3: arr3
        };
    }

    t.getHtmlStr=function(data) {
        var width = '0%';
        if (data.arr3.length == 0) {
            //width = '50%';
        }
        var str = '';
        for (var item in data) {
            str += this.getArrItemHtmlStr(data[item], width);
        }
        $('.index').removeClass('hide');
        $('.catalog-right').html(str);
    };

    t.getArrItemHtmlStr=function(arr) {
        var len = arr.length,
            str = '',
            className = '',
            txt = '';

        for (var i = 0; i < len; i++) {
            var item = arr[i];
            className = 'level2';
            txt = item.txt;
            if (item.level == 1) {
                className = 'level1';
                txt = item.num + '.' + txt;
            }
            str += '<li class="second-catalog ' + className + '"><a href="#'+item.hashTarget+'">' + txt + '</a></li>';
        }
        if (len == 0) {
            str = '';
        } else {
            str = '<div><ul>' + str + '</ul></div>';
        }
        return str;
    };

    //得到第一列 的列表项 数目
    t.getFirstBoxNum=function(len) {
        if (len < 7) {
            firstBoxNum = 3
        } else {
            var diff = len % 3;
            firstBoxNum = len / 3;
            if (diff != 0) {
                firstBoxNum += 1;
            }
        }
        return firstBoxNum | 0;
    };

    //加载百科简介
    t.loadContentInfo = function(result){
        if(!result.data||result.data.catalog==null){
            return '';
        }
        var str='',
            strTxt='',
            strDetail = '',
            len=result.data.catalog.length,
            item,
            id;
        for(var i=0;i<len;i++) {
            id='hash_'+(i+1);
            item = result.data.catalog[i];
            strTxt ='<div class="content-head first" id="'+id+'">' +item.name +'</div>' ;
            strDetail = '<p class="content-info">'+ item.detail+'</p>';
            if (!item.name||item.name=='') {
                strTxt='';
            }
            if(!item.detail||item.detail==''){
               strDetail='';
            }
            str += strTxt +strDetail+ this.getSecondLevel(item,id);
        }
        $('.content').removeClass('hide');
        $('.content').html(str);
    };

    //得到二级目录详情
    t.getSecondLevel = function(item,id){
        if (!item.level2||item.level2.length==0) {
            return '';
        }
        var len=item.level2.length,
            str='',
            strD='';
            for (var j=0;j<len;j++) {
                var level=item.level2[j],
                    strD='<p class="content-info">'+ level.detail+'</p>';
                if (!level.detail) {
                    strD= '';
                }
                str += '<div class="content-head" id="'+id+'_'+(j+1)+'">' +level.name +'</div>' + strD;
                }
            return str;
    };

    //加载百科延伸阅读
    t.loadReadAboutInfo = function (result) {
        if (!result.data||result.data.linkInfo==null) {
            return '';
        }
        var str='',
            strL='',
            len=result.data.linkInfo.length,
            item;
        for(var i=0;i<len;i++) {
            item=result.data.linkInfo[i];
            strL +='<li class="rd-link"><a href="'+item.link+'" target="_blank">'+item.name +'</a></li>';
        }
        str = '<div class="rd-head">延伸阅读</div>' +
            '<ul>' +
                strL+
            '</ul>';
        $('.read-about').removeClass('hide');
        $('.read-about').html(str);
    };


    //加载跳转顶部按钮
    t.loadTopBtn = function (){
        var str ='',
         str = '<div class="top-btn"><a href="#head"></a></div>';
        $('body').append(str);
    };


    //滚动显示
    t.getScroll = function(){
    $("html,body").scroll(function() {
        if ($(window).scrollTop() > 500) {
            $(".top-btn").fadeIn(1500);
        }
        else {
            $(".top-btn").fadeOut(1500);
        }
    })
    };

    return Encyclopedia;
});