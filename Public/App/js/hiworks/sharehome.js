/**
 * Created by jimmy on 2016/4/18.
 * version-2.7
 */
define(['fx','base','scale','fastclick'],function(fx,Base) {
    FastClick.attach(document.body);
    var HiWorks = function (url,baseId) {
        this.baseUrl = url;
        this.baseId=baseId;

        var eventName='click',that=this;
        if(this.isLocal){
            //eventName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        }
        var $bottom=$('.detail-bottom-btns');
        if(this.isFromApp){
            $bottom.eq(1).show();
            $('.touchSlider').addClass('app');
        }else{
            $bottom.eq(0).add($('#downloadCon')).show();
        }
        this.baseHiworkListUrl=this.baseUrl.replace('api.php','hiworks_list.php');
        $(document).on(eventName,'.btn',function(){
            event.stopPropagation();
        });

        //控制输入框
        $(document).on('input','#email',$.proxy(this,'controlCommitBtn'));

        //绑定邮箱
        $(document).on(eventName,'#do-bind',$.proxy(this,'bindEmail'));

        //取消绑定
        $(document).on(eventName,'#cancle-bind',$.proxy(this,'hideBindEmail'));

        //下载
        $(document).on(eventName,'.share-page-btns .btn',$.proxy(this,'doOperationForWorkShare'));

        //下载、复制、分享
        $(document).on(eventName,'.web-page-btns .item',$.proxy(this,'doOperationForWork'));


        $('#downloadCon .btnElement').on(eventName,function(){
            window.location.href = "http://www.hisihi.com/download.php";
        });

        this.viewWorksDetailInfo(this.baseId);  //加载内容

        this.controlCoverFootStyle();  //控制下载条样式

        /*禁用浏览器的左右滑动翻页功能*/
        var control = navigator.control || {};
        if (control.gesture) {
            control.gesture(false);
        }

    };
    HiWorks.prototype =new Base();
    HiWorks.constructor=HiWorks;

    var t=HiWorks.prototype;


    /*****************************************/

    /*
    * 作业详细信息查看
    */
    t.viewWorksDetailInfo=function(){
        this.controlLoadingBox(true);
        var that=this;
        var para = {
            url: this.baseHiworkListUrl+'/index/getHiworkDetailById',
            type: 'get',
            paraData: {hiwork_id:this.baseId,version:2.95},
            sCallback: function (result) {
                that.controlLoadingBox(false);
                if(!result.success){
                    return;
                }
                var data=result.data;
                that.currentWorksObj=data;

                that.setTitle(data.title);  //标题设置


                that.nextHiWorksId=data.next_hiwork_id;
                that.prevHiWorksId=data.pre_hiwork_id;

                var covers=data.multi_cover_info,
                    flag=true;
                if(!covers || covers.count==0){
                    flag=false;
                    covers.count=1;
                    var tempUrl=data.pic_url;
                    tempUrl = tempUrl || window.hisihiUrlObj.img_url + '/hiworks/hisihi.png';
                    tempUrl=tempUrl.replace(/@.*/g,'');
                    covers.data=[tempUrl];
                }
                that.fillInTouchSliderItem(covers,flag);
            },eCallback: function (data) {
                that.showTips(data.txt);
                that.controlLoadingBox(false);
            }
        };
        this.getDataAsync(para);


    };

    /*滑动图片*/
    t.initTouchSlider=function(){
        var h=$('body').height(),
            flag=$('#slider4').attr('data-init'),
            nh=h-135;
        if(this.isFromApp){
            nh=h-80;
        }
        $('#detail-main').height(nh).css('opacity','1');

        if(this.t4){
            this.t4.destroy();
        }
        this.t4=new TouchSlider('slider4',{speed:1000, direction:0, interval:60*60*1000, fullsize:true});
        var that=this;

        this.t4.on('before', function (m, n,type) {
            //已经查看完本相册，查看其他相册
            if(m==n){
                that.viewAnotherWorks(type);
            }else {
                $('#currentPage ul li').eq(n).addClass('active').siblings().removeClass('active');
            }
        });

        if(!flag) {
            $('#currentPage ul li').on('touchend', function (e) {
                var index = $(this).index();
                that.t4.slide(index);
            });
            $('#slider4').attr('data-init','true');
        }
    };

    /*查看其他的作业*/
    t.viewAnotherWorks=function(type){
        //下一个
        if(type=='left'){
            if(!this.nextHiWorksId){
                return;
            }
            this.baseId=this.nextHiWorksId;
            this.showTips('正在加载下一素材…');
        }
         //上一个
         else{
            if(!this.prevHiWorksId){
                return;
            }
            this.baseId=this.prevHiWorksId;
            this.showTips('正在加载上一素材…');
        }
        this.viewWorksDetailInfo();  //加载内容
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
                '<a href="javascript:showFullImg('+i+')">'+
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
        $('#currentPage ul').html(str1);

        //实例化缩放
        ImagesZoom.init({
            "elem": "#slider4"
        });

        this.initTouchSlider();
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

    /*share 下载*/
    t.doOperationForWorkShare=function(e){
        var $target=$(e.currentTarget),
            index=$target.index(),
            that=this;

        //下载
        this.controlModelBox(1,0,function(){
            //如果本地存储有邮箱信息，直接加载
            var email=that.getInfoFromStorage('myemail');
            if(email){
                $('#email').val(email);
                $('#do-bind').addClass('abled btn');
            }
        });
    };

    /*下载、分享、复制*/
    t.doOperationForWork=function(e){
        var $target=$(e.currentTarget),
            index=$target.index(),that=this;

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

        var para = {
            url: this.baseUrl + 'hiworks/sendDownLoadURLToEMail',
            type: 'get',
            paraData: { email: email, hiwork_id:this.currentWorksObj.id},
            sCallback: function (data) {
                if(data.success) {
                    email = that.substrLongStr(email, 20);
                    that.showTips('<p>已成功发送至邮箱</p><p>' + email + '</p><p>请注意查收</p>');
                    that.controlModelBox(0,0);
                }else{
                    that.showTips('邮件发送失败');
                }
            },eCallback: function (data) {
                that.showTips('邮件发送失败');
            }
        };
        this.getDataAsync(para);
    };

    //取消绑定邮箱
    t.hideBindEmail=function(){
        this.controlModelBox(0,0);
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


    /*控制底部logo的位置样式*/
    t.controlCoverFootStyle=function () {
        var $target = $('#downloadCon'),
            $a = $target.find('span'),
            aw = $a.width(),
            ah = aw * 0.40,
            bw = $target.width(),
            h = bw * 120 / 750;
        $target.css({'height': h + 'px', 'left': ($('body').width() - bw) / 2, 'opacity': 1});
        $('#work-detail-panel').css('padding-top', h + 'px');
        var fontSize = '16px';
        if (bw < 375) {
            fontSize = '14px';
        }
        $a.css({'top': (h - ah) / 2, 'height': ah + 'px', 'line-height': ah + 'px', 'font-size': fontSize});
    };


    /*
     *显示全图
     *@para:
     *index - {index} 图片数组下标
     */
    t.showFullImg=function(index){
        //alert(index);
    };

    /*
     *显示全图
     *@para:
     *index - {index} 图片数组下标
     */
    window.showFullImg=function(index){
        window.hiworks.showFullImg(index);
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

    /*设置页面的标题，
    * 针对ios在页面生成后，js 不能修改的情况
    */
    t.setTitle=function(title){
        if(this.deviceType.ios) {
            var $body = $('body');
            document.title = title;

            // hack在微信等webview中无法修改document.title的情况
            var $iframe = $('<iframe src="/favicon.ico"></iframe>').on('load', function () {
                setTimeout(function () {
                    $iframe.off('load').remove()
                }, 0)
            }).appendTo($body);
        }else{
            $('title').text(title);
        }

    };

    return HiWorks;
});