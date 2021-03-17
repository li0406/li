// search_result.js
let app = getApp();
let apiUrl = app.getApiUrl();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    vListInfo:[],
    isHide:true
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let searchValue = options.seachvalue
    wx.request({
      url: apiUrl+'/zxjt/search/keyword/' + searchValue,
      data:'String',
      header: {
        'content-type': 'application/json'
      },
      dataType: 'json',
      success: function(res) {
        if(res.data){
          that.setData({ vListInfo: res.data.videoList,isHide:true });
          wx.setNavigationBarTitle({
            title: searchValue,
          })
        }else{
          wx.setNavigationBarTitle({
            title: '没有搜索到内容',
          })
          that.setData({ vListInfo: [], isHide: false })
        }
      }
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
  onShareAppMessage: function () {
  
  },

  /**
   * 点击跳转到播放详情页
   */
  toDetailPlay:function(e){
    let id = e.currentTarget.dataset.id;
    // console.log(this.data.vListInfo[0].id)
    wx.navigateTo({
      url: '../detail_play/detail_play?id=' + id,
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  }
})