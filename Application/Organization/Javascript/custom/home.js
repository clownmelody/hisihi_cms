/**
 * Created by Jimmy on 2015/10/26.
 */
$(function(){
    var baseUrl=window.urlObject.apiUrl+'/api.php?s=/Organization',
        timeInterval=null;
    $('#showRegisterBox').on('click',function(){
        $('#loginBox').hide();
        $('#registerBox').show();
    });
    $('#showLoginBox').on('click',function(){
        $('#loginBox').show();
        $('#registerBox').hide();
    });
    $('#showForgetBox').on('click',function(){
        $('#forgetBox').show();
        $('#loginBox').hide();
    });

    $('#login').on('click',function(){
        $('#loginForm').ajaxSubmit({
            url:baseUrl+'/login',
            success:function(data){
                if(data.success && data.message=='登陆成功') {
                    $.cookie('hisihi-org',null);
                    $.cookie('hisihi-org',JSON.stringify(data),{expires:7});
                    window.location.href = window.urlObject.ctl + "/Index/announcement";
                }
            },
            error:function(e){
                alert('登录失败');
            }
        });
    });

    $('#register').on('click',function(){
        var number=$('#number').val();
        //mobile    用户手机号
        //sms_code  短信验证码
        //password
    });

    //获取手机验证码
    $('#sendCheckCode').on('click',function(){
        var tel = $("#mobileNumber").val(); //获取手机号
        var telReg = !!tel.match(/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/);

        //如果手机号码不能通过验证
        if(telReg == false){
            alert('手机号码不正确，请重新输入');
            return;
        }

        //mobile
        $.post(baseUrl+'/getSMS',{mobile:tel},function(data){
            if(data.success){
                $(this).attr('disabled','disabled');
                timeInterval = window.setInteval(function(){
                    updateTimeShowInfo();
                },60*1000);
            }else{
                alert('验证码获取失败，请重新获取');
            }
        });
    });

    /*
    *更新时间
     */
    function updateTimeShowInfo(){
        var $target=$('#leftTime'),
            leftTime=parseInt($target.text());
        if(leftTime==0){
            //按钮的初始状态
            $('#sendCheckCode').removeAttr('disabled');
            $target.text('60');
            window.clearInterval(timeInterval);

        }else{
            left--;
            $target.text(left);
        }

    }

    /*$(".item-box").stellar({
        horizontalScrolling: false,
              horizontalOffset: 40,//水平偏移
               verticalOffset: 150,//垂直偏移
          showElement: function($element){
            $element.fadeIn(2000);
          },
          hideElement: function($element){
            $element.fadeOut(2000);
          }
    });*/

    $.stellar({
          showElement: function($element){
            $element.show();
          },
          hideElement: function($element){
            $element.hide();
          }
    });

    //首页数据加载效果
    if($('.data-box').length>0 && $('.data-box').is(':visible') ){
      $('#num_problem').animateNumber({ number: 52414 },3000);
      $('#num_student').animateNumber({ number: 20779 },2000);
      $('#num_file').animateNumber({ number: 12886 },2000);
      $('#num_design').animateNumber({ number: 2019 },2000);
    }


});
