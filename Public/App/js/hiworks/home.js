/**
 * Created by jimmy on 2016/4/18.
 * version-2.7
 */
define(['fx','base','myscroll','lazyloading'],function(fx,Base,MyScroll) {
    var HiWorks = function (url,baseId) {
        this.baseUrl = url;
        this.$wrapper = $('body');
        //访问来源
        var userAgent = window.location.href;
        this.baseId=baseId;
        this.userInfo = {session_id: ''};
        this.isFromApp = userAgent.indexOf("hisihi-app") >= 0;
        if(this.isFromApp){
            $('.bottom-comment').show();
        }
        this.perPageCount=15;  //每次加载10条评论
        this.scrollObjArr=[];
        this.listPrexName='list-wrapper-';  //列表前缀名

        var eventName='click',that=this;
        this.deviceType = this.operationType();
        if(this.deviceType.mobile){
            eventName='touchend';
        }
        $(document).on(eventName,'.btn',function(){
            event.stopPropagation();
        });

        $(document).on(eventName,'#tabs-bar ul li', $.proxy(this,'switchTabs'));

        //$('.lists-ul img').imglazyload({
        //    container:''
        //    //backgroundImg: "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAATgAAAE4CAIAAABAHXg9AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NzQ0QjQxNTJFQjU0MTFFNUJEMzZGNkVENzY4QjMyOTEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NzQ0QjQxNTNFQjU0MTFFNUJEMzZGNkVENzY4QjMyOTEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo3NDRCNDE1MEVCNTQxMUU1QkQzNkY2RUQ3NjhCMzI5MSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3NDRCNDE1MUVCNTQxMUU1QkQzNkY2RUQ3NjhCMzI5MSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmWADtgAAAiQSURBVHja7Nzfj1xlHcDhFyMrsDTploq1ltVCQWpDm5YSRCIQG39FYgwEouHCC+K1vTDxHzDxVu9MjDHGC4nRxCikF4pRI6KGNqlY+aFSRSkLsd1NllWpF+v37Xt6dubMmZmtO1Nnd58nTTM9nZ6Z7u7nvO85885csby8nIDJ9hZfAhAqIFQQKiBUQKggVECogFBBqIBQQaiAUAGhglABoQJCBaECQgWECkIFhApCBYQKCBWECggVECoIFRAqCBUQKiBUECogVECoIFRAqIBQQaiAUEGogFABoYJQAaECQgWhAkIFoQJCBYQKQgWECggVhAoIFRAqCBUQKggVECogVBAqIFRAqCBUQKggVECogFBBqIBQAaGCUAGhAkIFoQJCBaECQgWECkIFhAoIFYQKCBWECggVECoIFRAqIFQQKiBUQKggVECoIFRAqIBQQaiAUAGhglABoYJQAaECQgWhAkIFhApCBYQKCBWECggVhAoIFRAqCBUQKiBUECogVBAqIFRAqCBUQKiAUEGogFABoYJQAaGCUAGhAkIFoQJCBYQK69RbfQmyb/w8/35wNh3aPZb9zy2kJ07mG4/e2/7ovzuTHj6U7n5v/uO//5OuunIEj/WJA2nHVt9boW4g0Um4dce49v+v89VDDBWVfv1n+cbn7vsfc60f60N7fWNNfRmPhaX02mL60z/Sl36Ux0Ywoo5LPfms/fN81xy7Nrstffi2/Hs93sZk9QsfTd/+VW71y8fSZ98/rtk4Qt2w57GDp69Hj6Td1w+Z6LZuv2aq649bp/O8NybA0eq3fp23aFWom/e//tQLzS3Pz7Xcbd+uXM4liVy/+pmuLadfT195Mt9obG8ddUOcnZZWYxq8c8aPKZs41O+eaBnueke8ozNdoR65Jd12Q/M+JcI1evlc1x9Lq3HK2rhyG82fmR+0n7NvVDee/duQe149ZawW6sTbv7M5I92xJV2/ZWV8i5lnr+uurea3/URa80vNjXUwkVm/tOIRW/+2bJyZro4XsaveQ0yrJ18c/hUQqlAnXedLmp//Tv79npurVzI7J6uX6tTfB4U0YJ9xXBjwt/WrrK0HmsYUuhxi9mxvnv2uxH82Lb7pp1+om1jMJ/slNODsNNLa8ra0+7pBux1woGk9H77/QN/Bf+iFMYS6wcVksjGfjHIe+206/O70wVvb1zCUtKan+rbHpmfBQ4dt145ltz99Ls0t5mtFg1caxX3AiDrcWlbYdiW3kF9KLeaXVtYntl4rmplun6CWtYR373G9B6GuwuBrvK2eONlyBtjvClPnVaIovH4x5vhL+cT1tcX0vl0jO4Ig1HVs5EtqD85W6/tPvFxdJfr4vr53LusZ9mzP96zH4RhOj53KN+IfqhShZnUeV0+NZodlsrqwVMX2yJ1p77uqBcBlcW+v8jrKmflqAI/hdPHNXPjtN/r+INSLp5HFaN+9+f1ncmx3vSdXmoa90y1G4PjbsvIhCi/z5AcODhpOy2u/A4xkvRSTwVXfiwuDdmzp2hiTz7V46oUcXgyJnzq8qvuXwfyv56rCy2TYZSSMqCvKItvGYoNzb6xpn2V9f4yoX/xe1/aotzESlmX6N16Y8cZpaik8PHTHkIc4eqR9e73G8OFDfRf0P36yfYEkQp1cJYwbto1yn7M9exu8/GjrdB7S5xZXGus3D69XPg29HB2V9rtPzLTjrHh2m2++UNeJ516pbtz0jlHutveKUVl+FJX2W360753VmoeY9HYu6218hFLvyqeRPD2EOtH+eGGOGgPd//1zwOr3o95/oGt7+QilAQt3RyiOJmVWXF5SarwNYLPtWaiTIgar8l6we29uv0N870diYal6p1vUWL9hPW7H6fGDh/O894fH09N/WTnJ7GyynEyu8eLWKicXX/tFdTvOrmMSHs9wJGPvetyzUCfI8ZeqG73vBS+XgnvPJ+NHYejbQR97Oi2dT68vtizfjeoaV3Eenc7pluNFOU09diq/fFqmu/VijJnpsX81fvKH6vQ4BqUyUX/8933fSLDh9yzUSVEvSNi/s2Xee+rVNe288yXT8n70cjEpbt9zcfQul2RjLC2VHrklfWR//vDBGBl+8Ez69F3V6FrtZPwz83IEKVPHGNLjyxL/i1fnRzDlXo97FuqkKAsS0sXPv41hrX4x4zd/rgbD3k/6Xc1HsXxsf7rzpubPSjneR7H1SVTMZiPIMuON3X7y9nwjzrJixI6N10zlLS/OVYeSy6CM5/E845nHczt9Nm8cyWqt9bhnoU6E+uXKKKQU9fxcc04bJ6j7djX/4dCPYkkXXmsZ+nloMaf95i+rw0Hnuvy4EbPuGGPLr+LyvI4SQ318BeJoEseF8hEQrXONTbJnoU6EejVSzDaLt3e/GXXP9rzkYOs4zwzjPDaOBWUlcKd4SjFPrq8txX3uuCwrfuMYEY8b53jlEBY/8Y98YPPuecJcsby8vElb/fGzeRJ72Y6+vYvyY8tVV/Y9Fpw4Xc1779t7aU+y/iiW+nOGL0lMIOMcL2aPI//KrMc9CxVYPYvyQaiAUEGogFABoYJQAaECQgWhAkIFoQJCBYQKQgWECggVhAoIFRAqCBUQKggVECogVBAqIFRAqCBUQKggVECogFBBqIBQAaGCUAGhAkIFoQJCBaECQgWECkIFhAoIFYQKCBWECggVECoIFRAqIFQQKiBUQKggVECoIFRAqIBQQaiAUAGhglABoYJQAaECQgWhAkIFhApCBYQKCBWECggVhAoIFRAqCBUQKiBUECogVBAqIFRAqCBUQKiAUEGogFABoYJQAaGCUAGhAkIFoQJCBYQKQgWECkIFhAoIFYQKCBUQKggVECogVBAqIFQQKiBUQKggVECogFBBqIBQQaiAUAGhglABoQJCBaECQgWECkIFhApCBYQKCBU2oP8KMADsjo9q5NtjwgAAAABJRU5ErkJggg=="//,
        //});

        this.loadClassInfo();


    };
    HiWorks.prototype =new Base(true);
    HiWorks.constructor=HiWorks;

    var t=HiWorks.prototype;

    /*
     *获得用户的信息 区分安卓和ios
     *从不同的平台的方法 获得用户的基本信息，进行发表评论时使用
     */
    t.getUserInfo=function (callback) {
        var userStr = '', that = this;
        if (this.deviceType.mobile) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction != "undefined") {
                    userStr = AppFunction.getUser(); //调用app的方法，得到用户的基体信息
                    //AppFunction.showShareView(true);  //调用安卓的方法，控制分享按钮可用
                }
            }
            else if (this.deviceType.ios) {
                //如果方法存在
                if (typeof getUser_iOS != "undefined") {
                    userStr = getUser_iOS();//调用app的方法，得到用户的基体信息
                }
            }
            if (userStr != '') {
                this.userInfo = JSON.parse(userStr);
                callback && callback.call(that);
            } else {
                var para = {
                    url: this.baseUrl + 'user/login',
                    type: 'get',
                    paraData: {username: '13554154325', password: '12345678', type: 1, client: 4},
                    sCallback: function (data) {
                        that.userInfo = data;
                        callback && callback.call(that);
                    }
                };
                //this.getDataAsync(para);
                callback && callback.call(that);
            }
        }
        else {
            callback && callback.call(that);
        }

    };

    /*加载二级分类*/
    t.loadClassInfo=function(index,pCount,callback){
        var that = this,
            paraData={cate: this.baseId};
        if(this.userInfo.session_id!==''){
            paraData.session_id=this.userInfo.session_id;
        }

        var para = {
            url: this.baseUrl + 'hiworks/category',
            type: 'get',
            paraData: paraData,
            sCallback: function (resutl) {
                that.fillInClassInfo(resutl);
                that.initMyScroll();
                $('#main-content').css('opacity','1');
                callback && callback(data);
            },
            eCallback: function (data) {
                var txt=data.txt;
                if(data.code=404){
                    txt='分类信息加载失败';
                }
                that.showTips.call(that,txt);
                $('.no-comment-info').hide();

                $loadingMore.removeClass('active');
                var $loadingError=$loadingMore.find('.loadError');  //加载失败对象
                $loadingMoreMain.hide();
                $loadingError.show();

                callback && callback();
            },
        };
        this.getDataAsync(para);
    };

    /*填入分类信息*/
    t.fillInClassInfo=function(result){
        var str='';
        if(result && result.category.length>0){
            var category=result.category,
                len=category.length,
                item;
            for(var i=0;i<len;i++){
                item=category[i];
                var className='';
                if(i==0){
                    className='active';
                }
                var title=item.title;
                title=this.substrLongStr(title,6);
                str+='<li data-loaded="false" data-init="false" class="'+className+'" data-id="'+item.id+'">'+title+'</li>';
            }
            $('#tabs-bar ul').append(str);

        }
    };

    /*填入所有的容器，但只实例化第一个*/
    t.initMyScroll=function(){
        var $li= $('#tabs-bar ul li'),str='';
        for(var i=0;i<$li.length;i++){
            str+=this.getScrollContent($li.eq(i).attr('data-id'));
        }
        $('#all-scroll-wrapper').append(str);
        var $wrappers=$('#all-scroll-wrapper .wrapper'),
            that=this;

        //初始化第一次容器
        this.initScrollLogical($wrappers.eq(0));
        //加载第一类
        this.loadCategoryInfo($li.eq(0).attr('data-id'),0,true, function (result) {
            that.scrollObjArr[0].refresh();
            var pcount=Math.ceil(result.totalCount/that.perPageCount);
            $li.eq(0).attr({'data-loaded':'true','data-pindex':1,'data-pcount':pcount});

            //$('.lists-ul img').imglazyload({
            //    container:$wrappers.eq(0)
            //    //backgroundImg: "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAATgAAAE4CAIAAABAHXg9AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NzQ0QjQxNTJFQjU0MTFFNUJEMzZGNkVENzY4QjMyOTEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NzQ0QjQxNTNFQjU0MTFFNUJEMzZGNkVENzY4QjMyOTEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo3NDRCNDE1MEVCNTQxMUU1QkQzNkY2RUQ3NjhCMzI5MSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3NDRCNDE1MUVCNTQxMUU1QkQzNkY2RUQ3NjhCMzI5MSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmWADtgAAAiQSURBVHja7Nzfj1xl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"//,
            //});
        });

    };

    /*容器内容*/
    t.getScrollContent=function(i){
        var str='<div class="wrapper" id="'+this.listPrexName+i+'" data-pcount="0" data-pindex="1">'+
                    '<div class="scroller">'+
                        '<div class="pullDown">'+
                            '<span class="pullDownIcon icon normal"></span>'+
                            '<span class="pullDownLabel">下拉刷新</span>'+
                        '</div>'+
                        '<div class="lists-ul">'+
                        '</div>'+
                        '<div class="pullUp">'+
                            '<span class="pullUpIcon icon normal"></span>'+
                            '<span class="pullUpLabel">上拉加载更多</span>'+
                        '</div>'+
                    '</div>'+
            '</div>';
        return str;
    };

    /*初始化滑动实例*/
    t.initScrollLogical=function($target){
        var s = new MyScroll($target, {
            //下拉刷新
            pullDownAction:$.proxy(this,'reloadWorksListInfo'),
            //上拉加载更多
            pullUpAction: $.proxy(this,'loadMoreWorksListInfo'),
        });
        $target.attr('data-init','true');
        this.scrollObjArr.push(s);
    };

    /*
    *切换分类标签
    *三种情况：1，点的不是当前在显示的，但是数据没有加载过;2.点的不是当前在显示的，但是数据加载过；3.点的是当前在显示的
    */
    t.switchTabs=function(e){
        var $target=$(e.currentTarget),
            index=$target.index();

        //情况3
        if($target.hasClass('active')){
            return;
        }
        $target.addClass('active').siblings().removeClass('active');
        var $wrapper=$('#all-scroll-wrapper .wrapper'),that=this;
        $wrapper.eq(index).show().siblings().hide();

        //情况1
        if($target.attr('data-loaded')!='true'){
            $('#loading-data').addClass('active');
            var id=$target.attr('data-id');
            this.loadCategoryInfo(id,0,true,function(result){
                that.scrollObjArr[index].refresh();
                var pcount=Math.ceil(result.totalCount/that.perPageCount);
                $target.attr({'data-loaded':'true','data-pindex':1,'data-pcount':pcount});
            });
        }

        if($target.attr('data-init')=='false') {
            this.initScrollLogical($wrapper.eq(index));
        }

        //情况2
    };

    /*
    * 加载分类下的云作业信息
    * @para：
    * id - {int} 三级分类
    * page - {int} 当前页码
    * reload  - {bool}操作方式 重新加载（true），还是滚动加载（false）
    * callback - {fn object} 回调方法
    */
    t.loadCategoryInfo=function(id,page,reload,callback){
        if(reload) {
            this.controlLoadingBox(true);
        }
        var that = this,
            paraData={base: this.baseId,cate:id,page:page,count:this.perPageCount};
        if(this.userInfo.session_id!==''){
            paraData.session_id=this.userInfo.session_id;
        }


        var para = {
            url: window.hisihiUrlObj.link_url + 'hiworks_list.php/index/getHiworksListByCate',
            type: 'get',
            paraData: paraData,
            sCallback: function (resutl) {
                that.controlLoadingBox();
                var str= that.getWorksListInfoStr(resutl,id),
                    $targetUl=$('#'+that.listPrexName+id).find('.lists-ul');
                if(reload) {
                    $targetUl.html(str);
                }else{
                    $targetUl.append(str);
                }
                callback && callback(resutl);
            },
            eCallback: function (data) {
                that.controlLoadingBox();
                var txt=data.txt;
                if(data.code=404){
                    txt='分类信息加载失败';
                }
                that.showTips.call(that,txt);
                $('.no-comment-info').hide();

                $loadingMore.removeClass('active');
                var $loadingError=$loadingMore.find('.loadError');  //加载失败对象
                $loadingMoreMain.hide();
                $loadingError.show();
                callback && callback();
            },
        };
        this.getDataAsync(para);
    };

    /*填充显示云作业列表信息*/
    t.getWorksListInfoStr=function(result,id){
        var str='',w=$(document).width()*0.27;
        if(result && result.data.length>0){
            var category=result.data,
                len=category.length,
                item;
            var tempStr='',flag,flag1=false;
            var j=0;
            for(var i=0;i<len;i++){
                item=category[i];
                var className='',marginTopClass='';
                var title=item.title;
                title=this.substrLongStr(title,12);
                flag=i==0 || i%3==0;
                if(flag){
                    str+='<ul>';
                    flag1=true;
                }
                j++;
                str+='<li class="'+className+' '+marginTopClass+'">'+
                        '<div class="img-box" style="height:'+w+'px;width:'+w+'px">'+
                            '<img src="'+ item.pic_url +'" data-alt="加载中…">'+
                        '</div>'+
                        '<div class="title">' + title + '</div>'+
                     '</li>';
                if(j==3 || i==len-1){
                    j=0;
                    str+='<div style="clear:both;"></div></ul>';
                }
            }
        }
        return str;
    };

    /*上拉加载更多*/
    t.loadMoreWorksListInfo=function(){
        var $li=$('#tabs-bar .active'),
            index=$li.index(),
            id=$li.attr('data-id'),
            pindex=$li.attr('data-pindex'),
            pcount=$li.attr('data-pcount'),
            that=this;
        if(pindex<pcount){
            this.loadCategoryInfo(id,pindex,false,function(data){
                pindex++;
                $li.attr({'data-pindex':pindex});
                var scrollObj=that.scrollObjArr[index];
                if(pindex==pcount){
                    scrollObj.controlDownTipsStyle(false);
                }
                scrollObj.resetUpStyle();
            });
        }
    };

    /*下拉重新加载*/
    t.reloadWorksListInfo=function(){
        var $li=$('#tabs-bar .active'),
            index=$li.index(),
            id=$li.attr('data-id'),
            that=this;
        this.loadCategoryInfo(id,0,true,function(result){
            that.scrollObjArr[index].refresh();
            $li.attr({'data-loaded':'true','data-pindex':1});
            var scrollObj=that.scrollObjArr[index];
            scrollObj.resetDownStyle();
        });
    };

    /*
    *控制加载等待框
    *@para
    * flag - {bool} 默认隐藏
    */
    t.controlLoadingBox=function(flag){
        var $target=$('#loading-data');
        if(flag) {
            $target.addClass('active');
        }else{
            $target.removeClass('active');
        }
    };

    /*
     *显示操作结果
     *para:
     *tip - {string} 内容结果
     */
    t.showTips=function(tip){
        if(tip.length>8){
            tip=tip.substr(0,7)+'…';
        }
        var $tip=$('body').find('.result-tips'),
            $p=$tip.find('p').text(tip);
        $tip.show();
        window.setTimeout(function(){
            $tip.hide();
            $p.text('');
        },1500);
    };

    /*
    *登录功能的回调方法
    *要做三件事：
    * 1，更新点赞 和点踩的信息
    * 2，收藏更新
    * 3，评论列表对应的点赞更新,将目前已经加载下来的评论重新加载。
    */
    window.loginSuccessCallback=function(){
        var obj=window.topContentObj;
        obj.controlModelBox(0,1);

        //得到用户基本信息
        obj.getUserInfo(function(){
            obj.loadVoteInfo(); //点赞信息
            obj.getFavoriteInfo(); //收藏信息
            window.topContentObj.updateCommentInfo();  //更新评论的点赞信息
        });
    };

    window.getShareInfo=function(){
        return {title:'123',url:'123123',thumb:'',description:''};
    };

    return HiWorks;
});