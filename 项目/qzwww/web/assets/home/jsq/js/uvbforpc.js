"use strict";

function _typeof2(e) {
    return (_typeof2 = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (e) {
        return typeof e
    } : function (e) {
        return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e
    })(e)
}

!function (e, u) {
    var c = u.head || u.getElementsByTagName("head")[0] || u.documentElement, l = c.getElementsByTagName("base")[0];
    e.load = function (e, t, n) {
        if ("IMG" !== (n = n || "IMG") || (o = ["Bytespider", "googlebot-image", "mediapartners-google", "yahooslurp", "Yahoo!SlupChina", "Yahoo!-AdCrawler", "YodaoBot", "Sosospider", "Sogou spider", "Sogou web spider", "Sogou inst spider", "Sogou wap spider", "soso", "baiduspider", "yahoo-blogs", "yahoo-verticalcrawler", "yahoofeedseeker", "yahooseeker-testing", "yahooseeker", "yahoo-mmcrawler", "yahoo!_mindset", "Yahoo! Slurp", "Yahoo! SlurpChina", "googlebot", "google-sitemaps", "msnbot", "msnbot-media", "bingbot", "BingPreview", "360Spider", "YisouSpider", "baidu Transcoder", "baidu\\+Transcoder"].join("|"), !new RegExp(".*(" + o + ").*", "gi").test(window.navigator.userAgent))) {
            var o, a, i, r = u.createElement(n);
            i = t, "onload" in (a = r) ? (a.onload = s, a.onerror = function () {
                s(!0)
            }) : a.onreadystatechange = function () {
                /loaded|complete/.test(a.readyState) && s()
            }, r.src = e, "IMG" === n && (r.width = 0, r.height = 0), l ? c.insertBefore(r, l) : c.appendChild(r)
        }

        function s(e) {
            a.onload = a.onerror = a.onreadystatechange = null, c.removeChild(a), a = null, i && i(e)
        }
    }
}(window, document), window.FCLUE = {}, window.SFUV = {
    Version: "1.33",
    G_reqURL: "https://countpvn.3g.fang.com/countpv",
    G_GlobalCname: "global_cookie",
    G_UniqueCname: "unique_cookie",
    G_GUID: "",
    G_UNID: "U_",
    G_time: 31536e6,
    G_domain: /youtx\.com/.test(location.host) ? "youtx.com" : "fang.com",
    G_path: "/",
    G_out: "",
    G_param: [],
    G_UVParam: "",
    G_STParam: "",
    getUqCode: function () {
        function e(e, t) {
            try {
                var n = e.toString(36);
                return n.length >= t ? n.substring(n.length - t, n.length) : n.substring(0, n.length)
            } catch (e) {
                return "0"
            }
        }

        for (var t = [], n = 0; n < 20; n++) {
            for (var o = Math.round(2147483647 * Math.random()).toString(36); o.length < 6;) o = "0" + o;
            t.push(o)
        }
        for (var a = [], i = {}; a.length < 4;) {
            var r = Math.round(2147483647 * Math.random()) % 20;
            void 0 === i[r] && (a.push(t[r]), i[r] = !0)
        }
        var s = "";
        s += e(location.href.length, 1), s += e(window.history.length, 1), s += e(document.cookie.length, 1), a.push(s);
        var u = (new Date).getTime().toString(36);
        return 0 <= u.indexOf("-") && (u = u.substring(1, u.length)), 8 < u.length && (u = u.substring(u.length - 8, u.length)), a.push(u), a.join("")
    },
    getDomain: function () {
        var e = location.hostname, t = e.replace(/\.(com|net|org|cn$)\.?.*/, "");
        return -1 === t.lastIndexOf(".") ? "." + e : (t = t.substring(t.lastIndexOf(".")), e.substring(e.lastIndexOf(t)))
    },
    getReferrer: function () {
        var t = "";
        try {
            t = document.top.document.referrer
        } catch (e) {
            if (document.parent) try {
                t = document.parent.document.referrer
            } catch (e) {
                t = ""
            }
        }
        return "" === t && (t = document.referrer), t
    },
    purify: function (e) {
        var t = new RegExp("#.*");
        return e = (e = (e = (e = e.replace(t, "")).replace(/\*/gi, "")).replace(/~/gi, "_")).replace(/\^/gi, "")
    },
    getcookie: function (e) {
        var t = new RegExp("(^| )" + e + "=([^;]*)(;|$)"), n = "", o = document.cookie.match(t);
        if (o) try {
            n = decodeURIComponent(o[2])
        } catch (e) {
            n = o[2]
        }
        return n
    },
    setcookie: function (e, t, n, o, a) {
        var i = "";
        n && (i = "; expires=" + (i = new Date((new Date).getTime() + n)).toGMTString()), document.cookie = e + "=" + escape(t) + i + (o ? ";path=" + o : "/") + (a ? ";domain=" + a : "")
    },
    hascookie: function () {
        var e = this.getcookie((new Date).valueOf() + "_cname");
        return void 0 === navigator.cookieEnabled ? (this.setcookie(e, "1"), "1" === this.getcookie(e) ? 1 : 0) : window.navigator.cookieEnabled ? 1 : 0
    },
    getNewUnid_mouse: function (e) {
        return e.split("*")
    },
    setNewUnid_mouse: function (e, t) {
        return e.split("*")[0] + "*" + t
    },
    init: function (e) {
        var t = {}, n = this.hascookie(), o = "", a = this.getcookie(this.G_GlobalCname),
            i = this.getcookie(this.G_UniqueCname);
        1 === n ? (a ? o = "0" : (this.G_GUID = this.getUqCode(), this.setcookie(this.G_GlobalCname, this.G_GUID, this.G_time, this.G_path, this.getDomain()), a = this.G_GUID, o = "1", i = this.G_UNID + a, this.setcookie(this.G_UniqueCname, i, 0, this.G_path, this.getDomain())), i || (i = this.G_UNID + this.getUqCode(), this.setcookie(this.G_UniqueCname, i, 0, this.G_path, this.getDomain()))) : a = "";

        function r() {
            if (!u) {
                u = !0;
                var e = 0;
                try {
                    e = parseInt(s.getNewUnid_mouse(i)[1])
                } catch (e) {
                }
                isNaN(e) && (e = 0), e += 1, s.setcookie(s.G_UniqueCname, s.setNewUnid_mouse(i, e), 0, s.G_path, s.getDomain())
            }
        }

        var s = this, u = !1, c = setInterval(function () {
            var e;
            (e = document.body) && (e.addEventListener ? e.addEventListener("mousemove", r, !1) : e.attachEvent("onmousemove", r), setTimeout(function () {
                clearInterval(s.intervalTimer)
            }, 0))
        }, 500);
        s.intervalTimer = c, "youtx.com" === s.G_domain ? s.G_reqURL = "//countpv.youtx.com/countpv" : "N" === e.isNorth && (s.G_reqURL = "https://countpvs.3g.fang.com/countpv"), t.v = s.Version, e.bid && (t.b = e.bid), void 0 === e.frameType ? window !== top ? t.f = 1 : t.f = 0 : t.f = e.frameType;
        var l, d = [];
        "string" == typeof e.location && e.location ? t.l = s.purify(e.location) : t.l = s.purify(window.location.href), "string" == typeof e.channel && e.channel && t.l && (l = -1 === t.l.indexOf("?") ? "?" : "&", t.l += l + "channel=" + e.channel), e.iscredible && t.l && (l = -1 === t.l.indexOf("?") ? "?" : "&", t.l += l + "iscredible=" + e.iscredible), e.newsfrom && t.l && (l = -1 === t.l.indexOf("?") ? "?" : "&", t.l += l + "newsfrom=" + e.newsfrom), e.urlowner && t.l && (l = -1 === t.l.indexOf("?") ? "?" : "&", t.l += l + "urlowner=" + e.urlowner), "string" == typeof e.hash && (t.l += encodeURIComponent("#" + e.hash)), "string" == typeof e.referrer && e.referrer ? t.r = s.purify(e.referrer) : t.r = s.purify(s.getReferrer()), window.FCLUE.surl = s.getReferrer(), t.g = a, t.u = i.split("*")[0], t.c = n, t.a = o, t.s = "", t.m = 1 < s.getNewUnid_mouse(i).length ? this.getNewUnid_mouse(i)[1] : 0, "function" == typeof encodeURI ? t.t = encodeURI(s.purify(document.title)) : t.t = "";
        var f = [];
        f.oa_token = s.getcookie("oa_token"), f.username = s.getcookie("new_managername"), f.sfut = s.getcookie("sfut"), f.oa_token && d.push("100~" + f.oa_token), f.username && d.push("2~~" + escape(f.username)), f.sfut && d.push("1~" + f.sfut), 0 === d.length && d.push("0"), t.i = d.join("*");
        var h = [], p = "";
        for (var g in t) if (t.hasOwnProperty(g) && "function" != typeof t[g]) {
            var b = (p = t[g].toString()).length % 8 - 1, _ = p.substring(b, 1 + b);
            t.s += _ || "n", h.push(g + "=" + p)
        }
        var m = (new Date).getTime();
        h.push(m), e.urlowner = e.urlowner || "", t.o = e.urlowner, h.push("o=" + e.urlowner), e.realip = e.realip || "", t.realip = e.realip, h.push("realip=" + e.realip), e.pageid = e.pageid || "", t.pageid = e.pageid, h.push("pageid=" + e.pageid);
        var v = [];
        e.page && (e.page = e.page.replace("^", "|"));
        for (var w = ["page", "city", "business"], y = 0; y < w.length; y++) {
            var k = w[y];
            v.push(k + "=" + (e[k] || ""))
        }
        v.push("client=pc"), s.G_STParam = v.join("^"), s.G_param = t, s.G_UVParam = h.join("^"), window.load(s.G_reqURL + "?" + s.G_UVParam)
    }
}, function () {
    var _ub = {}, Wa;
    _ub.$version = "201608261800", _ub.$domain = "jsub.fang.com", _ub.$actions = [2, 0, 2, 2, 4, 2, 5, 6, 5, 1, 4, 2, 3, 4, 4, 3, 4, 5, 5, 4, 5, 4, 3, 0, 4, 1, 0, 5, 0, 0, 0, 4, 0, 5, 5, 5, 5, 5, 5, 5, 6, 5, 4, 4, 6, 6, 6, 6, 6, 6, 4, 5, 4, 5, 4, 2, 2, 3, 4, 6, 6, 6, 5, 5, 4, 5, 5, 4, 4, 4, 4, 5, 5, 5, 3, 6, 4, 3, 5, 0, 0], _ub.$cookieEnabled = "boolean" == typeof navigator.cookieEnabled && navigator.cookieEnabled, _ub.$lsEnabled = (Wa = window.localStorage, Wa && "function" == typeof Wa.setItem), _ub.$flashEnabled = function () {
        var e = !1, t = 0;
        if (navigator.plugins && 0 < navigator.plugins.length) (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]) && (e = !0, t = parseFloat(navigator.plugins["Shockwave Flash" + (navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "")].description.split(" ")[2])); else if (window.ActiveXObject) try {
            var n = new window.ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
            e = !0, t = (t = n.GetVariable("$version")).split(",")[0].split(" ")[1]
        } catch (e) {
        }
        return e && 9 <= t
    }(), _ub.$flash = null, _ub.$frame = null, _ub.$img = null, _ub.$processing = 0, _ub.$ticker = {
        DETECTLS: 0,
        APPENDLS: 0,
        DETECTFLASH: 0,
        APPENDFLASH: 0,
        DETECTIMG: 0,
        APPENDIMG: 0,
        GETGUID: 0,
        REQUEST: 0,
        COLLECT: 0
    }, _ub.$guid = 0, _ub.$sessionid = 0, _ub.$user = {
        uid: "0", cid: "0", phone: "0", staff: "0", realtor: "0", getGuid: function () {
            _ub.$ticker.GETGUID++;
            var e = window.SFUV, t = e.getcookie(e.G_GlobalCname);
            t ? _ub.$guid = t : _ub.$ticker.GETGUID <= 1e3 && window.setTimeout(function () {
                _ub.$user.getGuid()
            }, 50)
        }, parseCookie: function () {
            var e = document.cookie, t = window.SFUV;
            if ("" !== e) {
                e = e.split("; ");
                for (var n = 0; n < e.length; n++) {
                    var o, a, i = e[n].indexOf("="), r = [];
                    if (i) switch (r.push(e[n].substring(0, i), e[n].substring(i + 1)), o = r[0], a = r[1], o) {
                        case"sfut":
                            _ub.$user.uid = a;
                            break;
                        case"userinfo":
                            /userid%253D(\d+)%2526/i.test(a) && (_ub.$user.uid = /userid%253D(\d+)%2526/i.exec(a)[1]), /username%253D(.*?)%2526/i.test(a) && (_ub.$user.uname = decodeURIComponent(/username%253D(.*?)%2526/i.exec(a)[1])), /isphonevalid%253D(\d+)%2526/i.test(a) && (_ub.$user.isphonevalid = /isphonevalid%253D(\d+)%2526/i.exec(a)[1]), /mail%253D(\S+%2540\S+\.\w+)%2526/i.test(a) && (_ub.$user.mail = decodeURIComponent(/mail%253D(\S+%2540\S+\.\w+)%2526/i.exec(a)[1])), /ismailvalid%253D(\d+)%2526/i.test(a) && (_ub.$user.ismailvalid = /ismailvalid%253D(\d+)%2526/i.exec(a)[1]), /cid%253D(\d+)%2526/i.test(a) && (_ub.$user.cid = /cid%253D(\d+)%2526/i.exec(a)[1]);
                            break;
                        case t.G_GlobalCname:
                            _ub.$guid = a;
                            break;
                        case t.G_UniqueCname:
                            _ub.$sessionid = a;
                            break;
                        case"isso_login":
                            0 < a.indexOf("%") ? _ub.$user.staff = a.substr(0, a.indexOf("%")) : 0 < a.indexOf("@") && (_ub.$user.staff = a.substr(0, a.indexOf("@")));
                            break;
                        case"agent_validation":
                            /^a=\d/i.test(a) && !/^a=0/i.test(a) && (_ub.$user.realtor = /^a=(\d)/i.exec(a)[1]);
                            break;
                        case"new_soufuncard":
                            3 < (a = a.split("%2C")).length && (a = a[3], /(\d+)$/.test(a) && (_ub.$user.cid = /(\d+)$/.exec(a)[1]))
                    }
                }
            }
        }, getP2: function () {
            var e = "0";
            return "0" != this.staff ? e = "4" : "0" != this.realtor ? e = "3" : "0" != this.cid ? e = "2" : 0 < this.uid && (e = "1"), e
        }
    }, _ub.city = 0, _ub.location = 0, _ub.biz = "", _ub.values = {}, _ub.client = function () {
        var e = {ie: 0, gecko: 0, webkit: 0, khtml: 0, opera: 0, ver: null},
            t = {ie: 0, firefox: 0, safari: 0, konq: 0, opera: 0, chrome: 0, ver: null}, n = {
                win: !1,
                mac: !1,
                xll: !1,
                iphone: !1,
                ipod: !1,
                ipad: !1,
                ios: !1,
                android: !1,
                nokiaN: !1,
                winMobile: !1,
                wii: !1,
                ps: !1
            }, o = window.navigator.userAgent, a = window.navigator.platform;
        if (n.win = 0 === a.indexOf("Win"), n.mac = 0 === a.indexOf("Mac"), n.xll = 0 === a.indexOf("Linux") || 0 === a.indexOf("Xll"), n.iphone = -1 < o.indexOf("iPhone"), n.ipod = -1 < o.indexOf("iPod"), n.ipad = -1 < o.indexOf("iPad"), n.mac && -1 < o.indexOf("Mobile") && (/CPU (?:iPhone )?OS (\d+_\d+)/.test(o) ? n.ios = parseFloat(RegExp.$1.replace("_", ".")) : n.ios = 2), /Android (\d+\.\d+)/.test(o) && (n.android = parseFloat(RegExp.$1)), n.nokiaN = -1 < o.indexOf("NokiaN"), "CE" === n.win ? n.winMobile = n.win : "Ph" === n.win && /Windows Phone OS (\d+.\d+)/.test(o) && (n.win = "Phone", n.winMobile = parseFloat(RegExp.$1)), n.wii = -1 < o.indexOf("Wii"), n.ps = /playstation/i.test(o), window.opera) e.ver = t.ver = window.opera.version(), e.opera = t.opera = parseFloat(e.ver); else if (/AppleWebKit\/(\S+)/i.test(o)) if (e.ver = t.ver = RegExp.$1, e.webkit = parseFloat(e.ver), /Chrome\/(\S+)/i.test(o)) t.chrome = parseFloat(e.ver); else if (/Version\/(\S+)/i.test(o)) t.safari = parseFloat(e.ver); else {
            var i = 1;
            i = e.webkit < 100 ? 1 : e.webkit < 312 ? 1.2 : e.webkit < 412 ? 1.3 : 2, t.safari = t.ver = i
        } else /KHTML\/(\S+)/i.test(o) || /Konqueror\/([^;]+)/i.test(o) ? (e.ver = t.ver = RegExp.$1, e.khtml = t.konq = parseFloat(e.ver)) : /rv:([^\)]+)\) Gecko\/\d{8}/i.test(o) ? (e.ver = RegExp.$1, e.gecko = parseFloat(e.ver), /Firefox\/(\S+)/i.test(o) && (t.ver = RegExp.$1, t.firefox = parseFloat(t.ver))) : (/MSIE ([^;]+)/i.test(o) && (e.ver = t.ver = RegExp.$1, e.ie = t.ie = parseFloat(e.ver)), /Trident\/([^;]+)/i.test(o) && (e.ver = t.ver = parseFloat(RegExp.$1) + 4, e.ie = t.ie = e.ver));
        if (n.win && /Win(?:dows )?([^do]{2})\s?(\d+\.\d+)?/.test(o)) if ("NT" === RegExp.$1) switch (RegExp.$2) {
            case"5.0":
                n.win = "2000";
                break;
            case"5.1":
                n.win = "XP";
                break;
            case"6.0":
                n.win = "Vista";
                break;
            case"6.1":
                n.win = "7";
                break;
            case"6.2":
                n.win = "8";
                break;
            case"6.3":
                n.win = "9";
                break;
            case"10.0":
                n.win = "10";
                break;
            default:
                n.win = "NT"
        } else "9x" === RegExp.$1 ? n.win = "ME" : n.win = RegExp.$1;
        return {engine: e, browser: t, system: n}
    }(), _ub._tick = function (c, f, t) {
        _ub.$ticker[t]++, _ub.$ticker[t] <= 600 && (eval(c) ? f() : window.setTimeout(function () {
            _ub._tick(c, f, t)
        }, 100))
    }, _ub._encrypt = function (e, t) {
        "function" == typeof JSEncrypt && t ? t() : window.load("https://" + _ub.$domain + "/" + e, function () {
            t && t()
        }, "SCRIPT")
    }, _ub._rsa = function (e) {
        var t = new window.JSEncrypt;
        for (var n in t.setPublicKey("MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDnJUXorWKGZEpLjgP9Aado78y8LwNiAqJNXkxLC0I5/rtnLmz8DuMgjxRVL+5iBeZ5a/Qm0zOOWd5/IJNLwZ6iAqX3NTxMuioAzaxXQWuhrgVJ+cxhWKuJGe1bsaPIUS+Py79a20FolQN+xT8Lf9aCTk9HdhjOd27LbX5DqwmO/wIDAQAB"), e) 4 <= n.indexOf("phone") && /^1[3|4|5|7|8]\d{9}$/.test(e[n]) && (e[n] = encodeURIComponent(t.encrypt(e[n].toString())))
    }, _ub._handling = function (e) {
        if ("object" === _typeof2(e)) {
            for (var t in e["vwg.l"] = encodeURIComponent(window.location.href), e["vwg.r"] = encodeURIComponent(window.SFUV.getReferrer()), e["vwg.userAgent"] = navigator.userAgent, _ub.client.browser) "ver" !== t && _ub.client.browser[t] && (e["vwg.browser"] = t + "^" + _ub.client.browser[t]);
            for (var n in _ub.client.system) _ub.client.system[n] && (e["vwg.system"] = "win" === n ? n + _ub.client.system[n] : n);
            var o = window.FCLUE.collect("", "", ""), a = {};
            ["scc", "sse", "spageid", "cpageid"].forEach(function (e) {
                a[e] = o[e]
            }), e["vwg.ext"] = JSON.stringify(a);
            var i = [], r = [];
            for (var s in e) e.hasOwnProperty(s) && (i.push(s + "=" + e[s]), -1 === s.indexOf(".") && r.push(s));
            return e = i.join("&"), r.length && (e += "&vwg.errorpage=" + encodeURIComponent(window.location.href) + "^" + r.join("^")), e
        }
    }, _ub._upload = function (e, t, n, o) {
        var a = /[^*]+/.exec(_ub.$sessionid);
        a = a && a[0] || "";
        var i = "youtx.com" === window.SFUV.G_domain ? "//countub.youtx.com" : "//countub" + (0 === parseInt(_ub.location, 10) ? "n" : "s") + ".3g.fang.com",
            r = window.location.protocol + i + "/w?g=" + e + "&c=" + encodeURIComponent(t) + "&b=" + n + "&s=" + a + "&" + _ub._handling(o);
        window.load(r + "&_=" + Math.round(1e10 * Math.random()), function () {
            _ub.callback && _ub.callback()
        })
    }, _ub._record = function (e, t, n, o) {
        var a = o, i = !1;
        for (var r in a) 4 <= r.indexOf("phone") && /^1[3|4|5|7|8]\d{9}$/.test(a[r]) && (i = !0);
        i ? _ub._encrypt("_jsencrypt.js", function () {
            _ub._rsa(e, t, n, o), _ub._upload(e, t, n, o)
        }) : _ub._upload(e, t, n, o)
    }, _ub._requestCallback = function (e) {
        for (var t = Object.keys(e), n = 0; n < t.length; n++) for (var o = t[n], a = 0; a < e[o].length; a++) e[o][a].w = _ub._weigh(e[o][a]);
        _ub.values = e, _ub.load(0), _ub.onload && _ub.onload()
    }, _ub._onmessage = function (e) {
        function t(e) {
            for (var t, n, o = [], a = e[1].split(","), i = 0; i < a.length; i++) "" !== (n = a[i].split("_"))[0] && 7 === n.length && ((t = {}).v = n[0], t.b = parseInt(n[1]), isNaN(t.b) && (t.b = 0), t.d = n[2], t.t = parseInt(n[3]), t.c = n[4], t.p = parseInt(n[5]), t.m = n[6], o.push(t));
            return o
        }

        if (-1 < e.origin.indexOf(_ub.$domain)) {
            var n = e.data;
            if (!/^collect/i.test(n)) if (/^request:/i.test(n)) {
                var o = {};
                if ("" !== (n = n.replace(/^request:/i, ""))) for (var a, i = n.split(";"), r = 0; r < i.length; r++) 2 === (a = i[r].split(":")).length && (o[a[0]] = t(a));
                _ub._requestCallback(o)
            } else /^view:/i.test(n) ? (n = n.replace(/^view:/i, ""), _ub._viewCallback && _ub._viewCallback(n)) : /^clear/i.test(n) && _ub._clearCallback && _ub._clearCallback();
            _ub._removeFrame()
        }
    }, _ub._initialize = function () {
        _ub.$cookieEnabled && (_ub.$user.parseCookie(), 0 === _ub.$guid && _ub.$user.getGuid(), window.addEventListener ? window.addEventListener("message", _ub._onmessage, !1) : window.attachEvent && window.attachEvent("onmessage", _ub._onmessage), !_ub.$lsEnabled && _ub.$flashEnabled && (_ub._tick("document.body != null && document.body.firstChild != null", function () {
            var e = document.createElement("DIV");
            e.id = "USERBEHAVIOR_DIV", e.style.position = "absolute", document.body.insertBefore(e, document.body.firstChild);
            var t = "http://flv" + _ub.$domain.substring(_ub.$domain.indexOf(".")) + "/_ub.swf?v=" + (new Date).valueOf().toString(),
                n = '<object id="USERBEHAVIOR_FLASH"';
            "Microsoft Internet Explorer" === navigator.appName ? n += 'classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" ' : (n += 'type="application/x-shockwave-flash" ', n += 'data="' + t + '" '), n += ' width="0" height="0">', n += '<param name="movie" value="' + t + '"></param>', n += '<param name="allowScriptAccess" value="always"></param>', n += "</object>", e.innerHTML = n
        }, "APPENDFLASH"), _ub._tick('document.getElementById("USERBEHAVIOR_FLASH") != null', function () {
            _ub.$flash = document.getElementById("USERBEHAVIOR_FLASH")
        }, "DETECTFLASH")))
    }, _ub._filter = function (e, t) {
        if (!t) return e;
        var n, o, a = t.split("&"), i = [], r = 0;
        for (r = 0; r < a.length; r++) (o = /[=!><\^\$#~]{2}/.exec(t)[0]) && (n = a[r].split(o), i.push({
            a: n[0],
            o: o,
            b: n[1]
        }));
        var s = [];
        for (r = 0; r < e.length; r++) {
            for (var u = !0, c = 0; c < i.length; c++) if (!_ub._matches(e[r], i[c])) {
                u = !1;
                break
            }
            u && s.push(e[r])
        }
        return s
    }, _ub._matches = function (e, t) {
        var n, o = !1;
        if (e[t.a]) switch (/[bdtwpm]/i.test(t.a) && (t.b = parseInt(t.b)), t.o) {
            case"==":
                o = e[t.a] === t.b;
                break;
            case"!=":
                o = e[t.a] !== t.b;
                break;
            case"=>":
                "v" === t.a && (t.b = parseInt(t.b, 10)), o = void 0 !== e[t.a] && e[t.a] > t.b;
                break;
            case"=<":
                "v" === t.a && (t.b = parseInt(t.b, 10)), o = void 0 !== e[t.a] && e[t.a] < t.b;
                break;
            case"<=":
                "v" === t.a && (t.b = parseInt(t.b, 10)), o = void 0 !== e[t.a] && e[t.a] <= t.b;
                break;
            case">=":
                "v" === t.a && (t.b = parseInt(t.b, 10)), o = void 0 !== e[t.a] && e[t.a] >= t.b;
                break;
            case"^=":
                (n = new RegExp("^" + t.b)).ignoreCase = !0, o = n.test(e[t.a]);
                break;
            case"$=":
                (n = new RegExp(t.b + "$")).ignoreCase = !0, o = n.test(e[t.a]);
                break;
            case"#=":
                (n = new RegExp(t.b)).ignoreCase = !0, o = n.test(e[t.a]);
                break;
            case"~=":
                if ("d" === t.a) {
                    var a = new Date, i = (a = new Date(a.valueOf() - 24 * t.b * 60 * 60 * 1e3)).getFullYear();
                    i = (i += "_" + (a.getMonth() + 1)).replace("_", 7 === i.length ? "" : "0"), i = (i += "_" + a.getDate()).replace("_", 9 === i.length ? "" : "0"), i = parseInt(i, 10), o = e[t.a] >= i
                }
        }
        return o
    }, _ub._weigh = function (e) {
        if (e.b && e.d && e.t && e.p && e.m) {
            var t, n, o, a, i, r = [5, 25, 40, 55, 70, 85, 100];
            return t = e.b > _ub.$actions.length ? r[0] : r[_ub.$actions[e.b]], n = Math.floor(((new Date).valueOf() - new Date(2012, 1, 1).valueOf()) / 1e3 / 3600 / 24), i = 24 * (n -= e.d) + ((new Date).getHours() - Math.floor(e.m / 3600)), n <= 5 ? n = 100 - 3 * n : 6 <= n && n <= 19 ? n = 85 - 2.5 * (n - 5) : 20 <= n && n <= 29 ? n = 50 - 2 * (n - 19) : 30 <= n && n <= 59 ? n = 30 - (n - 29) : 60 <= n && (n = 0), 100 < i && (i = 100), i = 100 - i, o = 5 < e.t ? 100 : [10, 30, 60, 90, 100][e.t - 1], e.p <= 2 ? a = 10 : 3 <= e.p && e.p <= 5 ? a = 30 : 6 <= e.p && e.p <= 9 ? a = 60 : 10 <= e.p && e.p <= 15 ? a = 80 : 16 <= e.p && (a = 100), .45 * t + .25 * n + .2 * o + .05 * a + .05 * i
        }
        return 0
    }, _ub._addFrame = function (t, n, o, a) {
        _ub.$lsEnabled && (_ub.$ticker[t] = 0, _ub._tick("document.body != null && document.body.firstChild != null && _ub.$guid != 0 && _ub.$processing === 0", function () {
            _ub.$processing = 1;
            var e = document.createElement("IFRAME");
            switch (e.id = "USERBEHAVIOR_FRAME", e.name = "USERBEHAVIOR_FRAME", e.style.display = "none", _ub.$frame = e, "function" == typeof a && (_ub.onload = a), t) {
                case"COLLECT":
                    setTimeout(function () {
                        // e.src = "https://" + _ub.$domain + "/lsc.htm?r=" + (new Date).valueOf() + "&g=" + _ub.$guid + "&c=" + encodeURIComponent(_ub.city) + "&b=" + n + "&p=" + o
                    }, 0);
                    break;
                case"REQUEST":
                    e.src = "https://" + _ub.$domain + "/lsr.htm?r=" + (new Date).valueOf() + "&c=" + encodeURIComponent(_ub.city) + "&p=" + o;
                    break;
                case"VIEW":
                    e.src = "https://" + _ub.$domain + "/lsv.htm?r=" + (new Date).valueOf();
                    break;
                case"CLEAR":
                    e.src = "https://" + _ub.$domain + "/lsn.htm?r=" + (new Date).valueOf()
            }
            document.body.insertBefore(e, document.body.firstChild)
        }, t))
    }, _ub._removeFrame = function () {
        _ub.$frame && (document.body.removeChild(_ub.$frame), _ub.$processing = 0, _ub.$frame = null)
    }, _ub.collect = function (e, t) {
        if (_ub.$cookieEnabled && t) {
            var n = isNaN(e) ? -1 : e;
            if ("string" == typeof t) {
                var o, a = t.split(";");
                t = {};
                for (var i = 0; i < a.length; i++) t[(o = a[i].split(":"))[0]] = o[1]
            }
            t["vwg.city"] = encodeURIComponent(_ub.city), t["vwg.passportid"] || (t["vwg.passportid"] = _ub.$user.uid || _ub._getCookie("sfut")), window.FCLUE.passportid = t["vwg.passportid"], null == t["vwg.usertype"] && (t["vwg.usertype"] = _ub.$user.getP2()), null == t["vwg.business"] && "" != _ub.biz && (t["vwg.business"] = _ub.biz.toUpperCase(), "W" == t["vwg.business"] && document.referrer && (t["vwg.refurl"] = encodeURIComponent(document.referrer))), null == t["vwg.agenttype"] && null != _ub.$user.realtor && "0" != _ub.$user.realtor && (t["vwg.agenttype"] = _ub.$user.realtor), "0" != _ub.$user.cid && null == t["vwg.sfcardid"] && (t["vwg.sfcardid"] = _ub.$user.cid), "0" != _ub.$user.staff && null == t["vwg.staffmail"] && (t["vwg.staffmail"] = _ub.$user.staff), t["vwg.clientstorage"] = (_ub.$flashEnabled ? 1 : 0) + (_ub.$lsEnabled ? 2 : 0);
            var r = "", s = "g_sourcepage";
            try {
                r = _ub._getCookie(s) || t["vwg.page"] || "", _ub._setCookie(s, t["vwg.page"] || "")
            } catch (e) {
                console.log("\u9875\u9762\u7f16\u53f7\u51fa\u9519")
            }
            t["vwg.sourcepage"] = r, window.FCLUE.rpage = r, window.FCLUE.page = t["vwg.page"];
            var u = [];
            if ("object" === _typeof2(t)) {
                for (var c in t) t.hasOwnProperty(c) && u.push(c + ":" + t[c]);
                u = u.join(";")
            }
            _ub._record(_ub.$guid, _ub.city, n, t), _ub.$flash ? (_ub.$ticker.COLLECT = 0, _ub._tick("_ub.$guid != 0 && _ub.$flash.collect != null", function () {
                try {
                    _ub.$flash.collect(_ub.$guid, encodeURIComponent(_ub.city), n, u)
                } catch (e) {
                    console.log(e)
                }
            }, "COLLECT")) : _ub._addFrame("COLLECT", n, u)
        }
    }, _ub.request = function (e, t) {
        _ub.$cookieEnabled && e && (_ub.$flash ? (_ub.$ticker.REQUEST = 0, _ub._tick("_ub.$guid != 0 && _ub.$flash.request != null", function () {
            try {
                _ub.$flash.request(encodeURIComponent(_ub.city), e)
            } catch (e) {
                console.log(e)
            }
        }, "REQUEST")) : _ub._addFrame("REQUEST", null, e, t))
    }, _ub.getValue = function (e, t, n) {
        if (void 0 === _ub.values[e]) return "";
        var o, a, i, r, s, u, c = parseInt(t, 10) || 0, l = _ub._filter(_ub.values[e], n), d = "";
        if (1 === l.length) return 3 === c ? l[0] : l[0].v;
        if (1 < l.length) {
            if (0 === c) {
                var f = 0;
                for (u = [], o = 0; o < l.length; o++) f += l[o].w, u.push({v: l[o].v, r: f});
                var h = Math.random() * f;
                for (o = 0; o < u.length; o++) if (u[o].r >= h) {
                    d = u[o].v;
                    break
                }
            } else if (1 === c) {
                var p = 0;
                for (u = [], o = 0; o < l.length; o++) l[o].w > p ? (u.push(l[o].v), p = l[o].w) : l[o].w === p && u.push(l[o].v);
                d = u[Math.floor(Math.random() * u.length)]
            } else if (2 === c) {
                for ((u = []).push(l[0]), i = l[0].d, o = 1; o < l.length; o++) i < (r = l[o].d) ? (u.push(l[o]), i = r) : r === i && u.push(l[o]);
                if (1 === u.length) d = u[0].v; else for (a = c = 0; a < u.length; a++) void 0 !== (s = u[a].m) && c < s && (c = s, d = u[a].v)
            } else if (3 === c) {
                var g = [];
                for (o = 0; o < l.length; o++) 1 === l[o].b && g.push(l[o]);
                if (g.length) {
                    for ((u = []).push(g[0]), i = g[0].d, o = 1; o < g.length; o++) i < (r = g[o].d) ? (u.push(g[o]), i = r) : r === i && u.push(g[o]);
                    if (1 === u.length) d = u[0]; else for (a = c = 0; a < u.length; a++) void 0 !== (s = u[a].m) && c < s && (c = s, d = u[a])
                }
            }
            return d
        }
        return ""
    }, _ub.load = function (e) {
        for (var t = Object.keys(_ub.values), n = 0; n < t.length; n++) {
            var o = t[n];
            _ub[o] = _ub.getValue(o, e)
        }
    }, _ub.onload = null, _ub._getCookie = function (e) {
        var t = new RegExp("(^| )" + e + "=([^;]*)(;|$)"), n = "", o = document.cookie.match(t);
        if (o) try {
            n = decodeURIComponent(o[2])
        } catch (e) {
            n = o[2]
        }
        return n
    }, _ub._setCookie = function (e, t) {
        document.cookie = e + "=" + encodeURIComponent(t) + "; path=/; domain=fang.com"
    }, window._ub = _ub
}(window), window.lifecycle = function () {
    var t = void 0;
    try {
        new EventTarget;
        t = typeof true === "undefined"
    } catch (e) {
        t = false
    }
    var e = typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol" ? function (e) {
            return _typeof2(e)
        } : function (e) {
            return e && typeof Symbol === "function" && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : _typeof2(e)
        }, a = function e(t, n) {
            if (!(t instanceof n)) {
                throw new TypeError("Cannot call a class as a function")
            }
        }, o = function () {
            function o(e, t) {
                for (var n = 0; n < t.length; n++) {
                    var o = t[n];
                    o.enumerable = o.enumerable || false;
                    o.configurable = true;
                    if ("value" in o) o.writable = true;
                    Object.defineProperty(e, o.key, o)
                }
            }

            return function (e, t, n) {
                if (t) o(e.prototype, t);
                if (n) o(e, n);
                return e
            }
        }(), i = function e(t, n) {
            if (typeof n !== "function" && n !== null) {
                throw new TypeError("Super expression must either be null or a function, not " + _typeof2(n))
            }
            t.prototype = Object.create(n && n.prototype, {
                constructor: {
                    value: t,
                    enumerable: false,
                    writable: true,
                    configurable: true
                }
            });
            if (n) Object.setPrototypeOf ? Object.setPrototypeOf(t, n) : t.__proto__ = n
        }, r = function e(t, n) {
            if (!t) {
                throw new ReferenceError("this hasn't been initialised - super() hasn't been called")
            }
            return n && (_typeof2(n) === "object" || typeof n === "function") ? n : t
        }, n = function () {
            function e() {
                a(this, e);
                this._registry = {}
            }

            o(e, [{
                key: "addEventListener", value: function e(t, n) {
                    this._getRegistry(t).push(n)
                }
            }, {
                key: "removeEventListener", value: function e(t, n) {
                    var o = this._getRegistry(t);
                    var a = o.indexOf(n);
                    if (a > -1) {
                        o.splice(a, 1)
                    }
                }
            }, {
                key: "dispatchEvent", value: function e(t) {
                    t.target = this;
                    Object.freeze(t);
                    this._getRegistry(t.type).forEach(function (e) {
                        return e(t)
                    });
                    return true
                }
            }, {
                key: "_getRegistry", value: function e(t) {
                    this._registry[t] = this._registry[t] || [];
                    return this._registry[t]
                }
            }]);
            return e
        }(), s = t ? EventTarget : n, u = function e(t) {
            a(this, e);
            this.type = t
        }, c, l = function (e) {
            i(o, e);

            function o(e, t) {
                a(this, o);
                var n = r(this, (o.__proto__ || Object.getPrototypeOf(o)).call(this, e));
                n.newState = t.newState;
                n.oldState = t.oldState;
                n.originalEvent = t.originalEvent;
                return n
            }

            return o
        }(t ? Event : u), d = "active", f = "passive", h = "hidden", p = "frozen", g = "terminated",
        b = (typeof safari === "undefined" ? "undefined" : e(safari)) === "object" && safari.pushNotification, _,
        m = ["focus", "blur", "visibilitychange", "freeze", "resume", "pageshow", "onpageshow" in self ? "pagehide" : "unload"],
        v = function e(t) {
            t.preventDefault();
            t.returnValue = "Are you sure?";
            return t.returnValue
        }, w, y = [[d, f, h, g], [d, f, h, p], [h, f, d], [p, h], [p, d], [p, f]].map(function e(t) {
            return t.reduce(function (e, t, n) {
                e[t] = n;
                return e
            }, {})
        }), k = function e(t, n) {
            for (var o = 0; o < y.length; ++o) {
                var a = y[o];
                var i = a[t];
                var r = a[n];
                if (i >= 0 && r >= 0 && r > i) {
                    return Object.keys(a).slice(i, r + 1)
                }
            }
            return []
        }, E = function e() {
            if (document.visibilityState === h) {
                return h
            }
            if (document.hasFocus()) {
                return d
            }
            return f
        }, C, $;
    return new (function (e) {
        i(n, e);

        function n() {
            a(this, n);
            var t = r(this, (n.__proto__ || Object.getPrototypeOf(n)).call(this));
            var e = E();
            t._state = e;
            t._unsavedChanges = [];
            t._handleEvents = t._handleEvents.bind(t);
            m.forEach(function (e) {
                return addEventListener(e, t._handleEvents, true)
            });
            if (b) {
                addEventListener("beforeunload", function (e) {
                    t._safariBeforeUnloadTimeout = setTimeout(function () {
                        if (!(e.defaultPrevented || e.returnValue.length > 0)) {
                            t._dispatchChangesIfNeeded(e, h)
                        }
                    }, 0)
                })
            }
            return t
        }

        o(n, [{
            key: "addUnsavedChanges", value: function e(t) {
                if (!this._unsavedChanges.indexOf(t) > -1) {
                    if (this._unsavedChanges.length === 0) {
                        addEventListener("beforeunload", v)
                    }
                    this._unsavedChanges.push(t)
                }
            }
        }, {
            key: "removeUnsavedChanges", value: function e(t) {
                var n = this._unsavedChanges.indexOf(t);
                if (n > -1) {
                    this._unsavedChanges.splice(n, 1);
                    if (this._unsavedChanges.length === 0) {
                        removeEventListener("beforeunload", v)
                    }
                }
            }
        }, {
            key: "_dispatchChangesIfNeeded", value: function e(t, n) {
                if (n !== this._state) {
                    var o = this._state;
                    var a = k(o, n);
                    for (var i = 0; i < a.length - 1; ++i) {
                        var r = a[i];
                        var s = a[i + 1];
                        this._state = s;
                        this.dispatchEvent(new l("statechange", {oldState: r, newState: s, originalEvent: t}))
                    }
                }
            }
        }, {
            key: "_handleEvents", value: function e(t) {
                if (b) {
                    clearTimeout(this._safariBeforeUnloadTimeout)
                }
                switch (t.type) {
                    case"pageshow":
                    case"resume":
                        this._dispatchChangesIfNeeded(t, E());
                        break;
                    case"focus":
                        this._dispatchChangesIfNeeded(t, d);
                        break;
                    case"blur":
                        if (this._state === d) {
                            this._dispatchChangesIfNeeded(t, E())
                        }
                        break;
                    case"pagehide":
                    case"unload":
                        this._dispatchChangesIfNeeded(t, t.persisted ? p : g);
                        break;
                    case"visibilitychange":
                        if (this._state !== p && this._state !== g) {
                            this._dispatchChangesIfNeeded(t, E())
                        }
                        break;
                    case"freeze":
                        this._dispatchChangesIfNeeded(t, p);
                        break
                }
            }
        }, {
            key: "state", get: function e() {
                return this._state
            }
        }, {
            key: "pageWasDiscarded", get: function e() {
                return document.wasDiscarded || false
            }
        }]);
        return n
    }(s))
}(), function (c) {
    function n() {
        var e = d.length;
        if (e) {
            var t = d[f] || d[e - 1];
            void 0 !== t && (f++, function (e) {
                if (e) {
                    var t = l.G_reqURL.split(l.G_domain + "/countpv").join(l.G_domain + "/stoptime");
                    if (!l.G_UVParam && l.G_param) {
                        var n = "", o = l.G_param, a = [];
                        for (var i in o) o.hasOwnProperty(i) && "function" != typeof o[i] && (n = o[i].toString(), a.push(i + "=" + n));
                        l.G_UVParam = a.join("^")
                    }
                    var r = d.length,
                        s = t + "?" + l.G_UVParam + "^st=" + e + "^" + l.G_STParam + "^len=" + r + "^sc=" + f;
                    if (c.load(s), !e) {
                        var u = "https://m.fang.com/tuangou/?c=tuangou&a=logStoptime&v=" + encodeURIComponent(s);
                        c.load(u)
                    }
                }
            }((new Date).getTime() - t))
        }
    }

    function t(e) {
        if (!o) {
            o = !0;
            var t = e && e.newState || "visible";
            "passive" === t && (t = "visible"), "hidden" === t ? n() : d.push((new Date).getTime()), o = !1
        }
    }

    var l = c.SFUV, d = [], f = 0, o = !1;
    t(""), c.lifecycle.addEventListener("statechange", function (e) {
        ("hidden" === e.newState && "passive" === e.oldState || "passive" === e.newState && "hidden" === e.oldState) && t(e)
    }), c.define && define("//static.soufunimg.com/count/uvbforpc.js", [], function () {
        return c._ub
    })
}(window), function (w) {
    var y = w.location.href, k = w._ub;
    w.FCLUE.collect = function (e, t, n) {
        var o = {};
        "string" == typeof e ? (o.creatingcity = e || "", o.phone = t || "", o.ext = n || "") : "object" === _typeof2(e) && (o = e);
        var a = k.city, i = o.creatingcity || a, r = w.FCLUE.rpage, s = w.FCLUE.surl || document.referrer, c = "",
            u = !1, l = "sf_source_cookie", d = function (e, t) {
                u = !0;
                var n = /[&?;/]s(f_source)?=([^&]*)/i.exec(e), o = n && n[2] || k._getCookie(l);
                if (o) {
                    var a = /(wap|pc)_(baidu|sogou|360|bing|google|sm)_seo/i;
                    return a.test(o) ? ("360" === (c = a.exec(o)[2]) && (c = "so360"), "") : o
                }
                if (n = new RegExp(["kuaichuan360", "qqbrowser_shenghuo2", "xmzhida", "hwmaz", "oppoapply", "meizuapply", "zteapply", "gioneeapply", "lenovoapply", "vivoapply", "oneplusapply", "nubiaapply", "msn\\.fang\\.com  ", "msn\\.villachina\\.com", "msn\\.landlist\\.cn", "msn\\.home\\.fang\\.com"].join("|"), "i").exec(e)) return n[0];
                if (n = new RegExp(["123\\.sogou\\.com", "hezuo\\.bj\\.fang\\.com/hezuosogou/index\\.html", "home\\.fang\\.com/zhuangxiuanli960\\.htm", "web\\.sogou\\.com", "(\\w+\\.)?hao123\\.com", "site\\.baidu\\.com", "map\\.baidu\\.com", "hao\\.360\\.cn", "www\\.3600\\.com", "home\\.fang\\.com/default360\\.htm", "home\\.fang\\.com/zhuangxiuanli960_360\\.htm", "hezuo\\.bj\\.fang\\.com/hezuo360/", "(\\w+\\.)?uc123\\.com", "hao\\.qq\\.com", "daohang\\.qq\\.com", "msn\\.fang\\.com", "msn\\.villachina\\.com", "msn\\.landlist\\.cn", "msn\\.home\\.fang\\.com", "msn\\.people\\.com\\.cn", "\\w+\\.duba\\.com", "(\\w+\\.)?2345a?\\.com", "(\\w+\\.)?114la\\.com", "(\\w+\\.)?265\\.com", "www\\.1616\\.net", "www\\.5566\\.net", "hezuo\\.bj\\.fang\\.com/hezuoduba/index\\.htm", "home\\.fang\\.com/hezuo/default_duba\\.htm", "esf\\.fang\\.com/newsecond/dubahezuo\\.htm", "zu\\.fang\\.com/external/default_kingsoft\\.htm", "home\\.fang\\.com/defaultduba\\.htm"].join("|"), "i").exec(t)) return n[0];
                var i = k._getCookie(l);
                return i ? (u = !1, i) : ""
            }(location.search || y.replace(location.hostname || location.host, ""), s);
        d && !0 === u && (k._setCookie(l, d), k._setCookie("sf_source", d));
        var f = !1, h = "engine_source_cookie", p = function (e) {
            var t, n, o = "";
            if (f = !0, c) o = c; else for (var a = {
                baidu: ["\\.baidu\\.", "\\.baiducontent\\.com", "\\.tradaquan\\.com"],
                google: ["\\.google\\.com"],
                sogou: ["\\.sogou\\.com"],
                soso: ["\\.soso\\.com"],
                bing: ["\\.bing\\.com"],
                youdao: ["\\.youdao\\."],
                yahoo: ["\\.yahoo\\."],
                so360: ["\\.360\\.cn", "\\.so\\.com", "\\.haosou\\.com"],
                easou: ["\\.easou\\.com"],
                yicha: ["\\.yicha\\.cn"],
                roboo: ["\\.roboo\\.com"],
                sm: ["\\.sm\\.cn"]
            }, i = Object.keys(a), r = 0; r < i.length; r++) {
                var s = a[i[r]];
                if (t = e, n = s.join("|"), new RegExp(".*(" + n + ").*", "gi").test(t)) {
                    o = i[r];
                    break
                }
            }
            if (o) return o;
            var u = k._getCookie(h);
            return u ? (f = !1, u) : o
        }(s);
        p && !0 === f && (k._setCookie(h, p), k._setCookie("sf_source", p));
        var g = w.FCLUE.page, b = w.SFUV, _ = b.getcookie(b.G_GlobalCname), m = b.getcookie(b.G_UniqueCname),
            v = /[^*]+/.exec(m);
        return {
            ccity: i || "",
            scity: a || "",
            scc: d || "",
            sse: p,
            spageid: s && r || "",
            cpageid: g || "",
            guid: _ || "",
            sessionid: m = v && v[0] || ""
        }
    }
}(window);
var _dctc = window._dctc || {}, _account = _dctc._account || [], _gaq = _dctc._gaq || [];
!function (e, i, r) {
    e.trackEvent = function (e) {
        if (e) try {
            if (0 < i.length) for (var t in i) if (i.hasOwnProperty(t)) {
                r.push(["t" + t + "._setAccount", i[t]]);
                var n = [];
                n.push("t" + t + "._trackEvent");
                for (var o = [e.c, e.a, e.l, e.v, e.n], a = 0; a < o.length; a++) o[a] && n.push(o[a]);
                r.push(n)
            }
        } catch (e) {
        }
    };
    var t, n,
        o = (t = location.hostname, -1 === (n = t.replace(/\.(com|net|org|cn$)\.?.*/, "")).lastIndexOf(".") ? "." + t : (n = n.substring(n.lastIndexOf(".")), t.substring(t.lastIndexOf(n))));
    if (0 < i.length) for (var a in i) if (i.hasOwnProperty(a)) {
        var s = "t" + a + "._addOrganic";
        r.push(["t" + a + "._setAccount", i[a]]), r.push(["t" + a + "._setDomainName", o]), r.push([s, "soso", "w"]), r.push([s, "soso", "key"]), r.push([s, "sogou", "query"]), r.push([s, "sogou", "keyword"]), r.push([s, "youdao", "q"]), r.push([s, "baidu", "word"]), r.push([s, "baidu", "q1"]), r.push([s, "baidu", "w"]), r.push([s, "baidu", "kw"]), r.push([s, "360", "q"]), r.push([s, "360", "kw"]), r.push([s, "so.com", "q"]), r.push([s, "easou", "q"]), r.push([s, "yicha", "key"]), r.push([s, "roboo", "q"]), r.push(["t" + a + "._trackPageview"])
    }
    if (e._trackTrans) for (var u in e._trackTrans) e._trackTrans.hasOwnProperty(u) && r.push(["t" + e._trackTrans[u] + "._trackTrans"])
}(_dctc, _account, _gaq), function (n) {
    function e(e) {
        var t = n._dctc || {};
        t.isNorth = t.isNorth || "Y", t.bid = t.bid || "1", e && (t.hash = e), n.SFUV.init(t)
    }

    e(), n.SFUV.update = e, n._ub._initialize(), setTimeout(function () {
        n.load("https://www.google-analytics.com/ga.js", function () {
        }, "SCRIPT")
    }, 2e3)
}(window);