/**
 * Created by hisihi on 2016/10/26.
 */
define(['base'],function(Base){
    var Work=function(){
        var that = this;
        var eventsName='click',that=this;
        this.controlLoadingBox(false);
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