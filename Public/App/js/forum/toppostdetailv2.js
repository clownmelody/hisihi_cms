/**
 * Created by jimmy on 2015/11/18.
 */



/********蒋建明修改 评论框********/
function commentObj($wrapper,urlObj){
    this.$wrapper=$wrapper;
    this.urlObj=urlObj;
    this.separateOperation();
    var that = this;

    this.controlCommentBoxStatus();
    this.$wrapper.on('touchend', '#comment-box .abled', $.proxy(this, 'commitComment'));
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

}

commentObj.prototype={

    separateOperation:function(){
        var that=this,
            operation=browserType(),
            info;
        if(operation.mobile){
            if(operation.android){
                info=AppFunction.getUser();
            }
            else if(operation.ios){
                info=getUser_iOS();
            }
            if(info) {
                this.userInfo = JSON.parse(info);
            }
        }else{
            $.ajax({
                url:this.urlObj.server_url+'/user/login',
                data:{username:'18601995231',password:'123456',type:'3',client:'4'},
                async:false,
                success:function(data) {
                    if (data.success) {
                        that.userInfo = {session_id: data.session_id, name: data.name, pic: data.avatar_url};
                    }
                }
            });
        }
    },

    /*
     *控制评论框的显示状态，如果用户没有登录， session_id 为空  则不显示
     */
    controlCommentBoxStatus:function(){
        if(!this.userInfo.session_id){
            this.$wrapper.hide();
            return;
        }
        this.$wrapper.show();
    },

    /*
     *控制
     */

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
        if(!this.userInfo.session_id){
            return;
        }
        $target.addClass('disabled');

        var ajaxTimeoutTest=$.ajax({
            url:this.server_url+'/Forum/doReply',  //请求的URL
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
                $target.removeClass('disabled');
            },
            complete : function(XMLHttpRequest,status){    //请求完成后最终执行参数
                if(status=='timeout'){   //超时,status还有success,error等值的情况
                    ajaxTimeoutTest.abort();
                    that.showCommentTips.call(that,'请求超时');
                    $target.removeClass('disabled');
                }
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



