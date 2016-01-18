define(["zepto","common"],function(){function i(i,a){this.$wrapper=i;var t=this;this.oid=a;this.pageIndex=1;this.pageSize=0;this.perPageSize=3;this.controlLoadingPos();this.videoPreviewBox();this.locationMapBox();this.initImgPercent();this.controlCommentInputStyle();this.controlCoverFootStyle();this.extendJqueryForScroll();this.loadBasicInfoData();this.loadTopAnnouncement();this.loadSignUpInfo();this.deviceType=getDeviceType(),eventsName="touchend";if(!this.deviceType.mobile){eventsName="click"}else{this.$wrapper.find(".btn").on("touchstart",function(){})}this.$wrapper.find("#videoPreviewBox img").bind("load",$.proxy(this,"controlPlayBtnStyle"));this.$wrapper.scroll($.proxy(this,"scrollContainer"));var e=[this.loadBasicInfoData,this.loadTopAnnouncement,this.loadSignUpInfo,this.loadMyVideoInfo,this.loadMyTeachersInfo,this.loadMyCompresAsseinfo,this.loadDetailCommentInfo];this.$wrapper.on(eventsName,".loadErrorCon a",function(){var i=$(this).data("index")|0,a=e[i];a&&a.call(t)})}i.prototype={controlLoadingPos:function(){var i=$(".loadingResultTips"),a=$("body"),t=i.width(),e=i.height(),n=a.width(),o=a.height();i.css({top:(o-e)/2,left:(n-t)/2,opacity:"1"})},videoPreviewBox:function(){var i=this.$wrapper.find("#videoPreviewBox"),a=this.$wrapper.width()-30,t=parseInt(a*(9/16)),e=i.find("i"),n=e.height(),o=e.width();this.$wrapper.find("#videoPreviewBox").css("height",t);e.css({top:(t-n)/2,left:(a-o)/2})},locationMapBox:function(){var i=this.$wrapper.find(".mainItemLocation"),a=this.$wrapper.width(),t=parseInt(a*(7/16)),e=i.find("i"),n=e.height(),o=e.width();this.$wrapper.find("#locationMap").css("height",t)},controlPlayBtnStyle:function(){var i=this.$wrapper.find("#videoPreviewBox img"),a=i.width(),t=i.height(),e=i.next(),n=e.height(),o=e.width();e.css({top:(t-n)/2,left:(a-o)/2})},controlCommentInputStyle:function(){var i=this.$wrapper.find("#myComment"),a=this.$wrapper.width()-35;i.css("width",a+"px")},loadData:function(i){if(!i.type){i.type="get"}var a=this;a.controlLoadingTips(1);var t=$.ajax({url:i.url,type:i.type,data:i.paraData,timeOut:10,contentType:"application/json;charset=utf-8",complete:function(e,n){if(n=="success"){var o=e.responseText,s={};if(o){s=JSON.parse(e.responseText)}else{s.status=false}if(s.success){a.controlLoadingTips(0);i.sCallback(JSON.parse(e.responseText))}else{var r=s.message;if(i.eCallback){i.eCallback(r)}a.controlLoadingTips(0)}}else if(n=="timeout"){t.abort();a.controlLoadingTips(0);i.eCallback()}else{a.controlLoadingTips(0);i.eCallback()}}})},loadBasicInfoData:function(){var i=this,a=i.$wrapper.find(".logoAndCertInfo"),t={url:window.urlObject.apiUrl+"appGetBaseInfo",paraData:{organization_id:this.oid},sCallback:$.proxy(this,"fillInBasicInfoData"),eCallback:function(){a.css("opacity",1);a.find(".loadErrorCon").show()}};this.loadData(t)},fillInBasicInfoData:function(i){var a=i.data;var t=a.authenticationInfo[2].status,e=t?"certed":"unCerted",n=a.authenticationInfo[3].status,o=n?"certed":"unCerted";var s=a.logo;if(this.deviceType.android){s=window.urlObject.image+"/orgbasicinfo/blur.jpg"}var r='<div class="head mainContent">'+'<div class="filter">'+'<img class="logoBg myLogo" src="'+s+'" alt="logo"/>'+'<div class="filterUp"></div>'+"</div>"+'<div class="mainInfo">'+'<div class="left">'+'<img id="myLogo" class="myLogo" src="'+a.logo+'" />'+"</div>"+'<div class="right">'+'<div id="orgName">'+a.name+"</div>"+'<div class="peopleInfo">'+'<div class="peopleInfoItem">'+'<div class="valInfo" id="viewedVal">'+a.view_count+"</div>"+'<div class="filedInfo">查看人数</div>'+"</div>"+'<div class="peopleInfoItem">'+'<div class="valInfo" id="teacherdVal">'+a.teachersCount+"</div>"+'<div class="filedInfo">老师</div>'+"</div>"+'<div class="peopleInfoItem">'+'<div class="valInfo" id="fansVal">'+a.followCount+"</div>"+'<div class="filedInfo">粉丝</div>'+"</div>"+'<div class="peopleInfoItem">'+'<div class="valInfo" id="groupsVal">'+a.groupCount+"</div>"+'<div class="filedInfo">群主</div>'+"</div>"+'<div style="clear: both;"></div>'+"</div>"+"</div>"+"</div>"+"</div>"+'<div class="bottom">'+'<div class="cerInfoItem '+e+'">'+"<span>"+'<i class="heiCerIcon spiteBg '+e+'"></i>'+'<span class="cerName '+e+'">嘿设汇认证</span>'+'<div style="clear: both;"></div>'+"</span>"+"</div>"+'<div class="cerInfoItem  '+o+'">'+"<span>"+'<i class="honestCerIcon spiteBg '+o+'"></i>'+'<span class="cerName '+e+'">诚信机构认证</span>'+'<div style="clear: both;"></div>'+"</span>"+"</div>"+"</div>";this.$wrapper.find(".logoAndCertInfo").html(r).css("opacity",1);this.$wrapper.find("#myLogo").setImgBox();this.fillInIntroduceInfo(i)},loadTopAnnouncement:function(){var i=this,a=i.$wrapper.find(".mainItemTopNews");this.loadData({url:window.urlObject.apiUrl+"topPost",paraData:{organization_id:this.oid},sCallback:function(t){a.css("opacity",1);i.fillInTopAnnouncement(t.data)},eCallback:function(i){a.css("opacity",1);a.find(".loadErrorCon").show().find("a").text("获取头条信息失败，点击重新加载").show()}})},fillInTopAnnouncement:function(i){var a="",t;if(!i||i.length==0){a='<li><div class="nonData">暂无头条信息</div></li>'}else{var e=i.length;for(var n=0;n<e;n++){t=i[n];a+="<li>"+'<div class="topNewLogo">头条</div>'+'<div class="title">'+'<a href="'+t.detail_url+'">'+t.title+"</a>"+"</div>"+"</li>"}}this.$wrapper.find(".mainItemTopNews .mainContent").html(a)},loadSignUpInfo:function(){var i=this,a=i.$wrapper.find(".mainItemSignUp");this.loadData({url:window.urlObject.apiUrl+"enrollList",paraData:{organization_id:this.oid,type:"all"},sCallback:function(t){a.css("opacity",1);a.find("#leftSingUpNum").text(t.available_count);i.fillInSignUpInfo(t.data)},eCallback:function(i){a.css("opacity",1);a.find(".loadErrorCon").show().find("a").text("获取报名信息失败，点击重新加载").show()}})},fillInSignUpInfo:function(i){var a="",t,e=!i||i.length==0;if(e){a='<div class="nonData">暂无人员报名</div>'}else{var n=i.length,o=Math.ceil(n/3)*3-n;for(var s=0;s<n;s++){t=i[s];var r=new Date(t.create_time*1e3).format("yyyy-MM-dd");a+="<li>"+'<span class="dot spiteBg"></span>'+"<span>"+t.student_name+"</span>"+"<span>&nbsp;&nbsp;同学于</span>"+"<span>&nbsp;&nbsp;"+r+"</span>"+"<span>&nbsp;&nbsp;成功报名</span>"+"</li>"}for(var s=0;s<o;s++){a+="<li></li>"}}this.$wrapper.find(".mainItemSignUp .signUpConUl").html(a);if(!e&&i.length>3){this.$wrapper.find(".signUpCon").Scroll({line:3,speed:2500,timer:2500,up:"btn1",down:"btn2"})}},fillInIntroduceInfo:function(i){var a=this.$wrapper.find(".mainItemBasicInfo"),t=this.$wrapper.find(".mainItemLocation");a.add(t).css("opacity","1");if(i&&i.data){var e=i.data,n=e.introduce,o=e.advantage,s=e.location,r=e.location_img;if(n){a.find(".introduce").html("<p>"+n+"</p>")}if(o){var l=o.split("#"),d="";for(var c=0;c<l.length;c++){d+="<li>"+l[c]+"</li>"}a.find(".itemContentDetail").html(d)}if(!s)t.find("#myLocation").text(s);if(r){t.find(".locationMap img").attr("src",r)}else{t.find(".noDataInHeader").html("&nbsp;&nbsp;&nbsp;&nbsp;地址信息暂无")}}},loadMyTeachersInfo:function(i){var a=this,t=a.$wrapper.find(".mainItemTeacherPower");this.loadData({url:window.urlObject.apiUrl+"appGetTeacherList",paraData:{organization_id:this.oid},sCallback:function(e){t.css("opacity",1);a.fillMyTeachersInfo(e.teacherList);i&&i()},eCallback:function(a){t.css("opacity",1);t.find(".loadErrorCon").show().find("a").text("获得教师信息失败，点击重新加载").show();i&&i()}})},fillMyTeachersInfo:function(i){var a="",t;if(!i||i.length==0){a='<div class="nonData">暂无老师</div>'}else{var e=i.length,n=e%2==0,o="border";for(var s=0;s<e;s++){if(n&&s>=e-2){o="unBorder"}if(!n&&s>=e-1){o="unBorder"}t=i[s].info;a+='<li class="'+o+'">'+'<div class="leftPic">'+'<img src="'+t.avatar128+'"/>'+"</div>"+'<div class="rightUserInfo">'+'<div class="name">'+t.nickname+"</div>"+'<div class="desc">'+t.institution.substrLongStr(12)+"</div>"+"</div>"+"</li>"}}this.$wrapper.find(".mainItemTeacherPower .teacherPowerDetail").prepend(a)},loadMyVideoInfo:function(i){var a=this,t=a.$wrapper.find(".videoPreview");this.loadData({url:window.urlObject.apiUrl+"getPropagandaVideo",paraData:{organization_id:this.oid},sCallback:function(t){if(t.success){var e=t.data.video_img;if(e){a.$wrapper.find("#videoPreview").attr("src",t.data.video_img)}else{a.$wrapper.find(".videoCon .itemHeader span").text("视频信息暂无")}i()}else{a.$wrapper.find(".noDataInHeader").text("视频信息暂无")}},eCallback:function(a){t.css("opacity",1);t.find(".loadErrorCon").show().find("a").text("获取视频信息失败，，点击重新加载").show();i()}})},loadMyCompresAsseinfo:function(i){var a=this,t=a.$wrapper.find(".mainItemCompresAsse");this.loadData({url:window.urlObject.apiUrl+"fractionalStatistics",paraData:{organization_id:this.oid},sCallback:function(e){t.css("opacity",1);a.fillMyCompresAsseInfo(e);i&&i()},eCallback:function(a){t.css("opacity",1);t.find(".loadErrorCon:eq(0)").show().find("a").text("获取评价信息失败，点击重新加载").show();i&&i()}})},fillMyCompresAsseInfo:function(i){var a=i.data;if(!a||a.length==0){return}var t="",e=this,n,o=this.$wrapper.find(".mainItemCompresAsse"),s=o.find(".basicHeader"),r=o.find(".assessmentDetail li");var l=this.getStarInfoByScore(i.comprehensiveScore);s.find("#myAssessment").text(i.comprehensiveScore);s.find("#starsConForCompress").prepend(l);for(var d=0;d<a.length;d++){n=a[d];r.each(function(){var i=$(this),a=e.getColorBlockInfoByScore(n.score);if(i.find(".title").text()==n.value){i.find(".score").text(n.score);i.find(".fillIn").addClass(a.cName).css("width",a.width+"%").next().css("width",100-a.width+"%");return false}})}},loadDetailCommentInfo:function(i,a){var t=this,e=t.$wrapper.find(".studentCommentCon");this.loadData({url:window.urlObject.apiUrl+"commentList",paraData:{organization_id:this.oid,page:i,count:t.perPageSize},sCallback:function(i){t.pageSize=Math.ceil((i.totalCount|0)/t.perPageSize);t.$wrapper.find("#commentNum").text(i.totalCount);t.fillDetailCommentInfo(i);a&&a.call(t)},eCallback:function(i){e.find(".loadErrorCon:eq(1)").show().find("a").text("获取评论信息失败，点击重新加载").show();a&&a.call(t)}})},fillDetailCommentInfo:function(i){var a=i.data,t="";if(!a||a.length==0){t='<li><div class="nonData">暂无评论</div></li>';this.$wrapper.find(".studentCommentDetail li").remove()}else{var e=a.length,n,o,s;for(var r=0;r<e;r++){n=a[r];o=n.userInfo;s=this.getDiffTime(new Date(n.create_time*1e3));t+="<li>"+'<div class="imgCon">'+'<div><img src="'+o.avatar128+'"/></div>'+"</div>"+'<div class="commentCon">'+'<div class="commentHead">'+'<span class="commentNickname">'+o.nickname+"</span>"+'<span class="rightItem starsCon">'+this.getStarInfoByScore(n.comprehensive_score|0)+'<div style="clear: both;"></div>'+"</span>"+"</div>"+'<div class="content">'+n.comment+"</div>"+'<div class="publicTime">发表于'+s+"</div>"+"</div>"+"</li>"}}this.$wrapper.find(".studentCommentDetail").append(t)},controlLoadingTips:function(i){var a=$("#loadingTip"),t=a.find(".loadingImg");if(i==1){a.css("z-index",1);t.addClass("active")}else{a.css("z-index",-1);t.removeClass("active")}},scrollContainer:function(i){var a=i.currentTarget,t=a.scrollHeight-$(a).height(),e=$(a).scrollTop(),n=[300,550];var o=this.$wrapper.find(".mainItemTeacherPower"),s=this.$wrapper.find(".mainItemCompresAsse");if(e>=n[0]&&e<n[1]&&o.attr("data-loading")=="false"&&o.attr("data-loaded")=="false"){var r=o.attr("data-loaded");o.attr("data-loading","true");if(r=="false"){this.loadMyTeachersInfo(function(){o.attr({"data-loaded":"true","data-loading":"false"})});this.loadMyVideoInfo(function(){o.prev().find(".videoCon").attr({"data-loaded":"true","data-loading":"false"})})}return}if(e>=n[1]&&s.attr("data-loading")=="false"&&s.attr("data-loaded")=="false"){var r=s.attr("data-loaded");s.attr("data-loading","true");if("false"==r){this.loadMyCompresAsseinfo(function(){s.attr({"data-loaded":"true","data-loading":"false"})});this.loadDetailCommentInfo(this.pageIndex,function(){s.attr({"data-loaded":"true","data-loading":"false"});this.pageIndex++})}return}},initImgPercent:function(){$.fn.setImgBox=function(){if(this.length==0){return}var i=this,a=new Image;a.src=this[0].src;a.onload=function(){var t=a.height,e=a.width,n=i.css("max-height"),o=i.css("max-width");if(!n||n=="none"){n=i.parent().height()}else{n=n.replace("px","")}if(!o||o=="none"){o=i.parent().width()}else{o=o.replace("px","")}var s=t>n;var r=e>o;var l=1;if(s||r){var d=n/t;var c=o/e;if(d<c){t=n;e=e*d;l=d}else{e=o;t=t*c;l=c}}i.css({width:e+"px",height:t+"px","margin-top":(i.parent().height()-t)/2+"px"}).attr("data-radio",l)};return this}},getStarInfoByScore:function(i){if(i.toString().indexOf(".")>0){i=this.myRoundNumber(i)}var a="",t=Math.floor(i),e=Math.ceil(i),n=e==t?0:1,o=5-e;for(var s=0;s<t;s++){a+='<i class="allStar spiteBgOrigin"></i>'}if(n==1){a+='<i class="halfStar spiteBgOrigin"></i>'}for(var s=0;s<o;s++){a+='<i class="emptyStar spiteBgOrigin"></i>'}return a},myRoundNumber:function(i){i=i.toFixed(1);var a=i.split("."),t=a[0],e=a[1];if(e!=0){var n=e<=2,o=e>=7;if(n){return t|0}else if(o){return t|0+1}else{return parseInt(t)+.5}}},getColorBlockInfoByScore:function(i){var a=[{min:0,max:2,cName:"greenFillIn"},{min:2,max:4,cName:"yellowFillIn"},{min:4,max:5.000000001,cName:"redFillIn"}];var t=$.grep(a,function(a,t){return i>=a.min&&i<a.max})[0];return{cName:t.cName,width:Math.ceil(i/5*100)}},controlCoverFootStyle:function(){var i=$("#downloadCon"),a=i.find("a"),t=a.width(),e=t*.4,n=i.width(),o=n*102/750;i.css({height:o+"px",left:($("body").width()-n)/2});this.$wrapper.css("bottom",o+"px");var s="16px";if(n<375){s="14px"}a.css({top:(o-e)/2,height:e+"px","line-height":e+"px","font-size":s})},getDiffTime:function(i){if(i){var a=1e3*60;var t=a*60;var e=t*24;var n=new Date-i;var o="";if(n<0){return o}var s=n/(7*e);var r=n/e;var l=n/t;var d=n/a;if(s>=1){o=i.getFullYear()+"."+(i.getMonth()+1)+"."+i.getDate();return o}else if(r>=1){o=parseInt(r)+"天前";return o}else if(l>=1){o=parseInt(l)+"小时前";return o}else if(d>=1){o=parseInt(d)+"分钟前";return o}else{o="刚刚";return o}}return""},extendJqueryForScroll:function(){var i=this;$.extend($.fn,{Scroll:function(a,t){if(!a)var a={};var e;var n=this.eq(0).find("ul"),o=n.find("li"),s=o.eq(0).height(),r=a.line?parseInt(a.line,10):parseInt(this.height()/s,10),l=a.speed?parseInt(a.speed,10):500,d=a.timer;if(r==0)r=1;var c=0-r*s;var f=function(){var a={"margin-top":c};if(i.deviceType.android){a={"margin-top":c,"-webkit-transform":"translate3d(0,0,0)","-moz-transform":"translate3d(0,0,0)"}}n.animate(a,500,"ease-out",function(){for(var i=1;i<=r;i++){n.find("li").eq(0).appendTo(n)}n.css({marginTop:0})})};var p=function(){if(d)e=window.setInterval(f,d)};p()}})}};return i});