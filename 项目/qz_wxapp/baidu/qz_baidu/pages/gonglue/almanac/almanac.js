import { fadanHandle, getfdDetail } from '../../../utils/api'

const app = getApp()
Page({

    // 页面的初始数据
    data: {
        time_dialog: true, // 开工吉日
        house_dialog: false, //房间朝向
        age_dialog: false, // 年龄/姓氏
        city_dialog: false,// 城市
        part_one: true,
        /*城市*/
        isHide: true, // 城市列表
        cityName: '',
        cityId: '',
        tel: '',
        isChecked: true,
        prePage: false,
        nextPage: true,
        getResult: false,
        resetResult: false,
        result: {},
        others: [],
        step: 1,
        source: '',
        hltime: '',
        steps: [
            {
                id: 0,
                content: 1
            },
            {
                id: 1,
                content: 2
            },
            {
                id: 2,
                content: 3
            },
            {
                id: 3,
                content: 4
            },
        ],
        //装修时间
        zxTime: [
            {
                id: 1,
                content: '半个月内',
                checked: false
            },
            {
                id: 2,
                content: '1个月内',
                checked: false
            },
            {
                id: 3,
                content: '3个月内',
                checked: false
            },
            {
                id: 4,
                content: '3个月以上',
                checked: false
            },
        ],
        //房屋朝向
        fwcx: [
            {
                id: 0,
                image_1: '/images/cx_1.png',
                image_2: '/images/cx_1_1.png',
                checked: false,
                content: '朝南'
            },
            {
                id: 1,
                image_1: '/images/cx_2.png',
                image_2: '/images/cx_2_1.png',
                checked: false,
                content: '朝北'
            },
            {
                id: 2,
                image_1: '/images/cx_3.png',
                image_2: '/images/cx_3_1.png',
                checked: false,
                content: '朝东'
            },
            {
                id: 3,
                image_1: '/images/cx_4.png',
                image_2: '/images/cx_4_1.png',
                checked: false,
                content: '朝西'
            },
            {
                id: 4,
                image_1: '/images/cx_5.png',
                image_2: '/images/cx_5_1.png',
                checked: false,
                content: '不太清楚'
            },
        ],
        //年龄
        inputContent: '',
        ages: [
            {
                id: 0,
                image_1: '/images/old_1.png',
                image_2: '/images/old_1_1.png',
                checked: false,
                content: '25岁以下'
            },
            {
                id: 1,
                image_1: '/images/old_2.png',
                image_2: '/images/old_2_1.png',
                checked: false,
                content: '25~35岁'
            },
            {
                id: 2,
                image_1: '/images/old_3.png',
                image_2: '/images/old_4_1.png',
                checked: false,
                content: '35~45岁'
            },
            {
                id: 3,
                image_1: '/images/old_4.png',
                image_2: '/images/old_4_1.png',
                checked: false,
                content: '45岁以上'
            },
        ]


    },
    inputHandle: function (e) {
        let inputContent = e.detail.value
        if (e.detail.cursor != 0) {
            if (!app.checkFun.checkNull(inputContent, "请输入正确的名称，只支持中文和英文")) return
            this.setData({
                inputContent: e.detail.value
            })
        } else {
            this.setData({
                inputContent: ''
            })
        }
    },
    //选择日期
    checkHandle: function (e) {
        // let current = e.target.id
        let current = e.detail.value
        let tempData = this.data.zxTime
        tempData.filter(item => {
            item.checked = false
            if (item.id == current) {
                item.checked = true
            }
        })
        this.setData({
            zxTime: tempData
        })
    },
    //房屋朝向
    fwCheckHandle: function (e) {
        // let current = e.target.id
        let current = e.detail.value
        let tempData = this.data.fwcx
        tempData.filter(item => {
            item.checked = false
            if (item.id == current) {
                item.checked = true
            }
        })
        this.setData({
            fwcx: tempData
        })
    },
    //年龄
    ageCheckhandle: function (e) {
        // let current = e.target.id
        let current = e.detail.value
        let tempData = this.data.ages
        tempData.filter(item => {
            item.checked = false
            if (item.id == current) {
                item.checked = true
            }
        })
        this.setData({
            ages: tempData
        })
    },
    //重测
    resetCountHandle: function () {
        let zxTime = this.data.zxTime
        zxTime.map(item => {
            item.checked = false
        })
        this.setData({
            zxTime
        })
        let fwcx = this.data.fwcx
        fwcx.map(item => {
            item.checked = false
        })
        this.setData({
            fwcx
        })
        let ages = this.data.ages
        ages.map(item => {
            item.checked = false
        })
        this.setData({
            ages
        })
        this.setData({
            part_one: true,
            prePage: false,
            nextPage: true,
            getResult: false,
            resetResult: false,
            city_dialog: false,
            time_dialog: true,
            house_dialog: false,
            age_dialog: false,
            step: 1,
            tel: '',
            inputContent: ''
        })
    },
    //下一页
    getNextPageHandle: function (e) {
        let step = this.data.step
        let checked
        switch (step) {
            case 1:
                checked = this.data.zxTime.some(item => {
                    if (item.checked == true) {
                        this.setData({
                            hltime: item.id
                        })
                        return true
                    }
                    return false
                })
                if (checked) {
                    this.setData({
                        prePage: true,
                        time_dialog: false,
                        house_dialog: true,
                        step: this.data.step + 1
                    })
                } else {
                    swan.showToast({
                        title: '请选择本题选项1！',
                        icon: 'none'
                    })
                }
                break;
            case 2:
                checked = this.data.fwcx.some(item => {
                    return item.checked == true
                })
                if (checked) {
                    this.setData({
                        prePage: true,
                        time_dialog: false,
                        house_dialog: false,
                        age_dialog: true,
                        step: this.data.step + 1
                    })
                } else {
                    swan.showToast({
                        title: '请选择本题选项！',
                        icon: 'none'
                    })
                }
                break;
            case 3:
                let reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
                checked = this.data.ages.some(item => {
                    if (item.checked) {
                        item.checked = true
                        return true
                    }
                    return false
                })
                if (this.data.inputContent.trim() == '' || !reg.test(this.data.inputContent)) {
                    swan.showToast({
                        title: '请输入正确的名称，只支持中文和英文！',
                        icon: 'none'
                    })
                    this.setData({
                        inputContent: ''
                    })
                    return
                }
                if (checked && this.data.inputContent.trim() != '') {
                    this.setData({
                        prePage: true,
                        time_dialog: false,
                        house_dialog: false,
                        age_dialog: false,
                        city_dialog: true,
                        step: this.data.step + 1
                    })
                } else {
                    swan.showToast({
                        title: '请选择本题选项！',
                        icon: 'none'
                    })
                }
                break;
            case 4:
                if (this.data.tel == '' || this.data.tel.length < 11) {
                    swan.showToast({
                        title: '请输入您的手机号码！',
                        icon: 'none'
                    })
                    return
                }
                
                let res = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");

                if(!res.test(this.data.tel)){                   
                     swan.showToast({
                        title: '请输入您正确的手机号',
                        icon:"none"
                    });
                    return false
                }
                if (this.data.cityId == '') {
                    swan.showToast({
                        title: '请选择城市',
                        icon: 'none'
                    })
                    return
                }
                if (!this.data.isChecked) {
                    swan.showToast({
                        title: '请勾选我已阅读并同意齐装网的《免责声明》！',
                        icon: 'none'
                    })
                    return
                }
                fadanHandle(
                    '/v1/hl_fb/?src='+app.globalData.sourceMark,
                    {
                        tel: this.data.tel,
                        cs: this.data.cityId,
                        name: this.data.inputContent,
                        source: '19062627',
                        hltime: this.data.hltime,
                        src:app.globalData.sourceMark
                    }
                )
                    .then(res => {
                        if (res.error_code == 0) {
                            getfdDetail(
                                '/v1/hl_component/?src='+app.globalData.sourceMark,
                                {
                                    tel: this.data.tel,                                    
                                    src:app.globalData.sourceMark
                                }
                            ).then(res => {
                                this.setData({
                                    result: res.data.current,
                                    others: res.data.other
                                })
                            })
                            this.setData({
                                part_one: false,
                                prePage: false,
                                nextPage: false,
                                getResult: false,
                                resetResult: true
                            })
                        } else {
                            swan.showToast({
                                title: res.error_msg,
                                icon: 'none'
                            })
                        }
                    })
                    .catch(error => {
                        swan.showToast({
                            title: error,
                            icon: 'none'
                        })
                    })
                break;
            default:
                break;
        }
    },
    //上一页
    preHandle: function (e) {
        let temp = this.data.step
        switch (temp) {
            case 1:
                break;
            case 2:
                this.setData({
                    part_one: true,
                    time_dialog: true,
                    house_dialog: false,
                    age_dialog: false,
                    city_dialog: false,
                    step: --this.data.step
                })
                break;
            case 3:
                this.setData({
                    part_one: true,
                    time_dialog: false,
                    house_dialog: true,
                    age_dialog: false,
                    city_dialog: false,
                    step: --this.data.step
                })
                break;
            case 4:
                this.setData({
                    part_one: true,
                    time_dialog: false,
                    house_dialog: false,
                    age_dialog: true,
                    city_dialog: false,
                    step: --this.data.step
                })
                break;
            default:
                this.setData({
                    part_one: true,
                    time_dialog: true,
                    house_dialog: false,
                    age_dialog: false,
                    city_dialog: false,
                    step: 1
                })
                break;
        }


    },

    // 页面的生命周期函数 – 监听页面加载
    onLoad: (res) => {

    },
    onSelectCityHandle: function () {
        this.setData({
            isHide: false
        })
    },
    closeSelect: function (res) {
        this.setData({
            isHide: true,
            cityName: res.detail[1],
            cityId: res.detail[0]
        })
    },
    onChangeHandle: function (e) {
        if (this.data.isChecked) {
            this.setData({
                isChecked: false
            })
        } else {
            this.setData({
                isChecked: true
            })
        }

    },
    telHandle: function (e) {
        let tel = e.detail.value
        // if (tel.length >= 11) {
        //     if (!app.checkFun.checkNull(tel, '请输入您的手机号') || !app.checkFun.checkPhone(tel)) return
        // }
        this.setData({
            tel: tel
        })
    },
    // 页面的生命周期函数 – 监听页面初次渲染完成
    onReady: (res) => {

    },

    // 页面的生命周期函数 – 监听页面显示
    onShow: (res) => {

    },

    // 页面的生命周期函数 – 监听页面隐藏
    onHide: (res) => {

    },

    // 页面的生命周期函数 – 监听页面卸载
    onUnload: (res) => {

    },

    // 页面的生命周期函数 – 监听页面重启，单击重启按钮时触发
    onForceReLaunch: (res) => {

    },

    // 页面的事件处理函数 – 监听用户下拉动作
    onPullDownRefresh: (res) => {

    },

    // 页面的事件处理函数 – 上拉触底事件的处理函数
    onReachBottom: (res) => {

    },

    // 页面的事件处理函数 – 用户点击右上角转发
    onShareAppMessage: (res) => {

    },

    // 页面的事件处理函数 – 页面滚动触发事件的处理函数
    onPageScroll: (res) => {

    },

    // 页面的事件处理函数 – 当前是 tab 页时，点击 tab 时触发
    onTabItemTap: (res) => {

    }
});
