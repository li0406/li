//index.js
//获取应用实例
let app = getApp();
let apiUrl = app.getApiUrl();
const zxsApiUrl = app.getZxsApiUrl();
Page({
  data: {
    version: app.globalData.version,
    userInfo: {
      nickName: '登录 / 注册',
      avatarUrl: '', //头像
      phone: '', //手机号
    },
    userId: '', //???
    login: '' //???
  },
  onLoad: function (option) {
    if (option.pc == 'call') {
      this.phoneCall()
    }
  },
  onShow: function () {
    let that = this;
    this.getUserInfos()
  },
  consultOnline: function(){
    app.sr.track('start_consult', {
      "action_type": "consult_online",
    })    
  },
  /**
   * 用户点击我的收藏模块跳转到收藏详情页
   */
  toUserMark: function () {
    let that = this;
    if (!this.data.userId) {
      app.newSq(that, function (data) {
        if (data !== 0) {
          that.setData({
            userInfo: data,
            userId: data.userId
          })
          wx.navigateTo({
            url: '../user_mark/user_mark?userId=' + that.data.userId
          })
        }
      });
    } else {
      wx.navigateTo({
        url: '../user_mark/user_mark?userId=' + that.data.userId
      })
    }
  },
  //装修现场
  toMyLive: function () {
    wx.showLoading({
      title: '加载中',
    })
    let that = this;
    let token = wx.getStorageSync('jwt_token')
    if (token == '') {
      wx.hideLoading()
      wx.navigateTo({
        url: '../login/login'
      })
    } else {
      wx.request({
        url: zxsApiUrl + '/business/worksite/user/get_lastorder_status', //查询是否发单
        method: 'GET',
        data: {
          day: 14,
          from_type: 1
        },
        header: {
          token: token,
          'content-type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          wx.hideLoading()
          if (res.data.error_code === 0) {
            if (res.data.data.order_status == 4) {
              wx.navigateTo({
                url: '../livedetail/livedetail?phone=' + that.data.userInfo.phone + '&order_status=' + res.data.data.order_status,
              })
            } else {
              wx.navigateTo({
                url: '../livereserve/livereserve?phone=' + that.data.userInfo.phone + '&order_status=' + res.data.data.order_status,
              })
            }
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
    }
  },
  //我的围观
  toMyMatch: function () {
    let that = this;
    let token = wx.getStorageSync('jwt_token')
    wx.showLoading()
    if (token == '') {
      wx.hideLoading()
      wx.navigateTo({
        url: '../login/login'
      })
    } else {
      wx.hideLoading()
      wx.navigateTo({
        url: '../watch/watch'
      })
    }
  },
  phoneCall: function () {
    wx.makePhoneCall({
      phoneNumber: '400-6969-336' //拨打400电话
    })
    app.sr.track('start_consult', {
      "action_type": "consult_call",
    })    
  },
  login: function () {
    let token = wx.getStorageSync('jwt_token')
    if (token == '') {
      wx.navigateTo({
        url: '../login/login',
      })
    }
  },
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') { }
    return {
      title: '齐装网装修家居',
      path: 'pages/user/user',
      success: function (res) { },
      fail: function (res) { }
    }
  },
  //退出
  toExit: function () {
    let that = this
    wx.showModal({
      title: "退出登录？",
      content: '',
      success(res) {
        if (res.confirm) {
          let token = wx.getStorageSync('jwt_token')
          if (token) {
            wx.request({
              url: zxsApiUrl + '/uc/v1/loginout',
              method: 'POST',
              data: {},
              header: {
                'Content-Type': 'application/x-www-form-urlencoded',
                token: token
              },
              dataType: 'json',
              responseType: 'text',
              success: function (res) {
                if (res.data.error_code === 0) {
                  wx.removeStorageSync('jwt_token')
                  wx.removeStorageSync('userInfo')
                  that.setData({
                    userInfo: {
                      nickName: '登录 / 注册',
                      avatarUrl: '', //头像
                      phone: '', //手机号
                    }
                  })
                  wx.showToast({
                    title: '已退出登陆',
                    icon: 'none',
                    duration: 2000
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
            that.setData({
              userInfo: {
                nickName: '登录 / 注册',
                avatarUrl: '', //头像
              }
            })
            wx.removeStorage({
              key: 'userInfo',
              success (res) {
                wx.showToast({
                  title: '已退出登陆',
                  icon: 'none',
                  duration: 2000
                })
              }
          });
          }

        } else if (res.cancel) {
          // console.log('用户点击取消')
        }
      }
    })
  },
  //获取用户信息
  getUserInfos: function () {
    let that = this;
    let token = wx.getStorageSync('jwt_token')
    wx.getStorage({
      key: 'userInfo',
      success: function (res) {
        // console.log("获取缓存信息==>", res)
        that.setData({
          userInfo: {
            nickName: res.data.userInfo.nickName,
            avatarUrl: res.data.userInfo.avatarUrl //头像
          }
        })
      }
    })
    if (token != '') {
      wx.request({
        url: zxsApiUrl + '/uc/v1/userinfo',
        method: 'GET',
        data: {},
        header: {
          token: token
        },
        dataType: 'json',
        responseType: 'text',
        success: function (res) {
          if (res.data.error_code === 0) {
            var userinfo = res.data.data.userinfo
            if(!userinfo.avatar && !that.data.userInfo.avatarUrl){
              that.setData({
                userInfo: {
                  nickName: userinfo.nickname, //昵称
                  phone: userinfo.phone, //手机号
                  avatarUrl: userinfo.avatar //头像
                }
              })
            }else{
              that.setData({
                ["userInfo.phone"]: userinfo.phone, //手机号
              })
            }            
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
    }
    
  }
})