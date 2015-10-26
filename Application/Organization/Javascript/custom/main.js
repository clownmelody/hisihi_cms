
/*
*requirejs基本信息配置
*/

require.config({
    baseUrl:window.urlObject.js,
    shim: {
        'jqueryui': ['jquery'],
    },
    paths: {
        jquery: 'libs/jquery-1.8.2.min',
        jqueryui:'libs/jquery-ui-1.9.2.custom.min',
        jqueryform:'libs/jquery.form',
        jquerycookie:'libs/jquery.cookie',
        util:'custom/util',
        menus: 'custom/home',
        menus: 'custom/menus',
        announcement: 'custom/announcement',
        teacher:'custom/teacher',
        basicinfo:'custom/basicinfo'
}});

require([
    'jquery',
    'util',
    'jqueryui',
    'menus',
    'teacher',
    'announcement',
    'basicinfo'
]);



require(['jquery', 'announcement'], function ($, announcement) {

});

