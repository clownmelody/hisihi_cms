/**
 * Created by jimmy on 2015/12/28.
 */

define(['zepto','common'],function(){
    function OrgBasicInfo($wrapper) {
        this.$wrapper = $wrapper;
        var that = this;

        //控制视频预览框的高度
        this.videoPreviewBox();
    }

    OrgBasicInfo.prototype={


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
    };

    return OrgBasicInfo;
});