
/*
*requirejs基本信息配置
*/

require.config({
    baseUrl:window.urlObject.js,
    shim: {
        'jqueryui': ['jquery'],
        'jqueryJcrop':['jquery'],
        'jqueryuploadify':['jquery'],
        'jqueryvalidate':['jquery'],
    },
    paths: {
        jquery: 'libs/jquery-1.8.2.min',
        jqueryui:'libs/jquery-ui-1.9.2.custom.min',
        jqueryform:'libs/jquery.form',
        jquerycookie:'libs/jquery.cookie',
        jqueryJcrop:'libs/jquery.Jcrop',
        jqueryuploadify:'libs/uploadify/jquery.uploadify.min',
        jqueryvalidate:'libs/jquery.validate',
        util:'custom/util',
        menus: 'custom/menus',
        announcement: 'custom/announcement',
        teacher:'custom/teacher',
        basicinfo:'custom/basicinfo',
        video:'custom/video',
        studentworks:'custom/studentworks',
        teachcondition:'custom/teachcondition',
        addnewlesson:'custom/addnewlesson',
        certification:'custom/certification'
}});

require([
    'jquery',
    'jqueryform',
    'jquerycookie',
    'jqueryuploadify',
    'jqueryvalidate',
    'util',
    'jqueryui',
    'jqueryJcrop',
    'menus',
    'teacher',
    'announcement',
    'basicinfo',
    'video',
    'studentworks',
    'teachcondition',
    'addnewlesson',
    'certification'
]);



//require(['jquery', 'announcement'], function ($, announcement) {
//
//});

