/**
 * Created by Jimmy on 2015/10/26.
 */
$(function(){
    $('#login').on('click',function(){
            $('#loginForm').ajaxSubmit({
                //type:'post',
                //url:'http://127.0.0.1:8080/hisihi-cms/api.php?s=/Organization/login',
                url:window.urlObject.apiUrl+'/api.php?s=/Organization/login',
                success:function(data){
                    if(data.success && data.message=='登陆成功') {
                        $.cookie('hisihi-org',JSON.stringify(data),{expires:7});
                        window.location.href = window.urlObject.ctl + "/Index/announcement";
                    }
                },
                error:function(e){
                    alert('登录失败');
                }
            });

        });
});

