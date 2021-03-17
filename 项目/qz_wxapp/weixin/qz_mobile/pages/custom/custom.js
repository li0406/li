// pages/custom/custom.js
import { getCompanyList } from "../../utils/api.js"
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
const app = getApp();
var dateTimePicker = require('../../utils/dateTimePicker.js');
var dateObj = new Date();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    curCity:'不限',
    cs:'',
    qx:'',
    tabActive: true,
    page: false,
    noResult: false,
    moreInfo: [true, true, true, true, true, true, true, true, true, true],
    list:[],
    pageCurrent: '',
    pageTotalNumber: '',
    pageNumber:[],
    parms:{
      cs: '',
      company: '',
      company_style: '',
      visit_style: '',
      level: '',
      enter_start:'',
      enter_end: '',
      next_start: '',
      next_end: '',
      p: 1,
      psize: 10,
      op_uid: '',
      order: 1
    },
    company_style:'',
    visit_style:'',
    level:'',
    enter_start: '',
    enter_end: '',
    next_start: '',
    next_end: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    that.getCompanyList(that.data.parms);
  },
  searchWord: function (e) {
    let that = this;
    let value = e.detail.value;
    that.setData({
      ['parms.company']: value,
      ['parms.p']:1
    })
    that.getCompanyList(that.data.parms);
  },
  getCompanyList: function (parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getCompanyList('/v1/company/list_company',
          parms,{
            'content-type': 'application/json',
            'token': token
          }
        ).then(res => {
          if (res.error_code == 0) {
            let totalNumber = res.data.page.page_total_number;
            let list = res.data.list;
            for (let i = 0; i < list.length; i++){
              list[i].sTime = list[i].visit_start_time + '-' + getTime(list[i].visit_end_time_num);
            }
            
            let pageArr = [];
            for (let i = 0; i < totalNumber;i++){
              pageArr.push(i+1)
            }
            that.setData({
              list: list,
              page: true,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr,
              noResult: false
            })
          }else{
            that.setData({
              list: [],
              page: false,
              noResult: true
            })
          }
          }).catch(function (error) {
            that.setData({
              list: [],
              page: false,
              noInternet: true
            })
          })
      },
      fail: function () {
        wx.redirectTo({
          url: '../login/login',
        })
      }
    })
  },
  changeType: function (e) {
    let that = this;
    that.setData({
      tabActive: e.currentTarget.dataset.type == "true",
      ['parms.order']: e.currentTarget.dataset.order,
      ['parms.p']: 1,
    });
    that.getCompanyList(that.data.parms);
  },
  moreInfo: function (e) {
    let that = this;
    let index = e.currentTarget.dataset.index;
    let moreInfoX = "moreInfo["+index+"]";
    if(e.currentTarget.dataset.info){
      that.setData({
        [moreInfoX]: false
      })
    }else{
      that.setData({
        [moreInfoX]: true
      })
    }
  },
  toCity: function () {
    let city = this.data.curCity;
    wx.navigateTo({
      url: '../city/city?needArea=1&curCity=' + city
    })
  },
  toJournal: function() {
    wx.navigateTo({
      url: '../journal/journal'
    })
  },
  toShaixuan: function (e) {
    let that = this;
    let company_style = that.data.company_style;
    let visit_style = that.data.visit_style;
    let level = that.data.level;
    let enter_start = that.data.enter_start;
    let enter_end = that.data.enter_end;
    let next_start = that.data.next_start;
    let next_end = that.data.next_end;
    wx.navigateTo({
      url: '../shaixuan/shaixuan?company_style=' + company_style + '&visit_style=' + visit_style + '&level=' + level + '&enter_start=' + enter_start + '&enter_end=' + enter_end + '&next_start=' + next_start + '&next_end=' + next_end
    })
  },
  toDetail: function (e) {
    let companyId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../journalDetail/journalDetail?companyId=' + companyId
    })
  },
  toReport: function (e) {
    let companyId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../report/report?companyId=' + companyId
    })
  },
  toJournalLog: function (e) {
    let companyId =e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../journalLog/journalLog?companyId=' + companyId
    })
  },
  toCustom: function () {
    wx.navigateTo({
      url: '../custom/custom'
    })
  },
  prevBtn: function (e) {
    let that = this;
    let p = that.data.pageCurrent;
    if(p <= 1){
      p = 1;
      return;
    } else {
      p = p - 1;
    }
    that.setData({
      ['parms.p']:p
    })
    that.getCompanyList(that.data.parms);
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
      ['parms.p']: p
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
    let that = this;
    that.setData({
      ['parms.cs']: that.data.cs,
      ['parms.company_style']: that.data.company_style,
      ['parms.visit_style']: that.data.visit_style,
      ['parms.level']: that.data.level,
      ['parms.next_start']: that.data.next_start,
      ['parms.next_end']: that.data.next_end,
      ['parms.enter_start']: that.data.enter_start,
      ['parms.enter_end']: that.data.enter_end,
    });

    that.getCompanyList(that.data.parms)
    if (that.data.list != '') {
      that.setData({
        noResult: false
      })
    }

  },
  toPage: function (e) {
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.p']: p
    })
    that.getCompanyList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
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