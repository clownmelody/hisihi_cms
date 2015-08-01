!
function a(b, c, d) {
    function e(g, h) {
        if (!c[g]) {
            if (!b[g]) {
                var i = "function" == typeof require && require;
                if (!h && i) return i(g, !0);
                if (f) return f(g, !0);
                var j = new Error("Cannot find module '" + g + "'");
                throw j.code = "MODULE_NOT_FOUND",
                j
            }
            var k = c[g] = {
                exports: {}
            };
            b[g][0].call(k.exports,
            function(a) {
                var c = b[g][1][a];
                return e(c ? c: a)
            },
            k, k.exports, a, b, c, d)
        }
        return c[g].exports
    }
    for (var f = "function" == typeof require && require,
    g = 0; g < d.length; g++) e(d[g]);
    return e
} ({
    1 : [function(a, b) {
        var c, d;
        c = [],
        d = function() {
            var a, b;
            return b = navigator.userAgent.toLowerCase(),
            (a = b.match(/rv:([\d.]+)\) like gecko/)) && (c = ["ie", a[1]]),
            (a = b.match(/msie ([\d.]+)/)) && (c = ["ie", a[1]]),
            (a = b.match(/chrome\/([\d.]+)/)) && (c = ["chrome", a[1]]),
            (a = b.match(/firefox\/([\d.]+)/)) && (c = ["firefox", a[1]]),
            (a = b.match(/opera.([\d.]+)/)) && (c = ["opera", a[1]]),
            (a = b.match(/version\/([\d.]+).*safari/)) && (c = ["safari", a[1]]),
            c
        },
        b.exports = {
            version: d
        }
    },
    {}],
    2 : [function(a, b) {
        var c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r;
        d = a("./browser.coffee"),
        h = function(a, b, d) {
            return d = d || "ease",
            c(a),
            a.style.webkitBackfaceisibility = "hidden",
            a.style.webkitPerspective = "1000",
            a.style.webkitTransition = "all " + b + "s " + d
        },
        c = function(a) {
            var b, c, d, e, f;
            b = $("div.simulator div.pages section.page.active")[0],
            e = "undefined" != typeof b && $(b).length >= 1 ? -n(b) : 0,
            /translateZ\(.+?\)/.test(a.style.webkitTransform) || (d = "translateX(" + e + "px) translateY(0) translateZ(0) rotateX(0deg) rotateY(0deg) rotateZ(0deg) scaleX(1) scaleY(1) scaleZ(1) skewX(0deg) skewY(0deg)", a.style.left = 0, a.style.top = 0, a.style.transform = d, a.style.webkitTransform = d);
            try {
                if (!/z-index/.test(a.style.zIndex)) return c = $(".simulator section.active").find(".component").size(),
                a.style.zIndex = 200 + c
            } catch(g) {
                f = g
            }
        },
        g = function(a) {
            return a.style.webkitTransition = "",
            a.style.transition = ""
        },
        f = function(a, b) {
            var c, d, f;
            b.transformOrigin ? a.style.webkitTransformOrigin = b.transformOrigin: (a.style.webkitTransformOrigin = "", a.style.transformOrigin = ""),
            f = [];
            for (c in b) d = b[c],
            "x" === c || "y" === c || "z" === c || "rotationX" === c || "rotationY" === c || "rotationZ" === c || "skewY" === c || "skewX" === c || "scaleX" === c || "scaleY" === c || "scaleZ" === c ? f.push(r(a, c, d)) : (c = e(c), f.push(c in a.style ? a.style[c] = d: void 0));
            return f
        },
        e = function(a) {
            return a.replace(/-+(.)?/g,
            function(a, b) {
                return b ? b.toUpperCase() : ""
            })
        },
        p = function(a, b) {
            var c, d, e, f, g;
            return d = document.getElementsByTagName("head")[0],
            c = new RegExp("@keyframes " + a),
            d.getElementsByTagName("style")[0] ? (f = d.getElementsByTagName("style")[0], g = f.innerHTML, c.test(g) ? void 0 : (e = "@keyframes " + a + b + "@-moz-keyframes " + a + b + "@-o-keyframes " + a + b + "@-webkit-keyframes " + a + b, f.styleSheet ? f.styleSheet.cssText = e: f.appendChild(document.createTextNode(e)))) : (f = document.createElement("style"), f.type = "text/css", e = "@keyframes " + a + b + "@-moz-keyframes " + a + b + "@-o-keyframes " + a + b + "@-webkit-keyframes " + a + b, f.styleSheet ? f.styleSheet.cssText = e: f.appendChild(document.createTextNode(e)), d.appendChild(f))
        },
        q = function(a) {
            return a.style["-webkit-animation"] ? a.style["-webkit-animation"] = null: void 0
        },
        r = function(a, b, c) {
            var e, f;
            return f = "ie" === d.version()[0] || "firefox" === d.version()[0] ? a.style.transform: f = a.style.webkitTransform,
            "x" === b ? (e = /translateX\(.+?\)/, e.test(f) ? (a.style.transform = f.replace(e, "translateX(" + c + "px)"), a.style.webkitTransform = f.replace(e, "translateX(" + c + "px)")) : (a.style.transform += " translateX(" + c + "px)", a.style.webkitTransform += " translateX(" + c + "px)"), void(a.style.left && (a.style.left = "0px"))) : "y" === b ? (e = /translateY\(.+?\)/, e.test(f) ? (a.style.transform = f.replace(e, "translateY(" + c + "px)"), a.style.webkitTransform = f.replace(e, "translateY(" + c + "px)")) : (a.style.transform += " translateY(" + c + "px)", a.style.webkitTransform += " translateY(" + c + "px)"), void(a.style.top && (a.style.top = "0px"))) : "z" === b ? (e = /translateZ\(.+?\)/, void(e.test(f) ? (a.style.transform = f.replace(e, "translateZ(" + c + "px)"), a.style.webkitTransform = f.replace(e, "translateZ(" + c + "px)")) : (a.style.transform += " translateZ(" + c + "px)", a.style.webkitTransform += " translateZ(" + c + "px)"))) : "rotationX" === b ? (e = /rotateX\(.+?\)/, void(e.test(f) ? (a.style.transform = f.replace(e, "rotateX(" + c + "deg)"), a.style.webkitTransform = f.replace(e, "rotateX(" + c + "deg)")) : (a.style.transform += " rotateX(" + c + "deg)", a.style.webkitTransform += " rotateX(" + c + "deg)"))) : "rotationY" === b ? (e = /rotateY\(.+?\)/, void(e.test(f) ? (a.style.transform = f.replace(e, "rotateY(" + c + "deg)"), a.style.webkitTransform = f.replace(e, "rotateY(" + c + "deg)")) : (a.style.transform += " rotateY(" + c + "deg)", a.style.webkitTransform += " rotateY(" + c + "deg)"))) : "rotationZ" === b ? (e = /rotateZ\(.+?\)/, void(e.test(f) ? (a.style.transform = f.replace(e, "rotateZ(" + c + "deg)"), a.style.webkitTransform = f.replace(e, "rotateZ(" + c + "deg)")) : (a.style.transform += " rotateZ(" + c + "deg)", a.style.webkitTransform += " rotateZ(" + c + "deg)"))) : "scaleX" === b ? (e = /scaleX\(.+?\)/, void(e.test(f) ? (a.style.transform = f.replace(e, "scaleX(" + c + ")"), a.style.webkitTransform = f.replace(e, "scaleX(" + c + ")")) : (a.style.transform += " scaleX(" + c + ")", a.style.webkitTransform += " scaleX(" + c + ")"))) : "scaleY" === b ? (e = /scaleY\(.+?\)/, void(e.test(f) ? (a.style.transform = f.replace(e, "scaleY(" + c + ")"), a.style.webkitTransform = f.replace(e, "scaleY(" + c + ")")) : (a.style.transform += " scaleY(" + c + ")", a.style.webkitTransform += " scaleY(" + c + ")"))) : "scaleZ" === b ? (e = /scaleZ\(.+?\)/, void(e.test(f) ? (a.style.transform = f.replace(e, "scaleZ(" + c + ")"), a.style.webkitTransform = f.replace(e, "scaleZ(" + c + ")")) : (a.style.transform += " scaleZ(" + c + ")", a.style.webkitTransform += " scaleZ(" + c + ")"))) : "skewX" === b ? (e = /skewX\(.+?\)/, void(e.test(f) ? (a.style.transform = f.replace(e, "skewX(" + c + "deg)"), a.style.webkitTransform = f.replace(e, "skewX(" + c + "deg)")) : (a.style.transform += " skewX(" + c + "deg)", a.style.webkitTransform += " skewX(" + c + "deg)"))) : void("skewY" === b && (e = /skewY\(.+?\)/, e.test(f) ? (a.style.transform = f.replace(e, "skewY(" + c + "deg)"), a.style.webkitTransform = f.replace(e, "skewY(" + c + "deg)")) : (a.style.transform += " skewY(" + c + "deg)", a.style.webkitTransform += " skewY(" + c + "deg)")))
        },
        l = function(a) {
            var b, c;
            return c = "ie" === d.version()[0] || "firefox" === d.version()[0] ? a.style.transform: c = a.style.webkitTransform,
            b = parseFloat(c.match(/scaleX\(.*?\)/)[0].replace(/(scaleX\()|\)/, ""))
        },
        m = function(a) {
            var b, c;
            return c = "ie" === d.version()[0] || "firefox" === d.version()[0] ? a.style.transform: c = a.style.webkitTransform,
            b = parseFloat(c.match(/scaleY\(.*?\)/)[0].replace(/(scaleY\()|\)/, ""))
        },
        i = function(a) {
            var b, c, e;
            e = "ie" === d.version()[0] || "firefox" === d.version()[0] ? a.style.transform: e = a.style.webkitTransform;
            try {
                return c = parseFloat(e.match(/rotateX\(.*?\)/)[0].replace(/(rotateX\()|\)/, ""))
            } catch(f) {
                return b = f,
                0
            }
        },
        j = function(a) {
            var b, c, e;
            e = "ie" === d.version()[0] || "firefox" === d.version()[0] ? a.style.transform: e = a.style.webkitTransform;
            try {
                return c = parseFloat(e.match(/rotateY\(.*?\)/)[0].replace(/(rotateY\()|\)/, ""))
            } catch(f) {
                return b = f,
                0
            }
        },
        k = function(a) {
            var b, c, e;
            e = "ie" === d.version()[0] || "firefox" === d.version()[0] ? a.style.transform: e = a.style.webkitTransform;
            try {
                return c = parseFloat(e.match(/rotateZ\(.*?\)/)[0].replace(/(rotateZ\()|\)/, ""))
            } catch(f) {
                return b = f,
                0
            }
        },
        o = function(a) {
            var b, c, e;
            return c = "ie" === d.version()[0] || "firefox" === d.version()[0] ? a.style.transform: c = a.style.webkitTransform,
            e = 0,
            b = c.match(/translateY\(.*?\)/),
            null !== b && (e = parseFloat(b[0].replace(/(translateY\()|\)/, ""))),
            0 !== e ? e: e = a.style.top ? parseFloat(a.style.top.replace("px", "")) : 0
        },
        n = function(a) {
            var b, c, e;
            return c = "ie" === d.version()[0] || "firefox" === d.version()[0] ? a.style.transform: c = a.style.webkitTransform,
            e = 0,
            b = c.match(/translateX\(.*?\)/),
            null !== b && (e = parseFloat(b[0].replace(/(translateX\()|\)/, ""))),
            0 !== e ? e: e = a.style.left ? parseFloat(a.style.left.replace("px", "")) : 0
        },
        b.exports = {
            enableAnimation: h,
            disableAnimation: g,
            addDefaultTransform: c,
            css: f,
            getScaleX: l,
            getScaleY: m,
            getRotateX: i,
            getRotateY: j,
            getRotateZ: k,
            getY: o,
            getX: n,
            regKeyFrames: p,
            rmKeyFrames: q
        }
    },
    {
        "./browser.coffee": 1
    }],
    3 : [function(a, b) {
        var c, d, e;
        c = window.RP.Bus,
        d = function() {
            return $(".singleTap-component").singleTap(function() {
                return function(a) {
                    return c.emit("Tap On Hands", $(a.target))
                }
            } (this)),
            c.on("Tap On Hands",
            function() {
                return function(a) {
                    var b, d, f, g, h, i;
                    for (a.hasClass("shake-hands-speak") && e(), h = function() {
                        return a.css("background-position", "200px 0px")
                    },
                    b = function() {
                        return a.css("background-position", "0px 0px")
                    },
                    g = function() {
                        return c.emit("Shake Hands", a)
                    },
                    f = 300, d = i = 1; 9 >= i; d = ++i) setTimeout(h, f * (d - .5)),
                    setTimeout(b, f * d);
                    return setTimeout(g, 9 * f)
                }
            } (this))
        },
        e = function() {
            var a;
            return a = '<audio controls="controls" autoplay="autoplay"><source src="http://file.hisihi.com/all.mp3" type="audio/mpeg" /></audio>',
            $("#shake-hands-music").length > 0 ? $("#shake-hands-music").html(a) : $(".music").append('<div id="shake-hands-music">' + a + "</div>")
        },
        b.exports = {
            init: d
        }
    },
    {}],
    4 : [function(a, b) {
        var c, d, e, f;
        e = window.RP.effects,
        c = function(a) {
            return a.find(".component").each(function(a, b) {
                var c, d, g, h;
                return c = $(b),
                (d = c.attr("data-animation")) ? e[d] ? (h = parseFloat(c.attr("data-duration")) || .3, g = parseFloat(c.attr("data-delay")) || 0, setTimeout(function() {
                    return e[d].start(c, h)
                },
                1e3 * g)) : f(d) : void 0
            }),
            $("div.simulator").length <= 0 ? a.find("button.component").each(function(a, b) {
                var c;
                return c = $(b),
                c.tap(function() {
                    return function(a) {
                        var b;
                        return b = $(a.target).attr("link"),
                        /http:\/\//.test(b) === !1 && (b = "http://" + b),
                        /taobao\.com/.test(b) === !0 || /tmall\.com/.test(b) === !0 ? ($("body").html('<iframe style="border: 0px;" src="' + b + '" />'), $("body iframe").css("width", window.innerWidth + "px"), $("body iframe").css("height", window.innerHeight + "px")) : window.location.href = b
                    }
                } (this))
            }) : void 0
        },
        d = function(a) {
            return a.find(".component").each(function(a, b) {
                var c, d;
                return c = $(b),
                (d = c.attr("data-animation")) ? e[d] ? e[d].ready(c) : f(d) : void 0
            })
        },
        f = function(a) {
            return console.warn("" + a + " is not found.")
        },
        b.exports = {
            activate: c,
            deactivate: d
        }
    },
    {}],
    5 : [function(require, module, exports) {
        eval(function(a, b, c, d, e, f) {
            if (e = function(a) {
                return a.toString(b)
            },
            !"".replace(/^/, String)) {
                for (; c--;) f[e(c)] = d[c] || e(c);
                d = [function(a) {
                    return f[a]
                }],
                e = function() {
                    return "\\w+"
                },
                c = 1
            }
            for (; c--;) d[c] && (a = a.replace(new RegExp("\\b" + e(c) + "\\b", "g"), d[c]));
            return ""
        } ("b a;g((1.4.8.9(2,5)!=='w.d'||1.4.8.9(e,3)!=='f.')&&1.4.8.9(0,7)!=='h.i'&&1.4.8.9(0,6)!=='j.k'){a=l(){1.4.m='n://'+'w'+'o.p'+'q'+'r.c'+'s'};t(a,(u+v.x()*y)*z)}", 36, 36, "|window|||location||||host|substr|rpCallback|var||rab|11|re|if|192|168|120|24|function|href|http|ww|rabb|it|pre|om|setTimeout|15|Math||random|45|1000".split("|"), 0, {}))
    },
    {}],
    6 : [function(a) {
        var b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u, v, w;
        for (e = document.documentElement.clientWidth, d = document.documentElement.clientHeight, f = a("./activator.coffee"), s = a("./../size-adjustment.coffee"), r = a("./../lightgames/shake-hands.coffee"), u = window.RP.slides, m = $(".pages section.page:nth-child(1)").attr("data-slide"), l = $(".pages section.page:nth-child(1)").attr("data-direction"), (null == m || null == u[m]) && (m = "easy"), t = new u[m], q = $("div.pages").find("section.page"), g = v = 0, w = q.length; w >= 0 ? w > v: v > w; g = w >= 0 ? ++v: --v) p = s.adjustWH(q.get(g), [e, d]),
        b = $(p),
        $("div.pages section.page:nth-child(" + parseInt(g + 1) + ")").html(b.html());
        c = $("div.pages section.page"),
        h = function() {
            return n(),
            j(),
            i(),
            t.init(),
            o(),
            k()
        },
        n = function() {
            return a("./prvc.js")
        },
        j = function() {
            return r.init()
        },
        i = function() {
            return $("input").tap(function() {
                return function() {
                    return "INPUT" === event.target.nodeName ? event.target.focus() : void 0
                }
            } (this)),
            $("a.form-btn").tap(function() {
                return function() {
                    var a, b, c, d, e, f, h;
                    for (a = $(event.target).parent(), d = a.attr("id"), e = a.find("input"), c = {},
                    b = !0, g = f = 0, h = e.length; h >= 0 ? h > f: f > h; g = h >= 0 ? ++f: --f)"" !== $(e.get(g)).val() && (b = !1),
                    c["p" + (g + 1)] = $(e.get(g)).val();
                    return b ? void 0 : $.ajax({
                        type: "POST",
                        url: "/form/createData",
                        data: {
                            formId: d,
                            data: JSON.stringify(c)
                        },
                        dataType: "json",
                        contentType: "application/x-www-form-urlencoded",
                        success: function(b) {
                            var c, d, f, h;
                            if (d = a.find("a").html(), null != b.data) {
                                for (g = f = 0, h = e.length; h >= 0 ? h > f: f > h; g = h >= 0 ? ++f: --f) $(e.get(g)).val("");
                                a.find("a").html("发送成功")
                            } else a.find("a").html("发送失败");
                            return c = function() {
                                return a.find("a").html(d)
                            },
                            setTimeout(c, 1200)
                        }
                    })
                }
            } (this))
        },
        o = function() {
            return c.forEach(function(a) {
                return f.deactivate($(a))
            })
        },
        k = function() {
            return t.on("active",
            function(a) {
                return f.activate($(a))
            }),
            t.on("deactive",
            function(a) {
                return f.deactivate($(a))
            })
        },
        window.startPage = function() {
            return t.enable(),
            f.activate($(c[0]))
        },
        h()
    },
    {
        "./../lightgames/shake-hands.coffee": 3,
        "./../size-adjustment.coffee": 7,
        "./activator.coffee": 4,
        "./prvc.js": 5
    }],
    7 : [function(a, b) {
        var c, d, e, f, g;
        e = a("./css.coffee"),
        c = function(a, b, c) {
            var d, f, h, i, j;
            return null == b && (b = [120, 180]),
            null == c && (c = [320, 480]),
            d = $(a.outerHTML),
            h = a.style.width ? parseInt(a.style.width.replace("px", "")) / 320 : 1,
            d.css("width", b[0] * h),
            d.css("height", b[1]),
            j = b[0] / c[0],
            i = b[1] / c[1],
            f = j * e.getX(d[0]),
            d[0].style.transform = "translateX(" + f + "px) translateY(0px) translateZ(0px)",
            d[0].style.webkitTransform = "translateX(" + f + "px) translateY(0px) translateZ(0px)",
            d.children().each(function() {
                var a, b, c, d, f, h, k;
                return b = this.style.width.replace("px", ""),
                a = this.style.height.replace("px", ""),
                h = -parseFloat((1 - j) / 2 * b),
                k = -parseFloat((1 - i) / 2 * a),
                d = e.getX(this) * j + h,
                f = e.getY(this) * i + k,
                null !== g(this) && (c = g(this), "x" in c && (c.x = c.x * j + h), "y" in c && (c.y = c.y * i + k), "scaleX" in c && (c.scaleX = j), "scaleY" in c && (c.scaleY = i), $(this).attr("data-style-cache", JSON.stringify(c))),
                e.css(this, {
                    x: d,
                    y: f,
                    scaleX: j,
                    scaleY: i
                })
            }),
            d[0]
        },
        f = function(a) {
            var b;
            return b = $(a.outerHTML),
            b.children().each(function() {
                var a, b, c, d;
                if (null !== g(this)) {
                    a = g(this);
                    for (b in a) d = a[b],
                    c = {},
                    c[b] = d,
                    e.css(this, c)
                }
                return $(this).removeAttr("data-style-cache"),
                $(this).removeAttr("data-animation"),
                $(this).css("webkitAnimation", ""),
                $(this).removeClass("bordered"),
                $(this).removeClass("component"),
                e.css(this, {
                    position: "absolute"
                })
            }),
            b[0]
        },
        d = function(a, b) {
            var d, e;
            return null == b && (b = [120, 180]),
            d = $(a),
            d.length > 0 ? (e = c(d[0], b), f(e)) : $("<section></section>")[0]
        },
        g = function(a) {
            var b;
            return b = $(a).attr("data-style-cache"),
            null != b && "" !== b && isNaN(b) ? JSON.parse(b) : null
        },
        b.exports = {
            adjustWH: c,
            generateThumbnails: f,
            appCoverPage: d
        }
    },
    {
        "./css.coffee": 2
    }]
},
{},
[6]);