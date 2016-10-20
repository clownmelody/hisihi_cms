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
            //eventName='touchend';
            this.baseUrl = this.baseUrl.replace('api.php', 'hisihi-cms/api.php');
        }

        this.async = true;  //同步加载所有的数据
        this.controlLoadingBox(true);  //是否显示加载等待动画
        window.setTimeout(function () {
            that.initData();
        }, 100);


        //点击相册加载更多，调用客户端的方法，弹出相册列表
        $(document).on(eventName, '.right-arrow', $.proxy(this, '.showAllStudentsWorks'));

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
                    //if(!result.success||result.data.org_type==null){
                    //    that.showTips('老师不存在01');
                    //}
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
            var avatar = result.data.avatar,
                name = result.data.name,
                title = result.data.title;
                if (result.data.uid==null){
                return '';
            }else {
                    var  str = '<div class="info-left">' +
                        '<a href="hisihi://user/detailinfo?uid='+result.data.uid+'"><img class="pic-head" src="'+avatar+'"></a>'+
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
        var strL = '<div class="content-box tag-box">'+
            '<div class="tag-left-box">'+
            '<div class="tag-left">'+
            '<div class="tag-img"></div>'+
            '</div>'+
            '</div>'+
            '<div class="tag-right">'+
            '<ul>'+
                str+
            '</ul>'+
            '</div>';
        $('.tag').removeClass('hide');
        $('.tag').html(strL);
    };

    //填充数字信息
    //判断老师信息，30为软件类，31为留学，32为手绘
    //如果是留学机构老师，页面数字文字说明部分修改
    t.fillNumber = function (result) {
        if (!result || !result.data.student_num||!result.data.teach_age) {
            return '';
        }
        var num = result.data.student_num,
            age = result.data.teach_age,
            rate = result.data.employment_rate,
            type = result.data.org_type;
        if (type != 31) {
            var str = '<div class="num-li" id="student">'+
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
                '</li>' +
                '</ul>' +
                '</div>' +
                '<div class="num-li" id="year">' +
                '<ul>' +
                '<li class="num-img" id="year-img"></li>' +
                '<li class="num-title"><span>从教年份</span></li>' +
                '<li class="num">' +
                '<span>' +
                age + '年' +
                '</span>' +
                '</li>' +
                '</ul>' +
                '</div>' +
                '<div class="num-li" id="job">' +
                '<ul>' +
                '<li class="num-img" id="job-img"></li>' +
                '<li class="num-title"><span>就业率</span></li>' +
                '<li class="num">' +
                '<span>' +
                rate + '%' +
                '</span>' +
                '</li>' +
                '</ul>' +
                '</div>';
        }
        else {
            var str = '<div class="num-li" id="student">' +
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
                '</ul>' +
                '</div>' +
                '<div class="num-li" id="year">' +
                '<ul>' +
                '<li class="num-img" id="year-img"></li>' +
                '<li class="num-title"><span>从教年份</span></li>' +
                '<li class="num">' +
                '<span>' +
                age + '年' +
                '</span>' +
                '</li>' +
                '</ul>' +
                '</div>' +
                '<div class="num-li" id="job">' +
                '<ul>' +
                '<li class="num-img" id="job-img"></li>' +
                '<li class="num-title"><span>留学成功率</span></li>' +
                '<li class="num">' +
                '<span>' +
                rate + '%' +
                '</span>' +
                '</li>' +
                '</ul>' +
                '</div>';
        }
        $('.number').html(str);
    };

    //老师简介
    t.fillTeacherIntroduce = function (result) {
        if(!result||result.data.introduce==null){
            return;
        }
        var int = result.data.introduce,
            str = '<div class="head">' +
                '<span>简介</span>' +
                '</div>' +
                '<p class="detail">' +
                int +
                '</p>';
        $('.introduction').html(str);
    };

    //填充学生作品相册
    //老师为留学老师则不显示
    t.fillStudentAlbum = function (result) {
        if (!result.success || result.data == null) {
            return '';
        }
        if (result.data.ory_type==31){
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
        strLi += '<div style="clear: both;"></div>';
        var str = '<ul class="picture-ul">' + strLi + '</ul>';
        var strL = '',
            strL = '<div class="header">' +
                '<span class="pic-title">学生作品</span>' +
                '<div class="right-arrow"><span></span></div>' +
                '</div>' +
                '<div class="preview-box">' +
                str +
                '</div>' +
                '</div>';
        $('.picture').html(strL);
    };

    //调用客户端方法
    //显示所有的学生作品
    t.showAllStudentWorks=function(){
        if (this.isFromApp) {
            if (this.deviceType.android) {
                //如果方法存在
                if (typeof AppFunction !='undefined' &&  typeof AppFunction.showStudentWorkList !='undefined') {
                    AppFunction.showStudentWorkList(); //显示所有的学生作品列表
                }
            } else {
                //如果方法存在
                if (typeof buyRebate != "undefined") {
                    showStudentWorkList();//调用app的方法，得到用户的基体信息
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
            len = result.data.length,
            com = result.data.company;
        if (com!=null){
        for (var i = 0; i < len; i++) {
            var item = result.data[i];
            str += '<div class="employee-box">' +
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
                '<li>' +
                '<span class="left">就职公司：</span>' +
                '<span class="right">' +
                item.company +
                '</span>' +
                '</li>' +
                '<li>' +
                '<span class="left">职位：</span>' +
                '<span class="right">' +
                item.title +
                '</span>' +
                '</li>' +
                '<li>' +
                '<span class="left">薪资：</span>' +
                '<span class="right">' +
                item.salary +
                '</span>' +
                '</li>' +
                '</ul>' +
                '</div>' +
                '</div>';
             }
        }else {
            for (var i = 0; i < len; i++) {
                var item = result.data[i];
                str += '<div class="employee-box">' +
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
                    '<li>' +
                    '<span class="left">国家：</span>' +
                    '<span class="right">' +
                    item.country +
                    '</span>' +
                    '</li>' +
                    '<li>' +
                    '<span class="left">学校：</span>' +
                    '<span class="right">' +
                    item.school +
                    '</span>' +
                    '</li>' +
                    '<li>' +
                    '<span class="left">专业：</span>' +
                    '<span class="right">' +
                    item.major +
                    '</span>' +
                    '</li>' +
                    '</ul>' +
                    '</div>' +
                    '</div>';
            }
            var strL = '<div class="head">' +
                '<span>学生就业信息</span>' +
                '</div>' +
                '<div class="employee-s">' +
                str +
                '</div>';
        }
        $('.employee').html(strL);
    };

    return Teacher;
});