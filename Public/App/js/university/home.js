/**
 * Created by jimmy on 2016/5/10.
 */

define(['base','mysilder','scale'],function(Base,Myslider){
    var University=function(id){
        this.uid = id;
        var eventName='click',that=this;
        this.deviceType = this.operationType();
        this.isLocal=window.location.href.indexOf('hisihi-cms')>=0;
        if(this.deviceType.mobile && this.isLocal){
            //eventName='touchend';
        }
        this.getBasicInfo();

        $(document).on(eventName,'.album-ul li', $.proxy(this,'viewPics'));

        $(document).on(eventName,'.album-name',function(){
            window.location.href='hisihi://university/detailinfo/album?id='+that.uid;
        });

        //关闭相册信息
        $(document).on(eventName,'.view-pics-box', function(){
            event.stopPropagation();
            if(event.target==this){
                $('.modal').removeClass('show');
                $('html,body').removeClass('ovfHidden');

            }
        });
    };

    //下载条
    var config={
        downloadBar:{
            show:true,
            pos:0
        }

    };

    University.prototype=new Base(config);
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
                    that.controlLoadingBox(false);
                    that.showTips.call(that,txt);
                    $('.nodata').show();
                    callback && callback();
                },
            };
        this.getDataAsyncPy(para);
    };

    t.fillInUniversityInfo=function(result){
        var strBasic=this.getBasicIntroduceInfo(result),
            strNums=this.getNumsInfoStr(result),
            strMajor=this.getMajorInfoStr(result),
            strEn=this.getInEnvironmentStr(result),
            strAlbum=this.getAlbumInfo(),
            strUnder=this.getUnderTip(result);

        var str=strBasic+
            strNums+
            strMajor+
            strEn+
            strAlbum+
            strUnder;
        $('body').append(str);
    };

    //电话号码
    //$('#request a').attr('href','tel:'+data.phone_num);

    //简介
    t.getBasicIntroduceInfo=function(result){
        return '<div class="main-item introduce">'+
                    '<div class="head-txt border-bottom">'+
                        '<div class="center-content">简介</div>'+
                    '</div>'+
                    '<div class="introduce-txt">'+
                        '<div class="center-info">'+
                            '<p>'+result.introduction+'</p>'+
                        '</div>'+
                    '</div>'+
                '</div>';
    };

    //指数信息
    t.getNumsInfoStr=function(data){
        var feed=data.tuition_fees,
            scholarship=data.scholarship;
        //feed=this.transformNums(feed);
        //scholarship=this.transformNums(scholarship);

        return '<div class="main-item nums">'+
                    '<ul>'+
                        '<li>'+
                            '<div class="nums-name">推荐指数</div>'+
                            '<div class="nums-val">'+data.sia_recommend_level+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">学生录取率</div>'+
                            '<div  class="nums-val">'+data.sia_student_enrollment_rate+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">申请难度</div>'+
                            '<div  class="nums-val">'+data.difficulty_of_application+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">学费</div>'+
                            '<div  class="nums-val">'+feed+'</div>'+
                        '</li>'+
                        '<div style="clear:both;"></div>'+
                    '</ul>'+
                    '<ul>'+
                        '<li>'+
                            '<div class="nums-name">托福、雅思</div>'+
                            '<div class="nums-val">'+data.toefl+'&nbsp;&nbsp;'+data.ielts+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">本科生比率</div>'+
                            '<div>'+data.proportion_of_undergraduates+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">奖学金</div>'+
                            '<div  class="nums-val">'+scholarship+'</div>'+
                        '</li>'+
                        '<li>'+
                            '<div class="nums-name">申请截止日期</div>'+
                            '<div  class="nums-val">'+data.deadline_for_applications+'</div>'+
                        '</li>'+
                        '<div style="clear:both;"></div>'+
                    '</ul>'+
                '</div>';
    };

    //人数超过+万单位
    t.transformNums=function(num){
        num =Number(num);
        if(num){
            if(num>100000){
                num=num/10000 +'万'
                return num;
            }
        }else{
            num=0;
        }
        return num;
    };

    //专业信息
    t.getMajorInfoStr=function(data){
        var str='',
            unMajors=data.undergraduate_major,
            len=unMajors.length,
            gMajors=data.graduate_major,
            len1=gMajors.length;
        if(len>0 || len1>0){
            str='<div class="main-item majors">';
        }
        if(len>0){
            str+=this.getMajorItemInfoStr('本科专业',unMajors);
        }
        if(len1>0){
            str+=this.getMajorItemInfoStr('硕士专业',gMajors);
        }
        if(len>0 || len1>0){
            str+='</div>';
        }
        return str
    };

    t.getMajorItemInfoStr=function(title,arr){
        var len=arr.length,
            str='<div class="majors-item">'+
                    '<div class="head-txt">'+
                    '<div class="center-content">'+title+'</div>'+
                '</div>'+
                '<ul class="center-content">';
            for(var i=0;i<len;i++){
                var name=arr[i];
                name=this.substrLongStr(name,8);
                str+='<li>'+name+'</li>';
            }
        str+='<div style="clear:both;"></div>'+
                '</ul>'+
                '</div>';
        return str;
    };

    //环境和要求
    t.getInEnvironmentStr=function(result){
        return '<div class="main-item environment"><div class="majors-item">'+
                    '<div class="head-txt">'+
                        '<div class="center-content">申请要求</div>'+
                    '</div>'+
                    '<div class="center-info">'+
                    '<p>'+
                        result.application_requirements+
                    '</p>'+
                    '<div class="center-content">'+
                    '</div>'+
                    '</div>'+
                '</div>'+
                '<div class="head-txt border-bottom">'+
                '</div>'+
                '<div class="majors-item">'+
                    '<div class="head-txt">'+
                    '<div class="center-content">学校环境</div>'+
                    '</div>'+

                    '<div class="center-info">'+
                    '<p>'+
                        result.school_environment+
                    '</p>'+
                    '<div class="center-info">'+
                    '</div>'+
                    '</div>'+
                    '</div>'+
                '</div></div>';
    };

    //大学相册
    t.getAlbumInfo= function () {
        var that=this,
            str='';
        this.getDataAsyncPy({
            url: window.hisihiUrlObj.api_url+'/v1/overseas_study/university/' +this.uid +'/photos',
            paraData: {per_page:8},
            sCallback: function(result){
                str = that.getAlbumStr(result);
            },
            eCallback:function(txt){
                str = '';
            },
            type:'get',
            async:false
        });
        return str;
    }

    t.getAlbumStr=function(result){
        if(!result || result.count==0){
            return '';
        }
        var strLi='',
            len=result.list.length;
        for(var i=0;i<len;i++){
            strLi+='<li>'+
                    '<img src="'+result.list[i].pic_url+'">'+
                '</li>';
        }
        //strLi+=strLi;
        strLi+='<div style="clear: both;"></div>';
        var str='<div class="main-item album">'+
            '<div class="head-txt border-bottom">'+
            '<div class="center-content album-name">相册</div>'+
            '</div>'+
            '<div class="album-info">' +
                '<ul class="album-ul">'+
                    strLi+
                '</ul>'+
            '</div>'+
        '</div>';
        return str;
    };

    t.viewPics=function(e){
        var $target=$(e.currentTarget),
            index=$target.index(),
            arr=[],
            imgArr=[],
            $li =$('.album-ul li'),
            len=$li.length;
        for(var i=0;i<len;i++){
            var url=$li.eq(i).find('img').attr('src');
            imgArr.push(url);
        }
        var arr=this.getItemStr(imgArr);

        var $span=$('.pics-nav span');
        $span.text(index+1+'/'+len);
        $('#filter-img').attr('src',imgArr[0]);

        $('.pic-modal').addClass('show');
        $('html,body').addClass('ovfHidden');

        new Myslider($('.view-pics-box'),arr,{
            autoPlay:false,
            showNav:false,
            index:index,
            changeCallback:function(type,picIndex){
                $span.text((picIndex+1)+'/'+len);
                $('#filter-img').attr('src',imgArr[picIndex]);
            }
        });

        var btnList=document.querySelectorAll('.view-pics-box .show-origin-pic');
        //实例化缩放
        ImagesZoom.init({
            elem: ".view-pics-box",  //容器dom
            btnsList:btnList,  //查看按钮
            initCallback:function(dom){
                $(dom).hide().parent().find('img').hide();
                $('.pics-nav label').hide();
            },
            closeCallback:function(){
                $('.view-pics-box .now img').show();
                $('.pics-nav label').show();
                for(var len=btnList.length,i=0;i<len;i++) {
                    $(btnList[i]).show();
                }
            }
        });
    };

    t.getItemStr=function(data){
        var len=data.length,arr=[];
        for(var i=0;i<len;i++){
            var item=data[i];
            arr.push('<img  src="'+item+'"><div class="show-origin-pic">查看大图</div>');
        }
        return arr;
    };

    //底部功能条（收藏/咨询/预约试听）
    t.getUnderTip=function() {
            //return '<ul class="main-item underTip">' +
            //    '<li id="collection">' +
            //        '<a href="http://www.hisihi.com/download.php">'+
            //            '<div class="button"><div class="img" id="colImg"></div><span>收藏</span><div>' +
            //        '</a>'+
            //    '</li>' +
            //    '<li id="request">' +
            //        '<a href="tel:10001">'+
            //        '<div class="button"><div class="img" id="reqImg"></div><span>客服</span><div>' +
            //        '</a>'+
            //    '</li>' +
            //    '<li id="join-class">' +
            //        '<div class="rightInfo listing">预约试听</div>' +
            //    '</li>' +
            //        '<div style="clear: both;"></div>' +
            //    '</ul>',


            return '<div class="main-item have-class">'+
            '<div class="class-title">我想报考<span id="close">x</span></div>'+
            '<input type="number" class="class-num input" placeholder="电话号码" size="14"></input>'+
            '<input type="text" class="class-name input" placeholder="姓名" size="14"></input>'+
            '<input class="class-qualifications input" placeholder="学历" size="14"></input>'+
            '<div class="class-major input">视觉传达设计</div>'+
            '<span id="warning-tip">留学顾问将在15分钟之内联系您</span>'+
            '<button class="class-button input">提交</button>'+
        '</div>';

    }

    return University;
});