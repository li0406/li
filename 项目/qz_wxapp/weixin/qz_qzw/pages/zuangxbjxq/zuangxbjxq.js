// pages/zuangxbjxq/zuangxbjxq.js
let app = getApp();
let apiUrl = app.getApiUrl();
let imgUrl = app.getImgUrl();
const untils = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    banbaojia: {},
    halfTotal: '',
    keting: {},
    chufang: [],
    woshi: [],
    wsj: [],
    sd: [],
    other: [],
    imgUrl: imgUrl,
    codeImg: untils.path()
  },
  onLoad: function(options) {
    if (!options.orderid) {
      wx.navigateTo({
        url: '../jsq/jsq',
      })
    }
    var that = this;
    wx.request({
      url: apiUrl + '/appletorder/detail?order_no=' + options.orderid,
      data: {
        order_no: options.orderid
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "POST",
      success: function(res) {
        if (res.data.status == 1) {
          that.setData({
            keting: res.data.data.child.kt,
            chufang: res.data.data.child.cf,
            woshi: res.data.data.child.zw,
            wsj: res.data.data.child.wsj,
            sd: res.data.data.child.sd,
            other: res.data.data.child.other,
            banbaojia: res.data.data.total,
          })
        }
      }
    })
    this.setData({
      codeImg: untils.path()
    })
  },

  zxbj: function() {
    wx.navigateTo({
      url: '../jsq/jsq'
    })
  },
  onShareAppMessage: function(ops) {
    if (ops.from === 'button') {}
    return {
      title: '齐装网装修家居',
      path: 'pages/zhuangxiubjxq/zhuangxiubjxq',
      success: function(res) {},
      fail: function(res) {}
    }
  },
})