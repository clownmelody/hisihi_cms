/**
 * Created by jimmy on 2016/4/18.
 * version-2.7
 */
define(['fx','base','fastclick'],function(fx,Base) {
    FastClick.attach(document.body);
    var HiWorks = function (url,baseId) {
        this.baseUrl = url;
        this.baseId=baseId;

        var eventName='click',that=this;
        this.deviceType = this.operationType();
        //if(this.deviceType.mobile){
        //    eventName='touchend';
        //}
        $(document).on(eventName,'.btn',function(){
            event.stopPropagation();
        });

        //控制输入框
        $(document).on('input','#email',$.proxy(this,'controlCommitBtn'));

        //绑定邮箱
        $(document).on(eventName,'#do-bind',$.proxy(this,'bindEmail'));

        //取消绑定
        $(document).on(eventName,'#cancle-bind',$.proxy(this,'hideBindEmail'));

        //下载、复制、分享
        $(document).on(eventName,'.detail-bottom-btns .btn',$.proxy(this,'doOperationForWork'));

        $('#downloadCon .btnElement').on(eventName,function(){
            window.location.href = "http://www.hisihi.com/download.php";
        });

        this.viewWorksDetailInfo();  //加载图片

        this.controlCoverFootStyle();  //控制下载条样式

    };
    HiWorks.prototype =new Base(true);
    HiWorks.constructor=HiWorks;

    var t=HiWorks.prototype;


    /*******************作业详细信息查看**********************/

    t.viewWorksDetailInfo=function(){
        var that=this;
        var para = {
            url: window.hisihiUrlObj.link_url + 'hiworks_list.php/index/getHiworkDetailById',
            type: 'get',
            paraData: {hiwork_id:this.baseId},
            sCallback: function (result) {
                if(!result.success){
                    return;
                }
                var data=result.data;
                that.currentWorksObj=data;

                var title=that.substrLongStr(data.title,12);
                $('#detail-title').text(title);
                var covers=data.multi_cover_info,
                    flag=true;
                if(!covers || covers.count==0){
                    flag=false;
                    covers.count=1;
                    covers.data=[window.hisihiUrlObj.img_url+'/hiworks/hisihi.png'];
                }
                that.fillInTouchSliderItem(covers,flag);
            },eCallback: function (data) {
                that.showTips(data.txt);
            }
        };
        this.getDataAsync(para);


    };

    /*滑动图片*/
    t.initTouchSlider=function(){
        var h=$('body').height(),
            flag=$('#slider4').attr('data-init');
        $('#detail-main').height(h-135).css('opacity','1');
        var t4=new TouchSlider('slider4',{speed:1000, direction:0, interval:60*60*1000, fullsize:true});
        if(!flag) {
            t4.on('before', function (m, n) {
                $('#currentPage ul li').eq(n).addClass('active').siblings().removeClass('active');
            });
            $('#currentPage ul li').on('touchend', function (e) {
                var index = $(this).index();
                t4.slide(index);
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

    /*下载、分享、复制*/
    t.doOperationForWork=function(e){
        var $target=$(e.currentTarget),
            index=$target.index();
        //下载
        this.controlModelBox(1,0);
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

        var para = {
            url: this.baseUrl + 'hiworks/sendDownLoadURLToEMail',
            type: 'get',
            paraData: { email: email, hiwork_id:this.currentWorksObj.id},
            sCallback: function (data) {
                if(data.success) {
                    email = that.substrLongStr(email, 20);
                    that.showTips('', '<p>已成功发送至邮箱</p><p>' + email + '</p><p>请注意查收</p>');
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
     *strFormat - {bool} 自定义的简单格式
     */
    t.showTips=function(tip,strFormat){
        var $tip=$('body').find('.result-tips'),
            $p=$tip.find('p').text(tip);
        if(strFormat){
            $tip.html(strFormat);
        }
        $tip.show();
        window.setTimeout(function(){
            $tip.hide();
            $p.text('');
        },1500);
    };


    /*
     *控模态窗口的显示 和 隐藏
     * Para:
     * opacity - {int} 透明度，1 表示显示，0表示隐藏
     * index - {int} 控制的对象，1 登录提示框，0评论框
     * title - {string} 提示标题
     * callback - {string} 回调方法
     */
    t.controlModelBox=function(opacity,index,title,callback) {
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

    return HiWorks;
});