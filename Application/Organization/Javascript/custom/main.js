require.config({
    baseUrl:window.urlObject.js,
    paths: {
        jquery: 'libs/jquery-1.8.2.min',
        announcement: 'custom/announcement'
    }
});

require([
    'jquery', 
    'announcement'
]);

require(['jquery', 'announcement'], function ($, announcement) {

});