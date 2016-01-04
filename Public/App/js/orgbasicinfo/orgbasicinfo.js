/**
 * Created by jimmy on 2015/12/28.
 */

define(['zepto','common'],function(){
    function OrgBasicInfo($wrapper) {
        this.$wrapper = $wrapper;
        var that = this;
        this.fillInData();

        //控制视频预览框的高度
        this.videoPreviewBox();
        this.locationMapBox();
        this.$wrapper.find('#videoPreviewBox img').bind('load',this.controlPlayBtnStyle);
    }

    OrgBasicInfo.prototype={

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

        fillInData:function(){},

    };

    return OrgBasicInfo;
});