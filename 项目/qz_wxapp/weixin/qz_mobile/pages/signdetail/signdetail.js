// pages/signdetail/signdetail.js
import {
  getSignList,
  signHandle,
  reportPaymentList
} from "../../utils/api.js"
Page({
  data: {
    detail: {
      companys: [{
          jc: '公司aa'
        },
        {
          jc: '公司aa'
        }
      ]
    },
    type_fw: '',
    popup: true,
    fdList: [],
    popup2: true
  },
  getSigndetail: function(id) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        getSignList('/v1/saler/signinfo', {
          id: id
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let tex = res.data.text;
            let chktime = res.data.qiandan_chktime?res.data.qiandan_chktime:''
            let qiantime = chktime.slice(0,16)
            if (tex) {
              let str = tex.replace(/↵/g, '\n');
              that.setData({
                str: str
              })
            }
            that.setData({
              detail: res.data,
              qiantime:qiantime
            })
          } else {
            that.setData({
              detail: {}
            })
          }

        })
      }
    })
  },
  // 弹窗
  hidePopup(flag = true) {
    this.setData({
      "popup": flag
    });
  },
  showPopup() {
    this.hidePopup(false);
  },
  signStatus(e) {
    let status = e.detail.value
    this.setData({
      infoStatus: status
    })
  },
  report: function() {
    let that = this
    let infoStatus = that.data.infoStatus;
    if (!infoStatus) {
      wx.showToast({
        title: '请选择状态',
        icon: 'none',
        duration: 2000
      })
      return false
    } else if (infoStatus == 1) {
      let id = that.data.id
      wx.getStorage({
        key: 'info',
        success: function(res) {
          let token = res.data.token;
          signHandle('/v1/orders/qdup', {
            id: id,
            qiandan_status: 1
          }, {
            'content-type': 'application/json',
            'token': token
          }).then(res => {
            if (res.error_code == 0) {
              wx.showToast({
                title: '已保存',
                duration: 1000,
                icon: 'none'
              })
              let type_fw = that.data.type_fw
              setTimeout(function() {
                wx.redirectTo({
                  url: '../signdetail/signdetail?id=' + id + '&status=' + 1 + '&type_fw=' + type_fw
                })
              }, 1000)

              let pages = getCurrentPages(); //当前页面    （pages就是获取的当前页面的JS里面所有pages的信息）
              let prevPage = pages[pages.length - 2]; //上一页面（prevPage 就是获取的上一个页面的JS里面所有pages的信息）

              prevPage.setData({
                status: 1
              })
            }
          })
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
    } else if (infoStatus == 2) {
      let id = that.data.id
      wx.getStorage({
        key: 'info',
        success: function(res) {
          let token = res.data.token;
          signHandle('/v1/orders/qdup', {
            id: id,
            qiandan_status: 2
          }, {
            'content-type': 'application/json',
            'token': token
          }).then(res => {
            if (res.error_code == 0) {
              wx.showToast({
                title: '已保存',
                icon: 'none',
                duration: 1000
              })
              let type_fw = that.data.type_fw
              setTimeout(function() {
                wx.redirectTo({
                  url: '../signdetail/signdetail?id=' + id + '&status=' + 2 + '&type_fw=' + type_fw
                })
              }, 1000)

              let pages = getCurrentPages(); //当前页面    （pages就是获取的当前页面的JS里面所有pages的信息）
              let prevPage = pages[pages.length - 2]; //上一页面（prevPage 就是获取的上一个页面的JS里面所有pages的信息）

              prevPage.setData({
                status: 2
              })
            }
          })
        }
      })
    }

  },
  //取消签单
  canel: function() {
    let that = this
    wx.showModal({
      title: '提示',
      content: '确定要取消签单？',
      success(res) {
        if (res.confirm) {
          let id = that.data.id
          wx.getStorage({
            key: 'info',
            success: function(res) {
              let token = res.data.token;
              signHandle('/v1/orders/qdcacel', {
                id: id
              }, {
                'content-type': 'application/json',
                'token': token
              }).then(res => {
                if (res.error_code == 0) {
                  wx.showToast({
                    title: '已取消该签单',
                    duration: 1000
                  })
                  setTimeout(function() {
                    wx.navigateBack({
                         delta: 1
                    })
                 
                  }, 1000)
                }
              })
            }
          })
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })
  },
  onLoad: function(options) {
    let id = options.id
    let status = options.status
    this.setData({
      id: id,
      status: status
    })
    this.getSigndetail(id)
  },
  // 弹窗
  hidePopup2(flag = true) {
    this.setData({
      "popup2": flag
    });
  },
  check(e){
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../detailsReport/detailsReport?id=' + id + '&noExit=true',
    })
  },
  getFdList(e){
    let id = e.currentTarget.dataset.id;
    let that = this;
    that.setData({
      "popup2": false
    });
    wx.getStorage({
      key: 'info',
      success: function(res) {
        let token = res.data.token;
        reportPaymentList('/v1/sale_report/orderback/report_payment_list', {
          order_id: id
        }, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            that.setData({
              fdList: res.data.list
            })
          }
        })
      }
    })
  },
  onReady: function() {

  },


  onShow: function() {

  },


  onHide: function() {

  },

  onUnload: function() {

  },


  onPullDownRefresh: function() {

  },


  onReachBottom: function() {

  },

  onShareAppMessage: function() {

  }
})