// pages/journalLog/journalLog.js
import { getListVisit } from "../../utils/api.js"
function getTime(timestamp) {
  let date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
  // let Y = date.getFullYear() + '-';
  // let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  // let D = date.getDate() + ' ';
  let h = (date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()) + ':';
  let m = (date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes());
  // let s = (date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds());
  return h + m;
}
function getTime1(timestamp) {
  let date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
  let Y = date.getFullYear() + '-';
  let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  let D = date.getDate() + ' ';
  let h = (date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()) + ':';
  let m = (date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes());
  // let s = (date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds());
  return Y + M + D + h + m;
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    company_id:'',
    list: [],
    pageCurrent: '',
    pageTotalNumber: '',
    totalNumber:'',
    pageNumber:[],
    parms: {
      company_id: '',
      page: 1,
      page_count: 10,
      visit_start: '',
      visit_end: '',
      num:''
    }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let companyId = options.companyId;
    let that = this;
    that.setData({
      ['parms.company_id']: companyId,
      company_id: companyId
    });
    that.getList(that.data.parms);
  },
  getList: function (parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getListVisit('/v1/report/list_visit', parms,{
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let list = res.data.list;
            let curPage = res.data.page.page_current;
            let totalPage = res.data.page.page_total_number;
            let totalNumber = res.data.page.total_number;
            let pageArr = [];
            for (var i = 0; i < list.length; i++) {
              list[i].index = totalNumber-((curPage - 1) * 10 + 1 + i)+1;
              list[i].sTime = getTime1(list[i].visit_start_time) + '-' + getTime(list[i].visit_end_time);
            }
            for (let i = 0; i < totalNumber; i++) {
              pageArr.push(i + 1)
            }

            that.setData({
              list: list,
              pageCurrent: curPage,
              pageTotalNumber: totalPage,
              totalNumber: totalNumber,
              pageNumber: pageArr
            })
          }
        })
      },
      fail: function () {
        wx.redirectTo({
          url: '../login/login',
        })
      }
    })
  },
  toReport: function (e) {
    let companyId = this.data.company_id;
    wx.navigateTo({
      url: '../report/report?companyId=' + companyId
    })
  },
  prevBtn: function (e) {
    let that = this;
    let p = that.data.pageCurrent;
    if (p <= 1) {
      p = 1;
      return;
    }else{
      p = p - 1;
    }
    that.setData({
      ['parms.page']: p
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },
  nextBtn: function (e) {
    let that = this;
    let p = that.data.pageCurrent;
    let max = that.data.pageTotalNumber;
    if (p >= max) {
      p = max;
      return;
    }else{
      p = p + 1;
    }
    that.setData({
      ['parms.page']: p
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },
  toPage: function (e) {
    console.log(e)
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.page']: p
    })
    that.getCompanyList(that.data.parms);
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
    let companyId = this.data.company_id;
    let that = this;
    that.setData({
      ['parms.company_id']: companyId,
      company_id: companyId
    });
    that.getList(that.data.parms);
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