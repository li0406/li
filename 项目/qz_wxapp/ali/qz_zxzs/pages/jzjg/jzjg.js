const app = getApp();
let apiUrl = app.getApiUrl(),
  imgUrl = app.getImgUrl();

let config = require("../../config.js");

function alertViewWithCancel(title, content, confirm) {
  my.alert({
    title: title || "提示",
    content: content || "消息提示",
    success: function() {
      confirm();
    }
  });
}

Page({
  data: {
    info: false,
    banbaojia: {},
    halfTotal: '',
    keting: {},
    chufang: [],
    woshi: [],
    wsj: [],
    sd: [],
    other: []
  },
  rule() {
    this.setData({
      info: true
    })
  },
  tapinfo() {
    this.setData({
      info: false
    })
  },
  onLoad: function(options) {
    // if (!options.orderid) {
    //   my.navigateTo({
    //     url: '/pages/jzact/jzact'
    //   })
    // }
    var that = this;
    my.request({
      url: 'https://appapi.qizuang.com/appletorder/detail?order_no=' + 2019101628257302,
      data: {
        order_no: options.orderid
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      method: "POST",
      success: function(res) {
        if (res.data.status != 1) {
          return
        }
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
    })
  }
});
