/**
 * Created by jimmy on 2016/5/10.
 */

define(['base'],function(Base){
    var University=function(id){
        this.uid = id;
        var eventName='click',that=this;
        this.deviceType = this.operationType();
        this.isLocal=window.location.href.indexOf('hisihi-cms')>=0;
        if(this.deviceType.mobile && this.isLocal){
            eventName='touchend';
        }
        this.getBasicInfo();
    };

    University.prototype=new Base();
    University.constructor=University;
    var t=University.prototype;

    t.getBasicInfo=function(callback){
        this.controlLoadingBox(true);
        var that = this,
            para = {
                url: window.hisihiUrlObj.api_url + 'v1/overseas_study/university/'+this.uid,
                type: 'get',
                paraData: null,
                sCallback: function (resutl) {
                    that.controlLoadingBox(false);
                    that.fillInUniversityInfo(resutl);
                    callback && callback(data);
                },
                eCallback: function (data) {
                    var txt=data.txt;
                    if(data.code=404){
                        txt='信息加载失败';
                    }
                    that.showTips.call(that,txt);
                    $('.no-comment-info').hide();
                    this.controlLoadingBox(false);
                    callback && callback();
                },
            };
        this.getDataAsyncPy(para);
    };

    t.fillInUniversityInfo=function(result){
        var str=this.getBasicIntroduceInfo(result)+
            this.fillInNumsInfo(result)+
            this.fillInMajorInfo(result)+
            this.fillInEnvironment(result);
        $('body').html(str);
    };

    //简介
    t.getBasicIntroduceInfo=function(result){
        return '<div class="main-item introduce">'+
                    '<div class="head-txt border-bottom">'+
                        '<div class="center-content">简介</div>'+
                    '</div>'+
                    '<div class="introduce-txt">'+
                        '<div class="center-content">'+
                            '<p>'+result.introduction+'</p>'+
                        '</div>'+
                    '</div>'+
                '</div>';
    };

    //指数信息
    t.fillInNumsInfo=function(data){
        return '<div class="main-item nums">'+
                    '<ul>'+
                        '<li>'+
                            '<div class="nums-name">SIA推荐指数</div>'+
                            '<div class="nums-val">'+data.sia_recommend_level+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">SIA学生录取率</div>'+
                            '<div  class="nums-val">'+data.sia_student_enrollment_rate+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">申请难度</div>'+
                            '<div  class="nums-val">'+data.difficulty_of_application+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">学费</div>'+
                            '<div  class="nums-val">'+data.tuition_fees+'</div>'+
                        '</li>'+
                        '<div style="clear:both;"></div>'+
                    '</ul>'+
                    '<ul>'+
                        '<li>'+
                            '<div class="nums-name">托福、雅思</div>'+
                            '<div class="nums-val">'+data.toefl+' '+data.ielts+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">本科生比率</div>'+
                            '<div>'+data.proportion_of_undergraduates+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">奖学金</div>'+
                            '<div  class="nums-val">'+data.scholarship+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">申请截止日期</div>'+
                            '<div  class="nums-val">'+data.deadline_for_applications+'</div>'+
                        '</li>'+
                        '<div style="clear:both;"></div>'+
                    '</ul>'+
                '</div>';
    };

    //专业信息
    t.fillInMajorInfo=function(data){
        var str='',
            unMajors=data.undergraduate_major,
            len=unMajors.length,
            gMajors=data.graduate_major,
            len1=gMajors.length;
        if(len>0 || len1>0){
            str='<div class="main-item majors">';
        }
        if(len>0){
            str+=this.getMajorInfoStr('本科专业',unMajors);
        }
        if(len1>0){
            str+=this.getMajorInfoStr('硕士专业',gMajors);
        }
        if(len>0 || len1>0){
            str+='</div>';
        }
        return str
    };

    t.getMajorInfoStr=function(title,arr){
        var len=arr.length,
            str='<div class="majors-item">'+
                    '<div class="head-txt">'+
                    '<div class="center-content">'+title+'</div>'+
                '</div>'+
                '<ul class="center-content">';
            for(var i=0;i<len;i++){
                str+='<li>'+arr[i]+'</li>';
            }
        str+='<div style="clear:both;"></div>'+
                '</ul>'+
                '</div>';
        return str;
    };

    //环境和要求
    t.fillInEnvironment=function(result){
        return '<div class="majors-item">'+
                    '<div class="head-txt">'+
                        '<div class="center-content">申请要求</div>'+
                    '</div>'+
                    '<div class="content-txt center-content">'+
                    '<p>'+
                        result.application_requirements+
                    '</p>'+
                    '<div class="center-content">'+
                    '</div>'+
                    '</div>'+
                    '</div>'+
                    '<div class="majors-item">'+
                    '<div class="head-txt">'+
                    '<div class="center-content">学校环境</div>'+
                    '</div>'+
                    '<div class="content-txt center-content">'+
                    '<p>'+
                        result.school_environment+
                    '</p>'+
                    '<div class="center-content">'+
                    '</div>'+
                    '</div>'+
                    '</div>'+
                '</div>';
    };


    /*******************通用功能*********************/

    /*
     *控制加载等待框
     *@para
     * flag - {bool} 默认隐藏
     */
    t.controlLoadingBox=function(flag){
        var $target=$('#loading-data');
        if(flag) {
            $target.addClass('active');
        }else{
            $target.removeClass('active');
        }
    };
    return University;
});