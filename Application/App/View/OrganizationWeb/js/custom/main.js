/// <reference path="../../libs/require.js" />
require.config({
    baseUrl: '../js/',
    paths: {
        jquery: 'libs/jquery-1.8.2.min',
        drawLine: 'custom/drawline',
        drawCircle: 'custom/drawcircle'
    }
});

require([
    'jquery', 
    'drawLine',
    'drawCircle',
]);

//require(['jquery', 'drawCircle', 'drawLine'], function ($, myDrawCircle, myDrawLine) {
//    myDrawLine.drawNormalLine();
//});