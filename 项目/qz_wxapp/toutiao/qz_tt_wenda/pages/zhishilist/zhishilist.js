import { getZhishiListData } from '../../utils/api.js'
const $time = require('../../utils/utils.js')
Page({
    data: {
        group: "",
        currentPage: 1,
        pageNumber: 1,
        href: '',
        wendaList: [{ gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }, { gujia: "no-data" }],
        nodata: false,
        ifdata:true
    },
    onLoad: function (options) {
        // 监听页面加载的生命周期函数
        this.setData({
            group: options.group,
            currentPage: options.p || 1,
            href: options.href
        })
        this.getDataList()
    },
    getDataList: function () {
        getZhishiListData(
            '/tt/v1/zhishi/list',
            {
                page: this.data.currentPage,
                name: this.data.href
            }).then(res => {
                if (res.error_code === 0) {
                    let resData = []
                    resData = res.data.list
                    if(resData.length == 0){
                       this.setData({
                           ifdata:false
                       })
                    }
                    this.setData({
                        wendaList: resData,
                        pageNumber: res.data.page.page_total_number,
                        nodata: resData.length === 0
                    })

                } else {
                    tt.showToast({
                        title: '网络错误，请稍后重试',
                        icon: 'none'
                    })
                }
            }).catch(res => {
                console.log(res, 2)
                tt.showToast({
                    title: '网络错误，请稍后重试',
                    icon: 'none'
                })
            })
    },
    onReady: function () {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShow: function () {
        // 监听页面显示的生命周期函数
        //  tt.setPageInfo(this.data[this.data.group])
    },
    onHide: function () {
        // 监听页面隐藏的生命周期函数
    },
    onUnload: function () {
        // 监听页面卸载的生命周期函数
    },
    onPullDownRefresh: function () {
        // 监听用户下拉动作
    },
    onReachBottom: function () {
        // 页面上拉触底事件的处理函数
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    }
});