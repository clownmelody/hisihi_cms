
$(function () {
    getData(118,true);
});

function getData(Uid,Api){ 
    $.ajax({ 
        type: "GET",
        url: "api.php?s=/user/getResumeProfile/uid/" + Uid + "/api/"+Api,
        dataType: "json",
        success: function(data) {
            getDataFu(data.info);
        },    
    });
};

//头像
function getDataFu(Data) {
    $(".user-portrait").append("<img src='"+Data.avatar128+"' alt=''>");
    userInfo(Data);
    extInfo(Data.extinfo);
    UserSpot(Data);
    Experience(Data.experience);
    WorksIMG(Data.works);
}

//详细
function userInfo(DataInfo) {
    var info = $("#userInfo");
    info.find(".user-name").html(DataInfo.nickname);
    info.find(".sex-day").html(SexAge(DataInfo.sex,DataInfo.birthday));
    info.find(".mobile-email").html(MobileEmail(DataInfo.mobile,DataInfo.email));
    info.find(".user-tool").append(userSkills(DataInfo.skills));
}

//ext
function extInfo (DataExt) {
    var College,Major;
    for (var i = DataExt.length - 1; i >= 0; i--) {

        if (DataExt[i].field_name == "college") {
           College = DataExt[i].field_content;
        }else if(DataExt[i].field_name == "major"){
           Major = DataExt[i].field_content;
        }
    };
    $(".education").append(ListExper(College,Major));
}

//UserSpot
function UserSpot (userSpot) {
    var DateSpot = userSpot.lightspot;
    var DateStrengths = userSpot.extinfo;
    for (var i = DateSpot.length - 1; i >= 0; i--) {
        $(".user-spot").append(SpanSpot(DateSpot[i].value));
    };
    for (var i = DateStrengths.length - 1; i >= 0; i--) {
        
        if (DateStrengths[i].field_name == "my_strengths") {
           $(".my-strengths").html(DateStrengths[i].field_content);
        };
    };
}

//Experience
function Experience (DataExper) {
    for (var i = DataExper.length - 1; i >= 0; i--) {
        var namedepar = DataExper[i].company_name+DataExper[i].department;
        $(".experience").append(ListExperFu(namedepar,DataExper[i].start_time,DataExper[i].end_time,DataExper[i].job_content));
    };
}

//Works
function WorksIMG (DataIMG) {
    for (var i = DataIMG.length - 1; i >= 0; i--) {
        $(".works-img").append("<img src='"+DataIMG[i].src+"'alt=''>");
    };
}

//SexAge
function SexAge (sex,age) {
    return userSex(sex)+" "+userAge(age)+"岁";
}

//MobileEmail
function MobileEmail (mobile,email) {
    return mobile+" "+email;
}

function SpanSpot (Spot) {
    return "<span class='"+ColorClass()+"'>"+Spot+"</span>";
}

//ListExper
function ListExperFu (NameDepar,Stime,Etime,Jcontent) {
    var NameDepar = "<li class='user-company'>"+NameDepar+"</li>";
    var Time = "<li class='user-time'>"+Stime+"—"+Etime+"</li>";
    var Major = "<p class='user-major'>"+Jcontent+"</p>";
    return "<li><ul class='user-empiric'>"+NameDepar+Time+"</ul>"+Major+"</li>";
}

//ListExper
function ListExper (NameDepar,Jcontent) {
    var NameDepar = "<li class='user-company'>"+NameDepar+"</li>";
    var Time = "<li class='user-time'>"+Jcontent+"</li>";
    return "<li><ul class='user-empiric'>"+NameDepar+Time+"</ul></li>";
}

//color
function ColorClass () {
    var num = getRandom(4);
    switch(num)
    {
        case 1:
            return "red"; 
            break;
        case 2:
            return "green"; 
            break;
        case 3:
            return "orange"; 
            break;
        case 4:
            return "bule";  
            break;
        default:
            return null;
    }
}

//随机
function getRandom(n){
    return Math.floor(Math.random()*n+1)
}



//性别
function userSex (DataSex) {
    var sex;
    switch(DataSex)
    {
        case "0":
            sex = "未知"; 
            break;
        case "1":
            sex = "男"; 
            break;
        case "2":
            sex = "女"; 
            break;
        default:
            return 0;
    }
    return sex;
}
//年龄
function userAge (DataAge) {
    var timeObject = new Date();
    var userYear = parseInt(DataAge.substr(0,4));
    var wYear = timeObject.getFullYear();
    var sex = wYear-userYear;
    return sex;
}

//技能
function userSkills (DataSkills) {
    for (var i = DataSkills.length - 1; i >= 0; i--) {
        $(".user-tool").append("<img src="+"'img/tool/"+DataSkills[i].value+".png'"+"alt=''>");
    };
    
}