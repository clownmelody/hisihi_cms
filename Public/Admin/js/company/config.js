/**
 * Created by jimmy-jiang on 2016/11/14.
 */
requirejs.config({
    baseUrl:window.urlObj.js,
    paths: {
        jquery: 'jquery-1.8.2.min',
        areadata:'company/areadata',
        areaselect:'company/areaselect'
    }
});
require(['jquery','areaselect','areadata'],function($,locationCard){
    /*地区选择*/
    new locationCard({
        ids: ['addressProvince', 'addressCity',''],
        targetSelector:'.location-info-box'
    }).init();
});