
/*
*requirejs基本信息配置
*/

require.config({
    baseUrl:window.urlObject.js,
    paths: {
        jquery: 'libs/jquery-1.8.2.min',
<<<<<<< HEAD
        menus: 'custom/menus',
        announcement: 'custom/announcement'
=======
        announcement: 'custom/announcement',
        teacher:'custom/teacher'
>>>>>>> c44c9c8da5f70b306f517651e26cf92ebc1eefe6
    }
});

require([
<<<<<<< HEAD
    'jquery',
    'menus',
    'announcement'
]);



require(['jquery', 'announcement'], function ($, announcement) {

});
=======
    'jquery', 
    'announcement',
    'teacher'
]);

//require(['jquery', 'announcement'], function ($, announcement) {
//
//});
>>>>>>> c44c9c8da5f70b306f517651e26cf92ebc1eefe6
