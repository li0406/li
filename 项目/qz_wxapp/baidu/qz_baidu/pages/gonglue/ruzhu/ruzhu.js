import {merchantHandle} from '../../../utils/api';

const app = getApp()
Page({

    // 页面的初始数据
    data: {
        switchAutoPlayStatus: true,
        autoPlayInterval: 2000,
        switchDuration: 500,
        isHide: true,
        cityName: '',
        city: "",
        cityId: '',
        name: '',
        linkman: '',
        tel: '',
        cs: '',
        result: false,
        defineArea: false,
    },
    //选择城市
    closeSelect: function (res) {
        this.setData({
            isHide: true,
            cityName: res.detail[1],
            cityId: res.detail[0]
        })
    },
    onSelectCityHandle: function () {
        this.setData({
            isHide: false
        })
    },
    SubmitHandle: function (e) {
        let that = this
        let city = ''
        if (!this.data.defineArea) {
            city = e.detail.value.city
        } else {
            city = e.detail.value.defineCity
        }

        let linkman = e.detail.value.linkman
        let phone = e.detail.value.tel
        let name = e.detail.value.name
        //公司名称
        if (!app.checkFun.checkCompanyName(name, "请输入正确的公司名称")) return
        //用户名称
        if (!app.checkFun.checkConnectName(linkman, "请输入正确的称呼")) return
        //联系方式
        if (!app.checkFun.checkPhone(phone)) return
        merchantHandle(
            '/v1/consult',
            {
                name: name,
                linkman: linkman,
                tel: phone,
                cs: this.data.cityId,
                sorce: 19062617
            })
            .then(res => {
                if (res.error_code == 0) {
                    that.setData({
                        result: true
                    })
                    setTimeout(() => {
                        that.setData({
                            result: false,
                            defineArea: false,
                            name: '',
                            linkman: '',
                            tel: '',
                            cs: '',
                            cityName: "B 北京市 北京",
                            cityId: "110100"
                        })
                    }, 3000)
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
    },
    screenHandle:function(){
         swan.pageScrollTo({
            scrollTop: 3000,
            duration: 50,
            success: function (res) {
                console.log('pageScrollTo success', res);
            },
            fail: function (err) {
                console.log('pageScrollTo fail', err);
            }
        });
    },
    inputHandle: function (e) {
        this.setData({
            name: e.detail.value,
        })
    },
    inputlxHandle: function (e) {
        this.setData({
            linkman: e.detail.value,
        })
    },
    telHandle: function (e) {
        this.setData({
            tel: e.detail.value,
        })
    },
    defineHandle: function () {
        this.setData({
            defineArea: true
        })
    },

    // 页面的生命周期函数 – 监听页面加载
    onLoad(res) {
        swan.setPageInfo({
            title: '齐装网',
            keywords: '齐装，齐装网，装修，装修网，装修公司',
            description: '资深家居装饰装修平台，装修难题我帮你。',
            image:'http://staticqn.qizuang.com/webstatic/img/xcx/baidu/rzbanner.jpg'
        })
    },

    // 页面的生命周期函数 – 监听页面初次渲染完成
    onReady(res) {
      
    },

    // 页面的生命周期函数 – 监听页面显示
    onShow(res) {
        swan.setPageInfo({
            title: '齐装网',
            keywords: '齐装，齐装网，装修，装修网，装修公司',
            description: '资深家居装饰装修平台，装修难题我帮你。',
            image:'http://staticqn.qizuang.com/webstatic/img/xcx/baidu/rzbanner.jpg'
        })
    },

    // 页面的生命周期函数 – 监听页面隐藏
    onHide(res) {

    },

    // 页面的生命周期函数 – 监听页面卸载
    onUnload(res) {

    },

    // 页面的生命周期函数 – 监听页面重启，单击重启按钮时触发
    onForceReLaunch(res) {

    },

    // 页面的事件处理函数 – 监听用户下拉动作
    onPullDownRefresh(res) {

    },

    // 页面的事件处理函数 – 上拉触底事件的处理函数
    onReachBottom(res) {

    },

    // 页面的事件处理函数 – 用户点击右上角转发
    onShareAppMessage(res) {

    },

    // 页面的事件处理函数 – 页面滚动触发事件的处理函数
    onPageScroll(res) {

    },

    // 页面的事件处理函数 – 当前是 tab 页时，点击 tab 时触发
    onTabItemTap(res) {

    }
});
