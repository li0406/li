// pages/baojiajg/jieguo.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    total:'',
    shafa:'',
    chaji: '', 
    dianshigui: '',
    xiegui: '',
    canzhuo: '',
    yigui: '',
    chuang: '',
    chugui: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let obj = JSON.parse(options.jsonStr);
    this.setData({
      shafa: obj.shafa,
      chaji: obj.chaji,
      dianshigui: obj.dianshigui,
      xiegui: obj.xiegui,
      canzhuo: obj.canzhuo,
      yigui: obj.yigui,
      chuang: obj.chuang,
      chugui: obj.chugui,
      total:obj.total
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

  },
  toSheji: function () {
    wx.navigateTo({
      url: "../zhuangxiusj/zhuangxiusj"
    })
  },
  toBaojia: function () {
    wx.navigateTo({
      url: "../jsq/jsq"
    })
  },
})