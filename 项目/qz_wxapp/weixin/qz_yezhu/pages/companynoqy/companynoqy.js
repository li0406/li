// pages/companynoqy/companynoqy.js
const app = getApp()
let apiUrl = app.getApiUrl();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    gongsiheader:null,
    gongsilist:null,
    title:null,
    currentIndex:0,
    detailInfo:{
      aboutInfo:"",
      shortInfo:"",
      isOpen:false,
      rongyu:[],
      openxs:false,
      largeImgUrl:[],
    },
    fadeLarge: true,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that=this;
    wx.request({
      url: apiUrl +"/v1/myrenovation/showcompanyinfo",
      data: { 
        comid: options.comid
      },
      method:"GET",
      header: { 
        "content-type": "application/json",
        'token': app.globalData.loginInfo.token
        },
        success:function(res){

          if (res.data.data.jianjie.trim().length<95){
           that.setData({
             openxs:true,
           })
          }
          that.setData({
            gongsiheader:res.data.data.info,
            title: res.data.data.info.comname,
            gongsilist: res.data.data.caseslist,
            'detailInfo.aboutInfo': res.data.data.jianjie.trim(),
            'detailInfo.shortInfo': res.data.data.jianjie.trim().substr(0, 95),
            'detailInfo.rongyu': res.data.data.rongyu
          })
            wx.setNavigationBarTitle({ title: that.data.title})
          } 
    });
  },
  openLong: function (e) {
    let open = e.currentTarget.dataset.open == "true" ? true : false
    this.setData({
      'detailInfo.isOpen': open
    })
  },

  setLargeImg: function (e) {
    let url = e.currentTarget.dataset.url;
    this.setData({
      largeImgUrl: url,
      fadeLarge: false
    })
  },
  cancelLarge: function () {
    this.setData({
      largeImgUrl: [],
      fadeLarge: true
    })
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  prevImg:function () {
    let length = this.data.detailInfo.rongyu.length;
    let index = this.data.currentIndex;
    if (index > 0) {
      this.setData({
        currentIndex: index - 1
      })
    }
  },
  nextImg:function () {
    let length = this.data.detailInfo.rongyu.length;
    let index = this.data.currentIndex;
    if (index < length -1) {
      this.setData({
        currentIndex: index + 1
      })
    }
  },

  prevImgal: function () {
    let length = this.data.largeImgUrl.length;
    let index = this.data.currentIndex;
    if (index > 0) {
      this.setData({
        currentIndex: index - 1
      })
    }
  },
  nextImgal: function () {
    let length = this.data.largeImgUrl.length;
    let index = this.data.currentIndex;
    if (index < length - 1) {
      this.setData({
        currentIndex: index + 1
      })
    }
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})