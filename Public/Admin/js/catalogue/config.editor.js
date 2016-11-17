/**
 * Created by jimmy-jiang on 2016/11/14.
 */
requirejs.config({
    baseUrl:window.urlObj.js,
    paths: {
        jquery: 'jquery-1.8.2.min',
        jqueryform:'jquery.form',
        'simple-module': 'editor/module',
        'simple-uploader': 'editor/uploader',
        'simple-hotkeys': 'editor/hotkeys',
        'simditor': 'editor/simditor'
    }
});
require(['jqueryform','simditor'],function(){
    $(function(){

        /**定义一个MyEditor对象**/
        var MyEditor=function(){
            this.init();
            /*上传文件*/
            $(document).on('change', '#uploadImgFile', $.proxy(this,'initUploadImg'));
        };
        MyEditor.prototype={

            init:function(){
                var $editor = $('#my-editor'),
                    toolbar = ['title', 'bold', 'italic', 'underline', 'fontScale', 'color', '|',
                        'ol', 'ul', 'blockquote', 'table', '|',
                        'link', 'image', 'hr', '|',
                        'indent', 'outdent', 'alignment'
                    ];
                //Simditor.locale = 'en-US'
                this.editor = new Simditor({
                    textarea: $editor,
                    toolbar:toolbar,
                    toolbarFloat: true,
                    cleanPaste:false
                });
                window.setTimeout(function () {
                    $editor.add().css('opacity', '1');
                }, 200);
                this.overWriteImgBtnFn(toolbar);
                this.initImgsArr();  //定义100个图片id 数组。

                this.getBasicToken();
            },

            //得到id 最大
            getMaxImgsId:function(){
                var len = this.editorImgsArr.length;
                for(var i=0;i<len;i++){
                    var item=this.editorImgsArr[i];
                    if(item.status==0){
                        item.status=1;
                        return {id:item.id,index:i};
                    }
                }
                return false;
            },

            setValue:function(val){
                this.editor.setValue(val);
            },

            getValue:function(){
              return this.editor.getValue();
            },

            /*重写编辑器的上传图片的方法*/
            overWriteImgBtnFn:function(arr) {
                this.btn = this.editor.toolbar.buttons[this.getImageBtnIndex(arr)];
                var that = this;
                this.btn.createImage = function (url, maxId) {
                    var range;
                    if (url == null) {
                        url = 'http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihiOrgLogo.png';
                    }
                    if (!this.editor.inputManager.focused) {
                        this.editor.focus();
                    }
                    range = this.editor.selection.range();
                    range.deleteContents();
                    this.editor.selection.range(range);
                    var $img = $('<img id="' + maxId + '">').attr('src', url);
                    range.insertNode($img[0]);
                    this.editor.selection.setRangeAfter($img, range);
                    this.editor.trigger('valuechanged');
                    return $img;
                };

                this.btn.command = function () {
                    //上传图片，然后回调
                    var info = that.getMaxImgsId();
                    if (info) {
                        that.btn.createImage('', info.id);
                        $("#uploadImgFile").trigger('click');
                    } else {
                        alert('最多只能添加100张图片');
                    }
                };
            },

            /*得到图片按钮的下标*/
            getImageBtnIndex:function(arr){
                var tempNum=0;
                var len=arr.length;
                for(var i=0;i<len;i++){
                    if(arr[i]=='|'){
                        tempNum++;
                    }
                    if(arr[i]=='image'){
                        return (i-tempNum);
                    }
                }
            },

            /*
             * 由于编辑器上传图片成功回调时，光标丢失，或者不在插入图片的位置，
             * 导致了图上都会出现在文章的最前面，
             * 所以想通过给图片定义id，先将空的图片插入到相应的位置，
             * 图片上传成功后，再找到图片，修改url。但是光标还是没有办法自动到图片的后面，
             * 后期修改
             * */
            initImgsArr:function(){
                this.editorImgsArr=[];
                for(var i=0;i<100;i++){
                    var tempObj={
                        id:'editor-img-'+i,
                        status:0
                    };
                    this.editorImgsArr.push(tempObj);
                }
            },

            //上传表单
            initUploadImg:function(e){
                this.controlLoadingCircleStatus(true);
                var target = $(e.currentTarget),
                    that=this;
                if (target.val() == "") return;
                var $formObj = $('#upImgForm'),
                    tokenStr = window.localStorage.getItem('cms-token'); //myToken,
                try{
                    var ajax_option= {
                        url:window.urlObj.apiUrl + 'v1/file',//默认是form action
                        headers: {'Authorization':tokenStr},
                        success: function (data) {
                            data=JSON.parse(data);
                            //var $img = that.btn.createImage.call(that.btn, data.filedata);
                            var info=that.getMaxImgsId();
                            if(info) {
                                info=that.editorImgsArr[info.index-1];
                                var $img = $('#'+info.id).attr('src', data.filedata);
                                $img[0].onload = function () {
                                    that.controlLoadingCircleStatus(false);
                                };
                            }
                            $formObj[0].reset();
                        },error:function(result){
                            alert('图片上传失败');
                            $formObj[0].reset();
                        }
                    };
                    $formObj.ajaxSubmit(ajax_option);
                }catch(ex){
                    alert('token获取失败');
                }

        },

        /*
         *控制旋转圈圈的显示和隐藏
         * para:
         * info - {object}
         */
        controlLoadingCircleStatus:function(flag,$target){
            if(!$target){
                $target=$('#imgLoadingCircle');
            }
            if(flag) {
                $target.addClass('active').parent().show();
            }else{
                $target.removeClass('active').parent().hide();
            }
        },



        /*请求数据 python*/
        getDataAsyncPy: function (paras) {
            if (!paras.type) {
                paras.type = 'post';
            }
            if (paras.async==undefined) {
                paras.async = true;
            }
            var that = this;
            var xhr = $.ajax({
                async:paras.async,
                url: paras.url,
                type: paras.type,
                data: paras.paraData,
                //timeout: 20000,
                timeout: 10000,
                contentType: 'application/json',
                beforeSend: function (myXhr) {
                    //自定义 头信息
                    if(paras.beforeSend){
                        paras.beforeSend(myXhr);
                    }else {
                        //将token加入到请求的头信息中
                        if (paras.needToken) {
                            myXhr.setRequestHeader('Authorization', paras.token);  //设置头消息
                        }
                    }
                },
                complete: function (xmlRequest, status) {
                    var rTxt = xmlRequest.responseText,
                        result = {};
                    if (rTxt) {
                        result = JSON.parse(xmlRequest.responseText);

                    } else {
                        result.code = 0;

                    }
                    if (status == 'success') {

                        paras.sCallback(result);

                    }
                    //超时
                    else if (status == 'timeout') {
                        xhr.abort();
                        paras.eCallback && paras.eCallback({code:'408',txt:'超时'});
                    }
                    else {
                        if(!result){
                            result={code: '404', txt: 'no found'};
                        }
                        paras.eCallback && paras.eCallback(result);
                    }
                }
            });

        },

        /*获得令牌*/
        getBasicToken:function(){
            console.log(window.urlObj.apiUrl);
            var that=this,
                para = {
                    async:true,
                    url: window.urlObj.apiUrl+'/v1/token',
                    type: 'post',
                    paraData: JSON.stringify({account:'jg2rw2xVjyrgbrZp', secret:'VbkzpPlZ6H4OvqJW', type: 100}),
                    sCallback: function (data) {
                        that.token =that.getBase64encode(data.token);
                    },eCallback:function(result){
                       console.log(result.txt);
                    }
                };
            this.getDataAsyncPy(para);
        },

        /***************64编码的方法****************/
        getBase64encode:function(str) {
            str+= ':'
            var out, i, len, base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
            var c1, c2, c3;
            len = str.length;
            i = 0;
            out = "";
            while (i < len) {
                c1 = str.charCodeAt(i++) & 0xff;
                if (i == len) {
                    out += base64EncodeChars.charAt(c1 >> 2);
                    out += base64EncodeChars.charAt((c1 & 0x3) << 4);
                    out += "==";
                    break;
                }
                c2 = str.charCodeAt(i++);
                if (i == len) {
                    out += base64EncodeChars.charAt(c1 >> 2);
                    out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
                    out += base64EncodeChars.charAt((c2 & 0xF) << 2);
                    out += "=";
                    break;
                }
                c3 = str.charCodeAt(i++);
                out += base64EncodeChars.charAt(c1 >> 2);
                out += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
                out += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
                out += base64EncodeChars.charAt(c3 & 0x3F);
            }
            return 'basic '+ out;
        },

            CLASS_NAME:'MyEditor'
        };



        var editor;
        initEditor();
        setEditorVal();
        function initEditor() {
            editor=new MyEditor();
        }

        //初始化编辑器内容
        function setEditorVal(){
            var val=$('#target-area').text();
            editor.setValue(val);
        }

        //提交编辑
        window.setTextAreaData=function(){
            var htmlStr=editor.getValue();
            $('#target-area').text(htmlStr);
            $('.submit-btn').trigger('click');
        };

    });
});