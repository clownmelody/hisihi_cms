
/*
*requirejs基本信息配置
*/

require.config({
    baseUrl:window.urlObject.js,
    paths: {
        jquery: 'libs/jquery-1.8.2.min',
        menus: 'custom/menus',
        announcement: 'custom/announcement',
        teacher:'custom/teacher',
        basicinfo:'custom/basicinfo'
});

require([
    'jquery',
    'menus',
    'teacher',
    'announcement',
    'basicinfo'
]);



require(['jquery', 'announcement'], function ($, announcement) {

});

