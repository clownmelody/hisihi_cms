/**
 * Created by Jimmy on 2015/10/21.
 */

//我的老师

define(['jquery'],function () {
    var MyTeacher = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.modelBox=null;
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
        this.$wrapper.on('click','#addTeacher', $.proxy(this,'showAddNewTeachersModelBox'));
        this.$wrapper.on('keydown','#newGroupName',$.proxy(this,'doCommitNewGroup'));
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
            this.showMembersInfo(data,0);
        },
        getDataAsync:function(callback){
            var tempObj={
                pageIndex:this.pageIndex,
                count:this.perPageSize
            };
            $.post('../../',tempObj,callback);
        },


        /*
        *显示所有的分组和组员情况信息
        * @para
        * data -{array} 分组和组员信息数组
        * type - {int} 调用该方法的来源 直接加载数据为了0，添加组别时调，则为了1
        */
        showMembersInfo:function(data,type){
            var str='',
                that=this;
            $.each(data,function(){
                str+='<li class="tItems" data-name="'+this.groupName+'">'+
                        '<div class="teacherHeader groupItemHeader"> '+
                            '<span class="teacherTitle">'+this.groupName+'</span>'+
                        '</div>'+
                        '<ul class="list memberItemUl">'+that.getSomeGroupMembersStr(this.members)+'<div style="clear:both;" class="clearForMemberUl"></ul>'+
                      '</li>';
            });
            str+='<div style="clear:both;">';
            this.$wrapper.find('#teacherMainCon').append(str);
            type==1 && this.scrollToBottom($('.wrapper')[0]); //滚动到底部
        },

        /*
         *显示具体 某个小组的组员情况信息
         * @para
         * data -{object} 分组和组员信息数组
         */
        getSomeGroupMembersStr:function(members){
            var str='';
            $.each(members,function(){
                str+='<li>'+
                    '<div class="memberItemLeft"><img src="'+this.imgSrc+'"></div>'+
                    '<div class="memberItemRight"><p class="tname">'+this.name+'</p><p class="trole">'+this.role+'</p></div>'+
                    '</li>';
            });

            return str;
        },

        /*
         控制容器的显示和隐藏
         */
        controlAddGroupConState:function(){
            this.$wrapper.find('.addGroupDetailCon').toggle(50);
            this.$wrapper.find('#newGroupName').focus();
        },

        /*
        *添加新的组别
         */
        addNewGroup:function(e){
            var $target=$(e.srcElement),
                index=$target.index();


            //提交取消
            if(index==3){
                this.$wrapper.find('#newGroupName').val('');
                this.$wrapper.find('.addGroupCon').trigger('click');
                return;
            }

            //添加
            this.execAddNewGroup();
        },

        /*
         *执行新加组别
         */
        execAddNewGroup:function(){
            //名称没有问题
            var validity=this.newNameValidity();
            if(validity.flag){
                this.$wrapper.find('.addGroupCon').trigger('click');
                this.$wrapper.find('#newGroupName').val('');
                var data = [{groupName:validity.name,members:[]}];
                this.showMembersInfo(data,1);
            }else{
                this.$wrapper.find('#newGroupCommitError').text(validity.tip).show().delay(500).hide(0);
            }
        },

        /*
        *名称合法性判断
         */
        newNameValidity:function(){
            var name=this.$wrapper.find('#newGroupName').val().replace(/(^\s*)|(\s*$)/g,''),
                $allTitles= this.$wrapper.find('#teacherMainCon li .teacherTitle'),
                allNameArr = this.getExistGroupName(),
                flag=true,
                tip='';
            if(name!='') {
                if ($.inArray(name,allNameArr)>=0) {
                    flag = false;
                    tip='该组别已经存在';
                }
            }else{
                flag=false;
                tip='名称不能为空';
            }
            return {flag:flag,name:name,tip:tip};
        },

        /*
        *得到目前已经拥有的分组
        * Returns
        * tempArr - {array}名字数组
        */
        getExistGroupName:function(){
            var $allTitles =this.$wrapper.find('#teacherMainCon li .teacherTitle'),
                tempArr=[];
            $allTitles.each(function () {
                tempArr.push($(this).text());
            });
            return tempArr;
        },

        /*
        *回车确认添加
        */
        doCommitNewGroup:function(e){
            if(e.keyCode==13){
                this.$wrapper.find('.gAddBtn').trigger('click');
            }
        },

        /*
        *滚动到底部
        **/
        scrollToBottom:function(target,callback){
            //$(target).scrollTop(target.scrollHeight+140);
            $('html,body').animate({ scrollTop: target.scrollHeight}, 800, callback);
        },

        /*
        *显示添加老师弹出层
        */
        showAddNewTeachersModelBox:function(){

            var that = this;
            if(!this.modelBox) {
                this.modelBox = new Hisihi.modelBox({
                    headLabel: '选择或新建分组',
                    boxMainContentForAlert: function () {
                        return '<div id="addNewTeacherModelBox">' +
                                    '<div class="addNewTeacherWrapper">' +
                                        '<ul class="list allGroupNamesList">' + that.getAllGroupNameStrForModelBox.call(that)+'</ul>' +
                                        '<div class="addNewTeacherItem">'+
                                            '<div class="addNewTeacherHeader">添加新老师</div>'+
                                            '<input type="text" id="addNewTeacherInput" class="form-control" placeholder="输入账号或者名字来查找老师"/>'+
                                            '<label class="errorInfo addNewTeacherInput">请选择老师</label>'+
                                        '</div>'+
                                    '</div>' +
                                    '<div class="addTeacherBtnRow">'+
                                        '<input type="button" id="addTeacherCommitBtn" class="btn btn-grey" value="确定"/>' +
                                        '<label id="addTeacherCalBtn" class="newGroupCommitCal" title="取消">取消</lable>'+
                                    '</div>' +
                            '</div>';
                    },
                    initCallback:function(){
                        that.initModelBoxCallback(this,that);
                    },
                    //closeBoxCallback: $.proxy(that, 'close'),
                    boxWidth: '680px',
                    boxHeight: '440px',
                    showAtFirst: true
                });
            }else{
                this.modelBox.show();
            }
        },

        /*
        *获得添加模态框的 可选组别
         */
        getAllGroupNameStrForModelBox:function(){
            var arrData=this.getExistGroupName(),
                str='',
                className='';
            arrData.push('新建分组');
            var len=arrData.length;
            for(var i=0;i<len;i++){
                className='';
                if(i==0){
                    className='selected';
                }
                if(i==len-1){
                    className='addNewOne';
                }
                str+='<li class="'+className+'"><div class="radioContainer">'+arrData[i]+'</div></li>';
            }
            str+='<div style="clear: both;"></div>';
            return str;

        },

        /*
        *模态窗口初始化完成回调方法
        * Para
        * modelContext - {object} 模态窗口的上下文对象
        * myContext - {object} 当前上下文对象
        */
        initModelBoxCallback:function(modelContext,myContext){
            this.modelBoxEventsInit(modelContext,myContext);
        },

        /*
        *模态窗口的事件注册
        */
        modelBoxEventsInit:function(modelContext,myContext){
            var $txtTarget=modelContext.$panel.find('#addNewTeacherInput'),
            $btnCommit=modelContext.$panel.find('#addNewTeacherInput');

            /*
            *确定添加
             */
            modelContext.$panel.on('click','#addTeacherCommitBtn',function(){
                if($txtTarget.val()==''){
                    modelContext.$panel.find('.addNewTeacherInput').show().delay(500).hide(0);
                    return;
                }
                modelContext.hide();

                //执行真正的添加
                var groupName=modelContext.$panel.find('.allGroupNamesList .selected').text();

                myContext.execAddNewTeacher.call(myContext,groupName,$txtTarget.val());
                myContext.clearAddTeacherInfo(modelContext.$panel);

            });

            /*
            *取消添加
            */
            modelContext.$panel.on('click','#addTeacherCalBtn',function(){
                modelContext.hide();
                myContext.clearAddTeacherInfo(modelContext.$panel);
            });

            /*
            *即时搜索
             */
           //var emailTips = new Hisihi.tipEmailUrl($txtTarget,
           //     { top: '37px', left: '0' },
           //     { widthInfo: $txtTarget.width() + 5 });
           //
           // $txtTarget.on('keyup', function (e) {
           //     if (e.keyCode == 13) {
           //         $("#resetPwd").trigger('click');
           //         this.value = '';
           //         event.returnValue = false;
           //     }
           //     if (e.keyCode == 40 || e.keyCode == 38) {
           //         emailTips._selectTipsEmailUrl(e.keyCode);
           //         return;
           //     }
           //     emailTips._showTipEmailUrl([{name:'阿信',role:'管理员',imgSrc:window.urlObject.image+'/userImg/app1.png'},{name:'郑钧',role:'成员',imgSrc:window.urlObject.image+'/userImg/app2.png'},{name:'李志',role:'成员',imgSrc:window.urlObject.image+'/userImg/app1.png'},{name:'阿信',role:'管理员',imgSrc:window.urlObject.image+'/userImg/app1.png'}]);
           // });
           //
           // $txtTarget.blur(function(){
           //     var isNull=true;
           //     if(this.value!=''){
           //         isNull=false;
           //     }
           //     $btnCommit[flag?'removeClass':'addClass']('abled');
           // });

            /*
            *组别选择
            */
            modelContext.$panel.on('click','.allGroupNamesList li',function(){
                var index=$(this).index();
                if(index!=$(this).siblings().length-1) {
                    $(this).addClass('selected').siblings().removeClass('selected');
                }

                //新建组别
                else{
                    modelContext.hide();
                    myContext.scrollToBottom($('.wrapper')[0],function(){
                       var $target= myContext.$wrapper.find('.addGroupDetailCon');
                        if($target.is(':hidden')){
                            $target.prev().trigger('click');
                        }
                    });
                }
            });

        },

        /*
        *确定添加老师的必填信息
        */
        checkAddTeacherValidity:function(){

        },

        /*
         *清除添加老师的内容填充
         */
        clearAddTeacherInfo:function($parent){
           $parent.find('#addNewTeacherInput').val('');
            var $li=$parent.find('.allGroupNamesList li'),
                $selected=$li.filter('.selected');
            if($selected.index()!=0) {
                $selected.removeClass('selected');
            }
            $li.eq(0).addClass('selected');
        },

        /*
        *添加老师到相应的分组
        */
        execAddNewTeacher:function(groupName,name){
            var member=[{name:name,role:'成员',imgSrc:window.urlObject.image+'/userImg/app3.png'}];
            var str = this.getSomeGroupMembersStr(member),
                $allLi=this.$wrapper.find('#teacherMainCon .tItems'),
                $li;
                $allLi.each(function(){
                if ($(this).data('name')==groupName){
                    $li = $(this);
                    return false;
                }
            });
            $li.find('.memberItemUl .clearForMemberUl').before(str);
        },

    };


    /***即时搜索***/
    Hisihi.tipEmailUrl=function($txtTarget, posStyle,options){
        $.extend(this,options);
        this.$txtTarget = $txtTarget;
        this.posStyle = posStyle;
        !this.$tipTarget && $txtTarget.after('<div class="tipEmailUrl"></div>');
        this.$tipTarget = $txtTarget.next();
        this.controlContainerStyle();
        this.eventsInit();
    };

    Hisihi.tipEmailUrl.prototype={
        /*显示邮箱提示*/
        _showTipEmailUrl: function (mydata) {
            var val = this.$txtTarget.val();
            if (val == '') {
                this.hideTipEmailUrl();
                return;
            }
            var str = '';
            this.emailTypeData=mydata;
            var len = this.emailTypeData.length;
            for (var i = 0; i < len; i++) {
                str += '<li class="tipEmailUrlLi">'+this.emailTypeData.name[i] + '</li>';
            }
            this.$tipTarget.show().html(str);
        },

        /*隐藏提示框*/
        hideTipEmailUrl: function () {
            this.$tipTarget.hide();
        },

        /*选择相应的邮箱提示*/
        _selectTipsEmailUrl: function (codeNum) {
            var $li =  this.$tipTarget.find('.tipEmailUrlLi');
            var $liSel = $li.filter('.selected');
            var index = $liSel.index();
            if ($li.length > 0) {
                $li.removeClass('selected');
                var $lastLi = $li.last();
                var $firstLi = $li.first();
                if (codeNum == 40) {//向下
                    if ($liSel.length == 0 || index == $li.length - 1) {
                        $firstLi.addClass('selected');
                    }
                    else {
                        $li.eq(index + 1).addClass('selected');
                    }
                }
                else { //38:  //向上
                    if ($liSel.length == 0 || index == 0) {
                        $lastLi.addClass('selected');
                    } else {
                        $li.eq(index - 1).addClass('selected');
                    }
                }
                this.$txtTarget.val($li.filter('.selected').text());
            }
        },

        eventsInit: function () {
            var that = this;
            /*从提示中选择相应的邮箱*/
            this.$tipTarget.on('click', 'li', function (e) {
                var text = this.innerText;
                that.$txtTarget.val(text);
                e.stopPropagation();
                that.hideTipEmailUrl();
            });

            /*隐藏邮箱提示信息*/
            $(document).on('click', function () {
                that.hideTipEmailUrl();
            });
        },

        /**控制框的样式 宽，位置等**/
        controlContainerStyle: function () {
            if (!this.widthInfo) {
                this.widthInfo = this.$txtTarget.width();
            }
            this.$tipTarget.css({
                'top': this.posStyle.top,
                'left': this.posStyle.left,
                'width': this.widthInfo
            });
        }
    };


    var myTeacher=new MyTeacher($('.teachersWrapper'));
    return myTeacher;

});