// pages/consult/detail.js
import {
  getConsult,
  postConsult
} from "../../utils/api.js"
const app = getApp();
const util = require('../../utils/util.js');
function alertViewNoCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
      title: title,
      content: content,
      showCancel: false,
      success: function (res) {
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
    data: {},
    recordStatusList: [],
    recordStatusIndex: -1,
    dealTypeList: [],
    dealTypeIndex: -1,
    successLevelList: [],
    successLevelIndex: -1,
    successLevelDesc: '',
    parms: {
      consult_id: '',
      address: '',
      deal_type: '',
      success_level: '',
      communication: '',
      status: '',
      deal_man: ''
    }
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    console.log(options)
    let that = this
    if (options) {
      that.setData({
        ['parms.consult_id']: options.id
      })
    }
    that.getFormOptions()
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
  //筛选数据
  getFormOptions: function () {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        that.setData({
          ['parms.deal_man'] : res.data.user
        })
        getConsult('/v1/consult/options', {
          actfrom: '2'
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          that.setData({
            recordStatusList: res.data.record_status_list,
            dealTypeList: res.data.deal_type_list,
            successLevelList: res.data.success_level_list,
            successLevelDesc: res.data.success_level_desc
          })
        })
      }
    })
  },

  // 合作状态
  bindPickerChange_rs: function (e) {
    let id = this.data.recordStatusList[e.detail.value].id
    this.setData({
      recordStatusIndex: e.detail.value,
      ['parms.status']: id
    })
  },
  // 跟踪方式
  bindPickerChange_dt: function (e) {
    let id = this.data.dealTypeList[e.detail.value].id
    this.setData({
      dealTypeIndex: e.detail.value,
      ['parms.deal_type']: id
    })
  },
  // 意向等级
  bindPickerChange_sl: function (e) {
    let id = this.data.successLevelList[e.detail.value].id
    console.log(id)
    this.setData({
      successLevelIndex: e.detail.value,
      ['parms.success_level']: id,
    })
  },
  //
  dealManInput(e) {
    this.setData({
      ['parms.deal_man']: e.detail.value
    })
  },
  addressInput(e) {
    this.setData({
      ['parms.address']: e.detail.value
    })
  },
  communicationInput(e) {
    this.setData({
      ['parms.communication']: e.detail.value
    })
  },
  sumbit() {
    let that = this;
    if (that.data.parms.deal_man == '') {
      alertViewNoCancel("提交失败", "请输入跟踪人员", function () {});
      return;
    }
    if (that.data.parms.deal_type == '') {
      alertViewNoCancel("提交失败", "请选择跟踪方式", function () {});
      return;
    }
    if (that.data.parms.success_level == '') {
      alertViewNoCancel("提交失败", "请选择意向等级", function () {});
      return;
    }
    if (that.data.parms.communication == '') {
      alertViewNoCancel("提交失败", "请输入谈话内容", function () {});
      return;
    }
    if (that.data.parms.status == '') {
      alertViewNoCancel("提交失败", "请选择合作状态", function () {});
      return;
    }
    wx.getStorage({
      key: 'info',
      success: function (res) {
          let token = res.data.token;
          postConsult('/v1/consult/record/add', that.data.parms, {
              token: token,
              'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
              if (res.error_code == 0) {
                  wx.showToast({
                      title: '提交成功',
                      icon: 'success',
                      duration: 200
                  })
                  setTimeout(function () {
                      wx.navigateBack({
                          delta: 1,
                      })
                  }, 300)
              } else {
                  alertViewNoCancel("提交失败", res.error_msg, function () {
                  });
                  return;
              }
          })
      }
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