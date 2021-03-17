//index.js
//获取应用实例
import {
  login
} from "../../utils/api.js"
const app = getApp()

Page({
  data: {
    motto: '',
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    codeId: null,
    noInfo: '',
    go: true,
    isClicked: false
  },
  //事件处理函数
  bindViewTap: function() {
    wx.navigateTo({
      url: '../logs/logs'
    })
  },
  onLoad: function(query) {
    let codeId = app.globalData.codeId ? app.globalData.codeId : ''
    this.setData({
      codeId: codeId
    })
  },
  onShow: function() {
    // this.reLogin()
  },
  getUserInfo: function(e) {
    let info = e.detail.userInfo
    if (!info) {
      return
    }
    app.globalData.userInfo = e.detail.userInfo
    this.setData({
      userInfo: e.detail.userInfo,
      hasUserInfo: true
    })
    wx.login({
      success: res => {
        login('/business/worksite/applet/login', {
          appid: 'wxbd6cab509c23991c',
          code: res.code,
          nikename: e.detail.userInfo.nickName,
          headimg: e.detail.userInfo.avatarUrl
        }, {
          'content-type': 'application/x-www-form-urlencoded'
        }).then(data => {
          if (data.error_code == 0) {
            wx.setStorage({
              key: 'info',
              data: {
                token: data.data.token,
                nikename: e.detail.userInfo.nickName,
                headimg: e.detail.userInfo.avatarUrl
              },
            })
            let codeId = this.data.codeId ? this.data.codeId : ''
            setTimeout(function() {
              wx.navigateTo({
                url: '../home/home?id=' + codeId,
              })
            }, 500);
          } else {
            wx.showToast({
              title: data.error_msg,
              icon: 'none',
              success: function() {
                  if (!this.data.isClicked) {
                      this.setData({
                          isClicked: true
                      })
                      setTimeout(function () {
                          wx.navigateTo({
                              url: '../login/login',
                          })
                      }, 500);
                  }
              }
            })
            this.setData({
              motto: '',
              noInfo: '暂无权限',
              go: true
            })
          }
        })
      }
    })
  },
  toItem: function(e) {
    let url = e.currentTarget.dataset.url
    wx.redirectTo({
      url: url + '?id=' + this.data.codeId
    })
  },

  reLogin() {
    let that = this
    let userInfo = that.data.userInfo
    if (userInfo.nickName == undefined) {
      that.setData({
        hasUserInfo: false
      })
      return false
    }
    wx.login({
      success: res => {
        login('/business/worksite/applet/login', {
          appid: 'wxbd6cab509c23991c',
          code: res.code,
          nikename: userInfo.nickName,
          headimg: userInfo.avatarUrl
        }, {
          'content-type': 'application/x-www-form-urlencoded'
        }).then(data => {
          if (data.error_code == 0) {
            wx.setStorage({
              key: 'info',
              data: {
                token: data.data.token,
                nikename: userInfo.nickName
              },
            })
            let codeId = this.data.codeId ? this.data.codeId : ''
            setTimeout(function() {
              wx.navigateTo({
                url: '../home/home?id=' + codeId,
              })
            }, 500);
          } else {
            wx.showToast({
              title: data.error_msg,
              icon: 'none',
              success: function() {
                setTimeout(function() {
                  wx.navigateTo({
                    url: '../login/login',
                  })
                }, 2000);
              }
            })
            this.setData({
              motto: '',
              noInfo: '暂无权限',
              go: true
            })
          }
        })

      }
    })
  }
})