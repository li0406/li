// pages/information/information.js
const app = getApp()
let apiUrl = app.getApiUrl();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userInfo: null,
    orderList:[],
    currentOrder:null,
    hideOrderId:'',
    hasNews:false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    wx.getSetting({
      success: function (res) {
        if (res.authSetting['scope.userInfo']) {
          wx.getUserInfo({
            lang: 'zh_CN',
            success: function (res) {
              that.setData({
                userInfo:res.userInfo
              })
            }
          })
        }
      }
    });
  },
  onShow:function(){
    var that = this;
    wx.request({
      url: apiUrl + '/v1/myrenovation/getorderslist',
      header: {
        'content-type': 'application/json',
        'token': app.globalData.loginInfo.token
      },
      method: "GET",
      success: function (res) {
        if (res.data.error_code == 0 && res.data.data.count_order > 0) {
          for (let i = 0; i < res.data.data.orderlist.length; i++ ){
            if (res.data.data.orderlist[i].order_no == app.globalData.currentOrderId){
              that.setData({
                currentOrder: res.data.data.orderlist[i]
              },function(){
                res.data.data.orderlist.splice(i,1);
                that.setData({
                  orderList: res.data.data.orderlist
                });
              });
            }
          }
        }
      }
    });

    wx.request({
      url: apiUrl + '/v1/user/newsstatus',
      header: {
        'content-type': 'application/json',
        'token': app.globalData.loginInfo.token
      },
      method: "GET",
      success: function(res) {
        if (res.data.data.hadnews == 1&&res.data.error_code==0){
          that.setData({
            hasNews:true
          })
        } 
      }
    })
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  },
  orderjindu: function (e) {
    let that = this;
    let url = e.currentTarget.dataset.url;
    wx.showModal({
      title: '提示',
      content: '确定默认查看订单更改为此订单?',
      showCancel: true,//是否显示取消按钮
      cancelText: "否",//默认是“取消”
      confirmText: "是",//默认是“确定”
      success: function (res) {
        if (res.cancel) {
        } else {
          app.globalData.currentOrderId = e.currentTarget.dataset.no;
          wx.navigateTo({
            url: url
          })
        }
      },
      fail: function (res) { },
      complete: function (res) { }
    })
  }

})