// pages/expired/expired.js
var util = require('../../utils/util.js');
import { getExpired } from "../../utils/api.js"
var dateObj = new Date();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    sTime:'',
    nowTime: '',
    cs:'',
    curCity: '不限',
    pageCurrent: '1',
    pageTotalNumber: '10',
    list:[],
    pageNumber: [],
    isShow: true,
    noResult: false,
    noInternet: false,
    parms: {
      start: '',
      end: '',
      cs: '',
      page: 1,
      page_count: 10,
      company: ''
    }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    that.getExpired(that.data.parms);
  },

  searchWord: function (e) {
    let that = this;
    let value = e.detail.value;
    that.setData({
      ['parms.company']: value,
      ['parms.page']: 1
    })
    that.getExpired(that.data.parms);
  },

  toCity: function () {
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?needArea=1&curCity=' + city
    })
  },

  getExpired: function (parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getExpired('/v1/company/expire_remind',
          parms, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code == 0) {
            let totalNumber = res.data.page.page_total_number;
            let pageArr = [];
            if (res.data.list.length < 1) {
              that.setData({
                noResult: true,
                noInternet: false,
                isShow: true
              })
            } else {
              that.setData({
                noResult: false,
                isShow: true
              })
            }
            if (res.data.page.total_number <= 10) {
              that.setData({
                isShow: true
              })
            } else {
              that.setData({
                isShow: false
              })
            }
            for (let i = 0; i < totalNumber; i++) {
              pageArr.push(i + 1)
            }
            that.setData({
              list: res.data.list,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr
            })
          }else{
            that.setData({
              list: [],
              isShow: true,
              noResult: true,
              noInternet: false
            })
          }
        }).catch(function (error) {
          that.setData({
            list: [],
            noInternet: true,
            noResult: false,
            isShow: true
          })
        })
      },
      fail: function (res) {
        wx.navigateTo({
          url: '../login/login'
        })
      }
    })
  },

  /**
   * 选择开始时间
   */
  bindDateChange: function (e) {
    var startTimestamp = Date.parse(new Date(e.detail.value)) / 1000
    var endTimestamp = Date.parse(new Date(this.data.end)) / 1000
    if (startTimestamp > endTimestamp){
      wx.showToast({
        title: '开始时间不能大于结束时间',
        duration: 2000,
        icon: 'none'
      })
    }else{
      this.setData({
        start: e.detail.value,
        ['parms.start']: e.detail.value,
        ['parms.page']: 1
      })
      if (this.data.end) {
        this.getExpired(this.data.parms);
      }
    }
  },

  /**
   * 选择结束时间
   */
  bindEndDateChange: function (e) {
    this.setData({
      end: e.detail.value,
      ['parms.end']: e.detail.value,
      ['parms.page']: 1
    })
    if (this.data.start) {
      this.getExpired(this.data.parms);
    }
  },

  /**
   * 点击上一页
   */
  goPrePage: function () {
    let that = this;
    let page = that.data.pageCurrent - 1;
    if (page <= 1) {
      page = 1;
      that.setData({
        ['parms.page']: page
      })
    }else{
      that.setData({
        ['parms.page']: page
      })
    }
    that.getExpired(that.data.parms)
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  /**
  * 点击下一页
  */
  goNextPage: function (e) {
    let that = this;
    let page = that.data.pageCurrent + 1;
    let max = that.data.pageTotalNumber;
    if (page >= max) {
      page = max;
      that.setData({
        ['parms.page']: page
      })
    }else{
      that.setData({
        ['parms.page']: page
      })
    }
    that.getExpired(that.data.parms)
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  /**
  * 点击跳转页数
  */
  toPage: function (e) {
    console.log(e)
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.page']: p
    })
    that.getExpired(that.data.parms)
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  //重新加载
  toExpired: function(){
    wx.navigateTo({
      url: '../expired/expired'
    })
  },

  /**
  * 点击跳转页数
  */
  goTargetPage: function () {
    console.log("目标页面")
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
    that.setData({
      ['parms.cs']: that.data.cs,
      ['parms.page']: 1
    });
    that.getExpired(that.data.parms);
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