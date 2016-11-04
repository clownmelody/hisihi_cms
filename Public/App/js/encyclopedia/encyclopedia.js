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
        window.setTimeout(function () {
            that.initData();
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


    t.initData = function(){
     //获取百科基本信息
        $.getJSON("Public/App/js/encyclopedia/data.json",function(result){
            t.loadAllInfo(result);
        });
    }

    //加载页面全部信息
    t.loadAllInfo =  function (result) {
        this.loadHeadInfo(result),
        this.loadIndexInfo(result),
        this.loadContentInfo(result),
        this.loadReadAboutInfo(result);
    };

    //加载头部信息
    t.loadHeadInfo = function(result) {
        //判断数据是否存在
        if (!result  ) {
            return '';
        }
        var str='',
            strTag='',
            title=result.headInfo.title,
            detail=result.headInfo.detail,
            len=result.likeKeyWords.length,
            item;
        for(var i=0;i<len;i++){
            item=result.likeKeyWords[i];
            strTag += '<li class="head-title-tag"><span>'+ item.txt +'</span></li>';
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
        if(!result){
            return ' ';
        }
    };


    //加载百科简介
    t.loadContentInfo = function(result){
        if(!result){
            return ' ';
        }
        var str='',
            strTxt='',
            strDetail = '',
            len=result.catalog.length,
            item,
            id;
        for(var i=0;i<len;i++) {
            id='hash_'+(i+1);
            item = result.catalog[i];
            strTxt ='<div class="content-head first" id="'+id+'">' +item.txt +'</div>' ;
            strDetail = '<p class="content-info">'+ item.detail+'</p>';
            if (!item.txt||item.txt=='') {
                strTxt='';
            }
            if(!item.detail||item.detail==''){
               strDetail='';
            }
            str += strTxt +strDetail+
                    this.getSecondLevel(item,id);
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
                str += '<div class="content-head" id="'+id+'_'+(j+1)+'">' +level.txt +'</div>' +
                        '<p class="content-info">'+ level.detail+'</p>';
                }
            return str;
    };

    //加载百科延伸阅读
    t.loadReadAboutInfo = function (result) {
        if (!result||result==null) {
            return '';
        }
        var str='',
            strL='',
            len=result.linkInfo.length,
            item;
        for(var i=0;i<len;i++) {
            item=result.linkInfo[i];
            strL +='<li class="rd-link">'+item.txt +'</li>';
        }
        str = '<div class="rd-head">延伸阅读</div>' +
            '<ul>' +
                strL+
            '</ul>';
        $('.read-about').html(str);
    };

    return Encyclopedia;
});