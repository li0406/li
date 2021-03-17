// pages/zuangxbjxq/zuangxbjxq.js
let app = getApp();
let apiUrl = app.getApiUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        showView1: false,
        showView2: false,
        showView3: false,
        showView4: false,
        showView5: false,
        showView6: false,
        showView7: false,
        showView8: false,
        banbaojia: {},
        halfTotal: '',
        keting: {},
        canting: [],
        zhuwo: [],
        ciwo: [],
        ggwsj: [],
        sdaz: [],
        zhqt: [],
        bookhouse: [],
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad (options) {
        var that = this;
        wx.request({
            url: apiUrl + '/zxjt/details/id/' + options.orderid,
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success (res) {
                console.log(res);
                that.setData({
                    banbaojia: res.data.data.nowdetails,
                    halfTotal: res.data.data.halfTotal
                });
                for (var key in that.data.banbaojia) {
                    for (let k in that.data.banbaojia[key]) {
                        if (that.data.banbaojia[key].id == 1) {
                            that.setData({
                                keting: that.data.banbaojia[key]
                            });
                        } else if (that.data.banbaojia[key].id == 2) {
                            that.setData({
                                canting: that.data.banbaojia[key]
                            });
                        } else if (that.data.banbaojia[key].id == 3) {
                            that.setData({
                                zhuwo: that.data.banbaojia[key]
                            });
                        } else if (that.data.banbaojia[key].id == 3) {
                            that.setData({
                                zhuwo: that.data.banbaojia[key]
                            });
                        } else if (that.data.banbaojia[key].id == 9) {
                            that.setData({
                                ciwo: that.data.banbaojia[key]
                            });
                        } else if (that.data.banbaojia[key].id == 4) {
                            that.setData({
                                ggwsj: that.data.banbaojia[key]
                            });
                        } else if (that.data.banbaojia[key].id == 57) {
                            that.setData({
                                sdaz: that.data.banbaojia[key]
                            });
                        } else if (that.data.banbaojia[key].id == 65) {
                            that.setData({
                                zhqt: that.data.banbaojia[key]
                            });
                        } else if (that.data.banbaojia[key].id == 11) {
                            that.setData({
                                bookhouse: that.data.banbaojia[key]
                            });
                        }
                    }
                }
            }
        });
        showView: (options.showView == "true" ? true : false);
    },
    kindToggle(e) {
        var id = e.currentTarget.id, list = this.data.list;
        for (var i = 0, len = list.length; i < len; ++i) {
            if (list[i].id == id) {
                list[i].open = !list[i].open
            } else {
                list[i].open = false
            }
        }
        this.setData({ list: list });
    },
    onChangeShowState1() {
        var that = this;
        that.setData({
            showView1: (!that.data.showView1),
            showView2: false,
            showView3: false,
            showView4: false,
            showView5: false,
            showView6: false,
            showView7: false,
            showView8: false,
        });
    },
    onChangeShowState2() {
        var that = this;
        that.setData({
            showView2: (!that.data.showView2),
            showView1: false,
            showView3: false,
            showView4: false,
            showView5: false,
            showView6: false,
            showView7: false,
            showView8: false,
        });
    },
    onChangeShowState3() {
        var that = this;
        that.setData({
            showView3: (!that.data.showView3),
            showView2: false,
            showView1: false,
            showView4: false,
            showView5: false,
            showView6: false,
            showView7: false,
            showView8: false,
        });
    },
    onChangeShowState4() {
        var that = this;
        that.setData({
            showView4: (!that.data.showView4),
            showView2: false,
            showView3: false,
            showView1: false,
            showView5: false,
            showView6: false,
            showView7: false,
            showView8: false,
        });
    },
    onChangeShowState5() {
        var that = this;
        that.setData({
            showView5: (!that.data.showView5),
            showView2: false,
            showView3: false,
            showView4: false,
            showView1: false,
            showView6: false,
            showView7: false,
            showView8: false,
        });
    },
    onChangeShowState6() {
        var that = this;
        that.setData({
            showView6: (!that.data.showView6),
            showView2: false,
            showView3: false,
            showView4: false,
            showView5: false,
            showView1: false,
            showView7: false,
            showView8: false,
        });
    },
    onChangeShowState7() {
        var that = this;
        that.setData({
            showView7: (!that.data.showView7),
            showView2: false,
            showView3: false,
            showView4: false,
            showView5: false,
            showView6: false,
            showView1: false,
            showView8: false,
        });
    },
    onChangeShowState8() {
        var that = this;
        that.setData({
            showView8: (!that.data.showView8),
            showView2: false,
            showView3: false,
            showView4: false,
            showView5: false,
            showView6: false,
            showView7: false,
            showView1: false,
        });
    },
    /**
     * 跳转到装修报价页面
     */
    zxbj() {
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj'
        });
    }
})