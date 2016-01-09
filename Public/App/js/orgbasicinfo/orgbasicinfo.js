/**
 * Created by jimmy on 2015/12/28.
 */

define(['zepto','common'],function(){
    function OrgBasicInfo($wrapper,oid) {
        this.$wrapper = $wrapper;
        var that = this;
        this.oid=oid;
        this.controlLoadingPos();

        /*加载基本信息*/
        var queryPara={
            url:window.urlObject.apiUrl+'appGetBaseInfo',
            paraData:{organization_id:this.oid},
            sCallback: $.proxy(this,'fillInData'),
            eCallback:null
        };
        this.loadData(queryPara);

        //控制视频预览框的高度
        this.videoPreviewBox();
        this.locationMapBox();
        this.initImgPercent();

        this.$wrapper.find('#videoPreviewBox img').bind('load',this.controlPlayBtnStyle);
        this.$wrapper.on('click','.loadError',function(){   //重新加载数据
            that.loadData(queryPara);
        });
    }

    OrgBasicInfo.prototype={

        /*加载等待框的位置*/
        controlLoadingPos:function(){
            var $loading = this.$wrapper.find('.loadingResultTips'),
                w=$loading.width(),
                h=$loading.height(),
                dw=this.$wrapper.width(),
                dh=this.$wrapper.height();
            $loading.css({'top':(dh-h)/2,'left':(dw-w)/2,'opacity':'1'});
        },

        /*机构视频预览的图片*/
        videoPreviewBox:function(){
            var $temp=this.$wrapper.find('#videoPreviewBox'),
                w=this.$wrapper.width(),
                h=parseInt(w*(9/16)),
                $i=$temp.find('i'),
                ih=$i.height(),
                iw=$i.width();
            this.$wrapper.find('#videoPreviewBox').css('height',h);
            $i.css({'top':(h-ih)/2,'left':(w-iw)/2});
        },

        /*地图预览的图片*/
        locationMapBox:function(){
            var $temp=this.$wrapper.find('.mainItemLocation'),
                w=this.$wrapper.width(),
                h=parseInt(w*(7/16)),
                $i=$temp.find('i'),
                ih=$i.height(),
                iw=$i.width();
            this.$wrapper.find('#locationMap').css('height',h);
        },

        /*播放按钮图片*/
        controlPlayBtnStyle:function(){
            var $temp=this.$wrapper.find('#videoPreviewBox img'),
                w=$temp.width(),
                h=$temp.height(),
                $i=$temp.next(),
                ih=$i.height(),
                iw=$i.width();
            $i.css({'top':(h-ih)/2,'left':(w-iw)/2});
        },

        loadData:function(paras) {
            var that=this;
            that.controlLoadingTips(0);
            var loginXhr = $.ajax({
                url: paras.url,
                type: paras.type,
                data: paras.paraData,
                timeOut: 10,
                contentType: 'application/json;charset=utf-8',
                complete: function (xmlRequest, status) {
                    if(status=='success') {
                        var rTxt = xmlRequest.responseText,
                            result = {};
                        if (rTxt) {
                            result = JSON.parse(xmlRequest.responseText)
                        } else {
                            result.status = false;
                        }

                        if (result.success) {
                            that.controlLoadingTips(1);
                            //that.fillInData(JSON.parse(xmlRequest.responseText).data);
                            paras.sCallback(JSON.parse(xmlRequest.responseText).data);
                        } else {
                            var txt=result.message;
                            that.controlLoadingTips(-1,txt);

                        }
                    }
                    //超时
                    else if (status == 'timeout') {
                        loginXhr.abort();
                        that.controlLoadingTips(-1,'加载失败，点击重新加载');
                    }
                    else {

                        that.controlLoadingTips(-1,'加载失败，点击重新加载');
                    }
                }
            });

        },

        /*显示具体信息*/
        fillInData:function(data){
            var  authen1=data.authenticationInfo[2].status,
                class1=authen1?'certed':'unCerted',
                authen2=data.authenticationInfo[3].status,
                class2=authen2?'certed':'unCerted';

           //var str= '<div class="mainItem logoAndCertInfo">'+
            var str='<div class="head mainContent">'+
                            '<div class="filter">'+
                                '<img class="logoBg myLogo" src="'+data.logo+'" alt="logo"/>'+
                                '<div class="filterUp"></div>'+
                            '</div>'+
                            '<div class="mainInfo">'+
                                '<div class="left">'+
                                    '<img id="myLogo" class="myLogo" src="'+data.logo+'" />'+
                                '</div>'+
                                '<div class="right">'+
                                    '<div id="orgName">'+data.name+'</div>'+
                                    '<div class="peopleInfo">'+
                                        '<div class="peopleInfoItem">'+
                                        '<div class="valInfo" id="viewedVal">'+data.view_count+'</div>'+
                                        '<div class="filedInfo">查看人数</div>'+
                                    '</div>'+
                                    '<div class="peopleInfoItem">'+
                                        '<div class="valInfo" id="teacherdVal">'+data.teachersCount+'</div>'+
                                        '<div class="filedInfo">老师</div>'+
                                    '</div>'+
                                    '<div class="peopleInfoItem">'+
                                        '<div class="valInfo" id="fansVal">'+data.followCount+'</div>'+
                                        '<div class="filedInfo">粉丝</div>'+
                                    '</div>'+
                                    '<div class="peopleInfoItem">'+
                                        '<div class="valInfo" id="groupsVal">'+data.groupCount+'</div>'+
                                        '<div class="filedInfo">群主</div>'+
                                    '</div>'+
                                    '<div style="clear: both;"></div>'+
                                '</div>'+
                            '</div>'+
                            '</div>'+
                        '</div>'+
                        '<div class="bottom">'+
                            '<div class="cerInfoItem">'+
                                '<span>'+
                                    '<i class="heiCerIcon spiteBg '+class1+'"></i>'+
                                    '<span class="cerName '+class1+'">嘿设汇认证</span>'+
                                    '<div style="clear: both;"></div>'+
                                '</span>'+
                            '</div>'+
                            '<div class="cerInfoItem">'+
                                '<span>'+
                                    '<i class="honestCerIcon spiteBg '+class2+'"></i>'+
                                    '<span class="cerName '+class1+'">诚信机构认证</span>'+
                                    '<div style="clear: both;"></div>'+
                                '</span>'+
                            '</div>'+
                        //'</div>'+
                    '</div>';
            this.$wrapper.find('.logoAndCertInfo').html(str).css('opacity',1);
            this.$wrapper.find('#myLogo').setImgBox();
            this.loadTopAnnouncement();
        },

        /*加载头条信息*/
        loadTopAnnouncement:function(){
            this.loadData({
                url: window.urlObject.apiUrl + 'getNotice',
                paraData: {organization_id: this.oid},
                sCallback: function(data){
                    data;
                    this.$wrapper.find('.mainItemTopNews').css('opacity',1);
                }
            });
        },


        /*
        *加载等待,
        *para:
        * status - {num} 状态控制 码
        * 0.显示加载等待;  1 隐藏等待; -1加载失败，重新加载
        */
        controlLoadingTips:function(status,txt){
            var $target=this.$wrapper.find('#loadingTip'),
                $img=$target.find('.loadingImg'),
                $error=$img.next().hide();
            if(status==0){
                $target.css('z-index',1);
                $img.addClass('active');
            }
            else if(status==1){
                $target.css('z-index',-1);
                $img.removeClass('active');
            }else{
                $img.removeClass('active');
                $error.text(txt).show();
            }

        },

        /*
         *滚动加载更多的数据
         * 通过滚动条是否在底部来确定
         * 同时通过 loadingData 类 来防止连续快速滚动导致的重复加载
         */
        scrollContainer:function(e){
            var target= e.currentTarget,
                height = target.scrollHeight - $(target).height();

            //加载评论内容
            if($(target).scrollTop()==400){

            }

            //加载更加多评论内容
            if ($(target).scrollTop() == height && !$(target).hasClass('loadingData')) {  //滚动到底部
                $(target).addClass('loadingData');
                this.loadData(this.pageIndex,function(){
                    $(target).removeClass('loadingData');
                });
            }
        },

        /*根据比例大小 计算图片的大小*/
        initImgPercent:function(){
            $.fn.setImgBox=function(){
                if (this.length == 0) {
                    return;
                }
                var img=new Image();
                img.src=this[0].src;
                var height = img.height,
                    width = img.width,
                    mHeight=this.css('max-height'),
                    mWidth=this.css('max-width');
                if (!mHeight || mHeight=='none') {
                    mHeight = this.parent().height();
                }else{
                    mHeight=mHeight.replace('px','');
                }
                if (!mWidth|| mWidth=='none') {
                    mWidth = this.parent().width();
                }
                else{
                    mWidth=mWidth.replace('px','');
                }
                var flag1 = height > mHeight;
                var flag2 = width > mWidth;
                var radio = 1;
                if (flag1 || flag2) {
                    var radio1 = mHeight / height;
                    var radio2 = mWidth / width;
                    if (radio1 < radio2) {
                        height = mHeight;
                        width = width * radio1;
                        radio = radio1;
                    } else {
                        width = mWidth;
                        height = height * radio2;
                        radio = radio2;
                    }
                }
                this.css({'width':width+'px','height':height+'px','margin-top':(this.parent().height()-height)/2+'px'}).attr('data-radio',radio);
                return this;
            };
        },
    };

    return OrgBasicInfo;
});