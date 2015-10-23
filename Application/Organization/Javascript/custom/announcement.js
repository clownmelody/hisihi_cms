/**
 * Created by Jimmy on 2015/10/21.
 */

//今天公告

define(function () {
    var TodayAnnoucement = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.pageIndex=0;  //��ǰҳ��
        this.pageSize=0;  //��ҳ��
        this.perPageSize=10; //ÿҳ��ʾ������
        this.loadData(0);

        //事件注册
        this.$wrapper.on('ul li','click', $.proxy(this.showDetailAnnounceInfo));
        this.$wrapper.on('scroll', $.proxy(this.loadData));

    };

    TodayAnnoucement.prototype={
        loadData:function(){
            var data=[{title:'习近平卡梅伦庄园会晤 在酒吧小酌吃炸鱼薯条',date:'2015.10.23'},
                {title:'巡视组：17家央企均有利益输送 存链条式腐败',date:'2015.10.20'},
                {title:'交通部专车新规或存法律硬伤 专家建议重制',date:'2015.10.24'},
                {title:'中英签4000亿大单 李嘉诚被指又快人一步',date:'2015.10.04'},
                {title:'青岛“天价虾”被宰游客将5万奖金捐重病患儿',date:'2015.10.04'},
                {title:'陕西夜跑女教师遇害续：警方悬赏5万缉凶',date:'2015.10.14'},
                {title:'湖南杀师案细节：三名少年作案后淡定上网',date:'2015.09.04'},
                {title:'“红海”转为“血海”：小米销量下滑引发业内担忧',date:'2015.08.04'}
            ];
            //this.getDataAsync(function(data){
            //    data;
            //});
            this.showDetailAnnounceInfo(data);
        },
        getDataAsync:function(callback){
            var tempObj={
                pageIndex:this.pageIndex,
                count:this.perPageSize
            };
            $.post('../../',tempObj,callback);
        },

        /*
        *显示详细的公告信息
        * @para
        * data -{array} 公告数组
        */
        showDetailAnnounceInfo:function(data){
            var str='';
            $.each(data,function(){
                str+='<li><span>'+this.title+'</span><span>'+this.date+'</span></li>';
            });
            this.$wrapper.append(str);
        }
    };
    var todayAnnouncement=new TodayAnnoucement($('#announcesContainer ul'));
    return todayAnnouncement;

});