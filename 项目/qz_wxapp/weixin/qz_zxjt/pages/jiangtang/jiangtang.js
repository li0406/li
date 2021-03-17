// user,wxml.js
let page = 1;
let app = getApp();
let apiUrl = app.getApiUrl();
let booLean = true;
Page({
  /**
   * 页面的初始数据
   */
  data: {
    zxbefore:'top-hoverd-btn',
    zxbecenter: '',
    zxafter: '',
    hidden: false,
    infoList:[],
    msgimg03: '../../img/bfsltb.png',
    idCount:1,
    scrollTop:0
  },


  zxbj:function(){
    wx.navigateTo({
      url:'../zuangxbj/zuangxbj'
    })
  },
  xiangx: function (e) {
    wx.navigateTo({
      url: '../detail_play/detail_play?id='+e.currentTarget.dataset.id
    })
  },

  onLoad: function (options) {
    var that = this;
    wx.request({
      url: apiUrl+'/zxjt/category/id/1/start/1',
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        that.setData({ infoList: res.data.data })
      }
    })

  },
  // 装修前
  tozxbefore: function () {
    this.setData({idCount:1})
    booLean = true;
    page = 1;
    this.setData({scrollTop: 0});
    this.updateBtnStatus('zxbefore');
    var that = this;
    wx.request({
      url: apiUrl +'/zxjt/category/id/1/start/1',
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        that.setData({ infoList: res.data.data })
      }
    })
      
  },
  // 装修中
  tozxbecenter: function () {
    this.setData({ idCount: 2 });
    booLean = true;
    page = 1;
    this.setData({ scrollTop: 0 });
    this.updateBtnStatus('zxbecenter');
    var that = this;
    wx.request({
      url: apiUrl +'/zxjt/category/id/2/start/1',
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        that.setData({ infoList: res.data.data })
      }
    })
      
  },
  // 装修后
  tozxafter: function () {
    this.setData({ idCount: 3 });
    booLean = true;
    page = 1;
    this.setData({ scrollTop: 0 });
    this.updateBtnStatus('zxafter');
    var that = this;
    wx.request({
      url: apiUrl +'/zxjt/category/id/3/start/1',
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        // console.log(res.data)
        that.setData({ infoList: res.data.data })
      }
    })
  },

  updateBtnStatus: function (k) {
    this.setData({
      zxbefore: this.getHoverd('zxbefore', k),
      zxbecenter: this.getHoverd('zxbecenter', k),
      zxafter: this.getHoverd('zxafter', k),
    });
  },
  getHoverd: function (src, dest) {
    return (src === dest ? 'top-hoverd-btn' : '');
  },

  /**
   * 生命周期函数--监听页面加载
   */

  onPullDownRefresh: function () {
    wx.stopPullDownRefresh()
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
  downLoad:function(){
    let that = this;
    let idCount = this.data.idCount;
    let arr = this.data.infoList;
    page++;
    if(booLean){
      wx.request({
        url: apiUrl + '/zxjt/category/id/' + idCount + '/start/' + page,
        header: {
          'content-type': 'application/json'
        },
        success: function (res) {
          if (res.data.data != null) {
            let arrAdd = arr.concat(res.data.data)
            // console.log(arr)
            wx.showToast({
              title: 'loading...',
              icon: 'loading'
            })
            that.setData({ infoList: arrAdd })
            //console.log(arrAdd)
          } else {
            booLean = false;
          }
        }
      })
    }
  }
})