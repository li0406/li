// pages/baojiaresult/baojiaresult.js
import { resultHandle } from "../../utils/api.js"
let app = getApp();
let apiUrl = app.getApiUrl();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    // showView:[false,false],
    banbaojia:{},
    halfTotal:'',
    keting:{},
    chufang:[],
    woshi:[],
    wsj:[],
    sd:[],
    other:[],
  },


  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    if (!options.tel){
      wx.navigateTo({
        url: '../baojia/baojia',
      })
    }
    var that = this;
    resultHandle('/v1/quote', {
      tel: options.tel
    }).then(data => {
      if(data.error_code == 0){
        that.setData({
          keting: data.date.child.kt,
          chufang: data.date.child.cf,
          woshi: data.date.child.zw,
          wsj: data.date.child.wsj,
          sd: data.date.child.sd,
          other: data.date.child.other,
          banbaojia: data.date.total,
        })
      }
    })
  },

  zxbj: function () {
    wx.navigateTo({
        url: '../baojia/baojia'
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
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
      console.log(ops.target)
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/baojiaresult/baojiaresult',
      success: function (res) { },
      fail: function (res) { }
    }
  },
})