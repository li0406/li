//index.js
//获取应用实例
let app = getApp();
let apiUrl = app.getApiUrl();

Page({
  data: {
    userInfo: {},
    markInfoAll: [],
    lookInfoAll: [],
    isHide:false,
    userInfo: null,
    markHide:false,
    lookHide:false
  },
  onLoad: function () {
    
  },
  onShow:function(){
    var that = this;
    //调用应用实例的方法获取全局数据
    app.getUserInfo(function (userInfo) {
      // 判断是否拒绝登录
      console.log(userInfo.userId)
      if (!userInfo.userId) {
        that.setData({ isHide: true, userInfo: userInfo})
      } else if (userInfo.userId) {
        that.setData({ userInfo: userInfo });
        wx.request({
          url: apiUrl + '/zxjt/likestore',
          data: {
            uid: userInfo.userId,// userInfo.userId 用户ID
            limit: 1
          },
          header: {
            'content-type': 'application/json'
          },
          success: function (res) {
            if (res.data.data.length>0){
              that.setData({ markInfoAll: res.data.data, markHide: false })
            }else{
              that.setData({ markInfoAll: [], markHide:true })
            }
          }
        });
      }
    });
    // 获取已经浏览过缓存id
    let arrInfo = app.getNewStorage('arrInfoKey');
    if (arrInfo && arrInfo.length>0){
      that.setData({ lookInfoAll: arrInfo, lookHide: false })
    }else{
      that.setData({ lookInfoAll: [], lookHide: true })
    }
  },
  /**
   * 用户点击我的收藏模块跳转到收藏详情页
   */
  toMyCollections:function () {
    wx.navigateTo({
      url: '../userCollections/userCollections',
    })
  },
  guankan:function(){
    wx.navigateTo({
      url:'../watchhistory/watchhistory'
    })
  },
  /**
   * 用户点击我的收藏列表跳转到视频播放页面
   */
  toDetailPlay:function(e){
    console.log(e)
    wx.navigateTo({
      url: '../detail_play/detail_play?id='+e.currentTarget.dataset.id,
    })
  },
  /**
   * 用户点击我的设置跳转到设置页面
   */
  delStorage: function () {
    wx.navigateTo({
      url: '../user_set/user_set',
    })
  }
})
