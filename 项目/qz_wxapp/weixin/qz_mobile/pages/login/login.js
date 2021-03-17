// pages/login/login.js
import { login, connectAccount } from "../../utils/api.js"
import config from "../../utils/config.js"
const app = getApp()
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: false,
    success: function (res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isHide: false,
    user:'',
    password:'',
    msg: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        that.setData({
          user: res.data.user,
          password: res.data.password
        })
      }
    })
  },
  login: function(e) {
    let that = this;
    let user = e.currentTarget.dataset.user;
    let password = e.currentTarget.dataset.password;
    if (user == ''){
      that.setData({
        isHide: true,
        msg: '请输入账号'
      })
      setTimeout(function () {
        that.setData({
          isHide: false
        })
      }, 3000)
      return;
    } else {
      if (user.length > 25){
        that.setData({
          isHide: true,
          msg: '账号长度为1-25字'
        })
        setTimeout(function () {
          that.setData({
            isHide: false
          })
        }, 3000)
        return;
      }
    }
    if (password == '') {
      that.setData({
        isHide: true,
        msg: '请输入密码'
      })
      setTimeout(function () {
        that.setData({
          isHide: false
        })
      }, 3000)
      return;
    }else {
      if (password.length > 25) {
        that.setData({
          isHide: true,
          msg: '密码长度为1-25字'
        })
        setTimeout(function(){
          that.setData({
            isHide: false
          })
        },3000)
        return;
      }
    }
    login('/account',{
      user_name: user,
      user_pwd: password,
      appid: config.appid
    },{
        'content-type': 'application/x-www-form-urlencoded'
    }).then(data => {
      if(data.error_code == 0){
        wx.setStorage({
          key: 'info',
          data: {
            token: data.data.token,
            user: user,
            password: password
          },
        })
        if(data.data.bind_status && parseInt(data.data.bind_status) === 2) {
            this.connectAccount()
        }
        wx.switchTab({
          url: '../index/index',
        })

      }else{
        that.setData({
          isHide: false,
        })
        alertViewWithCancel("登录失败", data.error_msg, function () {
          that.setData({
            user: '',
            password: ''
          })
        });
      }

    })

  },
  connectAccount() {
    wx.getStorage({
        key: 'info',
        success: function (res) {
            let token = res.data.token;
            connectAccount('/v1/applet/bind',{
                code: app.globalData.wxCode,
                appid: config.appid
              },{
                token: token,
                'content-type': 'application/x-www-form-urlencoded'
              }).then(res => {
                  // 不管成功失败，啥也不管
              })
        },
        fail: function() {
        },
        complete: function() {
        }
    })
  },
  user: function (e) {
    this.setData({
      user: e.detail.value
    })
  },
  password: function (e) {
    this.setData({
      password: e.detail.value
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