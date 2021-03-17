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
    item: {},
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
    console.log('123')
    let id = this.data.id
    let that = this
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getConsult('/v1/consult/detail', {
          id: id
        }, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
          if (res.error_code == 0) {
            that.setData({
              item: res.data.detail,
            })
          } else {
            that.setData({
              item: '',
              noResult: true,
              noInternet: false,
            })
          }
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
  //打电话
  toTel(e) {
    let tel = e.currentTarget.dataset.tel
    wx.makePhoneCall({
      phoneNumber: tel
    })
  },
  // 处理
  toDeal(e) {
    let id = e.currentTarget.dataset.id
    alertViewWithCancel("提交", "是否确认该条信息已处理？", function () {
      wx.getStorage({
        key: 'info',
        success: function (res) {
          let token = res.data.token;
          postConsult('/v1/consult/deal', {
            id: id
          }, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              wx.showToast({
                title: '处理成功',
                icon: 'success',
                duration: 200
              })
              that.getOrderList(that.data.parms);
            } else {
              alertViewWithCancel("处理失败", res.error_msg, function () {});
              return;
            }
          })
        }
      })
    })
  },
  // 新增记录
  toAdd(e) {
    let id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: 'recordAdd?id=' + id,
    })
  },
  // 历史记录
  toRecord(e) {
    let id = e.currentTarget.dataset.id
    wx.navigateTo({
      url: 'recordList?id=' + id,
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