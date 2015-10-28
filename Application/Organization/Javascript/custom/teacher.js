/**
 * Created by Jimmy on 2015/10/21.
 */

//我的老师

define(['jquery','jqueryui'],function () {
    var MyTeacher = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.modelBox=null;
        this.sectionId=JSON.parse($.cookie('hisihi-org')).session_id;
        this.organization_id=20;
        this.basicApiUrl=window.urlObject.apiUrl+'/api.php?s=/Organization';
        this.loadData();

        //事件注册
        var that=this;
        this.initSortEvents();  //拖动排序
        this.$wrapper.on('mouseover mouseout','.memberItemUl li.normal',function(e){
            var $target=$(this).find('img'),
                className='hover';
            if(e.type=='mouseover') {
                $target.addClass(className);
            }else{
                $target.removeClass(className);
            }
        });
        this.$wrapper.on('mouseover mouseout','.memberItemUl li.edit',function(e) {
            var $target=$(this).find('.deleteTeacher'),
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
        this.$wrapper.on('click','.editTeacher',$.proxy(this,'showEditTeacherBox'));
        this.$wrapper.on('click','.deleteTeacher',$.proxy(this,'deleteTeacher'));
    };

    MyTeacher.prototype={
        loadData:function(){
            this.getDataAsync(function(data){
                if(data.success) {
                    data = data.data;
                    this.showMembersInfo(data,0);
                }else{
                    alert('数据加载失败！');
                }
            });
        },
        getDataAsync:function(callback){
            var tempObj={
                    $page:1,
                    $count:1000000,
                    organization_id:this.organization_id,
                    session_id:this.sectionId
                },
                that=this;
            Hisihi.getDataAsync({
                type: "post",
                url: this.basicApiUrl+'/getAllGroupsTeachers',
                data: tempObj,
                callback:function(data){
                    callback.call(that,data)
                }
            });
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
                var groupInfo=this.group_info;
                str+='<li class="tItems" data-name="'+groupInfo.group_name+'" data-id="'+groupInfo.group_id+'">'+
                        '<div class="teacherHeader groupItemHeader"> '+
                            '<span class="teacherTitle">'+groupInfo.group_name+'</span>'+
                        '</div>'+
                        '<ul class="list memberItemUl list-data-ul">'+that.getSomeGroupMembersStr(this.teachers)+'<div style="clear:both;" class="clearForMemberUl"></ul>'+
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
                var name=this.nickname;
                if(name.length>6){
                    name=name.substr(0,5)+'…'
                }
                str+='<li class="normal" data-id="'+this.uid+'" data-relation-id="'+this.relation_id+'">'+
                        '<div class="memberItemLeft"><img src="'+this.avatar+'"></div>'+
                        '<div class="memberItemRight"><p class="tname" title="'+this.nickname +'">'+name+'</p><p class="trole">'+this.role+'</p></div>'+
                        '<div class="deleteTeacher delete-item-btn" title="删除"></div>'+
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
                //this.$wrapper.find('.addGroupCon').trigger('click');
                this.$wrapper.find('#newGroupName').val('');
                var tempData = {
                        organization_id:this.organization_id,
                        group_name:validity.name,
                        session_id:this.sectionId
                    },
                url=this.basicApiUrl+'/addTeachersGroup',
                    that=this;

                Hisihi.getDataAsync({
                    type: "post",
                    url: url,
                    data: tempData,
                    callback:function(data){
                        if(data.success) {
                            var tempData=[{
                                group_info:{group_name: validity.name,group_id:data.id},
                                teachers:[]
                            }];
                            that.showMembersInfo.call(that, tempData, 1);
                        }
                        else{
                            alert('添加失败！')
                        }
                    }
                });
            }else{
                this.$wrapper.find('#newGroupCommitError').text(validity.tip).show().delay(500).hide(0);
            }
        },

        /*
        *组员移动事件注册
        *
         */
        initSortEvents:function(){
            $target = this.$wrapper.find('.memberItemUl');

            //任务拖动
            $target.sortable({
                items: ">li",
                helper: 'clone',
                delay: 300,
                cursor: 'move',
                scroll: true,
                placeholder: "sortableplaceholder",
                connectWith: '.memberItemUl',
                start: function (event, ui) {

                },

                stop: function (event, ui) {

                }
            });
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
            var $allTitles =this.$wrapper.find('#teacherMainCon>li'),
                tempArr=[];
            $allTitles.each(function () {
                tempArr.push({
                    id:$(this).data('id'),
                    name:$(this).data('name')
                });
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
        *显示删除老师按钮
        */
        showEditTeacherBox:function(e){
            var $target=$(e.currentTarget),
                flag=$target.text()=='编辑',
                $li = this.$wrapper.find('.list-data-ul li');
            if(flag) {
                $target.text('关闭编辑');
                $li.removeClass('normal').addClass('edit');
            }else{
                $target.text('编辑');
                $li.removeClass('edit').addClass('normal');
            }
        },

        /*
        *删除老师
        */
        deleteTeacher:function(e){

            if(window.confirm('确定删除该老师么？')) {
                var $target=$(e.currentTarget).parent(),
                    tempData = {
                        relation_id:$target.attr('data-relation-id')
                    },
                    url=this.basicApiUrl+'/deleteTeacherFromGroup',
                    that=this;
                Hisihi.getDataAsync({
                    type: "post",
                    url: url,
                    data: tempData,
                    callback:function(data){
                        $target.remove();
                    }
                });
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
                                            '<input type="button" id="queryTeacher" class="btn brand-info" value="检索"/>'+
                                            '<label class="errorInfo addNewTeacherInput">请选择老师</label>'+
                                            '<div class="selectedBox" id="queryTeachResult"><ul></ul></div>'+
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
            arrData.push({name:'新建分组',id:''});
            var len=arrData.length;
            for(var i=0;i<len;i++){
                var item=arrData[i],
                    name=item.name;
                className='';
                    if(name.length>5){
                        name=name.substr(0,5)+'…';
                    }
                if(i==0){
                    className='selected';
                }
                if(i==len-1){
                    className='addNewOne';
                }
                str+='<li data-id='+item.id+' class="'+className+'" title="'+item.name+'"><div class="radioContainer">'+name+'</div></li>';
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
                var $li=modelContext.$panel.find('.allGroupNamesList .selected'),
                    groupInfo={groupId:$li.data('id'),groupName:$li.text()},
                    teacherInfo={
                        uid: 72,
                        nickname:'Leslie',
                        avatar: 'http://hisihi-avator.oss-cn-qingdao.aliyuncs.com/2015-03-26/551369fe8358c-05505543_256_256.jpg'
                    };
                myContext.execAddNewTeacher.call(myContext,groupInfo,teacherInfo);
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
            *执行搜索
            */
            modelContext.$panel.on('click','#queryTeacher',function() {
                //$.proxy(myContext, 'execQueryTeacher');
                myContext.execQueryTeacher(modelContext,myContext);
            });

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
        execAddNewTeacher:function(groupInfo,teacherInfo){
            var tempData = {
                    organization_id:this.organization_id,
                    uid:teacherInfo.uid,
                    teacher_group_id:groupInfo.groupId
                },
                url=this.basicApiUrl+'/addTeacherToGroup',
                that=this;
            Hisihi.getDataAsync({
                type: "post",
                url: url,
                data: tempData,
                callback:function(data){
                    if(data.success){
                        var member=[{
                            nickname:teacherInfo.nickname,
                            role:'成员',
                            uid:teacherInfo.uid,
                            avatar:teacherInfo.avatar
                        }];
                        var str = that.getSomeGroupMembersStr(member),
                            $allLi=that.$wrapper.find('#teacherMainCon .tItems'),
                            $li;
                        $allLi.each(function(){
                            if ($(this).data('name')==groupInfo.groupName){
                                $li = $(this);
                                return false;
                            }
                        });
                        $li.find('.memberItemUl .clearForMemberUl').before(str);
                    }
                    else{
                        if(data.error_code==-2){
                            alert('该老师已经添加过了');
                        }else {
                            alert('添加失败');
                        }
                    }
                }
            });
        },

        /*执行模糊查询*/
        execQueryTeacher:function(modelContext,myContext){
            var $input = modelContext.$panel.find('#addNewTeacherInput'),
                val = $input.val().replace(/(^\s*)|(\s*$)/g,'');
            if (val == '') {
                modelContext.$panel.find('.addNewTeacherInput').show().delay(500).hide(0);
                return;
            } else {
                var url = myContext.basicApiUrl + '/teachersFilter',
                    tempData = {
                        name: val
                    },
                    that = this;
                Hisihi.getDataAsync({
                    type: "post",
                    url: url,
                    data: tempData,
                    callback: function (data) {
                        myContext.fillInQueryTeacherResult.call(modelContext,data);
                    }
                });

            }
        },

        /*填充教师搜索结果*/
        fillInQueryTeacherResult:function(data) {
            if(data.success) {
                var $target = this.$panel.find('#queryTeachResult ul'),
                str='',
                tempData=data.data,
                len=tempData.length,
                item;
                for(var i=0;i<len;i++){
                    item=tempData[i];
                    str+='<li data-uid="'+item.uid+'">'+
                            '<img src="'+item.avatar+'"/>'+
                            '<span>'+item.nickname+'</span>'+
                         '</li>';
                }
                str+='<div style="clear:both;"></div>';
                $target.html(str);
            }else{
                alert("查询失败");
            }
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


    //var myTeacher=new MyTeacher($('.teachersWrapper'));
    //return myTeacher;
    var $wrapper=$('.teachersWrapper');
    if($wrapper.length>0) {
        new MyTeacher($wrapper);
    }

});