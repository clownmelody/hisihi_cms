/**
 * Created by hisihi on 2016/10/10.
 */
define(['base','async','myPhotoSwipe','deduction','lazyloading'],function(Base,Async,MyPhotoSwipe,Deduction){

    function Teacher($wrapper,oid,url) {
        this.$wrapper = $wrapper;
        var that = this;
        this.baseUrl = url;
        this.oid=oid;
        var eventName='click',that=this;
        if(this.isLocal){
            //eventName='touchend';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        }


        this.perPageSize=10;
        this.pageIndex=1;
        this.async=true;  //ͬ���������е�����
        this.showGuaranteeBorder=false; //  ���Ƿ���ʾ���������ı߿�
        this.controlLoadingBox(true);
        window.setTimeout(function(){
            that.initData();
        },100);

        $(document).on('input','#user-name, #phone-num', $.proxy(this,'singInBtnControl'));

        //photoswipe   //ѧ����Ʒ��Ϣ�鿴  ��ᡢ��Ƶ��Ϣ�鿴
        new MyPhotoSwipe('.works-preview-box',{
            bgFilter:true,
        });
    }

    //������
    var config={
        downloadBar:{
            show:true,
            pos:0
            //0��1������������������ʾ����������ʾ
        }
    };

    Teacher.prototype=new Base(config);
    Teacher.constructor=Teacher;
    var t=Teacher.prototype;

    /*�������ݣ����Ƕ�ף�ͬ������*/
    t.initData=function() {
        var that = this;
        Async.parallel({
            basic: function (callback) {
                that.loadBasicInfoData(function (result) {
                    if(!result){
                        that.showTips('��ʦ������');
                        that.controlLoadingBox(false);
                        return;
                    }
                    callback(null, result);
                });
            },
            announcement: function (callback) {
                that.loadTopAnnouncement(function (result) {
                    callback(null, result);
                });
            },
            signUp:function(callback) {
                that.loadSignUpInfo(function(result){
                    callback(null,result)
                });
            },
            //�γ�
            course:function(callback){
                that.loadTeachingCourse(function(result){
                    callback(null,result);
                });
            },
            pics:function(callback){
                that.loadPics(function(result){
                    callback(null,result)
                });
            },
            teacher:function(callback) {
                that.loadMyTeachersInfo(function(result){
                    callback(null,result)
                });
            },
        }, function (err, results) {
            var val;
            for(var item in results){
                var fn=null;
                val=results[item]
                switch (item){
                    case 'basic':
                        fn=that.fillInBasicInfoData;
                        break;
                    case 'announcement':
                        fn=that.fillInTopAnnouncement;
                        break;
                    case 'signUp':
                        fn=that.fillInSignUpInfo;
                        break;
                    case 'coupon':
                        fn=that.fillInCouponInfo;
                        break;
                    case 'course':
                        fn=that.fillInCourseInfo;
                        break;
                    case 'pics':
                        that.fillInPicsAndVideo([results['video']],val);
                        break;
                    case 'teacher':
                        fn = that.fillMyTeachersInfo;  /*�����ʦ��Ϣ*/
                        break;
                    default :
                        break;
                }
                fn && fn.call(that,val);
            }
            $('#wrapper,#footer').show();
            that.controlLoadingBox(false);
            //that.initVideoPlayer();
            $('.lazy-img').picLazyLoad($(window),{
                threshold:150
            });
        });
    };


    /*���ػ�����Ϣ*/
    t.loadBasicInfoData=function(callback) {
        var that=this,
            $target=that.$wrapper.find('.logoAndCertInfo'),
            queryPara={
                url:this.baseUrl+'appGetBaseInfo',
                paraData:{teacher_id:this.oid,version:3.02},
                sCallback:function(restult){
                    callback && callback(restult);
                },
                eCallback:function(){
                    //�绰����
                    $('.contact a').attr('href','javacript:void(0)').css('opacity','0.3');
                    callback && callback(null);
                },
                type:'get',
                async:this.async

            };
        this.getDataAsync(queryPara);
    };



    /*��ʾ������Ϣ*/
    t.fillInBasicInfoData=function(result) {
        $('.logo-box').show();
        var data = result.data, url = data.logo;
        if (this.deviceType.android) {
            url = window.hisihiUrlObj.image + '/orgbasicinfo/blur.jpg';
        }
        var name = data.name;
        if (name.length > 10) {
            name = name.substr(0, 9) + ' ��';
        }
        var $box = $('.logo-box');
        //logo
        $box.find('#filter-logo').attr('src', url);
        $box.find('#logo').attr('src', data.logo);

        // ��Ƶ�����ơ���֤
        $box.find('.name-main-box .org-name').text(data.name);
        this.setCertInfo(data.authenticationInfo);

        // ��˿�͹ۿ�����
        var $people = $box.find('.user-number-box label')
        $people.eq(0).text(this.translationCount(data.view_count));
        $people.eq(1).text(this.translationCount(data.followCount));

        //������Ϣ
        if (data.introduce) {
            $('.my-introduce').text(data.introduce).parent().show();
        }
        if (data.location) {
            $('.my-location').text(data.location).parent().show();
        }
        //�绰����
        $('.contact a').attr('href', 'tel:' + data.phone_num);


        this.fillAppointmentInfo(result);

        // �ֿ�ȯ ��ǩ
        this.fillInDeductionTags(result);

    };


    //�ֿ�ȯ��Ϣ
    t.loadCouponInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + '/v1/org/41/teaching_course',
            paraData: {organization_id: this.oid, version: 2.95},
            sCallback: function(result){
                callback && callback(result);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    t.fillInCouponInfo=function(data){
        if(!data || data.list.length==0){
            return;
        }
        var list=data.list,
            len=list.length,
            str='',coupon;

        for(var i=0;i<len;i++){
            coupon=list[i];
            if(coupon.is_out_of_date){
                continue;
            }
            str+='<li>'+
                '<div class="coupon-header">��'+coupon.money+'</div>'+
                '<div class="coupon-bottom">'+
                '<p>�����ڣ�'+coupon.course_name+'</p>'+
                '</div>'+
                '</li>'
        }
        $('.coupon-box').show().find('ul').html(str);
    };

    /*�γ��б�*/
    t.fillInCourseInfo=function(data){
        if(!data || !data.courses || data.courses.length==0){
            return;
        }
        var list = data.courses,
            len = list.length,
            str = '',
            rightStr = '',
            count= 0,
            item;



        for(var i=0;i<len;i++){
            item=list[i];
            rightStr=this.getRightStrAndMarginInfo(item.rebate_info);  //�ֿ�ȯ��Ϣ

            count++;
            var money=this.judgeInfoNullInfo(item.price);
            if(money!=''){
                money='��'+money;
            }else{
                money='<label class="noprice">���ޱ���</label>';
            }
            str+='<li data-course-id="'+item.id+'">'+
                    //'<a href="hisihi://techcourse/detailinfo?id='+item.id+'">' +
                '<a href="' + this.baseUrl + 'teaching_course_main_page_v3_02/course_id/' + item.id + '">'+
                '<div class="item-main">'+
                '<div class="left">'+
                '<div class="img-box">'+
                '<img class="lazy-img" data-original="'+item.cover_pic+'@66h_66w_2e">'+
                '</div>'+
                '</div>'+
                '<div class="middle">'+
                '<p class="title-info hasCoupon">'+item.course_name+'</p>'+
                '<p class="money-info">'+money+'</p>'+
                '</div>'+
                rightStr+
                '</div>'+
                '</a>'+
                '</li>';
        }
        if(str) {
            $('.deduction-detail').find('ul').html(str);
            $('.coupon').show();
            var diff=count- 2,
                height=90 + 15,
                $detail=$('.deduction-detail');
            if(diff>0){
                height=2 * 90 + 35;
                var className='slow';
                if(diff>8){
                    className='fast';
                }
                if(diff>3 && diff<=8){
                    className='normal';
                }
                $('.show-more-course').show().find('span').eq(0).text('�鿴����'+ diff +'���Żݿγ�');
            }
            if(diff==0){
                height=2 * 90 + 15;
            }
            $detail.addClass(className).css('height',height).attr({'data-height':height,'data-diff':diff});
        }
    };

    /*�ж��ֶ���Ϣ�Ƿ�Ϊ��*/
    t.judgeInfoNullInfo=function(info){
        var str=info;
        if(typeof info=='string'){
            str=str.trim();
        }
        if(!info || info==''||info==0){
            str='';
        }
        return str;
    };


    /*�õ��Ż�ȯ�ұߵ���Ϣ*/
    t.getRightStrAndMarginInfo=function(deduction){
        var str='';
        if(deduction) {
            var rValue=this.translationCount(deduction.rebate_value),
                val=this.translationCount(deduction.value);
            str ='<div class="right">' +
                '<div class="right-main">'+
                '<div class="money">��' + val +'</div>' +
                '<div class="deduction-money">�֣�'+ rValue +'</div>' +
                '</div>' +
                '</div>';
        }
        return str;
    };

    /*����ͷ����Ϣ*/
    t.loadTopAnnouncement=function(callback){
        var that=this;
        this.getDataAsyncPy({
            //http://dev.hisihi.com/api.php?s=/teacher/teacherv3_1/uid/7"
            //http://localhost/api.php?s=/teacher/getTeacherInfo
            url:window.hisihiUrlObj.apiUrlPy+'v1/org/' +this.oid +'/news',
            sCallback: function(result){
                callback && callback(result);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async

        });
    };

    /*���ͷ����Ϣ*/
    t.fillInTopAnnouncement=function(result){
        if(!result || !result.news || result.news.length==0){
            return;
        }
        var str='',
            item,
            data=result.news,
            $target=$('.news-box');
        $target.show();
        var len = data.length;
        for (var i = 0; i < len; i++) {
            if(i<2) {
                item = data[i];
                str += '<a href="' + item.url + '"><p>' + item.title + '</p></a>';
            }
        }
        $target.find('.right-item div').html(str);
    };

    //����
    t.scrollSingInInfo=function(){
        var $target=$('.sing-up-detail-box'),
            $item=$target.find('p'),
            h=40,
            len=$item.length,
            ty= -h+'px';
        var style={
            'margin-top':ty,
            '-webkit-transform':'translate3d(0,0,0)',
            '-moz-transform':'translate3d(0,0,0)',
            'opacity':'0.7'
        };
        window.setInterval(function(){
            $target.animate(
                style,
                500,'ease-out',
                function(){
                    $target.find('p').eq(0).appendTo($target);
                    $target.css({marginTop:0,'opacity':'1'});
                }
            );

        },2500);
    };

    /*���ر�����Ϣ*/
    t.loadSignUpInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url:this.baseUrl + 'enrollList',
            paraData: {organization_id: this.oid,type:'all'},
            sCallback: function(result){
                callback && callback(result.data);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    /*��䱨����Ϣ*/
    t.fillInSignUpInfo=function(data){
        var str='',item,
            flag=!data || data.length==0;

        //û���˱���
        if(flag) {
            return;
        }
        var len = data.length;
        for (var i = 0; i < len; i++) {
            item = data[i];
            var time = new Date(item.create_time * 1000).format('yyyy-MM-dd');
            str += '<p><span>' + item.student_name + '</span>ͬѧ �� ' + time + ' �ɹ�����</p>';
        }

        $('.sing-up-main').show().find('.sing-up-detail-box').html(str);

        //��ʾ�����߿�
        if(this.showGuaranteeBorder){
            $('.sing-up-guarantee').addClass('border');
        }

        // �����¼��������1������ʹ�ù�����ʾ�ķ�ʽ
        if (data.length > 1) {
            this.scrollSingInInfo();
        }
    };

    /*��õֿ�ȯʹ��������Ϣ*/
    t.fillInDeductionTags=function(result){
        if(!result || !result.data){
            return;2
        }
        var tipsArr=result.data.teaching_course_tag_list;
        if(tipsArr.length>0) {
            var $target=$('.deduction-tip').show();
            new Deduction(tipsArr, $target);
        }

    };

    /*�γ���Ϣ*/
    t.loadTeachingCourse=function(callback){1
        var that=this;
        this.getDataAsyncPy({
            url: window.hisihiUrlObj.apiUrlPy+'/v1/org/'+this.oid+'/teaching_course',
            paraData: {
                page:1,
                per_page:100000,
                except_id:''
            },
            sCallback: function(result){
                callback && callback(result);
            },
            eCallback:function(){
                callback && callback(null);
            },
            type:'get',
            beforeSend:function(xhr){
                xhr.setRequestHeader('version','3.02');  //����ͷ��Ϣ
            }
        });
    },

        /*������Ƶ*/
        t.loadVideo=function(callback){
            var that=this;
            this.getDataAsync({
                url:this.baseUrl + 'getPropagandaVideo',
                paraData: {organization_id: this.oid,count:8},
                sCallback: function(result){
                    callback && callback([result.data]);
                },
                eCallback:function(txt){
                    callback && callback(null);
                },
                type:'get',
                async:this.async
            });
        };

    /*�������*/
    t.loadPics=function(callback){
        var that=this;
        this.getDataAsync({
            url:this.baseUrl + 'appGetOrganizationEnvironment',
            paraData: {organization_id: this.oid,count:8},
            sCallback: function(result){
                callback && callback(result);
            },
            eCallback:function(txt){
                callback && callback(null);
            },
            type:'get',
            async:this.async
        });
    };

    t.fillInPicsAndVideo=function(data1,data2){
        var str = this.getVideoStr(data1);  /*��䱨����Ϣ*/
        var newStr = this.getPicsStr(data2.data,true),
            allStr=str;
        if(''!=newStr) {
            newStr = '<ul class="works-preview-box">'+newStr+'</ul>';
        }
        allStr=str + newStr;
        if(allStr!='') {
            $('.pics-box').show().find('.pics-preview-box ul')
                .html(allStr);
        }
    },

        /*
         * ���
         * para:
         * data - {arr} ͼƬ��Ϣ����
         * type - {bool} true ��ʾ��  ��� ģ����ã�false ��ʾ��  ��Ʒ ģ�����
         */
        t.getPicsStr=function(data,type){
            var $label=$('.pics-number label').eq(1);
            if(!data || data.length==0){
                return '';
            }
            var len=data.length,str='',
                pic,thumb,item;
            type && $label.text(len+'��Ƭ').css('display','inline');
            for(var i=0;i<len;i++){
                item=data[i];
                pic=item.picture;
                thumb=item.thumb
                str+='<li class="li-img" data-id="'+item.id+'">'+
                    '<a href="'+pic.url +'" data-size="'+pic.size.width +'x'+pic.size.height+'"></a>'+
                    '<img class="lazy-img works" data-original="'+thumb.url+'">'+
                    '</li>';
            }
            return str;
        };

    /*
     * ��Ƶ
     * para:
     * data- {arr} ��Ƶ�����б�
     * type - {bool} �Ƿ�ƻ���Ƶ�б�ĸ߶ȣ���ѧ��Ƶʱ��Ҫ����
     */
    t.getVideoStr=function(data,type){
        if(!data || data.length==0){
            return;
        }
        var len=data.length,
            str='',
            item,
            style='',
            $label=$('.pics-number label').eq(0),
            className='';
        if(type){
            var h=$('body').width()*7/16;
            style='height:'+h+'px;';
            $('.name-main-box img').css('display','inline-block');
        }else{
            if(!data[0].video_url) {
                $label.hide();
                return '';
            }
            $label.text(len+'��Ƶ').show();
            className='li-video';
        }

        for(var i=0;i<len;i++) {
            item=data[i];
            item.video_img = item.video_img || item.img || '';
            item.video_url = item.video_url || '';
            //item.video_url = 'http://91.16.0.7/video/14/output.m3u8';
            str+= '<li data-url="' + item.video_url + '" style="'+style+'" class="'+className+'">' +
                '<img class="lazy-img" data-original="' + item.video_img + '">' +
                '<span class="p-btn"><i class="icon-play"></i></span>' +
                '</li>';
        }
        return str;
    };

    /*������Ƶ��ϸ��Ϣ*/
    t.showPicsAndVideoDetailInfo=function(e){
        var $target=$(e.currentTarget),
            url=$target.data('url');
        if(!url){
            this.showTips('��Ƶ����');
            return;
        }
        this.resetVideoPlayerUrl($target.data('url'),$target.find('img').attr('src'));

        this.scrollControl(false);  //��ֹ����
    };


    /*�����ҵ���ʦ��Ϣ*/
    t.loadMyTeachersInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'appGetTeacherList',
            paraData: {organization_id: this.oid},
            sCallback: function(result){
                //that.fillMyTeachersInfo(result.teacherList);
                callback && callback(result.teacherList);
            },
            eCallback:function(txt){

            },
            type:'get',
            async:this.async
        });
    };

    /*����ҵ���ʦ��Ϣ*/
    t.fillMyTeachersInfo=function(data){
        var str='',itemInfo;
        if(!data || data.length==0){
            return;
        }
        else {
            var len = data.length;
            for (var i = 0; i < len; i++) {
                itemInfo = data[i].info;
                str+='<li><a target="_blank" href="'+this.baseUrl.replace('Organization','Teacher')+'teacherv3_1/uid/'+data[i].uid+'"><img src="'+itemInfo.avatar128+'"><p>' + itemInfo.nickname + '</p></a></li>';
            }
        }
        $('.teachers-box').show();
        $('.teachers-detail').html(str);
    }

    /*�����ҵ���Ƶ�̳���Ϣ*/
    t.loadTeachingVideoInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'appGetCoursesList',
            paraData: {organization_id: this.oid},
            sCallback: function(result){

                callback && callback(result);
            },
            eCallback:function(txt){
                $target.css('opacity',1);
                $target.find('.loadErrorCon').show().find('a').text('��ȡ��Ƶ��Ϣʧ�ܣ���������¼���').show();
                callback();
            },
            type:'get',
            async:this.async
        });
    };

    t.fillInTeachingVideo=function(data,basicData){
        if(!data || data.coursesList.length==0){
            return;
        }
        var $box =  $('.t-video-box').show();
        var str = this.getVideoStr(data.coursesList,true);
        $box.find('.basic-header span').html('('+data.coursesList.length+')');
        $box.find('ul').html(str);

    };

    //ԤԼ��,�ж��Ƿ�֧���������������Ȳ��ֹ�����ʾ
    t.fillAppointmentInfo=function(basicData){
        // false 0 null undefined
        var flag= parseInt(basicData.data.is_listen_preview) && basicData.data.listen_preview_text.length!=0;
        if (flag) {

            var str = '<div class="left-item"></div>' +
                '<div class="middle-item">' +
                '<p>'+
                basicData.data.listen_preview_text +
                '</p>'+
                '</div>' +
                '<div class="right-item"></div>';
            $('.appointment').show().html(str).css('height','44px');
        }
    };

    /*���Ž�ѧ��Ƶ*/
    t.showTeachingVideo=function(e){
        this.showTips('����App');
    };



    /*����ѧ����Ʒ*/
    t.loadWorksInfo=function(callback){
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'appGetStudentWorks',
            paraData: {organization_id: this.oid,count:8,version:2.9},
            sCallback: function(result){
                callback && callback(result)
            },
            eCallback:function(txt){

            },
            type:'get',
            async:this.async
        });
    };

    t.fillWorksInfo=function(result){
        if(!result || !result.data || result.data.length==0){
            return;
        }
        var $box =  $('.works-box').show(),
            data=result.data,
            len=data.length;
        var str = this.getPicsStr(result.data,false);
        $box.find('ul').html(str);
    };

    /*����Ⱥ��*/
    t.loadGroupsInfo=function(callback){
        var that=this;
        this.getDataAsyncPy({
            url: window.hisihiUrlObj.apiUrlPy+'/v1/im/org/' +this.oid +'/groups',
            paraData: {per_page:1},
            sCallback: function(result){
                callback && callback(result)
            },
            eCallback:function(txt){

            },
            type:'get',
            async:this.async
        });
    };

    t.filInGroupsInfo=function(result){
        if(!result || result.data.length==0){
            return;
        }
        var $box =  $('.groups-box').show(),
            data=result.data[0],
            str='<div class="left">'+
                '<img src="' + data.group_avatar + '">'+
                '</div>'+
                '<div class="right">'+
                '<p>' + data.group_name + '</p>'+
                '</div>';
        $box.find('.groups-detail-box').html(str);
    };


    /*�����ҵ�������Ϣ*/
    t.loadDetailCommentInfo=function(type,callback,perCount){
        if(!perCount){
            perCount=this.perPageSize;
        }
        var that=this;
        this.getDataAsync({
            url: this.baseUrl + 'commentList',
            paraData: {organization_id: this.oid,page:1,count:perCount,comment_type:type,version:'3.1'},
            sCallback: function(result){
                callback&&callback.call(that,result);
            },
            eCallback:function(txt){},
            type:'get',
            async:this.async
        });
    };



    return Teacher;
});