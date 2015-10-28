/**
 * Created by Jimmy on 2015/10/24.
 */

 //基本信息

 define(['jquery'],function () {
 	var BasicInfo = function ($wrapper) {
 	    this.$wrapper = $wrapper;
 	    //事件注册
 	    var that=this;
 	    this.$wrapper.on('focus','input',$.proxy(this,'getNameFocus'));
 	    //this.$wrapper.on('blur','input',$.proxy(this,'getNameBlur'));
 	    this.$wrapper.on('click','#SubmitBtn',$.proxy(this,'SubmitInfo'));
 	    this.$wrapper.on('click','#addtags',$.proxy(this,'AddTags'));
 	};

 	$(".recommend-box .box-tag .label").bind("click", function () {
 		var txt = $(this).html();
 		$(this).remove();
 		$(".tag-open").append("<span class='label label-primary'>"+txt+"<a href='javaScript: void(0);' onclick='$(this).parent().remove();' class='box-add' id='box-add'><span class='icon-add'>&#215;</span></a></span>");
 	});

 	$("#UploadImg").bind("click", function () {
 		$('#ImgModal').fadeIn(500);
 	});

 	$(".close").bind("click", function () {
 		$("#ImgModal").fadeOut(500);
 	});

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

 		SubmitInfo:function(){
 			$('.basicinfoWrapper').submit();
 		},
 	};

 	var basicInfo=new BasicInfo($('.basicinfoWrapper'));
 	return basicInfo;

 });