// pages/constructionteam/constructionteam.js
const app = getApp()
let apiUrl = app.getApiUrl();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    dingdanhao:'',
    shigongteamdata:[],
    errorInfo:''
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that=this;
    that.setData({
      dingdanhao: options.order,
    })
    wx.request({
      url: apiUrl + '/v1/workgroup',
      data: { 
        orderid: options.order, 
        tel: app.globalData.loginInfo.tel
      },
      method: "POST",
      header: {
        'content-type': 'application/x-www-form-urlencoded',
        'token': app.globalData.loginInfo.token
      },
      success: function (res) {
        if (res.data.error_code==0){
          that.setData({
            shigongteamdata: res.data.data.worker,
            })
        }else{
          that.setData({
            errorInfo:res.data.error_msg
          })
        }
      },
      fail:function(res){
        that.setData({
          errorInfo: res.data.error_msg
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