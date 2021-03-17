// pages/qzjg/qzjg.js
const app = getApp()
let apiUrl = 'https://appapi.qizuang.com';
let imgUrl = app.getImgUrl();
const navActive = require('../../utils/util.js');
var config = require('../../config');

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: false,
    success: function(res) {
      if (res.confirm) {
        confirm();
      }
    }
  });
}
Page({
  data: {
    showDialog: false,
    imgUrl: imgUrl,
  },
  onLoad: function(options) {
    this.setData({
      id: options.id
    })
    var that = this;
    wx.request({
      url: apiUrl + '/appletorder/detail?order_no=' + options.id,
      data: {
        order_no: options.id
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "POST",
      success: function(res) {
        if (res.data.status == 1) {
          that.setData({
            keting: res.data.data.child.kt,
            chufang: res.data.data.child.cf,
            woshi: res.data.data.child.zw,
            wsj: res.data.data.child.wsj,
            sd: res.data.data.child.sd,
            other: res.data.data.child.other,
            banbaojia: res.data.data.total,
          })
        }
      }
    })
  },
  clickgz() {
    this.setData({
      showDialog: !this.data.showDialog
    });
  },
  toggleDialog() {
    this.setData({
      showDialog: !this.data.showDialog
    });
  },
})