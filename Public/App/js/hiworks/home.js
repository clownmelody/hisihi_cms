/**
 * Created by jimmy on 2016/4/18.
 * version-2.7
 * version-2.9 修改 下拉刷新和上拉加载更多的样式，添加滑动 可以查看其他相册功能
 */
define(['fx','base','myscroll','scale'],function(fx,Base,MyScroll) {
    var HiWorks = function (url,baseId) {
        this.controlLoadingBox(true);
        this.baseUrl = url;
        this.$wrapper = $('body');
        this.baseId=baseId;
        this.perPageCount=15;  //每次加载15张图
        this.scrollObjArr=[];
        this.listPrexName='list-wrapper-';  //列表前缀名

        var eventName='click',that=this;
        if(this.isLocal){
            //eventName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        }
        this.baseHiworkListUrl=this.baseUrl.replace('api.php','hiworks_list.php');
        $(document).on(eventName,'.btn',function(){
            event.stopPropagation();
        });

        //标签分类切换
        $(document).on(eventName,'#tabs-bar ul li', $.proxy(this,'switchTabs'));

        //导航到上一级
        $(document).on(eventName,'#back-to-page', $.proxy(this,'back'));

        //显示搜寻框
        $(document).on(eventName,'.nav-bar-right', function(){
            that.controlSearchPanel(true);
            that.initSearchPanelScrollFn();
        });
        //隐藏搜寻框
        $(document).on(eventName,'#quit-search', function(){
            that.controlSearchPanel(false);
            $('#search-result-panel .lists-ul').html('');  //清空上次结果
            that.controlLoadingBox(false);
        });

        //搜寻框可用性控制
        $(document).on('input','#search-txt', $.proxy(this,'controlSearchTxt'));
        //$(document).on('keydown','#search-txt', $.proxy(this,'controlSearchTxt'));

        //执行搜索
        $(document).on(eventName,'#do-search', $.proxy(this,'doSearchByKeyWord'));

        //源作业详细信息查询
        $(document).on('tap','.lists-ul li',function(){
            that.viewWorksDetailInfo($(this));
        });

        //返回到列表
        $(document).on(eventName,'#back-to-list',$.proxy(this,'backToList'));

        //控制输入框
        $(document).on('input','#email',$.proxy(this,'controlCommitBtn'));

        //绑定邮箱
        $(document).on(eventName,'#do-bind',$.proxy(this,'bindEmail'));

        //取消绑定
        $(document).on(eventName,'#cancle-bind',$.proxy(this,'hideBindEmail'));

        //下载、复制、分享
        $(document).on(eventName,'.detail-bottom-btns .item',$.proxy(this,'doOperationForWork'));


        /*防止点击滑动封面项时，出现偏向一边的bug*/
        $(document).on(eventName,'#slider4 li',function(){
            event.stopPropagation();
        });

        //登录
        $(document).on(eventName,'#cancle-login',$.proxy(this,'closeLoginBox'));
        $(document).on(eventName, '#do-login', $.proxy(this, 'doLogin'));

        window.setTimeout(function(){
            that.getUserInfo(function(){
                that.loadClassInfo();
            });
        },100);
    };
    HiWorks.prototype =new Base();
    HiWorks.constructor=HiWorks;

    var t=HiWorks.prototype;



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
                $('#main-content,#tabs-bar,.nav-bar-middle').css('opacity','1');
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
        if(result && result.category && result.category.length>0){
            var category=result.category,
                len=category.length,
                item;
            for(var i=0;i<len;i++){
                item=category[i];
                var className='',
                    title=item.title;
                if(i==0){
                    className='active';
                    var temp=this.substrLongStr(title,16);
                    $('#category-title').text(temp);
                }
                title=this.substrLongStr(title,16);
                str+='<li data-loaded="false" data-init="false" class="'+className+'" data-id="'+item.id+'" data-name="'+item.title+'">'+title+'</li>';
                this.scrollObjArr.push({});
            }
            $('#tabs-bar ul').append(str);
            var w=$('#tabs-bar ul li').eq(0).width()+'px';
            if(len>1){
                $('#tabs-bar').append('<span class="line" style="width:'+w+'"></span>');
            }
        }
    };

    /*填入所有的容器，但只实例化第一个*/
    t.initMyScroll=function(){
        var $li= $('#tabs-bar ul li'),len=$li.length,str='';
        for(var i=0;i<len;i++){
            var w=(1/len)*100+'%';
            str+=this.getScrollContent($li.eq(i).attr('data-id'),w);
        }
        $('#all-scroll-wrapper').css('width',len*100+'%').append(str);
        var $wrappers=$('#all-scroll-wrapper .wrapper'),
            that=this;

        //初始化第一次容器
        this.initScrollLogical($wrappers.eq(0),0);

        //加载第一类
        this.loadCategoryInfo($li.eq(0).attr('data-id'),1,true, function (result) {
            that.setScrollInfoAfterLoaded(result,$li.eq(0),that.scrollObjArr[0]);
        });

    };

    /*
    * 完成源作业 加载后，处理 记录数和 页码，
    * 滚动容器内容属性
    * @para
    * result - {obj} 源作业 内容信息对象
    * $target - {jquery obj} 处理 记录数和 页码 的jqueryc对象
    * scrollObj -{obj} 滚动对象
    */
    t.setScrollInfoAfterLoaded=function(result,$target,scrollObj){
        if(!result){
            return;
        }
        var pcount=Math.ceil(result.totalCount/this.perPageCount);
        $target.attr({'data-loaded':'true','data-pindex':1,'data-pcount':pcount});
        var flag=false;
        if(result && result.totalCount>this.perPageCount){
            flag=true;
        }
        scrollObj.refresh(flag);
        if(flag) {
            scrollObj.resetDownStyle();
        }
    };

    /*容器内容*/
    t.getScrollContent=function(i,w){
        var str='<div class="wrapper" style="width:'+w+'" id="'+this.listPrexName+i+'" data-pcount="0" data-pindex="1">'+
                    '<div class="scroller">'+
                        '<div class="pull-down">'+
                            '<span class="font-icon icon-arrow-up"></span>'+
                            '<span class="label-tips">下拉刷新</span>'+
                        '</div>'+
                            '<div class="lists-ul">'+
                            '</div>'+
                        '<div class="pull-up">'+
                            '<span class="font-icon icon-arrow-down"></span>'+
                            '<span class="label-tips">上拉加载更多</span>'+
                        '</div>'+
                    '</div>'+
                '</div>';
        return str;
    };

    /*
    * 初始化滑动实例
    * @para
    * $target - {jquery obj} 对象
    * index -{int} 实例对应的tabs的下标
    */
    t.initScrollLogical=function($target,index){
        var s = new MyScroll($target, {
            //下拉刷新
            pullDownAction:$.proxy(this,'reloadWorksListInfo'),
            //上拉加载更多
            pullUpAction: $.proxy(this,'loadMoreWorksListInfo'),
        });
        $target.attr('data-init','true');
        this.scrollObjArr[index]=s;
    };

    //菜单滑动效果
    t.sliderMenu=function($target){
        //tabs样式修改
        $target.addClass('active').siblings().removeClass('active');
        var $siblings=$target.siblings(),
            index=$target.index(),
            l=-$target.index()*(1/($siblings.length+1)*100)+'%',
            w=0;
        $.each($siblings,function () {
            if($(this).index()>index){
                return false;
            }
            w+=$(this).width()+20;
        });
        w+='px';
        $('.line').css({
            'width':$target.width()+'px',
            '-webkit-transform' :'translate('+w+')',
            '-webkit-transition':'300ms linear'
        });
        $('#all-scroll-wrapper').css({
            '-webkit-transform' :'translate('+l+') translateZ(0px)',
            '-webkit-transition':'500ms ease-out'
        });
    };



    /*
    *切换分类标签
    *三种情况：1，点的不是当前在显示的，但是数据没有加载过;2.点的不是当前在显示的，但是数据加载过；3.点的是当前在显示的
    */
    t.switchTabs=function(e){
        var $target=$(e.currentTarget),
            index=$target.index();
        //this.scrollNavTabs($target);
        //情况3
        if($target.hasClass('active')){
            return;
        }
        this.controlLoadingBox(false);

        this.sliderMenu($target);

        //对应容器的显示和隐藏
        var that=this,
            $wrapper=$('#all-scroll-wrapper .wrapper');
            //$wrapper.eq(index).show().siblings().hide();

        //修改标题
        var title = $('#tabs-bar .active').attr('data-name');
        title=this.substrLongStr(title,18);
        $('#category-title').text(title);

        //情况1
        if($target.attr('data-loaded')!='true'){
            this.controlLoadingBox(true);
            var id=$target.attr('data-id'),
                that=this;
            this.loadCategoryInfo(id,1,true,function(result){
                that.setScrollInfoAfterLoaded(result,$target,that.scrollObjArr[index]);
            });
        }

        if($wrapper.eq(index).attr('data-init')!='true') {
            this.initScrollLogical($wrapper.eq(index),index);
        }

        //情况2
    };

    /*
    *自动地滚动导航菜单
    *当前菜单的右边距屏幕右边小于50px，或者菜单的左边距左侧屏幕小于50px,做相应的向左或者向右滑动
    *
    */
    t.scrollNavTabs=function($target){
        var $parent=$target.parents('#tabs-bar'),
            left=$parent.scrollLeft(),
            w=$target.width(),
            offset=$target.offset(),
            l=offset.left,
            r=$(document).width() - l-w;
        if(l<20){
            //$parent.animate({scrollLeft: '0px'}, 800);
            $parent.scrollLeft(left+l+20+'px');
            //alert('l');
            return;
        }
        if(r<30){
            $parent.animate({scrollLeft: left+w+'px'}, 500, 'ease-out');
            console.log(left);
            console.log($parent.scrollLeft());
        }
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
            url: this.baseHiworkListUrl + '/index/getHiworksListByCate',
            type: 'get',
            paraData: paraData,
            sCallback: function (resutl) {
                that.controlLoadingBox(false);
                var str= that.getWorksListInfoStr(resutl,id),
                    $wapper=$('#'+that.listPrexName+id),
                    $targetUl=$wapper.find('.lists-ul');
                if(reload) {
                    $targetUl.html(str);
                }else{
                    $targetUl.append(str);
                }
                callback && callback(resutl);
            },
            eCallback: function (data) {
                that.controlLoadingBox(false);
                var txt='数据加载失败';
                that.showTips.call(that,txt);
                callback && callback();
            },
        };
        this.getDataAsync(para);
    };


    /*填充显示云作业列表信息*/
    t.getWorksListInfoStr=function(result,id,keyword){
        var str='',w=$(document).width()*0.27;
        if(result && result.totalCount>0){
            var category=result.data,
                len=category.length,
                item;
            var tempStr='',flag;
            var j=0;
            for(var i=0;i<len;i++){
                item=category[i];
                var className='',marginTopClass='';
                var title=item.title;
                title=this.substrLongStr(title,12);
                if(keyword) {
                    title = title.replace(keyword, '<span style="color:#FA4535">' + keyword + '</span>');
                }
                flag=i==0 || i%3==0;
                if(flag){
                    str+='<ul>';
                }
                j++;
                var pic_url=item.pic_url;
                if(!pic_url){
                    pic_url=window.urlObj.img_url+'/hiworks/hisihi.png';
                }
                item.pic_url=pic_url;

                var jsonStr=JSON.stringify(item).replace(/"/g,"'");

                str+='<li class="'+className+' '+marginTopClass+'" data-json="'+jsonStr+'">'+
                        '<div class="img-box" style="height:'+w+'px;width:'+w+'px">'+
                            '<img src="'+ pic_url +'" data-alt="加载中…">'+
                        '</div>'+
                        '<div class="title">' + title + '</div>'+
                     '</li>';
                if(j==3 || i==len-1){
                    j=0;
                    str+='<div style="clear:both;"></div></ul>';
                }
            }
            //str+='<ul style="height: 90px;"></ul>';
        }
        else{
            str='<p class="no-works">暂无相关作业</p>';
        }
        return str;
    };

    /*上拉加载更多*/
    t.loadMoreWorksListInfo=function(){
        var $li=$('#tabs-bar .active'),
            index=$li.index(),
            id=$li.attr('data-id'),
            pindex=Number($li.attr('data-pindex')),
            pcount=Number($li.attr('data-pcount')),
            that=this;
        if(pindex<pcount){
            this.loadCategoryInfo(id,pindex+1,false,function(data){
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
        this.loadCategoryInfo(id,1,true,function(result){
            var scrollObj=that.scrollObjArr[index];
            $li.attr({'data-loaded':'true','data-pindex':1});
            scrollObj.refresh();
            scrollObj.resetDownStyle();
        });
    };


    /**************搜索功能******************/

    //显示和搜索功能相关的容器
    t.controlSearchPanel=function(flag){
        var $navTabsBar=$('#nav-tabs-bar'),
            $allWrapper=$('#all-scroll-wrapper'),
            $searchPanel=$allWrapper.next();
        if(flag){
            $navTabsBar.hide().siblings().show();
            $searchPanel.animate(
            {'top':'30px'},
            100,'ease-out',
            function(){

            });
        }else{
            $navTabsBar.show().siblings().hide();
            $searchPanel.animate(
                {'top':'150%'},
                100,'ease-out',
                function(){
                });
        }
    };

    /*搜索按钮可用性*/
    t.controlSearchTxt=function(e){
        var $this=$(e.currentTarget);
        var txt=$this.val().trim(),
            $btn=$('#do-search'),
            nc='active btn';
        if(txt){
            $btn.addClass(nc);
        }else{
            $btn.removeClass(nc);
        }
    };

    /*关键字搜索*/
    t.doSearchByKeyWord=function(){
        var that=this;
        that.sScrollObj.controlDownTipsStyle(false);
        this.execSearch(1,true,function(result){
            that.setScrollInfoAfterLoaded(result,$('#list-wrapper-search'),that.sScrollObj);

        });
    };

    /*初始化搜索面板*/
    t.initSearchPanelScrollFn=function(){
        var str=this.getScrollContent('search'),
            $target = $('#search-result-panel').append(str),
            $wrapper=$target.find('.wrapper');
        $target.find('.lists-ul').attr('id','search-result-ul');
        if($wrapper.attr('data-init')=='true'){
            return;
        }
        this.sScrollObj = new MyScroll($wrapper, {
            //下拉刷新
            pullDownAction:$.proxy(this,'reloadWorksListInfoByKetWord'),
            //上拉加载更多
            pullUpAction: $.proxy(this,'loadMoreWorksListInfoByKetWord'),
        });
        $wrapper.attr('data-init','true');
    };

    /*刷新查询结果*/
    t.reloadWorksListInfoByKetWord=function(){
        this.doSearchByKeyWord();


    };

    /*加载更新查询结果*/
    t.loadMoreWorksListInfoByKetWord=function(){
        var $target=$('#list-wrapper-search'),
            pindex=Number($target.attr('data-pindex')),
            pcount=Number($target.attr('data-pcount')),
            that=this,
            scrollObj=that.sScrollObj;

        if(pindex<pcount){
            pindex++;
            this.execSearch(pindex,false,function(result){
                that.sScrollObj.refresh();
                that.sScrollObj.resetUpStyle();
                $('#list-wrapper-search').attr({'data-pindex':pindex});
                if(pindex==pcount){
                    scrollObj.controlDownTipsStyle(false);
                }
            });
        }
    };

    /*
     * 执行搜索云作业信息
     * @para：
     * page - {int} 当前页码
     * reload  - {bool}操作方式 重新加载（true），还是滚动加载（false）
     * callback - {fn object} 回调方法
     */
    t.execSearch=function(page,reload,callback){
        var keyWord=$('#search-txt').val().trim();
        if(!keyWord){
            return;
        }
        if(!keyWord){
            return;
        }
        if(reload) {
            this.controlLoadingBox(true);
        }
        var that = this,
            paraData={keyword : keyWord,page:page,count:this.perPageCount};

        var para = {
            url: this.baseHiworkListUrl+'/index/searchHiworksByKeyWords',
            type: 'get',
            paraData: paraData,
            sCallback: function (resutl) {
                that.controlLoadingBox();
                var str= that.getWorksListInfoStr(resutl,'search',keyWord),
                    $targetUl=$('#search-result-panel').find('.lists-ul');
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
                    txt='数据加载失败';
                }
                that.showTips.call(that,txt);
                $('.no-comment-info').hide();

                //$loadingMore.removeClass('active');
                var $loadingError=$loadingMore.find('.loadError');  //加载失败对象
                //$loadingMoreMain.hide();
                $loadingError.show();
                callback && callback();
            },
        };
        this.getDataAsync(para);
    };


    /*******************作业详细信息查看**********************/

    t.viewWorksDetailInfo=function($target){
        //添加selected 类名，方便查找

        $('.lists-ul li.selected').removeClass('selected');
        $target.addClass('selected')
            .parents('.wrapper').addClass('selected')
            .siblings().removeClass('selected');



        this.currentWorksObj=JSON.parse($target.attr('data-json').replace(/'/g,'"'));  //当前选中的作业信息

        var title=this.substrLongStr(this.currentWorksObj.title,12);
        $('#detail-title').text(title);
        var covers=this.currentWorksObj.multi_cover_info,
            flag=true;
        if(!covers || covers.count==0){
            flag=false;
            var tempUrl=this.currentWorksObj.pic_url;
            tempUrl = tempUrl || window.hisihiUrlObj.img_url + '/hiworks/hisihi.png';
            tempUrl=tempUrl.replace(/@.*/g,'');
            covers= {
                count: 1,
                data: [tempUrl]
            };
        }

        $('#work-detail-panel').show();
        $('#main-content').hide();
        this.fillInTouchSliderItem(covers,flag);
    };

    /*返回作业列表*/
    t.backToList=function(){
        $('#work-detail-panel').hide();
        $('#main-content').show();
        this.currentWorksObj=null; //取消当前选中
    };


    /*滑动图片*/
    t.initTouchSlider=function(){
        var h=$('body').height(),
            flag=$('#slider4').attr('data-init'),that=this;
        $('#detail-main').height(h-135).css('opacity','1');
        if(this.t4){
            this.t4.destroy();
        }
        this.t4=new TouchSlider('slider4',{speed:1000, direction:0, interval:60*60*1000, fullsize:true});
        this.t4.on('before', function (m, n,type) {
            //已经查看完本相册，查看其他相册
            if(m==n){
                that.viewAnotherWorks(type);
            }else {
                $('#currentPage ul li').eq(n).addClass('active').siblings().removeClass('active');
            }
        });
        if(!flag) {
            $('#currentPage ul li').on('click', function (e) {
                var index = $(this).index();
                that.t4.slide(index);
            });
            $('#slider4').attr('data-init','true');
        }

    };

    /*
     *填充滚动区域的图片
     *@para:
     *covers - {obj} 内容信息，包括地址数组等
     *flag - {bool} 是否没有图片，false 则使用 nocover 样式 控制
     */
    t.fillInTouchSliderItem=function(covers,flag){
        var data=covers.data,
            len=data.length,
            str='',str1='',
            className='',className1='nocover';
        if(flag){
            className1='';
        }
        for(var i=0;i<len;i++){
            str+='<li >'+
                    //'<a href="javascript:showFullImg('+i+')">'+
                    '<a href="javascript:void(0)">'+
                        '<img src="'+data[i]+'" alt="" class="'+ className1 +'">'+
                    '</a>'+
                  '</li>';
            className='';
            if(i==0){
                className='active';
            }
            str1+='<li class="'+className+'"></li>';
        }
        $('#slider4').html(str);

        if(len<2) {
            str1='';
        }
        $('#currentPage ul').html(str1);
        //实例化缩放
        ImagesZoom.init({
            "elem": "#slider4"
        });

        //初始滑动
        this.initTouchSlider();
    };

    /*查看其他的作业*/
    t.viewAnotherWorks=function(type){
        var obj = this.getNextOrPrevHiworkObj(type),
            flag=false,
            that=this,
            timeOut=1500;

        //下一个
        if(type=='left'){
            if(!obj){
                return;
            }
            this.showTips('正在加载下一素材…',timeOut);
            flag=true;
        }
        //上一个
        else{
            if(!obj){
                return;
            }
            this.showTips('正在加载上一素材…',timeOut);
            flag=true;
        }
        if(flag){
            this.controlLoadingBox(true);
            window.setTimeout(function(){
                that.controlLoadingBox(false);
                that.viewWorksDetailInfo(obj);  //加载内容
            },timeOut);

        }
    };


    /*得到上一个或者下一个源作业id*/
    t.getNextOrPrevHiworkObj=function(type){
        var $wrapper=$('.wrapper.selected'),
            $li=$wrapper.find('.selected');
        if(type=='left'){
            var $nl=$li.next();
            if($nl[0].nodeName=='DIV'){
                var $pnext=$nl.parent().next();
                if($pnext.length!=0){
                    var $newLi=$pnext.find('li');
                    $nl=$newLi.eq(0);
                }else{
                    $nl=null;
                }
            }
            return $nl;
        }

        var $pl=$li.prev();
        if($pl.length==0){
            var $pprev=$li.parent().prev();
            if($pprev.length!=0){
                var $newLi=$pprev.find('li');
                $pl=$newLi.eq($newLi.length-1);
            }else{
                $pl=null;
            }
        }
        return $pl;
    };



    /*控制按钮的可用性*/
    t.controlCommitBtn=function(e){
        var $this=$(e.currentTarget);
        var txt=$this.val().trim(),
            $btn=$('#do-bind'),
            nc='abled  btn';
        if(txt){
            $btn.addClass(nc);
        }else{
            $btn.removeClass(nc);
        }
    };

    /*下载、分享、复制*/
    t.doOperationForWork=function(e){
        var $target=$(e.currentTarget),
            index=$target.index(),that=this;
        if(this.userInfo.session_id==''){
            this.controlModelBox(1,1);
            return;
        }
        //下载
        if(index==0){
            this.controlModelBox(1,0,function(){
                //如果本地存储有邮箱信息，直接加载
                var email=that.getInfoFromStorage('myemail');
                if(email){
                    $('#email').val(email);
                    $('#do-bind').addClass('abled btn');
                }
            });
        }
        //复制链接
        else if(index==1){
            this.copyLink();
        }
        //分享
        else{
            this.execShare();
        }
    };

    /*关闭登录提示框*/
    t.closeLoginBox=function(){
        this.controlModelBox(0,1);
    };

    /*调用app登录*/
    t.doLogin=function(){
        if (this.isFromApp) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction != "undefined") {
                    AppFunction.login(); //显示app的登录方法，得到用户的基体信息
                }
            } else {
                //如果方法存在
                if (typeof showLoginView != "undefined") {
                    showLoginView();//调用app的方法，得到用户的基体信息
                }
            }
        }
        this.controlModelBox(0,1);
    };

     /*导航返回*/
    t.back=function(){
        if (this.isFromApp) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction != "undefined" && typeof AppFunction.backToPrePage!= "undefined") {
                    AppFunction.backToPrePage();
                }
            } else {
                //如果方法存在
                if (typeof backToPrePage != "undefined") {
                    backToPrePage();
                }
            }
        }
    };

    //确定邮箱
    t.bindEmail=function(e){
        var $target=$(e.currentTarget),that=this;
        if(!$target.hasClass('abled')){
            return;
        }
        var $email=$('#email'),
            email=$email.val().trim(),
            reg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
        if(!reg.test(email)){
            this.showTips('邮箱格式有误，请重新输入');
            return;
        }
        //将邮箱信息写入到本地储存
        that.writeInfoToStorage({key:'myemail',val:email});

        that.controlModelBox(0,0);
        $('#email').val('');
        var para = {
            url: this.baseUrl + 'hiworks/sendDownLoadURLToEMail',
            type: 'get',
            paraData: {email: email, hiwork_id:this.currentWorksObj.id},
            sCallback: function (data) {
                if(data.success) {
                    that.controlLoadingBox(false);
                    email = that.substrLongStr(email, 20);
                    that.showTips('<p>已成功发送至邮箱</p><p>' + email + '</p><p>请注意查收</p>');
                    //更新数据统计
                    that.updateStatisticsNum('hiworks');
                }else{
                    that.controlLoadingBox(false);
                    that.showTips('邮件发送失败');
                }
            },eCallback: function (data) {
                that.showTips('邮件发送失败');
            }
        };
        this.controlModelBox(0,0);
        this.controlLoadingBox(true);
        window.setTimeout(function(){
            that.getDataAsync(para);
        },300);

    };

    //取消绑定邮箱
    t.hideBindEmail=function(){
        this.controlModelBox(0,0);
    };

    /*分享文章*/
    t.execShare=function(){
        if (this.deviceType.android) {
            if (typeof AppFunction.share != "undefined") {
                var info= window.getShareInfo();
                AppFunction.share(info);//调用app的方法，得到用户的基体信息
            }
        }
        else if(this.deviceType.ios){
            //如果方法存在
            if (typeof beginShare != "undefined") {
                beginShare();//调用app的方法，得到用户的基体信息
            }
        }
    };

    /*复制链接*/
    t.copyLink=function(){
        var link=window.getClipboradInfo();  //获得要粘贴的信息
        if (this.deviceType.android) {
            if (typeof AppFunction != "undefined" && typeof AppFunction.setClipboardInfo != "undefined") {
                AppFunction.setClipboardInfo(link);//调用app的方法，调用系统粘贴板
            }

        }
        else if(this.deviceType.ios){
            //如果方法存在
            if (typeof setClipboardInfo != "undefined") {
                setClipboardInfo('getClipboradInfo()');//调用app的方法，调用系统粘贴板
            }
        }
        this.showTips('链接已经复制到粘贴板');
    };


    /*******************通用功能*********************/

    /*
     *控模态窗口的显示 和 隐藏
     * Para:
     * opacity - {int} 透明度，1 表示显示，0表示隐藏
     * index - {int} 控制的对象，1 登录提示框，0评论框
     * title - {string} 提示标题
     * callback - {string} 回调方法
     */
    t.controlModelBox=function(opacity,index,callback) {
        var $target=$('.model-box'),
            $targetBox=$target.find('.model-box-item').eq(index),
            that=this;
        $target.animate(
            {opacity: opacity},
            10, 'ease-out',
            function () {
                if(opacity==0) {
                    $(this).hide();
                    callback && callback();
                }else{
                    $(this).show();
                    $targetBox.show().siblings().hide();
                    callback && callback();
                }
            });
    };


    window.checkSearchKeyword=function(){
        if(""==$('#search-txt').val().trim())  {//作业名称
            return false; //离开函数
        }else{
            window.hiworks.doSearchByKeyWord();
        }
    };

    /*
    *登录功能的回调方法
    *要做三件事：
    * 1，更新点赞 和点踩的信息
    * 2，收藏更新
    * 3，评论列表对应的点赞更新,将目前已经加载下来的评论重新加载。
    */
    window.loginSuccessCallback=function(){
        var obj=window.hiworks;

        //得到用户基本信息
        obj.getUserInfo(function(){

        });
    };

    //返回分享信息，供app调用
    window.getShareInfo=function(){
        var workObj=window.hiworks.currentWorksObj;
        var obj={
            tile: workObj.title.trim(),
            url: window.hiworks.baseUrl+'/Hiworks/hiworks_share/hiword_id/'+workObj.id,
            thumb: workObj.pic_url.trim(),
            description:'我在嘿设汇发现了⼀一个很棒的作业源⽂文件，居然可以直接下载'
        };
        return JSON.stringify(obj);
    };

    /*返回链接地址给IOS，用于粘贴板*/
    window.getClipboradInfo=function(){
        return  window.hiworks.currentWorksObj.download_url.trim();
    };

    return HiWorks;
});