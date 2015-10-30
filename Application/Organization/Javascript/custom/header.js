﻿define(['jquery'],function () {
	fillInHeadBasicInfo();
	function fillInHeadBasicInfo(){
		var sfsaff=$('#orgLogoAndName');
		var cookie = cookie=JSON.parse($.cookie('hisihi-org'));
		orgImgSrc=cookie.organization_logo,
			orgName=cookie.organization_name,
			orgId=cookie.organization_id,
			$target=$('#orgLogoAndName');
		if(!orgId){
			$target.hide();
		}else{
			$target.find('#headerLogo').attr('src',orgImgSrc);
			var tempName=orgName;
			if(orgName.length>15) {
				tempName=orgName.substr(0,14)+'…';
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
					$.cookie('the_cookie', null);
					window.location.href = window.urlObject.ctl + "/Index/home";
				}
			}
		});

	});
});