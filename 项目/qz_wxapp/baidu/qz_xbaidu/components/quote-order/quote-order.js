import {  } from "../../utils/api.js"
const app = getApp()
function GetRandomNum(Min, Max) {
    var Range = Max - Min;
    var Rand = Math.random();
    return (Min + Math.round(Rand * Range));
}
Component({
    properties: {
        propName: {
            value: 'val',
            observer: function (newVal, oldVal) {}
        },
        source: {
            type: 'val'
        }
    },
    options: {
        addGlobalClass: true
    },
    data: {
        cityName: '',
        cityId: '',
        num: '52800',
        isHide: true,
        checkValue: true,
        xzcity: '请选择城市',
        bjtel: '',
        mianji: '',
    },
    created: function() {
        // 随机数
        let that = this
        let timer = setInterval(function () {
            let getNum = GetRandomNum(30000, 120000);
            if (getNum > 99999) {
                that.setData({
                    num: getNum
                });
            } else {
                that.setData({
                    num: getNum
                });
            }
        }, 400);
    },
    methods: {
        // 城市选择控件
        onSelectCityHandle: function () {
            this.setData({
                isHide: false
            })
        },
        closeSelect: function (res) {
            if (JSON.stringify(res.detail) === '{}') {
                this.setData({
                    isHide: true,
                })
            } else {
                this.setData({
                    isHide: true,
                    cityName: res.detail[1],
                    cityId: res.detail[0]
                })
                this.triggerEvent('myevent', this.data.cityId)
            }
        },
        // 1. 发单
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
        getmj(e) {
            this.setData({
                mianji: e.detail.value,
            })
        },
        gettel(e) {
            this.setData({
                bjtel: e.detail.value,
            })
        },
        SubmitBj: function (e) {
            let city = this.data.cityName
            let phone = this.data.bjtel
            let mianji = this.data.mianji
            let checked = this.data.checkValue
            let that = this
            if (!app.checkFun.checkNull(city, "请选择城市")) return
            if (mianji.length < 1) {
                swan.showToast({
                    title: "请输入房屋面积",
                    icon: "none"
                })
                return;
            } else {
                if (parseFloat(mianji) > 1000 || parseFloat(mianji) <= 0) {
                    swan.showToast({
                        title: "房屋面积请输入1-1000的数字",
                        icon: "none"
                    })
                    return;
                }
            }
            if (phone.length < 1) {
                swan.showToast({
                    title: "请输入您的手机号",
                    icon: "none"
                })
                return;
            } else {
                var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
                if (!reg.test(phone)) {
                    swan.showToast({
                        title: "请输入正确的手机号码",
                        icon: "none"
                    })
                    return false;
                }
            }
            if (checked == false) {
                swan.showToast({
                    title: "请勾选我已阅读齐装网的【免责声明】",
                    icon: 'none'
                });
                return false;
            }
            swan.request({
                url: 'https://appapi.qizuang.com/fb_order/?src=' + app.globalData.sourceMark,
                data: {
                    cs: that.data.cityId,
                    mianji: mianji,
                    tel: phone,
                    fb_type: 'baojia',
                    source: this.properties.source,
                    src: app.globalData.sourceMark
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    if (res.data.status == 1) {
                        swan.showToast({
                            title: '稍后有客服专员与您来电，请保持电话畅通',
                            icon: "none"
                        })
                        that.setData({
                            mianji: '',
                            bjtel: ''
                        })
                    } else {
                        swan.showToast({
                            title: res.data.info,
                            icon: "none"
                        })
                    }
                }
            })
        }
    }
});