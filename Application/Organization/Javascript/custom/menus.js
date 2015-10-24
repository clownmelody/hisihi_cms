define(['jquery'],function () {
	var url = window.location.href.split('/');
	var last = url[url.length - 1];
	if(last == "index"){
		$(".side-item[name='1']").addClass("active");
	}else if(last == "basicinfo"){
		$(".side-item[name='2']").addClass("active");
	}else if(last == "teachers"){
		$(".side-item[name='3']").addClass("active");
	}else if(last == "video"){
		$(".side-item[name='4']").addClass("active");
	}else if(last == "studentworks"){
		$(".side-item[name='5']").addClass("active");
	}else if(last == "teachcondition"){
		$(".side-item[name='6']").addClass("active");
	}else if(last == "certification"){
		$(".side-item[name='7']").addClass("active");
	}else{
		$(".side-item[name='1']").addClass("active");
	};

    $(".side-item").bind("click", function () {
    	$(".side-item").removeClass("active");
    	$(this).addClass("active");
    });

})