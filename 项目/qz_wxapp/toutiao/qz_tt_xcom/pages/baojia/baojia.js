const app = getApp()
// 获取随机数的方法
function GetRandomNum(Min, Max) {
    var Range = Max - Min;
    var Rand = Math.random();
    return (Min + Math.round(Rand * Range));
}
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
    tt.showModal({
        title: title,
        content: content,
        showCancel: false,
        success: function (res) {
            if (res.confirm) {
                confirm();
            }
        }
    });
}
Page({
    data: {
        lingNum: '00000000000',
        num: '52800',
        cityName: '',
        cityId: '',
        tel: '',
        isHide: true,
        checkValue: true,
        xzcity: '请选择城市',
        orderid: '',
        isEmpty: [true, false],
        clickAble: true
    },
    //发单
    getFdHandle: function () {
        this.setData({
            maskShow: true,
            hb_show: true
        })
    },
    onCloseFdHandle: function () {
        this.setData({
            maskShow: false,
            hb_show: false
        })
    },
    inputmianji: function (e) {
        this.setData({ mianji: e.detail.value })
    },
    inputtel: function (e) {
        this.setData({ tel: e.detail.value })
    },
    SubmitHandle: function (e) {
        let city = this.data.cityName
        let phone = e.detail.value.tel
        let mianji = e.detail.value.mianji
        let cityId = this.data.cityId
        let checked = this.data.checkValue
        this.setData({
            tel: phone
        })
        let that = this
        //所在城市
        if (!app.checkFun.checkNull(city, "请选择城市")) return
        // 面积
        if (mianji.length < 1) {
            alertViewWithCancel("提示", "请输入面积", function () { });
            return;
        } else {
            if (parseFloat(mianji) > 1000 || parseFloat(mianji) <= 0) {
                alertViewWithCancel("提示", "房屋面积请输入1-1000的数字", function () {
                    that.setData({
                        mianji: ''
                    })
                });
                return;
            }
        }
        //联系方式
        if (phone.length < 1) {
            alertViewWithCancel("提示", "请输入您的手机号", function () {
            });
            return;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!reg.test(phone)) {
                alertViewWithCancel("提示", "请填写正确的手机号码", function () {
                });
                return false;
            }
        }

        if (that.data.checkValue == false) {
            tt.showToast({
                title: "请确定已阅读免责声明，并勾选。",
                icon: 'none'
            });
            return false;
        }

        //TODO 发单请求
        tt.request({
            url: 'https://appapi.qizuang.com/fb_order/?src='+app.globalData.sourceMark,
            data: {
                cs: that.data.cityId,
                mianji: mianji,
                tel: phone,
                fb_type: 'baojia',
                source: 19092744,
                src: app.globalData.sourceMark
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success: function (res) {
                if (res.data.status == 1) {
                    that.setData({
                        isEmpty: [false, true],
                        orderid: res.data.data.orderid
                    })
                } else {
                    tt.showToast({
                        title: res.data.info,
                        icon: "none"
                    })
                }


            }
        });
    },
    inputxiaoqu: function (e) {
        this.setData({ xiaoqu: e.detail.value })
    },
    inputcall: function (e) {
        this.setData({ call: e.detail.value })
    },
    SubmitHand: function (e) {
        let clickAble = this.data.clickAble;
        let that = this;
        if (!clickAble) {
            alertViewWithCancel("提示", "请勿频繁操作", function () { });
            return false;
        }
        let call = e.detail.value.call
        let xiaoqu = e.detail.value.xiaoqu
        let reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        if (call == "") {
            alertViewWithCancel("提示", "请输入称呼", function () {
                that.setData({
                    call: "",
                })
            });
            return;
        } else if (!reg.test(call)) {
            alertViewWithCancel("提示", "请输入正确的名称", function () {
                that.setData({
                    call: "",
                })
            });
            return;
        }
        if (call.length < 1) {
            alertViewWithCancel("提示", "请输入称呼", function () { });
            return;
        }
        if (xiaoqu.length < 1) {
            alertViewWithCancel("提示", "请输入小区名称", function () { });
            return;
        }
        that.setData({
            clickAble: false
        });
        setTimeout(function () {
            that.setData({
                clickAble: true
            })
        }, 5000)
        let phone = that.data.tel

        tt.request({
            url: 'https://appapi.qizuang.com/fb_order/?src='+app.globalData.sourceMark,
            data: {
                name: call,
                bjxq: xiaoqu,
                tel: phone,
                source: 19092744,
                src: app.globalData.sourceMark
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success: function (res) {
                if (res.data.status == 1) {
                    that.setData({
                        mianji: "",
                        tel: "",
                        call: "",
                        xiaoqu: "",
                        isEmpty: [true, false],
                    });
                    that.jump(that.data.orderid);
                } else {
                    alertViewWithCancel("提示", res.data.info, function () { });
                }

            }
        });
    },
    jump: function (e) {
        tt.navigateTo({
            url: '/pages/bjdetail/bjdetail?orderid=' + e,
        });
    },
    onSelectCityHandle: function () {
        this.setData({
            isHide: false
        })
    },
    //选择城市
    closeSelect: function (res) {
        this.setData({
            isHide: true,
            cityName: res.detail[1],
            cityId: res.detail[0]
        })

    },
    // 切换免责
    checkboxChange: function (e) {
        let that = this;
        if (e.detail.value == '') {
            that.setData({
                checkValue: false
            })
        } else {
            that.setData({
                checkValue: true
            })
        }
    },
    onLoad: function (e) {
        let that = this;
        // 随机数
        let timer = setInterval(function () {
            let getNum = GetRandomNum(30000, 120000);
            if (getNum > 99999) {
                that.setData({ lingNum: '0000000000', num: getNum });
            } else {
                that.setData({ lingNum: '00000000000', num: getNum });
            }
        }, 400);
    },
    onShow: function (e) {
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    }
});