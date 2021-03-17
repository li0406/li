// pages/shouquan/index.js
const app = getApp();
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
    isAgree:true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onShow: function (options) {
    var that=this;
    wx.getStorage({
      key: 'userInfo',
      success: function(res) {
        that.setData({
          isAgree:false
        })
      },
      fail: function(res) {
        that.setData({
          isAgree: true
        })
      }
    })
  },
  bindgetuserinfo:function(e){
    let that = this;
    if (e.detail.userInfo){
      that.setData({
        isAgree: false
      });
      app.getLoginAgain(function(res){
        app.globalData.userInfo=res;
        wx.setStorage({
          key: 'userInfo',
          data: res
        });
        wx.setStorage({
          key: 'userId',
          data: res.userId
        })
      });
    }else{
      that.setData({
        isAgree: true
      });
    }
  },
  onShareAppMessage: function (ops) {
    if (ops.from === 'button') {
      // 来自页面内转发按钮
      console.log(ops.target)
    }
    return {
      title: '齐装网装修家居',
      path: 'pages/shouquan/shouquan',
      success: function (res) { },
      fail: function (res) { }
    }
  },
})