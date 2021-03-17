// pages/myorder/myorder.js
const app = getApp()
let apiUrl = app.getApiUrl();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    myorderdata:[],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onShow: function (options) {
    var that=this;
    wx.request({
      url: apiUrl + '/v1/myrenovation/getorderslist',
      header: {
        'content-type': 'application/json',
        'token': app.globalData.loginInfo.token
      },
      method: "GET",
      success: function (res) {
        if (res.data.error_code == 0 && res.data.data.count_order > 0) {
          that.setData({
            myorderdata: res.data.data.orderlist
          })
        }
      }
    });
    // app.getNewStorage("loginInfo", function (res) {
    //   if (!res.token) {
    //     wx.navigateTo({
    //       url: '/pages/login/login'
    //     })
    //   }
    // });
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  },
  getOrderId:function(e){
    app.globalData.currentOrderId = e.currentTarget.dataset.id;
    app.globalData.currentOrderType = e.currentTarget.dataset.type;
    let url = e.currentTarget.dataset.url;
    wx.navigateTo({
      url: url
    })
  }
})