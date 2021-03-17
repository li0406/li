// pages/mylive/mylive.js
const app = getApp();
const zxsApiUrl = app.getZxsApiUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        teleInfo: '',
        teleCode: '',
        errorTit: '',
        isError: false,
        isTime: false,
        time:60
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {

    },
    userTel: function(e) {
        this.setData({
            teleInfo: e.detail.value
        })
    },
    searchOrder: function(e) {
        this.setData({
            teleCode: e.detail.value
        })
    },
    getCode: function() {
        let that = this
        let tel_reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if (that.data.teleInfo == '') {
            that.setData({
                isError: true,
                errorTit: '请输入业主手机号'
            })
            setTimeout(function() {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else if (!tel_reg.test(that.data.teleInfo)) {
            that.setData({
                isError: true,
                errorTit: '请输入正确的业主手机号'
            })
            setTimeout(function() {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else {
            that.setData({
                isError: false,
                errorTit: ''
            })
            wx.request({
                url: zxsApiUrl + '/business/common/send_sms_code',
                method: 'POST',
                data: {
                    phone: that.data.teleInfo,
                    type: 1
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                success: function(res) {
                    if (res.data.error_code == 0) {
                        that.setData({
                            isError: true,
                            errorTit: '验证码已发送'
                        })
                        setTimeout(function() {
                            that.setData({
                                isError: false,
                                errorTit: '',
                                isTime: true
                            })
                        }, 1000)
                    
                        let timer = setInterval(function(){
                            let time = that.data.time - 1
                            that.setData({
                                time: time
                            })
                            if (time === 0){
                                that.setData({
                                    isTime: false,
                                    time:60
                                })
                                clearInterval(timer)
                            }
                        },1000)
                    } else {
                        that.setData({
                            isError: true,
                            errorTit: res.data.error_msg
                        })
                        setTimeout(function() {
                            that.setData({
                                isError: false,
                                errorTit: ''
                            })
                        }, 1000)
                    }
                }
            });
        }
    },
    liveSubmutBtn: function() {
        let that = this
        let tel_reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if (that.data.teleInfo == '') {
            that.setData({
                isError: true,
                errorTit: '请输入业主手机号'
            })
            setTimeout(function() {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else if (!tel_reg.test(that.data.teleInfo)) {
            that.setData({
                isError: true,
                errorTit: '请输入正确的业主手机号'
            })
            setTimeout(function() {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else {
            if (that.data.teleCode == '') {
                that.setData({
                    isError: true,
                    errorTit: '请输入业主验证码'
                })
                setTimeout(function() {
                    that.setData({
                        isError: false,
                        errorTit: ''
                    })
                }, 1000)
            } else {
                wx.request({
                    url: zxsApiUrl + '/business/worksite/user/get_lastorder_status',
                    method: 'GET',
                    data: {
                        tel: that.data.teleInfo,
                        day: 14,
                        sms_code: that.data.teleCode
                    },
                    header: {
                        'content-type': 'application/x-www-form-urlencoded'
                    },
                    success: function(res) {
                        if (res.data.error_code === 0) {
                            if (res.data.data.order_status == 4) {
                                wx.redirectTo({
                                    url: '../livedetail/livedetail?phone=' + that.data.teleInfo + '&order_status=' + res.data.data.order_status,
                                })
                            } else {
                                wx.redirectTo({
                                    url: '../livereserve/livereserve?phone=' + that.data.teleInfo + '&order_status=' + res.data.data.order_status,
                                })
                            }
                        } else {
                            that.setData({
                                isError: true,
                                errorTit: res.data.error_msg
                            })
                            setTimeout(function() {
                                that.setData({
                                    isError: false,
                                    errorTit: ''
                                })
                            }, 1000)
                        }
                    }
                });
            }
        }
    },
    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function() {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function() {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {

    }
})