/**
 * Created by hisihi on 2016/10/26.
 */
define(['base'],function(Base){
    var Work=function(){
        if(this.isLocal){
            eventsName='touched';
            this.baseUrl=this.baseUrl.replace('api.php','hisihi-cms/api.php');
        };

        this.controlLoadingBox(true);
        window.setTimeout(function () {},100);
    }

    //œ¬‘ÿÃı
    var config = {
        downloadBar: {
            show: true,
            pos: 1
        }
    };

    Work.prototype=new Base(config);
    return Work;
});