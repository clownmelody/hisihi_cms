(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var isMobileCheck;

isMobileCheck = require('./common/checkMobile.coffee');

if (!isMobileCheck() && window.top === window) {
  window.location.href = window.location.pathname.replace('apps', 'appDesktop');
}



},{"./common/checkMobile.coffee":2}],2:[function(require,module,exports){

/*
    Description: 判断用户手机 or PC端
 */
var isMobileCheck;

isMobileCheck = function() {
  var MobileUI, isMobile;
  MobileUI = {};
  MobileUI.userAgent = navigator.userAgent.toLowerCase();
  MobileUI.browser = {
    version: (MobileUI.userAgent.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/) || [])[1],
    safari: /webkit/.test(MobileUI.userAgent),
    opera: /opera/.test(MobileUI.userAgent),
    msie: /msie/.test(MobileUI.userAgent) && !/opera/.test(MobileUI.userAgent),
    mozilla: /mozilla/.test(MobileUI.userAgent) && !/(compatible|webkit)/.test(MobileUI.userAgent)
  };
  MobileUI.phoneList = new Array("2.0 mmp", "240320", "avantgo", "blackberry", "blazer", "cellphone", "danger", "docomo", "elaine/3.0", "eudoraweb", "hiptop", "iemobile", "kyocera/wx310k", "Llg/u990", "midp-2.0", "mmef20", "mot-v", "netfront", "newt", "nintendo wii", "nitro", "nokia", "opera mini", "opera mobi", "palm", "playstation portable", "portalmmm", "proxinet", "proxiNet", "sharp-tq-gx10", "small", "sonyericsson", "symbian", "ts21i-10", "up.browser", "up.link", "windows ce", "winwap", "iphone", "ipod", "windows phone", "htc", "ucweb", "mobile", "android");
  MobileUI.isOnTouchStart = function() {
    return typeof ontouchstart !== "undefined";
  };
  MobileUI.isiPad = function() {
    return MobileUI.userAgent.indexOf("ipad") >= 0;
  };
  MobileUI.isAndroid = function() {
    return MobileUI.userAgent.indexOf("Android") > -1 || MobileUI.userAgent.indexOf("android") > -1;
  };
  MobileUI.isiPhone = function() {
    return (navigator.platform.indexOf("iPhone") !== -1) || (navigator.platform.indexOf("iPod") !== -1);
  };
  MobileUI.isWinPhone = function() {
    return MobileUI.userAgent.indexOf("windows phone") !== -1;
  };
  MobileUI.isPhone = function() {
    var appNameList, i, _i, _j, _len, _len1, _ref;
    if (MobileUI.isOnTouchStart() && !MobileUI.isiPad()) {
      return true;
    }
    _ref = MobileUI.phoneList;
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      i = _ref[_i];
      if (MobileUI.userAgent.indexOf(MobileUI.phoneList[i]) >= 0 && MobileUI.userAgent.indexOf("ipad") === -1) {
        return true;
      }
    }
    appNameList = new Array("Microsoft Pocket Internet Explorer");
    for (_j = 0, _len1 = appNameList.length; _j < _len1; _j++) {
      i = appNameList[_j];
      if (MobileUI.userAgent.indexOf(appNameList[i]) >= 0) {
        return true;
      }
    }
    return false;
  };
  isMobile = function() {
    return MobileUI.isOnTouchStart() || MobileUI.isPhone() || MobileUI.isiPad();
  };
  return isMobile();
};

module.exports = isMobileCheck;



},{}]},{},[1]);