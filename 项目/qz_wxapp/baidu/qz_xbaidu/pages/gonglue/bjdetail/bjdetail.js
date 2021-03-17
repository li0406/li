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
        swan.navigateTo({
            url: '/pages/gonglue/zhuangxiubj/zhuangxiubj'
        })
    },
    onLoad: function (options) {
        if (!options.orderid) {
            swan.navigateTo({
                url: '/pages/gonglue/zhuangxiubj/zhuangxiubj'
            })
        }
        var that = this;
        swan.request({
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
    }
});