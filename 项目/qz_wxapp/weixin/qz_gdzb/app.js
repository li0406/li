//app.js
import config from './utils/config.js'
var utils = require('./utils/util')
App({
  onLaunch: function(options) {
    let scene = decodeURIComponent(options.query.scene)
    let codeId = scene.substring(scene.length - 10)
    // 登录
    wx.login({
      success: res => {}
    })
    // 获取用户信息
    wx.getSetting({
      success: res => {
        if (res.authSetting['scope.userInfo']) {
          wx.getUserInfo({
            success: res => {
              this.globalData.userInfo = res.userInfo
              if (this.userInfoReadyCallback) {
                this.userInfoReadyCallback(res)
              }
            }
          })
        } else {}
      }
    })
  },
  globalData: {
    userInfo: null,
    id: null,
    codeId : null
  }
})