
// pages/user/user.js
//获取应用实例
let app = getApp();
let apid = app.getAPPid(),
  apiUrl = app.getApiUrl();

const navActive = require('../../utils/util.js');

Page({
  data: {
    userInfo: {},
    markInfoAll: [],
    lookInfoAll: [],
    isHide: false,
    userInfo: null,
    markHide: false,
    lookHide: false,
    userId: '',
    login: '',
    userInfo1: {},
    currentUrl: "",
    navList: ""
  },
  onLoad: function () {
    wx.currentUrl = getCurrentPages()[getCurrentPages().length - 1].route;//获取当前页面url
      this.setData({
        navList: navActive.activeNav(wx.currentUrl)
      })
  },
  onShow: function () {
    var that = this;
    //调用应用实例的方法获取全局数据
    app.getUserInfo(function (userInfo) {
      // 判断是否拒绝登录
      that.setData({ userId: userInfo.userId })
      if (!userInfo.userId) {
        that.setData({ isHide: true, userInfo: userInfo, login: '登录' })
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
            if (res.data.data.length > 0) {
              that.setData({ markInfoAll: res.data.data, markHide: false })
            } else {
              that.setData({ markInfoAll: [], markHide: true })
            }
          }
        });
      }
    });
    // 获取已经浏览过缓存id
    let arrInfo = app.getNewStorage('arrInfoKey');
    if (arrInfo && arrInfo.length > 0) {
      that.setData({ lookInfoAll: arrInfo, lookHide: false })
    } else {
      that.setData({ lookInfoAll: [], lookHide: true })
    }
  },
  /**
   * 用户点击我的收藏模块跳转到收藏详情页
   */
  toUserMark: function () {
    wx.navigateTo({
      url: '../user_collect/user_collect',
    })
    // let that = this;
    // if (this.data.userId) {
    //   wx.navigateTo({
    //     url: '../user_collect/user_collect',
    //   })
    // } else {
    //   app.getLoginAgain(function (res) {
    //     wx.login({
    //       success: function (l) {
    //         if (l.code) {
    //           wx.request({
    //             url: apiUrl + '/login',
    //             data: {
    //               appid: apid,
    //               code: l.code,
    //               name: res.nickName,
    //               logo: res.avatarUrl
    //             },
    //             header: {
    //               'content-type': 'application/x-www-form-urlencoded'
    //             },
    //             method: "POST",
    //             dataType: 'json',
    //             success: function (e) {
    //               wx.navigateTo({
    //                 url: '../user_mark/user_mark?userid=' + e.data.data,
    //               })
    //             }
    //           });
    //         }
    //       }
    //     });
      // });
    // }
  },
  /**
   * 用户点击我的设置跳转到设置页面
   */
  // delStorage: function () {
  //   wx.navigateTo({
  //     url: '../user_set/user_set',
  //   })
  // },
  phoneCall: function () {
    wx.makePhoneCall({
      phoneNumber: '4008-659-600' //拨打400电话
    })
  },
  login: function () {
    let that = this;
    app.getLoginAgain(function (res) {
      that.setData({ userInfo: res, login: '' });
    })
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

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
