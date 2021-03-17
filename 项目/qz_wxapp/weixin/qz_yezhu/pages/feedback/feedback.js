// pages/feedback/feedback.js
const app = getApp()
let apiUrl = app.getApiUrl();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    wrapdata:null,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that=this;
    wx.request({
      url: apiUrl + '/v1/myrenovation/getmyfeedback',
      data: { sgid: options.shigongid},
      method: "GET",
      header: {
        "content-type": "application/json",
        'token': app.globalData.loginInfo.token
      },
      success: function (res) {
        console.log(res.data.data)
        that.setData({
          wrapdata:res.data.data
        })
      }
    }); 


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