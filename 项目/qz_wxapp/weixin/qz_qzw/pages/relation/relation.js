// pages/relation/relation.js
//获取应用实例
const app = getApp();
const zxsApiUrl = app.getZxsApiUrl();
Page({

    /**
     * 页面的初始数据
     */
    data: {
        teleInfo: '', //电话号
        teleCode: '', //验证码
        errorTit: '',
        isTime: false,
        isError: false,
        wx_code_time: 60, //验证码时间
    },
    //用户电话
    userTel: function (e) {
        console.log(e.detail.value)
        this.setData({
            teleInfo: e.detail.value
        })
    },
    searchOrder: function (e) {
        this.setData({
            teleCode: e.detail.value
        })
    },
    //获取验证码
    getCode: function () {
        let that = this
        let tel_reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if (that.data.teleInfo == '') {
            that.setData({
                isError: true,
                errorTit: '请输入手机号'
            })
            setTimeout(function () {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else if (!tel_reg.test(that.data.teleInfo)) {
            that.setData({
                isError: true,
                errorTit: '请输入正确的手机号'
            })
            setTimeout(function () {
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
                url: zxsApiUrl + '/uc/v1/msg/send',
                method: 'POST',
                data: {
                    phone: that.data.teleInfo,
                    type: 6,
                    verify_type: 2, //验证图形验证码
                    challenge: 'challenge', //极验的challenge【app/PC】使用
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded',
                    'App-Env': 'appName=applet',
                    'Addr': 'APPLET_ZXJJ',
                    'App-From': '678269d281118caeb4e0305396e540d7',
                },
                success: function (res) {
                    that.setData({
                        isError: true,
                        errorTit: '验证码已发送'
                    })
                    setTimeout(function () {
                        that.setData({
                            isError: false,
                            errorTit: '',
                            isTime: true
                        })
                    }, 1000)
                    let timer = setInterval(function () {
                        let wx_code_time = that.data.wx_code_time - 1
                        that.setData({
                            wx_code_time: wx_code_time
                        })
                        if (wx_code_time === 0) {
                            that.setData({
                                isTime: false,
                                wx_code_time: 60
                            })
                            clearInterval(timer)
                        }
                    }, 1000)
                }
            });
        }
    },
    //登陆
    loginSubmutBtn: function () {
        let that = this
        let tel_reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if (that.data.teleInfo == '') {
            that.setData({
                isError: true,
                errorTit: '请输入手机号'
            })
            setTimeout(function () {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else if (!tel_reg.test(that.data.teleInfo)) {
            that.setData({
                isError: true,
                errorTit: '请输入正确的手机号'
            })
            setTimeout(function () {
                that.setData({
                    isError: false,
                    errorTit: ''
                })
            }, 1000)
        } else {
            if (that.data.teleCode == '') {
                that.setData({
                    isError: true,
                    errorTit: '请输入验证码'
                })
                setTimeout(function () {
                    that.setData({
                        isError: false,
                        errorTit: ''
                    })
                }, 1000)
            } else {
                // 关联手机号
                wx.login({
                    success(res) {
                        if (res.code) {
                            //发起网络请求
                            wx.request({
                                url: zxsApiUrl + '/uc/v1/third/party/phone_applet',
                                method: 'POST',
                                data: {
                                    phone: that.data.teleInfo,  //手机号
                                    verify_code: that.data.teleCode, //验证码
                                    wx_code: res.code, //小程序登录code
                                    third_nick_name: wx.getStorageSync('wx_nickname'), //昵称
                                    avatar: wx.getStorageSync('wx_avatar')//头像
                                },
                                header: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'App-From':'678269d281118caeb4e0305396e540d7',
                                    'Addr':'APPLET_ZXJJ'
                                },
                                success: function (res) {
                                    if (res.data.error_code === 0) {
                                        wx.setStorageSync('jwt_token', res.data.data.jwt_token)
                                        wx.switchTab({
                                            url: '../user/user',
                                        })
                                    } else {
                                        that.setData({
                                            isError: true,
                                            errorTit: res.data.error_msg
                                        })
                                        setTimeout(function () {
                                            that.setData({
                                                isError: false,
                                                errorTit: ''
                                            })
                                        }, 1000)
                                    }
                                }
                            });
                        } else {
                            console.log('登录失败！' + res.errMsg)
                        }
                    }
                })
                
            }
        }
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {

    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function () {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function () {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    }
})