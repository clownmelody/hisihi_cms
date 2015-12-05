/**
 * Created by jimmy on 2015/11/18.
 */



/********蒋建明修改 评论框********/
function commentObj($wrapper,urlObj){
    this.$wrapper=$wrapper;
    this.urlObj=urlObj;

    //根据平台的不同，调用不同的app 方法

    this.isFromApp; //页面跳转来源
    this.separateOperation();
    var that = this;


    //控制评论框的显示和总的内容框的高度
    this.controlCommentBoxStatus();

    this.$wrapper.on('touchend', '#comment-box .abled', $.proxy(this, 'commitComment'));
    //this.$wrapper.on('click', '#comment-box .abled', $.proxy(this, 'commitComment'));

    //控制输入框的状态，当有信息输入的时候才可用
    this.$wrapper.on('input','.comment-box-left textarea',function(){
        var txt=$(this).val().trim(),
            $btn=$(this).parent().next(),
            oc='disabled',nc='abled';
        if(!txt){
            oc='abled';
            nc='disabled';
        }
        $btn.removeClass(oc).addClass(nc);
    });

    //下载app
    $('#downloadCon .downBtn').on('touchend',function(){
        window.location.href='http://www.hisihi.com/download.php';
    });

}

commentObj.prototype={

    /*
    *区分安卓和ios
    *从不同的平台的方法 获得用户的基本信息，进行发表评论时使用
    */
    separateOperation:function(){
        var that=this,
            operation=browserType(),
            appFn;
        //this.userInfo = JSON.parse('');
        if(operation.mobile){
            if (operation.android) {
                //如果方法存在
                alert(typeof AppFunction);
                if(typeof AppFunction !="undefined") {
                    alert('进入安卓的方法  前');
                    appFn=AppFunction.getUser;
                    this.userInfo = JSON.parse(AppFunction.getUser());  //调用app的方法，得到用户的基体信息
                    alert('进入安卓的方法  后');
                    alert(JSON.stringify(this.userInfo));
                    //AppFunction.showShareView(true);  //调用安卓的方法，控制分享按钮可用
                }
            }
            else if (operation.ios) {
                //如果方法存在
                if (typeof getUser_iOS !="undefined") {
                    appFn=getUser_iOS;
                }
            }
            if (appFn) {
                //var ssss=appFn();
                //alert(ssss);
                //this.userInfo = JSON.parse(appFn());  //调用app的方法，得到用户的基体信息
                //alert('进入安卓的方法  后');
                this.isFromApp=true;  //来源于app
            }
            else{
                this.isFromApp=false;  //来源于普通页面
            }
        }
        else{
            this.isFromApp=false;  //来源于普通页面
            //$.ajax({
            //    url:this.urlObj.server_url+'/user/login',
            //    data:{username:'18601995231',password:'123456',type:'3',client:'4'},
            //    async:false,
            //    success:function(data) {
            //        if (data.success) {
            //            that.userInfo = {session_id: data.session_id, name: data.name, pic: data.avatar_url};
            //        }
            //    }
            //});
        }
    },

    /*
     * 控制评论框的显示状态，通过 session_id 是否 为空 来
     * 三种情况：
     * 1.用户已经登录，则直接显示评论框，并且主要容器的高度 不 为100%
     * 2.用户未登录，不显示评论框，主要容器的高度  为 100%
     * 3.用户不来源于app，而是从其他的地方进入，不显示评论框，显示下载条，主要容器的高度  不为 100%
     * 如果用户没有登录，   则不显示;并将内容框控制到最高
     */
    controlCommentBoxStatus:function(){
        var $target=$('.main');

        //来源于app
        if(this.isFromApp){
            if(!this.userInfo) {
                this.$wrapper.hide();
                $target.find('.detailed-main').css('margin-bottom','0');
                return;
            }else {
                this.$wrapper.show();
                $target.addClass('mainNormalScreen').removeClass('mainFullScreen');
            }
        }

        //来源于普通的页面
        else {
            $('#downloadCon').show();   //表示用户是从网页或者分享结果中进来的   直接显示下载条
            $target.addClass('mainNormalScreen').removeClass('mainFullScreen');
        }
    },

    /*提交评论*/
    commitComment:function(e){
        var $textarea=this.$wrapper.find('textarea'),
            str=$textarea.val().replace(/(^\s*)|(\s*$)/g,''),
            that=this,
            $target=$(e.currentTarget);
        if(str==''){
            that.showCommentTips.call(that,'内容为空');
            return;
        }
        if(!this.userInfo){
            that.showCommentTips.call(that,'请登录');
            return;
        }
        $target.addClass('disabled').removeClass('abled');
        var ajaxTimeoutTest=$.ajax({
            url:this.urlObj.server_url+'/Forum/doReply',  //请求的URL
            timeout : 20000, //超时时间设置，单位毫秒
            type : 'post',  //请求方式，get或post
            data :{post_id:$('#postid').val(),session_id:this.userInfo.session_id,content:str},  //请求所传参数，json格式
            dataType:'json',//返回的数据格式
            success:function(result){ //请求成功的回调函数
                var tip,$targetCon=$('.detailed-list');
                if(result.success){
                    $textarea.val('');
                    tip='评论成功';
                    var htmlStr='<li>'+
                        '<img src="'+that.userInfo.pic+'" alt="" class="detailed-item-img">'+
                        '<div class="detailed-item-body">'+
                        '<p class="detailed-item-name">'+that.userInfo.name+'</p>'+
                        '<p class="detailed-item-time">'+new Date().format("MM-dd hh:mm")+'</p>'+
                        '<p class="detailed-item-txt">'+str+'</p>'+
                        '</div>'+
                        '</li>';
                    if($targetCon.length>0) {
                        $targetCon.prepend(htmlStr);
                    }
                    else {
                        $targetCon=$('.detailed-main');
                        htmlStr = '<div class="detailed-box detailed-body">'+
                            '<p class="detailed-top-title review-top">评论</p>'+
                            '<ul class="detailed-list">'+htmlStr+'</ul>'+
                            '</div>';
                        $targetCon.append(htmlStr);
                    }
                }else{
                    var tip=result.message;
                    tip =tip||'评论失败';
                }
                that.showCommentTips.call(that,tip);
                $target.addClass('disabled');
            },
            error:function(e){
                if(e.status=='404'){
                    that.showCommentTips.call(that,'地址不存在');
                }else{
                    that.showCommentTips.call(that, e.statusText);
                }
            },
            complete : function(XMLHttpRequest,status){    //请求完成后最终执行参数
                if(status=='timeout'){   //超时,status还有success,error等值的情况
                    ajaxTimeoutTest.abort();
                    that.showCommentTips.call(that,'请求超时');
                }else if(status=='success'){
                }else{
                    that.showCommentTips.call(that,'状态码：'+status+',内容:'+XMLHttpRequest.statusText);
                }
                $target.addClass('disabled');
            }
        });
    },

    /*
     *显示信息发送结果
     *para:
     *tip - {string} 内容结果
     */
    showCommentTips:function(tip){
        var $tip=this.$wrapper.find('.comment-box-header');
        $tip.text(tip).css('opacity',1);
        window.setTimeout(function(){
            $tip.text('').css('opacity',0);
        },1500)
    },

};



/*
 *IOS调用  控制分享按钮的可用性
 */
function canShare(){
    return true;
}



