// user_set.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    currentSize:''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  
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
    let that = this;
    wx.getStorageInfo({
      success: function (res) {
        if ((res.currentSize / 1024) >= 0){
          let str = (res.currentSize / 1024).toFixed(2);
          that.setData({ currentSize: str })
        }else{
          that.setData({ currentSize: '0' })
        }
        
      }
    })
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
  /**
   * 用户点击右清楚缓存
   */
  delStorageNum:function(){
    let that = this;
    if (that.data.currentSize>0){
      wx.removeStorageSync('indexInfo');
      wx.removeStorageSync('json');
      wx.showToast({
        title: '清除缓存中...',
        icon: 'loading',
        duration: 1000,
        success:function(){
          that.setData({ currentSize: '0.00' })
        }
      })
    }else{
      that.setData({ currentSize: '0.00' })
    }
  }
})