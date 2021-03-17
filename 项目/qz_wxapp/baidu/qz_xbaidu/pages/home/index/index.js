import { getIndex } from '../../../utils/api.js'
const $time = require('../../../utils/utils.js')
const app = getApp()
// 获取随机数的方法
function GetRandomNum(Min, Max) {
    var Range = Max - Min;
    var Rand = Math.random();
    return (Min + Math.round(Rand * Range));
}
Page({
    data: {
        scrollHeight: '',
        images: [],
        current: 0,
        switchIndicateStatus: true,
        switchAutoPlayStatus: true,
        switchDuration: 500,
        autoPlayInterval: 5000,
        tabChange: [true, false, false],
        currentTab: '',
        switchIndicateStatusdur: false,
        switchAutoPlayStatusdur: false,
        switchDurationdur: 0,
        autoPlayIntervaldur: 5000,
        toView: '',
        hasSCroll: false,
        scrollTab: [[
            {
                name: "验房",
                imgSrc: "../../../images/zu_1.png",
                href: "../../gonglue/process/process?category=shoufang"
            }, {
                name: "选公司",
                imgSrc: "../../../images/zu_2.png",
                href: "../../gonglue/process/process?category=gongsi"
            },
            {
                name: "量房",
                imgSrc: "../../../images/zu_3.png",
                href: "../../gonglue/process/process?category=liangfang"
            },
            {
                name: "设计预算",
                imgSrc: "../../../images/zu_4.png",
                href: "../../gonglue/process/process?category=shejiyusuan"
            }], [
            {
                name: "选材",
                imgSrc: "../../../images/sg_1.png",
                href: "../../gonglue/process/process?category=xuancai"
            },
            {
                name: "拆改",
                imgSrc: "../../../images/sg_2.png",
                href: "../../gonglue/process/process?category=chagai"
            },
            {
                name: "水电",
                imgSrc: "../../../images/sg_3.png",
                href: "../../gonglue/process/process?category=shuidian"
            },
            {
                name: "防水",
                imgSrc: "../../../images/sg_4.png",
                href: "../../gonglue/process/process?category=fangshui"
            },
            {
                name: "泥瓦",
                imgSrc: "../../../images/sg_5.png",
                href: "../../gonglue/process/process?category=niwa"
            },
            {
                name: "木工",
                imgSrc: "../../../images/sg_6.png",
                href: "../../gonglue/process/process?category=mugong"
            },
            {
                name: "油漆",
                imgSrc: "../../../images/sg_7.png",
                href: "../../gonglue/process/process?category=youqi"
            }, {}
        ], [
            {
                name: "验收",
                imgSrc: "../../../images/rz_1.png",
                href: "../../gonglue/process/process?category=jianche"
            },
            {
                name: "保养",
                imgSrc: "../../../images/rz_2.png",
                href: "../../gonglue/process/process?category=baoyang"
            }, {
                name: "配饰",
                imgSrc: "../../../images/rz_3.png",
                href: "../../gonglue/process/process?category=peishi"
            }, {
                name: "家居",
                imgSrc: "../../../images/rz_4.png",
                href: "../../gonglue/process/process?category=jjsh"
            }
        ]],
        autoHeight: false,
        fdType: 0,
        num: '52800',
        // 发单
        cityName: '',
        cityName_second: '',
        cityId: '',
        bm: '',
        isHide: true,
        checkValue: true,
        xzcity: '请选择城市',
        // 设计
        xcheckValue: true,
        mianji: '',
        bjtel: '',
        xname: '',
        sjtel: ''
    },
    banner: function (e) {
        getIndex(
            '/bd/v1/home/banner',
            {
                cs: e
            }
        ).then(data => {
            if (data.error_code == 0) {
                this.setData({
                    images: data.data
                })
            } else {
                swan.showToast({
                    title: data.error_msg,
                    icon: 'none'
                })
            }
        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
    },
    toSwiper: function (e) {
        let url = e.currentTarget.dataset.url;
        swan.navigateTo({
            url: url
        })
    },
    toClass: function (e) {
        let url = e.currentTarget.dataset.url;
        let mode = e.currentTarget.dataset.mode;
        let cs = e.currentTarget.dataset.cs;
        let zx = {}
        if(cs == 'cs') {
            const xoptions = swan.getStorageSync('xoptions');
            if(xoptions.cityName != undefined) {
                zx = {
                    cid: xoptions.cid,
                    cityName: xoptions.cityName,
                    cs: xoptions.cs,
                    id: 0
                }
            } else {
                zx = {
                    cid: this.data.cityId,
                    cityName: this.data.cityName_second,
                    cs: this.data.bm,
                    id: 0
                }
            }
            swan.setStorageSync('xoptions', zx);
        }
        if (mode == "Tab") {
            swan.switchTab({
                url: url
            })
        } else {
            swan.navigateTo({
                url: url
            })
        }
    },
    changTab: function (e) {
        if (e.target.dataset.current == 1) {
            this.setData({
                autoHeight: true
            })
        } else {
            this.setData({
                autoHeight: false
            })
        }
        var that = this;
        that.setData({
            currentTab: e.target.dataset.current
        });
    },
    fdTab(e) {
        this.setData({
            fdType: e.target.dataset.num
        });
    },

    updata() {
        swan.showFavoriteGuide({
            type: 'tip',
            content: '关注小程序,下次使用更便捷',
            success: res => {
                console.log('关注成功', err);
            },
            fail: err => {
                console.log('关注失败：', err);
            }
        })
        if (swan.canIUse("getUpdateManager")) {
            const updateManager = swan.getUpdateManager();
            updateManager.onCheckForUpdate(function (res) {
                if (res.hasUpdate) {
                    updateManager.onUpdateReady(function () {
                        swan.showModal({
                            title: "更新提示",
                            content: "新版本已经准备好，是否重启小程序？",
                            success(res) {
                                if (res.confirm) {
                                    updateManager.applyUpdate();
                                }
                            }
                        });
                    });
                    updateManager.onUpdateFailed(function () {
                        swan.showModal({
                            title: "已经有新版本了哟~",
                            content: "新版本已经上线啦~，请您删除当前小程序，重新搜索打开哟~"
                        });
                    });
                }
            });
        } else {
            swan.showModal({
                title: "提示",
                content:
                    "当前版本过低，无法使用该功能，请升级到最新百度版本后重试。"
            });
        }
    },
    // 1. 发单
    onSelectCityHandle: function () {
        this.setData({
            isHide: false
        })
    },
    closeSelect: function (res) {
        if (res.detail[3] == 2) {
            this.banner(res.detail[0])
        }
        if (JSON.stringify(res.detail) === '{}') {
            this.setData({
                isHide: true,
            })
        } else {
            this.setData({
                isHide: true,
                cityName: res.detail[1],
                cityName_second: res.detail[2],
                cityId: res.detail[0],
                bm: res.detail[4]
            })
        }
    },
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
        //所在城市
        if (!app.checkFun.checkNull(city, "请选择城市")) return
        // 面积
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
        //联系方式
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

        //TODO 发单请求
        swan.request({
            url: 'https://appapi.qizuang.com/fb_order/?src=' + app.globalData.sourceMark,
            data: {
                cs: that.data.cityId,
                mianji: mianji,
                tel: phone,
                fb_type: 'baojia',
                source: 20081223,
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
        });
    },

    // 2. 设计
    getname(e) {
        this.setData({
            xname: e.detail.value,
        })
    },
    getsjtel(e) {
        this.setData({
            sjtel: e.detail.value,
        })
    },
    xcheckboxChange: function (e) {
        let that = this;
        if (e.detail.value == '') {
            that.setData({
                xcheckValue: false
            })
        } else {
            that.setData({
                xcheckValue: true
            })
        }
    },
    SubmitSj: function (e) {
        let city = this.data.cityName
        let phone = this.data.sjtel
        let name = this.data.xname
        let checked = this.data.xcheckValue
        let regName = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
        let that = this
        if (!app.checkFun.checkNull(city, "请选择城市")) return
        if (name == '') {
            swan.showToast({
                title: "请输入您的姓名",
                icon: "none"
            })
            return;
        } else {
            if (!regName.test(name)) {
                swan.showToast({
                    title: "请输入正确的姓名",
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

        //TODO 发单请求
        swan.request({
            url: 'https://appapi.qizuang.com/fb_order/?src=' + app.globalData.sourceMark,
            data: {
                cs: that.data.cityId,
                name: name,
                tel: phone,
                fb_type: 'sheji',
                source: 20081231,
                src: app.globalData.sourceMark
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success: function (res) {
                if (res.data.status == 1) {
                    swan.showToast({
                        title: '感谢您的申请！',
                        icon: "none"
                    })
                    that.setData({
                        xname: '',
                        sjtel: ''
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
    onLoad() {
        let that = this
        that.timer = setInterval(function () {
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
        this.removeSkeleton()
    },
    onShow() {
        this.updata()
        const xoptions = swan.getStorageSync('xoptions');
        let zx = {
            cid: xoptions.cid,
            cityName: xoptions.cityName,
            id: 0,
            cs: xoptions.cs
        }
        this.setData({
            cs: xoptions.cs ? xoptions.cs : 'sz',
            bm: xoptions.cs ? xoptions.cs : 'sz'
        })
        swan.setStorageSync('xoptions', zx);
        swan.setPageInfo({
            title: '齐装网-专业的装饰装修网_装修设计公司一站式服务平台',
            keywords: '装修网,装修平台,装修设计,装饰公司',
            description: '齐装网是一家专业的互联网装修行业知名平台,汇集了全国数万家装饰公司和优秀的装修设计师,提供免费的家装、工装等各类房屋装修设计方案,免费上门量房服务,有房要装,就上齐装！',
            image: [],
            success: res => {
                console.log('setPageInfo success', res);
            },
            fail: err => {
                console.log('setPageInfo fail', err);
            }
        })
    },
    onHide() {
        this.timer && clearInterval(this.timer);
    }
})
