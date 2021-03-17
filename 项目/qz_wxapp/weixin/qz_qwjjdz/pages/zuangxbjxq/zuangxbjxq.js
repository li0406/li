// pages/zuangxbjxq/zuangxbjxq.js
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
    console.log(options.orderid)
    //let orderid = '2018050410718910';
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
        success: function (res) {
          console.log(res);
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
      })
  },

  zxbj: function () {
    wx.navigateTo({
        url: '../jsq/jsq'
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
      path: 'pages/zhuangxiubjxq/zhuangxiubjxq',
      success: function (res) { },
      fail: function (res) { }
    }
  },
})