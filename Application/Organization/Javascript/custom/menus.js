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
		case "video":
		case "addnewlesson":
			index=4;
			break;
		case "studentworks":
			index=5;
			break;
		case "teachcondition":
			index=6;
			break;
		default :
			index=7;
			break;

	}
	(function(){
		$(".side-item").eq(index - 1).addClass('active')
			.parent()
			.siblings().find('a').removeClass("active");
	}());
});