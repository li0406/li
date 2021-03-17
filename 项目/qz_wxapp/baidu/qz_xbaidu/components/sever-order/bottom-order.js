import { fadanHandle, merchantHandle, getOrderDetail, getCityData } from "../../utils/api.js"
const app = getApp()
Component({
    properties: {
        name: {
            type: String,
            value: '',
            observer: function (newv, oldv, path) {
            }
        }
    },
    data: {
        btns: [
            {
                btnText: '立即申请预约',
                site_source: '20082457',
                bannerImg: 'https://staticqn.qizuang.com/custom/20200901/FhdpJhDyESYjsd4OHkL7dpjoXWDd'
            }, {
                btnText: '立即申领设计方案',
                site_source: '20082438',
                bannerImg: 'http://staticqn.qizuang.com/custom/20200806/Fh5hnqjznLBtwL3jTDpUY9cJMP8T'
            }, {
                btnText: '免费获取报价明细',
                site_source: '20082400',
                bannerImg: 'http://staticqn.qizuang.com/custom/20200806/FgytYKkjCbpJk-MGqEZuFu0yK-_M'
            }],
        currentWin: '',
        isOpenMask: false,
        inputList: [
            {
                id: '1',
                title: '公司名称',
                placeholder: '您的公司名称',
                eleType: 'input',
                type: 'text',
                name: 'company',
                val: '',
                isShow: 3
            },
            {
                id: '2',
                title: '联系人',
                placeholder: '您的称呼',
                eleType: 'input',
                type: 'text',
                name: 'linkman',
                val: '',
                isShow: 3
            },
            {
                title: '所在城市',
                placeholder: '请选择您所在的城市',
                eleType: 'button',
                type: 'default',
                name: 'city',
                val: '',
                isShow: 1
            },
            {
                title: '房屋面积',
                placeholder: '㎡',
                eleType: 'input',
                type: 'digit',
                name: 'area',
                val: '',
                isShow: 1
            },
            {
                title: '联系方式',
                placeholder: '请输入您的手机号',
                eleType: 'input',
                type: 'number',
                name: 'phone',
                val: '',
                isShow: 0
            }
        ],
        resultList: [
            {
                title: '申请成功',
                content: '正在安排人员为您开启量房服务',
                isType: 0
            },
            {
                title: '领取成功',
                content: '【齐装网】给小主请安！小齐已经接到您的装修圣旨，正马不停蹄赶来您的身边，并会在第一时间与您联系，请您注意接听哦，如有疑问请致电400-6969-336。',
                isType: 1
            },

            {
                title: '领取成功',
                content: '',
                isType: 2
            }
        ],
        currentResult: '',
        isHide: true,
        selectText: '',
        cityId: '',
        isPhonex: false,
        prevIndex: '0',
        cityIndex: '0',
        valuecity: null,
        val: [],
        src: '', //发单标识
        site_source: '', // 位置标识
        result_flag: false,
        bj_detail: {}, //快速报价 详细
        total: '',
    }, // 私有数据，可用于模版渲染

    // 生命周期函数，可以为函数，或一个在methods段中定义的方法名
    attached: function () {
        this.setData({
            src: app.globalData.sourceMark,
            isPhonex: app.globalData.isIphoneX ? "isPhonex" : ''
        })
    },

    detached: function () {
    },
    methods: {
        // 底部按钮切换
        openOrderWin: function (e) {
            console.log(e)
            this.setData({
                result_flag: false
            })
            let currIndex = e
            for (let i = 0; i < this.data.btns.length; i++) {
                if (i == currIndex) {
                    this.setData({
                        ['btns[' + i + '].active']: !this.data.btns[i].active,
                        currentWin: i,
                        currentResult: i,
                        isOpenMask: !this.data.btns[i].active,
                        site_source: this.data.btns[i].site_source
                    })
                } else {
                    this.setData({
                        ['btns[' + i + '].active']: false
                    })
                }
            }
        },
        formSubmitHandle: function (e) {
            let that = this;
            let city = this.data.selectText;
            let area = e.detail.value.area;
            let phone = e.detail.value.phone;
            let company = e.detail.value.company;
            let linkman = e.detail.value.linkman;
            let srcRemark = this.data.src;
            //所在城市
            if (!app.checkFun.checkNull(city, "请选择城市")) return
            //房屋面积
            if (!app.checkFun.checkNull(area, '请输入房屋面积') || !app.checkFun.checkNumber(area)) return
            //联系方式
            if (!app.checkFun.checkNull(phone, '请输入您的手机号') || !app.checkFun.checkPhone(phone)) return
            //发单报价
            fadanHandle(
                '/v1/fb?source=' + parseInt(this.data.site_source), {
                cs: this.data.cityId,
                mianji: area,
                tel: phone,
                src: srcRemark,//渠道标识
                source: parseInt(this.data.site_source)//位置标识
            }).then(data => {
                if (data.error_code == 0) {
                    if (this.data.currentWin == 2) { // 如果是快速报价
                        getOrderDetail('/v1/quote', {
                            tel: phone,
                            source: this.data.site_source
                        }).then(data => {
                            if (data.error_code == 0) {
                                this.setData({
                                    bj_detail: data.date.child,
                                    total: data.date.total,
                                    result_flag: true
                                })
                            }
                        }).catch(res => {
                            console.log(res)
                        })
                    } else {
                        this.setData({
                            result_flag: true
                        })
                    }
                } else {
                    swan.showToast({
                        title: data.error_msg,
                        icon: 'none',
                        mask: false,
                    });
                }
            }).catch(res => {
                swan.showToast({
                    title: "网络异常",
                    icon: 'none',
                    mask: false,
                });
            })
        },
        selectHandle: function () {
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
                    cityId: res.detail[0],
                    selectText: res.detail[1]
                })
            }
        },
        //关闭所有弹窗
        closeAllWin: function (e) {
            for (let i = 0; i < this.data.btns.length; i++) {
                this.setData({
                    ['btns[' + i + '].active']: false
                })
            }
            this.setData({
                result_flag: false,
                isOpenMask: false,
                isHide: true
            })
        }
    }
});
