/**
 * Created by hisihi on 2016/10/31.
 */
define(['base','async'],function(Base,Async) {

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
        this.controlLoadingBox(true);
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
                    if(result.data){
                        that.controlLoadingBox(false);
                        that.loadAllInfo(result);
                        $('body').css('opacity','1');
                    }else{
                        that.controlLoadingBox(false);
                        that.showTips('词条详情加载失败');
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
        this.loadHeadInfo(result);
        this.loadContentInfo(result),
        this.loadReadAboutInfo(result);
        this.loadIndexInfo(result);
    };

    //加载头部信息
    t.loadHeadInfo = function(result) {
        //判断数据是否存在
        if (!result||result.data.headInfo.title=='') {
            return '';
        }
        var str='',
            strTag='',
            title=result.data.headInfo.title,
            detail=result.data.headInfo.detail,
            len=result.data.likeKeyWords.length,
            item,
            linkHref='';
        if(this.isFromApp){
            linkHref='hisihi://encyclopedia/detailinfo?id=';
        }else{
            linkHref=this.baseUrl+'/Encyclopedia/encyclopedia/id/';
        }
        for(var i=0;i<len;i++){
            item=result.data.likeKeyWords[i];
            strTag += '<li class="head-title-tag"><a href="'+linkHref+item.id+'" target="_blank"><span>'+ item.name +'</span></a></li>';
        }
        str ='<div class="head-main-title">'+title+'</div>'+
            '<div class="head-detail">'+detail+'</div>'+
            '<ul class="head-tag">'+
            '<li class="head-title">相关词条：</li>'+
                strTag+
            '<div class="clear"></div>'+
            '</ul>';
        $('.head').html(str);
    };

    //加载目录
    t.loadIndexInfo = function(result){
        //判断数据是否存在或者目录长度是否为空
        if(!result||result.data.catalog.length==0){
            return ' ';
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
        $('.catalog-right').html(str);
    };

    t.getArrItemHtmlStr=function(arr, width) {
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
        if(!result||result.data.catalog.length==0){
            return ' ';
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
        $('.content').html(str);
    };

    t.getSecondLevel = function(item,id){
        if (!item.level2||item.level2.length==0) {
            return '';
        }
        var len=item.level2.length,
            str='';
            for (var j=0;j<len;j++) {
                level=item.level2[j];
                str += '<div class="content-head" id="'+id+'_'+(j+1)+'">' +level.name +'</div>' +
                        '<p class="content-info">'+ level.detail+'</p>';
                }
            return str;
    };

    //加载百科延伸阅读
    t.loadReadAboutInfo = function (result) {
        if (!result||result.data.linkInfo==null) {
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
        $('.read-about').html(str);
    };


    //目录文字超长省略
    t.getTxtLong = function() {
        //限制字符个数
        $(".text").each(function(){
            var maxwidth=23;
            if($(this).text().length>maxwidth){ $(this).text($(this).text().substring(0,maxwidth)); $(this).html($(this).html()+'…');
            }
        })
        };


    return Encyclopedia;
});