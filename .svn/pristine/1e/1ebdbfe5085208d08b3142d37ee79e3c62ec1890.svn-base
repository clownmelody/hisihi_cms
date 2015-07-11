$(document).ready(function () {
//平台、设备和操作系统
    var system = {
        win: false,
        mac: false,
        xll: false
    };
//检测平台
    var p = navigator.platform;
    system.win = p.indexOf("Win") == 0;
    system.mac = p.indexOf("Mac") == 0;
    system.x11 = (p == "X11") || (p.indexOf("Linux") == 0);
//跳转语句
    if (system.win || system.mac || system.xll) {//不跳转

    } else {
        window.location.href = "app/apps/";//手机
    }
});