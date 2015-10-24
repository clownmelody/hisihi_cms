/**
 * Created by Jimmy on 2015/10/21.
 */

//我的老师

define(['jquery'],function () {
    var MyTeacher = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.loadData();
        //事件注册
        var that=this;
        this.$wrapper.on('mouseover mouseout','.memberItemUl li',function(e){
            var $target=$(this).find('img'),
                className='hover';
            if(e.type=='mouseover') {
                $target.addClass(className);
            }else{
                $target.removeClass(className);
            }
        });
        this.$wrapper.on('click','.addGroupCon', $.proxy(this,'controlAddGroupConState'));
        this.$wrapper.on('click','.gAddBtn', $.proxy(this,'addNewGroup'));
    };

    MyTeacher.prototype={
        loadData:function(){
            var data=[
                {groupName:'UI设计',members:[{name:'阿信',role:'管理员',imgSrc:window.urlObject.image+'/userImg/app1.png'},{name:'郑钧',role:'成员',imgSrc:window.urlObject.image+'/userImg/app2.png'},{name:'李志',role:'成员',imgSrc:window.urlObject.image+'/userImg/app1.png'},{name:'阿信',role:'管理员',imgSrc:window.urlObject.image+'/userImg/app1.png'},{name:'郑钧',role:'成员',imgSrc:window.urlObject.image+'/userImg/app2.png'},{name:'李志',role:'成员',imgSrc:window.urlObject.image+'/userImg/app1.png'}]},
                {groupName:'平面设计',members:[{name:'万晓利',role:'成员',imgSrc:window.urlObject.image+'/userImg/app3.png'},{name:'张玮玮',role:'成员',imgSrc:window.urlObject.image+'/userImg/app1.png'},{name:'花大爷',role:'成员',imgSrc:window.urlObject.image+'/userImg/app1.png'}]},
                {groupName:'环艺设计',members:[{name:'二手玫瑰',role:'成员',imgSrc:window.urlObject.image+'/userImg/app3.png'},{name:'丢火车',role:'成员',imgSrc:window.urlObject.image+'/userImg/app3.png'}]},
                {groupName:'游戏设计',members:[{name:'干死那个石家庄人',role:'成员',imgSrc:window.urlObject.image+'/userImg/app3.png'},{name:'后海大鲨鱼',role:'成员',imgSrc:window.urlObject.image+'/userImg/app3.png'}]},
                {groupName:'网页设计',members:[]}
            ];
            //this.getDataAsync(function(data){
            //    data;
            //});
            this.showMembersInfo(data);
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
        showMembersInfo:function(data){
            var str='',
                str1='';
            $.each(data,function(){
                str1='';
                var gitem=this,
                    members=this.members;
                    $.each(members,function(){
                        str1+='<li>'+
                                    '<div class="memberItemLeft"><img src="'+this.imgSrc+'"></div>'+
                                    '<div class="memberItemRight"><p class="tname">'+this.name+'</p><p class="trole">'+this.role+'</p></div>'+
                                '</li>';
                    });
                str1+='<div style="clear:both;">';
                str+='<li class="tItems">'+
                        '<div class="teacherHeader groupItemHeader"> '+
                            '<span class="teacherTitle">'+this.groupName+'</span>'+
                        '</div>'+
                        '<ul class="list memberItemUl">'+str1+'</ul>'+
                      '</li>';
            });
            str+='<div style="clear:both;">';
            this.$wrapper.find('#teacherMainCon').append(str);
        },

        /*
         控制容器的高度
         */
        controlAddGroupConState:function(){
            this.$wrapper.find('.addGroupDetailCon').toggle(50);
        },

        /*
        *添加新的组别
         */
        addNewGroup:function(e){
            var $target=$(e.srcElement),
                index=$target.index();
            this.$wrapper.find('.addGroupCon').trigger('click');

            //提交取消
            if(index==3){
                this.$wrapper.find('#newGroupName').value('');
            }


            //添加
        },

    };

    var myTeacher=new MyTeacher($('.teachersWrapper'));
    return myTeacher;

});