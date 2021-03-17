Page({
    data: {
        banbaojia: {},
        halfTotal: '',
        keting: {},
        chufang: [],
        woshi: [],
        wsj: [],
        sd: [],
        other: []
    },
    zxbj: function () {
        tt.navigateTo({
            url: '/pages/zhuangxiubj/zhuangxiubj'
        })
    },
    onLoad: function (options) {
        if (!options.orderid) {
            tt.navigateTo({
                url: '/pages/zhuangxiubj/zhuangxiubj'
            })
        }
        var that = this;
        tt.request({
            url: 'https://appapi.qizuang.com/appletorder/detail?order_no=' + options.orderid,
            data: {
                order_no: options.orderid
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success: function (res) {
                if (res.data.status == 1) {
                    that.setData({
                        keting: res.data.data.child.kt,
                        chufang: res.data.data.child.cf,
                        woshi: res.data.data.child.zw,
                        wsj: res.data.data.child.wsj,
                        sd: res.data.data.child.sd,
                        other: res.data.data.child.other,
                        banbaojia: res.data.data.total,
                    })
                } else {
                    that.setData({
                        keting: '11070',
                        chufang: '3542',
                        woshi: '7970.4',
                        wsj: '7084.8',
                        sd: '11068',
                        other: '3542.4',
                        banbaojia: '44280',
                    })
                }

            }
        })
    },
    onReady: function () {
        // 监听页面初次渲染完成的生命周期函数
    },
    onShow: function () {
        // 监听页面显示的生命周期函数
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