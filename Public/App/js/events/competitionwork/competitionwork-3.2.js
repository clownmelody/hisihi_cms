/**
 * Created by hisihi on 2016/10/26.
 */
define(['base','async'],function(Base,Async){

    var Work=function($wrapper,id,url){
        this.$wrapper=$wrapper;
        var that = this;
        this.id = id;
        this.baseUrl =  url;
        var eventsName='click',that=this;
        if(this.isLocal){
            eventsName='touched';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        };

        this.controlLoadingBox(false);
        window.setTimeout(function () {
            //that.���ط���();
        },100);
    }

    //������
    var config = {
        downloadBar: {
            show: true,
            pos: 1
        }
    };



    Work.prototype=new Base(config);
    Work.constructor=Work;
    var t=Work.prototype;

    //��ȡ�����








    return Work;
});