/**
 * Created by Jimmy on 2015/10/24.
 */

 //基本信息

 define(['jquery'],function () {
 	var BasicInfo = function ($wrapper) {
 	    this.$wrapper = $wrapper;

 	    //事件注册
 	    var that=this;

		this.$wrapper.on('click',".certification-btn", function () {
			if ($('.certification-btn').hasClass('disabled')) {
			}else{
				$('.certification-btn').addClass('disabled');
				$('.certification-txt').html("我们会派人和您联系，核实您的认证形象，请您耐心等待。");
			}
			
		});

 	};


 	BasicInfo.prototype={

 	};
	 var $wrapper=$('.certification');
	 if($wrapper.length>0) {
		  new BasicInfo($wrapper);
	 }

 });