define(['jquery','jquerycookie'],function () {
	fillInHeadBasicInfo();
	function fillInHeadBasicInfo(){
		var cookie = cookie=JSON.parse($.cookie('hisihi-org'));
		orgImgSrc=cookie.organization_logo,
			orgName=cookie.organization_name,
			orgId=cookie.organization_id,
			$target=$('#orgLogoAndName');
		if(!orgId){
			$target.hide();
			//$target.find('#headerLogo').attr('src','http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png');

		}else{
			$target.find('#headerLogo').attr('src',orgImgSrc);
			var tempName=orgName;
			if(orgName.length>9) {
				tempName=orgName.substr(0,8)+'…';
			}
			$target.find('#headOrgName').text(tempName).attr('title',orgName);
		}
	}

	/*登出*/
	$('#headerLoginOut').click(function(){
		Hisihi.getDataAsync({
			type: "post",
			url: window.urlObject.apiUrl+'/api.php?s=/Organization/logout',
			data: {},
			org:true,
			callback:function(data) {
				if (data.success) {
					$.cookie('the_cookie', null,{path:"/"});
					window.location.href = window.urlObject.ctl + "/Index/home";
				}else{
					alert(data.message);
				}
			}
		});

	});
});