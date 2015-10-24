define(['jquery'],function () {
	var url = window.location.href;
	console.log(url);
    $(".side-item").bind("click", function () {

    	$(".side-item").removeClass("active");
    	$(this).addClass("active");
    });

})