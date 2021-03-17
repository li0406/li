// pages/news/news.js
const app = getApp()
let apiUrl = app.getApiUrl();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    newsdata:""
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that=this;
    wx.request({
      url: apiUrl + '/v1/user/newslist',
      header: {
        'content-type': 'application/json',
        'token': app.globalData.loginInfo.token
      },
      success: function (res) {
        that.setData({
          newsdata:res.data.data.list,
        })
      }
    });
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  }
})