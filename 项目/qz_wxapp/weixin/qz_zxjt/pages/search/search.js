// search.js
var data = {
  "hotSearch":[
    { "word":"装修流程", id:"0"},
    { "word": "材料", id:"1"},
    { "word": "装修公司", id: "2"},
    { "word": "家居软装", id: "3"},
    { "word": "施工", id: "4"},
    { "word": "装修设计",id:"5"}
    ]
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    "hotList":data.hotSearch,
    inputValue:'',

  },
  kk: function () {
    wx.navigateTo({
      url: '../zuangxsj/zuangxsj'
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const that = this;
    
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
   * 搜索后跳转
   */
  searchResult:function(e){
    if (!this.data.inputValue){
      wx.showModal({
        title:'提示',
        content: '请输入要搜索的内容！',
        showCancel:false
      })
    }else{
      wx.navigateTo({
        url: '../search_result/search_result?seachvalue=' + this.data.inputValue,
        success: function (res) { },
        fail: function (res) { },
        complete: function (res) { },
      })
    }
  },
  /**
   * 热词点击后跳转
   */
  hotHandle:function(e){
    var id = e.target.dataset.id;
    var tit = e.target.dataset.title
    wx.navigateTo({
      url: '../search_result/search_result?id=' + id + '&title=' + tit,
      success: function (res) { },
      fail: function (res) { },
      complete: function (res) { },
    })
  },
  inputValue:function(e){
    this.setData({ inputValue: e.detail.value});
  },
  /**
   * 悬浮框设计点击页面跳转
   */
  toDes: function () {
    wx.navigateTo({
      url: '../zuangxsj/zuangxsj',
    })
  },
})