/**
 * Created by Jimmy on 2015/10/24.
 */

 //基本信息

 define(['jquery','jqueryuploadify','jqueryvalidate','util'],function () {
 	var BasicInfo = function ($wrapper) {
 	    this.$wrapper = $wrapper;
		this.cookie=JSON.parse($.cookie('hisihi-org'));
		this.sectionId=this.cookie.session_id;
		this.organization_id=this.cookie.organization_id;
		this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
		if(this.organization_id && this.organization_id!='0') {
			this.loadBasicData();  //显示基本信息
		}
		this.loadCommonAdvantageTags();//加载普通的标签
		this.initJcropParas();
		Hisihi.initUploadify($("#upload_company_picture"), $.proxy(this,'updateOrgLogoCallback'));  //头像上传插件初始化
		this.validate=this.getFormValidity();

 	    //事件注册
 	    var that=this;
 	    this.$wrapper.on('focus','input',$.proxy(this,'getNameFocus'));
 	    this.$wrapper.on('click','#submitBasicInfoBtn',$.proxy(this,'submitBaseInfo'));

		/*添加标签*/
		this.$wrapper.on('click',".recommendBox a",function () {
			var txt = $(this)[0].title;
			that.execAddNewTag(txt);
		});

		/*删除标签*/
		this.$wrapper.on('click',".myAdvantageItem .box-add",function (){
			$(this).parents('.myAdvantageItem').remove();

		});

		/*显示 隐藏标签*/

		this.$wrapper.on('click',"#showRecommendBox",function (){
			var flag = $(this)[0].checked,
				$target=that.$wrapper.find('#recommendBox');
			if(flag){$target.show()}
			else{$target.hide()}

		});

		//机构基本确认提交
		this.$wrapper.on('click','#addtags',$.proxy(this,'addNewTags'));

		//上传头像
		this.$wrapper.on('click',"#uploadImg", function () {
			$('#ImgModal').fadeIn(500);
			//that.initializeCrop(that.$wrapper.find('#myNewPicture'));  //头像裁剪初始化
		});

		//关闭弹出层
		this.$wrapper.on("click","#modalFootBts a", function(){
			var index=$(this).index();
			if(index==0){
				that.cancelCrop.call(that,true);
			}else{
				that.updateLogo();
			}
		});

 	};

 	BasicInfo.prototype={

		/*显示基本信息*/
		loadBasicData:function(){
			if (this.$wrapper.data('cornerLoading')) {
				this.$wrapper.cornerLoading('showLoading');
			} else {
				this.$wrapper.cornerLoading();
			}
			var url=this.basicApiUrl+'/getBaseInfo',
				that=this;
			Hisihi.getDataAsync({
				type: "post",
				url: url,
				data: {},
				org:true,
				callback: $.proxy(this,'fillInOrgBasicInfo')
			});
		},

		/*填充机构基本信息*/
		fillInOrgBasicInfo:function(result){
			this.$wrapper.cornerLoading('hideLoading');
			if(result.success) {
				var data=result.data,
				 $form = this.$wrapper.find('#basicForm');
				$form.find('#name').val(data.name);
				$form.find('#Signature').val(data.slogan);
				$form.find('#Address').val(data.location);
				$form.find('#orgBasicIntroduce').val(data.introduce);
				$newImg = this.$wrapper.find('#myNewPicture');
				$form.find('#basicInfoLogo').add($newImg).attr({'src':data.logo.url,'data-lid':data.logo.id});

				//加载优势标签
				var str =this.loadAdvantage(data.advantage);
				this.$wrapper.find('#myAdvantage').html(str);
				$form.find('#Contact').val(data.phone_num);
				$form.find('#organization_id').attr('data-org-id',this.organization_id);
			}else{
				alert(result.message);
			}
		},

		//加载通用的标签
		loadCommonAdvantageTags:function(){
			var url=this.basicApiUrl+'/getCommonAdvantageTags',
				that=this;
			Hisihi.getDataAsync({
				type: "post",
				url: url,
				data: {},
				org:false,
				callback: function (result) {
					if (result) {
						if (result.success) {
							var str = that.fillInCommonAdvantageTags.call(that, result.data);
							that.$wrapper.find('#recommendBox').html(str);
						} else {
							alert(result.message);
						}
					}else{
						alert(result.message);
					}
				}
			});
		},

		//填充通用标签
		fillInCommonAdvantageTags:function(data){
			var str='',
				len=data.length,item,name='';
			for(var i=0;i<len;i++){
				item=data[i];
				name=item.value;
				if(name.length>5){
					name=name.substr(0,4)+'…'
				}
				str+='<a data-id="'+item.id+'" title="'+item.value+'" href="javaScript: void(0);" class="label label-default">'+name+'</a>';
			}
			return str;

		},

 		getNameFocus:function(){
 			$('input').nextAll('p').remove(".error-txt");
 		},
 		getNameBlur:function(){
 			if (!$('input[name="name"]').val()){
 			    $('input[name="name"]').parent().append("<p class='error-txt text-danger'>当前输入为空</p>");
 			}
 		},

		/*显示该机构的优势标签*/
		loadAdvantage:function(data){
			if(!data){return '';}
			var str='',
				len=data.length,item,name='';
			for(var i=0;i<len;i++){
				item=data[i];
				name=item.value;
				if(name.length>5){
					name=name.substr(0,4)+'…'
				}
				str+='<span class="label label-primary myAdvantageItem" title="'+item.value+'" data-id="'+ item.id+'">'+
						name+
						'<a href="javaScript: void(0);" class="box-add">' +
							'<span class="icon-add">&#215;</span>'+
						'</a>'+
					  '</span>';
			}
			return str;
		},

		/*是否已经有该标签*/
		isHaveThisTag:function(val){
			var $span = this.$wrapper.find("#myAdvantage>span").filter(function(){
				return $(this).attr('title')==val;
			});
			if($span.length==0){
				return false;
			}
			return true;
		},

 		//添加标签
		addNewTags:function(){
 			var $input=this.$wrapper.find('#myNewAdvantage'),
				tagsTxt = $input.val().replace(/(^\s*)|(\s*$)/g,'');
			if (tagsTxt=='') {
				this.$wrapper.find('#tagExitTips').text('当前输入为空').show().delay(500).hide();
 			}else{
				this.execAddNewTag(tagsTxt);
				$input.val('');
 			}
 		},

		/*
		*标签添加到列表中
		*para:
		*txt - {string} 新的标签名称
		*/
		execAddNewTag:function(txt){
			if(!this.isHaveThisTag(txt)){
				var str=this.loadAdvantage([{id:'54',value:txt}]);
				this.$wrapper.find("#myAdvantage").append(str);
			}else{
				this.$wrapper.find('#tagExitTips').show().delay(500).hide(0);
			}

		},

		/*确认修改信息*/
		submitBaseInfo:function(){

			if(this.validate.form()) {
				var $form = this.$wrapper.find('#basicForm'),
					$label=this.$wrapper.find('#basicInfoResultLabel'),
					that=this;
					name=$form.find('#name').val(),
					newData= {
					organization_id:$form.find('#organization_id').attr('data-org-id'),
					name: name,
					slogan: $form.find('#Signature').val(),
					introduce: $form.find('#orgBasicIntroduce').val(),
					logo: $form.find('#basicInfoLogo').attr('data-lid'),
					advantage:this.getAllMyTagsId(),
					location: $form.find('#Address').val(),
					phone_num:$form.find('#Contact').val()
				};

				Hisihi.getDataAsync({
					type: "post",
					url: this.basicApiUrl + '/saveBaseInfo',
					data: newData,
					org:false,
					callback: function(e){
						var tempStr='操作成功',
							tempArrData=null;
						if(e.success) {
							if(!newData.organization_id){
								tempArrData=[
									{keyName:'organization_name',val: e.name},
									{keyName:'organization_id',val: e.organization_id},
									{keyName:'organization_logo',val: e.logo}
								];
							}else{
								tempArrData=[
									{keyName:'organization_name',val: name},
									{keyName:'organization_logo',val: $form.find('#basicInfoLogo').attr('src')}
								];
							}
							that.updateCookie.call(that,tempArrData);
							var tempName=Hisihi.substrLongStr(name,9);
							$('#headOrgName').attr('title',name).text(tempName);
							$('#orgLogoAndName').show();
						}else{
							tempStr= e.message;
						}
						$label.text(tempStr).show().delay(1000).hide(0);
					}
				});
			}
 		},

		/*更新logo*/
		updateLogo:function(){
			var that=this;
			if(this.organization_id && this.organization_id!='0')
			{
				Hisihi.getDataAsync({
					type: "post",
					url: this.basicApiUrl + '/updateLogo',
					data: {pic_id: that.$wrapper.find('#myNewPicture').attr('data-lid')},
					org: true,
					callback: function (e) {
						if (e.success) {
							that.fillInNewLogo.call(that);
						} else {
							alert(e.message);
						}
					}
				});
			}else{
				that.fillInNewLogo.call(that);
			}
		},

		/*
		*更新logo
		*/
		fillInNewLogo:function(){
			var $newPic = this.$wrapper.find('#myNewPicture'),
				src = $newPic.attr('src');
			this.$wrapper.find('#basicInfoLogo')
				.add($('#headerLogo'))
				.attr({'src': src, 'data-lid': $newPic.attr('data-lid')});
			this.updateCookie.call(this, [{keyName:'organization_logo',val:src}]);
			this.cancelCrop.call(this, true);
		},

		getAllMyTagsId:function(){
			var tempStr='';
			this.$wrapper.find('#myAdvantage .myAdvantageItem').each(function(){
				tempStr+=$(this).attr('data-id')+'#';
			});
			if(tempStr.length>0){
				tempStr=tempStr.substr(0,tempStr.length-1);
			}
			return tempStr;

	},
		/*重新设置cookie*/
		updateCookie:function(arr){
			for(var i =0;i<arr.length;i++) {
				this.cookie[arr[i].keyName] = arr[i].val;
			}
			$.cookie('hisihi-org',null,{path:"/"});
			$.cookie('hisihi-org',JSON.stringify(this.cookie),{expires:7,path:'/'});
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
				this.$wrapper.find("#ImgModal").hide();
				//清空file之前的内容
				this.$wrapper.find('#userPhotoForm')[0].reset();
			}
			//this.jcrop_api.destroy();  //隐藏裁剪框

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
		 *头像上传成功回调
		 */
		updateOrgLogoCallback:function(file, data) {
		 if (data.success) {
			 var $img=$('#myNewPicture');
			 $img.attr({'src':data.logo.path,'data-lid':data.logo.id});
			 this.cancelCrop(false);
			 //that.initializeCrop($img);
		 } else {
			 //(data.info);
			 alert('头像上传失败');
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