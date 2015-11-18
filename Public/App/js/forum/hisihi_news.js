?
var hisihiNews = function ($wrapper,urlObj) {
        this.separateOperation();
        this.$wrapper = $wrapper;
        this.urlObj=urlObj;
        this.pageIndex = 1;
        this.pageSize = 20;
        this.totalPage=1;
        this.loadData(1);
        this.$wrapper.scroll($.proxy(this,'scrollContainer'));
    };

hisihiNews.prototype = {


    separateOperation:function(){
        var operation=browserType();
        if(operation.mobile){
            if(operation.android){
                AppFunction.showShareView(false);  //���ð�׿�ķ��������Ʒ���ť������
            }
            else if(operation.ios){
                alert("IOS");
            }
        }



    },

    /*
     *���������б�����
     * para:
     * pageIndex - {int} ��ǰ��ҳ����
     */
    loadData: function (pageIndex) {
        var that=this;
        this.getDataAsync(pageIndex);
    },

    /*
     *����������������б����ݣ������ܵ�ҳ���������������
     * para:
     * pageIndex - {int} ��ǰ��ҳ����
     */
    getDataAsync: function (pageIndex) {
        if(pageIndex>this.totalPage){
            return;
        }
        $loadinng=this.$wrapper.find('#loadingTip').show();
        var tempObj = {
                pageIndex: pageIndex,
                count: this.pageSize
            },
            url = this.urlObj.server_url + '/newsList',
            that = this;

        var ajaxTimeoutTest=$.ajax({
            url: url,  //�����URL
            timeout: 10000, //��ʱʱ�����ã���λ����
            type: 'post',  //����ʽ��get��post
            data:tempObj,
            dataType: 'json',//���ص����ݸ�ʽ
            success: function (result) { //����ɹ��Ļص�����
                $loadinng.hide();
                if(result.success) {
                    that.totalPage=Math.ceil(result.totalCount/that.pageSize);
                    that.totalPage=5;
                    that.pageIndex++;
                    $loadinng.before(that.getNewsContent(result.data));
                }else{
                    $loadinng.next().text(result.massage).show().delay(2000).hide(0);
                }
            },
            complete : function(XMLHttpRequest,status){    //������ɺ�����ִ�в���
                $loadinng.hide();
                if(status=='timeout'){   //��ʱ,status����success,error��ֵ�����
                    ajaxTimeoutTest.abort();
                    $loadinng.next().text('����ʱ').show();
                }
                else if(status=='error'){
                    var tips='�������';
                    ajaxTimeoutTest.abort();
                    if(XMLHttpRequest.status=='404') {
                        tips='�����ַ����';
                    }
                    $loadinng.next().text(tips).show();
                }
            }
        });
    },

    /*
     *�������
     * para:
     * data - {array} �������
     * return
     * str - {string} �����ַ���
     */
    getNewsContent:function(data){
        data=JSON.parse('[{"id":"5472","title":"��ҳ��ͼƬ������ҳ��ͼƬ������ҳ��ͼƬ������ҳ��ͼƬ������ҳ��ͼƬ������ҳ��ͼƬ������ҳ��ͼƬ������ҳ��ͼƬ������ҳ��ͼƬ������ҳ��ͼƬ����","create_time":"1447299691","view_count":"89757","is_out_link":"0","link_url":"","url":"'+this.server_url+'/toppostdetailv2/post_id/5472","pic_url":"http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-11-12/56440a5ccd24e.jpg"},{"id":"5471","title":"���Ų���","create_time":"1447295771","view_count":"12043","is_out_link":"0","link_url":"","url":"'+this.server_url+'/toppostdetailv2/post_id/5471","pic_url":"http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-11-12/56440a5ccd24e.jpg"}]');
        var str = '',title, len = data.length, item,dateStr;
        for (var i = 0; i < len; i++) {
            item = data[i];
            title=this.substrLongStr(item.title,25);
            dateStr=this.getTimeFromTimestamp(item.create_time);
            str += '<li class="newsLiItem"><a href="'+item.url+'">' +
                '<div class="left">' +
                '<img src="' + item.pic_url + '"/>' +
                '</div>' +
                '<div class="right">' +
                '<div class="rightHeader">' +
                '<p>'+title+'</p>' +
                '</div>' +
                '<div class="rightBottom">'+
                '<div class="rightBottomLeft">'+
                '<i class="viewTimesIcon"><img src="'+this.urlObj.img_url+'/viewTimes.png"/></i>'+
                '<span>'+item.view_count +'</span>'+
                '</div>'+
                '<div class="rightBottomRight">'+ dateStr + '</div>'+
                '</div>' +
                '</div>' +
                '</a></li>';
        }
        return str;
    },

    /*
     *�������ظ��������
     */
    scrollContainer:function(e){
        var target= e.currentTarget,
            height = target.scrollHeight - $(target).height();
        if ($(target).scrollTop() == height) {  //�������ײ�
            this.loadData(this.pageIndex);
        }
    },

    /*
     *�ַ�����ȡ
     * para
     * str - {string} Ŀ���ַ���
     * len - {int} ��󳤶�
     */
    substrLongStr: function (str, len) {
        if (str.length > len) {
            str = str.substr(0, parseInt(len - 1)) + '����';
        }
        return str;
    },

    getTimeFromTimestamp:function (dateInfo, dateFormat) {
        return new Date(parseFloat(dateInfo) * 1000).format(dateFormat);
    },




};

function getUser_iOS(session_id,name,avatar_url){
    alert('session_id:'+session_id,'name:'+name+'avatar_url:̫���˲���ʾ��');
}

