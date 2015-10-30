define(['jquery'],function () {
	var url = window.location.href.split('/');
	var last = url[url.length - 1];
	var index=null;
	switch (last) {
		case "announcement":
				index=1;
			break;
		case "basicinfo":
				index=2;
			break;
		case "teachers":
			index=3;
			break;
		case "certification":
			index=7;
			break;

		case "studentworks":
			index=5;
			break;
		case "teachcondition":
			index=6;
			break;
		default :
			index=dealWithLessonUrl(url);
			break;

	}

	function dealWithLessonUrl(url) {
		var flag1= $.inArray('video',url)>=0;
		var flag2= $.inArray('addnewlesson',url)>=0;
		var flag3= $.inArray('lessondetailinfo',url)>=0;
		if(flag1 || flag2||flag3){
			return 4;
		}else{
			return 100;
		}
	}
	(function(){
		$(".side-item").eq(index - 1).addClass('active')
			.parent()
			.siblings().find('a').removeClass("active");
	}());
});