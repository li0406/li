// pages/addsReport/addsReport.js
import {
  smalladd,
  smallinfo,
  editinfo
} from "../../utils/api.js"
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
    companyAccount: {},
    hzIndex: '',
    pics: []
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    let that = this
    let id = options.id ? options.id : ''
    let status = options.status
    if (id != '') {
      that.setData({
        ['parms.id']: id,
        id: id,
        status: status
      })
    }
    if (status == '2') {
      that.setData({
        signImg: '../../img/sign-dai.png'
      })
    } else if (status == '3') {
      that.setData({
        signImg: '../../img/sign-yi.png'
      })
    } else if (status == '4') {
      that.setData({
        signImg: '../../img/sign-bu.png'
      })
    } else if (status == '5') {
      that.setData({
        signImg: '../../img/sign-dkf.png'
      })
    } else if (status == '6') {
      that.setData({
        signImg: '../../img/sign-kbt.png'
      })
    } else if (status == '30') {
      that.setData({
        signImg: '../../img/sign-kfw.png'
      })
    }
    that.editinfo(id)
  },
  onReady: function() {

  },
  onShow: function() {

  },
  editinfo(id) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        smallinfo('/v1/sale_report/payment/detail', {
          id: id,
          act_from: 1
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let img_list = res.data.detail.auth_imgs
            let pics = img_list.map((item, index) => {
              return item.img_full
            })
            that.setData({
              data: res.data.detail,
              companyAccount: res.data.company_account,
              hzIndex: res.data.detail.cooperation_type,
              pics: pics
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
    var pics = this.data.pics;
    wx.previewImage({
      current: pics[index],
      urls: pics
    })
  },
  // 审核
  toSelect: function(e) {
    let that = this
    let id = that.data.id;
    that.setData({
      showModal: true,
      checkId: id
    })
  },
  preventTouchMove: function() { // 关闭弹窗
    let that = this
    that.setData({
      showModal: false,
      infoStatus: '',
      reason: ''
    })
  },
  // 单选框
  signStatus(e) {
    let status = e.detail.value
    this.setData({
      infoStatus: status
    })
  },
  bindTextAreaBlur: function(e) {
    this.setData({
      reason: e.detail.value
    })
  },
  exampass(e) {
    let that = this
    let id = that.data.checkId
    let reason = that.data.reason
    let infoStatus = e.currentTarget.dataset.info;
    if (infoStatus == '1') {
      wx.getStorage({
        key: 'info',
        success: function(res) {
          let token = res.data.token;
          smalladd('/v1/sale_report/payment/exampass', {
            id: id
          }, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              wx.showToast({
                title: '提交成功',
                icon: 'success',
                duration: 2000
              })
              that.setData({
                showModal: false
              })
              setTimeout(function() {
                wx.navigateBack({
                  delta: 1,
                })
              }, 300)
            } else {
              alertViewNoCancel("提交失败", res.error_msg, function() {});
              return;
            }
          })
        }
      })
    } else if (infoStatus == '2') {
      wx.getStorage({
        key: 'info',
        success: function(res) {
          let token = res.data.token;
          smalladd('/v1/sale_report/payment/examfail', {
            id: id,
            reason: reason
          }, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              wx.showToast({
                title: '提交成功',
                icon: 'success',
                duration: 2000
              })
              that.setData({
                showModal: false
              })
              setTimeout(function() {
                wx.navigateBack({
                  delta: 1,
                })
              }, 300)
            } else {
              alertViewNoCancel("提交失败", res.error_msg, function() {});
              return;
            }
          })
        }
      })
    }

  },
})