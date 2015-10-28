/**
 * Created by Jimmy on 2015/10/24.
 */

 //基本信息

 define(['jquery'],function () {
 	var BasicInfo = function ($wrapper) {
 	    this.$wrapper = $wrapper;
		this.sectionId=JSON.parse($.cookie('hisihi-org')).session_id;
		this.organization_id=20;
		this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
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


 	    //事件注册
 	    var that=this;
 	    this.$wrapper.on('focus','input',$.proxy(this,'getNameFocus'));
 	    //this.$wrapper.on('blur','input',$.proxy(this,'getNameBlur'));
 	    this.$wrapper.on('click','#SubmitBtn',$.proxy(this,'submitBaseInfo'));
 	    this.$wrapper.on('click','#addtags',$.proxy(this,'AddTags'));
		$('#dataImportFileInput').change(function (e) {
			that.uploadNewImg.call(that,e);
		});

		this.$wrapper.on('click',".recommend-box .box-tag .label",function () {
			var txt = $(this).html();
			$(this).remove();
			$(".tag-open").append("<span class='label label-primary'>"+txt+"<a href='javaScript: void(0);' onclick='$(this).parent().remove();' class='box-add' id='box-add'><span class='icon-add'>&#215;</span></a></span>");
		});

		//上传头像
		this.$wrapper.on('click',"#UploadImg", function () {
			$('#ImgModal').fadeIn(500);
			that.initializeCrop(that.$wrapper.find('#myPicture'));  //头像裁剪初始化
		});

		//关闭弹出层
		this.$wrapper.on("click",".close", $.proxy(that,'cancelCrop'));

 	};



 	$("#myFile").change(function () {
 	    var filepath = $("input[name='myFile']").val();
 	    console.log(filepath);
 	    var extStart = filepath.lastIndexOf(".");
 	    console.log(extStart);
 	    var ext = filepath.substring(extStart, filepath.length).toUpperCase();
 	    console.log(ext);
 	    if (ext != ".BMP" && ext != ".PNG" && ext != ".GIF" && ext != ".JPG" && ext != ".JPEG") {
 	        alert("图片限于bmp,png,gif,jpeg,jpg格式");
 	        $("#fileType").text("")
 	        $("#fileSize").text("");
 	        return false;
 	    } else { $("#fileType").text(ext) }
 	    var file_size = 0;
 	    if ($.browser.msie) {
 	        var img = new Image();
 	        img.src = filepath;
 	        while (true) {
 	            if (img.fileSize > 0) {
 	                if (img.fileSize > 3 * 1024 * 1024) {
 	                    alert("图片不大于100MB。");
 	                } else {
 	                    var num03 = img.fileSize / 1024;
 	                    num04 = num03.toFixed(2)
 	                    $("#fileSize").text(num04 + "KB");
 	                }
 	                break;
 	            }
 	        }
 	    } else {
 	        file_size = this.files[0].size;
 	        var size = file_size / 1024;
 	        if (size > 10240) {
 	            alert("上传的图片大小不能超过10M！");
 	        } else {
 	            var num01 = file_size / 1024;
 	            num02 = num01.toFixed(2);
 	            $("#fileSize").text(num02 + " KB");
 	        }
 	    }
 	    return true;
 	});
	

 	BasicInfo.prototype={

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
			this.$wrapper.find('#basicForm').ajaxSubmit({
				//type:'post',
				//url:'http://127.0.0.1:8080/hisihi-cms/api.php?s=/Organization/login',
				url: window.urlObject.apiUrl + '/saveBaseInfo',
				success: function (data) {

				}
			});
 		},

		//上传头像图片
		uploadNewImg:function(e){

			/*是否正在上图片*/
			var $obj = $(e.currentTarget),
				that=this,
				value = $obj.val();
			this.$wrapper.find('#photoErrorInfo').text('').hide();
			if (value == '') {
				$('#photoErrorInfo').text('请选择图片！').show();
				return;
			}
			$('#userPhotoForm').ajaxSubmit({
				url: that.basicApiUrl + '/uploadLogo',
				type: 'post',
				beforeSubmit: function () {
					var ss = '';
				},
				complete: function (data) {
					var text = data.responseText;
					if ('公有账号只能查看哦' == text) {
						$('#photoErrorInfo').text(text).show();
						$('#photoSuggestInfo').hide();
					}
					else {
						data = JSON.parse(data.responseText);
						if (data.success) {
							//显示上传图片，并准备裁剪
							that.uploadPhotoSuccessCallBack.call(that, data);
						}
						else {
							$('#photoErrorInfo').text(data.error).show();
							$('#photoSuggestInfo').hide();
						}
					}
				},
				error: function (e) {

				}
			});

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
		cancelCrop:function() {
			this.$wrapper.find("#ImgModal").fadeOut(500);
			this.jcrop_api.destroy();  //隐藏裁剪框
			//清空file之前的内容
			this.$wrapper.find('#userPhotoForm')[0].reset();
		},

 	};
	 var $wrapper=$('.basicinfoWrapper');
	 if($wrapper.length>0) {
		  new BasicInfo($wrapper);
	 }

 });