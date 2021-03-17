// pages/erpMemberReport/erpMemberReport.js
import { memberReportDetail } from "../../utils/api.js"
Page({

  /**
   * 页面的初始数据
   */
  data: {
    type: '', // 记录当前显示哪个栏目，装修公司，erp还是三维家
    common: false,
    erp: false,
    three: false,
    id: '', // 当前记录id
    ctype: '', // 会员类型
    info: null // 数据信息
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const type = options.type
    const id = options.id
    const ctype = options.ctype
    console.log(options)
    switch (type) {
      case 'hd':
        this.setData({
          common: true,
          erp: false,
          three: false,
          id: id,
          ctype: ctype
        })
        break
      case 'sanweijia':
        this.setData({
          common: fasle,
          erp: true,
          three: false,
          id: id,
          ctype: ctype
        })
        break
      case 'erp':
        this.setData({
          common: false,
          erp: false,
          three: true,
          id: id,
          ctype: ctype
        })
        break
      default:
        this.setData({
          common: false,
          erp: false,
          three: false,
          id: '',
          ctype: ''
        })
        break
    }
    this.getDetail()
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

  },

  /**
   * 获取当前记录详情
   */
  getDetail() {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        memberReportDetail('/v1/sale_report/show',
          {
            id: that.data.id,
            cooperation_type: that.data.ctype,
            admin_user: 1
          }, {
            'Content-Type': 'application/json',
            'token': token
          }
        ).then(res => {
          console.log(res)
          if (res.error_code == 0) {
            that.setData({
              info: res.data.info
            })
          } else {
            wx.showToast({
              title: res.error_msg,
              duration: 2000,
              icon: 'none'
            })
          }
        })
      },
      fail: function (res) {
        wx.navigateTo({
          url: '../login/login'
        })
      }
    })
  }
})
