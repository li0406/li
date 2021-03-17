// pages/company/company.js
import { getCompany } from "../../utils/api.js"
Page({

  /**
   * 页面的初始数据
   */
  data: {
    searchWord:"",
    companyList:[],
    companyName: '请选择',
    company_id: '',
    curCity: '',
    areaName: '',
    cs: '',
    qx: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },
  input(e) {
    let value = e.detail.value;
    this.setData({
      searchWord: value
    })
    this._search(value);
  },
  _search(value) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getCompany('/v1/company/old_list', {
          company_name: value
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          let list = res.data;
          if(res.error_code == 0){
            that.setData({
              companyList: list
            })
            console.log(that.data)
          }else{
            that.setData({
              companyList: []
            })
          }
        })
      }
    })
  },
  searchWord: function (e) {
    let that = this;
    let companyName = e.detail.value;
    let pages = getCurrentPages();
    let prevPage = pages[pages.length - 2];
    prevPage.setData({
      companyName: companyName,
      company_id: '',
      curCity: '',
      areaName: '',
      cs: '',
      qx: ''
    })
    wx.navigateBack({
      delta: 1,
    })
  },
  searchMt: function (e) {
    let companyName = e.currentTarget.dataset.word;
    let pages = getCurrentPages();
    let prevPage = pages[pages.length - 2];
    prevPage.setData({
      companyName: companyName,
      company_id: '',
      curCity: '',
      areaName: '',
      cs: '',
      qx: ''
    })
    wx.navigateBack({
      delta: 1,
    })
  },
  detailMt(e) {
    let companyName = e.currentTarget.dataset.companyname;
    let company_id = e.currentTarget.dataset.companyid;
    let cs = e.currentTarget.dataset.cs;
    let qx = e.currentTarget.dataset.qx;
    let cname = e.currentTarget.dataset.cname;
    let aname = e.currentTarget.dataset.aname;

    let pages = getCurrentPages();
    let prevPage = pages[pages.length - 2];
    prevPage.setData({
      companyName: companyName,
      company_id: company_id,
      curCity: cname,
      areaName: aname,
      cs: cs,
      qx: qx
    })
    wx.navigateBack({
      delta: 1,
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

  }
})