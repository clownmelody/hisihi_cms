(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
 */
var CanvasSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

CanvasSlide = (function(_super) {
  __extends(CanvasSlide, _super);

  function CanvasSlide() {
    return CanvasSlide.__super__.constructor.apply(this, arguments);
  }

  CanvasSlide.prototype._enableAnimation = function(duration) {
    var cssStr;
    this.isAnimating = true;
    cssStr = "all 0s ease-in-out";
    this.$elementPages[0].style.webkitTransition = cssStr;
    return this.$elementSections[this.previousIndex].style.webkitAnimation = "myfirst 0s both ease";
  };

  CanvasSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return CanvasSlide;

})(Slide);

module.exports = CanvasSlide;



},{}],2:[function(require,module,exports){
var CoverSlide, Css, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

Css = window.RP.Css;

CoverSlide = (function(_super) {
  __extends(CoverSlide, _super);

  function CoverSlide() {
    return CoverSlide.__super__.constructor.apply(this, arguments);
  }

  CoverSlide.prototype._changeSwipePermission = function() {
    return this.swipePermission = true;
  };

  CoverSlide.prototype._enableAnimation = function(duration) {
    this.isAnimating = true;
    Css.regKeyFrames("slides-cover-scaleFromBottom", "{ to {  -webkit-transform: translateY(100%) scale(.5); transform: translateY(100%) scale(.5); } }");
    Css.regKeyFrames("slides-cover-scaleFromTop", "{ to { -webkit-transform: translateY(-100%) scale(.5); transform: translateY(-100%) scale(.5); } }");
    this.$elementPages[0].style.webkitTransition = "-webkit-transform .4s ease-in-out";
    this.$elementPages[0].style.Transition = "transform .4s ease-in-out";
    this.$elementSections[this.previousIndex].style.webkitAnimation = this.previousIndex < this.currentIndex ? "slides-cover-scaleFromBottom .8s ease both" : "slides-cover-scaleFromTop .8s ease both";
    this.$elementSections[this.previousIndex].style.zIndex = "0";
    return this.$elementSections[this.currentIndex].style.zIndex = "1";
  };

  CoverSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementPages[0].style.Transition = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementSections[this.previousIndex].style.Animation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementSections[this.currentIndex].style.zIndex = "";
  };

  return CoverSlide;

})(Slide);

module.exports = CoverSlide;



},{}],3:[function(require,module,exports){
var Css, CubeSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

Css = window.RP.Css;

CubeSlide = (function(_super) {
  __extends(CubeSlide, _super);

  function CubeSlide() {
    return CubeSlide.__super__.constructor.apply(this, arguments);
  }

  CubeSlide.prototype._changeSwipePermission = function() {
    return this.swipePermission = false;
  };

  CubeSlide.prototype._enableAnimation = function(duration) {
    this.isAnimating = true;
    Css.regKeyFrames("rotateCubeTopOut", "{0% { } 50% { -webkit-animation-timing-function: ease-out; animation-timing-function:ease-out; -webkit-transform: translateY(-50%) translateZ(-200px) rotateX(45deg); transform: translateY(-50%) translateZ(-200px) rotateX(45deg); } 100% { opacity: .3; -webkit-transform: translateY(-77%) translateZ(-286px) rotateX(58deg); transform:  translateY(-77%) translateZ(-286px) rotateX(58deg); }}");
    Css.regKeyFrames("rotateCubeTopIn", "{0% { opacity: .3; -webkit-transform: translateY(-30%) rotateX(-90deg); transform: translateY(-30%) rotateX(-90deg); } 50% { -webkit-animation-timing-function: ease-out; animation-timing-function: ease-out; -webkit-transform: translateY(-83%) translateZ(-230px) rotateX(-45deg); transform: translateY(-83%) translateZ(-230px) rotateX(-45deg); } 100% { -webkit-transform: translateY(-100%); transform: translateY(-100%);}}");
    Css.regKeyFrames("rotateCubeBottomOut", "{0% { } 50% { -webkit-animation-timing-function: ease-out; animation-timing-function: ease-out; -webkit-transform: translateY(50%) translateZ(-200px) rotateX(-45deg); transform: translateY(50%) translateZ(-200px) rotateX(-45deg); } 100% { opacity: .3; -webkit-transform: translateY(23%) translateZ(-286px) rotateX(-122deg); transform: translateY(23%) translateZ(-286px) rotateX(-122deg);}}");
    Css.regKeyFrames("rotateCubeBottomIn", "{0% { opacity: .3; } 50% { -webkit-animation-timing-function: ease-out; animation-timing-function: ease-out; -webkit-transform: translateY(83%) translateZ(-230px) rotateX(45deg); transform: translateY(80%) translateZ(-200px) rotateX(45deg); } 100% { -webkit-transform: translateY(100%); transform: translateY(100%); }}");
    this.$elementPages[0].style.webkitTransition = "-webkit-transform .6s linear 10s";
    if (this.previousIndex < this.currentIndex) {
      this.$elementSections[this.previousIndex].style.webkitAnimation = "rotateCubeTopOut .6s both linear";
      this.$elementSections[this.currentIndex].style.webkitAnimation = "rotateCubeTopIn .6s both linear";
    } else {
      this.$elementSections[this.previousIndex].style.webkitAnimation = "rotateCubeBottomOut .6s both linear";
      this.$elementSections[this.currentIndex].style.webkitAnimation = "rotateCubeBottomIn .6s both linear";
    }
    this.$elementSections[this.previousIndex].style.zIndex = "0";
    return this.$elementSections[this.currentIndex].style.zIndex = "1";
  };

  CubeSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementSections[this.currentIndex].style.webkitAnimation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementSections[this.currentIndex].style.zIndex = "";
  };

  return CubeSlide;

})(Slide);

module.exports = CubeSlide;



},{}],4:[function(require,module,exports){
var EasySlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

EasySlide = (function(_super) {
  __extends(EasySlide, _super);

  function EasySlide() {
    return EasySlide.__super__.constructor.apply(this, arguments);
  }

  EasySlide.prototype._changeSwipePermission = function() {
    return this.swipePermission = true;
  };

  EasySlide.prototype._enableAnimation = function(duration) {
    this.isAnimating = true;
    this.$elementPages[0].style.webkitTransition = "all .4s ease-out";
    return this.$elementPages[0].style.Transition = "all .4s ease-out";
  };

  EasySlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    return this.$elementPages[0].style.Transition = "";
  };

  return EasySlide;

})(Slide);

module.exports = EasySlide;



},{}],5:[function(require,module,exports){
var Css, FadeSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

Css = window.RP.Css;

FadeSlide = (function(_super) {
  __extends(FadeSlide, _super);

  function FadeSlide() {
    return FadeSlide.__super__.constructor.apply(this, arguments);
  }

  FadeSlide.prototype._changeSwipePermission = function() {
    return this.swipePermission = true;
  };

  FadeSlide.prototype._enableAnimation = function(duration) {
    this.isAnimating = true;
    Css.regKeyFrames("slides-fade-moveToTopFade", "{ to { opacity: 0.3; -webkit-transform: translateY(100%); transform: translateY(100%); } }");
    Css.regKeyFrames("slides-fade-moveToBottomFade", "{ to { opacity: 0.3; -webkit-transform: translateY(-100%); transform: translateY(-100%); } }");
    this.$elementPages[0].style.webkitTransition = "-webkit-transform .4s ease-in-out";
    this.$elementPages[0].style.Transition = "transform .4s ease-in-out";
    this.$elementSections[this.previousIndex].style.webkitAnimation = this.previousIndex < this.currentIndex ? "slides-fade-moveToTopFade .8s ease both" : "slides-fade-moveToBottomFade .8s ease both";
    this.$elementSections[this.previousIndex].style.Animation = this.previousIndex < this.currentIndex ? "slides-fade-moveToTopFade .8s ease both" : "slides-fade-moveToBottomFade .8s ease both";
    this.$elementSections[this.previousIndex].style.zIndex = "0";
    return this.$elementSections[this.currentIndex].style.zIndex = "1";
  };

  FadeSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementPages[0].style.Transition = "";
    this.$elementSections[this.previousIndex].style.Animation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementSections[this.currentIndex].style.zIndex = "";
  };

  return FadeSlide;

})(Slide);

module.exports = FadeSlide;



},{}],6:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
 */
var Css, FallSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

Css = window.RP.Css;

FallSlide = (function(_super) {
  __extends(FallSlide, _super);

  function FallSlide() {
    return FallSlide.__super__.constructor.apply(this, arguments);
  }

  FallSlide.prototype._changeSwipePermission = function() {
    return this.swipePermission = false;
  };

  FallSlide.prototype._enableAnimation = function(duration) {
    var cssStr;
    Css.regKeyFrames("fallFromTop", "{ 0% {} 20% { -webkit-transform: rotateZ(0deg) ; transform: rotateZ(10deg) ; -webkit-animation-timing-function: ease-out; animation-timing-function: ease-out; } 40% { -webkit-transform: rotateZ(10deg); transform: rotateZ(17deg); } 60% { -webkit-transform: rotateZ(17deg); transform: rotateZ(16deg); } 80% { -webkit-transform: rotateZ(16deg); transform: rotateZ(16deg); } 100% { -webkit-transform:  rotateZ(17deg) translateY(200%); transform:  rotateZ(17deg) translateY(200%); }}");
    Css.regKeyFrames("fallFromBottom", "{ 0% { -webkit-transform: rotateZ(0deg)  translateY(-100%); transform: rotateZ(0deg)  translateY(-100%); } 20% { -webkit-transform: rotateZ(10deg)  translateY(-100%); transform: rotateZ(10deg)  translateY(-100%); -webkit-animation-timing-function: ease-out; animation-timing-function: ease-out; } 40% { -webkit-transform: rotateZ(17deg) translateY(-100%); transform: rotateZ(17deg) translateY(-100%); } 60% { -webkit-transform: rotateZ(16deg) translateY(-100%); transform: rotateZ(16deg) translateY(-100%); } 100% { -webkit-transform:  rotateZ(17deg) translateY(0); transform:  rotateZ(17deg) translateY(0); }}");
    this.isAnimating = true;
    cssStr = "all .1s ease-in-out";
    this.$elementPages[0].style.webkitTransition = cssStr;
    this.$elementSections[this.previousIndex].style.zIndex = "1";
    this.$elementSections[this.currentIndex].style.zIndex = "0";
    if (this.previousIndex < this.currentIndex) {
      this.$elementSections[this.previousIndex].style.webkitTransform = "translateY(100%)";
      return this.$elementSections[this.previousIndex].style.webkitAnimation = "fallFromTop 1s both ease-in";
    } else {
      return this.$elementSections[this.previousIndex].style.webkitAnimation = "fallFromBottom .8s both ease-in";
    }
  };

  FallSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return FallSlide;

})(Slide);

module.exports = FallSlide;



},{}],7:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
 */
var FirstSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

FirstSlide = (function(_super) {
  __extends(FirstSlide, _super);

  function FirstSlide() {
    return FirstSlide.__super__.constructor.apply(this, arguments);
  }

  FirstSlide.prototype._enableAnimation = function(duration) {
    var cssStr;
    this.isAnimating = true;
    cssStr = "all 0s ease-in-out";
    this.$elementPages[0].style.webkitTransition = cssStr;
    return this.$elementSections[this.previousIndex].style.webkitAnimation = "myfirst 0s both ease";
  };

  FirstSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return FirstSlide;

})(Slide);

module.exports = FirstSlide;



},{}],8:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
 */
var Css, FlipSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

Css = window.RP.Css;

FlipSlide = (function(_super) {
  __extends(FlipSlide, _super);

  function FlipSlide() {
    return FlipSlide.__super__.constructor.apply(this, arguments);
  }

  FlipSlide.prototype._changeSwipePermission = function() {
    return this.swipePermission = true;
  };

  FlipSlide.prototype._enableAnimation = function(duration) {
    this.isAnimating = true;
    Css.regKeyFrames("flipOutTop", "{to { -webkit-transform: translateZ(-1000px) rotateX(90deg); transform: translateZ(-1000px) rotateX(90deg); opacity: 0.2; }}");
    Css.regKeyFrames("flipInBottom", "{from { -webkit-transform: translateZ(-1000px) rotateX(-90deg); transform: translateZ(-1000px) rotateX(-90deg); opacity: 0.2; }}");
    Css.regKeyFrames("flipOutBottom", "{to { -webkit-transform: translateZ(-1000px) rotateX(-90deg); transform: translateZ(-1000px) rotateX(-90deg); opacity: 0.2; }}");
    Css.regKeyFrames("flipInTop", "{from { -webkit-transform: translateZ(1000px) rotateX(-90deg); transform: translateZ(1000px) rotateX(-90deg); opacity: 0.2; }}");
    if (this.previousIndex < this.currentIndex) {
      this.$elementSections[this.previousIndex].style.webkitTransform = "translateY(100%)";
      this.$elementSections[this.previousIndex].style.webkitTransformOrigin = "187.5px 1000.5px";
      this.$elementSections[this.currentIndex].style.webkitTransformOrigin = "187.5px 1000.5px";
      this.$elementSections[this.previousIndex].style.webkitAnimation = "flipOutTop 3s both ease-in";
      return this.$elementSections[this.currentIndex].style.webkitAnimation = "flipInBottom 3s both ease-out";
    } else {
      this.$elementSections[this.previousIndex].style.webkitTransform = "translateY(-100%)";
      this.$elementSections[this.previousIndex].style.webkitTransformOrigin = "50% -100%";
      this.$elementSections[this.previousIndex].style.webkitAnimation = "flipOutBottom .9s both ease-in";
      return this.$elementSections[this.currentIndex].style.webkitAnimation = "flipInTop .9s both ease-out";
    }
  };

  FlipSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.webkitTransformOrigin = "";
    this.$elementSections[this.currentIndex].style.webkitTransformOrigin = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return FlipSlide;

})(Slide);

module.exports = FlipSlide;



},{}],9:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
 */
var Css, GlueSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

Css = window.RP.Css;

GlueSlide = (function(_super) {
  __extends(GlueSlide, _super);

  function GlueSlide() {
    return GlueSlide.__super__.constructor.apply(this, arguments);
  }

  GlueSlide.prototype._changeSwipePermission = function() {
    return this.swipePermission = true;
  };

  GlueSlide.prototype._enableAnimation = function(duration) {
    var cssStr;
    this.isAnimating = true;
    Css.regKeyFrames("moveFromBottom", "{from { -webkit-transform: translateY(100%); transform: translateY(100%); }}");
    Css.regKeyFrames("rotateBottomSideFirst", "{0% { } 40% { -webkit-transform: rotateX(-15deg); transform: rotateX(-15deg); opacity: .8; -webkit-animation-timing-function: ease-out; animation-timing-function: ease-out; } 100% { -webkit-transform: scale(0.8) translateZ(-200px); transform: scale(0.8) translateZ(-200px); opacity:0; }}");
    Css.regKeyFrames("rotateTopSideFirst", "{0% { } 40% { -webkit-transform: rotateX(15deg); opacity: .8; transform: rotateX(15deg); opacity: .8; -webkit-animation-timing-function: ease-out; animation-timing-function: ease-out; } 100% { -webkit-transform: scale(0.8) translateZ(-200px); opacity:0; transform: scale(0.8) translateZ(-200px); opacity:0; }}");
    Css.regKeyFrames("", "{from { -webkit-transform: translateY(-100%); transform: translateY(-100%); }}");
    cssStr = "all 0.5s ease-in-out";
    this.$elementPages[0].style.webkitTransition = cssStr;
    this.$elementPages[0].style.Transition = cssStr;
    this.$elementSections[this.previousIndex].style.webkitAnimation = "glue 1s both ease-in";
    this.$elementSections[this.previousIndex].style.Animation = "glue 1s both ease-in";
    if (this.previousIndex < this.currentIndex) {
      this.$elementSections[this.previousIndex].style.webkitAnimation = "rotateBottomSideFirst .6s both ease";
      return this.$elementSections[this.previousIndex].style.Animation = "rotateBottomSideFirst .6s both ease";
    } else {
      this.$elementSections[this.previousIndex].style.webkitAnimation = "rotateTopSideFirst .6s both ease";
      return this.$elementSections[this.previousIndex].style.Animation = "rotateTopSideFirst .6s both ease";
    }
  };

  GlueSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.currentIndex].style.webkitTransform = "";
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.Transition = "";
    this.$elementSections[this.currentIndex].style.Transform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementSections[this.previousIndex].style.Animation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return GlueSlide;

})(Slide);

module.exports = GlueSlide;



},{}],10:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
 */
var MySlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

MySlide = (function(_super) {
  __extends(MySlide, _super);

  function MySlide() {
    return MySlide.__super__.constructor.apply(this, arguments);
  }

  MySlide.prototype._enableAnimation = function(duration) {
    var cssStr;
    this.isAnimating = true;
    cssStr = "all 5s ease-in-out";
    this.$elementPages[0].style.webkitTransition = cssStr;
    return this.$elementSections[this.previousIndex].style.webkitAnimation = "Myslide 1s both ease-in";
  };

  MySlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return MySlide;

})(Slide);

module.exports = MySlide;



},{}],11:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
 */
var NewspaperSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

NewspaperSlide = (function(_super) {
  __extends(NewspaperSlide, _super);

  function NewspaperSlide() {
    return NewspaperSlide.__super__.constructor.apply(this, arguments);
  }

  NewspaperSlide.prototype._enableAnimation = function(duration) {
    var cssStr;
    this.isAnimating = true;
    cssStr = "all 5s ease-in-out";
    this.$elementPages[0].style.webkitTransition = cssStr;
    return this.$elementSections[this.previousIndex].style.webkitAnimation = "spaper 1s both ease-in";
  };

  NewspaperSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return NewspaperSlide;

})(Slide);

module.exports = NewspaperSlide;



},{}],12:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
 */
var PushTopSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

PushTopSlide = (function(_super) {
  __extends(PushTopSlide, _super);

  function PushTopSlide() {
    return PushTopSlide.__super__.constructor.apply(this, arguments);
  }

  PushTopSlide.prototype._enableAnimation = function(duration) {
    var cssStr;
    this.isAnimating = true;
    cssStr = "all 5s ease-in-out";
    this.$elementPages[0].style.webkitTransition = cssStr;
    return this.$elementSections[this.previousIndex].style.webkitAnimation = "pushtop 1s both ease-in";
  };

  PushTopSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.webkitAnimation = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return PushTopSlide;

})(Slide);

module.exports = PushTopSlide;



},{}],13:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
 */
var RotateXSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

RotateXSlide = (function(_super) {
  __extends(RotateXSlide, _super);

  function RotateXSlide() {
    return RotateXSlide.__super__.constructor.apply(this, arguments);
  }

  RotateXSlide.prototype._enableAnimation = function(duration) {
    var cssStr, previousDuration, sectionTransformStr, translateY;
    this.isAnimating = true;
    cssStr = "all 0.5s ease-in-out";
    this.$elementPages[0].style.webkitTransition = cssStr;
    translateY = this.previousIndex < this.currentIndex ? this._getHeight() : -this._getHeight();
    previousDuration = duration;
    this.$elementSections[this.previousIndex].style.webkitTransition = "all 0.1s ease-in-out";
    sectionTransformStr = "rotateX(90deg)";
    this.$elementSections[this.previousIndex].style.webkitTransform = sectionTransformStr;
    this.$elementSections[this.previousIndex].style.webkitPerspective = "500";
    this.$elementSections[this.currentIndex].style.zIndex = "1";
    return this.$elementSections[this.previousIndex].style.zIndex = "0";
  };

  RotateXSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return RotateXSlide;

})(Slide);

module.exports = RotateXSlide;



},{}],14:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
 */
var RotateYSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

RotateYSlide = (function(_super) {
  __extends(RotateYSlide, _super);

  function RotateYSlide() {
    return RotateYSlide.__super__.constructor.apply(this, arguments);
  }

  RotateYSlide.prototype._enableAnimation = function(duration) {
    var cssStr, previousDuration, sectionTransformStr, translateY;
    this.isAnimating = true;
    cssStr = "all 0.5s ease-in-out";
    this.$elementPages[0].style.webkitTransition = cssStr;
    translateY = this.previousIndex < this.currentIndex ? this._getHeight() : -this._getHeight();
    previousDuration = duration;
    this.$elementSections[this.previousIndex].style.webkitTransition = "all 0.1s ease-in-out";
    sectionTransformStr = "rotateY(90deg)";
    this.$elementSections[this.previousIndex].style.webkitTransform = sectionTransformStr;
    this.$elementSections[this.previousIndex].style.webkitPerspective = "500";
    this.$elementSections[this.currentIndex].style.zIndex = "1";
    return this.$elementSections[this.previousIndex].style.zIndex = "0";
  };

  RotateYSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return RotateYSlide;

})(Slide);

module.exports = RotateYSlide;



},{}],15:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Define The Slide Effect Between Every Two Pages
    疯狂转转。。。。
 */
var RotateZSlide, Slide,
  __hasProp = {}.hasOwnProperty,
  __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

Slide = window.RP.Slide;

RotateZSlide = (function(_super) {
  __extends(RotateZSlide, _super);

  function RotateZSlide() {
    return RotateZSlide.__super__.constructor.apply(this, arguments);
  }

  RotateZSlide.prototype._enableAnimation = function(duration) {
    var cssStr, sectionTransformStr, translateY;
    this.isAnimating = true;
    cssStr = "all 0.5s ease-in-out";
    this.$elementPages[0].style.webkitTransition = cssStr;
    this.$elementPages[0].style.webkitTransform = "rotateZ(-30000deg)";
    translateY = this.previousIndex < this.currentIndex ? this._getHeight() : -this._getHeight();
    this.$elementSections[this.previousIndex].style.webkitTransition = "all 0.5s ease-in-out";
    sectionTransformStr = "rotateZ(30000deg)";
    this.$elementSections[this.previousIndex].style.webkitTransform = sectionTransformStr;
    this.$elementSections[this.previousIndex].style.webkitPerspective = "500";
    this.$elementSections[this.currentIndex].style.zIndex = "1";
    return this.$elementSections[this.previousIndex].style.zIndex = "0";
  };

  RotateZSlide.prototype._disableAnimation = function() {
    this.isAnimating = false;
    this.$elementPages[0].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransition = "";
    this.$elementSections[this.previousIndex].style.webkitTransform = "";
    this.$elementSections[this.currentIndex].style.zIndex = "";
    this.$elementSections[this.previousIndex].style.zIndex = "";
    return this.$elementPages[0].style.backgroundColor = "";
  };

  return RotateZSlide;

})(Slide);

module.exports = RotateZSlide;



},{}],16:[function(require,module,exports){

/*
    Last Edit: Jensen 0923
    Description: Everytime You Add an Slide File e.g. ./cover.coffee,
        You Should Add Require Path in This File
 */
var slides;

slides = {};

slides.canvas = require("./canvas.coffee");

slides.cover = require("./cover.coffee");

slides.cube = require("./cube.coffee");

slides.easy = require("./easy.coffee");

slides.fade = require("./fade.coffee");

slides.fall = require("./fall.coffee");

slides.first = require("./first.coffee");

slides.flip = require("./flip.coffee");

slides.glue = require("./glue.coffee");

slides.mySlide = require("./mySlide.coffee");

slides.newspaper = require("./newspaper.coffee");

slides.pushTop = require("./pushTop.coffee");

slides.rotateX = require("./rotateX.coffee");

slides.rotateY = require("./rotateY.coffee");

slides.rotateZ = require("./rotateZ.coffee");

window.RP.slides = slides;



},{"./canvas.coffee":1,"./cover.coffee":2,"./cube.coffee":3,"./easy.coffee":4,"./fade.coffee":5,"./fall.coffee":6,"./first.coffee":7,"./flip.coffee":8,"./glue.coffee":9,"./mySlide.coffee":10,"./newspaper.coffee":11,"./pushTop.coffee":12,"./rotateX.coffee":13,"./rotateY.coffee":14,"./rotateZ.coffee":15}]},{},[16]);