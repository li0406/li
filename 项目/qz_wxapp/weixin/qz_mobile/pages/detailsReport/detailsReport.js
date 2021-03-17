// pages/addsReport/addsReport.js
import {smallinfo} from "../../utils/api.js"
const app = getApp()

function alertViewNoCancel(title = "提示", content = "消息提示", confirm) {
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

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: true,
    success: function(res) {
      if (res.confirm) {
        confirm();
      } else if (res.cancel) {

      }
    }
  });
}
Page({

  /**
   * 页面的初始数据
   */
  data: {
    data: {},
    hzIndex:0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let that = this
    let id = options.id ? options.id : ''
    if (id != '') {
      that.setData({
        ['parms.id']: id,
        id: id
      })
    }
    that.getInfo(id)
  },
  onReady: function() {

  },
  onShow: function() {

  },
  getInfo(id) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        smallinfo('/v1/sale_report/payment/detail', {
          id: id
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            that.setData({
              data: res.data.detail,
              company: res.data.company_account,
              hzIndex: res.data.detail.cooperation_type
            })
          } else {
            alertViewNoCancel("请求失败", res.error_msg, function() {});
            return;
          }
        }).catch(function(error) {})
      },
      fail: function(res) {
        wx.showToast({
          title: '请登陆',
          icon: 'none',
          duration: 2000
        })
        setTimeout(function() {
          wx.navigateTo({
            url: '../login/login'
          })
        }, 2000)
      }
    })
  },
  // 预览图片
  previewImg1: function(e) {
    var index = e.currentTarget.dataset.index;
    var pics = this.data.data.auth_imgs.map(item => {
      return item.img_full
    });
    wx.previewImage({
      current: pics[index].img_full,
      urls: pics
    })
  },
  //跳转大报备
   submit(e) {
     let id = e.currentTarget.dataset.id;
     let cooperation_type = e.currentTarget.dataset.type;
     wx.navigateTo({
       url: '../memberDetail/memberDetail?id=' + id + '&cooperation_type=' + cooperation_type
     })
   }
})