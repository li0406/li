// pages/tongji/tongji.js
import { miniDetails } from "../../utils/api.js"
import { STATIC_HOST } from '../../utils/config.js'
Page({
  data: {
    List:[]
  },
  toItem: function(e) {
    let url = e.currentTarget.dataset.url
    wx.navigateTo({
      url: url
    })
  },
  miniDetails: function (e) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        miniDetails('/v1/appletmenu/menus',
          { tab: 2 }, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code == 0) {
            let miniList = res.data.list
            miniList.forEach((item, index) => {
              item.icon = STATIC_HOST+'/'+item.icon
            })
            that.setData({
              List : miniList
            })
          } else {
            wx.showToast({
              title: res.error_msg,
              duration: 2000,
              icon: 'none'
            })
            that.setData({
            })
          }

        }).catch(function (error) {
          that.setData({
          })
        })
      },
      fail: function (res) {
        wx.showToast({
          title: '请登陆',
          icon: 'none',
          duration: 2000
        })
        setTimeout(function () {
          wx.navigateTo({
            url: '../login/login'
          })
        }, 2000)

      }
    })
  },
  onLoad: function(options) {
    
  },

  onReady: function() {

  },

  onShow: function() {
    let that = this;
    that.miniDetails();
  },

  onHide: function() {

  },

  onUnload: function() {

  },


  onPullDownRefresh: function() {

  },


  onReachBottom: function() {

  },

  onShareAppMessage: function() {

  }
})