/**
 * Created by Jimmy on 2015/10/21.
 */

//���չ���

define(function () {
    var TodayAnnoucement = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.pageIndex=0;  //��ǰҳ��
        this.pageSize=0;  //��ҳ��
        this.perPageSize=10; //ÿҳ��ʾ������
        this.loadData(0);

        //�¼�����
        this.$wrapper.on('ul li','click', $.proxy(this.showDetailAnnounceInfo));
        this.$wrapper.on('scroll', $.proxy(this.loadData));

    };

    TodayAnnoucement.prototype={
        loadData:function(){
            this.getDataAsync(function(data){
                data;
            });
        },
        getDataAsync:function(callback){
            var tempObj={
                pageIndex:this.pageIndex,
                count:this.perPageSize
            };
            $.post('../../',tempObj,callback);
        },

        /*
        *��ʾ����Ļ������ݣ�
        * ���������ʱ��
        */
        showDetailAnnounceInfo:function(data){
            var str='';
            $.each(data,function(item){
                str+='<li><span>item.title</span><span>item.time</span></li>';
            });
            this.$wrapper.append(str);
        }
    };

    return TodayAnnoucement;

});