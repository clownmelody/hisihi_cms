
/*
*requirejs基本信息配置
*/

require.config({
    baseUrl:window.urlObject.js,
    paths: {
        jquery: 'libs/jquery-1.8.2.min',
        menus: 'custom/menus',
        announcement: 'custom/announcement'
    }
});

require([
    'jquery',
    'menus',
    'announcement'
]);



require(['jquery', 'announcement'], function ($, announcement) {

});
