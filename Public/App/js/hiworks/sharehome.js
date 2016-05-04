/**
 * Created by jimmy on 2016/4/18.
 * version-2.7
 */
define(['fx','base','myscroll'],function(fx,Base,MyScroll) {
    var HiWorks = function (url,baseId) {
        this.baseUrl = url;
        this.$wrapper = $('body');

        //������Դ
        this.baseId=baseId;

        var eventName='click',that=this;
        this.deviceType = this.operationType();
        if(this.deviceType.mobile){
            eventName='touchend';
        }
        $(document).on(eventName,'.btn',function(){
            event.stopPropagation();
        });

        //���������
        $(document).on('input','#email',$.proxy(this,'controlCommitBtn'));

        //������
        $(document).on(eventName,'#do-bind',$.proxy(this,'bindEmail'));

        //ȡ����
        $(document).on(eventName,'#cancle-bind',$.proxy(this,'hideBindEmail'));

        //���ء����ơ�����
        $(document).on(eventName,'.detail-bottom-btns .item',$.proxy(this,'doOperationForWork'));

        this.viewWorksDetailInfo();

    };
    HiWorks.prototype =new Base(true);
    HiWorks.constructor=HiWorks;

    var t=HiWorks.prototype;


    /*******************��ҵ��ϸ��Ϣ�鿴**********************/

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

    /*����ͼƬ*/
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
    *�����������ͼƬ
    *@para:
    *covers - {obj} ������Ϣ��������ַ�����
    *flag - {bool} �Ƿ�û��ͼƬ��false ��ʹ�� nocover ��ʽ ����
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

    /*���ư�ť�Ŀ�����*/
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

    /*���ء���������*/
    t.doOperationForWork=function(e){
        var $target=$(e.currentTarget),
            index=$target.index();
        if(this.userInfo.session_id==''){
            this.controlModelBox(1,1);
            return;
        }
        //����
        this.controlModelBox(1,0);
    };

    //ȷ������
    t.bindEmail=function(e){
        var $target=$(e.currentTarget),that=this;
        if(!$target.hasClass('abled')){
            return;
        }
        var $email=$('#email'),
            email=$email.val().trim(),
            reg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
        if(!reg.test(email)){
            this.showTips('�����ʽ��������������');
            return;
        }

        var para = {
            url: this.baseUrl + 'hiworks/bindEmail',
            type: 'get',
            paraData: {session_id: this.userInfo.session_id, email: email, hiwork_id:this.currentWorksObj.download_url.trim()},
            sCallback: function (data) {
                that.userInfo = data;
                that.showTips('','<p>�ѳɹ�����������</p><p>124569874125@163.com</p><p>��ע�����</p>');
            },eCallback: function (data) {
                that.showTips(data.txt);
            }
        };
        this.getDataAsync(para);
    };

    //ȡ��������
    t.hideBindEmail=function(){
        this.controlModelBox(0,0);
    };


    /*******************ͨ�ù���*********************/

    /*
     *���Ƽ��صȴ���
     *@para
     * flag - {bool} Ĭ������
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
     *��ʾ�������
     *para:
     *tip - {string} ���ݽ��
     *strFormat - {bool} �Զ���ļ򵥸�ʽ
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
     *��ģ̬���ڵ���ʾ �� ����
     * Para:
     * opacity - {int} ͸���ȣ�1 ��ʾ��ʾ��0��ʾ����
     * index - {int} ���ƵĶ���1 ��¼��ʾ��0���ۿ�
     * title - {string} ��ʾ����
     * callback - {string} �ص�����
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