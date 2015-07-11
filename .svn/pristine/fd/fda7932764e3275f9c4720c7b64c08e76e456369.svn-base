(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var IDX, NAMEMAPPING, bounce, css;

css = window.RP.Css;

bounce = {};

IDX = 0;

NAMEMAPPING = {};

bounce.getAnimationName = function(translateX, translateY) {
  var key, name;
  key = 'translateX' + translateX + '-translateY' + translateY;
  name = NAMEMAPPING[key];
  if (!name) {
    name = NAMEMAPPING[key] = 'bounce' + IDX++;
  }
  return name;
};

bounce.start = function($el, duration) {
  var name, translateX, translateY, _ref, _ref1;
  css.disableAnimation($el[0]);
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  translateX = (_ref = styler.x) != null ? _ref : css.getX($el[0]);
  translateY = (_ref1 = styler.y) != null ? _ref1 : css.getY($el[0]);
  name = this.getAnimationName(translateX, translateY);
  css.css($el[0], {
    '-webkit-animation': name + ' 2s  backwards'
  });
  return css.regKeyFrames(name, '{0% { -webkit-transform: translateX(' + (5 + translateX) + 'px) translateY(' + (100 + translateY) + 'px); transform: translateX(' + (5 + translateX) + 'px) translateY(' + (100 + translateY) + 'px);} 5%{ -webkit-transform: translateX(' + (10 + translateX) + 'px) translateY(' + (150 + translateY) + 'px);  transform: translateX(' + (10 + translateX) + 'px) translateY(' + (150 + translateY) + 'px); } 15%{ -webkit-transform: scale(0.8) translateX(' + (15 + translateX) + 'px) translateY(' + (200 + translateY) + 'px); transform: scale(0.8) translateX(' + (15 + translateX) + 'px) translateY(' + (200 + translateY) + 'px);} 30%{ -webkit-transform: scale(1) translateX(' + (20 + translateX) + 'px) translateY(' + (-50 + translateY) + 'px); transform: scale(1) translateX(' + (18 + translateX) + 'px) translateY(' + (-50 + translateY) + 'px);} 40%{ -webkit-transform: scale(1) translateX(' + (20 + translateX) + 'px) translateY(' + (-100 + translateY) + 'px); transform: scale(1) translateX(' + (20 + translateX) + 'px) translateY(' + (-100 + translateY) + 'px);} 50%{ -webkit-transform: translateX(' + (25 + translateX) + 'px) translateY(' + (-150 + translateY) + 'px); transform: translateX(' + (25 + translateX) + 'px) translateY(' + (-150 + translateY) + 'px);} 60%{ -webkit-transform: ttranslateX(' + (30 + translateX) + 'px) translateY(' + (-100 + translateY) + 'px); transform: translateX(' + (30 + translateX) + 'px) translateY(' + (-100 + translateY) + 'px);} 70%{ -webkit-transform: translateX(' + (35 + translateX) + 'px) translateY(' + (-50 + translateY) + 'px); transform: translateX(' + (35 + translateX) + 'px) translateY(' + (-50 + translateY) + 'px);} 80%{ -webkit-transform: scale(0.9) translateX(' + (40 + translateX) + 'px) translateY(' + (0 + translateY) + 'px); transform: scale(0.9) translateX(' + (40 + translateX) + 'px) translateY(' + (0 + translateY) + 'px);} 90%{ -webkit-transform: scale(1) translateX(' + (45 + translateX) + 'px) translateY(' + (-50 + translateY) + 'px); transform: scale(1) translateX(' + (45 + translateX) + 'px) translateY(' + (-50 + translateY) + 'px);} 100% { -webkit-transform: translateX(' + (50 + translateX) + 'px) translateY(' + (0 + translateY) + 'px); transform: translateX(' + (50 + translateX) + 'px) translateY(' + (0 + translateY) + 'px); } }');

  /*setTimeout ->
      css.disableAnimation $el[0]
  ,duration*1000
   */
};

bounce.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      x: css.getX($el[0]),
      y: css.getY($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
    ({
      y: style.y - 200,
      x: style.x - 50
    });
  }
  return css.rmKeyFrames($el[0]);
};

module.exports = bounce;



},{}],2:[function(require,module,exports){
var IDX, NAMEMAPPING, bounceIn, css;

css = window.RP.Css;

bounceIn = {};

IDX = 0;

NAMEMAPPING = {};

bounceIn.getAnimationName = function(scaleX, scaleY, translateX, translateY) {
  var key, name;
  if (scaleX == null) {
    scaleX = 1;
  }
  if (scaleY == null) {
    scaleY = 1;
  }
  key = 'scaleX-' + scaleX + '-scaleY' + scaleY + '-translateX' + translateX + '-translateY' + translateY;
  name = NAMEMAPPING[key];
  if (!name) {
    name = NAMEMAPPING[key] = 'bounceIn' + IDX++;
  }
  return name;
};

bounceIn.start = function($el, duration) {
  var name, scale0, scale10, scale5, scale7, scaleX, scaleY, styler, translateStr, translateX, translateY, _ref, _ref1, _ref2, _ref3;
  if (duration == null) {
    duration = 1;
  }
  css.disableAnimation($el[0]);
  styler = JSON.parse($el.attr("data-style-cache"));
  css.css($el[0], styler);
  scaleX = (_ref = styler.scaleX) != null ? _ref : 1;
  scaleY = (_ref1 = styler.scaleY) != null ? _ref1 : 1;
  scale0 = 'scaleX(' + (scaleX * 0.3) + ') scaleY(' + (scaleY * 0.3) + ')';
  scale5 = 'scaleX(' + (scaleX * 1.05) + ') scaleY(' + (scaleY * 1.05) + ')';
  scale7 = 'scaleX(' + (scaleX * 0.9) + ') scaleY(' + (scaleY * 0.9) + ')';
  scale10 = 'scaleX(' + (scaleX * 1) + ') scaleY(' + (scaleY * 1) + ')';
  translateX = (_ref2 = styler.x) != null ? _ref2 : css.getX($el[0]);
  translateY = (_ref3 = styler.y) != null ? _ref3 : css.getY($el[0]);
  translateStr = 'translateX(' + translateX + 'px) translateY(' + translateY + 'px)';
  name = this.getAnimationName(scaleX, scaleY, translateX, translateY);
  css.css($el[0], {
    '-webkit-animation': name + ' ' + duration + 's backwards',
    'animation': name + ' ' + duration + 's backwards'
  });
  return css.regKeyFrames(name, '{ 0%{opacity:0; -webkit-transform: ' + translateStr + ' ' + scale0 + '; transform: ' + translateStr + ' ' + scale0 + ';} 50%{opacity:1;transform: ' + translateStr + ' ' + scale5 + '; -webkit-transform: ' + translateStr + ' ' + scale5 + ';} 70%{transform: ' + translateStr + ' ' + scale7 + '; -webkit-transform: ' + translateStr + ' ' + scale7 + ';} 100%{transform: ' + translateStr + ' ' + scale10 + '; -webkit-transform: ' + translateStr + ' ' + scale10 + ';} }');

  /*setTimeout ->
      css.disableAnimation $el[0]
  , duration * 1000
   */
};

bounceIn.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      scaleX: css.getScaleX($el[0]),
      scaleY: css.getScaleY($el[0]),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  css.css($el[0], {
    opacity: 0
  });
  return css.rmKeyFrames($el[0]);
};

module.exports = bounceIn;



},{}],3:[function(require,module,exports){

/*
	Last Edit: Jensen 0920
    Description: Everytime You Add an Effect File e.g. ./flashing.coffee,
    	You Should Add Require Path in This File
 */
var effects;

effects = {};

effects.show = require("./show.coffee");

effects.fade = require("./fade.coffee");

effects.fadeFromTop = require("./fadeFromTop.coffee");

effects.fadeFromRight = require("./fadeFromRight.coffee");

effects.fadeFromBottom = require("./fadeFromBottom.coffee");

effects.fadeFromLeft = require("./fadeFromLeft.coffee");

effects.fromtop = require("./fromtop.coffee");

effects.fromright = require("./fromright.coffee");

effects.frombottom = require("./frombottom.coffee");

effects.fromleft = require("./fromleft.coffee");

effects.rainy = require("./rainy.coffee");

effects.rotation = require("./rotation.coffee");

effects.spread = require("./spread.coffee");

effects.flashing = require("./flashing.coffee");

effects.bounceIn = require("./bounceIn.coffee");

effects.erase = require("./erase.coffee");

effects.bounce = require("./bounce.coffee");

effects.jitter = require("./jitter.coffee");

effects.light = require("./light.coffee");

effects.rotation2d = require("./rotation2d.coffee");

effects.small2big = require("./small2big.coffee");

window.RP.effects = effects;



},{"./bounce.coffee":1,"./bounceIn.coffee":2,"./erase.coffee":4,"./fade.coffee":5,"./fadeFromBottom.coffee":6,"./fadeFromLeft.coffee":7,"./fadeFromRight.coffee":8,"./fadeFromTop.coffee":9,"./flashing.coffee":10,"./frombottom.coffee":11,"./fromleft.coffee":12,"./fromright.coffee":13,"./fromtop.coffee":14,"./jitter.coffee":15,"./light.coffee":16,"./rainy.coffee":17,"./rotation.coffee":19,"./rotation2d.coffee":20,"./show.coffee":21,"./small2big.coffee":22,"./spread.coffee":23}],4:[function(require,module,exports){
var StackBlur, css, erase, _initErase;

StackBlur = require("./stackblur.js");

css = window.RP.Css;

erase = {};

_initErase = function($el, $canvas, ctx) {
  var elH, elL, elT, elW, sX, sY, timeout, touchend, touchmove, touchstart, x1, y1;
  x1 = 0;
  y1 = 0;
  timeout = 0;
  sX = css.getScaleX($el[0]);
  sY = css.getScaleY($el[0]);
  elL = css.getX($el[0]);
  elT = css.getY($el[0]);
  elW = parseInt($el.css('width').replace('px', ''));
  elH = parseInt($el.css('height').replace('px', ''));
  touchstart = (function(_this) {
    return function(e) {
      clearTimeout(timeout);
      e.preventDefault();
      e.stopPropagation();
      try {
        x1 = (e.targetTouches[0].pageX - elL - (1 - sX) * elW / 2) / sX;
        y1 = (e.targetTouches[0].pageY - elT - (1 - sY) * elH / 2) / sY;
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
        ctx.lineWidth = 50;
        ctx.globalCompositeOperation = "destination-out";
        return $canvas.on('touchmove', touchmove);
      } catch (_error) {
        e = _error;
      }
    };
  })(this);
  touchmove = (function(_this) {
    return function(e) {
      var x2, y2;
      clearTimeout(timeout);
      e.preventDefault();
      e.stopPropagation();
      try {
        x2 = (e.targetTouches[0].pageX - elL - (1 - sX) * elW / 2) / sX;
        y2 = (e.targetTouches[0].pageY - elT - (1 - sY) * elH / 2) / sY;
        ctx.save();
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
        ctx.stroke();
        ctx.restore();
        x1 = x2;
        return y1 = y2;
      } catch (_error) {
        e = _error;
      }
    };
  })(this);
  touchend = (function(_this) {
    return function(e) {
      var timeoutFn;
      $canvas.off('touchmove', touchmove);
      e.preventDefault();
      e.stopPropagation();
      timeoutFn = function() {
        var canvas, dd, i, imgData, interval, x, y, _i, _j, _ref, _ref1;
        canvas = $canvas[0];
        imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        interval = 30;
        dd = 0;
        for (x = _i = 0, _ref = imgData.width; interval > 0 ? _i <= _ref : _i >= _ref; x = _i += interval) {
          for (y = _j = 0, _ref1 = imgData.height; interval > 0 ? _j <= _ref1 : _j >= _ref1; y = _j += interval) {
            i = (y * imgData.width + x) * 4;
            if (imgData.data[i + 3] > 0) {
              dd++;
            }
          }
        }
        if (dd / (imgData.width * imgData.height / (interval * interval)) < 0.7) {
          $canvas.addClass('hide');
          $canvas.off('touchstart', touchstart);
          return $canvas.off('touchend', touchend);
        }
      };
      return timeout = setTimeout(timeoutFn, 100);
    };
  })(this);
  $canvas.on('touchstart', touchstart);
  return $canvas.on('touchend', touchend);
};

erase.start = function($el, duration) {};

erase.ready = function($el) {
  var $canvas, ctx, idx, img, ori, path, src;
  if ($('div.simulator').length <= 0) {
    src = $el.attr('src');
    if (src && $el.hasClass('image')) {
      if ($el[0].$canvas) {
        $el[0].$canvas.remove();
        $el[0].$canvas = void 0;
      }
      $canvas = $('<canvas></canvas>');
      $el.parent().append($canvas);
      $el[0].$canvas = $canvas;
      ctx = $canvas[0].getContext('2d');
      img = new Image();
      if ((src.indexOf('http://')) !== -1) {
        path = src.substring(7);
        idx = path.indexOf('/');
        ori = 'http://' + (path.substring(0, idx + 1));
        img.crossOrigin = ori;
      }
      img.src = src + '?_=' + (new Date()).getTime();
      img.onload = (function(_this) {
        return function() {
          $canvas.attr('width', $el.css('width'));
          $canvas.attr('height', $el.css('height'));
          $canvas.css('position', 'absolute');
          $canvas.css('-webkit-transform', $el.css('-webkit-transform'));
          $canvas.css('top', $el.css('top'));
          $canvas.css('left', $el.css('left'));
          $canvas.css('z-index', parseInt($el.css('z-index')) + 1);
          ctx.globalCompositeOperation = "source-over";
          ctx.drawImage(img, 0, 0, $canvas[0].width, $canvas[0].height);
          ctx.globalCompositeOperation = "destination-out";
          return StackBlur.stackBlurCanvasRGBA($canvas[0], 0, 0, $canvas[0].width, $canvas[0].height, 25);
        };
      })(this);
      return _initErase($el, $canvas, ctx);
    }
  }
};

module.exports = erase;



},{"./stackblur.js":24}],5:[function(require,module,exports){
var css, fade;

css = window.RP.Css;

fade = {};

fade.start = function($el, duration) {
  css.enableAnimation($el[0], 2, "ease-out");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, duration * 1000);
};

fade.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    x: style.x,
    y: style.y
  });
};

module.exports = fade;



},{}],6:[function(require,module,exports){
var css, fadeFromBottom;

css = window.RP.Css;

fadeFromBottom = {};

fadeFromBottom.start = function($el, duration) {
  css.enableAnimation($el[0], 0.8, "ease");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, 0.8 * 1000);
};

fadeFromBottom.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    y: style.y + 75
  });
};

module.exports = fadeFromBottom;



},{}],7:[function(require,module,exports){
var css, fadeFromleft;

css = window.RP.Css;

fadeFromleft = {};

fadeFromleft.start = function($el, duration) {
  if (duration == null) {
    duration = 1;
  }
  css.enableAnimation($el[0], 0.8, "ease");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, 0.8 * 1000);
};

fadeFromleft.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    x: style.x - 75
  });
};

module.exports = fadeFromleft;



},{}],8:[function(require,module,exports){
var css, fromright;

css = window.RP.Css;

fromright = {};

fromright.start = function($el, duration) {
  css.enableAnimation($el[0], 0.8, "ease");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, 0.8 * 1000);
};

fromright.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    x: style.x + 75
  });
};

module.exports = fromright;



},{}],9:[function(require,module,exports){
var css, fadeFromtop;

css = window.RP.Css;

fadeFromtop = {};

fadeFromtop.start = function($el, duration) {
  css.enableAnimation($el[0], 0.8, "ease");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, 0.8 * 1000);
};

fadeFromtop.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    y: style.y - 75
  });
};

module.exports = fadeFromtop;



},{}],10:[function(require,module,exports){

/*
    Jensen 0920
    Description: Infinite Flashing Animation
    Version 0.1.0
 */
var css, flashing;

css = window.RP.Css;

flashing = {};

flashing.start = function($el, duration) {
  css.enableAnimation($el[0], duration);
  css.css($el[0], {
    '-webkit-animation': 'flashing ' + duration + 's'
  });
  css.regKeyFrames('flashing', '{ 0% {background: red;opacity:1} 50% {background:yellow;opacity:1} 100% {background:blue;opacity:1} }');
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, duration * 1000);
};

flashing.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index")
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.rmKeyFrames($el[0]);
};

module.exports = flashing;



},{}],11:[function(require,module,exports){
var css, frombottom;

css = window.RP.Css;

frombottom = {};

frombottom.start = function($el, duration) {
  css.enableAnimation($el[0], duration, "ease");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, duration * 1000);
};

frombottom.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    y: style.y + 200
  });
};

module.exports = frombottom;



},{}],12:[function(require,module,exports){
var css, fromleft;

css = window.RP.Css;

fromleft = {};

fromleft.start = function($el, duration) {
  css.enableAnimation($el[0], duration, "ease", "backwards");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, duration * 1000);
};

fromleft.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      x: css.getX($el[0]),
      y: css.getY($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    x: style.x - 200
  });
};

module.exports = fromleft;



},{}],13:[function(require,module,exports){
var css, fromright;

css = window.RP.Css;

fromright = {};

fromright.start = function($el, duration) {
  css.enableAnimation($el[0], duration, "ease");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, duration * 1000);
};

fromright.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      x: css.getX($el[0]),
      y: css.getY($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    x: style.x + 200
  });
};

module.exports = fromright;



},{}],14:[function(require,module,exports){
var css, fromtop;

css = window.RP.Css;

fromtop = {};

fromtop.start = function($el, duration) {
  css.enableAnimation($el[0], duration, "ease");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, duration * 1000);
};

fromtop.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    y: style.y - 200
  });
};

module.exports = fromtop;



},{}],15:[function(require,module,exports){
var IDX, NAMEMAPPING, css, jitter;

css = window.RP.Css;

jitter = {};

IDX = 0;

NAMEMAPPING = {};

jitter.getAnimationName = function(scaleX, scaleY, translateX, translateY) {
  var key, name;
  if (scaleX == null) {
    scaleX = 1;
  }
  if (scaleY == null) {
    scaleY = 1;
  }
  key = 'scaleX-' + scaleX + '-scaleY' + scaleY + '-translateX' + translateX + '-translateY' + translateY;
  name = NAMEMAPPING[key];
  if (!name) {
    name = NAMEMAPPING[key] = 'jitter' + IDX++;
  }
  return name;
};

jitter.start = function($el, duration) {
  var name, scaleStr, scaleX, scaleY, styler, translateX, translateY, _ref, _ref1, _ref2, _ref3;
  css.disableAnimation($el[0]);
  styler = JSON.parse($el.attr("data-style-cache"));
  css.css($el[0], styler);
  scaleX = (_ref = styler.scaleX) != null ? _ref : 1;
  scaleY = (_ref1 = styler.scaleY) != null ? _ref1 : 1;
  translateX = (_ref2 = styler.x) != null ? _ref2 : css.getX($el[0]);
  translateY = (_ref3 = styler.y) != null ? _ref3 : css.getY($el[0]);
  name = this.getAnimationName(scaleX, scaleY, translateX, translateY);
  scaleStr = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
  css.css($el[0], {
    '-webkit-animation': name + ' ' + duration + 's' + " backwards ",
    'animation': name + ' ' + duration + 's' + " backwards "
  });
  return css.regKeyFrames(name, '{ 0% { -webkit-transform: translate(' + (0 + translateX) + 'px, ' + (0 + translateY) + 'px) rotate(0deg) ' + scaleStr + '; transform: translate(' + (0 + translateX) + 'px, ' + (0 + translateY) + 'px) rotate(0deg) ' + scaleStr + ';} 2% { -webkit-transform: translate(' + (-1 + translateX) + 'px, ' + (3 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; transform: translate(' + (-1 + translateX) + 'px, ' + (3 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; } 4% { -webkit-transform: translate(' + (-4 + translateX) + 'px, ' + (5 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; transform: translate(' + (-4 + translateX) + 'px, ' + (5 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; } 6% { -webkit-transform: translate(' + (-1 + translateX) + 'px, ' + (6 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; transform: translate(' + (-1 + translateX) + 'px, ' + (6 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; } 8% { -webkit-transform: translate(' + (5 + translateX) + 'px, ' + (-4 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (5 + translateX) + 'px, ' + (-4 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; } 10% { -webkit-transform: translate(' + (-7 + translateX) + 'px, ' + (-3 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (-7 + translateX) + 'px, ' + (-3 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; } 12% { -webkit-transform: translate(' + (-1 + translateX) + 'px, ' + (8 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; transform: translate(' + (-1 + translateX) + 'px, ' + (8 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; } 14% { -webkit-transform: translate(' + (3 + translateX) + 'px, ' + (-5 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; transform: translate(' + (3 + translateX) + 'px, ' + (-5 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; } 16% { -webkit-transform: translate(' + (1 + translateX) + 'px, ' + (0 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; transform: translate(' + (1 + translateX) + 'px, ' + (0 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; } 18% { -webkit-transform: translate(' + (-6 + translateX) + 'px, ' + (-10 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; transform: translate(' + (-6 + translateX) + 'px, ' + (-10 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; } 20% { -webkit-transform: translate(' + (3 + translateX) + 'px, ' + (-2 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; transform: translate(' + (3 + translateX) + 'px, ' + (-2 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; } 22% { -webkit-transform: translate(' + (0 + translateX) + 'px, ' + (0 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; transform: translate(' + (0 + translateX) + 'px, ' + (0 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; } 24% { -webkit-transform: translate(' + (-5 + translateX) + 'px, ' + (-4 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; transform: translate(' + (-5 + translateX) + 'px, ' + (-4 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; } 26% { -webkit-transform: translate(' + (-1 + translateX) + 'px, ' + (3 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (-1 + translateX) + 'px, ' + (3 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; } 28% { -webkit-transform: translate(' + (1 + translateX) + 'px, ' + (1 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (1 + translateX) + 'px, ' + (1 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; } 30% { -webkit-transform: translate(' + (-4 + translateX) + 'px, ' + (8 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; transform: translate(' + (-4 + translateX) + 'px, ' + (8 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; } 32% { -webkit-transform: translate(' + (-9 + translateX) + 'px, ' + (7 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (-9 + translateX) + 'px, ' + (7 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; } 34% { -webkit-transform: translate(' + (4 + translateX) + 'px, ' + (-9 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; transform: translate(' + (4 + translateX) + 'px, ' + (-9 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; } 36% { -webkit-transform: translate(' + (1 + translateX) + 'px, ' + (-6 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; transform: translate(' + (1 + translateX) + 'px, ' + (-6 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; } 38% { -webkit-transform: translate(' + (-4 + translateX) + 'px, ' + (0 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; transform: translate(' + (-4 + translateX) + 'px, ' + (0 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; } 40% { -webkit-transform: translate(' + (3 + translateX) + 'px, ' + (-7 + translateY) + 'px) rotate(0.5deg) ' + scaleStr + '; transform: translate(' + (3 + translateX) + 'px, ' + (-7 + translateY) + 'px) rotate(0.5deg) ' + scaleStr + '; } 42% { -webkit-transform: translate(' + (4 + translateX) + 'px, ' + (4 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; transform: translate(' + (4 + translateX) + 'px, ' + (4 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; } 44% { -webkit-transform: translate(' + (8 + translateX) + 'px, ' + (-4 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; transform: translate(' + (8 + translateX) + 'px, ' + (-4 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; } 46% { -webkit-transform: translate(' + (9 + translateX) + 'px, ' + (9 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (9 + translateX) + 'px, ' + (9 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; } 48% { -webkit-transform: translate(' + (6 + translateX) + 'px, ' + (-8 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; transform: translate(' + (6 + translateX) + 'px, ' + (-8 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; } 50% { -webkit-transform: translate(' + (-1 + translateX) + 'px, ' + (4 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (-1 + translateX) + 'px, ' + (4 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; } 52% { -webkit-transform: translate(' + (4 + translateX) + 'px, ' + (6 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; transform: translate(' + (4 + translateX) + 'px, ' + (6 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; } 54% { -webkit-transform: translate(' + (9 + translateX) + 'px, ' + (-3 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; transform: translate(' + (9 + translateX) + 'px, ' + (-3 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; } 56% { -webkit-transform: translate(' + (8 + translateX) + 'px, ' + (-2 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (8 + translateX) + 'px, ' + (-2 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; } 58% { -webkit-transform: translate(' + (-2 + translateX) + 'px, ' + (-9 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; transform: translate(' + (-2 + translateX) + 'px, ' + (-9 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; } 60% { -webkit-transform: translate(' + (-1 + translateX) + 'px, ' + (-5 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; transform: translate(' + (-1 + translateX) + 'px, ' + (-5 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; } 62% { -webkit-transform: translate(' + (-8 + translateX) + 'px, ' + (3 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; transform: translate(' + (-8 + translateX) + 'px, ' + (3 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; } 64% { -webkit-transform: translate(' + (6 + translateX) + 'px, ' + (-2 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (6 + translateX) + 'px, ' + (-2 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; } 66% { -webkit-transform: translate(' + (-5 + translateX) + 'px, ' + (9 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; transform: translate(' + (-5 + translateX) + 'px, ' + (9 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; } 68% { -webkit-transform: translate(' + (3 + translateX) + 'px, ' + (1 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; transform: translate(' + (3 + translateX) + 'px, ' + (1 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; } 70% { -webkit-transform: translate(' + (6 + translateX) + 'px, ' + (4 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; transform: translate(' + (6 + translateX) + 'px, ' + (4 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; } 72% { -webkit-transform: translate(' + (-6 + translateX) + 'px, ' + (-5 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; transform: translate(' + (-6 + translateX) + 'px, ' + (-5 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; } 74% { -webkit-transform: translate(' + (-8 + translateX) + 'px, ' + (0 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; transform: translate(' + (-8 + translateX) + 'px, ' + (0 + translateY) + 'px) rotate(-0.5deg) ' + scaleStr + '; } 76% { -webkit-transform: translate(' + (-5 + translateX) + 'px, ' + (-8 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; transform: translate(' + (-5 + translateX) + 'px, ' + (-8 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; } 78% { -webkit-transform: translate(' + (5 + translateX) + 'px, ' + (-3 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; transform: translate(' + (5 + translateX) + 'px, ' + (-3 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; } 80% { -webkit-transform: translate(' + (-6 + translateX) + 'px, ' + (-3 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; transform: translate(' + (-6 + translateX) + 'px, ' + (-3 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; } 82% { -webkit-transform: translate(' + (7 + translateX) + 'px, ' + (8 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; transform: translate(' + (7 + translateX) + 'px, ' + (8 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; } 84% { -webkit-transform: translate(' + (-6 + translateX) + 'px, ' + (9 + translateY) + 'px) rotate(0.5deg) ' + scaleStr + '; transform: translate(' + (-6 + translateX) + 'px, ' + (9 + translateY) + 'px) rotate(0.5deg) ' + scaleStr + '; } 86% { -webkit-transform: translate(' + (1 + translateX) + 'px, ' + (8 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (1 + translateX) + 'px, ' + (8 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; } 88% { -webkit-transform: translate(' + (-9 + translateX) + 'px, ' + (-2 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; transform: translate(' + (-9 + translateX) + 'px, ' + (-2 + translateY) + 'px) rotate(1.5deg) ' + scaleStr + '; } 90% { -webkit-transform: translate(' + (4 + translateX) + 'px, ' + (-6 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; transform: translate(' + (4 + translateX) + 'px, ' + (-6 + translateY) + 'px) rotate(-1.5deg) ' + scaleStr + '; } 92% { -webkit-transform: translate(' + (0 + translateX) + 'px, ' + (-1 + translateY) + 'px) rotate(0.5deg) ' + scaleStr + '; transform: translate(' + (0 + translateX) + 'px, ' + (-1 + translateY) + 'px) rotate(0.5deg) ' + scaleStr + '; } 94% { -webkit-transform: translate(' + (2 + translateX) + 'px, ' + (-9 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; transform: translate(' + (2 + translateX) + 'px, ' + (-9 + translateY) + 'px) rotate(2.5deg) ' + scaleStr + '; } 96% { -webkit-transform: translate(' + (-9 + translateX) + 'px, ' + (1 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; transform: translate(' + (-9 + translateX) + 'px, ' + (1 + translateY) + 'px) rotate(-2.5deg) ' + scaleStr + '; } 98% { -webkit-transform: translate(' + (-9 + translateX) + 'px, ' + (-5 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + '; transform: translate(' + (-9 + translateX) + 'px, ' + (-5 + translateY) + 'px) rotate(-3.5deg) ' + scaleStr + ';} }');

  /*setTimeout ->
    css.disableAnimation $el[0]
  , duration*1000
   */
};

jitter.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      scaleX: css.getScaleX($el[0]),
      scaleY: css.getScaleY($el[0]),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  css.css($el[0], {
    opacity: 0
  });
  return css.rmKeyFrames($el[0]);
};

module.exports = jitter;



},{}],16:[function(require,module,exports){

/*
    dd.wang 11/26
    Description: Copy PPT Rotation Animation
    Version 0.1.0
 */
var css, light;

css = window.RP.Css;

light = {};

light.start = function($el, duration) {
  var styler;
  css.disableAnimation($el[0]);
  styler = JSON.parse($el.attr("data-style-cache"));
  css.css($el[0], styler);
  css.css($el[0], {
    '-webkit-animation': 'light ' + duration + 's' + " ease-in-out infinite both"
  });
  return css.regKeyFrames('light', '{ 0% {opacity: 0;} 50% {opacity: 1;} 100%{opacity: 0;} }');

  /*setTimeout ->
      css.disableAnimation $el[0]
  , duration * 1000
   */
};

light.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  css.css($el[0], {
    opacity: 0
  });
  return css.rmKeyFrames($el[0]);
};

module.exports = light;



},{}],17:[function(require,module,exports){
var RainyDay, css, rainy, _initRain;

RainyDay = require("./rainyday.js");

css = window.RP.Css;

rainy = {};

_initRain = function($el) {
  var $bg, rainyEngine;
  $bg = $el.parent();
  rainyEngine = new RainyDay({
    top: $el[0].style.top,
    left: $el[0].style.left,
    webkitTransform: $el[0].style.webkitTransform,
    zIndex: $el[0].style.zIndex,
    image: $el[0],
    parentElement: $bg[0]
  });
  rainyEngine.gravity = rainyEngine.GRAVITY_NON_LINEAR;
  rainyEngine.trail = rainyEngine.TRAIL_DROPS;
  css.css(rainyEngine.canvas, {
    'opacity': 0
  });
  setTimeout(function() {
    css.enableAnimation(rainyEngine.canvas, 1.5, "linear");
    return css.css(rainyEngine.canvas, {
      'opacity': 1
    });
  }, 0);
  rainyEngine.rain([[0, 2, 100]]);
  return rainyEngine.rain([[0, 2, 0.5], [1, 5, 1]], 60);
};

rainy.start = function($el, duration) {
  var data, idx, ori, path;
  if ($('div.simulator').length <= 0) {
    data = {};
    data.bg = $el.attr('src');
    if (data.bg && $el.hasClass('image')) {
      if ((data.bg.indexOf('http://')) !== -1) {
        path = data.bg.substring(7);
        idx = path.indexOf('/');
        ori = 'http://' + (path.substring(0, idx + 1));
        $el[0].crossOrigin = ori;
      }
      $el[0].onload = function() {
        _initRain($el);
        return $el[0].style.zIndex = parseInt($el[0].style.zIndex) - 200;
      };
      $el.attr('src', data.bg + '?_=' + (new Date()).getTime());
    }
  }
  return css.css($el[0], JSON.parse($el.attr("data-style-cache")));
};

rainy.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    return $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    return style = JSON.parse(styleStr);
  }
};

module.exports = rainy;



},{"./rainyday.js":18}],18:[function(require,module,exports){
/**
 * Defines a new instance of the rainyday.js.
 * http://maroslaw.github.io/rainyday.js/
 * @param options options element with script parameters
 * @param canvas to be used (if not defined a new one will be created)
 */

/**
 * Jensen Modify 1023
 */

function RainyDay(options, canvas) {

	if (this === window) { //if *this* is the window object, start over with a *new* object
		return new RainyDay(options, canvas);
	}

	this.img = options.image;
	var defaults = {
		opacity: 1,
		blur: 10,
		crop: [0, 0, this.img.naturalWidth, this.img.naturalHeight],
		enableSizeChange: true,
		parentElement: document.getElementsByTagName('body')[0],
		fps: 30,
		fillStyle: '#8ED6FF',
		enableCollisions: true,
		gravityThreshold: 3,
		gravityAngle: Math.PI / 2,
		gravityAngleVariance: 0,
		reflectionScaledownFactor: 5,
		reflectionDropMappingWidth: 200,
		reflectionDropMappingHeight: 200,
		width: this.img.clientWidth,
		height: this.img.clientHeight,
		position: 'absolute',
		top: 0,
		left: 0
	};

	// add the defaults to options
	for (var option in defaults) {
		if (typeof options[option] === 'undefined') {
			options[option] = defaults[option];
		}
	}
	this.options = options;

	this.drops = [];

	// prepare canvas elements
	this.canvas = canvas || this.prepareCanvas();
	this.prepareBackground();
	this.prepareGlass();

	// assume defaults
	this.reflection = this.REFLECTION_MINIATURE;
	this.trail = this.TRAIL_DROPS;
	this.gravity = this.GRAVITY_NON_LINEAR;
	this.collision = this.COLLISION_SIMPLE;

	// set polyfill of requestAnimationFrame
	this.setRequestAnimFrame();
}

/**
 * Create the main canvas over a given element
 * @returns HTMLElement the canvas
 */
RainyDay.prototype.prepareCanvas = function() {
	var canvas = document.createElement('canvas');
	canvas.style.position = this.options.position;
	canvas.style.top = this.options.top;
	canvas.style.left = this.options.left;
	canvas.style.zIndex = this.options.zIndex;
	canvas.style.webkitTransform = this.options.webkitTransform;
	canvas.width = this.options.width;
	canvas.height = this.options.height;
	this.options.parentElement.appendChild(canvas);
	if (this.options.enableSizeChange) {
		this.setResizeHandler();
	}
	return canvas;
};

RainyDay.prototype.setResizeHandler = function() {
	// use setInterval if oneresize event already use by other.
	if (window.onresize !== null) {
		window.setInterval(this.checkSize.bind(this), 100);
	} else {
		window.onresize = this.checkSize.bind(this);
		window.onorientationchange = this.checkSize.bind(this);
	}
};

/**
 * Periodically check the size of the underlying element
 */
RainyDay.prototype.checkSize = function() {
	var clientWidth = this.img.clientWidth;
	var clientHeight = this.img.clientHeight;
	var clientOffsetLeft = this.img.offsetLeft;
	var clientOffsetTop = this.img.offsetTop;
	var canvasWidth = this.canvas.width;
	var canvasHeight = this.canvas.height;
	var canvasOffsetLeft = this.canvas.offsetLeft;
	var canvasOffsetTop = this.canvas.offsetTop;

	if (canvasWidth !== clientWidth || canvasHeight !== clientHeight) {
		this.canvas.width = clientWidth;
		this.canvas.height = clientHeight;
		this.prepareBackground();
		this.glass.width = this.canvas.width;
		this.glass.height = this.canvas.height;
		this.prepareReflections();
	}
	if (canvasOffsetLeft !== clientOffsetLeft || canvasOffsetTop !== clientOffsetTop) {
		this.canvas.offsetLeft = clientOffsetLeft;
		this.canvas.offsetTop = clientOffsetTop;
	}
};

/**
 * Start animation loop
 */
RainyDay.prototype.animateDrops = function() {
	if (this.addDropCallback) {
		this.addDropCallback();
	}
	// |this.drops| array may be changed as we iterate over drops
	var dropsClone = this.drops.slice();
	var newDrops = [];
	for (var i = 0; i < dropsClone.length; ++i) {
		if (dropsClone[i].animate()) {
			newDrops.push(dropsClone[i]);
		}
	}
	this.drops = newDrops;
	window.requestAnimFrame(this.animateDrops.bind(this));
};

/**
 * Polyfill for requestAnimationFrame
 */
RainyDay.prototype.setRequestAnimFrame = function() {
	var fps = this.options.fps;
	window.requestAnimFrame = (function() {
		return window.requestAnimationFrame ||
			window.webkitRequestAnimationFrame ||
			window.mozRequestAnimationFrame ||
			function(callback) {
				window.setTimeout(callback, 1000 / fps);
			};
	})();
};

/**
 * Create the helper canvas for rendering raindrop reflections.
 */
RainyDay.prototype.prepareReflections = function() {
	this.reflected = document.createElement('canvas');
	this.reflected.width = this.canvas.width / this.options.reflectionScaledownFactor;
	this.reflected.height = this.canvas.height / this.options.reflectionScaledownFactor;
	var ctx = this.reflected.getContext('2d');
	ctx.drawImage(this.img, this.options.crop[0], this.options.crop[1], this.options.crop[2], this.options.crop[3], 0, 0, this.reflected.width, this.reflected.height);
};

/**
 * Create the glass canvas.
 */
RainyDay.prototype.prepareGlass = function() {
	this.glass = document.createElement('canvas');
	this.glass.width = this.canvas.width;
	this.glass.height = this.canvas.height;
	this.context = this.glass.getContext('2d');
};

/**
 * Main function for starting rain rendering.
 * @param presets list of presets to be applied
 * @param speed speed of the animation (if not provided or 0 static image will be generated)
 */
RainyDay.prototype.rain = function(presets, speed) {
	// prepare canvas for drop reflections
	if (this.reflection !== this.REFLECTION_NONE) {
		this.prepareReflections();
	}

	this.animateDrops();

	// animation
	this.presets = presets;

	this.PRIVATE_GRAVITY_FORCE_FACTOR_Y = (this.options.fps * 0.001) / 25;
	this.PRIVATE_GRAVITY_FORCE_FACTOR_X = ((Math.PI / 2) - this.options.gravityAngle) * (this.options.fps * 0.001) / 50;

	// prepare gravity matrix
	if (this.options.enableCollisions) {

		// calculate max radius of a drop to establish gravity matrix resolution
		var maxDropRadius = 0;
		for (var i = 0; i < presets.length; i++) {
			if (presets[i][0] + presets[i][1] > maxDropRadius) {
				maxDropRadius = Math.floor(presets[i][0] + presets[i][1]);
			}
		}

		if (maxDropRadius > 0) {
			// initialize the gravity matrix
			var mwi = Math.ceil(this.canvas.width / maxDropRadius);
			var mhi = Math.ceil(this.canvas.height / maxDropRadius);
			this.matrix = new CollisionMatrix(mwi, mhi, maxDropRadius);
		} else {
			this.options.enableCollisions = false;
		}
	}

	for (var i = 0; i < presets.length; i++) {
		if (!presets[i][3]) {
			presets[i][3] = -1;
		}
	}

	var lastExecutionTime = 0;
	this.addDropCallback = function() {
		var timestamp = new Date().getTime();
		if (timestamp - lastExecutionTime < speed) {
			return;
		}
		lastExecutionTime = timestamp;
		var context = this.canvas.getContext('2d');
		context.clearRect(0, 0, this.canvas.width, this.canvas.height);
		context.drawImage(this.background, 0, 0, this.canvas.width, this.canvas.height);
		// select matching preset
		var preset;
		for (var i = 0; i < presets.length; i++) {
			if (presets[i][2] > 1 || presets[i][3] === -1) {
				if (presets[i][3] !== 0) {
					presets[i][3]--;
					for (var y = 0; y < presets[i][2]; ++y) {
						this.putDrop(new Drop(this, Math.random() * this.canvas.width, Math.random() * this.canvas.height, presets[i][0], presets[i][1]));
					}
				}
			} else if (Math.random() < presets[i][2]) {
				preset = presets[i];
				break;
			}
		}
		if (preset) {
			this.putDrop(new Drop(this, Math.random() * this.canvas.width, Math.random() * this.canvas.height, preset[0], preset[1]));
		}
		context.save();
		context.globalAlpha = this.options.opacity;
		context.drawImage(this.glass, 0, 0, this.canvas.width, this.canvas.height);
		context.restore();
	}
		.bind(this);
};

/**
 * Adds a new raindrop to the animation.
 * @param drop drop object to be added to the animation
 */
RainyDay.prototype.putDrop = function(drop) {
	drop.draw();
	if (this.gravity && drop.r > this.options.gravityThreshold) {
		if (this.options.enableCollisions) {
			this.matrix.update(drop);
		}
		this.drops.push(drop);
	}
};

/**
 * Clear the drop and remove from the list if applicable.
 * @drop to be cleared
 * @force force removal from the list
 * result if true animation of this drop should be stopped
 */
RainyDay.prototype.clearDrop = function(drop, force) {
	var result = drop.clear(force);
	if (result) {
		var index = this.drops.indexOf(drop);
		if (index >= 0) {
			this.drops.splice(index, 1);
		}
	}
	return result;
};

/**
 * Defines a new raindrop object.
 * @param rainyday reference to the parent object
 * @param centerX x position of the center of this drop
 * @param centerY y position of the center of this drop
 * @param min minimum size of a drop
 * @param base base value for randomizing drop size
 */

function Drop(rainyday, centerX, centerY, min, base) {
	this.x = Math.floor(centerX);
	this.y = Math.floor(centerY);
	this.r = (Math.random() * base) + min;
	this.rainyday = rainyday;
	this.context = rainyday.context;
	this.reflection = rainyday.reflected;
}

/**
 * Draws a raindrop on canvas at the current position.
 */
Drop.prototype.draw = function() {
	this.context.save();
	this.context.beginPath();

	var orgR = this.r;
	this.r = 0.95 * this.r;
	if (this.r < 3) {
		this.context.arc(this.x, this.y, this.r, 0, Math.PI * 2, true);
		this.context.closePath();
	} else if (this.colliding || this.yspeed > 2) {
		if (this.colliding) {
			var collider = this.colliding;
			this.r = 1.001 * (this.r > collider.r ? this.r : collider.r);
			this.x += (collider.x - this.x);
			this.colliding = null;
		}

		var yr = 1 + 0.1 * this.yspeed;
		this.context.moveTo(this.x - this.r / yr, this.y);
		this.context.bezierCurveTo(this.x - this.r, this.y - this.r * 2, this.x + this.r, this.y - this.r * 2, this.x + this.r / yr, this.y);
		this.context.bezierCurveTo(this.x + this.r, this.y + yr * this.r, this.x - this.r, this.y + yr * this.r, this.x - this.r / yr, this.y);
	} else {
		this.context.arc(this.x, this.y, this.r * 0.9, 0, Math.PI * 2, true);
		this.context.closePath();
	}

	this.context.clip();

	this.r = orgR;

	if (this.rainyday.reflection) {
		this.rainyday.reflection(this);
	}

	this.context.restore();
};

/**
 * Clears the raindrop region.
 * @param force force stop
 * @returns Boolean true if the animation is stopped
 */
Drop.prototype.clear = function(force) {
	this.context.clearRect(this.x - this.r - 1, this.y - this.r - 2, 2 * this.r + 2, 2 * this.r + 2);
	if (force) {
		this.terminate = true;
		return true;
	}
	if ((this.y - this.r > this.rainyday.canvas.height) || (this.x - this.r > this.rainyday.canvas.width) || (this.x + this.r < 0)) {
		// over edge so stop this drop
		return true;
	}
	return false;
};

/**
 * Moves the raindrop to a new position according to the gravity.
 */
Drop.prototype.animate = function() {
	if (this.terminate) {
		return false;
	}
	var stopped = this.rainyday.gravity(this);
	if (!stopped && this.rainyday.trail) {
		this.rainyday.trail(this);
	}
	if (this.rainyday.options.enableCollisions) {
		var collisions = this.rainyday.matrix.update(this, stopped);
		if (collisions) {
			this.rainyday.collision(this, collisions);
		}
	}
	return !stopped || this.terminate;
};

/**
 * TRAIL function: no trail at all
 */
RainyDay.prototype.TRAIL_NONE = function() {
	// nothing going on here
};

/**
 * TRAIL function: trail of small drops (default)
 * @param drop raindrop object
 */
RainyDay.prototype.TRAIL_DROPS = function(drop) {
	if (!drop.trailY || drop.y - drop.trailY >= Math.random() * 100 * drop.r) {
		drop.trailY = drop.y;
		this.putDrop(new Drop(this, drop.x + (Math.random() * 2 - 1) * Math.random(), drop.y - drop.r - 5, Math.ceil(drop.r / 5), 0));
	}
};

/**
 * TRAIL function: trail of unblurred image
 * @param drop raindrop object
 */
RainyDay.prototype.TRAIL_SMUDGE = function(drop) {
	var y = drop.y - drop.r - 3;
	var x = drop.x - drop.r / 2 + (Math.random() * 2);
	if (y < 0 || x < 0) {
		return;
	}
	this.context.drawImage(this.clearbackground, x, y, drop.r, 2, x, y, drop.r, 2);
};

/**
 * GRAVITY function: no gravity at all
 * @returns Boolean true if the animation is stopped
 */
RainyDay.prototype.GRAVITY_NONE = function() {
	return true;
};

/**
 * GRAVITY function: linear gravity
 * @param drop raindrop object
 * @returns Boolean true if the animation is stopped
 */
RainyDay.prototype.GRAVITY_LINEAR = function(drop) {
	if (this.clearDrop(drop)) {
		return true;
	}

	if (drop.yspeed) {
		drop.yspeed += this.PRIVATE_GRAVITY_FORCE_FACTOR_Y * Math.floor(drop.r);
		drop.xspeed += this.PRIVATE_GRAVITY_FORCE_FACTOR_X * Math.floor(drop.r);
	} else {
		drop.yspeed = this.PRIVATE_GRAVITY_FORCE_FACTOR_Y;
		drop.xspeed = this.PRIVATE_GRAVITY_FORCE_FACTOR_X;
	}

	drop.y += drop.yspeed;
	drop.draw();
	return false;
};

/**
 * GRAVITY function: non-linear gravity (default)
 * @param drop raindrop object
 * @returns Boolean true if the animation is stopped
 */
RainyDay.prototype.GRAVITY_NON_LINEAR = function(drop) {
	if (this.clearDrop(drop)) {
		return true;
	}

	if (drop.collided) {
		drop.collided = false;
		drop.seed = Math.floor(drop.r * Math.random() * this.options.fps);
		drop.skipping = false;
		drop.slowing = false;
	} else if (!drop.seed || drop.seed < 0) {
		drop.seed = Math.floor(drop.r * Math.random() * this.options.fps);
		drop.skipping = drop.skipping === false ? true : false;
		drop.slowing = true;
	}

	drop.seed--;

	if (drop.yspeed) {
		if (drop.slowing) {
			drop.yspeed /= 1.1;
			drop.xspeed /= 1.1;
			if (drop.yspeed < this.PRIVATE_GRAVITY_FORCE_FACTOR_Y) {
				drop.slowing = false;
			}

		} else if (drop.skipping) {
			drop.yspeed = this.PRIVATE_GRAVITY_FORCE_FACTOR_Y;
			drop.xspeed = this.PRIVATE_GRAVITY_FORCE_FACTOR_X;
		} else {
			drop.yspeed += 1 * this.PRIVATE_GRAVITY_FORCE_FACTOR_Y * Math.floor(drop.r);
			drop.xspeed += 1 * this.PRIVATE_GRAVITY_FORCE_FACTOR_X * Math.floor(drop.r);
		}
	} else {
		drop.yspeed = this.PRIVATE_GRAVITY_FORCE_FACTOR_Y;
		drop.xspeed = this.PRIVATE_GRAVITY_FORCE_FACTOR_X;
	}

	if (this.options.gravityAngleVariance !== 0) {
		drop.xspeed += ((Math.random() * 2 - 1) * drop.yspeed * this.options.gravityAngleVariance);
	}

	drop.y += drop.yspeed;
	drop.x += drop.xspeed;

	drop.draw();
	return false;
};

/**
 * Utility function to return positive min value
 * @param val1 first number
 * @param val2 second number
 */
RainyDay.prototype.positiveMin = function(val1, val2) {
	var result = 0;
	if (val1 < val2) {
		if (val1 <= 0) {
			result = val2;
		} else {
			result = val1;
		}
	} else {
		if (val2 <= 0) {
			result = val1;
		} else {
			result = val2;
		}
	}
	return result <= 0 ? 1 : result;
};

/**
 * REFLECTION function: no reflection at all
 */
RainyDay.prototype.REFLECTION_NONE = function() {
	this.context.fillStyle = this.options.fillStyle;
	this.context.fill();
};

/**
 * REFLECTION function: miniature reflection (default)
 * @param drop raindrop object
 */
RainyDay.prototype.REFLECTION_MINIATURE = function(drop) {
	var sx = Math.max((drop.x - this.options.reflectionDropMappingWidth) / this.options.reflectionScaledownFactor, 0);
	var sy = Math.max((drop.y - this.options.reflectionDropMappingHeight) / this.options.reflectionScaledownFactor, 0);
	var sw = this.positiveMin(this.options.reflectionDropMappingWidth * 2 / this.options.reflectionScaledownFactor, this.reflected.width - sx);
	var sh = this.positiveMin(this.options.reflectionDropMappingHeight * 2 / this.options.reflectionScaledownFactor, this.reflected.height - sy);
	var dx = Math.max(drop.x - 1.1 * drop.r, 0);
	var dy = Math.max(drop.y - 1.1 * drop.r, 0);
	this.context.drawImage(this.reflected, sx, sy, sw, sh, dx, dy, drop.r * 2, drop.r * 2);
};

/**
 * COLLISION function: default collision implementation
 * @param drop one of the drops colliding
 * @param collisions list of potential collisions
 */
RainyDay.prototype.COLLISION_SIMPLE = function(drop, collisions) {
	var item = collisions;
	var drop2;
	while (item != null) {
		var p = item.drop;
		if (Math.sqrt(Math.pow(drop.x - p.x, 2) + Math.pow(drop.y - p.y, 2)) < (drop.r + p.r)) {
			drop2 = p;
			break;
		}
		item = item.next;
	}

	if (!drop2) {
		return;
	}

	// rename so that we're dealing with low/high drops
	var higher,
		lower;
	if (drop.y > drop2.y) {
		higher = drop;
		lower = drop2;
	} else {
		higher = drop2;
		lower = drop;
	}

	this.clearDrop(lower);
	// force stopping the second drop
	this.clearDrop(higher, true);
	this.matrix.remove(higher);
	lower.draw();

	lower.colliding = higher;
	lower.collided = true;
};

/**
 * Resizes canvas, draws original image and applies blurring algorithm.
 */
RainyDay.prototype.prepareBackground = function() {
	this.background = document.createElement('canvas');
	this.background.width = this.canvas.width;
	this.background.height = this.canvas.height;

	this.clearbackground = document.createElement('canvas');
	this.clearbackground.width = this.canvas.width;
	this.clearbackground.height = this.canvas.height;

	var context = this.background.getContext('2d');
	context.clearRect(0, 0, this.canvas.width, this.canvas.height);

	context.drawImage(this.img, this.options.crop[0], this.options.crop[1], this.options.crop[2], this.options.crop[3], 0, 0, this.canvas.width, this.canvas.height);

	context = this.clearbackground.getContext('2d');
	context.clearRect(0, 0, this.canvas.width, this.canvas.height);
	context.drawImage(this.img, this.options.crop[0], this.options.crop[1], this.options.crop[2], this.options.crop[3], 0, 0, this.canvas.width, this.canvas.height);

	if (!isNaN(this.options.blur) && this.options.blur >= 1) {
		this.stackBlurCanvasRGB(this.canvas.width, this.canvas.height, this.options.blur);
	}
};

/**
 * Implements the Stack Blur Algorithm (@see http://www.quasimondo.com/StackBlurForCanvas/StackBlurDemo.html).
 * @param width width of the canvas
 * @param height height of the canvas
 * @param radius blur radius
 */
RainyDay.prototype.stackBlurCanvasRGB = function(width, height, radius) {

	var shgTable = [
		[0, 9],
		[1, 11],
		[2, 12],
		[3, 13],
		[5, 14],
		[7, 15],
		[11, 16],
		[15, 17],
		[22, 18],
		[31, 19],
		[45, 20],
		[63, 21],
		[90, 22],
		[127, 23],
		[181, 24]
	];

	var mulTable = [
		512, 512, 456, 512, 328, 456, 335, 512, 405, 328, 271, 456, 388, 335, 292, 512,
		454, 405, 364, 328, 298, 271, 496, 456, 420, 388, 360, 335, 312, 292, 273, 512,
		482, 454, 428, 405, 383, 364, 345, 328, 312, 298, 284, 271, 259, 496, 475, 456,
		437, 420, 404, 388, 374, 360, 347, 335, 323, 312, 302, 292, 282, 273, 265, 512,
		497, 482, 468, 454, 441, 428, 417, 405, 394, 383, 373, 364, 354, 345, 337, 328,
		320, 312, 305, 298, 291, 284, 278, 271, 265, 259, 507, 496, 485, 475, 465, 456,
		446, 437, 428, 420, 412, 404, 396, 388, 381, 374, 367, 360, 354, 347, 341, 335,
		329, 323, 318, 312, 307, 302, 297, 292, 287, 282, 278, 273, 269, 265, 261, 512,
		505, 497, 489, 482, 475, 468, 461, 454, 447, 441, 435, 428, 422, 417, 411, 405,
		399, 394, 389, 383, 378, 373, 368, 364, 359, 354, 350, 345, 341, 337, 332, 328,
		324, 320, 316, 312, 309, 305, 301, 298, 294, 291, 287, 284, 281, 278, 274, 271,
		268, 265, 262, 259, 257, 507, 501, 496, 491, 485, 480, 475, 470, 465, 460, 456,
		451, 446, 442, 437, 433, 428, 424, 420, 416, 412, 408, 404, 400, 396, 392, 388,
		385, 381, 377, 374, 370, 367, 363, 360, 357, 354, 350, 347, 344, 341, 338, 335,
		332, 329, 326, 323, 320, 318, 315, 312, 310, 307, 304, 302, 299, 297, 294, 292,
		289, 287, 285, 282, 280, 278, 275, 273, 271, 269, 267, 265, 263, 261, 259
	];

	radius |= 0;

	var context = this.background.getContext('2d');
	var imageData = context.getImageData(0, 0, width, height);
	var pixels = imageData.data;
	var x,
		y,
		i,
		p,
		yp,
		yi,
		yw,
		rSum,
		gSum,
		bSum,
		rOutSum,
		gOutSum,
		bOutSum,
		rInSum,
		gInSum,
		bInSum,
		pr,
		pg,
		pb,
		rbs;
	var radiusPlus1 = radius + 1;
	var sumFactor = radiusPlus1 * (radiusPlus1 + 1) / 2;

	var stackStart = new BlurStack();
	var stackEnd = new BlurStack();
	var stack = stackStart;
	for (i = 1; i < 2 * radius + 1; i++) {
		stack = stack.next = new BlurStack();
		if (i === radiusPlus1) {
			stackEnd = stack;
		}
	}
	stack.next = stackStart;
	var stackIn = null;
	var stackOut = null;

	yw = yi = 0;

	var mulSum = mulTable[radius];
	var shgSum;
	for (var ssi = 0; ssi < shgTable.length; ++ssi) {
		if (radius <= shgTable[ssi][0]) {
			shgSum = shgTable[ssi - 1][1];
			break;
		}
	}

	for (y = 0; y < height; y++) {
		rInSum = gInSum = bInSum = rSum = gSum = bSum = 0;

		rOutSum = radiusPlus1 * (pr = pixels[yi]);
		gOutSum = radiusPlus1 * (pg = pixels[yi + 1]);
		bOutSum = radiusPlus1 * (pb = pixels[yi + 2]);

		rSum += sumFactor * pr;
		gSum += sumFactor * pg;
		bSum += sumFactor * pb;

		stack = stackStart;

		for (i = 0; i < radiusPlus1; i++) {
			stack.r = pr;
			stack.g = pg;
			stack.b = pb;
			stack = stack.next;
		}

		for (i = 1; i < radiusPlus1; i++) {
			p = yi + ((width - 1 < i ? width - 1 : i) << 2);
			rSum += (stack.r = (pr = pixels[p])) * (rbs = radiusPlus1 - i);
			gSum += (stack.g = (pg = pixels[p + 1])) * rbs;
			bSum += (stack.b = (pb = pixels[p + 2])) * rbs;

			rInSum += pr;
			gInSum += pg;
			bInSum += pb;

			stack = stack.next;
		}

		stackIn = stackStart;
		stackOut = stackEnd;
		for (x = 0; x < width; x++) {
			pixels[yi] = (rSum * mulSum) >> shgSum;
			pixels[yi + 1] = (gSum * mulSum) >> shgSum;
			pixels[yi + 2] = (bSum * mulSum) >> shgSum;

			rSum -= rOutSum;
			gSum -= gOutSum;
			bSum -= bOutSum;

			rOutSum -= stackIn.r;
			gOutSum -= stackIn.g;
			bOutSum -= stackIn.b;

			p = (yw + ((p = x + radius + 1) < (width - 1) ? p : (width - 1))) << 2;

			rInSum += (stackIn.r = pixels[p]);
			gInSum += (stackIn.g = pixels[p + 1]);
			bInSum += (stackIn.b = pixels[p + 2]);

			rSum += rInSum;
			gSum += gInSum;
			bSum += bInSum;

			stackIn = stackIn.next;

			rOutSum += (pr = stackOut.r);
			gOutSum += (pg = stackOut.g);
			bOutSum += (pb = stackOut.b);

			rInSum -= pr;
			gInSum -= pg;
			bInSum -= pb;

			stackOut = stackOut.next;

			yi += 4;
		}
		yw += width;
	}

	for (x = 0; x < width; x++) {
		gInSum = bInSum = rInSum = gSum = bSum = rSum = 0;

		yi = x << 2;
		rOutSum = radiusPlus1 * (pr = pixels[yi]);
		gOutSum = radiusPlus1 * (pg = pixels[yi + 1]);
		bOutSum = radiusPlus1 * (pb = pixels[yi + 2]);

		rSum += sumFactor * pr;
		gSum += sumFactor * pg;
		bSum += sumFactor * pb;

		stack = stackStart;

		for (i = 0; i < radiusPlus1; i++) {
			stack.r = pr;
			stack.g = pg;
			stack.b = pb;
			stack = stack.next;
		}

		yp = width;

		for (i = 1; i < radiusPlus1; i++) {
			yi = (yp + x) << 2;

			rSum += (stack.r = (pr = pixels[yi])) * (rbs = radiusPlus1 - i);
			gSum += (stack.g = (pg = pixels[yi + 1])) * rbs;
			bSum += (stack.b = (pb = pixels[yi + 2])) * rbs;

			rInSum += pr;
			gInSum += pg;
			bInSum += pb;

			stack = stack.next;

			if (i < (height - 1)) {
				yp += width;
			}
		}

		yi = x;
		stackIn = stackStart;
		stackOut = stackEnd;
		for (y = 0; y < height; y++) {
			p = yi << 2;
			pixels[p] = (rSum * mulSum) >> shgSum;
			pixels[p + 1] = (gSum * mulSum) >> shgSum;
			pixels[p + 2] = (bSum * mulSum) >> shgSum;

			rSum -= rOutSum;
			gSum -= gOutSum;
			bSum -= bOutSum;

			rOutSum -= stackIn.r;
			gOutSum -= stackIn.g;
			bOutSum -= stackIn.b;

			p = (x + (((p = y + radiusPlus1) < (height - 1) ? p : (height - 1)) * width)) << 2;

			rSum += (rInSum += (stackIn.r = pixels[p]));
			gSum += (gInSum += (stackIn.g = pixels[p + 1]));
			bSum += (bInSum += (stackIn.b = pixels[p + 2]));

			stackIn = stackIn.next;

			rOutSum += (pr = stackOut.r);
			gOutSum += (pg = stackOut.g);
			bOutSum += (pb = stackOut.b);

			rInSum -= pr;
			gInSum -= pg;
			bInSum -= pb;

			stackOut = stackOut.next;

			yi += width;
		}
	}

	context.putImageData(imageData, 0, 0);

};

/**
 * Defines a new helper object for Stack Blur Algorithm.
 */
function BlurStack() {
	this.r = 0;
	this.g = 0;
	this.b = 0;
	this.next = null;
}

/**
 * Defines a gravity matrix object which handles collision detection.
 * @param x number of columns in the matrix
 * @param y number of rows in the matrix
 * @param r grid size
 */
function CollisionMatrix(x, y, r) {
	this.resolution = r;
	this.xc = x;
	this.yc = y;
	this.matrix = new Array(x);
	for (var i = 0; i <= (x + 5); i++) {
		this.matrix[i] = new Array(y);
		for (var j = 0; j <= (y + 5); ++j) {
			this.matrix[i][j] = new DropItem(null);
		}
	}
}

/**
 * Updates position of the given drop on the collision matrix.
 * @param drop raindrop to be positioned/repositioned
 * @param forceDelete if true the raindrop will be removed from the matrix
 * @returns collisions if any
 */
CollisionMatrix.prototype.update = function(drop, forceDelete) {
	if (drop.gid) {
		if (!this.matrix[drop.gmx] || !this.matrix[drop.gmx][drop.gmy]) {
			return null;
		}
		this.matrix[drop.gmx][drop.gmy].remove(drop);
		if (forceDelete) {
			return null;
		}

		drop.gmx = Math.floor(drop.x / this.resolution);
		drop.gmy = Math.floor(drop.y / this.resolution);
		if (!this.matrix[drop.gmx] || !this.matrix[drop.gmx][drop.gmy]) {
			return null;
		}
		this.matrix[drop.gmx][drop.gmy].add(drop);

		var collisions = this.collisions(drop);
		if (collisions && collisions.next != null) {
			return collisions.next;
		}
	} else {
		drop.gid = Math.random().toString(36).substr(2, 9);
		drop.gmx = Math.floor(drop.x / this.resolution);
		drop.gmy = Math.floor(drop.y / this.resolution);
		if (!this.matrix[drop.gmx] || !this.matrix[drop.gmx][drop.gmy]) {
			return null;
		}

		this.matrix[drop.gmx][drop.gmy].add(drop);
	}
	return null;
};

/**
 * Looks for collisions with the given raindrop.
 * @param drop raindrop to be checked
 * @returns DropItem list of drops that collide with it
 */
CollisionMatrix.prototype.collisions = function(drop) {
	var item = new DropItem(null);
	var first = item;

	item = this.addAll(item, drop.gmx - 1, drop.gmy + 1);
	item = this.addAll(item, drop.gmx, drop.gmy + 1);
	item = this.addAll(item, drop.gmx + 1, drop.gmy + 1);

	return first;
};

/**
 * Appends all found drop at a given location to the given item.
 * @param to item to which the results will be appended to
 * @param x x position in the matrix
 * @param y y position in the matrix
 * @returns last discovered item on the list
 */
CollisionMatrix.prototype.addAll = function(to, x, y) {
	if (x > 0 && y > 0 && x < this.xc && y < this.yc) {
		var items = this.matrix[x][y];
		while (items.next != null) {
			items = items.next;
			to.next = new DropItem(items.drop);
			to = to.next;
		}
	}
	return to;
};

/**
 * Removed the drop from its current position
 * @param drop to be removed
 */
CollisionMatrix.prototype.remove = function(drop) {
	this.matrix[drop.gmx][drop.gmy].remove(drop);
};

/**
 * Defines a linked list item.
 */
function DropItem(drop) {
	this.drop = drop;
	this.next = null;
}

/**
 * Adds the raindrop to the end of the list.
 * @param drop raindrop to be added
 */
DropItem.prototype.add = function(drop) {
	var item = this;
	while (item.next != null) {
		item = item.next;
	}
	item.next = new DropItem(drop);
};

/**
 * Removes the raindrop from the list.
 * @param drop raindrop to be removed
 */
DropItem.prototype.remove = function(drop) {
	var item = this;
	var prevItem = null;
	while (item.next != null) {
		prevItem = item;
		item = item.next;
		if (item.drop.gid === drop.gid) {
			prevItem.next = item.next;
		}
	}
};

if (module && module.exports) {
    module.exports = RainyDay;
}
},{}],19:[function(require,module,exports){

/*
    Jensen 0919
    Description: Copy PPT Rotation Animation
    Version 0.1.0
 */
var css, rotation;

css = window.RP.Css;

rotation = {};

rotation.start = function($el, duration) {
  css.enableAnimation($el[0], duration);
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, duration * 1000);
};

rotation.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      rotationY: css.getRotateY($el[0]),
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    rotationY: style.rotationY + 180
  });
};

module.exports = rotation;



},{}],20:[function(require,module,exports){

/*
    dd.wang 11/26
    Description: Copy PPT Rotation Animation
    Version 0.1.0
 */
var IDX, NAMEMAPPING, css, rotation2d;

css = window.RP.Css;

rotation2d = {};

IDX = 0;

NAMEMAPPING = {};

rotation2d.getAnimationName = function(scaleX, scaleY, translateX, translateY) {
  var key, name;
  if (scaleX == null) {
    scaleX = 1;
  }
  if (scaleY == null) {
    scaleY = 1;
  }
  key = 'scaleX-' + scaleX + '-scaleY' + scaleY + '-translateX' + translateX + '-translateY' + translateY;
  name = NAMEMAPPING[key];
  if (!name) {
    name = NAMEMAPPING[key] = 'rotation2d' + IDX++;
  }
  return name;
};

rotation2d.start = function($el, duration) {
  var name, s, scStr, scaleX, scaleY, styler, translateStr, translateX, translateY, _ref, _ref1, _ref2, _ref3;
  if (duration == null) {
    duration = 10;
  }
  css.disableAnimation($el[0]);
  styler = JSON.parse($el.attr("data-style-cache"));
  scaleX = (_ref = css.getScaleX($el[0])) != null ? _ref : 1;
  scaleY = (_ref1 = css.getScaleY($el[0])) != null ? _ref1 : 1;
  s = Math.min(scaleX, scaleY);
  scStr = 'scaleX(' + s + ') scaleY(' + s + ')';
  translateX = (_ref2 = styler.x) != null ? _ref2 : css.getX($el[0]);
  translateY = (_ref3 = styler.y) != null ? _ref3 : css.getY($el[0]);
  name = this.getAnimationName(scaleX, scaleY, translateX, translateY);
  translateStr = 'translate(' + translateX + 'px, ' + translateY + 'px)';
  css.css($el[0], styler);
  css.css($el[0], {
    '-webkit-transform-origin': '50% 50%',
    '-webkit-animation': name + ' ' + duration + 's' + " linear infinite"
  });
  return css.regKeyFrames(name, '{ 0% {-webkit-transform:' + translateStr + ' rotate(0deg) ' + scStr + ';} 100% {-webkit-transform:' + translateStr + ' rotate(360deg) ' + scStr + ';} }');
};

rotation2d.ready = function($el) {
  var s, style, styleStr, sx, sy;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  sx = css.getScaleX($el[0]);
  sy = css.getScaleY($el[0]);
  s = Math.min(sx, sy);
  css.css($el[0], {
    scaleX: s,
    scaleY: s
  });
  return css.rmKeyFrames($el[0]);
};

module.exports = rotation2d;



},{}],21:[function(require,module,exports){
var css, show;

css = window.RP.Css;

show = {};

show.start = function($el, duration) {
  css.enableAnimation($el[0], duration, "ease-in");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, duration * 1000);
};

show.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      scaleX: css.getScaleX($el[0]),
      scaleY: css.getScaleY($el[0]),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    scaleX: style.scaleX * 2,
    scaleY: style.scaleY * 2
  });
};

module.exports = show;



},{}],22:[function(require,module,exports){
var css, small2big;

css = window.RP.Css;

small2big = {};

small2big.start = function($el, duration) {
  css.enableAnimation($el[0], duration, "ease-in");
  css.css($el[0], JSON.parse($el.attr("data-style-cache")));
  return setTimeout(function() {
    return css.disableAnimation($el[0]);
  }, duration * 1000);
};

small2big.ready = function($el) {
  var style, styleStr;
  styleStr = $el.attr("data-style-cache");
  if (!styleStr) {
    style = {
      opacity: $el.css("opacity"),
      zIndex: $el.css("z-index"),
      scaleX: css.getScaleX($el[0]),
      scaleY: css.getScaleY($el[0]),
      y: css.getY($el[0]),
      x: css.getX($el[0])
    };
    $el.attr("data-style-cache", JSON.stringify(style));
  } else {
    style = JSON.parse(styleStr);
  }
  return css.css($el[0], {
    opacity: 0,
    scaleX: style.scaleX / 2,
    scaleY: style.scaleY / 2
  });
};

module.exports = small2big;



},{}],23:[function(require,module,exports){
var css, spread, _initSpread;

css = window.RP.Css;

spread = {};

_initSpread = function($el, $bg) {
  var b, button, i, m, _i, _len, _open, _ref, _results;
  this.$masks = (function() {
    var _i, _results;
    _results = [];
    for (m = _i = 1; _i <= 4; m = ++_i) {
      _results.push($bg.find(".mask-" + m));
    }
    return _results;
  })();
  this.$buttons = (function() {
    var _i, _results;
    _results = [];
    for (b = _i = 1; _i <= 3; b = ++_i) {
      _results.push($bg.find(".mask-btn-" + b));
    }
    return _results;
  })();
  this.count = 0;
  _open = function(idx) {
    var i, t, w, x, _i;
    w = $bg.width();
    for (i = _i = 0; _i <= 11; i = ++_i) {
      t = i * 60;
      x = i * w;
      setTimeout((function(_this) {
        return function(t, x) {
          return function() {
            return $bg.css('-webkit-mask-position', "-" + x + "px 0");
          };
        };
      })(this)(t, x), t);
    }
    if (this.$buttons[idx]) {
      this.$buttons[idx].hide();
    }
    this.count++;
    if (this.count === 3) {
      return setTimeout(((function(_this) {
        return function() {
          return _open(3);
        };
      })(this)), 1000);
    }
  };
  _ref = this.$buttons;
  _results = [];
  for (i = _i = 0, _len = _ref.length; _i < _len; i = ++_i) {
    button = _ref[i];
    _results.push(button.on('tap', (function(_this) {
      return function(i) {
        return function() {
          return _open(i);
        };
      };
    })(this)(i)));
  }
  return _results;
};

spread.start = function($el, duration) {
  var $bg;
  if ($('div.simulator').length <= 0) {
    $bg = $("<div class='bg'> <div class='mask-btns'> <div class='mask-btn mask-btn-1'></div> <div class='mask-btn mask-btn-2'></div> <div class='mask-btn mask-btn-3'></div> </div> </div>");
    $el.parent().append($bg);
    $bg.css('width', $el.css('width'));
    $bg.css('height', $el.css('height'));
    $bg.css('position', 'absolute');
    $bg.css('-webkit-transform', $el.css('-webkit-transform'));
    $bg.css('top', $el.css('top'));
    $bg.css('left', $el.css('left'));
    $bg.css('z-index', parseInt($el.css('z-index')) + 1);
    $bg.css('backgroundImage', 'url("")');
    $bg.css('backgroundSize', '100% 100%');
    $bg.css('backgroundSize', '100% 100%');
    $bg.css('webkitMaskPosition', '640px 640px');
    $bg.css('webkitMaskRepeat', 'no-repeat');
    $bg.css('webkitMaskSize', '1200% 100%');
    $bg.css('webkitMaskImage', 'url("")');
    $bg.find('div.mask').css('position', 'absolute');
    $bg.find('div.mask').css('top', '0');
    $bg.find('div.mask').css('left', '0');
    $bg.find('div.mask').css('width', '100%');
    $bg.find('div.mask').css('height', '100%');
    $bg.find('div.mask-btns').css('width', '100%');
    $bg.find('div.mask-btns').css('height', '100%');
    $bg.find('div.mask-btn').css('position', 'absolute');
    $bg.find('div.mask-btn').css('width', '100px');
    $bg.find('div.mask-btn').css('height', '100px');
    $bg.find('div.mask-btn').css('background', 'url("")  no-repeat center');
    $bg.find('div.mask-btn-1').css('left', '7%');
    $bg.find('div.mask-btn-1').css('top', '40%');
    $bg.find('div.mask-btn-2').css('left', '68%');
    $bg.find('div.mask-btn-2').css('top', '37%');
    $bg.find('div.mask-btn-3').css('left', '31%');
    $bg.find('div.mask-btn-3').css('top', '63%');
    $el.remove();
    return _initSpread($el, $bg);
  }
};

spread.ready = function($el) {};

module.exports = spread;



},{}],24:[function(require,module,exports){
/*

StackBlur - a fast almost Gaussian Blur For Canvas

Version: 	0.5
Author:		Mario Klingemann
Contact: 	mario@quasimondo.com
Website:	http://www.quasimondo.com/StackBlurForCanvas
Twitter:	@quasimondo

In case you find this class useful - especially in commercial projects -
I am not totally unhappy for a small donation to my PayPal account
mario@quasimondo.de

Or support me on flattr: 
https://flattr.com/thing/72791/StackBlur-a-fast-almost-Gaussian-Blur-Effect-for-CanvasJavascript

Copyright (c) 2010 Mario Klingemann

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/

StackBlur = {}

StackBlur.mul_table = [
        512,512,456,512,328,456,335,512,405,328,271,456,388,335,292,512,
        454,405,364,328,298,271,496,456,420,388,360,335,312,292,273,512,
        482,454,428,405,383,364,345,328,312,298,284,271,259,496,475,456,
        437,420,404,388,374,360,347,335,323,312,302,292,282,273,265,512,
        497,482,468,454,441,428,417,405,394,383,373,364,354,345,337,328,
        320,312,305,298,291,284,278,271,265,259,507,496,485,475,465,456,
        446,437,428,420,412,404,396,388,381,374,367,360,354,347,341,335,
        329,323,318,312,307,302,297,292,287,282,278,273,269,265,261,512,
        505,497,489,482,475,468,461,454,447,441,435,428,422,417,411,405,
        399,394,389,383,378,373,368,364,359,354,350,345,341,337,332,328,
        324,320,316,312,309,305,301,298,294,291,287,284,281,278,274,271,
        268,265,262,259,257,507,501,496,491,485,480,475,470,465,460,456,
        451,446,442,437,433,428,424,420,416,412,408,404,400,396,392,388,
        385,381,377,374,370,367,363,360,357,354,350,347,344,341,338,335,
        332,329,326,323,320,318,315,312,310,307,304,302,299,297,294,292,
        289,287,285,282,280,278,275,273,271,269,267,265,263,261,259];
        
   
StackBlur.shg_table = [
	     9, 11, 12, 13, 13, 14, 14, 15, 15, 15, 15, 16, 16, 16, 16, 17, 
		17, 17, 17, 17, 17, 17, 18, 18, 18, 18, 18, 18, 18, 18, 18, 19, 
		19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 20, 20, 20,
		20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 21,
		21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21,
		21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 22, 22, 22, 22, 22, 22, 
		22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22,
		22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 23, 
		23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23,
		23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23,
		23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 
		23, 23, 23, 23, 23, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 
		24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24,
		24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24,
		24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24,
		24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24 ];

// function stackBlurImage( imageID, canvasID, radius, blurAlphaChannel )
// {
			
//  	var img = document.getElementById( imageID );
// 	var w = img.naturalWidth;
//     var h = img.naturalHeight;
       
// 	var canvas = document.getElementById( canvasID );
      
//     canvas.style.width  = w + "px";
//     canvas.style.height = h + "px";
//     canvas.width = w;
//     canvas.height = h;
    
//     var context = canvas.getContext("2d");
//     context.clearRect( 0, 0, w, h );
//     context.drawImage( img, 0, 0 );

// 	if ( isNaN(radius) || radius < 1 ) return;
	
// 	if ( blurAlphaChannel )
// 		stackBlurCanvasRGBA( canvasID, 0, 0, w, h, radius );
// 	else 
// 		stackBlurCanvasRGB( canvasID, 0, 0, w, h, radius );
// }


StackBlur.stackBlurCanvasRGBA = function ( dom, top_x, top_y, width, height, radius )
{
	if ( isNaN(radius) || radius < 1 ) return;
	radius |= 0;
	
	var canvas  = dom;
	var context = canvas.getContext("2d");
	var imageData;
	
	try {
	  try {
		imageData = context.getImageData( top_x, top_y, width, height );
	  } catch(e) {
	  
		// NOTE: this part is supposedly only needed if you want to work with local files
		// so it might be okay to remove the whole try/catch block and just use
		imageData = context.getImageData( top_x, top_y, width, height );
		// try {
		// 	netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
		// 	imageData = context.getImageData( top_x, top_y, width, height );
		// } catch(e) {
		// 	alert("Cannot access local image");
		// 	throw new Error("unable to access local image data: " + e);
		// 	return;
		// }
	  }
	} catch(e) {
	  alert("Cannot access image");
	  throw new Error("unable to access image data: " + e);
	}
			
	var pixels = imageData.data;
			
	var x, y, i, p, yp, yi, yw, r_sum, g_sum, b_sum, a_sum, 
	r_out_sum, g_out_sum, b_out_sum, a_out_sum,
	r_in_sum, g_in_sum, b_in_sum, a_in_sum, 
	pr, pg, pb, pa, rbs;
			
	var div = radius + radius + 1;
	var w4 = width << 2;
	var widthMinus1  = width - 1;
	var heightMinus1 = height - 1;
	var radiusPlus1  = radius + 1;
	var sumFactor = radiusPlus1 * ( radiusPlus1 + 1 ) / 2;
	
	var stackStart = new BlurStack();
	var stack = stackStart;
	for ( i = 1; i < div; i++ )
	{
		stack = stack.next = new BlurStack();
		if ( i == radiusPlus1 ) var stackEnd = stack;
	}
	stack.next = stackStart;
	var stackIn = null;
	var stackOut = null;
	
	yw = yi = 0;
	
	var mul_sum = StackBlur.mul_table[radius];
	var shg_sum = StackBlur.shg_table[radius];
	
	for ( y = 0; y < height; y++ )
	{
		r_in_sum = g_in_sum = b_in_sum = a_in_sum = r_sum = g_sum = b_sum = a_sum = 0;
		
		r_out_sum = radiusPlus1 * ( pr = pixels[yi] );
		g_out_sum = radiusPlus1 * ( pg = pixels[yi+1] );
		b_out_sum = radiusPlus1 * ( pb = pixels[yi+2] );
		a_out_sum = radiusPlus1 * ( pa = pixels[yi+3] );
		
		r_sum += sumFactor * pr;
		g_sum += sumFactor * pg;
		b_sum += sumFactor * pb;
		a_sum += sumFactor * pa;
		
		stack = stackStart;
		
		for( i = 0; i < radiusPlus1; i++ )
		{
			stack.r = pr;
			stack.g = pg;
			stack.b = pb;
			stack.a = pa;
			stack = stack.next;
		}
		
		for( i = 1; i < radiusPlus1; i++ )
		{
			p = yi + (( widthMinus1 < i ? widthMinus1 : i ) << 2 );
			r_sum += ( stack.r = ( pr = pixels[p])) * ( rbs = radiusPlus1 - i );
			g_sum += ( stack.g = ( pg = pixels[p+1])) * rbs;
			b_sum += ( stack.b = ( pb = pixels[p+2])) * rbs;
			a_sum += ( stack.a = ( pa = pixels[p+3])) * rbs;
			
			r_in_sum += pr;
			g_in_sum += pg;
			b_in_sum += pb;
			a_in_sum += pa;
			
			stack = stack.next;
		}
		
		
		stackIn = stackStart;
		stackOut = stackEnd;
		for ( x = 0; x < width; x++ )
		{
			pixels[yi+3] = pa = (a_sum * mul_sum) >> shg_sum;
			if ( pa != 0 )
			{
				pa = 255 / pa;
				pixels[yi]   = ((r_sum * mul_sum) >> shg_sum) * pa;
				pixels[yi+1] = ((g_sum * mul_sum) >> shg_sum) * pa;
				pixels[yi+2] = ((b_sum * mul_sum) >> shg_sum) * pa;
			} else {
				pixels[yi] = pixels[yi+1] = pixels[yi+2] = 0;
			}
			
			r_sum -= r_out_sum;
			g_sum -= g_out_sum;
			b_sum -= b_out_sum;
			a_sum -= a_out_sum;
			
			r_out_sum -= stackIn.r;
			g_out_sum -= stackIn.g;
			b_out_sum -= stackIn.b;
			a_out_sum -= stackIn.a;
			
			p =  ( yw + ( ( p = x + radius + 1 ) < widthMinus1 ? p : widthMinus1 ) ) << 2;
			
			r_in_sum += ( stackIn.r = pixels[p]);
			g_in_sum += ( stackIn.g = pixels[p+1]);
			b_in_sum += ( stackIn.b = pixels[p+2]);
			a_in_sum += ( stackIn.a = pixels[p+3]);
			
			r_sum += r_in_sum;
			g_sum += g_in_sum;
			b_sum += b_in_sum;
			a_sum += a_in_sum;
			
			stackIn = stackIn.next;
			
			r_out_sum += ( pr = stackOut.r );
			g_out_sum += ( pg = stackOut.g );
			b_out_sum += ( pb = stackOut.b );
			a_out_sum += ( pa = stackOut.a );
			
			r_in_sum -= pr;
			g_in_sum -= pg;
			b_in_sum -= pb;
			a_in_sum -= pa;
			
			stackOut = stackOut.next;

			yi += 4;
		}
		yw += width;
	}

	
	for ( x = 0; x < width; x++ )
	{
		g_in_sum = b_in_sum = a_in_sum = r_in_sum = g_sum = b_sum = a_sum = r_sum = 0;
		
		yi = x << 2;
		r_out_sum = radiusPlus1 * ( pr = pixels[yi]);
		g_out_sum = radiusPlus1 * ( pg = pixels[yi+1]);
		b_out_sum = radiusPlus1 * ( pb = pixels[yi+2]);
		a_out_sum = radiusPlus1 * ( pa = pixels[yi+3]);
		
		r_sum += sumFactor * pr;
		g_sum += sumFactor * pg;
		b_sum += sumFactor * pb;
		a_sum += sumFactor * pa;
		
		stack = stackStart;
		
		for( i = 0; i < radiusPlus1; i++ )
		{
			stack.r = pr;
			stack.g = pg;
			stack.b = pb;
			stack.a = pa;
			stack = stack.next;
		}
		
		yp = width;
		
		for( i = 1; i <= radius; i++ )
		{
			yi = ( yp + x ) << 2;
			
			r_sum += ( stack.r = ( pr = pixels[yi])) * ( rbs = radiusPlus1 - i );
			g_sum += ( stack.g = ( pg = pixels[yi+1])) * rbs;
			b_sum += ( stack.b = ( pb = pixels[yi+2])) * rbs;
			a_sum += ( stack.a = ( pa = pixels[yi+3])) * rbs;
		   
			r_in_sum += pr;
			g_in_sum += pg;
			b_in_sum += pb;
			a_in_sum += pa;
			
			stack = stack.next;
		
			if( i < heightMinus1 )
			{
				yp += width;
			}
		}
		
		yi = x;
		stackIn = stackStart;
		stackOut = stackEnd;
		for ( y = 0; y < height; y++ )
		{
			p = yi << 2;
			pixels[p+3] = pa = (a_sum * mul_sum) >> shg_sum;
			if ( pa > 0 )
			{
				pa = 255 / pa;
				pixels[p]   = ((r_sum * mul_sum) >> shg_sum ) * pa;
				pixels[p+1] = ((g_sum * mul_sum) >> shg_sum ) * pa;
				pixels[p+2] = ((b_sum * mul_sum) >> shg_sum ) * pa;
			} else {
				pixels[p] = pixels[p+1] = pixels[p+2] = 0;
			}
			
			r_sum -= r_out_sum;
			g_sum -= g_out_sum;
			b_sum -= b_out_sum;
			a_sum -= a_out_sum;
		   
			r_out_sum -= stackIn.r;
			g_out_sum -= stackIn.g;
			b_out_sum -= stackIn.b;
			a_out_sum -= stackIn.a;
			
			p = ( x + (( ( p = y + radiusPlus1) < heightMinus1 ? p : heightMinus1 ) * width )) << 2;
			
			r_sum += ( r_in_sum += ( stackIn.r = pixels[p]));
			g_sum += ( g_in_sum += ( stackIn.g = pixels[p+1]));
			b_sum += ( b_in_sum += ( stackIn.b = pixels[p+2]));
			a_sum += ( a_in_sum += ( stackIn.a = pixels[p+3]));
		   
			stackIn = stackIn.next;
			
			r_out_sum += ( pr = stackOut.r );
			g_out_sum += ( pg = stackOut.g );
			b_out_sum += ( pb = stackOut.b );
			a_out_sum += ( pa = stackOut.a );
			
			r_in_sum -= pr;
			g_in_sum -= pg;
			b_in_sum -= pb;
			a_in_sum -= pa;
			
			stackOut = stackOut.next;
			
			yi += width;
		}
	}
	
	context.putImageData( imageData, top_x, top_y );
	
}


// function stackBlurCanvasRGB( id, top_x, top_y, width, height, radius )
// {
// 	if ( isNaN(radius) || radius < 1 ) return;
// 	radius |= 0;
	
// 	var canvas  = document.getElementById( id );
// 	var context = canvas.getContext("2d");
// 	var imageData;
	
// 	try {
// 	  try {
// 		imageData = context.getImageData( top_x, top_y, width, height );
// 	  } catch(e) {
	  
// 		// NOTE: this part is supposedly only needed if you want to work with local files
// 		// so it might be okay to remove the whole try/catch block and just use
// 		// imageData = context.getImageData( top_x, top_y, width, height );
// 		try {
// 			netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
// 			imageData = context.getImageData( top_x, top_y, width, height );
// 		} catch(e) {
// 			alert("Cannot access local image");
// 			throw new Error("unable to access local image data: " + e);
// 			return;
// 		}
// 	  }
// 	} catch(e) {
// 	  alert("Cannot access image");
// 	  throw new Error("unable to access image data: " + e);
// 	}
			
// 	var pixels = imageData.data;
			
// 	var x, y, i, p, yp, yi, yw, r_sum, g_sum, b_sum,
// 	r_out_sum, g_out_sum, b_out_sum,
// 	r_in_sum, g_in_sum, b_in_sum,
// 	pr, pg, pb, rbs;
			
// 	var div = radius + radius + 1;
// 	var w4 = width << 2;
// 	var widthMinus1  = width - 1;
// 	var heightMinus1 = height - 1;
// 	var radiusPlus1  = radius + 1;
// 	var sumFactor = radiusPlus1 * ( radiusPlus1 + 1 ) / 2;
	
// 	var stackStart = new BlurStack();
// 	var stack = stackStart;
// 	for ( i = 1; i < div; i++ )
// 	{
// 		stack = stack.next = new BlurStack();
// 		if ( i == radiusPlus1 ) var stackEnd = stack;
// 	}
// 	stack.next = stackStart;
// 	var stackIn = null;
// 	var stackOut = null;
	
// 	yw = yi = 0;
	
// 	var mul_sum = StackBlur.mul_table[radius];
// 	var shg_sum = StackBlur.shg_table[radius];
	
// 	for ( y = 0; y < height; y++ )
// 	{
// 		r_in_sum = g_in_sum = b_in_sum = r_sum = g_sum = b_sum = 0;
		
// 		r_out_sum = radiusPlus1 * ( pr = pixels[yi] );
// 		g_out_sum = radiusPlus1 * ( pg = pixels[yi+1] );
// 		b_out_sum = radiusPlus1 * ( pb = pixels[yi+2] );
		
// 		r_sum += sumFactor * pr;
// 		g_sum += sumFactor * pg;
// 		b_sum += sumFactor * pb;
		
// 		stack = stackStart;
		
// 		for( i = 0; i < radiusPlus1; i++ )
// 		{
// 			stack.r = pr;
// 			stack.g = pg;
// 			stack.b = pb;
// 			stack = stack.next;
// 		}
		
// 		for( i = 1; i < radiusPlus1; i++ )
// 		{
// 			p = yi + (( widthMinus1 < i ? widthMinus1 : i ) << 2 );
// 			r_sum += ( stack.r = ( pr = pixels[p])) * ( rbs = radiusPlus1 - i );
// 			g_sum += ( stack.g = ( pg = pixels[p+1])) * rbs;
// 			b_sum += ( stack.b = ( pb = pixels[p+2])) * rbs;
			
// 			r_in_sum += pr;
// 			g_in_sum += pg;
// 			b_in_sum += pb;
			
// 			stack = stack.next;
// 		}
		
		
// 		stackIn = stackStart;
// 		stackOut = stackEnd;
// 		for ( x = 0; x < width; x++ )
// 		{
// 			pixels[yi]   = (r_sum * mul_sum) >> shg_sum;
// 			pixels[yi+1] = (g_sum * mul_sum) >> shg_sum;
// 			pixels[yi+2] = (b_sum * mul_sum) >> shg_sum;
			
// 			r_sum -= r_out_sum;
// 			g_sum -= g_out_sum;
// 			b_sum -= b_out_sum;
			
// 			r_out_sum -= stackIn.r;
// 			g_out_sum -= stackIn.g;
// 			b_out_sum -= stackIn.b;
			
// 			p =  ( yw + ( ( p = x + radius + 1 ) < widthMinus1 ? p : widthMinus1 ) ) << 2;
			
// 			r_in_sum += ( stackIn.r = pixels[p]);
// 			g_in_sum += ( stackIn.g = pixels[p+1]);
// 			b_in_sum += ( stackIn.b = pixels[p+2]);
			
// 			r_sum += r_in_sum;
// 			g_sum += g_in_sum;
// 			b_sum += b_in_sum;
			
// 			stackIn = stackIn.next;
			
// 			r_out_sum += ( pr = stackOut.r );
// 			g_out_sum += ( pg = stackOut.g );
// 			b_out_sum += ( pb = stackOut.b );
			
// 			r_in_sum -= pr;
// 			g_in_sum -= pg;
// 			b_in_sum -= pb;
			
// 			stackOut = stackOut.next;

// 			yi += 4;
// 		}
// 		yw += width;
// 	}

	
// 	for ( x = 0; x < width; x++ )
// 	{
// 		g_in_sum = b_in_sum = r_in_sum = g_sum = b_sum = r_sum = 0;
		
// 		yi = x << 2;
// 		r_out_sum = radiusPlus1 * ( pr = pixels[yi]);
// 		g_out_sum = radiusPlus1 * ( pg = pixels[yi+1]);
// 		b_out_sum = radiusPlus1 * ( pb = pixels[yi+2]);
		
// 		r_sum += sumFactor * pr;
// 		g_sum += sumFactor * pg;
// 		b_sum += sumFactor * pb;
		
// 		stack = stackStart;
		
// 		for( i = 0; i < radiusPlus1; i++ )
// 		{
// 			stack.r = pr;
// 			stack.g = pg;
// 			stack.b = pb;
// 			stack = stack.next;
// 		}
		
// 		yp = width;
		
// 		for( i = 1; i <= radius; i++ )
// 		{
// 			yi = ( yp + x ) << 2;
			
// 			r_sum += ( stack.r = ( pr = pixels[yi])) * ( rbs = radiusPlus1 - i );
// 			g_sum += ( stack.g = ( pg = pixels[yi+1])) * rbs;
// 			b_sum += ( stack.b = ( pb = pixels[yi+2])) * rbs;
			
// 			r_in_sum += pr;
// 			g_in_sum += pg;
// 			b_in_sum += pb;
			
// 			stack = stack.next;
		
// 			if( i < heightMinus1 )
// 			{
// 				yp += width;
// 			}
// 		}
		
// 		yi = x;
// 		stackIn = stackStart;
// 		stackOut = stackEnd;
// 		for ( y = 0; y < height; y++ )
// 		{
// 			p = yi << 2;
// 			pixels[p]   = (r_sum * mul_sum) >> shg_sum;
// 			pixels[p+1] = (g_sum * mul_sum) >> shg_sum;
// 			pixels[p+2] = (b_sum * mul_sum) >> shg_sum;
			
// 			r_sum -= r_out_sum;
// 			g_sum -= g_out_sum;
// 			b_sum -= b_out_sum;
			
// 			r_out_sum -= stackIn.r;
// 			g_out_sum -= stackIn.g;
// 			b_out_sum -= stackIn.b;
			
// 			p = ( x + (( ( p = y + radiusPlus1) < heightMinus1 ? p : heightMinus1 ) * width )) << 2;
			
// 			r_sum += ( r_in_sum += ( stackIn.r = pixels[p]));
// 			g_sum += ( g_in_sum += ( stackIn.g = pixels[p+1]));
// 			b_sum += ( b_in_sum += ( stackIn.b = pixels[p+2]));
			
// 			stackIn = stackIn.next;
			
// 			r_out_sum += ( pr = stackOut.r );
// 			g_out_sum += ( pg = stackOut.g );
// 			b_out_sum += ( pb = stackOut.b );
			
// 			r_in_sum -= pr;
// 			g_in_sum -= pg;
// 			b_in_sum -= pb;
			
// 			stackOut = stackOut.next;
			
// 			yi += width;
// 		}
// 	}
	
// 	context.putImageData( imageData, top_x, top_y );
	
// }

function BlurStack()
{
	this.r = 0;
	this.g = 0;
	this.b = 0;
	this.a = 0;
	this.next = null;
}

module.exports = StackBlur;
},{}]},{},[3]);