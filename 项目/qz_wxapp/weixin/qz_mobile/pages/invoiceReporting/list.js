import {
  getInvoice,
  postInvoice
} from "../../utils/api.js"

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    showCancel: true,
    success: function (res) {
      if (res.confirm) {
        confirm();
      } else if (res.cancel) {

      }
    }
  });
}

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
Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: '',
    cs: '',
    style: [{
      id: '',
      name: '请选择'
    }, {
      id: '1',
      name: '增值税专用发票'
    }, {
      id: '2',
      name: '增值税普通发票'
    }, {
      id: '3',
      name: '增值税电子普通发票'
    }],
    style_index: 0,
    styleId: '',
    isNewId: '',
    status: [{
      id: '',
      name: '请选择'
    }, {
      id: '0',
      name: '待提交'
    }, {
      id: '1',
      name: '待审核'
    }, {
      id: '2',
      name: '待开票'
    }, {
      id: '3',
      name: '已卡票'
    }, {
      id: '4',
      name: '已驳回'
    }],
    status_index: 0,
    statusId: '',
    parms: {
      payment_id: '', // 关联报备id
      invoice_title: '', // 发票抬头
      status: '', // 开票类型
      type: '', // 状态
      page: 1,
      limit: 10
    },
    list: [],
    pageCurrent: '',
    pageTotalNumber: '',
    pageNumber: [],
    noResult: false,
    noInternet: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    console.log(options)
    if (options.id) {
      let id = options.id
      this.setData({
        ['parms.payment_id']: id
      })
    }
    this.getOption()
  },
  onReady: function () {

  },
  onShow: function () {
    let that = this;
    if (!that.data.id) {
      that.getList(that.data.parms);
      if (that.data.list != '') {
        that.setData({
          noResult: false
        })
      }
    }

  },
  getOption() {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getInvoice('/v1/invoice/options', {}, {
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            let style = [{
              id: '',
              name: '请选择'
            }, ...res.data.type]
            let status = [{
              id: '',
              name: '请选择'
            }, ...res.data.status]
            that.setData({
              style,
              status
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
  /**
   * 搜索条件
   */
  // 1. 类型勾选
  bindPickerChange_style: function (e) {
    let id = this.data.style[e.detail.value].id;
    this.setData({
      style_index: e.detail.value,
      styleId: id
    })
    let that = this;
    that.setData({
      ['parms.type']: id,
      ['parms.page']: 1
    })
    that.getList(that.data.parms);
  },
  bindPickerChange_status: function (e) {
    let id = this.data.status[e.detail.value].id;
    this.setData({
      status_index: e.detail.value,
      statusId: id
    })
    let that = this;
    that.setData({
      ['parms.status']: id,
      ['parms.page']: 1
    })
    that.getList(that.data.parms);
  },
  // 2. 搜索
  searchWord: function (e) {
    let that = this;
    let value = e.detail.value;
    that.setData({
      ['parms.payment_id']: '',
      ['parms.invoice_title']: value,
      ['parms.page']: 1
    })
    that.getList(that.data.parms);
  },
  // 3. 分页处理
  prevBtn: function (e) {
    let that = this;
    let p = that.data.pageCurrent;
    if (p <= 1) {
      p = 1;
      return;
    } else {
      p = p - 1;
    }
    that.setData({
      ['parms.page']: p
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },
  nextBtn: function (e) {
    let that = this;
    let p = that.data.pageCurrent;
    let max = that.data.pageTotalNumber;
    if (p >= max) {
      p = max;
      return;
    } else {
      p = p + 1;
    }
    that.setData({
      ['parms.page']: p
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })

  },
  toPage: function (e) {
    let that = this;
    let p = e.detail.value;
    p = Number(p) + 1;
    that.setData({
      ['parms.page']: p
    })
    that.getList(that.data.parms);
    wx.pageScrollTo({
      scrollTop: 0,
      duration: 0
    })
  },

  //错误提示
  popRemark: function (e) {
    let title = e.target.dataset.title;
    let content = e.target.dataset.content;
    wx.showModal({
      title: title,
      content: content,
      showCancel: false,
      confirmText: '知道了',
      success(res) {
        if (res.confirm) {}
      }
    })
  },

  /**
   * 请求数据
   */
  getList: function (parms) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getInvoice('/v1/invoice/list', that.data.parms, {
          token: token,
          'content-type': 'application/x-www-form-urlencoded',
        }).then(res => {
          if (res.error_code == 0) {
            let totalNumber = res.data.page.page_total_number;
            let list = res.data.list;
            let pageArr = [];
            for (let i = 0; i < totalNumber; i++) {
              pageArr.push(i + 1)
            }
            that.setData({
              list: list,
              noResult: false,
              page: true,
              pageCurrent: res.data.page.page_current,
              pageTotalNumber: res.data.page.page_total_number,
              pageNumber: pageArr,
            })
            if (list.length == 0) {
              that.setData({
                noResult: true,
                page: false
              })
            }
          } else {
            console.log(res.error_msg)
          }
        }).catch(function (error) {
          that.setData({
            list: [],
            page: false,
            noInternet: true
          })
        })
      }
    })
  },
  /**
   *列表操作(编辑、删除、撤回、查看、补充)
   */
  // 编辑
  toEdit: function (e) {
    let id = e.currentTarget.dataset.id;
    let cooperation_type = e.currentTarget.dataset.cooperation_type;
    // wx.removeStorage({
    //   key: 'person_list',
    //   success: function (res) {
    //     // console.log(res)
    //   }
    // })
    wx.navigateTo({
      url: '../invoiceReporting/add?id=' + id
    })
  },
  // 提交
  toSubmit: function (e) {
    let that = this;
    let id = e.currentTarget.dataset.id;

    alertViewWithCancel("提交确认", "确认要提交至财务处审核吗？", function () {
      wx.getStorage({
        key: 'info',
        success: function (res) {
          let token = res.data.token;
          postInvoice('/v1/invoice/submit', {
            id: id
          }, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              that.getList(that.data.parms);
              wx.showToast({
                title: '提交成功',
                icon: 'success',
                duration: 2000
              })
            } else {
              alertViewNoCancel("提交失败", res.error_msg, function () {});
              return;
            }
          })
        }
      })
    });
  },
  // 撤回
  toback: function (e) {
    let that = this;
    let id = e.currentTarget.dataset.id;
    alertViewWithCancel("撤回确认", "确认要撤回吗？（该操作不可挽回)", function () {
      wx.getStorage({
        key: 'info',
        success: function (res) {
          let token = res.data.token;
          postInvoice('/v1/invoice/recall', {
            id: id
          }, {
            token: token,
            'content-type': 'application/x-www-form-urlencoded',
          }).then(res => {
            if (res.error_code == 0) {
              that.getList(that.data.parms);
              wx.showToast({
                title: '撤回成功',
                icon: 'success',
                duration: 2000
              })
            } else {
              alertViewNoCancel("撤回失败", res.error_msg, function () {

              });
              return;
            }
          })
        }
      })
    });
  },
  // 详情
  toDetail: function (e) {
    let id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../invoiceReporting/details?id=' + id,
    })
  },

  /**
   * 跳转操作
   */
  toAddReport: function () { //添加新报备
    // wx.removeStorage({
    //   key: 'person_list',
    //   success: function (res) {
    //     console.log(res)
    //   }
    // })
    wx.navigateTo({
      url: '../invoiceReporting/add'
    })
  },
  toMemberReport: function () { // 重载
    wx.navigateTo({
      url: '../sReport/sReport'
    })
  },
})