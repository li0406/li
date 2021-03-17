//app.js
import config from './utils/config.js'
App({
  onLaunch: function () {
    // 展示本地存储能力
    // var logs = wx.getStorageSync('logs') || []
    // logs.unshift(Date.now())
    // wx.setStorageSync('logs', logs)

    // 登录
    wx.login({
      success: res => {
        // console.log(res)
        this.globalData.wxCode = res.code
      }
    })
    // 获取用户信息
    wx.getSetting({
      success: res => {
        if (res.authSetting['scope.userInfo']) {
          // 已经授权，可以直接调用 getUserInfo 获取头像昵称，不会弹框
          wx.getUserInfo({
            success: res => {
              // 可以将 res 发送给后台解码出 unionId
              this.globalData.userInfo = res.userInfo

              // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
              // 所以此处加入 callback 以防止这种情况
              if (this.userInfoReadyCallback) {
                this.userInfoReadyCallback(res)
              }
            }
          })
        }
      }
    })
    // 检测并获取小程序更新 api 说明：https://developers.weixin.qq.com/miniprogram/dev/api/getUpdateManager.html
    if (wx.canIUse('getUpdateManager')) { // 基础库 1.9.90 开始支持，低版本需做兼容处理
      const updateManager = wx.getUpdateManager();
      updateManager.onCheckForUpdate(function (result) {
        if (result.hasUpdate) { // 有新版本
          updateManager.onUpdateReady(function () { // 新的版本已经下载好
            wx.showModal({
              title: '更新提示',
              content: '新版本已经准备好，是否重启应用？',
              success: function (result) {
                if (result.confirm) { // 点击确定，调用 applyUpdate 应用新版本并重启
                  updateManager.applyUpdate();
                }
              }
            });
          });
          updateManager.onUpdateFailed(function () { // 新的版本下载失败
            wx.showModal({
              title: '已经有新版本了哟~',
              content: '新版本已经上线啦~，请您删除当前小程序，重新搜索打开哟~'
            });
          });
        }
      });
    } else { // 有更新肯定要用户使用新版本，对不支持的低版本客户端提示
      wx.showModal({
        title: '温馨提示',
        content: '当前微信版本过低，无法使用该应用，请升级到最新微信版本后重试。'
      });
    }
  },

  globalData: {
    userInfo: null,
    baseUrl: config.API_HOST,
    imgBaseUrl: config.STATIC_HOST,
    token: '',
    wxCode: ''
  }
})