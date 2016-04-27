/**
 * Created by jimmy on 2016/4/18.
 * version-2.7
 */
define(['fx','base','myscroll'],function(fx,Base,MyScroll) {
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
        this.perPageCount=16;  //每次加载10条评论
        this.scrollObjArr=[];

        var eventName='click',that=this;
        this.deviceType = this.operationType();
        if(this.deviceType.mobile){
            eventName='touchend';
        }
        $(document).on(eventName,'.btn',function(){
            event.stopPropagation();
        });

        $(document).on(eventName,'#tabs-bar ul li', $.proxy(this,'switchTabs'));

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
                str+='<li data-loaded="false" data-init="false" class="'+className+'" data-id="'+item.id+'">'+item.title+'</li>';
            }
            $('#tabs-bar ul').append(str);

        }
    };

    /*填入所有的容器，但只实例化第一个*/
    t.initMyScroll=function(){
        var $li= $('#tabs-bar ul li'),str='';
        for(var i=0;i<$li.length;i++){
            str+=this.getScrollContent(i);
        }
        $('#all-scroll-wrapper').append(str);
        var $wrappers=$('#all-scroll-wrapper .wrapper');
        this.initScrollLogical($wrappers.eq(0));
        this.loadCategoryInfo($li.attr('data-id'),0);  //加载第一类
    };

    /*容器内容*/
    t.getScrollContent=function(i){
        var str='<div class="wrapper" id="'+i+'">'+
                    '<div class="scroller">'+
                        '<div class="pullDown">'+
                            '<span class="pullDownIcon icon normal"></span>'+
                            '<span class="pullDownLabel">下拉刷新</span>'+
                        '</div>'+
                        '<div class="news-lists">'+

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
            pullDownAction: function () {
                alert('down');
            },
            pullUpAction: function () {
                alert('up');
            },
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
        var $wrapper=$('#all-scroll-wrapper .wrapper');
        $wrapper.eq(index).show().siblings().hide();

        //情况1
        if($target.attr('data-loaded')!='true'){
            $('#loading-data').addClass('active');
            var id=$target.attr('data-id');
            this.loadCategoryInfo(id,0);
        }

        if($target.attr('data-init')=='false') {
            this.initScrollLogical($wrapper.eq(index));
        }

        //情况2
    };

    /*
    *加载分类下的云作业信息
    * @para：
    * id - {int}
    */
    t.loadCategoryInfo=function(id,page){
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
                that.fillInClassInfo(resutl);
                //$('#main-content').css('opacity','1');
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

    /*控制加载等待框*/
    t.controlLoadingBox=function(flag){

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