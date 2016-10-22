/**
 * Created by hisihi on 2016/10/10.
 */
define(['base','async','myPhotoSwipe','lazyloading'],function(Base,Async,PhotoSwipe) {

    function Teacher($wrapper, uid, url) {
        this.$wrapper = $wrapper;
        var that = this;
        this.baseUrl = url;
        this.uid = uid;
        var eventName = 'click', that = this;
        if (this.isLocal) {
            eventName='touchend';
            this.baseUrl = this.baseUrl.replace('api.php', 'hisihi-cms/api.php');
        }

        this.async = true;  //同步加载所有的数据
        this.controlLoadingBox(true);  //是否显示加载等待动画
        window.setTimeout(function () {
            that.initData();
        }, 100);

        //点击相册加载更多，调用客户端的方法，弹出相册列表
        $(document).on(eventName, '.right-arrow', $.proxy(this, 'showAllStudentWorks'));

        //photoswipe 查看相册大图
        new PhotoSwipe('.picture-ul', {
            bgFilter: true,
        });
    }

    //下载条
    var config = {
        downloadBar: {
            show: true,
            pos: 1
        }
    };

    Teacher.prototype = new Base(config);
    Teacher.constructor = Teacher;
    var t = Teacher.prototype;

    //获取老师基本信息
    t.loadBasicInfoData = function (callback) {
        var that = this,
            queryPara = {
                url: this.baseUrl + 'teacher/getTeacherInfo/',
                paraData: {teacher_id: this.uid},
                sCallback: function (result) {
                    if(!result.success||result.data.org_type==null){
                        that.showTips('该老师不存在');
                    }
                    callback && callback(result);
                },
                eCallback: function () {
                    callback && callback(null);
                },
                type: 'get',
                async: this.async
            };
        this.getDataAsync(queryPara);
    };

    //获取老师下学生作品
    t.loadTeacherStudentWork = function (callback) {
        var that = this,
        //http://localhost/api.php?s=/teacher/getTeacherStudentWorkList
            queryPara = {
                url: this.baseUrl + 'teacher/getTeacherStudentWorkList',
                paraData: {teacher_id: this.uid, page: 1, count: 8},
                sCallback: function (result) {
                    callback && callback(result);
                },
                eCallback: function () {
                    callback && callback(null);
                },
                type: 'get',
                async: this.async
            };
        this.getDataAsync(queryPara);
    };

    //获取老师下学生就业信息列表
    t.loadStudentEmployee = function (callback) {
        var that = this,
        //http://localhost/api.php?s=/teacher/getStudentEmployList
            queryPara = {
                url: this.baseUrl + 'teacher/getStudentEmployList',
                paraData: {teacher_id: this.uid},
                sCallback: function (result) {
                    callback && callback(result);
                },
                eCallback: function () {
                    callback && callback(null);
                },
                type: 'get',
                async: this.async
            };
        this.getDataAsync(queryPara);
    };

    //同步发起请求，获取全部数据
    t.initData = function () {
        var that = this;
        Async.parallel({
            basic: function (callback) {
                that.loadBasicInfoData(function (result) {
                    if (!result) {
                        that.showTips('老师不存在02');
                        that.controlLoadingBox(false);
                        return;
                    }
                    callback(null, result);
                });
            },
            teacher: function (callback) {
                that.loadTeacherStudentWork(function (result) {
                    callback(null, result);
                });
            },
            job: function (callback) {
                that.loadStudentEmployee(function (result) {
                    callback(null, result);
                });
            },
        }, function (err, results) {
            var val;
            for (var item in results) {
                var fn = null;
                val = results[item]
                switch (item) {
                    case 'basic':
                        fn = that.fillInBasicInfoData;
                        break;
                    case 'teacher':
                        fn = that.fillStudentAlbum;
                        break;
                    case 'job':
                        fn = that.fillStudentEmployee;
                        break;
                    default :
                        break;
                }
                fn && fn.call(that, val);
            }
            $('.wrapper').show();
            that.controlLoadingBox(false);
            $('.lazy-img').picLazyLoad($(window), {
                threshold: 150
            });
        });
    };

    //填充老师详情信息
    t.fillInBasicInfoData = function (result) {
        this.fillTeacherBasicInfo(result),
            this.fillTips(result),
            this.fillNumber(result),
            this.fillTeacherIntroduce(result);
    }

    //填充老师头部基本信息
    t.fillTeacherBasicInfo = function (result) {
        if (!result||result.data.name==null){
            return '';
        }
        else {
            var str='',
                avatar = result.data.avatar,
                name = result.data.name,
                title = result.data.title,
                aStr='';
                //获取不到用户uid则禁止跳转
                if (result.data.uid==''|| result.data.uid!=0){
                    aStr='<a href="hisihi://user/detailinfo?uid=' + result.data.uid + '"><img class="pic-head" src="' + avatar + '"></a>';
                }else{
                    aStr='<img class="pic-head" src="' + avatar + '">';
                }
                str = '<div class="info-left">' +
                        aStr+
                    '</div>' +
                    '<div class="info-right">' +
                    '<ul>' +
                    '<li>' +
                    '<span id="name">' +
                    name +
                    '</span>' +
                    '</li>' +
                    '<li>' +
                    '<span id="title">' +
                    title +
                    '</span>' +
                    '</li>' +
                    '</ul>' +
                    '</div>';

        }
        $('.info').show().html(str);
    };

    //填充标签信息，#分割具体标签内容
    //如果老师优势标签不存在，隐藏该模块
    t.fillTips = function (result) {
        if (!result || !result.data.tag) {
            return '';
        }
        var str = '',
            tip = result.data.tag,//字符串
            a = new Array(),//定义一个数组
            a = tip.split("#");//字符分割
        for (var i = 0; i < a.length; i++) {
            str += '<li>' +
                '<span>' + a[i] + '</span>' +
                '</li>';
        }
        var strL = '<div class="tag-left-box">'+
            '<div class="tag-left">'+
            '<div class="tag-img"></div>'+
            '</div>'+
            '</div>'+
            '<div class="tag-right-box">'+
            '<div class="tag-right">'+
            '<ul>'+
                str+
            '</ul>'
            '</div>';
        $('.tag').removeClass('hide');
        $('.tag').html(strL);
    };

    //填充数字信息
    //判断老师信息，30为软件类，31为留学，32为手绘
    //如果是留学机构老师，页面数字文字说明部分修改
    t.fillNumber = function (result) {
        var num = result.data.student_num,
            age = result.data.teach_age,
            rate = result.data.employment_rate,
            type = result.data.org_type,
            numStr1 = '<div class="num-li" id="student">'+
                '<ul>' +
            '<li class="num-img" id="student-img">' +
            '</li>' +
            '<li class="num-title">' +
            '<span>学生人数</span>' +
            '</li>' +
            '<li class="num">' +
            '<span>' +
            num + '人' +
            '</span>' +
            '</li>'+
            '</ul>' +
            '</div>',
            ageStr1= '<div class="num-li" id="year">' +
                '<ul>' +
                '<li class="num-img" id="year-img"></li>' +
                '<li class="num-title"><span>从教年份</span></li>' +
                '<li class="num">' +
                '<span>' +
                age  +
                '</span>' +
                '</li>' +
                '</ul>'+
                '</div>',
            rateStr1='<div class="num-li" id="job">' +
                '<ul>' +
                '<li class="num-img" id="job-img"></li>' +
                '<li class="num-title"><span>就业率</span></li>' +
                '<li class="num">' +
                '<span>' +
                rate + '%' +
                '</span>' +
                '</li>' +
                '</ul>'+
                '</div>',
            numStr2= '<div class="num-li" id="student">' +
                '<ul>' +
                '<li class="num-img" id="student-img">' +
                '</li>' +
                '<li class="num-title">' +
                '<span>指导人数</span>' +
                '</li>' +
                '<li class="num">' +
                '<span>' +
                num + '人' +
                '</span>' +
                '</li>' +
                '</ul>'+
                '</div>' ,
            ageStr2= '<div class="num-li" id="year">' +
                '<ul>' +
                '<li class="num-img" id="year-img"></li>' +
                '<li class="num-title"><span>从教年份</span></li>' +
                '<li class="num">' +
                '<span>' +
                age  +
                '</span>' +
                '</li>' +
                '</ul>'+
                '</div>',
            rateStr2= '<div class="num-li" id="job">' +
                '<ul>' +
                '<li class="num-img" id="job-img"></li>' +
                '<li class="num-title"><span>留学成功率</span></li>' +
                '<li class="num">' +
                '<span>' +
                rate + '%' +
                '</span>' +
                '</li>' +
                '</ul>'+
                '</div>';
        if(!result||num==''&age==''&rate==''){
            return ' ',
            $('.number-box').addClass('hide');
        };

        //判断字段是否为空，如果为空则不显示
        if(num==''||num==null){
            numStr1='', numStr2='';
        }
        if(age==''||age==null){
            ageStr1='',ageStr2='';
        }
        if(rate==''||rate==null){
            rateStr1='',rateStr2='';
        }
        if (type != 31) {
            var str = '<div class="number">'+ numStr1+ ageStr1+ rateStr1+ '</div>';
        }
        else {
            var str = '<div class="number">'+ numStr2+ ageStr2+ rateStr2+ '</div>';
        }
        $('.number-box').removeClass('hide');
        $('.number-box').html(str);
    };

    //老师简介
    t.fillTeacherIntroduce = function (result) {
        if(!result||result.data.org_type ==''){
            return '';
        }
        var int = result.data.introduce,
            intStr= '<div class="head">' +
                '<span>简介</span>' +
                '</div>' +
                '<p class="detail">' +
                int +
                '</p>';
        if(int==''||int==null){
            intStr='';
        }
         var str = '<div class="introduction">'+ intStr+ '</div>';
        $('.introduction-box').removeClass('hide');
        $('.introduction-box').html(str);
    };

    //填充学生作品相册
    //老师为留学老师则不显示
    t.fillStudentAlbum = function (result) {
        if (!result.success || !result.data) {
            return '';
        }

        var strLi = '',
            len = result.data.length,
            item;
        for (var i = 0; i < len; i++) {
            item = result.data[i];
            var imgH = item.origin_info.height,
                imgW = item.origin_info.width;
            strLi += '<li>' +
                '<a href="' + item.pic_url + '" data-size="' + imgW + 'x' + imgH + '"></a>' +
                '<img src="' + item.pic_url + '@80h_80w_1e">' +
                '</li>';
        }
        //strLi +=
        var strL = '<div class="picture">'+
                '<div class="header">' +
                '<span class="pic-title">学生作品</span>' +
                '<div class="right-arrow"><span></span></div>' +
                '</div>' +
                '<div class="preview-box">' +
                '<ul class="picture-ul">' +
                    strLi +
                '<div style="clear: both;"></div>'+
                '</ul>';
                '</div>' +
                '</div>'
                '</div>';
        $('.picture-box').html(strL);
    };

    //调用客户端方法
    //显示所有的学生作品
    t.showAllStudentWorks=function(){
        if (this.isFromApp) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction !='undefined' &&  typeof AppFunction.showAllStudentWorks !='undefined') {
                    AppFunction.showAllStudentWorks(this.uid); //显示所有的学生作品列表
                }
            } else {
                //如果方法存在
                if (typeof showStudentWorkList != "undefined") {
                    showStudentWorkList();//调用app的方法，得到用户的基体信息sss
                }
            }
        }else{
            this.showTips.call(this,'下载嘿设汇App，查看更多！');
        }
    };

    //加载学生就业信息
    //判断老师信息，30为软件类，31为留学，32为手绘
    //如果是留学机构老师，学生就业信息文字说明部分修改
    t.fillStudentEmployee = function (result) {
        if (!result.success || result.data.length == 0) {
            return '';
        }
        if (result.data)
        var str = '',
            diffStr,
            titleStr='学生就业信息',
            len = result.data.length,
            isOVersea=null;
            for (var i = 0; i < len; i++) {
                var item = result.data[i];
                if(i==0){
                    isOVersea=item.company==''||item.company==null;
                }
                //留学
                if(isOVersea){
                    diffStr=this.getOverseaStudentInfo(item);
                    titleStr='学生留学信息';
                }else{
                    diffStr=this.getNormalStudentInfo(item);
                }
                str += '<li class="employee-box">' +
                            '<div class="student-left">' +
                                '<img src="' + item.avatar + '"/>' +
                            '</div>' +
                            '<div class="student-right">' +
                                '<ul>' +
                                    '<li>' +
                                        '<span class="student-name">' +
                                        item.name +
                                        '</span>' +
                                    '</li>' +
                                    diffStr+
                                '</ul>' +
                            '</div>' +
                        '</li>';

            }
            var strL = '<div class="head">' +
                            '<span>'+titleStr+'</span>' +
                        '</div>' +
                        '<ul class="employee-s">' +
                            str +
                        '</ul>';

        $('.employee').html(strL);
    };


    t.getFilterStudentInfo=function(data,keyArr){
        var obj={},val;
        for(var key in data){
            if($.inArray(key,keyArr)>=0){
                val=data[key];
                if(val){
                    obj[key]=val;
                }
            }
        }
        return obj;
    };

    t.getStudentInfoHtmlStr=function(nameObj,valObj){
        var str='',val;
        for(var key in valObj){
            val=valObj[key];
            if(val){
                str+='<li>'+
                    '<span class="left">'+ nameObj[key]+'：</span>' +
                    '<span class="right">' +val +'</span>'+
                    '</li>';
            }
        }
        return str;
    }

    /*留学学生留学信息*/
    t.getOverseaStudentInfo=function(data){
        var arr=['country','school','major'],
            nameObj={country:'国家',school:'学校',major:'专业'},
            valObj=this.getFilterStudentInfo(data,arr);
         return this.getStudentInfoHtmlStr(nameObj,valObj);
    };

    /*普通学生就业信息*/
    t.getNormalStudentInfo=function(data){
        var arr=['company','title','salary'],
            nameObj={company:'就职公司',title:'职位',salary:'薪资'},
            valObj=this.getFilterStudentInfo(data,arr);
        return this.getStudentInfoHtmlStr(nameObj,valObj);
    };



    return Teacher;
});