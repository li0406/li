// pages/qiandan/qiandan.js
// pages/fendan/fendan.js
var util = require('../../utils/util.js');
import { fendan } from "../../utils/api.js";
var dateObj = new Date();
function tipShow(title) {
  wx.showToast({
    title: title,
    duration: 2000,
    icon: 'none'
  })
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    list: [],
    createTime: '2010-10-10',
    nowTime: util.formatDate(new Date()),
    curCity: '不限',
    cs:'',
    isShow: true,
    ishide: false,
    pageNumber: [],
    pageCurrent: '',
    pageTotalNumber: '',
    noResult: false,
    noInternet: false,
    parms: {
      qdstart: '',
      qdend: '',
      page: 1,
      page_count: 10,
      cs: 320500,
      company_info: '',
      down: ''
    }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var year = dateObj.getFullYear()
    var month = dateObj.getMonth() + 1
    var date = dateObj.getDate()
    var that = this
    this.setData({
      qdstart: year + '-' + util.formatNumber(month) + '-01',
      qdend: year + '-' + util.formatNumber(month) + '-' + util.formatNumber(date),
      ['parms.qdstart']: year + '-' + util.formatNumber(month) + '-01',
      ['parms.qdend']: year + '-' + util.formatNumber(month) + '-' + util.formatNumber(date)
    })
    that.fendan(that.data.parms);
  },

  fendan: function (parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        fendan('/v1/statistics/company_orders',
          parms, {
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          console.log(res)
          if (res.error_code == 0) {
            let totalNumber = res.data.page.page_total_number;
            let pageArr = [];
            if (res.data.list.length <= 1) {
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
            if (res.data.page.total_number == 0) {
              that.setData({
                ishide: true,
                isShow: true
              })
            } else if (res.data.page.total_number <= 10 && res.data.page.total_number > 0) {
              that.setData({
                isShow: true,
                ishide: false
              })
            } else {
              that.setData({
                isShow: false,
                ishide: false
              })
            }
            for (let i = 0; i < totalNumber; i++) {
              pageArr.push(i + 1)
            }
            that.setData({
              list: res.data.list,
              total: res.data.page.total_number,
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
        }).catch(function(error){
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

  searchWord: function (e) {
    let that = this;
    let value = e.detail.value;
    if (this.data.curCity == '不限') {
      tipShow('请选择城市')
      return;
    }
    that.setData({
      ['parms.company_info']: value,
      ['parms.page']: 1
    })
    that.fendan(that.data.parms);
  },

  toCity: function () {
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?needArea=1&curCity=' + city
    })
  },

  /**
   * 选择开始时间
   */
  bindDateChange: function (e) {
    var startTimestamp = Date.parse(new Date(e.detail.value)) / 1000
    var endTimestamp = Date.parse(new Date(this.data.qdend)) / 1000
    if ((endTimestamp - startTimestamp) > 31536000 || (startTimestamp - endTimestamp) > 31536000) {
      tipShow('时间间隔不能超过一年')
      return;
    }else{
      if (this.data.curCity == '不限') {
        tipShow('请选择城市')
        this.setData({
          ['parms.qdstart']: e.detail.value,
          qdstart: e.detail.value
        })
        return;
      } else {
        this.setData({
          ['parms.qdstart']: e.detail.value,
          qdstart: e.detail.value,
          ['parms.page']: 1
        })
      }
    }
    this.fendan(this.data.parms);
  },

  /**
   * 选择结束时间
   */
  bindEndDateChange: function (e) {
    var startTimestamp = Date.parse(new Date(this.data.qdstart)) / 1000
    var endTimestamp = Date.parse(new Date(e.detail.value)) / 1000
    if ((endTimestamp - startTimestamp) > 31536000 || (startTimestamp - endTimestamp) > 31536000) {
      tipShow('时间间隔不能超过一年')
      return;
    }else{
      if (this.data.curCity == '不限') {
        tipShow('请选择城市')
        this.setData({
          ['parms.qdend']: e.detail.value,
          qdend: e.detail.value
        })
        return;
      } else {
        this.setData({
          ['parms.qdend']: e.detail.value,
          qdend: e.detail.value,
          ['parms.page']: 1
        })
      }
    }
    this.fendan(this.data.parms);
  },

  bindToPage: function (e) {
    let id = e.currentTarget.dataset.id
    let start = ''
    let end = ''
    if (this.data.parms.qdstart != '' && this.data.parms.qdend != ''){
      start = this.data.parms.qdstart
      end = this.data.parms.qdend
    }
    wx.navigateTo({
      url: '../sign/sign?id=' + id +'&start=' +start + '&end=' + end
    })
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
    } else {
      that.setData({
        ['parms.page']: page
      })
    }
    that.fendan(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  /**
  * 点击下一页
  */
  goNextPage: function () {
    let that = this;
    let page = that.data.pageCurrent + 1;
    let max = that.data.pageTotalNumber;
    if (page >= max) {
      page = max;
      that.setData({
        ['parms.page']: page
      })
    } else {
      that.setData({
        ['parms.page']: page
      })
    }
    that.fendan(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  //重新加载
  toQianDan: function(){
    wx.navigateTo({
      url: '../qiandan/qiandan'
    })
  },

  /**
  * 点击跳转页数
  */
  toPage: function (e) {
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.page']: p
    })
    that.fendan(that.data.parms)
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
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
    let that = this;
    that.setData({
      ['parms.cs']: that.data.cs,
      ['parms.page']: 1
    });
    that.fendan(that.data.parms);
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