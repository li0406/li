// pages/watchhistory/watchhistory.js
let app = getApp();
let apiUrl = app.getApiUrl();
let imgUrl = app.getImgUrl();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    lookInfo:[],
    lookHide:false,
    imgUrl: imgUrl
  
  },
  lsjltz:function(){
    wx.navigateTo({
      url: "../detail_play/detail_play"
    })
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
    let arrInfo = app.getNewStorage('arrInfoKey');
    if (arrInfo && arrInfo.length>0){
      that.setData({ lookInfo: arrInfo, lookHide: true })
    }else{
      that.setData({ lookInfo: [], lookHide: false })
    }
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
  // 清楚观看历史
  clearHistory:function(){
    let that= this;
    wx.showModal({
      title: '提示',
      content: '确定要清楚历史记录吗？',
      showCancel: true,
      success:function(res){
        if (res.confirm) {
          // console.log('用户点击确定')
          wx.removeStorage({
            key: 'arrIdKey',
            success: function (res) {
              that.setData({lookInfo:[],lookHide:false})
            }
          })
          wx.removeStorage({
            key: 'arrInfoKey',
            success: function (res) {
              that.setData({ lookInfo: [], lookHide: false })
            }
          })
        } else if (res.cancel) {
          // console.log('用户点击取消')
        }
      }
    })
    
  }
})