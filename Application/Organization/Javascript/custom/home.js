/**
 * Created by Jimmy on 2015/10/26.
 */
$(function(){
    var baseUrl=window.urlObject.apiUrl+'/api.php?s=/Organization',
        timeInterval=null,
        registerValidity=setValidityForRegister(),
        loginValidity=setValidityForLogin();
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
        if(loginValidity.form()) {
            $('#loginForm').ajaxSubmit({
                url: baseUrl + '/login',
                success: function (data) {
                    if(data) {
                        if (data.success) {
                            if (data.message == '登陆成功') {
                                setCookie({
                                    organization_id: data.organization_id,
                                    organization_logo:data.organization_logo,
                                    organization_name:data.organization_name,
                                    session_id:data.session_id,
                                    uid: data.uid,
                                    username:data.username
                                });
                            }
                            else {
                                alert('登录失败');
                            }
                        }
                        else{
                            alert(data.message);
                        }
                    }else{
                        alert('登录失败');
                    }
                },
                error: function (e) {
                    alert('登录失败');
                }
            });
        }
    });

    $('#register').on('click',function(){
        if(registerValidity.form()) {
            var number = $('#registerMobile').val(),
                pwd=$('#registerPassword').val(),
                checkCode=$('#registerCheckCode').val();
                tempData={
                    mobile:number,
                    password:pwd,
                    sms_code:checkCode
                };
            for(var item in tempData) {
                tempData[item] = tempData[item].replace(/(^\s*)|(\s*$)/g, '');
            }
            //mobile    用户手机号
            //sms_code  短信验证码
            //password
            $.post(baseUrl+'/register',tempData,function(data){
                if(data.success) {
                    setCookie({
                        session_id: data.session_id,
                        organization_id:null,
                        organization_name:null,
                        uid:data.uid,
                        organization_logo:'',
                        username:''


                    });
                }else{
                    alert(data.message);
                }
            });
        }
    });

    //获取手机验证码
    $('#sendCheckCode').on('click',function(){
        var tel = $("#registerMobile").val(); //获取手机号
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
    *写入cookie，并页面跳转
    */
    function setCookie(data){
        $.cookie('hisihi-org',null,{path:"/"});
        $.cookie('hisihi-org',JSON.stringify(data),{expires:7,path:'/'});
        window.location.href = window.urlObject.ctl + "/Index/announcement";
    }

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

    /*表单必填项控制*/
    function setValidityForRegister(){
        return $("#registerForm").validate({
                rules: {
                    registerMobile: {
                        required: true,
                    },
                    registerPassword: {
                        required: true,
                    },
                    registerCheckCode:{
                        required: true,
                    }
                },
                messages: {
                    registerMobile: "请输入姓名",
                    registerPassword: {
                        required: "请输入密码",
                    },
                    registerCheckCode:{
                        required: "请输入手机验证码",
                    }
                },
                errorPlacement: function (error, element) {
                    error.appendTo(element.next('.basicFormInfoError'));
                }
            });
    }

    /*表单必填项控制*/
    function setValidityForLogin(){
        return $("#registerForm").validate({
            rules: {
                mobile: {
                    required: true,
                },
                password: {
                    required: true,
                }
            },
            messages: {
                phoneNum: "请输入手机号",
                registerPassword: {
                    required: "请输入密码",
                }
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.next('.basicFormInfoError'));
            }
        });
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

    var __IMG__ = "/hisihi-cms/Application/Organization/Content/images/home/";
    $('.item1').bgStretcher({
      images: [__IMG__+'bg1.jpg', __IMG__+'bg2.jpg', __IMG__+'bg3.jpg'],
      imageWidth: 1024, 
      imageHeight: 768,
      slideDirection: 'N',
      slideShowSpeed: 1000,
      transitionEffect: 'fade',
      sequenceMode: 'normal',
    });

    //首页数据加载效果
    if($('.data-box').length>0 && $('.data-box').is(':visible') ){
      $('#num_problem').animateNumber({ number: 825199 },3000);
      $('#num_student').animateNumber({ number: 414072 },2000);
      $('#num_file').animateNumber({ number: 2030 },2000);
      $('#num_design').animateNumber({ number: 287 },2000);
    }


});
