// pages/invoiceReporting/details.js
import {
  getInvoice,
  postInvoice
} from "../../utils/api.js"
Page({
  /**
   * 页面的初始数据
   */
  data: {
    id: "",
    xbbPics: [],
    htPics: [],
    smPics: [],
    checkLog: [],
    more: false,
    moreText: '更多',
    data: {},
    paymentPics: {},
    invoicePics: {},
    invoiceOtherPics: {},
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let id = options.id
    this.setData({
      id
    })
    this.getInfo(id)
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
  getInfo(id) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getInvoice('/v1/invoice/view', {
          id: id
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let xbbPics = []
            let htPics = []
            let smPics = []
            let checkLog = []
            res.data.paymentPics.forEach(item => {
              if (item.img_src) {
                xbbPics.push(item.img_full)
              }
            });
            res.data.invoicePics.forEach(item => {
              if (item.img_src) {
                htPics.push(item.img_full)
              }
            });
            res.data.invoiceOtherPics.forEach(item => {
              if (item.img_src) {
                smPics.push(item.img_full)
              }
            })
            res.data.invoiceLogs.forEach(item => {
              if (item.img_src) {
                smPics.push(item.img_full)
              }
            })
            res.data.invoiceLogs.forEach(item => {
              // 3 待开票 4 已开票 5 已驳回
              if (item.status === 5) {
               item.status = 4
              }else if(item.status === 4){
                item.status = 3
              }else if(item.status === 3){
                item.status = 5
              }
              checkLog.push(item)
            })

            that.setData({
              data: res.data.invoiceDetails,
              paymentPics: res.data.paymentPics,
              paymentDetails: res.data.paymentDetails,
              invoicePics: res.data.invoicePics,
              invoiceOtherPics: res.data.invoiceOtherPics,
              checkLog,
              xbbPics,
              htPics,
              smPics
            })
          } else {
            alertViewNoCancel("请求失败", res.error_msg, function () {});
            return;
          }
        }).catch(function (error) {})
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
  // 规则展开
  topOpen() {
    this.setData({
      topIsOpen: true
    })
  },
  // 规则收起
  topClose() {
    this.setData({
      topIsOpen: false
    })
  },
  // 预警信息 
  tipReport(e) {
    let id = e.currentTarget.dataset.id
    let info = e.currentTarget.dataset.info || ''
    let status = e.currentTarget.dataset.status
    if(status > 3){
      wx.showModal({
        title: '预警信息',
        content: info,
        showCancel: false,
        confirmText: '知道了',
        success(res) {
          if (res.confirm) {}
        }
      })
    }else{
      wx.showModal({
        title: '预警信息',
        content: info,
        cancelText: '取消',
        confirmText: '查看',
        confirmColor: '#1890FF',
        showCancel: true,
        success: function (res) {
          if (res.confirm) {
            wx.navigateTo({
              url: '../invoiceReporting/list?id=' + id
            })
          }
        }
      });
    }
  },
  moreBtn() {
    let that = this
    let more = !that.data.more
    let text = ''
    if (more) {
      text = '收起'
    } else {
      text = '更多'
    }
    this.setData({
      more: more,
      moreText: text
    })
  },
  // 图片预览
  previewImg: function (e) {
    let name = e.currentTarget.dataset.item
    var index = e.currentTarget.dataset.index;
    var pics = []
    if (name === 'xbbPics') {
      pics = this.data.xbbPics
    } else if (name === 'htPics') {
      pics = this.data.htPics
    } else {
      pics = this.data.smPics
    }
    wx.previewImage({
      current: pics[index],
      urls: pics
    })
  },

})