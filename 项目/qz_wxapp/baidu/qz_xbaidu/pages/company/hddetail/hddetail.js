import { hdDetail } from '../../../utils/api.js'
Page({
    data: {
        id: '',
        info: '',
        content: ''
    },
    onLoad: function (xoptions) {
        // 监听页面加载的生命周期函数
        this.setData({
            id: xoptions.id
        })
        this.hdDetail()
    },
    hdDetail: function() {
        hdDetail(
            '/bd/v1/company/activity_details',
            {
                id: this.data.id
            }
        ).then(data => {
            if(data.error_code === 0){
                let str = data.data.detail.text.replace(/<img[^>]*>/gi, function (match, capture) {
                    return match.replace(/(<img[^>]*)(\/?>)/gi, "$1width='100%' $2") // 添加width="100%"
                })
                this.setData({
                    info: data.data.detail,
                    content: str
                })
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
                    image: []
                })
            }
        })
    },
    onReady: function() {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShow: function() {
        // 监听页面显示的生命周期函数
    },
    onHide: function() {
        // 监听页面隐藏的生命周期函数
    },
    onUnload: function() {
        // 监听页面卸载的生命周期函数
    },
    onPullDownRefresh: function() {
        // 监听用户下拉动作
    },
    onReachBottom: function() {
        // 页面上拉触底事件的处理函数
    },
    onShareAppMessage: function () {
        // 用户点击右上角转发
    }
});