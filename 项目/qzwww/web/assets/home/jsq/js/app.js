webpackJsonp([1], {
    101: function (t, e, a) {
        function s(t) {
            a(398)
        }

        var i = a(10)(a(176), a(433), s, null, null);
        t.exports = i.exports
    }, 107: function (t, e, a) {
        "use strict";

        function s() {
        }

        Object.defineProperty(e, "__esModule", {value: !0}), s.prototype.calculate = function (t) {
            var e = {interest: "", toatlInterest: "", rate: t.rate}, a = this;
            a.data = t;
            var s = this.data.rate / 12, i = [], o = [], n = parseInt(a.data.month), r = parseFloat(a.data.totalMoney),
                l = 0, c = 0, u = 0, h = [];
            if (1 == a.data.type) {
                for (var v = r * s * Math.pow(1 + s, n) / (Math.pow(1 + s, n) - 1); l < a.data.month; l++) i.push(Math.round(r * s * (Math.pow(1 + s, n) - Math.pow(1 + s, l)) / (Math.pow(1 + s, n) - 1))), o.push(Math.round(r * s * Math.pow(1 + s, l) / (Math.pow(1 + s, n) - 1))), c += o[l];
                for (var l = 0; l < i.length; l++) u += Number(i[l]);
                u = Math.round(v * n - r), v = Math.round(v);
                for (var m = 1; m <= a.data.month; m++) h.push(Math.round(u - Math.round(v) * m))
            } else if (2 == a.data.type) {
                var o = Math.round(r / n), v = [], p = o * s;
                u = Math.round((r / n + r * s + r / n * (1 + s)) / 2 * n - r);
                for (var d = 0; l < a.data.month; l++) i.push(Math.round((r - c) * s)), v.push(parseInt(o + (r - c) * s)), c = o * (l + 1), d += Math.round(v[l]), h.push(Math.round(r + u - d));
                e.djMoney = Math.round(p)
            }
            return e.interest = i, e.toatlInterest = u, e.monYg = v, e.moMoney = o, e.leftMoney = h, e.totalMoney = r, e.firstMonYg = "number" == typeof v ? v : v[0], e
        }, e.Calculate = s
    }, 108: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var s = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (t) {
            return typeof t
        } : function (t) {
            return t && "function" == typeof Symbol && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t
        }, i = a(57), o = {};
        o.install = function (t, e) {
            if (!document.querySelectorAll(".alertBox").length) {
                var a = t.extend(i), o = new a, n = o.$mount().$el;
                document.body.appendChild(n), t.prototype.$toast = {
                    show: function (t) {
                        "string" == typeof t ? o.text = t : "object" === (void 0 === t ? "undefined" : s(t)) && Object.assign(o, t), o.show = !0, 0 !== t.time && setTimeout(function () {
                            o.show = !1
                        }, t.time || o.time)
                    }, hide: function () {
                        o.show = !1
                    }
                }
            }
        }, e.default = o
    }, 146: function (t, e, a) {
        function s(t) {
            a(397)
        }

        var i = a(10)(a(171), a(432), s, null, null);
        t.exports = i.exports
    }, 148: function (t, e, a) {
        "use strict";

        function s(t) {
            return t && t.__esModule ? t : {default: t}
        }

        var i = a(147), o = s(i), n = a(409), r = s(n), l = a(186), c = s(l), u = a(413), h = s(u), v = a(150),
            m = s(v), p = a(108), d = s(p);
        o.default.config.productionTip = !1;
        var f = function (t) {
            // var e = t.protocol + "//" + t.host;
            // return -1 !== t.host.indexOf("newhouse.fang.com") ? e += "/house/s/list" : -1 !== t.host.indexOf("myditu.test.fang.com") && (e += "/trunk/web/tools/"), e + "/?c=tools"
            return "https://newhouse.fang.com/house/s/list/?c=tools"
        }(window.location);
        o.default.prototype.G = {
            jk: f,
            city: '',
            urlXFSF: f + "&a=getXfsf",
            urlESFSF: f + "&a=getEsfSf",
            bannersma: !0
        }, -1 !== window.location.href.indexOf("?bannersma") && (o.default.prototype.G.bannersma = !1), o.default.prototype.$axios = m.default, m.default.defaults.withCredentials = !0, o.default.use(d.default), new o.default({
            el: "#app",
            router: c.default,
            template: "<App/>",
            components: {App: r.default}
        }), new o.default({el: "#loginBarNew", template: "<Login/>", components: {Login: h.default}})
    }, 168: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var s = a(414), i = function (t) {
            return t && t.__esModule ? t : {default: t}
        }(s);
        a(387), e.default = {
            name: "app", data: function () {
                return {}
            }, watch: {}, methods: {}, components: {navigate: i.default}
        }
    }, 169: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.default = {
            name: "ajFew", data: function () {
                return {
                    items: [{text: "8成", val: 8}, {text: "7.5成", val: 7.5}, {text: "7成", val: 7}, {
                        text: "6.5成",
                        val: 6.5
                    }, {text: "6成", val: 6}, {text: "5.5成", val: 5.5}, {text: "5成", val: 5}, {
                        text: "4.5成",
                        val: 4.5
                    }, {text: "4成", val: 4}, {text: "3.5成", val: 3.5}, {text: "3成", val: 3}, {
                        text: "2.5成",
                        val: 2.5
                    }, {text: "2成", val: 2}]
                }
            }, methods: {
                fill: function (t) {
                    this.$emit("few-msg", t)
                }
            }
        }
    }, 170: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.default = {
            name: "AjRate",
            props: {items: {type: Array, default: [], required: !0}},
            data: function () {
                return {}
            },
            methods: {
                fill: function (t) {
                    this.$emit("rate-msg", t)
                }
            }
        }
    }, 171: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.default = {
            name: "AjYear",
            props: ["showYear"],
            data: function () {
                for (var t = [], e = 30; e >= 1; e--) t.push({text: e + "年（" + 12 * e + "期）", val: e});
                return {items: t}
            },
            methods: {
                fill: function (t) {
                    this.$emit("year-msg", t)
                }
            }
        }
    }, 172: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.default = {
            name: "DropSelect",
            props: {
                selectItem: {type: Array, default: [], required: !0},
                klass: {type: String, default: "", required: !1},
                name: {type: String, default: "", required: !0},
                defaultItem: {
                    type: Object, default: function () {
                        return {name: "", value: ""}
                    }, required: !0
                }
            },
            data: function () {
                return {classObj: {option: !0, none: !0}}
            },
            mounted: function () {
                var t = this;
                this.$nextTick(function () {
                    window.addEventListener("click", t.close)
                })
            },
            beforeDestroy: function () {
                window.removeEventListener("click", this.close)
            },
            methods: {
                close: function (t) {
                    this.$el.contains(t.target) || (this.classObj.none = !0)
                }, toggleDrop: function () {
                    this.classObj.none = !this.classObj.none
                }, selectDrop: function (t) {
                    this.$emit("selectDrop", t, this.name), this.toggleDrop()
                }
            }
        }
    }, 173: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.default = {
            data: function () {
                return {}
            }, props: {show: {default: !1}, text: {default: "稍候"}, mask: {default: !1}, time: {default: 2e3}}
        }
    }, 174: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0});
        var s = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (t) {
            return typeof t
        } : function (t) {
            return t && "function" == typeof Symbol && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t
        };
        e.default = {
            name: "LoginBarNew", data: function () {
                return {
                    passport: "http://passport.fang.com/",
                    login: "",
                    loginTitle: "",
                    register: "",
                    registerTitle: "",
                    target: "_self"
                }
            }, mounted: function () {
                this.loginInfo()
            }, methods: {
                loginInfo: function () {
                    var t = this;
                    // this.$axios.get(t.G.jk + "&a=ajaxGetUser&r=" + Math.random()).then(function (e) {
                    //     e.data && "object" === s(e.data) && t.rewrite(e.data.username)
                    // }).catch(function (e) {
                    //     t.rewrite("")
                    // })
                }, rewrite: function (t) {
                    if (t) {
                        this.target = "_blank";
                        var e = encodeURIComponent(location.href);
                        this.login = "http://my.fang.com/", this.register = this.passport + "logout.aspx?backurl=" + e, this.loginTitle = t, this.registerTitle = "退出"
                    } else {
                        var e = encodeURIComponent(location.href);
                        this.login = this.passport + "login.aspx?backurl=" + e, this.loginTitle = "登录"
                    }
                }
            }
        }
    }, 175: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.default = {
            name: "Navigate", data: function () {
                return {
                    bannersma: this.G.bannersma,
                    items: [{id: "fangdai", title: "房贷计算器"}, {id: "shuifei", title: "税费计算器"}, {
                        id: "tqhd",
                        title: "提前还贷计算器"
                    }, {id: "gjj", title: "公积金贷款额度查询"}, {id: "gfnl", title: "购房能力评估"}, {
                        id: "zhuangxiu",
                        title: "装修贷款计算器"
                    }],
                    is3385City: ["bj", "sh", "gz", "sz", "tj", "wuhan", "xian", "zz", "cd", "dl", "cq"].includes(this.G.city),
                    guohuCenterUrl: "//guohu.fang.com/" + this.G.city + "/?kgh_source=pc_ghcenter_fang_ty_tool",
                    moduleName: function (t) {
                        var e = t.location.href, a = void 0, s = e.match(/\/(jsq|house)\/([a-z]+)\.htm/);
                        return a = s && s[0] ? s[0] : "/" + url + "/", {
                            "/jsq/fd/": "fangdai",
                            "/jsq/sf/": "shuifei",
                            "/jsq/tq/": "tqhd",
                            "/jsq/gjj/": "gjj",
                            "/jsq/pg/": "gfnl",
                            "/jsq/zxdk/": "zhuangxiu"
                        }[a]
                    }(window)
                }
            }, watch: {$route: "routerChange"}, methods: {
                navi: function (t) {
                    var e = this, a = {
                        fangdai: "/jsq/fd/",
                        shuifei: "/jsq/sf/",
                        tqhd: "/jsq/tq/",
                        gjj: "/jsq/gjj/",
                        gfnl: "/jsq/pg/",
                        zhuangxiu: "/jsq/zxdk/"
                    }, s = a[t] + (e.G.bannersma ? "" : "?bannersma");
                    this.$router.push(s)
                }, routerChange: function () {
                    this.moduleName = this.$route.name
                }
            }
        }
    }, 176: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.default = {
            name: "SelectComponent",
            props: ["title", "items", "showImportant"],
            data: function () {
                return {
                    fill: function (t) {
                        this.items.forEach(function (t, e, a) {
                            a[e].cla = ""
                        }), t.cla = "on", this.$emit("select-msg", t)
                    }
                }
            }
        }
    }, 177: function (t, e, a) {
        "use strict";

        function s(t) {
            return t && t.__esModule ? t : {default: t}
        }

        Object.defineProperty(e, "__esModule", {value: !0});
        var i = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (t) {
                return typeof t
            } : function (t) {
                return t && "function" == typeof Symbol && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t
            }, o = a(146), n = s(o), r = a(411), l = s(r), c = a(410), u = s(c), h = a(107), v = a(45), m = a(51),
            p = new h.Calculate;
        e.default = {
            name: "Fangdai", components: {AjFew: u.default, AjRate: l.default, AjYear: n.default}, data: function () {
                return {
                    items1: [{text: "商业贷款", val: 1}, {text: "公积金贷款", val: 2}, {text: "组合型贷款", val: 3}],
                    items2: [{text: "根据面积、单价计算"}, {text: "根据贷款总额计算"}],
                    items3: [{text: "等额本息", cla: "on", val: 1}, {text: "等额本金", cla: "", val: 2}],
                    showCalType: !1,
                    calcuTypeCon: "根据面积、单价计算",
                    fewCon: "bj" === this.G.city || "sh" === this.G.city ? "6.5成" : "7成",
                    few: "bj" === this.G.city || "sh" === this.G.city ? 6.5 : 7,
                    showDkType: !1,
                    dkType: "商业贷款",
                    showFew: !1,
                    buildArea: "",
                    unitPrice: "",
                    dktotal: "",
                    rate: 4.9,
                    basisPoint: 0,
                    showBasisPoint: !1,
                    baseRate: 4.9,
                    gjjBaseRate: 3.25,
                    showDkIpt: !1,
                    rateCon: "15年10月24日基准利率（基准利率）",
                    rateConItem: {},
                    year: 30,
                    yearCon: "30年（ 360期 ）",
                    showYear: !1,
                    showRate: !1,
                    totalLx: 0,
                    monMoney: 0,
                    totalMoney: 0,
                    totalMon: "",
                    calculateMsg: {rate: .049, month: 240, totalMoney: "", type: 1},
                    calculateComMsg: {
                        sd: {rate: .049, month: "", totalMoney: "", type: 1},
                        gjj: {rate: .025, month: "", totalMoney: "", type: 1}
                    },
                    gjjRateConItem: {},
                    sdRateConItem: {},
                    showCom: !1,
                    sdRate: 4.9,
                    gjjRate: 3.25,
                    showRategjj: !1,
                    showRatesd: !1,
                    isWidth5: "counter_list_width1",
                    gjjtotal: 0,
                    sdtotal: 100,
                    type: "sd",
                    isType1: "每月月供",
                    djMoney: 100,
                    firstMoney: "0",
                    dkMoney: "0",
                    disCount: {disCount: 1, sdDisCount: 1, gjjDisCount: 1},
                    showResult: !1,
                    isFade: !0,
                    rateItems: [],
                    rateType: [],
                    userActionOptions: {type: 0, pageId: "jsq_fd^fdjsq_pc", params: {}}
                }
            }, computed: {
                showDj: function () {
                    return "首月月供" === this.isType1
                }, firstMoneyBx: function () {
                    return parseFloat((this.buildArea * this.unitPrice * (10 - this.few) / 10).toFixed(2)) || 0
                }, dkTotalMoney: function () {
                    return parseFloat((this.buildArea * this.unitPrice * this.few / 10).toFixed(2)) || 0
                }, transBasisPoint: function () {
                    return (100 * (parseFloat(this.basisPoint) || 0) / 1e4).toFixed(3) || 0
                }, caRate: function () {
                    var t = parseFloat(this.transBasisPoint) || 0, e = (parseFloat(this.selectRate) + t).toFixed(3);
                    return e > 0 && e < 100 ? e : (this.basisPoint = "", this.$toast.show("基点数值不规范，请重新输入"), this.selectRate)
                }, selectRate: function () {
                    return "com" === this.type ? this.sdRate : this.rate
                }
            }, mounted: function () {
                var t = this;
                (0, v.yhxw)(this.userActionOptions);
                for (var e = ["showYear", "showRate", "showRatesd", "showRategjj", "showFew", "showDkType", "showCalType"], a = 0; a < e.length; a++) !function (e) {
                    window.addEventListener("click", function () {
                        if (!t[e]) return !1;
                        t[e] = !1
                    })
                }(e[a]);
                this.getLoanRate()
            }, methods: {
                clickDk: function (t) {
                    this.hide("showDkType"), this.showDkType = !this.showDkType, t.stopPropagation()
                }, selectDkType: function (t) {
                    this.dkType = t.text, 3 === t.val ? (this.showCom = !0, this.isWidth5 = "counter_list_width5", this.type = "com") : (2 === t.val ? this.type = "gjj" : this.type = "sd", this.showCom = !1, this.isWidth5 = "counter_list_width1"), this.showDkType = !1, this.clearAll()
                }, selectCalType: function (t) {
                    this.hide("showCalType"), this.showCalType = !this.showCalType, this.showResult = !1, t.stopPropagation()
                }, calcuType: function (t, e) {
                    this.showDkIpt = 1 === t, this.calcuTypeCon = e.text, this.showCalType = !1
                }, clickFew: function (t) {
                    this.hide("showFew"), this.showFew = !this.showFew, t.stopPropagation()
                }, clickYear: function (t) {
                    this.hide("showYear"), this.showYear = !this.showYear, t.stopPropagation()
                }, clickRate: function (t) {
                    this.hide("showRate"), this.showRate = !this.showRate, t.stopPropagation()
                }, gjjClickRate: function (t) {
                    this.hide("showRategjj"), this.showRategjj = !this.showRategjj, t.stopPropagation()
                }, sdClickRate: function (t) {
                    this.hide("showRatesd"), this.showRatesd = !this.showRatesd, t.stopPropagation()
                }, fewMsg: function (t) {
                    this.fewCon = t.text, this.few = t.val, this.showFew = !1
                }, rateMsg: function (t) {
                    this.rateConItem = t, this.calculateMsg.rate = this.setLoanRate(this.year, this.type, t), this.rateCon = t.calculatename, this.rate = Math.round(1e4 * this.calculateMsg.rate) / 100, "gjj" !== this.type && this.rateCon.indexOf("LPR") > -1 ? this.showBasisPoint = !0 : this.showBasisPoint = !1, this.showRate = !1
                }, setLoanRate: function (t, e, a) {
                    var s = 0;
                    return "sd" === e ? t <= 1 ? s = a.commercefirst : t <= 3 ? s = a.commercesecond : t <= 5 ? s = a.commerceone : t <= 30 && (s = a.commerceten) : t <= 5 ? s = a.fundone : t <= 30 && (s = a.fundten), Number(s)
                }, basisPointFocus: function () {
                    0 === this.basisPoint && (this.basisPoint = "")
                }, basisPointBlur: function () {
                    "" === this.basisPoint && (this.basisPoint = 0)
                }, limitVal: function (t, e) {
                    return e.match(t)[0]
                }, sdIpt: function (t) {
                    this.sdtotal = this.limitVal(/\d{0,6}(\.\d{0,2})?/g, t.target.value), this.calculateComMsg.sd.totalMoney = 1e4 * this.sdtotal || 0
                }, gjjIpt: function (t) {
                    this.gjjtotal = this.limitVal(/\d{0,6}(\.\d{0,2})?/g, t.target.value), this.calculateComMsg.gjj.totalMoney = 1e4 * this.gjjtotal || 0
                }, sdRateIpt: function (t) {
                    this.sdRate = this.limitVal(/\d{0,2}(\.\d{0,2})?/g, t.target.value)
                }, gjjRateIpt: function (t) {
                    this.gjjRate = this.limitVal(/\d{0,2}(\.\d{0,2})?/g, t.target.value)
                }, priceIpt: function (t) {
                    this.unitPrice = this.limitVal(/\d{0,6}(\.\d{0,2})?/g, t.target.value)
                }, areaIpt: function (t) {
                    this.buildArea = this.limitVal(/\d{0,4}(\.\d{0,2})?/g, t.target.value)
                }, rateIpt: function (t) {
                    this.rate = this.limitVal(/\d{0,2}(\.\d{0,2})?/g, t.target.value)
                }, basisPointIpt: function (t) {
                    this.basisPoint = this.limitVal(/[\-|\d]{0,4}(\.\d{0,3})?/g, t.target.value)
                }, dktotalIpt: function (t) {
                    this.dktotal = this.limitVal(/\d{0,10}(\.\d{0,2})?/g, t.target.value)
                }, rateComSd: function (t) {
                    this.sdRateConItem = t, this.calculateComMsg.sd.rate = this.setLoanRate(this.year, "sd", t), this.rateCon = t.calculatename, this.sdRate = Math.round(1e4 * this.calculateComMsg.sd.rate) / 100, "gjj" !== this.type && this.rateCon.indexOf("LPR") > -1 ? this.showBasisPoint = !0 : this.showBasisPoint = !1, this.showRatesd = !1
                }, rateComGjj: function (t) {
                    this.gjjRateConItem = t, this.calculateComMsg.gjj.rate = this.setLoanRate(this.year, "gjj", t), this.rateCon = t.calculatename, this.gjjRate = Math.round(1e4 * this.calculateComMsg.gjj.rate) / 100, this.showRategjj = !1
                }, yearMsg: function (t) {
                    this.yearCon = t.text, this.showYear = !1, this.year = t.val, "gjj" === this.type || "sd" === this.type ? (this.calculateMsg.rate = this.setLoanRate(this.year, this.type, this.rateConItem), this.rate = Math.round(1e4 * this.calculateMsg.rate) / 100) : "com" === this.type && (this.calculateComMsg.gjj.rate = this.setLoanRate(this.year, "gjj", this.gjjRateConItem), this.gjjRate = Math.round(1e4 * this.calculateComMsg.gjj.rate) / 100, this.calculateComMsg.sd.rate = this.setLoanRate(this.year, "sd", this.sdRateConItem), this.sdRate = Math.round(1e4 * this.calculateComMsg.sd.rate) / 100), this.calculateMsg.month = 12 * this.year
                }, parseNumber: function (t) {
                    return parseFloat(parseFloat(t).toFixed(3))
                }, hkType: function (t) {
                    this.items3.forEach(function (t, e, a) {
                        a[e].cla = ""
                    }), t.cla = "on", this.isType1 = 1 === t.val ? "每月月供" : "首月月供", "com" === this.type ? (this.calculateComMsg.sd.type = t.val, this.calculateComMsg.gjj.type = t.val) : this.calculateMsg.type = t.val, this.showResult = !1
                }, sum: function (t, e, a) {
                    return t && e ? parseFloat((parseFloat(t) + parseFloat(e)).toFixed(2)) : t && !e ? parseFloat(parseFloat(t).toFixed(2)) : !t && e ? parseFloat(parseFloat(e).toFixed(2)) : void 0
                }, calculate: function (t) {
                    var e = this, a = this, s = {};
                    if ("com" === this.type) {
                        var i = p.calculate(t.sd), o = p.calculate(t.gjj);
                        this.dkMoney = a.sum(i.totalMoney, o.totalMoney) || 0, this.monMoney = a.sum(i.firstMonYg, o.firstMonYg) || 0, this.totalLx = m.formatNumber(a.sum(i.toatlInterest, o.toatlInterest)) || 0, this.totalMoney = m.formatNumber(a.sum(a.sum(i.totalMoney, o.totalMoney), a.totalLx)) || 0, this.totalMon = 12 * this.year + "个" || 0, this.djMoney = i.djMoney + o.djMoney || 0, this.userActionOptions.type = 148, s = {
                            loanamount: this.calculateComMsg.sd.totalMoney,
                            loantime: encodeURIComponent(this.year + "年"),
                            providentfundloanamount: this.calculateComMsg.gjj.totalMoney,
                            annualinterestrate: 100 * this.calculateComMsg.sd.rate + "%",
                            loanrateforgjj: 100 * this.calculateComMsg.gjj.rate + "%",
                            repaymentmethod: 1 === this.calculateComMsg.sd.type ? "等额本息" : "等额本金",
                            xtfh_yuegong: this.monMoney,
                            xtfh_paymentinterest: this.totalLx,
                            xtfh_repaymentamount: this.totalMoney
                        }, 1 !== this.calculateComMsg.sd.type && (s.xtfh_monthlyrepayment = this.monMoney, s.xtfh_monthdeclineamount = this.djMoney)
                    } else {
                        var n = p.calculate(t);
                        this.firstMoney = this.firstMoneyBx || 0, this.dkMoney = (this.showDkIpt ? 1e4 * this.dktotal : this.dkTotalMoney) || 0, this.monMoney = n.firstMonYg || 0, this.totalLx = m.formatNumber(n.toatlInterest) || 0, this.totalMoney = m.formatNumber(a.sum(n.totalMoney, n.toatlInterest)) || 0, this.totalMon = 12 * this.year + "个" || 0, this.djMoney = m.formatNumber(n.djMoney) || 0, s = {
                            xtfh_downpayment: this.firstMoney,
                            loantime: encodeURIComponent(this.year + "年"),
                            repaymentmethod: 1 === this.calculateMsg.type ? "等额本息" : "等额本金",
                            xtfh_loanamount: this.dkMoney,
                            xtfh_loantime: encodeURIComponent(this.totalMon + "月"),
                            xtfh_yuegong: this.monMoney,
                            xtfh_paymentinterest: this.totalLx,
                            xtfh_repaymentamount: this.totalMoney
                        }, "根据面积、单价计算" === this.calcuTypeCon && (s.unitprice = this.unitPrice, s.area = this.buildArea, s.loanradio = encodeURIComponent(this.few + "成"), s.totalprice = this.unitPrice * this.buildArea), "gjj" === this.type ? (s.loanrateforgjj = this.rate, this.userActionOptions.type = 147) : (s.annualinterestrate = this.rate, this.userActionOptions.type = 146), 1 !== this.calculateMsg.type && (s.xtfh_monthlyrepayment = this.monMoney, s.xtfh_monthdeclineamount = this.djMoney)
                    }
                    this.userActionOptions.params = {}, this.userActionOptions.params = this.deepClone(s), /**console.log(this.userActionOptions),*/ (0, v.yhxw)(this.userActionOptions), this.showResult = !1, setTimeout(function () {
                        e.showResult = !0
                    }, 0)
                }, deepClone: function (t) {
                    var e = t instanceof Array ? [] : {};
                    for (var a in t) ({}).hasOwnProperty.call(t, a) && (e[a] = i(t[a]) === Object ? this.deepClone(t[a]) : t[a]);
                    return e
                }, startCalcu: function () {
                    if (this.showCom) {
                        if ("" === this.sdtotal && this.sdRate > 0) return this.$toast.show("请输入商业贷款金额"), this.showResult = !1, !1;
                        if ("" === this.gjjtotal && this.gjjRate > 0) return this.$toast.show("请输入公积金贷款金额"), this.showResult = !1, !1;
                        if (this.sdtotal && !this.sdRate) return this.$toast.show("请输入商业贷款利率"), this.showResult = !1, !1;
                        if (this.gjjtotal && !this.gjjRate) return this.$toast.show("请输入公积金贷款利率"), this.showResult = !1, !1;
                        if (!(this.sdtotal || this.sdRate || this.gjjRate || this.gjjtotal)) return this.$toast.show("请输入商业贷款金额"), this.showResult = !1, !1;
                        this.calculateComMsg.gjj.month = 12 * this.year, this.calculateComMsg.sd.month = 12 * this.year, this.calculateComMsg.gjj.rate = this.gjjRate / 100 || 0, this.showBasisPoint ? this.calculateComMsg.sd.rate = this.caRate / 100 || 0 : this.calculateComMsg.sd.rate = this.sdRate / 100 || 0, /**console.log(2),*/ /**console.log(this.calculateComMsg.sd.rate),*/ this.calculate(this.calculateComMsg)
                    } else {
                        if (this.showDkIpt && !this.dktotal) return this.$toast.show("请输入贷款总额"), this.showResult = !1, !1;
                        if (!this.showDkIpt) {
                            if (!this.unitPrice) return this.$toast.show("请输入房屋单价"), this.showResult = !1, !1;
                            if (!this.buildArea) return this.$toast.show("请输入房屋面积"), this.showResult = !1, !1
                        }
                        if ("sd" === this.type) {
                            if (!this.caRate || "0" === this.caRate) return this.$toast.show("利率输入不合法，请重新输入"), !1
                        } else if (!this.rate || "0" === this.rate) return this.$toast.show("利率输入不合法，请重新输入"), !1;
                        this.dkMoney = this.showDkIpt ? 1e4 * this.dktotal || 0 : this.dkTotalMoney, this.calculateMsg.totalMoney = this.dkMoney, "sd" === this.type && this.showBasisPoint ? this.calculateMsg.rate = this.caRate / 100 : this.calculateMsg.rate = this.rate / 100, this.calculateMsg.month = 12 * this.year, /**console.log(1), console.log(this.calculateMsg.rate),*/ this.calculate(this.calculateMsg)
                    }
                }, hide: function (t) {
                    for (var e = ["showYear", "showRate", "showRatesd", "showRategjj", "showFew", "showDkType", "showCalType"], a = this, s = 0; s < e.length; s++) e[s] !== t && (a[e[s]] = !1)
                }, clearAll: function () {
                    var t = this;
                    this.showResult = !1, this.showDkIpt = !1, this.calcuTypeCon = "根据面积、单价计算", this.fewCon = "bj" === this.G.city || "sh" === this.G.city ? "6.5成" : "7成", this.few = "bj" === this.G.city || "sh" === this.G.city ? 6.5 : 7, this.buildArea = "", this.unitPrice = "", this.dktotal = "", this.year = 30, this.yearCon = "30年（ 360期 ）", this.totalLx = 0, this.monMoney = 0, this.totalMoney = 0, this.totalMon = "", this.calculateMsg = {
                        rate: .049,
                        month: 240,
                        totalMoney: "",
                        type: 1
                    }, this.calculateComMsg = {
                        sd: {rate: .049, month: "", totalMoney: "", type: 1},
                        gjj: {rate: .025, month: "", totalMoney: "", type: 1}
                    };
                    var e = t.rateItems[0];
                    t.rateMsg(e), t.rateComSd(e), t.rateComGjj(e), this.gjjtotal = "", this.sdtotal = "", this.isType1 = "每月月供", this.djMoney = "", this.firstMoney = "0", this.dkMoney = "0", this.basisPoint = 0, this.disCount = {
                        disCount: 1,
                        sdDisCount: 1,
                        gjjDisCount: 1
                    }, this.isWidth5 = "com" === this.type ? "counter_list_width5" : "counter_list_width1", this.items3.forEach(function (t, e, a) {
                        a[e].cla = ""
                    }), this.items3[0].cla = "on"
                }, getLoanRate: function () {
                    var t = this;
                    var loanRate = [
                        {
                            "id": "94",
                            "calculatename": "最新报价利率（LPR）",
                            "commercefirst": "0.0385",
                            "commercesecond": "0.0385",
                            "commerceone": "0.0385",
                            "commerceten": "0.0465",
                            "fundone": "0.0275",
                            "fundten": "0.0325",
                            "isshow": "1",
                            "defaultshow": "1",
                            "sort": "92",
                            "addtime": "2020-05-28 14:56:27",
                            "updatetime": "2020-06-09 15:09:22"
                        },
                        {
                            "id": "86",
                            "calculatename": "基准利率",
                            "commercefirst": "0.0435",
                            "commercesecond": "0.0475",
                            "commerceone": "0.0475",
                            "commerceten": "0.049",
                            "fundone": "0.0275",
                            "fundten": "0.0325",
                            "isshow": "1",
                            "defaultshow": "",
                            "sort": "91",
                            "addtime": "2015-10-24 10:15:40",
                            "updatetime": "2020-06-09 15:09:34"
                        },
                        {
                            "id": "87",
                            "calculatename": "基准利率下限（7折）",
                            "commercefirst": "0.03045",
                            "commercesecond": "0.03325",
                            "commerceone": "0.03325",
                            "commerceten": "0.0343",
                            "fundone": "0.01925",
                            "fundten": "0.02275",
                            "isshow": "1",
                            "defaultshow": "",
                            "sort": "90",
                            "addtime": "2015-10-24 10:15:40",
                            "updatetime": "2020-06-09 15:09:48"
                        },
                        {
                            "id": "89",
                            "calculatename": "基准利率上限（1.1倍）",
                            "commercefirst": "0.04785",
                            "commercesecond": "0.05225",
                            "commerceone": "0.05225",
                            "commerceten": "0.0539",
                            "fundone": "0.03025",
                            "fundten": "0.03575",
                            "isshow": "1",
                            "defaultshow": "",
                            "sort": "89",
                            "addtime": "2015-10-24 10:15:40",
                            "updatetime": "2020-06-09 15:09:59"
                        },
                        {
                            "id": "90",
                            "calculatename": "基准利率上限（1.05倍）",
                            "commercefirst": "0.045675",
                            "commercesecond": "0.049875",
                            "commerceone": "0.049875",
                            "commerceten": "0.05145",
                            "fundone": "0.028875",
                            "fundten": "0.034125",
                            "isshow": "1",
                            "defaultshow": "",
                            "sort": "0",
                            "addtime": "2017-09-21 16:40:55",
                            "updatetime": "2020-06-09 15:10:44"
                        },
                        {
                            "id": "88",
                            "calculatename": "基准利率下限（85折）",
                            "commercefirst": "0.036975",
                            "commercesecond": "0.040375",
                            "commerceone": "0.040375",
                            "commerceten": "0.04165",
                            "fundone": "0.023375",
                            "fundten": "0.027625",
                            "isshow": "1",
                            "defaultshow": "",
                            "sort": "0",
                            "addtime": "2015-10-24 10:15:40",
                            "updatetime": "2020-06-09 15:10:24"
                        }
                    ]

                    if (loanRate && "object" === i(loanRate)) {
                        t.rateItems = loanRate;
                        var a = t.rateItems[0];
                        t.rateMsg(a), t.rateComSd(a), t.rateComGjj(a)
                    }
                    // this.$axios.get(t.G.jk + "&a=getLoanRate").then(function (e) {
                    //     if (e.data && "object" === i(e.data)) {
                    //         t.rateItems = e.data;
                    //         var a = t.rateItems[0];
                    //         t.rateMsg(a), t.rateComSd(a), t.rateComGjj(a)
                    //     }
                    // }).catch(function (t) {
                    //     console.warn("getLoanRate", t)
                    // })
                }
            }
        }
    }, 178: function (t, e, a) {
        "use strict";

        function s(t) {
            return t && t.__esModule ? t : {default: t}
        }

        Object.defineProperty(e, "__esModule", {value: !0});
        var i = a(146), o = s(i), n = a(57), r = (s(n), a(45)), l = a(51);
        e.default = {
            components: {AjYear: o.default}, name: "gfnl", data: function () {
                return {
                    showYear: !1,
                    yearCon: "20年（240期）",
                    year: 20,
                    totalPrice: 0,
                    unitPrice: 0,
                    monthIncome: "",
                    monthPay: "",
                    expectArea: "",
                    showResult: !1,
                    totalMoney: "",
                    rate: .0049,
                    showUnable: !1,
                    userActionOptions: {type: 0, pageId: "jsq_pg^zxdk_pc"}
                }
            }, methods: {
                clickYear: function (t) {
                    this.showYear = !this.showYear, t.stopPropagation()
                }, yearMsg: function (t) {
                    this.yearCon = t.text, this.year = t.val, this.showYear = !1
                }, limitVal: function (t, e) {
                    return e.match(t)[0]
                }, infoIpt1: function (t) {
                    this.totalMoney = this.limitVal(/\d{0,10}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt2: function (t) {
                    this.monthIncome = this.limitVal(/\d{0,8}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt3: function (t) {
                    this.monthPay = this.limitVal(/\d{0,8}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt4: function (t) {
                    this.expectArea = this.limitVal(/\d{0,4}(\.\d{0,2})?/g, t.target.value)
                }, startCalculate: function () {
                    var t = this, e = this;
                    return this.totalMoney ? this.totalMoney <= 0 ? (this.$toast.show("可用于购房的资金应大于0，请重新输入"), !1) : this.monthIncome ? this.monthIncome <= 0 ? (this.$toast.show("家庭月收入应大于等于0，请重新输入"), !1) : this.monthPay ? this.monthPay <= 0 ? (this.$toast.show("家庭每月可用于购房的开支应大于等于0，请重新输入"), !1) : this.expectArea ? this.expectArea <= 0 ? (this.$toast.show("计划购买房屋的面积应大于等于0，请重新输入"), !1) : (this.totalPrice = parseFloat((((this.monthIncome - this.monthPay) * ((Math.pow(1 + this.rate, 12 * this.year) - 1) / (this.rate * Math.pow(1 + this.rate, 12 * this.year))) + parseFloat(this.totalMoney)) / 1e4).toFixed(2)), this.unitPrice = l.formatNumber(1e4 * e.totalPrice / this.expectArea) || 0, this.showUnable = this.totalPrice < 0, this.showResult = !1, this.userActionOptions.type = 50, this.userActionOptions.params = {
                        ownfunds: this.totalMoney,
                        monthlyincome: this.monthIncome,
                        expend: this.monthPay,
                        loantime: this.year + "年",
                        area: this.expectArea
                    }, (0, r.yhxw)(this.userActionOptions), void setTimeout(function () {
                        t.showResult = !0
                    }, 0)) : (this.$toast.show("请填写您计划购买房屋的面积"), !1) : (this.$toast.show("请填写预计家庭每月可用于购房支出"), !1) : (this.$toast.show("请填写现家庭月收入"), !1) : (this.$toast.show("请填写现可用于购房的资金"), !1)
                }, clearAll: function () {
                    this.totalMoney = "", this.year = 20, this.yearCon = "20年（240期）", this.totalPrice = 0, this.unitPrice = 0, this.monthIncome = "", this.monthPay = "", this.expectArea = "", this.showResult = !1
                }
            }, mounted: function () {
                var t = this;
                (0, r.yhxw)(this.userActionOptions), window.addEventListener("click", function () {
                    if (!t.showYear) return !1;
                    t.showYear = !1
                })
            }
        }
    }, 179: function (t, e, a) {
        "use strict";

        function s(t) {
            return t && t.__esModule ? t : {default: t}
        }

        Object.defineProperty(e, "__esModule", {value: !0});
        var i = a(101), o = s(i), n = a(57), r = s(n), l = a(45);
        e.default = {
            components: {SelectComponent: o.default, Toast: r.default}, name: "fangdai", data: function () {
                return {
                    items1: [{text: "政策性住房", cla: "", val: 0}, {text: "其他", cla: "on", val: 1}],
                    items2: [{text: "AAA级", cla: "", val: 0}, {text: "AA级", cla: "", val: 1}, {
                        text: "其他",
                        cla: "on",
                        val: 2
                    }],
                    gjjJc: "",
                    jcPoint: 12,
                    otherJc: "",
                    otherJcPoint: 12,
                    buildPrice: "",
                    dkYear: 20,
                    fwType: 1,
                    selfxyJb: 2,
                    totalMoney: 0,
                    showResult: !1,
                    userActionOptions: {type: 0, pageId: "jsq_fd^gjjdked_pc", params: {}}
                }
            }, methods: {
                jbMsg: function (t) {
                    this.selfxyJb = t.val
                }, fwMsg: function (t) {
                    this.fwType = t.val
                }, limitVal: function (t, e) {
                    return e.match(t)[0]
                }, infoIpt1: function (t) {
                    this.gjjJc = this.limitVal(/\d{0,6}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt2: function (t) {
                    this.jcPoint = this.limitVal(/\d{0,2}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt3: function (t) {
                    this.otherJc = this.limitVal(/\d{0,6}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt4: function (t) {
                    this.otherJcPoint = this.limitVal(/\d{0,2}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt5: function (t) {
                    this.buildPrice = this.limitVal(/\d{0,12}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt6: function (t) {
                    this.dkYear = this.limitVal(/\d{0,2}/g, t.target.value)
                }, getResult: function () {
                    var t = this, e = this;
                    if (!this.gjjJc) return this.$toast.show("住房公积金个人月缴存额不能为空，请输入"), !1;
                    if (!this.jcPoint || this.jcPoint > 100 || 0 == this.jcPoint) return this.$toast.show("缴存比例不正确"), !1;
                    if (this.otherJc && (!this.otherJcPoint || this.otherJcPoint > 100 || 0 == this.otherJcPoint)) this.$toast.show("配偶缴存比例不正确"); else {
                        if (!this.buildPrice) return this.$toast.show('"房屋评估价值或实际购房款"不能为空，请输入'), !1;
                        if (0 == this.buildPrice) return this.$toast.show('"房屋评估价值或实际购房款"不能为零，请输入'), !1;
                        if (!this.dkYear) return this.$toast.show("贷款年限不能为空，请输入"), !1;
                        if (0 == this.dkYear) return this.$toast.show("贷款年限不能为零，请重新输入"), !1;
                        if (this.dkYear > 30) return this.$toast.show("贷款年限不能大于30年，请重新输入"), this.dkYear = "", !1
                    }
                    var a = (this.gjjJc / (this.jcPoint / 100) || 0) + (this.otherJc / (this.otherJcPoint / 100) || 0);
                    if (a <= 400) return this.$toast.show("家庭月收入低于400，不符合贷款条件"), !1;
                    var s = Math.round((a - 400) / this.calculate() * 1e4 * 10) / 10, i = void 0;
                    switch (e.selfxyJb) {
                        case 0:
                            s *= 1.3;
                            break;
                        case 1:
                            s *= 1.15
                    }
                    switch (s = Math.min(s, 8e5), e.fwType) {
                        case 0:
                            i = .9 * e.buildPrice;
                            break;
                        case 1:
                            i = .8 * e.buildPrice
                    }
                    s = Math.floor(Math.min(s, i) / 1e4 * 10) / 10, this.totalMoney = s || 0, this.showResult = !1;
                    var o = void 0;
                    o = 0 === this.selfxyJb ? "AAA级" : 1 === this.selfxyJb ? "AA级" : "其他", this.userActionOptions.type = 147, this.userActionOptions.params = {
                        cpfdepositmine: this.gjjJc,
                        cpfratiomine: this.jcPoint,
                        cpfdepositspouse: this.otherJc,
                        cpfratiospouse: this.otherJcPoint,
                        totalprice: this.buildPrice,
                        dwellingtype: 1 === this.fwType ? "其他" : "政策性住房",
                        loantime: this.dkYear + "年",
                        creditgrade: o,
                        xtfh_loanamount: encodeURIComponent(this.totalMoney + "万")
                    }, (0, l.yhxw)(this.userActionOptions), setTimeout(function () {
                        t.showResult = !0
                    }, 0)
                }, formatNumber: function (t, e) {
                    var a = void 0, s = void 0, i = void 0, o = void 0;
                    if (a = t.toString(), s = a.indexOf("."), i = a.length, 0 == e) -1 != s && (a = a.substring(0, s)); else if (-1 == s) for (a += ".", o = 1; o <= e; o++) a += "0"; else for (a = a.substring(0, s + e + 1), o = i; o <= s + e; o++) a += "0";
                    return a
                }, formatData: function (t, e) {
                    var a = this.formatNumber(t, e), s = parseFloat(a);
                    if (t.toString().length > a.length) {
                        var i = t.toString().substring(a.length, a.length + 1);
                        if (parseFloat(i) < 5) return a;
                        var o = void 0, n = void 0;
                        if (0 == e) n = 1; else {
                            o = "0.";
                            for (var r = 1; r < e; r++) o += "0";
                            o += "1", n = parseFloat(o)
                        }
                        a = this.formatNumber(s + n, e)
                    }
                    return a
                }, calculate: function () {
                    var t = this, e = void 0;
                    return e = t.dkYear > 5 ? Math.round(3825) / 1e6 : Math.round(3375) / 1e6, this.formatData(1e4 * e * Math.pow(1 + e, 12 * t.dkYear) / (Math.pow(1 + e, 12 * t.dkYear) - 1), 2)
                }, resetData: function (t, e) {
                    for (var a = 0; a < t.length; a++) t[a].cla = "";
                    t[0].cla = "on"
                }, clearBoth: function () {
                    this.gjjJc = "", this.jcPoint = "", this.otherJc = "", this.otherJcPoint = "", this.buildPrice = "", this.dkYear = "", this.totalMoney = 0, this.resetData(this.items1, this.fwType), this.resetData(this.items2, this.selfxyJb), this.showResult = !1
                }
            }, mounted: function () {
                (0, l.yhxw)(this.userActionOptions)
            }
        }
    }, 180: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.default = {
            name: "fwxzComponent", data: function () {
                return {
                    items: [{text: "普通住宅", val: 1}, {text: "非普通住宅", val: 2}, {text: "经济适用房", val: 3}],
                    fill: function (t) {
                        this.$emit("fw-msg", t)
                    }
                }
            }
        }
    }, 181: function (t, e, a) {
        "use strict";

        function s(t) {
            return t && t.__esModule ? t : {default: t}
        }

        Object.defineProperty(e, "__esModule", {value: !0});
        var i = a(101), o = s(i), n = a(418), r = s(n), l = a(420), c = s(l), u = a(57), h = s(u), v = a(45), m = a(51);
        e.default = {
            components: {JzType: c.default, FwxzComponent: r.default, SelectComponent: o.default, Toast: h.default},
            name: "shuifei",
            data: function () {
                return {
                    items1: [{text: "新房", cla: "on"}, {text: "二手房", cla: ""}],
                    items2: [{text: "满5年", cla: "on", val: 3}, {text: "满2年", cla: "", val: 2}, {
                        text: "不满两年",
                        cla: "on",
                        val: 1
                    }],
                    items3: [{text: "是", cla: "on", val: 1}, {text: "否", cla: "", val: 2}],
                    items4: [{text: "是", cla: "on", val: 1}, {text: "否", cla: "", val: 2}],
                    items5: [{text: "是", cla: "on", val: 1}, {text: "否", cla: "", val: 2}],
                    buildArea: "",
                    unitPrice: "",
                    price2: "",
                    jzType: 0,
                    jzTypeCon: "总价",
                    buildType: 1,
                    buildTypeCon: "普通住宅",
                    isFive: 3,
                    isFirst: 1,
                    elevator: 1,
                    isOnly: 1,
                    showFw: !1,
                    showJz: !1,
                    showEsf: !1,
                    resultTotal: 0,
                    yhTax: 0,
                    qTax: 0,
                    gbTax: "",
                    qsdjTax: "",
                    wxjjTax: "",
                    gbyhTax: "",
                    zzTax: "",
                    zhdjTax: "",
                    gsTax: "",
                    totalTax: 0,
                    ajaxData: {},
                    showResult: !1,
                    tipCon: "要买新房，快来算算吧！",
                    totalMoney: "",
                    userActionOptions: {type: 0, pageId: "jsq_shuifei^sfjsq_pc", params: {}}
                }
            },
            computed: {
                showYj: function () {
                    return this.showEsf && 2 === this.jzType
                }
            },
            methods: {
                selectMsg: function (t) {
                    this.showEsf = "二手房" === t.text, this.tipCon = "二手房" === t.text ? "要买二手房，快来算算吧！" : "要买新房，快来算算吧！", this.clearSelect()
                }, clickJz: function (t) {
                    this.showJz = !this.showJz, t.stopPropagation()
                }, clickFw: function (t) {
                    this.showFw = !this.showFw, t.stopPropagation()
                }, limitVal: function (t, e) {
                    return e.match(t)[0]
                }, infoIpt1: function (t) {
                    this.buildArea = this.limitVal(/\d{0,4}(\.\d{0,2})?/g, t.target.value), this.showEsf && (this.totalMoney = parseFloat((this.buildArea * this.unitPrice / 1e4).toFixed(2)) || "")
                }, infoIpt2: function (t) {
                    this.unitPrice = this.limitVal(/\d{0,6}/, t.target.value), this.showEsf && (this.totalMoney = parseFloat((this.buildArea * this.unitPrice / 1e4).toFixed(2)) || "")
                }, infoIpt3: function (t) {
                    this.totalMoney = this.limitVal(/\d{0,6}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt4: function (t) {
                    this.price2 = this.limitVal(/\d{0,6}(\.\d{0,2})?/g, t.target.value)
                }, fwMsg: function (t) {
                    this.buildTypeCon = t.text, this.buildType = t.val, this.showFw = !1
                }, jzMsg: function (t) {
                    this.jzType = t.val, this.jzTypeCon = t.text, this.showJz = !1
                }, fiveMsg: function (t) {
                    this.isFive = t.val
                }, firstMsg: function (t) {
                    this.isFirst = t.val
                }, elevatorMsg: function (t) {
                    this.elevator = t.val
                }, onlyMsg: function (t) {
                    this.isOnly = t.val
                }, resetData: function (t, e) {
                    for (var a = 0; a < t.length; a++) t[a].cla = "";
                    t[0].cla = "on", t[0].val
                }, clearSelect: function () {
                    this.buildArea = "", this.unitPrice = "", this.totalMoney = "", this.jzType = 1, this.jzTypeCon = "总价", this.buildType = 1, this.buildTypeCon = "普通住宅", this.resetData(this.items2, this.isFive), this.resetData(this.items3, this.isFirst), this.resetData(this.items4, this.isOnly), this.showResult = !1
                }, getTax: function () {
                    var t = this;
                    if (this.showEsf) {
                        if (!this.buildArea) return this.$toast.show("请输入房屋面积"), !1;
                        if ("0" === this.buildArea) return this.$toast.show("房屋面积不能为0"), !1;
                        if (!this.unitPrice) return this.$toast.show("请输入房屋单价"), !1;
                        if (!this.totalMoney) return this.$toast.show("请输入房屋总价"), !1;
                        if ("0" === this.totalMoney) return this.$toast.show("房屋总价不能为0"), !1;
                        if (2 === this.jzType && !this.price2) return this.$toast.show("请输入房屋原价"), !1;
                        this.userActionOptions.type = 37, this.ajaxData = {
                            CaculateType: this.jzType,
                            Area: this.buildArea,
                            HouseType: this.buildType,
                            IsFirstHouse: this.isFirst,
                            IsOnlyHouse: this.isOnly,
                            Price: 1e4 * this.totalMoney,
                            Price2: 1e4 * this.price2,
                            YearType: this.isFive,
                            city: this.G.city
                        }, this.$axios.get(t.G.urlESFSF, {params: t.ajaxData}).then(function (e) {
                            var a = e.data;
                            if (a) {
                                t.gsTax = m.formatNumber(a.geshui, 2), t.gbyhTax = m.formatNumber(a.gongbenyinhuashui, 2), t.qTax = m.formatNumber(a.qishui, 2), t.yhTax = m.formatNumber(a.yinhuashui, 2), t.zzTax = m.formatNumber(a.zengzhishui, 2), t.zhdjTax = m.formatNumber(a.zonghedijiakuan, 2), t.totalTax = m.formatNumber(a.heji, 2);
                                var s;
                                1 === t.isFive ? s = "不满两年" : 2 === t.isFive ? s = "满两年不满5年" : 3 === t.isFive && (s = "满5年"), t.userActionOptions.params = {
                                    xtfh_addtax: t.zzTax,
                                    xtfh_contracttax: t.qTax,
                                    xtfh_coststamptax: t.gbyhTax,
                                    xtfh_incometax: t.gsTax,
                                    xtfh_integratedcost: t.zhdjTax,
                                    area: t.buildArea,
                                    unitprice: t.unitPrice,
                                    totalprice: t.ajaxData.Price,
                                    valuebasedway: 0 === t.ajaxData.CaculateType ? "总价" : "差价",
                                    dwellingtype: t.buildTypeCon,
                                    year: s,
                                    isfirst: 1 === t.isFirst ? "是" : "否",
                                    isonly: 1 === t.isOnly ? "是" : "否",
                                    xtfh_stamptax: t.yhTax,
                                    tax: t.totalTax
                                }, (0, v.yhxw)(t.userActionOptions), t.showResult = !1, setTimeout(function () {
                                    t.showResult = !0
                                }, 0)
                            } else t.$toast.show("输入信息不合法，请重新输入")
                        }).catch(function (e) {
                            t.$toast.show(e)
                        })
                    } else {
                        if (!this.buildArea) return this.$toast.show("请输入房屋面积"), !1;
                        if ("0" === this.buildArea) return this.$toast.show("房屋面积不能为0"), !1;
                        if (!this.unitPrice) return this.$toast.show("请输入房屋单价"), !1;
                        if ("0" === this.unitPrice) return this.$toast.show("房屋单价不能为0"), !1;
                        this.userActionOptions.type = 36, this.ajaxData = {
                            Area: t.buildArea,
                            IsFirstHouse: t.isFirst,
                            priceper: t.unitPrice,
                            elevator: t.elevator,
                            city: this.G.city
                        }, this.$axios.get(t.G.urlXFSF, {params: t.ajaxData}).then(function (e) {
                            var a = e.data;
                            a ? (t.qTax = m.formatNumber(a.qishui, 2), t.gbTax = m.formatNumber(a.gongbenfei, 2), t.qsdjTax = m.formatNumber(a.quanshudengjifei, 2), t.wxjjTax = m.formatNumber(a.weixiujijin, 2), t.totalTax = m.formatNumber(a.shuifeizongjia, 2), t.resultTotal = m.formatNumber(a.zongjia, 2), t.showResult = !1, t.userActionOptions.params = {
                                area: t.buildArea,
                                unitprice: t.unitPrice,
                                genre: "",
                                isfirst: 1 === t.isFirst ? "是" : "否",
                                xtfh_totalprice: t.buildArea * t.unitPrice,
                                elevator: 1 === t.elevator ? "是" : "否",
                                tax: t.totalTax,
                                xtfh_contracttax: t.qTax
                            }, (0, v.yhxw)(t.userActionOptions), setTimeout(function () {
                                t.showResult = !0
                            }, 0)) : t.$toast.show("输入不合法，请重新输入")
                        }).catch(function (e) {
                            t.$toast.show(e)
                        })
                    }
                }
            },
            mounted: function () {
                var t = this;
                (0, v.yhxw)(this.userActionOptions);
                for (var e = ["showJz", "showFw"], a = 0; a < e.length; a++) !function (e) {
                    window.addEventListener("click", function () {
                        if (!t[e]) return !1;
                        t[e] = !1
                    })
                }(e[a])
            }
        }
    }, 182: function (t, e, a) {
        "use strict";
        Object.defineProperty(e, "__esModule", {value: !0}), e.default = {
            name: "jzType", data: function () {
                return {
                    items: [{text: "总价", val: 1}, {text: "差价", val: 2}], fill: function (t) {
                        this.$emit("jztype-msg", t)
                    }
                }
            }
        }
    }, 183: function (t, e, a) {
        "use strict";

        function s(t, e, a) {
            for (var s = v - e, i = v + a, o = [], n = s; n <= i; n++) o.push({value: n, name: n + "年"});
            return o
        }

        Object.defineProperty(e, "__esModule", {value: !0});
        var i = a(45), o = a(412), n = a(185), r = a(108), l = a(51),
            c = [{value: 38, name: "15年10月24日利率上限（1.3倍）"}, {value: 37, name: "15年10月24日利率上限（1.2倍）"}, {
                value: 36,
                name: "15年10月24日利率上限（1.1倍）"
            }, {value: 35, name: "15年10月24日利率上限（1.05倍）"}, {value: 34, name: "15年10月24日利率下限（95折）"}, {
                value: 33,
                name: "15年10月24日利率下限（9折）"
            }, {value: 32, name: "15年10月24日利率下限（88折）"}, {value: 31, name: "15年10月24日利率下限（85折）"}, {
                value: 30,
                name: "15年10月24日利率下限（7折）"
            }, {value: 29, name: "15年10月24日基准利率"}, {value: 28, name: "15年8月26日利率上限（1.1倍）"}, {
                value: 27,
                name: "15年8月26日利率下限（85折）"
            }, {value: 26, name: "15年8月26日利率下限（7折）"}, {value: 25, name: "15年8月26日基准利率"}, {
                value: 24,
                name: "15年6月28日利率上限（1.1倍）"
            }, {value: 23, name: "15年6月28日利率下限（85折）"}, {value: 22, name: "15年6月28日利率下限（7折）"}, {
                value: 21,
                name: "15年6月28日基准利率"
            }, {value: 20, name: "15年5月11日利率上限（1.1倍）"}, {value: 19, name: "15年5月11日利率下限（85折）"}, {
                value: 18,
                name: "15年5月11日利率下限（7折）"
            }, {value: 17, name: "15年5月11日基准利率"}, {value: 16, name: "15年3月1日利率上限（1.1倍）"}, {
                value: 15,
                name: "15年3月1日利率下限（85折）"
            }, {value: 14, name: "15年3月1日利率下限（7折）"}, {value: 13, name: "15年3月1日基准利率"}, {
                value: 12,
                name: "14年11月22日利率上限（1.1倍）"
            }, {value: 11, name: "14年11月22日利率下限（85折）"}, {value: 10, name: "14年11月22日利率下限（7折）"}, {
                value: 9,
                name: "14年11月22日基准利率"
            }, {value: 8, name: "12年7月6日利率上限（1.1倍）"}, {value: 7, name: "12年7月6日利率下限（85折）"}, {
                value: 6,
                name: "12年7月6日利率下限（7折）"
            }, {value: 5, name: "12年7月6日基准利率"}, {value: 4, name: "12年6月8日利率上限（1.1倍）"}, {
                value: 3,
                name: "12年6月8日利率下限（85折）"
            }, {value: 2, name: "12年6月8日利率下限（7折）"}, {value: 1, name: "12年6月8日基准利率"}],
            u = [{name: "商业贷款", value: 0}, {name: "公积金贷款", value: 1}],
            h = [{name: "2年(24期)", value: 24}, {name: "3年(36期)", value: 36}, {
                name: "4年(48期)",
                value: 48
            }, {name: "5年(60期)", value: 60}, {name: "6年(72期)", value: 72}, {
                name: "7年(84期)",
                value: 84
            }, {name: "8年(96期)", value: 96}, {name: "9年(108期)", value: 108}, {
                name: "10年(120期)",
                value: 120
            }, {name: "11年(132期)", value: 132}, {name: "12年(144期)", value: 144}, {
                name: "13年(156期)",
                value: 156
            }, {name: "14年(168期)", value: 168}, {name: "15年(180期)", value: 180}, {
                name: "20年(240期)",
                value: 240
            }, {name: "25年(300期)", value: 300}, {name: "30年(360期)", value: 360}], v = (new Date).getFullYear(),
            m = s(v, 12, 0), p = s(v, 7, 30),
            d = [{value: 1, name: "01月"}, {value: 2, name: "02月"}, {value: 3, name: "03月"}, {
                value: 4,
                name: "04月"
            }, {value: 5, name: "05月"}, {value: 6, name: "06月"}, {value: 7, name: "07月"}, {
                value: 8,
                name: "08月"
            }, {value: 9, name: "09月"}, {value: 10, name: "10月"}, {value: 11, name: "11月"}, {value: 12, name: "12月"}],
            f = [{value: "short", name: "缩短还款期限，月还款额基本不变"}, {value: "reduce", name: "减少月还款额，还款期限不变"}],
            _ = [{value: "all", name: "一次性还清"}, {value: "part", name: "部分提前还款"}];
        e.default = {
            name: "Huandai", components: {dropSelect: o, Toast: r}, data: function () {
                return {
                    hasTip: !1,
                    calDone: !1,
                    repayType: u,
                    repayTime: h,
                    rateType: c,
                    firstYear: m,
                    month: d,
                    advYear: p,
                    repayMode: _,
                    repayMethod: f,
                    selected: {
                        repayType: u[0],
                        repayNum: "",
                        repayTime: h[14],
                        rateType: c[8],
                        firstYear: m[12],
                        firstMonth: d[(new Date).getMonth()],
                        advYear: p[7],
                        advMonth: d[(new Date).getMonth()],
                        repayMode: {value: "all"},
                        repayMethod: {value: "short"},
                        partInput: ""
                    },
                    result: {
                        resultNum: 0,
                        resultDate: "",
                        resultAlready: "",
                        resultAlreadyInterest: "",
                        resultOnce: "",
                        resultNextMonth: "",
                        resultSave: "",
                        resultDateNew: "",
                        resultTip: ""
                    },
                    userActionOptions: {type: 0, pageId: "jsq_fd^tqhk_pc", params: {}}
                }
            }, mounted: function () {
                (0, i.yhxw)(this.userActionOptions)
            }, methods: {
                selectType: function (t, e) {
                    this.selected[e] = t
                }, yfdIpt: function (t) {
                    var e = t.target.value;
                    this.selected.repayNum = e.match(/\d{0,10}(\.\d{0,2})?/g)[0]
                }, cal: function () {
                    var t = this, e = this, a = this.selected, s = a.repayNum, o = a.repayMode,
                        n = a.partInput ? a.partInput : "";
                    if (!s) return void this.$toast.show("请填入贷款总额");
                    if (s = 1e4 * parseFloat(s), "part" === o.value && !n) return void this.$toast.show("请填入部分提前还款额度");
                    var r = a.repayTime.value, c = void 0;
                    1 === a.repayType.value && (c = r > 60 ? this.getlilv(a.rateType.value, 2, 10) / 12 : this.getlilv(a.rateType.value, 2, 3) / 12), 0 === a.repayType.value && (c = r > 60 ? this.getlilv(a.rateType.value, 1, 10) / 12 : this.getlilv(a.rateType.value, 1, 3) / 12);
                    var u = 12 * a.advYear.value + a.advMonth.value - (12 * a.firstYear.value + a.firstMonth.value);
                    if (u < 0 || u > r) return this.$toast.show("预计提前还款时间与第一次还款时间有矛盾，请查实"), !1;
                    for (var h = s * (c * Math.pow(1 + c, r)) / (Math.pow(1 + c, r) - 1), v = Math.floor((12 * a.firstYear.value + a.firstMonth.value + r - 2) / 12) + "年" + ((12 * a.firstYear.value + a.firstMonth.value + r - 2) % 12 + 1) + "月", m = h * u, p = 0, d = 0, f = 1; f <= u; f++) p += (s - d) * c, d = d + h - (s - d) * c;
                    var _ = "", w = void 0, y = void 0, g = void 0, x = void 0;
                    if ("part" === o.value) if ((n = 1e4 * parseInt(n, 10)) + h >= (s - d) * (1 + c)) _ = "您的提前还款额已足够还清所欠贷款！", e.hasTip = !0; else if (e.hasTip = !1, d += h, w = h + n, "short" === a.repayMethod.value) {
                        var j = d + n, C = 0;
                        for (C = 0; j <= s; C++) j = j + h - (s - j) * c;
                        C -= 1, y = (s - d - n) * (c * Math.pow(1 + c, C)) / (Math.pow(1 + c, C) - 1), g = h * r - m - w - y * C, x = Math.floor((12 * a.advYear.value + a.advMonth.value + C - 2) / 12) + "年" + ((12 * a.advYear.value + a.advMonth.value + C - 2) % 12 + 1) + "月"
                    } else "reduce" === a.repayMethod.value && (y = (s - d - n) * (c * Math.pow(1 + c, r - u)) / (Math.pow(1 + c, r - u) - 1), g = h * r - m - w - y * (r - u), x = v);
                    "all" !== o.value && "" == _ || (w = (s - d) * (1 + c), y = 0, g = h * r - m - w, x = a.advYear.value + "年" + a.advMonth.value + "月");
                    var M = this.result, b = Math.round(100 * h) / 100, A = Math.round(100 * m) / 100,
                        T = Math.round(100 * p) / 100, I = Math.round(100 * w) / 100, R = Math.round(100 * y) / 100,
                        k = Math.round(100 * g) / 100;
                    if (M.resultNum = b > 1e4 ? (b / 1e4).toFixed(2) + "万" : b, M.resultNum = b > 1e8 ? (b / 1e8).toFixed(2) + "亿" : b, M.resultDate = v, M.resultAlready = l.formatNumber(A), M.resultAlreadyInterest = l.formatNumber(T), M.resultOnce = l.formatNumber(I), M.resultNextMonth = l.formatNumber(R), M.resultSave = l.formatNumber(k), M.resultDateNew = x, M.resultTip = _, this.calDone = !1, this.userActionOptions.params = {
                        loanamount: this.selected.repayNum + "万",
                        loantime: this.selected.repayTime.value + "月",
                        repaymenttime: this.selected.firstYear.value + "年" + this.selected.firstMonth.value + "月",
                        repaymenttimeadvance: this.selected.advYear.value + "年" + this.selected.advMonth.value + "月",
                        repaymentmethod: "all" === this.selected.repayMode.value ? "一次还清" : "部分还清",
                        xtfh_yuegong: M.resultNum,
                        xtfh_totalrepayment: M.resultAlready,
                        xtfh_interestpaid: M.resultAlreadyInterest
                    }, "all" !== this.selected.repayMode.value) {
                        var P = "";
                        P = "short" === this.selected.repayMethod ? "缩短还款期限，月还款额基本不变" : "减少月还款额，还款期限不变", this.userActionOptions.params.treatmentmethod = encodeURIComponent(P)
                    }
                    0 === a.repayType.value ? (this.userActionOptions.type = 146, this.userActionOptions.params.annualinterestrate = (100 * c * 12).toFixed(3) + "%") : (this.userActionOptions.type = 147, this.userActionOptions.params.loanrateforgjj = (100 * c * 12).toFixed(3) + "%"), (0, i.yhxw)(this.userActionOptions), setTimeout(function () {
                        t.calDone = !0
                    }, 0)
                }, getlilv: function (t, e, a) {
                    var s = this.getArrayIndexFromYear(a, 1), i = this.getArrayIndexFromYear(a, 2);
                    return t = parseInt(t, 10), 1 == e ? n[t][e][s] : 2 == e ? n[t][e][i] : void 0
                }, getArrayIndexFromYear: function (t, e) {
                    var a = 0;
                    return 1 == e ? a = 1 == t ? 1 : t > 1 && t <= 3 ? 3 : t > 3 && t <= 5 ? 5 : 10 : 2 == e && (a = t > 5 ? 10 : 5), a
                }, empty: function () {
                    this.selected;
                    this.selected.repayType = u[0], this.selected.repayNum = "", this.selected.repayTime = h[14], this.selected.rateType = c[8], this.selected.firstYear = year[0], this.selected.firstMonth = d[(new Date).getMonth()], this.selected.advYear = year[0], this.selected.advMonth = d[(new Date).getMonth()], this.selected.repayMode = {value: "all"}, this.selected.repayMethod = {value: "short"}, this.selected.partInput = ""
                }
            }
        }
    }, 184: function (t, e, a) {
        "use strict";

        function s(t) {
            return t && t.__esModule ? t : {default: t}
        }

        Object.defineProperty(e, "__esModule", {value: !0});
        var i = a(101), o = s(i), n = a(107), r = a(57), l = s(r), c = a(45), u = a(51), h = new n.Calculate;
        e.default = {
            components: {SelectComponent: o.default, Toast: l.default}, name: "fangdai", data: function () {
                return {
                    items: [{text: "个人装修贷款"}, {text: "个人购车贷款"}],
                    items2: [{text: "等额本金", val: 2, cla: "on"}, {text: "等额本息", val: 1, cla: ""}],
                    type: "个人装修贷款",
                    showType: !1,
                    chargeTax: "",
                    payTax: "",
                    totalMoney: "",
                    rate: "",
                    showResult: !1,
                    calculateMsg: {type: 2, totalMoney: "", month: "", rate: ""},
                    firstMoney: "",
                    djMoney: "",
                    totalLx: "",
                    otherTax: "",
                    userActionOptions: {type: 0, pageId: "jsq_fd^zxdk_pc"}
                }
            }, computed: {
                showDj: function () {
                    return 2 === this.calculateMsg.type
                }
            }, methods: {
                clickType: function (t) {
                    this.showType = !this.showType, t.stopPropagation()
                }, selectType: function (t) {
                    this.type = t.text, this.showType = !1, this.showResult = !1, this.clearAll()
                }, limitVal: function (t, e) {
                    return e.match(t)[0]
                }, infoIpt1: function (t) {
                    this.totalMoney = this.limitVal(/\d{0,6}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt2: function (t) {
                    this.calculateMsg.month = this.limitVal(/\d{0,3}/, t.target.value)
                }, infoIpt3: function (t) {
                    this.rate = this.limitVal(/\d{0,2}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt4: function (t) {
                    this.chargeTax = this.limitVal(/\d{0,2}(\.\d{0,2})?/g, t.target.value)
                }, infoIpt5: function (t) {
                    this.payTax = this.limitVal(/\d{0,2}(\.\d{0,2})?/g, t.target.value)
                }, clearAll: function () {
                    this.rate = "", this.totalMoney = "", this.calculateMsg.month = "", this.chargeTax = "", this.payTax = "", this.showResult = !1
                }, calculate: function () {
                    var t = this;
                    this.calculateMsg.totalMoney = 1e4 * this.totalMoney, this.calculateMsg.rate = this.rate / 100;
                    var e = h.calculate(this.calculateMsg);
                    this.djMoney = u.formatNumber(e.djMoney, 2) || 0, this.firstMoney = u.formatNumber(e.firstMonYg, 2), this.totalLx = u.formatNumber(e.toatlInterest, 2), this.otherTax = u.formatNumber((this.totalMoney * this.payTax).toFixed(2) || 0, 2), this.showResult = !1, this.userActionOptions.type = 29, this.userActionOptions.params = {
                        xtfh_othermoney: this.otherTax,
                        xtfh_monthlyrepayment: this.firstMoney,
                        loantype: this.type,
                        loanamount: this.totalMoney,
                        loantime: this.calculateMsg.month + "个月",
                        annualinterestrate: parseFloat(100 * this.calculateMsg.rate).toFixed(1) + "%",
                        monthmanagefee: parseFloat(this.chargeTax).toFixed(1) + "%",
                        onecharge: parseFloat(this.payTax).toFixed(1) + "%",
                        repaymentmethod: 1 === this.calculateMsg.type ? "等额本息" : "等额本金",
                        xtfh_totalinterest: this.totalLx
                    }, (0, c.yhxw)(this.userActionOptions), setTimeout(function () {
                        t.showResult = !0
                    }, 0)
                }, startCalculate: function () {
                    return this.totalMoney ? "0" === this.totalMoney ? (this.$toast.show("贷款金额不能为0，请输入！"), !1) : this.rate ? "0" === this.rate ? (this.$toast.show("贷款利率不能为0，请输入！"), !1) : this.calculateMsg.month ? "0" === this.calculateMsg.month ? (this.$toast.show("贷款期限不能为0，请输入！"), !1) : void this.calculate() : (this.$toast.show("贷款期限不能为空，请输入！"), !1) : (this.$toast.show("贷款利率不能为空，请输入！"), !1) : (this.$toast.show("贷款金额不能为空，请输入！"), !1)
                }, selectMsg: function (t) {
                    this.calculateMsg.type = t.val, this.showResult = !1
                }
            }, mounted: function () {
                var t = this;
                (0, c.yhxw)(this.userActionOptions);
                for (var e = ["showType"], a = 0; a < e.length; a++) !function (e) {
                    window.addEventListener("click", function () {
                        if (!t[e]) return !1;
                        t[e] = !1
                    })
                }(e[a])
            }
        }
    }, 185: function (t, e, a) {
        "use strict";
        var s = [];
        s[1] = [], s[1][1] = [], s[1][2] = [], s[1][1][1] = .0631, s[1][1][3] = .064, s[1][1][5] = .0665, s[1][1][10] = .068, s[1][2][5] = .042, s[1][2][10] = .047, s[2] = [], s[2][1] = [], s[2][2] = [], s[2][1][1] = .04417, s[2][1][3] = .0448, s[2][1][5] = .04655, s[2][1][10] = .0476, s[2][2][5] = .042, s[2][2][10] = .047, s[3] = [], s[3][1] = [], s[3][2] = [], s[3][1][1] = .053635, s[3][1][3] = .0544, s[3][1][5] = .056525, s[3][1][10] = .0578, s[3][2][5] = .042, s[3][2][10] = .047, s[4] = [], s[4][1] = [], s[4][2] = [], s[4][1][1] = .06941, s[4][1][3] = .0704, s[4][1][5] = .07315, s[4][1][10] = .0748, s[4][2][5] = .042, s[4][2][10] = .047, s[5] = [], s[5][1] = [], s[5][2] = [], s[5][1][1] = .06, s[5][1][3] = .0615, s[5][1][5] = .064, s[5][1][10] = .0655, s[5][2][5] = .04, s[5][2][10] = .045, s[6] = [], s[6][1] = [], s[6][2] = [], s[6][1][1] = .042, s[6][1][3] = .04305, s[6][1][5] = .0448, s[6][1][10] = .04585, s[6][2][5] = .04, s[6][2][10] = .045, s[7] = [], s[7][1] = [], s[7][2] = [], s[7][1][1] = .051, s[7][1][3] = .052275, s[7][1][5] = .0544, s[7][1][10] = .055675, s[7][2][5] = .04, s[7][2][10] = .045, s[8] = [], s[8][1] = [], s[8][2] = [], s[8][1][1] = .066, s[8][1][3] = .06765, s[8][1][5] = .0704, s[8][1][10] = .07205, s[8][2][5] = .04, s[8][2][10] = .045, s[9] = [], s[9][1] = [], s[9][2] = [], s[9][1][1] = .06, s[9][1][3] = .06, s[9][1][5] = .06, s[9][1][10] = .0615, s[9][2][5] = .0375, s[9][2][10] = .0425, s[10] = [], s[10][1] = [], s[10][2] = [], s[10][1][1] = .042, s[10][1][3] = .042, s[10][1][5] = .042, s[10][1][10] = .04305, s[10][2][5] = .02625, s[10][2][10] = .02975, s[11] = [], s[11][1] = [], s[11][2] = [], s[11][1][1] = .051, s[11][1][3] = .051, s[11][1][5] = .051, s[11][1][10] = .052275, s[11][2][5] = .031875, s[11][2][10] = .036125, s[12] = [], s[12][1] = [],s[12][2] = [],s[12][1][1] = .066,s[12][1][3] = .066,s[12][1][5] = .066,s[12][1][10] = .06765,s[12][2][5] = .04125,s[12][2][10] = .04675,s[13] = [],s[13][1] = [],s[13][2] = [],s[13][1][1] = .0535,s[13][1][3] = .0575,s[13][1][5] = .0575,s[13][1][10] = .059,s[13][2][5] = .035,s[13][2][10] = .04,s[14] = [],s[14][1] = [],s[14][2] = [],s[14][1][1] = .03745,s[14][1][3] = .04025,s[14][1][5] = .04025,s[14][1][10] = .0413,s[14][2][5] = .0245,s[14][2][10] = .028,s[15] = [],s[15][1] = [],s[15][2] = [],s[15][1][1] = .045475,s[15][1][3] = .048875,s[15][1][5] = .048875,s[15][1][10] = .05015,s[15][2][5] = .02975,s[15][2][10] = .034,s[16] = [],s[16][1] = [],s[16][2] = [],s[16][1][1] = .05885,s[16][1][3] = .06325,s[16][1][5] = .06325,s[16][1][10] = .0649,s[16][2][5] = .0385,s[16][2][10] = .044,s[17] = [],s[17][1] = [],s[17][2] = [],s[17][1][1] = .051,s[17][1][3] = .055,s[17][1][5] = .055,s[17][1][10] = .0565,s[17][2][5] = .0325,s[17][2][10] = .0375,s[18] = [],s[18][1] = [],s[18][2] = [],s[18][1][1] = .0357,s[18][1][3] = .0385,s[18][1][5] = .0385,s[18][1][10] = .03955,s[18][2][5] = .02275,s[18][2][10] = .02625,s[19] = [],s[19][1] = [],s[19][2] = [],s[19][1][1] = .04335,s[19][1][3] = .04675,s[19][1][5] = .04675,s[19][1][10] = .048025,s[19][2][5] = .027625,s[19][2][10] = .031875,s[20] = [],s[20][1] = [],s[20][2] = [],s[20][1][1] = .0561,s[20][1][3] = .0605,s[20][1][5] = .0605,s[20][1][10] = .06215,s[20][2][5] = .03575,s[20][2][10] = .04125,s[21] = [],s[21][1] = [],s[21][2] = [],s[21][1][1] = .0485,s[21][1][3] = .0525,s[21][1][5] = .0525,s[21][1][10] = .054,s[21][2][5] = .03,s[21][2][10] = .035,s[22] = [],s[22][1] = [],s[22][2] = [],s[22][1][1] = .03395,s[22][1][3] = .03675,s[22][1][5] = .03675,s[22][1][10] = .0378,s[22][2][5] = .021,s[22][2][10] = .0245,s[23] = [],s[23][1] = [];
        s[23][2] = [], s[23][1][1] = .041225, s[23][1][3] = .044625, s[23][1][5] = .044625, s[23][1][10] = .0459, s[23][2][5] = .0255, s[23][2][10] = .02975, s[24] = [], s[24][1] = [], s[24][2] = [], s[24][1][1] = .05335, s[24][1][3] = .05775, s[24][1][5] = .05775, s[24][1][10] = .0594, s[24][2][5] = .033, s[24][2][10] = .0385, s[25] = [], s[25][1] = [], s[25][2] = [], s[25][1][1] = .046, s[25][1][3] = .05, s[25][1][5] = .05, s[25][1][10] = .0515, s[25][2][5] = .0275, s[25][2][10] = .0325, s[26] = [], s[26][1] = [], s[26][2] = [], s[26][1][1] = .0322, s[26][1][3] = .035, s[26][1][5] = .035, s[26][1][10] = .03605, s[26][2][5] = .01925, s[26][2][10] = .02275, s[27] = [], s[27][1] = [], s[27][2] = [], s[27][1][1] = .0391, s[27][1][3] = .0425, s[27][1][5] = .0425, s[27][1][10] = .043775, s[27][2][5] = .023375, s[27][2][10] = .027625, s[28] = [], s[28][1] = [], s[28][2] = [], s[28][1][1] = .0506, s[28][1][3] = .055, s[28][1][5] = .055, s[28][1][10] = .05665, s[28][2][5] = .03025, s[28][2][10] = .03575, s[29] = [], s[29][1] = [], s[29][2] = [], s[29][1][1] = .0435, s[29][1][3] = .0475, s[29][1][5] = .0475, s[29][1][10] = .049, s[29][2][5] = .0275, s[29][2][10] = .0325, s[30] = [], s[30][1] = [], s[30][2] = [], s[30][1][1] = .03045, s[30][1][3] = .03325, s[30][1][5] = .03325, s[30][1][10] = .0343, s[30][2][5] = .01925, s[30][2][10] = .02275, s[31] = [], s[31][1] = [], s[31][2] = [], s[31][1][1] = .036975, s[31][1][3] = .040375, s[31][1][5] = .040375, s[31][1][10] = .04165, s[31][2][5] = .023375, s[31][2][10] = .027625, s[32] = [], s[32][1] = [], s[32][2] = [], s[32][1][1] = .03828, s[32][1][3] = .0418, s[32][1][5] = .0418, s[32][1][10] = .04312, s[32][2][5] = .0242, s[32][2][10] = .0286, s[33] = [], s[33][1] = [], s[33][2] = [], s[33][1][1] = .03915, s[33][1][3] = .04275, s[33][1][5] = .04275, s[33][1][10] = .0441, s[33][2][5] = .02475, s[33][2][10] = .02925, s[34] = [], s[34][1] = [], s[34][2] = [], s[34][1][1] = .041325,s[34][1][3] = .045125,s[34][1][5] = .045125,s[34][1][10] = .04655,s[34][2][5] = .026125,s[34][2][10] = .030875,s[35] = [],s[35][1] = [],s[35][2] = [],s[35][1][1] = .045675,s[35][1][3] = .049875,s[35][1][5] = .049875,s[35][1][10] = .05145,s[35][2][5] = .028875,s[35][2][10] = .034125,s[36] = [],s[36][1] = [],s[36][2] = [],s[36][1][1] = .04785,s[36][1][3] = .05225,s[36][1][5] = .05225,s[36][1][10] = .0539,s[36][2][5] = .03025,s[36][2][10] = .03575,s[37] = [],s[37][1] = [],s[37][2] = [],s[37][1][1] = .0522,s[37][1][3] = .057,s[37][1][5] = .057,s[37][1][10] = .0588,s[37][2][5] = .033,s[37][2][10] = .039,s[38] = [],s[38][1] = [],s[38][2] = [],s[38][1][1] = .05655,s[38][1][3] = .06175,s[38][1][5] = .06175,s[38][1][10] = .0637,s[38][2][5] = .03575,s[38][2][10] = .04225,t.exports = s
    }, 186: function (t, e, a) {
        "use strict";

        function s(t) {
            return t && t.__esModule ? t : {default: t}
        }

        Object.defineProperty(e, "__esModule", {value: !0});
        var i = a(147), o = s(i), n = a(440), r = s(n), l = a(415), c = s(l), u = a(419), h = s(u), v = a(421),
            m = s(v), p = a(417), d = s(p), f = a(416), _ = s(f), w = a(422), y = s(w);
        o.default.use(r.default);
        var g = history.pushState && /test\.fang\.com\:8080|ditu\.(test\.)*fang\.com|newhouse\.([a-z\.]*)fang\.com/.test(location.host),
            x = /\/tools\/([a-zA-Z0-9]+)\/([a-z]+)/.exec(window.location.href), j = [];
        g || j.push({
            path: "/", redirect: function (t) {
                var e, a = t.location.href, s = a.match(/\/(jsq|house)\/([a-zA-Z]+)\.htm/);
                return s && s[0] ? e = s[0] : (s = a.match(/\/tools\/([a-zA-Z0-9]+)\/([a-z]+)/), e = s && s[0] ? s[0] : "/tools/bj/fangdai"), e
            }(window)
        }), j.push({
            path: x && x[0] ? x[0] : "/jsq/fd/" +
                "",
            name: "fangdai",
            component: c.default
        }), j.push({path: "/jsq/sf/", name: "shuifei", component: h.default}), j.push({
            path: "/jsq/tq/",
            name: "tqhd",
            component: m.default
        }), j.push({path: "/jsq/gjj/", name: "gjj", component: d.default}), j.push({
            path: "/jsq/pg/",
            name: "gfnl",
            component: _.default
        }), j.push({path: "/jsq/zxdk/", name: "zhuangxiu", component: y.default});
        var C = new r.default({
            scrollBehavior: function (t, e, a) {
                return a || {x: 0, y: 0}
            }, mode: "history", routes: j, fallback: false,
        }), M = {
            fangdai: "房贷计算器2020最新在线 -公积金商业贷组合贷贷款计算器在线计算 - 齐装网",
            shuifei: "税费计算器2020 - 二手房税费计算器 - 新房税费计算器 - 齐装网",
            tqhd: "利息计算器 - 提前还贷计算器最新2020 - 齐装网",
            gjj: "住房公积金贷款计算器在线2020 - 公积金贷款额度查询 - 齐装网",
            gfnl: "购房贷款计算器 - 购房资格能力评估在线2020 - 齐装网",
            zhuangxiu: "装修贷款计算器在线2020 - 齐装网"
        }, D = {
            fangdai: "计算房贷首选房贷计算器2020，使用2020房贷计算器在线计算房贷，包括房贷计算器、贷款计算器、公积金贷款计算器、商业贷款计算器、组合贷款计算器、等额本金贷款、等额本息贷款，房贷计算器，一键帮您计算",
            shuifei: "税费计算器2020、二手房税费计算器、新房税费计算器大全",
            tqhd: "计算房贷税费首选税费计算器2020，包括新房税费计算器、二手房税费计算器，税费计算就用2020最新贷款计算器",
            gjj: "计算公积金贷款首选公积金贷款计算器2020，包括公积金贷款额度、公积金贷款比例，公积金贷款就用2020最新公积金贷款计算器",
            gfnl: "购房资格、购房能力评估，买房你够格了吗",
            zhuangxiu: "装修贷款计算器，测测装修你可以贷多少钱"
        }, K = {
            fangdai: "房贷计算器2020、贷款计算器、公积金贷款计算器、商业贷款计算器、组合贷款计算器",
            shuifei: "税费计算器2020、二手房税费计算器、新房税费计算器",
            tqhd: "房贷利息计算、提前还贷计算器、提前还贷计算器2020",
            gjj: "公积金贷款、公积金贷款计算器2020、公积金贷款额度",
            gfnl: "购房资格、购房能力评估、购房贷款计算器",
            zhuangxiu: "装修贷款计算器"
        };
        C.beforeEach(function (t, e, a) {
            var s = t.name;
            document.title = M[s]
            $('meta[name = description]').attr('content', D[s])
            $('meta[name = keywords]').attr('content', K[s])
            a()
        }), e.default = C
    }, 387: function (t, e) {
    }, 388: function (t, e) {
    }, 389: function (t, e) {
    }, 390: function (t, e) {
    }, 391: function (t, e) {
    }, 392: function (t, e) {
    }, 393: function (t, e) {
    }, 394: function (t, e) {
    }, 395: function (t, e) {
    }, 396: function (t, e) {
    }, 397: function (t, e) {
    }, 398: function (t, e) {
    }, 399: function (t, e) {
    }, 400: function (t, e) {
    }, 401: function (t, e) {
    }, 402: function (t, e) {
    }, 403: function (t, e) {
    }, 404: function (t, e) {
    }, 409: function (t, e, a) {
        function s(t) {
            a(403)
        }

        var i = a(10)(a(168), a(438), s, null, null);
        t.exports = i.exports
    }, 410: function (t, e, a) {
        function s(t) {
            a(394)
        }

        var i = a(10)(a(169), a(429), s, null, null);
        t.exports = i.exports
    }, 411: function (t, e, a) {
        function s(t) {
            a(392)
        }

        var i = a(10)(a(170), a(427), s, null, null);
        t.exports = i.exports
    }, 412: function (t, e, a) {
        function s(t) {
            a(399)
        }

        var i = a(10)(a(172), a(434), s, "data-v-6b68ca70", null);
        t.exports = i.exports
    }, 413: function (t, e, a) {
        function s(t) {
            a(402)
        }

        var i = a(10)(a(174), a(437), s, null, null);
        t.exports = i.exports
    }, 414: function (t, e, a) {
        function s(t) {
            a(400)
        }

        var i = a(10)(a(175), a(435), s, null, null);
        t.exports = i.exports
    }, 415: function (t, e, a) {
        function s(t) {
            a(401)
        }

        var i = a(10)(a(177), a(436), s, null, null);
        t.exports = i.exports
    }, 416: function (t, e, a) {
        function s(t) {
            a(391)
        }

        var i = a(10)(a(178), a(426), s, null, null);
        t.exports = i.exports
    }, 417: function (t, e, a) {
        function s(t) {
            a(389)
        }

        var i = a(10)(a(179), a(424), s, null, null);
        t.exports = i.exports
    }, 418: function (t, e, a) {
        function s(t) {
            a(395)
        }

        var i = a(10)(a(180), a(430), s, null, null);
        t.exports = i.exports
    }, 419: function (t, e, a) {
        function s(t) {
            a(388)
        }

        var i = a(10)(a(181), a(423), s, null, null);
        t.exports = i.exports
    }, 420: function (t, e, a) {
        function s(t) {
            a(404)
        }

        var i = a(10)(a(182), a(439), s, null, null);
        t.exports = i.exports
    }, 421: function (t, e, a) {
        function s(t) {
            a(390)
        }

        var i = a(10)(a(183), a(425), s, "data-v-2488403e", null);
        t.exports = i.exports
    }, 422: function (t, e, a) {
        function s(t) {
            a(393)
        }

        var i = a(10)(a(184), a(428), s, null, null);
        t.exports = i.exports
    }, 423: function (t, e, a) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("table", [a("tr", [a("td", {
                    staticClass: "counter_list counter_list_width3 xfStyle",
                    attrs: {valign: "top"}
                }, [a("ul", {staticClass: "clearfix fl"}, [a("select-component", {
                    attrs: {title: "房屋", items: t.items1},
                    on: {"select-msg": t.selectMsg}
                }), t._v(" "), a("li", {}, [a("span", [t._v("房屋面积：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.buildArea,
                        expression: "buildArea"
                    }],
                    attrs: {type: "text", maxlength: "7", pattern: "[0-9]*", placeholder: "请输入房屋面积", name: ""},
                    domProps: {value: t.buildArea},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.buildArea = e.target.value)
                        }, t.infoIpt1]
                    }
                }), t._v(" "), a("i", [t._v("平米")])]), t._v(" "), a("li", {}, [a("span", [t._v("房屋单价：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.unitPrice,
                        expression: "unitPrice"
                    }],
                    attrs: {type: "text", maxlength: "6", pattern: "[0-9]*", placeholder: "请输入房屋单价", name: ""},
                    domProps: {value: t.unitPrice},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.unitPrice = e.target.value)
                        }, t.infoIpt2]
                    }
                }), t._v(" "), a("i", [t._v("元/平米")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showEsf,
                        expression: "showEsf"
                    }]
                }, [a("span", [t._v("房屋总价：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.totalMoney,
                        expression: "totalMoney"
                    }],
                    attrs: {type: "text", maxlength: "6", pattern: "[0-9]*", placeholder: "请输入房屋总价", name: ""},
                    domProps: {value: t.totalMoney},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.totalMoney = e.target.value)
                        }, t.infoIpt3]
                    }
                }), t._v(" "), a("i", [t._v("万元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showYj,
                        expression: "showYj"
                    }]
                }, [a("span", [t._v("房屋原价：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.price2,
                        expression: "price2"
                    }],
                    attrs: {type: "text", maxlength: "6", pattern: "[0-9]*", placeholder: "请输入房屋原价", name: ""},
                    domProps: {value: t.price2},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.price2 = e.target.value)
                        }, t.infoIpt4]
                    }
                }), t._v(" "), a("i", [t._v("万元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showEsf,
                        expression: "showEsf"
                    }]
                }, [a("span", [t._v("计征方式：")]), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.jzTypeCon,
                        expression: "jzTypeCon"
                    }],
                    attrs: {type: "", name: "", readonly: "readonly"},
                    domProps: {value: t.jzTypeCon},
                    on: {
                        click: t.clickJz, input: function (e) {
                            e.target.composing || (t.jzTypeCon = e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("jz-type", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showJz,
                        expression: "showJz"
                    }], on: {"jztype-msg": t.jzMsg}
                })], 1)]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showEsf,
                        expression: "showEsf"
                    }]
                }, [a("span", [t._v("房产性质：")]), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.buildTypeCon,
                        expression: "buildTypeCon"
                    }],
                    attrs: {type: "", name: "", readonly: "readonly"},
                    domProps: {value: t.buildTypeCon},
                    on: {
                        click: t.clickFw, input: function (e) {
                            e.target.composing || (t.buildTypeCon = e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("fwxz-component", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showFw,
                        expression: "showFw"
                    }], on: {"fw-msg": t.fwMsg}
                })], 1)]), t._v(" "), a("select-component", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showEsf,
                        expression: "showEsf"
                    }], attrs: {title: "房产购置满5年", items: t.items2}, on: {"select-msg": t.fiveMsg}
                }), t._v(" "), a("select-component", {
                    attrs: {title: "买房家庭首次购房", items: t.items3},
                    on: {"select-msg": t.firstMsg}
                }), t._v(" "), a("select-component", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showEsf,
                        expression: "!showEsf"
                    }], attrs: {title: "有无电梯", items: t.items5}, on: {"select-msg": t.elevatorMsg}
                }), t._v(" "), a("select-component", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showEsf,
                        expression: "showEsf"
                    }], attrs: {title: "卖方家庭唯一住房", items: t.items4}, on: {"select-msg": t.onlyMsg}
                }), t._v(" "), a("li", [a("span"), t._v(" "), a("span", {
                    staticClass: "start",
                    on: {click: t.getTax}
                }, [t._v("开始计算")]), t._v(" "), a("span", {
                    staticClass: "empty",
                    on: {click: t.clearSelect}
                }, [t._v("清空重填")])])], 1)]), t._v(" "), t._m(0), t._v(" "), a("td", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showResult,
                        expression: "showResult"
                    }],
                    staticClass: "counter_right counter_right_width3",
                    class: {fadeInRight: t.showResult},
                    attrs: {valign: "top"}
                }, [a("ul", [t._m(1), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showEsf,
                        expression: "!showEsf"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("房款总款：")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.resultTotal))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showEsf,
                        expression: "!showEsf"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("工本费：")]), t._v(" "), a("span", [t._v(t._s(t.gbTax))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showEsf,
                        expression: "showEsf"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("工本印花税：")]), t._v(" "), a("span", [t._v(t._s(t.gbyhTax))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showEsf,
                        expression: "showEsf"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("印花税：")]), t._v(" "), a("span", [t._v(t._s(t.yhTax))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("契税：")]), t._v(" "), a("span", [t._v(t._s(t.qTax))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showEsf,
                        expression: "showEsf"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("个人所得税：")]), t._v(" "), a("span", [t._v(t._s(t.gsTax))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showEsf,
                        expression: "showEsf"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("增值税：")]), t._v(" "), a("span", [t._v(t._s(t.zzTax))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showEsf,
                        expression: "showEsf"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("综合地价款：")]), t._v(" "), a("span", [t._v(t._s(t.zhdjTax))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showEsf,
                        expression: "!showEsf"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("权属登记费：")]), t._v(" "), a("span", [t._v(t._s(t.qsdjTax))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showEsf,
                        expression: "!showEsf"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("维修基金：")]), t._v(" "), a("span", [t._v(t._s(t.wxjjTax))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("合计：")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.totalTax))]), t._v(" "), a("i", [t._v("元")])])]), t._v(" "), a("p", {staticClass: "note"}, [t._v("*以上结果仅供参考")])]), t._v(" "), a("td", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showResult,
                        expression: "!showResult"
                    }], staticClass: "counter_right2"
                }, [a("img", {
                    attrs: {
                        src: "/assets/home/jsq/jisuan_right.9ebbde4.png",
                        alt: ""
                    }
                }), t._v(" "), a("p", [t._v(t._s(t.tipCon))])])])])
            }, staticRenderFns: [function () {
                var t = this, e = t.$createElement, s = t._self._c || e;
                return s("td", {staticClass: "counter_center"}, [s("img", {attrs: {src: a(50), alt: ""}})])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("li", {staticClass: "title"}, [a("h2", [t._v("计算结果")])])
            }]
        }
    }, 424: function (t, e, a) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("table", [a("tr", [a("td", {staticClass: "counter_list counter_list_width4"}, [a("ul", {staticClass: "clearfix fl"}, [a("li", {}, [t._m(0), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.gjjJc,
                        expression: "gjjJc"
                    }],
                    attrs: {type: "text", placeholder: "请输入公积金个人月缴存额", name: ""},
                    domProps: {value: t.gjjJc},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.gjjJc = e.target.value)
                        }, t.infoIpt1]
                    }
                }), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {}, [t._m(1), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.jcPoint,
                        expression: "jcPoint"
                    }],
                    attrs: {type: "text", placeholder: "请输入个人缴存比例", name: ""},
                    domProps: {value: t.jcPoint},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.jcPoint = e.target.value)
                        }, t.infoIpt2]
                    }
                }), t._v(" "), a("i", [t._v("%")])]), t._v(" "), a("li", {}, [a("span", [t._v("配偶住房公积金个人月缴存额：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.otherJc,
                        expression: "otherJc"
                    }],
                    attrs: {type: "text", placeholder: "请输入配偶公积金月缴存额", name: ""},
                    domProps: {value: t.otherJc},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.otherJc = e.target.value)
                        }, t.infoIpt3]
                    }
                }), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {}, [a("span", [t._v("缴存比例：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.otherJcPoint,
                        expression: "otherJcPoint"
                    }],
                    attrs: {type: "text", placeholder: "请输入配偶公积金缴存比例", name: ""},
                    domProps: {value: t.otherJcPoint},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.otherJcPoint = e.target.value)
                        }, t.infoIpt4]
                    }
                }), t._v(" "), a("i", [t._v("%")])]), t._v(" "), a("li", {}, [t._m(2), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.buildPrice,
                        expression: "buildPrice"
                    }],
                    attrs: {type: "text", placeholder: "请输入房屋评估价值或实际购房款", name: ""},
                    domProps: {value: t.buildPrice},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.buildPrice = e.target.value)
                        }, t.infoIpt5]
                    }
                }), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("select-component", {
                    attrs: {
                        showImportant: "ok",
                        title: "房屋性质",
                        items: t.items1
                    }, on: {"select-msg": t.fwMsg}
                }), t._v(" "), a("li", {}, [t._m(3), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.dkYear,
                        expression: "dkYear"
                    }],
                    attrs: {type: "text", placeholder: "请输入贷款年限", name: ""},
                    domProps: {value: t.dkYear},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.dkYear = e.target.value)
                        }, t.infoIpt6]
                    }
                }), t._v(" "), a("i", [t._v("年")])]), t._v(" "), a("li", {staticClass: "zhushi"}, [t._v("( 注：贷款期限最长可计算到借款人70周岁，同时不得超过30年。)")]), t._v(" "), a("select-component", {
                    attrs: {
                        showImportant: "ok",
                        title: "个人信用等级",
                        items: t.items2
                    }, on: {"select-msg": t.jbMsg}
                }), t._v(" "), a("li", [a("span"), t._v(" "), a("span", {
                    staticClass: "start",
                    on: {click: t.getResult}
                }, [t._v("开始计算")]), t._v(" "), a("span", {
                    staticClass: "empty",
                    on: {click: t.clearBoth}
                }, [t._v("清空重填")])])], 1)]), t._v(" "), t._m(4), t._v(" "), a("td", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showResult,
                        expression: "showResult"
                    }],
                    staticClass: "counter_right counter_right_width4",
                    class: {fadeInRight: t.showResult},
                    attrs: {valign: "top"}
                }, [a("ul", [t._m(5), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("您可以申请的最高贷款额度是 ")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.totalMoney))]), t._v(" "), a("i", [t._v("万元")])])]), t._v(" "), a("p", {staticClass: "note"}, [t._v("*以上结果仅供参考")])]), t._v(" "), a("td", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showResult,
                        expression: "!showResult"
                    }], staticClass: "counter_right2"
                }, [a("img", {
                    attrs: {
                        src: "/assets/home/jsq/jisuan_right.9ebbde4.png",
                        alt: ""
                    }
                }), t._v(" "), a("p", [t._v("要贷款买房，赶紧算算吧！")])])])])
            }, staticRenderFns: [function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("span", [a("b", [t._v("*")]), t._v("住房公积金个人月缴存额：")])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("span", [a("b", [t._v("*")]), t._v("缴存比例：")])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("span", [a("b", [t._v("*")]), t._v("房屋评估价值或实际购房款：")])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("span", [a("b", [t._v("*")]), t._v("贷款申请年限：")])
            }, function () {
                var t = this, e = t.$createElement, s = t._self._c || e;
                return s("td", {staticClass: "counter_center"}, [s("img", {attrs: {src: a(50), alt: ""}})])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("li", {staticClass: "title"}, [a("h2", [t._v("计算结果")])])
            }]
        }
    }, 425: function (t, e, a) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("table", [a("tr", [a("td", {staticClass: "counter_list counter_list_width3"}, [a("ul", {staticClass: "clearfix fl"}, [a("li", [a("span", [t._v("还款类型：")]), t._v(" "), a("dropSelect", {
                    attrs: {
                        selectItem: t.repayType,
                        name: "repayType",
                        defaultItem: t.selected.repayType
                    }, on: {selectDrop: t.selectType}
                })], 1), t._v(" "), a("li", {}, [a("span", [t._v("原房贷金额：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model.number",
                        value: t.selected.repayNum,
                        expression: "selected.repayNum",
                        modifiers: {number: !0}
                    }],
                    attrs: {type: "text", placeholder: "请输入原房贷金额"},
                    domProps: {value: t.selected.repayNum},
                    on: {
                        input: [function (e) {
                            e.target.composing || t.$set(t.selected, "repayNum", t._n(e.target.value))
                        }, t.yfdIpt], blur: function (e) {
                            return t.$forceUpdate()
                        }
                    }
                }), t._v(" "), a("i", [t._v("万元")])]), t._v(" "), a("li", [a("span", [t._v("原房贷年限：")]), t._v(" "), a("dropSelect", {
                    attrs: {
                        selectItem: t.repayTime,
                        name: "repayTime",
                        defaultItem: t.selected.repayTime
                    }, on: {selectDrop: t.selectType}
                })], 1), t._v(" "), a("li", {}, [a("span", [t._v("利率：")]), t._v(" "), a("dropSelect", {
                    attrs: {
                        selectItem: t.rateType,
                        name: "rateType",
                        defaultItem: t.selected.rateType
                    }, on: {selectDrop: t.selectType}
                })], 1), t._v(" "), a("li", {staticClass: "date_select clearfix"}, [a("span", [t._v("第一次还款时间：")]), t._v(" "), a("dropSelect", {
                    attrs: {
                        selectItem: t.firstYear,
                        klass: "date_right",
                        name: "firstYear",
                        defaultItem: t.selected.firstYear
                    }, on: {selectDrop: t.selectType}
                }), t._v(" "), a("dropSelect", {
                    attrs: {
                        selectItem: t.month,
                        name: "firstMonth",
                        defaultItem: t.selected.firstMonth
                    }, on: {selectDrop: t.selectType}
                })], 1), t._v(" "), a("li", {staticClass: "date_select clearfix"}, [a("span", [t._v("预计提前还款时间：")]), t._v(" "), a("dropSelect", {
                    attrs: {
                        selectItem: t.advYear,
                        klass: "date_right",
                        name: "advYear",
                        defaultItem: t.selected.advYear
                    }, on: {selectDrop: t.selectType}
                }), t._v(" "), a("dropSelect", {
                    attrs: {
                        selectItem: t.month,
                        name: "advMonth",
                        defaultItem: t.selected.advMonth
                    }, on: {selectDrop: t.selectType}
                })], 1), t._v(" "), a("li", {staticClass: "way_block"}, [a("span", [t._v("还款方式：")]), t._v(" "), a("p", {
                    staticClass: "fangshi",
                    on: {
                        click: function (e) {
                            return t.selectType(t.repayMode[0], "repayMode")
                        }
                    }
                }, [a("i", {class: {on: "all" === t.selected.repayMode.value}}), t._v("\n\t\t\t\t\t\t" + t._s(t.repayMode[0].name) + "\n\t\t\t\t\t")]), t._v(" "), a("p", {
                    staticClass: "fangshi",
                    on: {
                        click: function (e) {
                            return t.selectType(t.repayMode[1], "repayMode")
                        }
                    }
                }, [a("i", {class: {on: "part" === t.selected.repayMode.value}}), t._v("\n\t\t\t\t\t\t" + t._s(t.repayMode[1].name)), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model.number",
                        value: t.selected.partInput,
                        expression: "selected.partInput",
                        modifiers: {number: !0}
                    }],
                    attrs: {type: "number", placeholder: "请输入部分提前还款额", maxlength: "10"},
                    domProps: {value: t.selected.partInput},
                    on: {
                        input: function (e) {
                            e.target.composing || t.$set(t.selected, "partInput", t._n(e.target.value))
                        }, blur: function (e) {
                            return t.$forceUpdate()
                        }
                    }
                }), t._v(" "), a("i", {staticClass: "position_wen"}, [t._v("万元")])])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: "part" === t.selected.repayMode.value,
                        expression: "selected.repayMode.value === 'part'"
                    }], staticClass: "way_block"
                }, [a("span", [t._v("处理方式：")]), t._v(" "), a("p", {
                    staticClass: "fangshi", on: {
                        click: function (e) {
                            return t.selectType(t.repayMethod[0], "repayMethod")
                        }
                    }
                }, [a("i", {class: {on: "short" === t.selected.repayMethod.value}}), t._v("\n\t\t\t\t\t\t" + t._s(t.repayMethod[0].name) + "\n\t\t\t\t\t")]), t._v(" "), a("p", {
                    staticClass: "fangshi",
                    on: {
                        click: function (e) {
                            return t.selectType(t.repayMethod[1], "repayMethod")
                        }
                    }
                }, [a("i", {class: {on: "reduce" === t.selected.repayMethod.value}}), t._v("\n\t\t\t\t\t\t" + t._s(t.repayMethod[1].name) + "\n\t\t\t\t\t")])]), t._v(" "), a("li", [a("span"), t._v(" "), a("span", {
                    staticClass: "start",
                    on: {click: t.cal}
                }, [t._v("开始计算")]), t._v(" "), a("span", {
                    staticClass: "empty",
                    on: {click: t.empty}
                }, [t._v("清空重填")])])])]), t._v(" "), t._m(0), t._v(" "), a("td", {
                    staticClass: "counter_right counter_right_width5",
                    class: {none: !t.calDone, fadeInRight: t.calDone},
                    attrs: {valign: "top"}
                }, [a("ul", [t._m(1), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("原月还款额：")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.result.resultNum))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("原最后还款期：")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.result.resultDate))]), t._v(" "), a("i")]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("已还款总额：")]), t._v(" "), a("span", [t._v(t._s(t.result.resultAlready))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("已还利息额：")]), t._v(" "), a("span", [t._v(t._s(t.result.resultAlreadyInterest))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("该月一次还款额：")]), t._v(" "), a("span", [t._v(t._s(t.result.resultOnce))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("下月起月还款额：")]), t._v(" "), a("span", [t._v(t._s(t.result.resultNextMonth))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("节省利息支出：")]), t._v(" "), a("span", [t._v(t._s(t.result.resultSave))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("新的最后还款期：")]), t._v(" "), a("span", [t._v(t._s(t.result.resultDateNew))]), t._v(" "), a("i")]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.hasTip,
                        expression: "hasTip"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("计算结果提示：")]), t._v(" "), a("span", [t._v(t._s(t.result.resultTip))]), t._v(" "), a("i")])]), t._v(" "), a("p", {staticClass: "note"}, [t._v("*以上结果仅供参考")])]), t._v(" "), a("td", {
                    staticClass: "counter_right2",
                    class: {none: t.calDone}
                }, [a("img", {
                    attrs: {
                        src: "/assets/home/jsq/jisuan_right.9ebbde4.png",
                        alt: ""
                    }
                }), t._v(" "), a("p", [t._v("提前还贷款，快来算算吧！")])])])])
            }, staticRenderFns: [function () {
                var t = this, e = t.$createElement, s = t._self._c || e;
                return s("td", {staticClass: "counter_center"}, [s("img", {attrs: {src: a(50), alt: ""}})])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("li", {staticClass: "title"}, [a("h2", [t._v("计算结果")])])
            }]
        }
    }, 426: function (t, e, a) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("table", [a("tr", [a("td", {staticClass: "counter_list counter_list_width2"}, [a("ul", {staticClass: "clearfix fl"}, [a("li", {}, [a("span", [t._v("现持有资金：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.totalMoney,
                        expression: "totalMoney"
                    }],
                    attrs: {type: "text", placeholder: "请输入现持有资金", name: ""},
                    domProps: {value: t.totalMoney},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.totalMoney = e.target.value)
                        }, t.infoIpt1]
                    }
                }), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "zhushi"}, [t._v("（包括现金、存款、有价证券和可以筹措到的资金总和） ")]), t._v(" "), a("li", {}, [a("span", [t._v("现家庭月收入：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.monthIncome,
                        expression: "monthIncome"
                    }],
                    attrs: {type: "text", placeholder: "请输入家庭月收入", name: ""},
                    domProps: {value: t.monthIncome},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.monthIncome = e.target.value)
                        }, t.infoIpt2]
                    }
                }), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {}, [a("span", [t._v("家庭平均每月固定支出：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.monthPay,
                        expression: "monthPay"
                    }],
                    attrs: {type: "text", placeholder: "请输入家庭每月固定支出", name: ""},
                    domProps: {value: t.monthPay},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.monthPay = e.target.value)
                        }, t.infoIpt3]
                    }
                }), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", [a("span", [t._v("您期望偿还贷款的年限：")]), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.yearCon,
                        expression: "yearCon"
                    }],
                    attrs: {type: "", name: "", readonly: "readonly"},
                    domProps: {value: t.yearCon},
                    on: {
                        click: t.clickYear, input: function (e) {
                            e.target.composing || (t.yearCon = e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("aj-year", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showYear,
                        expression: "showYear"
                    }], on: {"year-msg": t.yearMsg}
                })], 1)]), t._v(" "), a("li", {}, [a("span", [t._v("您计划购买房屋的面积：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.expectArea,
                        expression: "expectArea"
                    }],
                    attrs: {type: "text", placeholder: "请输入您计划购买的房屋面积", name: ""},
                    domProps: {value: t.expectArea},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.expectArea = e.target.value)
                        }, t.infoIpt4]
                    }
                }), t._v(" "), a("i", [t._v("平方米")])]), t._v(" "), a("li", [a("span"), t._v(" "), a("span", {
                    staticClass: "start",
                    on: {click: t.startCalculate}
                }, [t._v("开始计算")]), t._v(" "), a("span", {
                    staticClass: "empty",
                    on: {click: t.clearAll}
                }, [t._v("清空重填")])])])]), t._v(" "), t._m(0), t._v(" "), a("transition", {attrs: {"enter-active-class": "fadeInRight"}}, [t.showResult && !t.showUnable ? a("td", {
                    staticClass: "counter_right counter_right_width2",
                    attrs: {valign: "top"}
                }, [a("ul", [a("li", {staticClass: "title"}, [a("h2", [t._v("计算结果")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("您可以购买的房屋总价为 ")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.totalPrice))]), t._v(" "), a("i", [t._v("万元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("您可以购买的房屋单价为 ")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.unitPrice))]), t._v(" "), a("i", [t._v("元/平米")])])]), t._v(" "), a("p", {staticClass: "note"}, [t._v("*以上结果仅供参考")])]) : t._e()]), t._v(" "), a("td", {
                    staticClass: "counter_right2",
                    class: {none: t.showResult}
                }, [a("img", {
                    attrs: {
                        src: "/assets/home/jsq/jisuan_right.9ebbde4.png",
                        alt: ""
                    }
                }), t._v(" "), a("p", [t._v("要贷款买房，赶紧算算吧！")])]), t._v(" "), a("td", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showUnable,
                        expression: "showUnable"
                    }], staticClass: "counter_right2"
                }, [a("p", [t._v("根据计算，您的购房能力暂时较弱，可以考虑减少开支或是增加收入！")])])], 1)])
            }, staticRenderFns: [function () {
                var t = this, e = t.$createElement, s = t._self._c || e;
                return s("td", {staticClass: "counter_center"}, [s("img", {attrs: {src: a(50), alt: ""}})])
            }]
        }
    }, 427: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("ul", {
                    staticClass: "option",
                    staticStyle: {height: "150px", "overflow-x": "hidden", "overflow-y": "auto"}
                }, t._l(t.items, function (e) {
                    return a("li", {
                        on: {
                            click: function (a) {
                                return t.fill(e)
                            }
                        }
                    }, [t._v(t._s(e.calculatename))])
                }), 0)
            }, staticRenderFns: []
        }
    }, 428: function (t, e, a) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("table", [a("tr", [a("td", {staticClass: "counter_list counter_list_width7"}, [a("ul", {staticClass: "clearfix fl"}, [a("li", [t._m(0), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.type,
                        expression: "type"
                    }],
                    attrs: {name: "", readonly: "readonly"},
                    domProps: {value: t.type},
                    on: {
                        click: t.clickType, input: function (e) {
                            e.target.composing || (t.type = e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("ul", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showType,
                        expression: "showType"
                    }], staticClass: "option"
                }, t._l(t.items, function (e) {
                    return a("li", {
                        on: {
                            click: function (a) {
                                return t.selectType(e)
                            }
                        }
                    }, [t._v(t._s(e.text))])
                }), 0)])]), t._v(" "), a("li", {}, [t._m(1), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.totalMoney,
                        expression: "totalMoney"
                    }],
                    attrs: {type: "text", placeholder: "请输入贷款金额", name: ""},
                    domProps: {value: t.totalMoney},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.totalMoney = e.target.value)
                        }, t.infoIpt1]
                    }
                }), t._v(" "), a("i", [t._v("万元")])]), t._v(" "), a("li", {}, [t._m(2), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.calculateMsg.month,
                        expression: "calculateMsg.month"
                    }],
                    attrs: {type: "text", placeholder: "请输入贷款期限", name: ""},
                    domProps: {value: t.calculateMsg.month},
                    on: {
                        input: [function (e) {
                            e.target.composing || t.$set(t.calculateMsg, "month", e.target.value)
                        }, t.infoIpt2]
                    }
                }), t._v(" "), a("i", [t._v("个月")])]), t._v(" "), a("li", {}, [t._m(3), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.rate,
                        expression: "rate"
                    }],
                    attrs: {type: "text", placeholder: "请输入年利率", name: ""},
                    domProps: {value: t.rate},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.rate = e.target.value)
                        }, t.infoIpt3]
                    }
                }), t._v(" "), a("i", [t._v("%")])]), t._v(" "), a("li", {}, [a("span", [t._v("月管理费：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.chargeTax,
                        expression: "chargeTax"
                    }],
                    attrs: {type: "text", placeholder: "请输入月管理费比例（选填）", name: ""},
                    domProps: {value: t.chargeTax},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.chargeTax = e.target.value)
                        }, t.infoIpt4]
                    }
                }), t._v(" "), a("i", [t._v("%")])]), t._v(" "), a("li", {}, [a("span", [t._v("一次收费：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.payTax,
                        expression: "payTax"
                    }],
                    attrs: {type: "text", placeholder: "请输入一次收费比例（选填）", name: ""},
                    domProps: {value: t.payTax},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.payTax = e.target.value)
                        }, t.infoIpt5]
                    }
                }), t._v(" "), a("i", [t._v("%")])]), t._v(" "), a("select-component", {
                    attrs: {
                        title: "还款种类",
                        items: t.items2
                    }, on: {"select-msg": t.selectMsg}
                }), t._v(" "), a("li", [a("span"), t._v(" "), a("span", {
                    staticClass: "start",
                    on: {click: t.startCalculate}
                }, [t._v("开始计算")]), t._v(" "), a("span", {
                    staticClass: "empty",
                    on: {click: t.clearAll}
                }, [t._v("清空重填")])])], 1)]), t._v(" "), t._m(4), t._v(" "), a("td", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showResult,
                        expression: "showResult"
                    }],
                    staticClass: "counter_right counter_right_width1",
                    class: {fadeInRight: t.showResult},
                    attrs: {valign: "top"}
                }, [a("ul", [t._m(5), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("首月供：")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.firstMoney))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showDj,
                        expression: "showDj"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("月递减：")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.djMoney))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("总利息：")]), t._v(" "), a("span", [t._v(t._s(t.totalLx))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("其他费用：")]), t._v(" "), a("span", [t._v(t._s(t.otherTax))]), t._v(" "), a("i", [t._v("元")])])]), t._v(" "), a("p", {staticClass: "note"}, [t._v("*以上结果仅供参考")])]), t._v(" "), a("td", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showResult,
                        expression: "!showResult"
                    }], staticClass: "counter_right2"
                }, [a("img", {
                    attrs: {
                        src: "/assets/home/jsq/jisuan_right.9ebbde4.png",
                        alt: ""
                    }
                }), t._v(" "), a("p", [t._v("要贷款买房，赶紧算算吧！")])])])])
            }, staticRenderFns: [function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("span", [a("b", [t._v("*")]), t._v("贷款种类：")])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("span", [a("b", [t._v("*")]), t._v("贷款金额：")])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("span", [a("b", [t._v("*")]), t._v("贷款期限：")])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("span", [a("b", [t._v("*")]), t._v("年利率：")])
            }, function () {
                var t = this, e = t.$createElement, s = t._self._c || e;
                return s("td", {staticClass: "counter_center"}, [s("img", {attrs: {src: a(50), alt: ""}})])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("li", {staticClass: "title"}, [a("h2", [t._v("计算结果")])])
            }]
        }
    }, 429: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("ul", {
                    staticClass: "option",
                    staticStyle: {height: "200px", "overflow-y": "auto"}
                }, t._l(t.items, function (e) {
                    return a("li", {
                        on: {
                            click: function (a) {
                                return t.fill(e)
                            }
                        }
                    }, [t._v(t._s(e.text))])
                }), 0)
            }, staticRenderFns: []
        }
    }, 430: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("ul", {staticClass: "option"}, t._l(t.items, function (e) {
                    return a("li", {
                        on: {
                            click: function (a) {
                                return t.fill(e)
                            }
                        }
                    }, [t._v(t._s(e.text))])
                }), 0)
            }, staticRenderFns: []
        }
    }, 431: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("transition", {attrs: {name: "fadeIn"}}, [a("div", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.show,
                        expression: "show"
                    }], staticClass: "alertBox"
                }, [a("div", {
                    directives: [{name: "show", rawName: "v-show", value: t.mask, expression: "mask"}],
                    staticClass: "alert-mask"
                }), t._v(" "), a("div", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.show,
                        expression: "show"
                    }], staticClass: "box"
                }, [t._v("\n            " + t._s(t.text) + "\n        ")])])])
            }, staticRenderFns: []
        }
    }, 432: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("ul", {
                    staticClass: "option",
                    staticStyle: {height: "200px", "overflow-y": "auto"}
                }, t._l(t.items, function (e) {
                    return a("li", {
                        key: e.val, on: {
                            click: function (a) {
                                return t.fill(e)
                            }
                        }
                    }, [t._v(t._s(e.text))])
                }), 0)
            }, staticRenderFns: []
        }
    }, 433: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("li", [t.showImportant ? a("span", [a("b", [t._v("*")]), t._v(t._s(t.title) + "：")]) : a("span", [t._v(t._s(t.title) + "：")]), t._v(" "), t._l(t.items, function (e) {
                    return a("span", {
                        staticClass: "fangshi", on: {
                            click: function (a) {
                                return t.fill(e)
                            }
                        }
                    }, [a("i", {class: e.cla}), t._v(t._s(e.text))])
                })], 2)
            }, staticRenderFns: []
        }
    }, 434: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("div", {staticClass: "clearfix", class: t.klass}, [a("p", {
                    staticClass: "select",
                    on: {click: t.toggleDrop}
                }, [a("input", {
                    attrs: {type: "", name: "", readonly: "readonly"},
                    domProps: {value: t.defaultItem.name || t.selectItem[0].name}
                }), t._v(" "), a("i")]), t._v(" "), a("ul", {
                    staticClass: "option",
                    class: t.classObj
                }, t._l(t.selectItem, function (e) {
                    return a("li", {
                        on: {
                            click: function (a) {
                                return t.selectDrop(e)
                            }
                        }
                    }, [t._v(t._s(e.name))])
                }), 0)])
            }, staticRenderFns: []
        }
    }, 435: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("section", [a("a", {
                    attrs: {
                        href: '/zxbj/',
                        style: 'display: block',
                        target: '_blank'
                    },
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.bannersma,
                        expression: "bannersma"
                    }], staticClass: "banner_top"
                }, [t.is3385City ? a("a", {
                    attrs: {
                        href: t.guohuCenterUrl,
                        target: "_blank"
                    }
                }, [a("img", {
                    attrs: {
                        src: "/assets/home/jsq/img/jsq_banner.jpg",
                        alt: ""
                    }
                })]) : a("img", {
                    attrs: {
                        src: "/assets/home/jsq/img/jsq_banner.jpg",
                        alt: ""
                    }
                })]), t._v(" "), a("div", {staticClass: "tab_list"}, [a("ul", {staticClass: "clearfix"}, t._l(t.items, function (e) {
                    return a("li", {
                        key: e.id, class: {on: t.moduleName === e.id}, on: {
                            click: function (a) {
                                return a.stopPropagation(), t.navi(e.id)
                            }
                        }
                    }, [t._v(t._s(e.title))])
                }), 0)])])
            }, staticRenderFns: []
        }
    }, 436: function (t, e, a) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("table", [a("tr", [a("td", {
                    staticClass: "counter_list",
                    class: t.isWidth5
                }, [a("ul", {staticClass: "clearfix fl"}, [a("li", [a("span", [t._v("贷款类别：")]), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.dkType,
                        expression: "dkType"
                    }],
                    attrs: {type: "", name: "", readonly: "readonly"},
                    domProps: {value: t.dkType},
                    on: {
                        click: t.clickDk, input: function (e) {
                            e.target.composing || (t.dkType = e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("ul", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showDkType,
                        expression: "showDkType"
                    }], staticClass: "option"
                }, t._l(t.items1, function (e) {
                    return a("li", {
                        key: e.text, on: {
                            click: function (a) {
                                return t.selectDkType(e)
                            }
                        }
                    }, [t._v(t._s(e.text))])
                }), 0)])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showCom,
                        expression: "showCom"
                    }]
                }, [a("span", [t._v("商业贷款额：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.sdtotal,
                        expression: "sdtotal"
                    }],
                    staticClass: "black",
                    attrs: {type: "text", placeholder: "请输入商业贷款额", name: ""},
                    domProps: {value: t.sdtotal},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.sdtotal = e.target.value)
                        }, t.sdIpt]
                    }
                }), t._v(" "), a("i", [t._v("万")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showCom,
                        expression: "showCom"
                    }], staticClass: "money_rate"
                }, [a("span", [t._v("利率方式：")]), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.sdRateConItem.calculatename,
                        expression: "sdRateConItem.calculatename"
                    }],
                    attrs: {type: "text", name: "", readonly: "readonly"},
                    domProps: {value: t.sdRateConItem.calculatename},
                    on: {
                        click: t.sdClickRate, input: function (e) {
                            e.target.composing || t.$set(t.sdRateConItem, "calculatename", e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("aj-rate", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showRatesd,
                        expression: "showRatesd"
                    }], attrs: {items: t.rateItems}, on: {"rate-msg": t.rateComSd}
                })], 1), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.sdRate,
                        expression: "sdRate"
                    }],
                    attrs: {type: "text", name: "", placeholder: ""},
                    domProps: {value: t.sdRate},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.sdRate = e.target.value)
                        }, t.sdRateIpt]
                    }
                }), t._v(" "), a("i", [t._v("%")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showCom,
                        expression: "showCom"
                    }]
                }, [a("span", [t._v("公积金贷款额：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.gjjtotal,
                        expression: "gjjtotal"
                    }],
                    attrs: {type: "text", placeholder: "请输入公积金贷款额", name: ""},
                    domProps: {value: t.gjjtotal},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.gjjtotal = e.target.value)
                        }, t.gjjIpt]
                    }
                }), t._v(" "), a("i", [t._v("万")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showCom,
                        expression: "showCom"
                    }], staticClass: "money_rate"
                }, [a("span", [t._v("公积金利率：")]), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.gjjRateConItem.calculatename,
                        expression: "gjjRateConItem.calculatename"
                    }],
                    attrs: {type: "", name: "", readonly: "readonly"},
                    domProps: {value: t.gjjRateConItem.calculatename},
                    on: {
                        click: t.gjjClickRate, input: function (e) {
                            e.target.composing || t.$set(t.gjjRateConItem, "calculatename", e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("aj-rate", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showRategjj,
                        expression: "showRategjj"
                    }], attrs: {items: t.rateItems}, on: {"rate-msg": t.rateComGjj}
                })], 1), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.gjjRate,
                        expression: "gjjRate"
                    }],
                    attrs: {type: "text", name: "", placeholder: ""},
                    domProps: {value: t.gjjRate},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.gjjRate = e.target.value)
                        }, t.gjjRateIpt]
                    }
                }), t._v(" "), a("i", [t._v("%")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showCom,
                        expression: "!showCom"
                    }]
                }, [a("span", [t._v("计算方式：")]), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.calcuTypeCon,
                        expression: "calcuTypeCon"
                    }],
                    staticClass: "black",
                    attrs: {type: "text", name: "", readonly: "readonly"},
                    domProps: {value: t.calcuTypeCon},
                    on: {
                        click: t.selectCalType, input: function (e) {
                            e.target.composing || (t.calcuTypeCon = e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("ul", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showCalType,
                        expression: "showCalType"
                    }], staticClass: "option"
                }, t._l(t.items2, function (e, s) {
                    return a("li", {
                        key: e.text, on: {
                            click: function (a) {
                                return t.calcuType(s, e)
                            }
                        }
                    }, [t._v(t._s(e.text))])
                }), 0)])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showDkIpt,
                        expression: "showDkIpt"
                    }]
                }, [a("span", [t._v("贷款总额：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.dktotal,
                        expression: "dktotal"
                    }],
                    attrs: {type: "text", placeholder: "请输入贷款总额", name: ""},
                    domProps: {value: t.dktotal},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.dktotal = e.target.value)
                        }, t.dktotalIpt]
                    }
                }), t._v(" "), a("i", [t._v("万")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showDkIpt && !t.showCom,
                        expression: "!showDkIpt && !showCom"
                    }]
                }, [a("span", [t._v("房屋单价：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.unitPrice,
                        expression: "unitPrice"
                    }],
                    attrs: {type: "text", placeholder: "请输入房屋单价", name: ""},
                    domProps: {value: t.unitPrice},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.unitPrice = e.target.value)
                        }, t.priceIpt]
                    }
                }), t._v(" "), a("i", [t._v("元/平米")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showDkIpt && !t.showCom,
                        expression: "!showDkIpt && !showCom"
                    }]
                }, [a("span", [t._v("房屋面积：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.buildArea,
                        expression: "buildArea"
                    }],
                    staticClass: "black",
                    attrs: {type: "text", placeholder: "请输入房屋面积", name: ""},
                    domProps: {value: t.buildArea},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.buildArea = e.target.value)
                        }, t.areaIpt]
                    }
                }), t._v(" "), a("i", [t._v("平方米")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showDkIpt && !t.showCom,
                        expression: "!showDkIpt && !showCom"
                    }]
                }, [a("span", [t._v("按揭成数：")]), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.fewCon,
                        expression: "fewCon"
                    }],
                    attrs: {type: "", name: "", readonly: "readonly"},
                    domProps: {value: t.fewCon},
                    on: {
                        click: t.clickFew, input: function (e) {
                            e.target.composing || (t.fewCon = e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("aj-few", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showFew,
                        expression: "showFew"
                    }], on: {"few-msg": t.fewMsg}
                })], 1)]), t._v(" "), a("li", [a("span", [t._v("按揭年数：")]), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.yearCon,
                        expression: "yearCon"
                    }],
                    attrs: {type: "", name: "", readonly: "readonly"},
                    domProps: {value: t.yearCon},
                    on: {
                        click: t.clickYear, input: function (e) {
                            e.target.composing || (t.yearCon = e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("aj-year", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showYear,
                        expression: "showYear"
                    }], on: {"year-msg": t.yearMsg}
                })], 1)]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showCom,
                        expression: "!showCom"
                    }], staticClass: "money_rate"
                }, [a("span", [t._v("利率方式：")]), t._v(" "), a("div", {staticClass: "clearfix"}, [a("p", {staticClass: "select"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.rateConItem.calculatename,
                        expression: "rateConItem.calculatename"
                    }],
                    attrs: {name: "", readonly: "readonly"},
                    domProps: {value: t.rateConItem.calculatename},
                    on: {
                        click: t.clickRate, input: function (e) {
                            e.target.composing || t.$set(t.rateConItem, "calculatename", e.target.value)
                        }
                    }
                }), t._v(" "), a("i")]), t._v(" "), a("aj-rate", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showRate,
                        expression: "showRate"
                    }], attrs: {items: t.rateItems}, on: {"rate-msg": t.rateMsg}
                })], 1), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.rate,
                        expression: "rate"
                    }],
                    attrs: {type: "text", name: "", placeholder: ""},
                    domProps: {value: t.rate},
                    on: {
                        input: [function (e) {
                            e.target.composing || (t.rate = e.target.value)
                        }, t.rateIpt]
                    }
                }), t._v(" "), a("i", [t._v("%")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showBasisPoint,
                        expression: "showBasisPoint"
                    }]
                }, [a("span", [t._v("基点：")]), t._v(" "), a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.basisPoint,
                        expression: "basisPoint"
                    }],
                    attrs: {placeholder: "", name: ""},
                    domProps: {value: t.basisPoint},
                    on: {
                        blur: t.basisPointBlur, focus: t.basisPointFocus, input: [function (e) {
                            e.target.composing || (t.basisPoint = e.target.value)
                        }, t.basisPointIpt]
                    }
                }), t._v(" "), a("i", [t._v("BP(‱)")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showBasisPoint,
                        expression: "showBasisPoint"
                    }], staticClass: "loan_rate"
                }, [a("span", [t._v("商贷利率：")]), t._v(" "), a("div", {staticClass: "loan_input"}, [a("p", [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.selectRate,
                        expression: "selectRate"
                    }],
                    attrs: {placeholder: "", name: "", readonly: "readonly"},
                    domProps: {value: t.selectRate},
                    on: {
                        input: function (e) {
                            e.target.composing || (t.selectRate = e.target.value)
                        }
                    }
                }), t._v(" "), a("i", [t._v("(％)")])]), t._v(" "), a("span", [t._v("+")]), t._v(" "), a("p", [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.transBasisPoint,
                        expression: "transBasisPoint"
                    }],
                    attrs: {placeholder: "", name: "", readonly: "readonly"},
                    domProps: {value: t.transBasisPoint},
                    on: {
                        input: function (e) {
                            e.target.composing || (t.transBasisPoint = e.target.value)
                        }
                    }
                }), t._v(" "), a("i", [t._v("(％)")])]), t._v(" "), a("span", [t._v("=")]), t._v(" "), a("p", {staticClass: "wid150"}, [a("input", {
                    directives: [{
                        name: "model",
                        rawName: "v-model",
                        value: t.caRate,
                        expression: "caRate"
                    }],
                    attrs: {placeholder: "", name: "", readonly: "readonly"},
                    domProps: {value: t.caRate},
                    on: {
                        input: function (e) {
                            e.target.composing || (t.caRate = e.target.value)
                        }
                    }
                }), t._v(" "), a("i", [t._v("(％)")])])])]), t._v(" "), a("li", [a("span", [t._v("还款方式：")]), t._v(" "), t._l(t.items3, function (e) {
                    return a("span", {
                        key: e.text, staticClass: "fangshi", on: {
                            click: function (a) {
                                return t.hkType(e)
                            }
                        }
                    }, [a("i", {class: e.cla}), t._v(t._s(e.text))])
                })], 2), t._v(" "), a("li", [a("span"), t._v(" "), a("span", {
                    staticClass: "start",
                    on: {click: t.startCalcu}
                }, [t._v("开始计算")]), t._v(" "), a("span", {
                    staticClass: "empty",
                    on: {click: t.clearAll}
                }, [t._v("清空重填")])])])]), t._v(" "), t._m(0), t._v(" "), a("td", {
                    staticClass: "counter_right counter_right_width1",
                    class: {fadeInRight: t.showResult, none: !t.showResult},
                    attrs: {valign: "top"}
                }, [a("ul", [t._m(1), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: !t.showDkIpt && !t.showCom,
                        expression: "!showDkIpt && !showCom"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("首付：")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.firstMoney))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v(t._s(t.isType1) + "：")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.monMoney))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {
                    directives: [{
                        name: "show",
                        rawName: "v-show",
                        value: t.showDj,
                        expression: "showDj"
                    }], staticClass: "clearfix"
                }, [a("h3", [t._v("每月递减：")]), t._v(" "), a("span", {staticClass: "on"}, [t._v(t._s(t.djMoney))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("贷款总额：")]), t._v(" "), a("span", [t._v(t._s(t.dkMoney))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("支付利息：")]), t._v(" "), a("span", [t._v(t._s(t.totalLx))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("还款总额：")]), t._v(" "), a("span", [t._v(t._s(t.totalMoney))]), t._v(" "), a("i", [t._v("元")])]), t._v(" "), a("li", {staticClass: "clearfix"}, [a("h3", [t._v("还款月数：")]), t._v(" "), a("span", [t._v(t._s(t.totalMon))]), t._v(" "), a("i", [t._v("月")])])]), t._v(" "), a("p", {staticClass: "note"}, [t._v("*以上结果仅供参考")])]), t._v(" "), a("td", {
                    staticClass: "counter_right2",
                    class: {none: t.showResult}
                }, [a("img", {
                    attrs: {
                        src: "/assets/home/jsq/jisuan_right.9ebbde4.png",
                        alt: ""
                    }
                }), t._v(" "), a("p", [t._v("要贷款买房，赶紧算算吧！")])])])])
            }, staticRenderFns: [function () {
                var t = this, e = t.$createElement, s = t._self._c || e;
                return s("td", {staticClass: "counter_center"}, [s("img", {attrs: {src: a(50), alt: ""}})])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("li", {staticClass: "title"}, [a("h2", [t._v("计算结果")])])
            }]
        }
    }, 437: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("div", {attrs: {id: "loginBarNew"}}, [t._m(0), t._v(" "), t._m(1), t._v(" "), t._m(2), t._v(" "), a("div", {staticClass: "sline21041104"}), t._v(" "), a("div", {
                    staticClass: "s4a",
                    staticStyle: {"background-image": "none", width: "100px"},
                    attrs: {onMouseOver: "this.className='s4a on2014'", onMouseOut: "this.className='s4a'"}
                }, [a("div", {
                    staticClass: "s4Box",
                    staticStyle: {
                        width: "90px",
                        overflow: "hidden",
                        "text-overflow": "ellipsis",
                        "white-space": "nowrap",
                        "text-align": "right"
                    }
                }, [a("a", {
                    attrs: {
                        href: t.login,
                        target: t.target
                    }
                }, [t._v(t._s(t.loginTitle))])]), t._v(" "), "登录" != t.loginTitle ? a("div", {staticClass: "listBox"}, [a("ul", [a("li", [a("a", {
                    attrs: {
                        rel: "nofollow",
                        href: t.login,
                        target: t.target
                    }
                }, [t._v("我的齐装网")])])]), t._v(" "), a("div", {
                    staticClass: "tuic",
                    staticStyle: {
                        height: "26px",
                        "line-height": "26px",
                        "text-align": "center",
                        "border-top": "1px solid #cccccc",
                        "font-size": "12px"
                    }
                }, [a("a", {
                    staticStyle: {display: "block"},
                    attrs: {id: "sfHeadLogout", rel: "nofollow", href: t.register, target: "_self"}
                }, [t._v(t._s(t.registerTitle))])])]) : t._e()])])
            }, staticRenderFns: [function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("div", {
                    staticClass: "s4a",
                    staticStyle: {"background-image": "none"},
                    attrs: {onMouseOver: "this.className='s4a on2014'", onMouseOut: "this.className='s4a'"}
                }, [a("div", {staticClass: "s4Box"}, [a("a", {
                    attrs: {
                        href: "javascript:;",
                    }
                }, [t._v("家居云")])])])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("div", {
                    staticClass: "s4a",
                    staticStyle: {"background-image": "none"},
                    attrs: {onMouseOver: "this.className='s4a on2014'", onMouseOut: "this.className='s4a'"}
                }, [a("div", {staticClass: "s4Box"}, [a("a", {
                    attrs: {
                        href: "javascript:;",
                    }
                }, [t._v("开发云")])])])
            }, function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("div", {
                    staticClass: "s4a",
                    staticStyle: {"background-image": "none"},
                    attrs: {onMouseOver: "this.className='s4a on2014'", onMouseOut: "this.className='s4a'"}
                }, [a("div", {staticClass: "s4Box"}, [a("a", {
                    attrs: {
                        href: "javascript:;",
                    }
                }, [t._v("经纪云")])])])
            }]
        }
    }, 438: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("div", [a("div", {staticClass: "counter main_1000 clearfix"}, [a("navigate"), t._v(" "), a("router-view")], 1)])
            }, staticRenderFns: []
        }
    }, 439: function (t, e) {
        t.exports = {
            render: function () {
                var t = this, e = t.$createElement, a = t._self._c || e;
                return a("ul", {staticClass: "option"}, t._l(t.items, function (e) {
                    return a("li", {
                        on: {
                            click: function (a) {
                                return t.fill(e)
                            }
                        }
                    }, [t._v(t._s(e.text))])
                }), 0)
            }, staticRenderFns: []
        }
    }, 442: function (t, e, a) {
        a(149), t.exports = a(148)
    }, 45: function (t, e, a) {
        "use strict";

        function s(t) {
            var e, a = t.pageId || "jsq_fd^sydk_pc", s = t.type || 0, i = t.params || [], o = s;
            0 === s ? e = {"vwg.page": a} : "jsq_fd^fdjsq_pc" === a && 147 === s ? e = {
                "vwg.page": a,
                "vwj.xtfh_downpayment": i.xtfh_downpayment,
                "vwj.unitprice": i.unitprice,
                "vwj.area": i.area,
                "vwj.loanradio": i.loanradio,
                "vwj.loantime": i.loantime,
                "vwj.loanrateforgjj": i.loanrateforgjj,
                "vwj.repaymentmethod": encodeURIComponent(i.repaymentmethod),
                "vwj.totalprice": i.totalprice,
                "vwj.xtfh_loanamount": i.xtfh_loanamount,
                "vwj.xtfh_loantime": i.xtfh_loantime,
                "vwj.xtfh_yuegong": i.xtfh_yuegong,
                "vwj.xtfh_paymentinterest": i.xtfh_paymentinterest,
                "vwj.xtfh_repaymentamount": i.xtfh_repaymentamount
            } : "jsq_fd^fdjsq_pc" === a && 146 === s ? e = {
                "vwg.page": a,
                "vwj.xtfh_downpayment": i.xtfh_downpayment,
                "vwj.unitprice": i.unitprice,
                "vwj.area": i.area,
                "vwj.loanradio": i.loanradio,
                "vwj.loantime": i.loantime,
                "vwj.annualinterestrate": i.annualinterestrate,
                "vwj.repaymentmethod": encodeURIComponent(i.repaymentmethod),
                "vwj.totalprice": i.totalprice,
                "vwj.xtfh_loanamount": i.xtfh_loanamount,
                "vwj.xtfh_loantime": i.xtfh_loantime,
                "vwj.xtfh_yuegong": i.xtfh_yuegong,
                "vwj.xtfh_monthlyrepayment": i.xtfh_monthlyrepayment || "",
                "vwj.xtfh_monthdeclineamount": i.xtfh_monthdeclineamount || "",
                "vwj.xtfh_paymentinterest": i.xtfh_paymentinterest,
                "vwj.xtfh_repaymentamount": i.xtfh_repaymentamount
            } : "jsq_fd^fdjsq_pc" === a && 148 === s ? e = {
                "vwg.page": a,
                "vwj.loanamount": i.loanamount,
                "vwj.loantime": i.loantime,
                "vwj.providentfundloanamount": i.providentfundloanamount,
                "vwj.annualinterestrate": encodeURIComponent(i.annualinterestrate),
                "vwj.loanrateforgjj": encodeURIComponent(i.loanrateforgjj),
                "vwj.repaymentmethod": encodeURIComponent(i.repaymentmethod),
                "vwj.xtfh_yuegong": i.xtfh_yuegong,
                "vwj.xtfh_monthlyrepayment": i.xtfh_monthlyrepayment,
                "vwj.xtfh_monthdeclineamount": i.xtfh_monthdeclineamount,
                "vwj.xtfh_paymentinterest": i.xtfh_paymentinterest,
                "vwj.xtfh_repaymentamount": i.xtfh_repaymentamount
            } : "jsq_shuifei^sfjsq_pc" === a && 36 === s ? e = {
                "vwg.page": a,
                "vwj.area": i.area,
                "vwj.unitprice": i.unitprice,
                "vwj.genre": i.genre,
                "vwj.isfirst": i.isfirst,
                "vwj.xtfh_totalprice": i.xtfh_totalprice,
                "vwj.elevator": i.elevator,
                "vwj.tax": i.tax,
                "vwj.xtfh_contracttax": i.xtfh_contracttax
            } : "jsq_shuifei^sfjsq_pc" === a && 37 === s ? e = {
                "vwg.page": a,
                "vwj.xtfh_addtax": i.xtfh_addtax,
                "vwj.xtfh_contracttax": i.xtfh_contracttax,
                "vwj.xtfh_coststamptax": i.xtfh_coststamptax,
                "vwj.xtfh_incometax": i.xtfh_incometax,
                "vwj.xtfh_integratedcost": i.xtfh_integratedcost,
                "vwj.area": i.area,
                "vwj.unitprice": i.unitprice,
                "vwj.totalprice": i.totalprice,
                "vwj.valuebasedway": encodeURIComponent(i.valuebasedway),
                "vwj.dwellingtype": i.dwellingtype,
                "vwj.year": i.year,
                "vwj.isfirst": i.isfirst,
                "vwj.isonly": i.isonly,
                "vwj.xtfh_stamptax": i.xtfh_stamptax
            } : "jsq_fd^gjjdked_pc" === a && 147 === s ? e = {
                "vwg.page": a,
                "vwj.cpfdepositmine": i.cpfdepositmine,
                "vwj.cpfratiomine": i.cpfratiomine,
                "vwj.cpfdepositspouse": i.cpfdepositspouse,
                "vwj.cpfratiospouse": i.cpfratiospouse,
                "vwj.totalprice": i.totalprice,
                "vwj.dwellingtype": i.dwellingtype,
                "vwj.loantime": i.loantime,
                "vwj.creditgrade": i.creditgrade
            } : "jsq_fd^tqhk_pc" === a && 147 === s ? e = {
                "vwg.page": a,
                "vwj.loanamount": i.loanamount,
                "vwj.loantime": i.loantime,
                "vwj.loanrateforgjj": i.loanrateforgjj,
                "vwj.repaymenttime": encodeURIComponent(i.repaymenttime),
                "vwj.repaymenttimeadvance": i.repaymenttimeadvance,
                "vwj.repaymentmethod": i.repaymentmethod,
                "vwj.treatmentmethod": i.treatmentmethod,
                "vwj.xtfh_yuegong": i.xtfh_yuegong,
                "vwj.xtfh_totalrepayment": i.xtfh_totalrepayment,
                "vwj.xtfh_interestpaid": i.xtfh_interestpaid
            } : "jsq_fd^tqhk_pc" === a && 146 === s ? e = {
                "vwg.page": a,
                "vwj.loanamount": i.loanamount,
                "vwj.loantime": i.loantime,
                "vwj.annualinterestrate": i.annualinterestrate,
                "vwj.repaymenttime": encodeURIComponent(i.repaymenttime),
                "vwj.repaymenttimeadvance": i.repaymenttimeadvance,
                "vwj.repaymentmethod": i.repaymentmethod,
                "vwj.treatmentmethod": i.treatmentmethod,
                "vwj.xtfh_yuegong": i.xtfh_yuegong,
                "vwj.xtfh_totalrepayment": i.xtfh_totalrepayment,
                "vwj.xtfh_interestpaid": i.xtfh_interestpaid
            } : "jsq_fd^zxdk_pc" === a && 29 === s ? e = {
                "vwg.page": a,
                "vwj.xtfh_othermoney": i.xtfh_othermoney,
                "vwj.loantype": i.loantype,
                "vwj.loanamount": i.loanamount,
                "vwj.loantime": i.loantime,
                "vwj.annualinterestrate": encodeURIComponent(i.annualinterestrate),
                "vwj.monthmanagefee": encodeURIComponent(i.monthmanagefee),
                "vwj.onecharge": encodeURIComponent(i.onecharge),
                "vwj.repaymentmethod": i.repaymentmethod,
                "vwj.xtfh_totalinterest": i.xtfh_totalinterest
            } : "jsq_pg^zxdk_pc" === a && 50 === s && (e = {
                "vwj.ownfunds": i.ownfunds,
                "vwj.monthlyincome": i.monthlyincome,
                "vwj.expend": i.expend,
                "vwj.loantime": i.loantime,
                "vwj.area": i.area
            });
            var n = {};
            for (var r in e) e.hasOwnProperty(r) && null !== e[r] && "" !== e[r] && void 0 !== e[r] && "undefined" !== e[r] && (n[r] = e[r]);
            _ub.city = "北京", _ub.collect(o, n)
        }

        Object.defineProperty(e, "__esModule", {value: !0}), e.yhxw = s
    }, 50: function (t, e) {
        t.exports = "data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAABDAAD/4QMqaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjUtYzAxNCA3OS4xNTE0ODEsIDIwMTMvMDMvMTMtMTI6MDk6MTUgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RDJGREUyMjA4N0YwMTFFN0IwQTU5RjNDQjFGQ0YwMkIiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDJGREUyMjE4N0YwMTFFN0IwQTU5RjNDQjFGQ0YwMkIiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpEMkZERTIxRTg3RjAxMUU3QjBBNTlGM0NCMUZDRjAyQiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpEMkZERTIxRjg3RjAxMUU3QjBBNTlGM0NCMUZDRjAyQiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pv/uAA5BZG9iZQBkwAAAAAH/2wCEAAUDAwMDAwUDAwUHBAQEBwgGBQUGCAkHBwgHBwkLCQoKCgoJCwsMDAwMDAsODg4ODg4UFBQUFBYWFhYWFhYWFhYBBQUFCQgJEQsLERQPDg8UFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFv/AABEIAB0AFQMBEQACEQEDEQH/xABnAAADAQEAAAAAAAAAAAAAAAAAAQIDCAEBAAAAAAAAAAAAAAAAAAAAABAAAQIEAgcJAQAAAAAAAAAAARECAFESAzFBIWGB0SITI3GRweEyUmIzBDQRAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhEDEQA/AOxm9Bwtn63eg+0y3QDurbdz26QAjxNs9kBpUKal0YrkkAnNa9pa4KDiICLLnGq241cspVPznARQKz+ZemEcmrTw98Bd17lFq363Z+0TgGS389oBoXJozJMBPIPLx6q11fLdl2QBYxdX9q8fgmqUAH+gVy6clz2wGsB//9k="
    }, 51: function (t, e, a) {
        "use strict";
        t.exports = {
            formatNumber: function (t) {
                return t > 1e9 ? parseFloat((t / 1e8).toFixed(2)) + "亿" : t > 1e8 ? parseFloat((t / 1e4).toFixed(2)) + "万" : Math.round(t)
            }
        }
    }, 57: function (t, e, a) {
        function s(t) {
            a(396)
        }

        var i = a(10)(a(173), a(431), s, null, null);
        t.exports = i.exports
    }
}, [442]);