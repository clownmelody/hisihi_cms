/**
 * Created by hisihi on 2016/10/10.
 */
define(['base','async','myPhotoSwipe','lazyloading'],function(Base,Async,PhotoSwipe){

    function Teacher($wrapper,uid,url) {
        this.$wrapper = $wrapper;
        var that = this;
        this.baseUrl = url;
        this.uid=uid;
        var eventName='click',that=this;
        if(this.isLocal){
            //eventName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        }

        //this.perPageSize=10;
        //this.pageIndex=1;
        this.async=true;  //同步加载所有的数据
        this.controlLoadingBox(true);  //是否显示加载等待动画
        window.setTimeout(function(){
            that.initData();
        },100);

        //photoswipe 查看相册大图
        new PhotoSwipe('.picture-ul',{
            bgFilter: true,
        });

    }

    //下载条
    var config={
        downloadBar:{
            show:true,
            pos:1
        }
    };

    Teacher.prototype=new Base(config);
    Teacher.constructor=Teacher;
    var t=Teacher.prototype;

    //获取老师基本信息
    t.loadBasicInfoData=function(callback) {
        var that=this,
            queryPara={
                url:this.baseUrl+'teacher/getTeacherInfo/',
                paraData:{teacher_id:this.uid},
                sCallback:function(result){
                    callback && callback(result);
                },
                eCallback:function(){
                    callback && callback(null);
                },
                type:'get',
                async:this.async
            };
        this.getDataAsync(queryPara);
    };

    //获取老师下学生作品
    t.loadTeacherStudentWork=function(callback){
        var that=this,
        //http://localhost/api.php?s=/teacher/getTeacherStudentWorkList
       queryPara={
            url:this.baseUrl + 'teacher/getTeacherStudentWorkList',
            paraData: {teacher_id:this.uid ,page:1, count:8},
            sCallback: function(result){
                callback && callback(result);
            },
            eCallback:function(){
                callback && callback(null);
            },
          type:'get',
          async:this.async
        };
      this.getDataAsync(queryPara);
    };

    //获取老师下学生就业信息列表
    t.loadStudentEmployee=function(callback){
        var that=this,
        //http://localhost/api.php?s=/teacher/getStudentEmployList
        queryPara={
            url:this.baseUrl + 'teacher/getStudentEmployList',
            paraData: {teacher_id:this.uid},
            sCallback:function(result){
                callback && callback(result);
            },
            eCallback:function(){
                callback && callback(null);
            },
                type:'get',
                async:this.async
                };
            this.getDataAsync(queryPara);
    };

    //发起请求，填充页面信息
    t.initData=function(){
        var that = this;
        Async.parallel({
            basic:function(callback){
                that.loadBasicInfoData(function(result){
                    if(!result){
                        that.showTips('老师不存在');
                        that.controlLoadingBox(false);
                        return;
                    }
                    callback(null,result);
                });
            },
            teacher:function(callback){
                that.loadTeacherStudentWork(function(result){
                    callback(null,result);
                });
            },
            job:function(callback){
                that.loadStudentEmployee(function(result){
                    callback(null,result);
                });
            },
        },function (err, results){
            var val;
            for(var item in results) {
                var fn = null;
                val = results[item]
                switch (item) {
                    case 'basic':
                        fn = that.fillInBasicInfoData;
                        break;
                    case 'teacher':
                        fn= that.fillStudentAlbum;
                        break;
                    case 'job':
                        fn = that.fillStudentEmployee;
                        break;
                    default :
                        break;
                }
                fn && fn.call(that, val);
            }
            $('#wrapper').show();
            that.controlLoadingBox(false);
            $('.lazy-img').picLazyLoad($(window), {
                threshold: 150
            });
        });
    };

    //填充老师详情信息
    t.fillInBasicInfoData=function(result) {
        this.fillTeacherBasicInfo(result),
        this.fillTips(result),
        this.fillNumber(result),
        this.fillTeacherIntroduce(result);
    }

    //填充老师头部基本信息
    t.fillTeacherBasicInfo=function(result){
        var avatar=result.data.avatar,
            name=result.data.name,
            title=result.data.title,
            str ='<div class="info-left">'+
                        '<img src="' + avatar + '"/>' +
                '</div>'+
                        '<div class="info-right">'+
                        '<ul>'+
                        '<li>'+
                        '<span id="name">'+
                           name+
                        '</span>'+
                        '</li>'+
                        '<li>'+
                        '<span id="title">'+
                          title+
                        '</span>'+
                        '</li>'+
                        '</ul>'+
                        '</div>';
        $('.info').html(str);
    };

    //填充标签信息，#分割具体标签内容
    t.fillTips=function(result){
        var str='',
            tip=result.data.tag,//字符串
            a = new Array(),//定义一个数组
            a =tip.split("#");//字符分割
            for (var i=0;i<a.length;i++) {
                str += '<li>'+
                    '<span>'+a[i]+'</span>'+
                    '</li>';
            }
        $('.tag-right ul').html(str);
    };

    //填充数字信息
    t.fillNumber=function(result){
        var num=result.data.student_num,
            age=result.data.teach_age,
            rate=result.data.employment_rate;
            str= '<div class="num-li" id="student">'+
            '<ul>'+
            '<li class="num-img" id="student-img">'+
            '</li>'+
            '<li class="num-title">'+
            '<span>学生人数</span>'+
            '</li>'+
            '<li class="num">'+
            '<span>'+
                num+'人'+
            '</span>'+
            '</li>'+
            '</ul>'+
            '</div>'+
            '<div class="num-li" id="year">'+
                '<ul>'+
                    '<li class="num-img" id="year-img"></li>'+
                    '<li class="num-title"><span>从教年份</span></li>'+
                    '<li class="num">'+
                    '<span>'+
                        age+'年'+
                    '</span>'+
                    '</li>'+
                '</ul>'+
            '</div>'+
            '<div class="num-li" id="job">'+
            '<ul>'+
            '<li class="num-img" id="job-img"></li>'+
            '<li class="num-title"><span>就业率</span></li>'+
            '<li class="num">'+
            '<span>'+
                rate+'%'+
            '</span>'+
            '</li>'+
            '</ul>'+
            '</div>';
        $('.number').html(str);
    };

    //老师简介
    t.fillTeacherIntroduce=function(result){
        var int=result.data.introduce,
            str= '<div class="head">'+
                '<span>简介</span>'+
                '</div>'+
                '<p class="detail">'+
                    int+
                '</p>';
        $('.introduction').html(str);
    };

    //填充学生作品相册
    t.fillStudentAlbum=function(result){
        if(!result||result.count==0){
            return '';
        }
        var strLi='',
            len=result.data.length,
            item;
        for(var i=0;i<len;i++){
            item=result.data[i];
            strLi+='<li>'+
                '<a href="'+item.pic_url+'" data-size="'+item.size[0]+'x'+item.size[1]+'"></a>'+
                //'<a href="'+item.pic_url+'"></a>'+
                '<img src="'+item.pic_url+'@80h_80w_1e">'+
                '</li>';
        }
        strLi+='<div style="clear: both;"></div>';
        var str= '<ul class="picture-ul">'+ strLi+ '</ul>';
        $('.preview-box').html(str);
    };

    //加载学生就业信息
    t.fillStudentEmployee=function(result){
        var str='',
            len=result.data.length;
        for (var i=0;i<len;i++){
             var item=result.data[i];
            str +='<div class="employee-box">'+
                    '<div class="student-left">'+
                        '<img src="' + item.avatar + '"/>' +
                    '</div>'+
                    '<div class="student-right">'+
                        '<ul>'+
                            '<li>'+
                                '<span class="student-name">'+
                                    item.name+
                                '</span>'+
                            '</li>'+
                            '<li>'+
                                '<span class="left">就职公司：</span>'+
                                '<span class="right">'+
                                     item.company+
                                '</span>'+
                            '</li>'+
                            '<li>'+
                                '<span class="left">职位：</span>'+
                                '<span class="right">'+
                                    item.title+
                                '</span>'+
                            '</li>'+
                            '<li>'+
                                '<span class="left">薪资：</span>'+
                                '<span class="right">'+
                                    item.salary+
                                '</span>'+
                            '</li>'+
                        '</ul>'+
                    '</div>'+
                '</div>';
        }
        $('.employee-s').html(str);
    };

    //学生就业信息文字超长部分进行省略
    t.getWold=function(result){

    };

    return Teacher;
});