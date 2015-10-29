/**
 * Created by Jimmy on 2015/10/24.
 */

 //基本信息

 define(['jquery','jqueryuploadify'],function () {
 	var BasicInfo = function ($wrapper) {
 	    this.$wrapper = $wrapper;
		this.sectionId=JSON.parse($.cookie('hisihi-org')).session_id;
		this.organization_id=20;
		this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
		this.loadBasicData();  //显示基本信息
		this.initJcropParas();
		this.initUploadify();  //头像上传插件初始化
		this.validate=this.getFormValidity();

 	    //事件注册
 	    var that=this;
 	    this.$wrapper.on('focus','input',$.proxy(this,'getNameFocus'));
 	    this.$wrapper.on('click','#submitBasicInfo',$.proxy(this,'submitBaseInfo'));
 	    this.$wrapper.on('click','#addtags',$.proxy(this,'AddTags'));

		this.$wrapper.on('click',".recommend-box .box-tag .label",function () {
			var txt = $(this).html();
			$(this).remove();
			$(".tag-open").append("<span class='label label-primary'>"+txt+"<a href='javaScript: void(0);' onclick='$(this).parent().remove();' class='box-add' id='box-add'><span class='icon-add'>&#215;</span></a></span>");
		});

		//机构基本确认提交
		this.$wrapper.on('click','#addtags',$.proxy(this,'AddTags'));

		//上传头像
		this.$wrapper.on('click',"#UploadImg", function () {
			$('#ImgModal').fadeIn(500);
			that.initializeCrop(that.$wrapper.find('#myPicture'));  //头像裁剪初始化
		});

		//关闭弹出层
		this.$wrapper.on("click",".close", function(){
			that.cancelCrop.call(that,true);
		});

 	};

 	BasicInfo.prototype={

		/*显示基本信息*/
		loadBasicData:function(){
			var url=this.basicApiUrl+'/getCourses',
				that=this;
			Hisihi.getDataAsync({
				type: "post",
				url: url,
				data: {},
				org:true,
				callback:function(data) {}
			});
		},
 		getNameFocus:function(){
 			$('input').nextAll('p').remove(".error-txt");
 		},
 		getNameBlur:function(){
 			if (!$('input[name="name"]').val()){
 			    $('input[name="name"]').parent().append("<p class='error-txt text-danger'>当前输入为空</p>");
 			}
 		},

 		//添加标签
 		AddTags:function(){
 			var tagsTxt = $('input[name="AddTags"]');
 			tagsTxt.nextAll('p').remove(".error-txt");
 			if (!tagsTxt.val()) {
 				tagsTxt.parent().append("<p class='error-txt text-danger'>当前输入为空</p>");
 			}else{
 				$(".tag-open").append("<span class='label label-primary'>"+tagsTxt.val()+"<a href='javaScript: void(0);' onclick='$(this).parent().remove();' class='box-add' id='box-add'><span class='icon-add'>&#215;</span></a></span>");
 				tagsTxt.attr("value","");
 			};
 		},

		submitBaseInfo:function(){
			if(this.validate.form()) {
				this.$wrapper.find('#basicForm').ajaxSubmit({
					//type:'post',
					//url:'http://127.0.0.1:8080/hisihi-cms/api.php?s=/Organization/login',
					url: window.urlObject.apiUrl + '/saveBaseInfo',
					success: function (data) {

					}
				});
			}
 		},


		/*裁剪插件初始化*/
		initializeCrop: function ($img) {
			var that = this;
			$img.Jcrop({
				//minSize: [50, 50],
				maxSize: [550, 550],
				boxWidth: that.imgBoxNewInfo.width,
				boxHeight: that.imgBoxNewInfo.height,
				trueSize: [that.imgBoxNewInfo.width, that.imgBoxNewInfo.height],
				aspectRatio: 1,
				borderOpacity: 0,
				bgColor: 'black',  //整个背景色
				bgOpacity: 0.4,
				boundary: 0,
				sideHandles: false,
				baseClass: "jcrop",
				onRelease: function () { },
				onChange: function (c) {
					that.jcropRegion = {
						X: c.x,
						Y: c.y,
						Height: c.h,
						Width: c.w
					};
				},
				onSelect: function (c) {
					that.jcropRegion = {
						X: c.x,
						Y: c.y,
						Height: c.h,
						Width: c.w
					};
				}
			}, function () {
				that.jcrop_api = this;
				/*初始化裁剪框*/
				//that.initCropBoxInfo.call(that);
			});
		},

		/*取消裁剪*/
		cancelCrop:function(flag) {
			if(flag) {
				this.$wrapper.find("#ImgModal").fadeOut(500);
				//清空file之前的内容
				this.$wrapper.find('#userPhotoForm')[0].reset();
			}
			this.jcrop_api.destroy();  //隐藏裁剪框

		},

		/*
		*设定参数
		*/
		initJcropParas:function(){
			/*jcrop头像裁剪对象*/
			this.jcrop_api=null;
			/*jcrop头像裁剪默认范围*/
			this.jcropRegion={
				X: 10,
				Y: 10,
				Height: 180,
				Width: 180
			};

			/*裁剪图片框的最大最小值*/
			this.imgBoxDefaultInfo={ maxWidth: 605, maxHeight: 370 };
			this.imgBoxNewInfo={width:231,height:240};
		},

		/*
		*初始化头像上传插件
		 */
		initUploadify:function() {
			var that=this;
			$("#upload_company_picture").uploadify({
				"height": 30,
				"swf":window.urlObject.js+"/libs/uploadify/uploadify.swf",
				"fileObjName": "download",
				"buttonText": "上传图片",
				"uploader":that.basicApiUrl+'/uploadLogo' ,
				"width": 120,
				'removeTimeout': 1,
				'fileTypeExts': '*.jpg; *.png; *.gif;',
				"onUploadSuccess": uploadPictureCompany,
				'onFallback': function () {
					alert('未检测到兼容版本的Flash.');
				}
			});
			function uploadPictureCompany(file, data) {
				var data = $.parseJSON(data);
				var src = '';
				if (data.success) {
					var $img=$('#myPicture');
					$img.attr({'src':data.logo.path,'data-img-id':data.logo.id});
					that.cancelCrop(false);
					that.initializeCrop($img);
				} else {
					//(data.info);
					data.info
					setTimeout(function () {

					}, 1500);
				}
			}
		},

		//表单验证
		getFormValidity:function(){
			return $("#basicForm").validate({
				rules: {
					name: {
						required: true,
					},
					slogan: {
						required: true,
					},
					location: {
						required: true,
					},
					phone_num:{
						required: true,
					}
				},
				messages: {
					name: "请输入姓名",
					email: {
						required: "签名不能为空",
					},
					location: {
						required: "地址不能为空",
					},
					phone_num:{
						required: "联系方式不能为空",
					}
				},
				errorPlacement: function (error, element) {
					error.appendTo(element.next('.basicFormInfoError'));
				}
			});
		},


 	};


	 var $wrapper=$('.basicinfoWrapper');
	 if($wrapper.length>0) {
		  new BasicInfo($wrapper);
	 }

 });