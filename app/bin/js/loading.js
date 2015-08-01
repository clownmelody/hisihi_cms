!
function a(b, c, d) {
    function e(g, h) {
        if (!c[g]) {
            if (!b[g]) {
                var i = "function" == typeof require && require;
                if (!h && i) return i(g, !0);
                if (f) return f(g, !0);
                throw new Error("Cannot find module '" + g + "'")
            }
            var j = c[g] = {
                exports: {}
            };
            b[g][0].call(j.exports,
            function(a) {
                var c = b[g][1][a];
                return e(c ? c: a)
            },
            j, j.exports, a, b, c, d)
        }
        return c[g].exports
    }
    for (var f = "function" == typeof require && require,
    g = 0; g < d.length; g++) e(d[g]);
    return e
} ({
    1 : [function() {
        window.addEventListener("load",
        function() {
            var a, b, c, d, e, f;
            for (a = .275 * window.innerWidth / 150, $("div#circle")[0].style.webkitTransform = "scaleX(" + a + ") scaleY(" + a + ")", $("div#circle").css("top", .367 * window.innerHeight - 75 * (1 - a) + "px"), $("div#circle").css("left", .3625 * window.innerWidth - 75 * (1 - a) + "px"), $("div#circle").removeClass("invisible"), $("#circle-filled-right").addClass("percent50"), c = 500, setTimeout(function() {
                return $("#circle-filled-right").removeClass("percent50"),
                $("#circle-filled-right").addClass("finish-percent50"),
                $("#circle-unfilled-left").addClass("invisible"),
                $("#circle-filled-left").removeClass("invisible"),
                $("#circle-filled-left").addClass("percent80")
            },
            c / 2), setTimeout(function() {
                return $("#circle-filled-left").addClass("percent100")
            },
            4 * c / 5), d = function(a, b) {
                return setTimeout(function() {
                    return 10 > a && (a = "0" + a),
                    99 === a ? ($("#circle div.text.percent").addClass("hide"), $("#circle div.text.unit").addClass("hide"), $("#circle div.img").removeClass("hide")) : $("#circle div.text.percent").text(a)
                },
                a * b / 100)
            },
            f = [], b = e = 0; 100 > e; b = ++e) d(b, c),
            f.push(function(a, b) {
                return setTimeout(function() {
                    return $("#loading-container").css("opacity", 1 - (a + 1) / 100),
                    99 === a ? ($("#loading-container").addClass("hide"), startPage()) : void 0
                },
                10 * a + b)
            } (b, c));
            return f
        })
    },
    {}]
},
{},
[1]);