/**
 * Created by Jimmy on 2015/10/26.
 */
$(function(){
    $('#login').on('click',function(){
        $('#loginForm').ajaxSubmit({
            url:window.urlObject.apiUrl+'/api.php?s=/Organization/login',
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

    $(".item-box").stellar({
        horizontalScrolling: false,
              horizontalOffset: 40,//水平偏移
               verticalOffset: 150,//垂直偏移
        hideElement: function($elem) { $elem.hide(); },
        showElement: function($elem) { $elem.show(); }

    });

    //首页数据加载效果
    if($('.data-box').length>0 && $('.data-box').is(':visible') ){
      $('#num_problem').animateNumber({ number: 52414 },3000);
      $('#num_student').animateNumber({ number: 20779 },2000);
      $('#num_file').animateNumber({ number: 12886 },2000);
      $('#num_design').animateNumber({ number: 2019 },2000);
    }


});
