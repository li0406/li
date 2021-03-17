// pages/fendetails/fendetails.js
import { fendetails } from "../../utils/api.js";
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isShow: false,
    id:''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  
  },

  searchWord: function (e) {
    let value = e.detail.value;
    if(!value){
      this.setData({
        isShow: true
      })
      return;
    }else{
      this.setData({
        isShow: false
      })
    }
    this.setData({
      id: value
    })
    console.log(this.data.id)
    this.fendetails(this.data.id)
  },

  fendetails: function (id) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        fendetails('/v1/orders/fen_companys',
          {company:id}, {
            'Content-Type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code == 0) {
            wx.navigateTo({    //保留当前页面，跳转到应用内的某个页面
              url: "/pages/order/order?id=" + that.data.id
            })
          }else{
            wx.showToast({
              title: res.error_msg,
              duration: 2000,
              icon: 'none'
            })
          }
        })
      },
      fail: function(res){
        wx.navigateTo({
          url: '../login/login'
        })
      }
    })
  },
 
  /**
     * 输入框绑定
     */
  bindKeyInput: function (e) {
    this.setData({
      id: e.detail.value
    })
  },

  /**
   * 查询、跳转
   */
  search:function(e){
    if (this.data.id == ''){
      this.setData({
        isShow: true
      })
      return;
    }else{
      this.setData({
        isShow: false
      })
      this.fendetails(this.data.id)
    }
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