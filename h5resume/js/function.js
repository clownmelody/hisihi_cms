$(function () {
    var uid = $('#uid').val();
    var width=$(document).width();
    $('body').css('width',$(document).width());
    getData(uid,true);
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
function  getDataFu(Data) {
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
    info.find(".sex-day").html(SexAge(DataInfo.sex,DataInfo.birthday)+ePosition(DataInfo.extinfo));
    info.find(".mobile-email").html(MobileEmail(DataInfo.mobile,DataInfo.email));
    info.find(".user-tool").append(userSkills(DataInfo.skills));
}

//
function Judge(data){
    if (data === undefined || data == null) {
        return " ";
    }else{
        return data;
    }
}

//ext
function extInfo (DataExt) {
    var College,Major;
    for (var i = DataExt.length - 1; i >= 0; i--) {

        if (DataExt[i].field_name == "college") {
           College = DataExt[i].field_content;
        }else if(DataExt[i].field_name == "major"){
           Major = DataExt[i].field_content;
        }else{
            College = "";
            Major = "";
        }
    };
    $(".education").append(ListExper(College,Major));
}

//UserSpot
function UserSpot (userSpot) {
    var DateSpot = userSpot.lightspot;
    var DateStrengths = userSpot.extinfo;
    var num = 0;
    if (DateSpot === undefined || DateSpot == null) {
        for (var i = DateStrengths.length - 1; i >= 0; i--) {
            
            if (DateStrengths[i].field_name == "my_strengths") {
               $(".my-strengths").html(DateStrengths[i].field_content);
            }

        };
    }else{
        for (var i = DateSpot.length - 1; i >= 0; i--) {
            num = NumSpot(DateSpot[i].value.length,num);
            $(".user-spot").append(SpanSpot(DateSpot[i].value,num));
        };
        for (var i = DateStrengths.length - 1; i >= 0; i--) {
            
            if (DateStrengths[i].field_name == "my_strengths") {
               $(".my-strengths").html(DateStrengths[i].field_content);
            }
        };
    };
}

//NumSpot
function NumSpot(data,num){
    var dataNum = data + num;
    if (dataNum > 8) {
        return 0;
    }else{
        return dataNum;
    }
}

//Experience
function Experience (DataExper) {
    if (DataExper === undefined || DataExper == null )  {
        return "";
    }else{
        for (var i = DataExper.length - 1; i >= 0; i--) {
            var namedepar = Judge(DataExper[i].company_name)+Judge(DataExper[i].department);
            $(".experience").append(ListExperFu(namedepar,Judge(DataExper[i].start_time),Judge(DataExper[i].end_time),Judge(DataExper[i].job_content)));
        };
    }
}

//Works
function WorksIMG (DataIMG) {
    if (DataIMG === undefined || DataIMG == null ) {
        return "";
    }else{
        for (var i = DataIMG.length - 1; i >= 0; i--) {
            $(".works-img").append("<img src='"+DataIMG[i].src+"'alt=''><br/>");
        };
    }
}

//SexAge
function SexAge (sex,age) {
    return userSex(Judge(sex))+" "+userAge(age);
}

//MobileEmail
function MobileEmail (mobile,email) {
    return Judge(mobile)+" "+Judge(email);
}

function SpanSpot (Spot,num) {
    if (num == 0) {
        return "<span class='"+ColorClass()+"' style='white-space: nowrap; display: inline;vertical-align: baseline; line-height: 3em;'>"+Spot+"</span></br>";
    }else{
        return "<span class='"+ColorClass()+"' style='white-space: nowrap; display: inline;vertical-align: baseline;  line-height: 3em;'>"+Spot+"</span>";
    }
    
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
    if(NameDepar && Jcontent) {
        if ((NameDepar.length + Jcontent.length) > 20) {
            var NameDepar = "<li class='user-company'style='width: 100%; text-align: center;margin-bottom: 1em;'>" + NameDepar + "</li>";
            var Time = "<li class='user-time' style='width: 100%; text-align: center;'>" + Jcontent + "</li>";
        } else {
            var NameDepar = "<li class='user-company' style='width: 100%;'>" + NameDepar;
            var Time = "<span class='user-time' style='float: right;color: #999;'>" + Jcontent + "</span></li>";
        }
        return "<li><ul class='user-empiric'>"+NameDepar+Time+"</ul></li>";
    }
    else{
        return "<li><ul class='user-empiric'></ul></li>";
    }
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

//期望职位
function ePosition (Dataposi) {
    for (var i = Dataposi.length - 1; i >= 0; i--) {
        
        if (Dataposi[i].field_name == "expected_position") {
           return " "+Dataposi[i].field_content;
        };
    };
    return "";
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
            return "";
    }
    return sex;
}
//年龄
function userAge (DataAge) {
    if (DataAge === undefined || DataAge == null ) {
        return "";
    }else{
        var timeObject = new Date();
        var userYear = parseInt(DataAge.substr(0,4));
        var wYear = timeObject.getFullYear();
        var sex = wYear-userYear;
        return sex+"岁";
    };
}

//技能
function userSkills (DataSkills) {

    if (DataSkills === undefined || DataSkills == null ) {
        return "";
    }else{
       for (var i = DataSkills.length - 1; i >= 0; i--) {
            $(".user-tool").append("<img src="+"'h5resume/img/tool/"+DataSkills[i].value+".png'"+">");
        };
    }
    
    
}