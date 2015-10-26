/**
 * Created by Jimmy on 2015/10/26.
 */
$(function(){
    $('#login').on('click',function(){
            $('#loginForm').ajaxSubmit({
                type:'post',
                //url:'http://115.28.72.197/api.php?s=/Organization/login&mobile=1850755434&password=976499',
                //url:window.urlObject.apiUrl+'/api.php?s=/Organization/login',
                url:'http://localhost/my/welcome.php',
                data:{mobile:1850755434,password:976499 },
                success:function(data){
                    window.location.href =window.urlObject.ctl + "/Index/announcement";
                },
                error:function(e){
                    alert(e);
                }
            });

        });
        return false;
});

