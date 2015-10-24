/**
 * Created by Jimmy on 2015/10/21.
 */

//今天公告

define(['jquery'],function () {
    var TodayAnnoucement = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.pageIndex=0;  //当前页
        this.pageSize=0;  //总页数
        this.perPageSize=10; //每次加载数目
        this.loadData(0);
        this.controlContainerHeight();
        //事件注册
        var that=this;
        this.$wrapper.on('#announcesContainer ul li','click', $.proxy(this.showDetailAnnounceInfo));
        this.$wrapper.parent().scroll(function(){
            alert();
            that.scrollContainer.call(that,this);
        });

    };

    TodayAnnoucement.prototype={
        loadData:function(){
            var data=[{title:'习近平卡梅伦庄园会晤 在酒吧小酌吃炸鱼薯条',date:'2015.10.23',src:'http://world.huanqiu.com/exclusive/2015-10/7827489.html?from=bdwz'},
                {title:'巡视组：17家央企均有利益输送 存链条式腐败',date:'2015.10.20',src:'http://world.huanqiu.com/exclusive/2015-10/7827489.html?from=bdwz'},
                {title:'交通部专车新规或存法律硬伤 专家建议重制',date:'2015.10.24',src:''},
                {title:'中英签4000亿大单 李嘉诚被指又快人一步',date:'2015.10.04',src:''},
                {title:'青岛“天价虾”被宰游客将5万奖金捐重病患儿',date:'2015.10.04',src:''},
                {title:'陕西夜跑女教师遇害续：警方悬赏5万缉凶',date:'2015.10.14',src:''},
                {title:'湖南杀师案细节：三名少年作案后淡定上网',date:'2015.09.04',src:''},
                {title:'青岛“天价虾”被宰游客将5万奖金捐重病患儿',date:'2015.10.04',src:''},
                {title:'陕西夜跑女教师遇害续：警方悬赏5万缉凶',date:'2015.10.14',src:''},
                {title:'湖南杀师案细节：三名少年作案后淡定上网',date:'2015.09.04',src:''},
                {title:'交通部专车新规或存法律硬伤 专家建议重制',date:'2015.10.24',src:''},
                {title:'中英签4000亿大单 李嘉诚被指又快人一步',date:'2015.10.04',src:''},
                {title:'青岛“天价虾”被宰游客将5万奖金捐重病患儿',date:'2015.10.04',src:''},
                {title:'陕西夜跑女教师遇害续：警方悬赏5万缉凶',date:'2015.10.14',src:''},
                {title:'湖南杀师案细节：三名少年作案后淡定上网',date:'2015.09.04',src:''},
                {title:'青岛“天价虾”被宰游客将5万奖金捐重病患儿',date:'2015.10.04',src:''},
                {title:'陕西夜跑女教师遇害续：警方悬赏5万缉凶',date:'2015.10.14',src:''},
                {title:'湖南杀师案细节：三名少年作案后淡定上网',date:'2015.09.04',src:''},
                {title:'“红海”转为“血海”：小米销量下滑引发业内担忧',date:'2015.08.04',src:''},
            ];
            //this.getDataAsync(function(data){
            //    data;
            //});
            this.showShortAnnounceInfo(data);
        },
        getDataAsync:function(callback){
            var tempObj={
                pageIndex:this.pageIndex,
                count:this.perPageSize
            };
            $.post('../../',tempObj,callback);
        },

        /*
        *滚动加载更多的数据
        * @para:
        * target - {object} javascript 对象
        */
        scrollContainer:function(target){
            var height = target.scrollHeight - $(target).height();
            if ($(target).scrollTop() == height) {  //滚动到底部
                this.loadData();
                this.$wrapper.find('.loadingData').show().delay(2000).hide(0);
            }
        },

        /*
         *显示详细的公告信息
         * @para
         * data -{array} 公告数组
         */
        showDetailAnnounceInfo:function(){

        },

        /*
        *显示简要的公告信息
        * @para
        * data -{array} 公告数组
        */
        showShortAnnounceInfo:function(data){
            var str='';
            $.each(data,function(){
                str+='<li class="anListItem"><a href="'+this.src+'"> <span>'+this.title+'</span><span>'+this.date+'</span></a></li>';
            });
            this.$wrapper.find('#announcesContainer .clearDiv').before(str);
        },

        /*
         控制容器的高度
         */
        controlContainerHeight:function(){

        },

    };
    var todayAnnouncement=new TodayAnnoucement($('.anWrapper'));
    return todayAnnouncement;

});