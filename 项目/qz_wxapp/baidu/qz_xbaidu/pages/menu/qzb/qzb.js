// import { getWalkDetail, baojia } from '../../../utils/api.js'
import { fb } from '../../../utils/api.js'
const app = getApp()
// 获取随机数的方法
function GetRandomNum(Min, Max) {
    var Range = Max - Min;
    var Rand = Math.random();
    return (Min + Math.round(Rand * Range));
}
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
    swan.showModal({
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
        num: '52800',
        cityName: '',
        cityId: '',
        tel: '',
        isHide: true,
        checkValue: true,
        xzcity: '请选择城市',
        orderid: '',
        isEmpty: [true, false],
        clickAble: true,
        dialogbul:false,
        successbul:false
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
            swan.showToast({
                title: "请确定已阅读免责声明，并勾选。",
                icon: 'none'
            });
            return false;
        }

        //TODO 发单请求
        swan.request({
            url: 'https://appapi.qizuang.com/fb_order/?src='+app.globalData.sourceMark,
            data: {
                cs: that.data.cityId,
                mianji: mianji,
                tel: phone,
                fb_type: 'baojia',
                source: 19070251,
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
                    swan.showToast({
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

        swan.request({
            url: 'https://appapi.qizuang.com/fb_order/?src='+app.globalData.sourceMark,
            data: {
                name: call,
                bjxq: xiaoqu,
                tel: phone,
                source: 19070251,
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
        swan.navigateTo({
            url: '/pages/gonglue/bjdetail/bjdetail?orderid=' + e,
        });
    },
    onSelectCityHandle: function () {
        this.setData({
            isHide: false
        })
    },
    //选择城市
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
        }
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

        swan.setPageInfo({
            title: '装修保障服务-装修纠纷维权',
            keywords: '装修保障，装修服务，装修纠纷，装修维权',
            description: '齐装保装修保障服务为广大装修业主提供最优的保障，解决装修纠纷问题，有问题能够第一时间处理，让您不再为装修而烦恼，省时省力不烦心！',
            image:'http://m.qizuang.com/assets/mobile/baojia/img/baojia-banner.jpg'
        })
    },
    onShow: function (e) {
        swan.setPageInfo({
            title: '装修保障服务-装修纠纷维权',
            keywords: '装修保障，装修服务，装修纠纷，装修维权',
            description: '齐装保装修保障服务为广大装修业主提供最优的保障，解决装修纠纷问题，有问题能够第一时间处理，让您不再为装修而烦恼，省时省力不烦心！',
            image:'http://m.qizuang.com/assets/mobile/baojia/img/baojia-banner.jpg'
        })
    },
    goTop(){
        console.log(322)
        swan.pageScrollTo({
                scrollTop: 0
            })
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    },
    opendialog(){
        this.setData({
            dialogbul:true
        })
    },
    closedialog(){
        this.setData({
            dialogbul:false
        })
    },
    freeapplication(){
        if(this.data.cityId==''){
            swan.showToast({
                title: '请选择城市',
                icon: 'none'
            })
            return
        }
        if(this.data.tel==''){
            swan.showToast({
                title: '请输入手机号码',
                icon: 'none'
            })
            return
        }
        if(!this.data.checkValue){
            swan.showToast({
                title: '请阅读免责声明',
                icon: 'none'
            })
            return
        }
        let params = {
            cs:this.data.cityId,
            tel:this.data.tel,
            source:'20081431',
            src: app.globalData.sourceMark
        }
        fb('/v1/fb',params).then(data => {
            if (data.error_code == 0) {
                this.setData({
                    successbul:true
                })
            } else {
                swan.showToast({
                    title: data.error_msg,
                    icon: 'none'
                })
            }
        }).catch(error => {
            swan.showToast({
                title: "网络异常",
                icon: 'none'
            })
        })
    },
    determine(){
        this.setData({
            successbul:false
        })
    }
});