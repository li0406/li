// pages/user/user.js
import { getUser,exit } from "../../utils/api.js"
Page({

  /**
   * 页面的初始数据
   */
  data: {
    imageUrl: '',
    cName: '',
    deparment: '',
    position: '',
    token:'',
    ver:''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getUser('/v1/user/info', {}, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          console.log(res)
          if (res.error_code == 0) {
            if (res.data.info.dept_name == null){
              that.setData({
                deparment: '齐装网'
              })
            }else{
              that.setData({
                deparment: res.data.info.dept_name,
              })
            }
            that.setData({
              token: token,
              cName: res.data.info.user_name,
              position: res.data.info.role_name,
              imageUrl: res.data.info.logo
            })
          }
        })
      }
    })
    const accountInfo = wx.getAccountInfoSync();
    that.setData({
      ver:accountInfo.miniProgram.version
    })
  },

  /**
   * 退出当前账号
   */
  exitAccount: function (e) {
    exit('/loginout', {}, {
      'content-type': 'application/json',
      'token':this.data.token
    }).then(data => {
      if (data.error_code == 0) {
        wx.clearStorage()
        wx.reLaunch({
          url: "../login/login"
        })
      } 
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