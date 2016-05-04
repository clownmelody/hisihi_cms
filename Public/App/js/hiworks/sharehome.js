/**
 * Created by jimmy on 2016/4/18.
 * version-2.7
 */
define(['fx','base','myscroll'],function(fx,Base,MyScroll) {
    var HiWorks = function (url,baseId) {
        this.baseUrl = url;
        this.$wrapper = $('body');

        //访问来源
        this.baseId=baseId;

        var eventName='click',that=this;
        this.deviceType = this.operationType();
        if(this.deviceType.mobile){
            eventName='touchend';
        }
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
        $(document).on(eventName,'.detail-bottom-btns .item',$.proxy(this,'doOperationForWork'));

        this.viewWorksDetailInfo();

    };
    HiWorks.prototype =new Base(true);
    HiWorks.constructor=HiWorks;

    var t=HiWorks.prototype;


    /*******************作业详细信息查看**********************/

    t.viewWorksDetailInfo=function(){
        var para = {
            url: this.baseUrl + 'hiworks/bindEmail',
            type: 'get',
            paraData: {id:this.this.baseId},
            sCallback: function (data) {
                var title=that.substrLongStr(this.currentWorksObj.title,12);
                $('#detail-title').text(title);
                var covers=this.currentWorksObj.multi_cover_info,
                    flag=true;
                if(covers.count==0){
                    flag=false;
                    covers.count=1;
                    covers.data=[window.hisihiUrlObj.img_url+'/hiworks/hisihi.png'];
                }
                this.fillInTouchSliderItem(covers,flag);
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
        var t4=new TouchSlider('slider4',{speed:1000, direction:0, interval:1000000, fullsize:true});
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
                '<a href="#">'+
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
        if(this.userInfo.session_id==''){
            this.controlModelBox(1,1);
            return;
        }
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
            url: this.baseUrl + 'hiworks/bindEmail',
            type: 'get',
            paraData: {session_id: this.userInfo.session_id, email: email, hiwork_id:this.currentWorksObj.download_url.trim()},
            sCallback: function (data) {
                that.userInfo = data;
                that.showTips('','<p>已成功发送至邮箱</p><p>124569874125@163.com</p><p>请注意查收</p>');
            },eCallback: function (data) {
                that.showTips(data.txt);
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

    return HiWorks;
});