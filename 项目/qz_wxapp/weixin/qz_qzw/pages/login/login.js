// pages/login/login.js
//获取应用实例
const app = getApp();
const zxsApiUrl = app.getZxsApiUrl();
const imgUrl = app.getImgUrl();
var config = require('../../config');
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
        time: 60, //验证码时间
        disabled: true,
        jwt_token: '', //用户token
        userInfo: {
            nickName: '', //昵称
            avatarUrl: '', //头像地址
        }
    },
    //用户电话
    userTel: function (e) {
        this.setData({
            teleInfo: e.detail.value
        })
    },
    //验证码输入
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
                    type: 2,
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
                    // console.log(res)
                    if (res.data.error_code === 0) {
                        that.setData({
                            isError: true,
                            errorTit: '验证码已发送',
                            isTime: true
                        })
                    }
                    setTimeout(function () {
                        that.setData({
                            isError: false,
                            errorTit: ''
                        })
                    },1000)

                    let timer = setInterval(function () {
                        let time = that.data.time - 1
                        that.setData({
                            time: time
                        })
                        if (time === 0) {
                            that.setData({
                                isTime: false,
                                time: 60
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
                //验证码登录
                let live_id = that.data.live_id
                wx.login({
                    success(res) {
                        // console.log(res)
                        if (res.code) {
                            //发起网络请求
                            wx.request({
                                url: zxsApiUrl + '/uc/v1/login_applet',
                                method: 'POST',
                                data: {
                                    phone: that.data.teleInfo,
                                    verify_code: that.data.teleCode,
                                    wx_code: res.code
                                },
                                header: {
                                    'content-type': 'application/x-www-form-urlencoded',
                                    'App-From': '678269d281118caeb4e0305396e540d7',
                                    'Addr': 'APPLET_ZXJJ'
                                },
                                dataType: 'json',
                                responseType: 'text',
                                success: function (res) {
                                    // console.log(res)
                                    if (res.data.error_code === 0) {
                                        that.data.jwt_token = res.data.data.jwt_token
                                        wx.setStorageSync('jwt_token', res.data.data.jwt_token)
                                        if (live_id) {
                                            wx.redirectTo({
                                                url: '../siteDetail/siteDetail?live_id=' + live_id
                                            });
                                        }
                                        wx.switchTab({
                                            url: '../user/user',
                                        })

                                    } else {
                                        wx.showToast({
                                            title: res.data.error_msg,
                                            icon: 'none',
                                            duration: 2000
                                        })
                                    }
                                },
                                fail: function (res) { },
                                complete: function (res) { },
                            })
                        } else {
                            console.log('登录失败！' + res.errMsg)
                        }
                    }
                })
            }
        }
    },
    toAccountPwd: function () {
        wx.navigateTo({
            url: '../account_pwd/account_pwd',
        })
    },
    //授权登录
    getUserInfo: function (e) {
        let that = this;
        wx.showLoading();
        // 查看是否授权
        wx.getSetting({
            success(res) {
                console.log(res)
                if (res.authSetting['scope.userInfo']) {
                    // 已经授权，可以直接调用 getUserInfo 获取头像昵称
                    wx.getUserInfo({
                        success: function (res) {
                            that.setData({
                                userInfo: {
                                    nickName: res.userInfo.nickName, //昵称
                                    avatarUrl: res.userInfo.avatarUrl //头像地址
                                }
                            })
                            // 同步缓存
                            wx.setStorageSync('wx_avatar', that.data.userInfo.avatarUrl) //关联手机号用
                            wx.setStorageSync('wx_nickname', that.data.userInfo.nickName) //关联手机号用
                            wx.setStorage({
                                key: 'userInfo',
                                data: res
                            });
                            wx.setStorage({
                                key: 'userId',
                                data: res.userId
                            })
                            wx.login({
                                success(res) {
                                    // console.log('code', res.code)
                                    if (res.code) {
                                        //发起网络请求
                                        wx.request({
                                            url: zxsApiUrl + '/uc/v1/third/party/login_applet',
                                            method: 'POST',
                                            data: {
                                                wx_code: res.code
                                            },
                                            header: {
                                                'content-type': 'application/x-www-form-urlencoded',
                                                'App-From': '678269d281118caeb4e0305396e540d7',
                                                'Addr': 'APPLET_ZXJJ'
                                            },
                                            success: function (res) {
                                                // console.log('res', res)
                                                if (res.data.error_code === 0) {
                                                    that.data.jwt_token = res.data.data.jwt_token
                                                    wx.setStorageSync('jwt_token', res.data.data.jwt_token)
                                                    if (res.data.data.success_code == 1) {
                                                        wx.switchTab({
                                                            url: '../user/user',
                                                        })
                                                    } else if (res.data.data.success_code == 2) {
                                                        // console.log('没有绑定手机号')

                                                        wx.navigateTo({
                                                            url: '../relation/relation',
                                                        })
                                                    }
                                                } else {
                                                    wx.showToast({
                                                        title: "[" + res.data.error_code + "] " + res.data.error_msg,
                                                        icon: 'none',
                                                        duration: 2000
                                                    })
                                                }
                                            },
                                            fail: function (res) {
                                                console.log('fail', res)
                                            },
                                            complete: function (res) { },
                                        })
                                    } else {
                                        console.log('登录失败！' + res.errMsg)
                                    }
                                }
                            })
                        },


                    })
                }
            },
            complete: function (res) {
                wx.hideLoading();
            }
        })
    },
    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        if (app.globalData.userInfo) {
            this.setData({
                userInfo: app.globalData.userInfo,
                hasUserInfo: true
            })
        } else if (this.data.canIUse) {
            // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
            // 所以此处加入 callback 以防止这种情况
            app.userInfoReadyCallback = res => {
                this.setData({
                    userInfo: res.userInfo,
                    hasUserInfo: true
                })
            }
        } else {
            // 在没有 open-type=getUserInfo 版本的兼容处理
            wx.getUserInfo({
                success: res => {
                    app.globalData.userInfo = res.userInfo
                    this.setData({
                        userInfo: res.userInfo,
                        hasUserInfo: true
                    })
                }
            })
        }
        let live_id = options.live_id ? options.live_id : ''
        this.setData({
            live_id: live_id
        })
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