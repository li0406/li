// pages/consult/detail.js
import {
  getConsult,
  postConsult
} from "../../utils/api.js"
const app = getApp();
const util = require('../../utils/util.js');

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: true,
    success: function (res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    nomore: 2, //1加载中 2加载完成 3没数据了
    page: 1,
    // 请求数据
    list: [],
    pageCurrent: '0',
    pageTotalNumber: '0',
    noResult: false,
    page: false,
    pageNumber: [],
    id: '',
    noResult: false,
    noInternet: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    console.log(options)
    let that = this
    if (options) {
      that.setData({
        id: options.id
      })
      that.getDetail()
    }
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
  getDetail() {
    let id = this.data.id
    let that = this
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getConsult('/v1/consult/record/list', {
          consult_id: id
        }, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
            if (res.error_code == 0) {
              //获取页码
              let totalNumber = res.data.page.page_total_number;
              let pageArr = [];
              for (let i = 0; i < totalNumber; i++) {
                pageArr.push(i + 1)
              }
              let datalist = res.data.list
              if (datalist.length < 1) {
                that.setData({
                  list: [],
                  page: false,
                  noResult: true
                })
                return false
              }
              that.setData({
                list: datalist,
                pageCurrent: res.data.page.page_current,
                pageTotalNumber: res.data.page.page_total_number,
                pageNumber: pageArr,
                noResult: false,
                page: true
              })
              if (res.data.page.total_number < 10) {
                that.setData({
                  page: false
                })
              } else {
                that.setData({
                  page: true
                })
              }
            } else {
              that.setData({
                list: [],
                page: false,
                noResult: true,
                noInternet: false,
              })
            }
          }).catch(function (error) {
            that.setData({
              list: [],
              page: false,
              noResult: false,
              //   noInternet: true
            })
          })
      },
      fail: function (res) {
        wx.showToast({
          title: '请登陆',
          icon: 'none',
          duration: 2000
        })
        setTimeout(function () {
          wx.navigateTo({
            url: '../login/login'
          })
        }, 2000)

      }
    })
  },
  // 网络故障
  toCustom: function () {
    wx.navigateTo({
      url: '../consult/detail'
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