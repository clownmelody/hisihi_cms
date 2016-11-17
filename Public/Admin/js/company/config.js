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
    var city=$('#city').val(),
        cityArr = [],
        p = '',
        c = '';
    if (city) {
        cityArr = city.split(' ');
        if(cityArr.length==1){
            cityArr.splice(0,0,'');
        }
        if (cityArr[0]) {
            p = cityArr[0];
        }
        if (cityArr[1]) {
            c = cityArr[1];
        }
    }
    $('#addressProvince').val(p);
    $('#addressCity').val(c);
    new locationCard({
        ids: ['addressProvince', 'addressCity',''],
        targetSelector:'.location-info-box'
    }).init();
});