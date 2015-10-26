/**
 * Created by Jimmy on 2015/10/24.
 */

 //基本信息

 define(['jquery'],function () {
 	var BasicInfo = function ($wrapper) {
 	    this.$wrapper = $wrapper;
 	    //事件注册
 	    var that=this;
 	    this.$wrapper.on('blur','input',$.proxy(this,'getNameInfo'));
 	    this.$wrapper.on('click','#SubmitBtn',$.proxy(this,'SubmitInfo'));
 	};

 	$(".recommend-box .box-tag .label").bind("click", function () {
 		var txt = $(this).html();
 		$(this).remove();
 		$(".tag-open").append("<span class='label label-primary'>"+txt+"<a href='javaScript: void(0);' onclick='$(this).parent().remove();' class='box-add' id='box-add'><span class='icon-add'>&#215;</span></a></span>");
 	});

 	


 	BasicInfo.prototype={

 		// 验证用户名
 		getNameInfo:function(){
 			$('input[name="name"]').next('p').remove(".error-txt");
 			if (!$('input[name="name"]').val()){
 			    $('input[name="name"]').parent().append("<p class='error-txt text-danger'>当前输入为空</p>");
 			}
 		},

 		//推荐标签
 		featuredTags:function(){

 		}, 		

 		SubmitInfo:function(){
 			$('.basicinfoWrapper').submit();
 		},
 	};

 	var basicInfo=new BasicInfo($('.basicinfoWrapper'));
 	return basicInfo;

 });